package com.reskator.invoicing.invoice

import com.reskator.invoicing.NotFoundException
import com.reskator.invoicing.customer.CustomerService
import org.jdbi.v3.core.Jdbi
import org.jdbi.v3.core.kotlin.inTransactionUnchecked
import org.jdbi.v3.core.kotlin.useTransactionUnchecked
import org.jdbi.v3.core.kotlin.withHandleUnchecked

/** Invoices of one company at a time: another company's invoice with the same number is not found (B2). */
class InvoiceService(
    private val jdbi: Jdbi,
    private val repository: InvoiceRepository,
    private val customers: CustomerService,
    private val parameters: ParameterService
) {
    fun list(company: Int) = jdbi.withHandleUnchecked { repository.list(it, company) }

    fun get(company: Int, invoiceNumber: Int): Invoice = jdbi.withHandleUnchecked {
        repository.find(it, company, invoiceNumber) ?: throw NotFoundException("Invoice not found")
    }

    /** Saves the invoice and its customer, and returns the invoice number it got. */
    fun create(company: Int, input: InvoiceInput): Int {
        val invoice = input.validate()

        return jdbi.inTransactionUnchecked { handle ->
            repository.lockCompany(handle, company)
            val customer = customers.save(handle, company, invoice.customerId, invoice.customer)
            val base = repository.maxReferenceNumber(handle, company)?.let(ReferenceCounter::nextBase)
                ?: parameters.initialReferenceNumber(handle, company)
            val invoiceNumber = repository.maxInvoiceNumber(handle, company)?.plus(1)
                ?: parameters.initialInvoiceNumber(handle, company)

            val id = repository.insert(handle, company, customer, invoiceNumber, ReferenceCounter.reference(base), invoice)
            invoice.items.forEach { repository.insertItem(handle, id, it) }
            invoiceNumber
        }
    }

    /**
     * Saves the invoice and its customer, and syncs the items like Invoice::updateFromModel: items missing from
     * the input are removed, items without an id added, and the rest updated.
     */
    fun update(company: Int, invoiceNumber: Int, input: InvoiceInput) {
        val invoice = input.validate()

        jdbi.useTransactionUnchecked { handle ->
            val existing = repository.find(handle, company, invoiceNumber) ?: throw NotFoundException("Invoice not found")
            val customer = customers.save(handle, company, invoice.customerId, invoice.customer)
            repository.update(handle, existing.id, customer, invoice)

            repository.removeItemsExcept(handle, existing.id, invoice.items.mapNotNull { it.id })
            invoice.items.filter { it.id == null }.forEach { repository.insertItem(handle, existing.id, it) }
            invoice.items.filter { it.id != null }.forEach {
                if (!repository.updateItem(handle, existing.id, it)) throw NotFoundException("Invoice item not found")
            }
        }
    }

    fun delete(company: Int, invoiceNumber: Int) {
        jdbi.useTransactionUnchecked { handle ->
            val invoice = repository.find(handle, company, invoiceNumber) ?: throw NotFoundException("Invoice not found")
            repository.delete(handle, invoice.id)
        }
    }
}
