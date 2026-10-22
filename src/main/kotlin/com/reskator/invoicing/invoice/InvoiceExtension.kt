package com.reskator.invoicing.invoice

import io.pebbletemplates.pebble.extension.AbstractExtension
import io.pebbletemplates.pebble.extension.Filter
import io.pebbletemplates.pebble.template.EvaluationContext
import io.pebbletemplates.pebble.template.PebbleTemplate
import java.io.File
import java.net.URLConnection
import java.util.Base64

/** The invoice templates' filters: amounts as "1 234,56", the invoice sums, and images embedded as data URIs. */
class InvoiceExtension(private val templateDir: File) : AbstractExtension() {
    override fun getFilters(): Map<String, Filter> = mapOf(
        "money" to filter { (it as Money).format() },
        "itemTotal" to filter { InvoiceCalculator.itemTotal(it as InvoiceItem).format() },
        "itemTotalWithoutTax" to filter { InvoiceCalculator.itemTotalWithoutTax(it as InvoiceItem).format() },
        "total" to filter { InvoiceCalculator.total((it as Invoice).items).format() },
        "totalWithoutTax" to filter { InvoiceCalculator.totalWithoutTax((it as Invoice).items).format() },
        "taxTotal" to filter { InvoiceCalculator.taxTotal((it as Invoice).items).format() },
        "base64image" to filter { base64Image(it.toString()) }
    )

    /** An image file in the template directory, e.g. `"logo.gif"|base64image`, as a data URI. */
    private fun base64Image(name: String): String {
        require(name == File(name).name) { "Not a file name in the template directory: $name" }
        val file = File(templateDir, name)
        val mime = URLConnection.guessContentTypeFromName(name) ?: "application/octet-stream"
        return "data:$mime;base64," + Base64.getEncoder().encodeToString(file.readBytes())
    }
}

private fun filter(apply: (Any) -> Any?) = object : Filter {
    override fun getArgumentNames() = emptyList<String>()

    override fun apply(input: Any?, args: Map<String, Any?>, self: PebbleTemplate, context: EvaluationContext, lineNumber: Int) =
        input?.let(apply)
}
