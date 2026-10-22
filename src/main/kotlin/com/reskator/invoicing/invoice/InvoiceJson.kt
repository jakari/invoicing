package com.reskator.invoicing.invoice

import com.reskator.invoicing.Violations
import com.reskator.invoicing.customer.Customer
import com.reskator.invoicing.customer.CustomerDetails
import com.reskator.invoicing.customer.CustomerInput
import com.reskator.invoicing.customer.validate
import kotlinx.serialization.Serializable
import java.math.BigDecimal
import java.time.LocalDate

/** An invoice as the API sends it; null optional fields are left out. */
@Serializable
data class InvoiceOutput(
    @Serializable(IsoDateSerializer::class) val created: LocalDate,
    @Serializable(IsoDateSerializer::class) val due: LocalDate,
    val customer: Customer,
    val invoiceNumber: Int,
    val referenceNumber: Long,
    val items: List<ItemOutput>,
    val remarkingTime: Int,
    val interestOnArrears: Int,
    val customerReference: String?,
    val delivery: String?,
    val conditionsOfPayment: String?,
    val template: String
)

@Serializable
data class ItemOutput(
    val id: Int,
    val description: String,
    val amount: Int,
    @Serializable(EurosSerializer::class) val price: Money,
    @Serializable(TaxSerializer::class) val tax: BigDecimal
)

@Serializable
data class CreatedInvoice(val invoiceNumber: Int)

/** An invoice as the client sends it; its totals and other extra fields are ignored. */
@Serializable
data class InvoiceInput(
    @Serializable(IsoDateSerializer::class) val created: LocalDate? = null,
    @Serializable(IsoDateSerializer::class) val due: LocalDate? = null,
    val customer: CustomerInput? = null,
    val items: List<ItemInput>? = null,
    val remarkingTime: Int? = null,
    val interestOnArrears: Int? = null,
    val customerReference: String? = null,
    val delivery: String? = null,
    val conditionsOfPayment: String? = null,
    val template: String? = null
)

/** An item without an id is new; one with an id updates that item of the invoice. */
@Serializable
data class ItemInput(
    val id: Int? = null,
    val description: String? = null,
    val amount: Int? = null,
    @Serializable(EurosSerializer::class) val price: Money? = null,
    @Serializable(TaxSerializer::class) val tax: BigDecimal? = null
)

data class ValidInvoice(
    val created: LocalDate,
    val due: LocalDate,
    val customerId: Int?,
    val customer: CustomerDetails,
    val items: List<InvoiceItem>,
    val remarkingTime: Int,
    val interestOnArrears: Int,
    val customerReference: String?,
    val delivery: String?,
    val conditionsOfPayment: String?,
    val template: String
)

/** Template names become file names, so they are limited to these characters. */
val TEMPLATE_NAME = Regex("^[A-Za-z0-9_-]+$")

private val MAX_TAX = BigDecimal("99.99")

/**
 * Validates the invoice with its customer and items, so that what the database would reject is a 400 listing
 * every violation.
 */
fun InvoiceInput.validate(): ValidInvoice {
    val violations = Violations()

    fun <T> required(property: String, value: T?): T? {
        if (value == null) violations.add(property, "This value should not be blank.")
        return value
    }

    fun range(property: String, value: Int?, max: Int): Int? {
        when {
            value == null -> violations.add(property, "This value should not be blank.")
            value < 0 -> violations.add(property, "This value should be 0 or more.")
            value > max -> violations.add(property, "This value should be $max or less.")
        }
        return value
    }

    val created = required("created", created)
    val due = required("due", due)
    val customer = required("customer", customer)?.let { it.id to it.validate(violations, "customer.") }
    val items = when {
        items == null -> null.also { violations.add("items", "This value should not be blank.") }
        items.isEmpty() -> null.also { violations.add("items", "This collection should contain 1 element or more.") }
        else -> items.mapIndexed { i, item -> item.validate(violations, "items[$i].") }
    }
    val remarkingTime = range("remarkingTime", remarkingTime, 360)
    val interestOnArrears = range("interestOnArrears", interestOnArrears, 100)
    val customerReference = violations.optional("customerReference", customerReference)
    val delivery = violations.optional("delivery", delivery)
    val conditionsOfPayment = violations.optional("conditionsOfPayment", conditionsOfPayment)
    val template = when {
        template.isNullOrBlank() -> null.also { violations.add("template", "This value should not be blank.") }
        !TEMPLATE_NAME.matches(template) -> null.also { violations.add("template", "This value is not valid.") }
        else -> template
    }

    violations.throwIfAny()

    return ValidInvoice(
        created!!, due!!, customer!!.first, customer.second, items!!.map { it!! }, remarkingTime!!, interestOnArrears!!,
        customerReference, delivery, conditionsOfPayment, template!!
    )
}

private fun ItemInput.validate(violations: Violations, prefix: String): InvoiceItem? {
    val description = violations.notBlank(prefix + "description", description)
    if (amount == null) violations.add(prefix + "amount", "This value should not be blank.")
    if (price == null) violations.add(prefix + "price", "This value should not be blank.")
    when {
        tax == null -> violations.add(prefix + "tax", "This value should not be blank.")
        tax.signum() < 0 -> violations.add(prefix + "tax", "This value should be 0 or more.")
        tax > MAX_TAX -> violations.add(prefix + "tax", "This value should be 99.99 or less.")
    }

    return if (amount != null && price != null && tax != null) InvoiceItem(id, description, amount, price, tax) else null
}
