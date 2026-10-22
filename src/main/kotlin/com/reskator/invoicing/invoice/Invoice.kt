package com.reskator.invoicing.invoice

import com.reskator.invoicing.customer.Customer
import java.math.BigDecimal
import java.time.LocalDate
import java.time.temporal.ChronoUnit
import kotlin.math.absoluteValue

data class InvoiceItem(
    val id: Int?,
    val description: String,
    val amount: Int,
    val price: Money,
    /** Percentage, numeric(4,2) in the database */
    val tax: BigDecimal
)

/**
 * An invoice with a copy of its customer's details as they were when the invoice was last saved, so later edits
 * of the customer don't change it. The templates read its properties by name.
 */
data class Invoice(
    val id: Int,
    val company: Int,
    val customer: Int,
    val invoiceNumber: Int,
    val referenceNumber: Long,
    val created: LocalDate,
    val due: LocalDate,
    val status: String,
    val remarkingTime: Int,
    val interestOnArrears: Int,
    val customerReference: String?,
    val delivery: String?,
    val conditionsOfPayment: String?,
    val template: String,
    val customerName: String,
    val customerAdditionalName: String,
    val customerContactPerson: String,
    val customerStreetName: String,
    val customerPostCode: String,
    val customerCity: String,
    val customerEmail: String,
    val customerVat: String?,
    val customerPhone: String?,
    val items: List<InvoiceItem> = emptyList()
) {
    /** Days from the invoice date to the due date, the payment term on the templates. */
    val paymentTermDays: Long get() = ChronoUnit.DAYS.between(created, due).absoluteValue

    fun toOutput() = InvoiceOutput(
        created = created,
        due = due,
        customer = Customer(
            customer, customerName, customerAdditionalName, customerContactPerson, customerStreetName,
            customerPostCode, customerCity, customerEmail, customerVat, customerPhone
        ),
        invoiceNumber = invoiceNumber,
        referenceNumber = referenceNumber,
        items = items.map { ItemOutput(it.id!!, it.description, it.amount, it.price, it.tax) },
        remarkingTime = remarkingTime,
        interestOnArrears = interestOnArrears,
        customerReference = customerReference,
        delivery = delivery,
        conditionsOfPayment = conditionsOfPayment,
        template = template
    )
}
