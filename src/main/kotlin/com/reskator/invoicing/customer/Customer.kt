package com.reskator.invoicing.customer

import com.reskator.invoicing.Violations
import com.reskator.invoicing.invoice.InvoiceListItem
import kotlinx.serialization.Serializable

/** A customer row; the JSON leaves out vat and phone when they are null. */
@Serializable
data class Customer(
    val id: Int,
    val name: String,
    val additionalName: String,
    val contactPerson: String,
    val streetName: String,
    val postCode: String,
    val city: String,
    val email: String,
    val vat: String? = null,
    val phone: String? = null
)

@Serializable
data class CustomerWithInvoices(
    val id: Int,
    val name: String,
    val additionalName: String,
    val contactPerson: String,
    val streetName: String,
    val postCode: String,
    val city: String,
    val email: String,
    val vat: String? = null,
    val phone: String? = null,
    val invoices: List<InvoiceListItem>
)

fun Customer.withInvoices(invoices: List<InvoiceListItem>) =
    CustomerWithInvoices(id, name, additionalName, contactPerson, streetName, postCode, city, email, vat, phone, invoices)

/** A customer as the client sends it, alone or within an invoice. An id of 0 or null means a new customer. */
@Serializable
data class CustomerInput(
    val id: Int? = null,
    val name: String? = null,
    val additionalName: String? = null,
    val contactPerson: String? = null,
    val streetName: String? = null,
    val postCode: String? = null,
    val city: String? = null,
    val email: String? = null,
    val vat: String? = null,
    val phone: String? = null
)

data class CustomerDetails(
    val name: String,
    val additionalName: String,
    val contactPerson: String,
    val streetName: String,
    val postCode: String,
    val city: String,
    val email: String,
    val vat: String?,
    val phone: String?
)

/** Required fields and the lengths of their columns. */
fun CustomerInput.validate(violations: Violations, prefix: String = "") = CustomerDetails(
    name = violations.notBlank(prefix + "name", name),
    additionalName = violations.notNull(prefix + "additionalName", additionalName),
    contactPerson = violations.notNull(prefix + "contactPerson", contactPerson),
    streetName = violations.notBlank(prefix + "streetName", streetName),
    // post_code is varchar(10) in the customer table
    postCode = violations.notBlank(prefix + "postCode", postCode, max = 10),
    city = violations.notBlank(prefix + "city", city),
    email = violations.notNull(prefix + "email", email),
    vat = violations.optional(prefix + "vat", vat),
    phone = violations.optional(prefix + "phone", phone)
)
