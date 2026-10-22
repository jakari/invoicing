package com.reskator.invoicing.invoice

import com.reskator.invoicing.customer.CustomerDetails
import org.jdbi.v3.core.Handle
import org.jdbi.v3.core.kotlin.mapTo
import org.jdbi.v3.core.statement.SqlStatement
import org.jdbi.v3.core.statement.Update
import java.sql.ResultSet
import java.time.LocalDate

class InvoiceRepository {
    /**
     * The company's invoices, newest first, with totals that include the tax. The tax is rounded per item
     * like InvoiceCalculator does for the printed invoice (B7), and items are joined on invoice.id (B1).
     */
    fun list(handle: Handle, company: Int): List<InvoiceListItem> =
        handle.createQuery(
            """
            SELECT customer.name AS customer, invoice_number, reference_number, created, due,
                   COALESCE(SUM(item.amount * item.price + ROUND(item.amount * item.price * item.tax / 100)), 0) AS total
            FROM invoice
            JOIN customer ON invoice.customer = customer.id
            LEFT JOIN invoice_item item ON invoice.id = item.invoice
            WHERE invoice.company = :company
            GROUP BY invoice.id, customer.id
            ORDER BY created DESC, invoice_number DESC
            """
        )
            .bind("company", company)
            .map { rs, _ -> listItem(rs) }
            .list()

    /** The customer's invoices with totals without tax, in the order they were created. */
    fun listForCustomer(handle: Handle, company: Int, customer: Int): List<InvoiceListItem> =
        handle.createQuery(
            """
            SELECT invoice.customer_name AS customer, invoice_number, reference_number, created, due,
                   COALESCE(SUM(item.amount * item.price), 0) AS total
            FROM invoice
            LEFT JOIN invoice_item item ON invoice.id = item.invoice
            WHERE invoice.company = :company AND invoice.customer = :customer
            GROUP BY invoice.id
            ORDER BY invoice.id
            """
        )
            .bind("company", company)
            .bind("customer", customer)
            .map { rs, _ -> listItem(rs) }
            .list()

    private fun listItem(rs: ResultSet) = InvoiceListItem(
        rs.getString("customer"),
        rs.getInt("invoice_number"),
        rs.getLong("reference_number"),
        rs.getObject("created", LocalDate::class.java),
        rs.getObject("due", LocalDate::class.java),
        Money(rs.getLong("total"))
    )

    /** Invoice numbers run per company, so the same number exists in several companies. */
    fun find(handle: Handle, company: Int, invoiceNumber: Int): Invoice? {
        val invoice = handle.createQuery("SELECT * FROM invoice WHERE company = :company AND invoice_number = :number")
            .bind("company", company)
            .bind("number", invoiceNumber)
            .mapTo<Invoice>()
            .findOne()
            .orElse(null) ?: return null

        return invoice.copy(items = items(handle, invoice.id))
    }

    private fun items(handle: Handle, invoice: Int): List<InvoiceItem> =
        handle.createQuery("SELECT id, description, amount, price, tax FROM invoice_item WHERE invoice = :invoice ORDER BY id")
            .bind("invoice", invoice)
            .map { rs, _ ->
                InvoiceItem(rs.getInt("id"), rs.getString("description"), rs.getInt("amount"), Money(rs.getLong("price")), rs.getBigDecimal("tax"))
            }
            .list()

    /** Serializes invoice creation per company, so concurrent invoices don't get the same number (B6). */
    fun lockCompany(handle: Handle, company: Int) {
        handle.createQuery("SELECT id FROM company WHERE id = :company FOR UPDATE").bind("company", company).mapTo<Int>().one()
    }

    fun maxReferenceNumber(handle: Handle, company: Int): Long? =
        handle.createQuery("SELECT MAX(reference_number) FROM invoice WHERE company = :company")
            .bind("company", company).mapTo<Long>().findOne().orElse(null)?.takeIf { it != 0L }

    fun maxInvoiceNumber(handle: Handle, company: Int): Int? =
        handle.createQuery("SELECT MAX(invoice_number) FROM invoice WHERE company = :company")
            .bind("company", company).mapTo<Int>().findOne().orElse(null)?.takeIf { it != 0 }

    /** Doctrine generated invoice ids from the sequence itself, so invoice.id has no default. */
    fun insert(handle: Handle, company: Int, customer: Int, invoiceNumber: Int, referenceNumber: Long, invoice: ValidInvoice): Int =
        handle.createQuery(
            """
            INSERT INTO invoice (id, company, customer, invoice_number, reference_number, status, $FIELD_COLUMNS)
            VALUES (nextval('invoice_id_seq'), :company, :customer, :invoiceNumber, :referenceNumber, 'PENDING', $FIELD_VALUES)
            RETURNING id
            """
        )
            .bind("company", company)
            .bind("customer", customer)
            .bind("invoiceNumber", invoiceNumber)
            .bind("referenceNumber", referenceNumber)
            .bindInvoice(invoice)
            .mapTo<Int>()
            .one()

    fun update(handle: Handle, id: Int, customer: Int, invoice: ValidInvoice) {
        val assignments = FIELD_COLUMNS.split(", ").zip(FIELD_VALUES.split(", ")).joinToString { (column, value) -> "$column = $value" }
        handle.createUpdate("UPDATE invoice SET customer = :customer, $assignments WHERE id = :id")
            .bind("id", id)
            .bind("customer", customer)
            .bindInvoice(invoice)
            .execute()
    }

    private fun <T : SqlStatement<T>> T.bindInvoice(invoice: ValidInvoice): T = this
        .bind("created", invoice.created)
        .bind("due", invoice.due)
        .bind("remarkingTime", invoice.remarkingTime)
        .bind("interestOnArrears", invoice.interestOnArrears)
        .bind("customerReference", invoice.customerReference)
        .bind("delivery", invoice.delivery)
        .bind("conditionsOfPayment", invoice.conditionsOfPayment)
        .bind("template", invoice.template)
        .bindCustomer(invoice.customer)

    private fun <T : SqlStatement<T>> T.bindCustomer(customer: CustomerDetails): T = this
        .bind("customerName", customer.name)
        .bind("customerAdditionalName", customer.additionalName)
        .bind("customerContactPerson", customer.contactPerson)
        .bind("customerStreetName", customer.streetName)
        .bind("customerPostCode", customer.postCode)
        .bind("customerCity", customer.city)
        .bind("customerEmail", customer.email)
        .bind("customerVat", customer.vat)
        .bind("customerPhone", customer.phone)

    fun insertItem(handle: Handle, invoice: Int, item: InvoiceItem) {
        handle.createUpdate(
            "INSERT INTO invoice_item (invoice, description, amount, price, tax) VALUES (:invoice, :description, :amount, :price, :tax)"
        )
            .bind("invoice", invoice)
            .bindItem(item)
            .execute()
    }

    /** Returns false when the invoice has no such item. */
    fun updateItem(handle: Handle, invoice: Int, item: InvoiceItem): Boolean =
        handle.createUpdate(
            "UPDATE invoice_item SET description = :description, amount = :amount, price = :price, tax = :tax WHERE id = :id AND invoice = :invoice"
        )
            .bind("invoice", invoice)
            .bind("id", item.id)
            .bindItem(item)
            .execute() == 1

    /** Removes the invoice's items that are not in [keep]. */
    fun removeItemsExcept(handle: Handle, invoice: Int, keep: List<Int>) {
        handle.createUpdate("DELETE FROM invoice_item WHERE invoice = :invoice AND NOT (id = ANY(:keep))")
            .bind("invoice", invoice)
            .bindArray("keep", Int::class.javaObjectType, keep)
            .execute()
    }

    fun delete(handle: Handle, id: Int) {
        handle.execute("DELETE FROM invoice_item WHERE invoice = ?", id)
        handle.execute("DELETE FROM invoice WHERE id = ?", id)
    }

    private fun Update.bindItem(item: InvoiceItem) = this
        .bind("description", item.description)
        .bind("amount", item.amount)
        .bind("price", item.price.cents)
        .bind("tax", item.tax)

    companion object {
        private const val FIELD_COLUMNS = "created, due, remarking_time, interest_on_arrears, customer_reference, delivery, " +
            "conditions_of_payment, template, customer_name, customer_additional_name, customer_contact_person, " +
            "customer_street_name, customer_post_code, customer_city, customer_email, customer_vat, customer_phone"
        private const val FIELD_VALUES = ":created, :due, :remarkingTime, :interestOnArrears, :customerReference, :delivery, " +
            ":conditionsOfPayment, :template, :customerName, :customerAdditionalName, :customerContactPerson, " +
            ":customerStreetName, :customerPostCode, :customerCity, :customerEmail, :customerVat, :customerPhone"
    }
}
