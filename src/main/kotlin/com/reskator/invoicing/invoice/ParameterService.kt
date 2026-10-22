package com.reskator.invoicing.invoice

import com.reskator.invoicing.settings.InvoiceSettings
import org.jdbi.v3.core.Handle
import org.jdbi.v3.core.kotlin.mapTo

/** Per-company numbering parameters, falling back to the `default` section of the settings file (B8). */
class ParameterService(private val settings: InvoiceSettings) {
    fun initialInvoiceNumber(handle: Handle, company: Int): Int = get(handle, company, "initial_invoice_number").toInt()

    fun initialReferenceNumber(handle: Handle, company: Int): Long = get(handle, company, "initial_reference_number").toLong()

    private fun get(handle: Handle, company: Int, key: String): String {
        val value = handle.createQuery("SELECT value FROM parameters WHERE company = :company AND key = :key")
            .bind("company", company)
            .bind("key", key)
            .mapTo<String>()
            .findFirst()
            .orElse(null)
            ?: settings.default(key)?.toString()

        return checkNotNull(value) { "No parameter $key for company $company, and none under default in the settings" }.trim()
    }
}
