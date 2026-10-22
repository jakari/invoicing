package com.reskator.invoicing.customer

import com.reskator.invoicing.Violations
import com.reskator.invoicing.NotFoundException
import com.reskator.invoicing.invoice.InvoiceRepository
import org.jdbi.v3.core.Handle
import org.jdbi.v3.core.Jdbi
import org.jdbi.v3.core.kotlin.inTransactionUnchecked
import org.jdbi.v3.core.kotlin.withHandleUnchecked

/** Customers of one company at a time: another company's customer is not found. */
class CustomerService(
    private val jdbi: Jdbi,
    private val repository: CustomerRepository,
    private val invoices: InvoiceRepository
) {
    fun getAll(company: Int) = jdbi.withHandleUnchecked { repository.getAll(it, company) }

    fun search(company: Int, name: String) = jdbi.withHandleUnchecked { repository.search(it, company, name) }

    fun get(company: Int, id: Int): CustomerWithInvoices = jdbi.withHandleUnchecked {
        val customer = repository.find(it, company, id) ?: throw NotFoundException("Customer not found")
        customer.withInvoices(invoices.listForCustomer(it, company, id))
    }

    fun update(company: Int, id: Int, input: CustomerInput) {
        val details = Violations().let { violations -> input.validate(violations).also { violations.throwIfAny() } }
        jdbi.inTransactionUnchecked { update(it, company, id, details) }
    }

    /** Saves the customer of an invoice: creates it when it has no id, else updates the company's customer. Returns its id. */
    fun save(handle: Handle, company: Int, id: Int?, details: CustomerDetails): Int =
        if (id == null || id == 0) {
            repository.create(handle, company, details)
        } else {
            update(handle, company, id, details)
            id
        }

    private fun update(handle: Handle, company: Int, id: Int, details: CustomerDetails) {
        if (!repository.update(handle, company, id, details)) throw NotFoundException("Customer not found")
    }
}
