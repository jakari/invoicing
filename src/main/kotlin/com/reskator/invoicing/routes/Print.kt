package com.reskator.invoicing.routes

import com.reskator.invoicing.invoice.InvoiceRenderer
import com.reskator.invoicing.invoice.InvoiceSender
import com.reskator.invoicing.invoice.InvoiceService
import com.reskator.invoicing.invoice.PdfRenderer
import com.reskator.invoicing.plugins.company
import io.ktor.http.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.koin.ktor.ext.inject

/** Inside companyRoutes. */
fun Route.printing() {
    val invoiceService by inject<InvoiceService>()
    val renderer by inject<InvoiceRenderer>()
    val pdfRenderer by inject<PdfRenderer>()
    val sender by inject<InvoiceSender>()

    // The HTML the PDF is made of, for previewing a template
    get("/invoice/{number}/print_html") {
        val invoice = invoiceService.get(call.company.id, intParam("number"))
        call.respondText(renderer.render(invoice), ContentType.Text.Html)
    }

    get("/invoice/lasku-{number}.pdf") {
        val invoice = invoiceService.get(call.company.id, intParam("number"))
        val pdf = withContext(Dispatchers.IO) { pdfRenderer.render(renderer.render(invoice)) }
        call.respondBytes(pdf, ContentType.Application.Pdf)
    }

    post("/invoice/{number}/send-email") {
        val invoice = invoiceService.get(call.company.id, intParam("number"))
        withContext(Dispatchers.IO) { sender.send(invoice) }
        call.respondText("")
    }
}
