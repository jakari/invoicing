package com.reskator.invoicing.invoice

import kotlinx.serialization.Serializable
import java.time.LocalDate

/** A row of an invoice list: the invoices page shows totals with tax, a customer's page totals without tax. */
@Serializable
data class InvoiceListItem(
    val customer: String,
    val invoiceNumber: Int,
    val referenceNumber: Long,
    @Serializable(ListDateSerializer::class) val created: LocalDate,
    @Serializable(ListDateSerializer::class) val due: LocalDate,
    @Serializable(EurosSerializer::class) val total: Money
)
