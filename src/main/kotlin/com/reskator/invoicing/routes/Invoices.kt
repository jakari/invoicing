package com.reskator.invoicing.routes

import com.reskator.invoicing.invoice.CreatedInvoice
import com.reskator.invoicing.invoice.InvoiceInput
import com.reskator.invoicing.invoice.InvoiceService
import com.reskator.invoicing.plugins.company
import io.ktor.http.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import org.koin.ktor.ext.inject

/** Inside companyRoutes. Invoices are addressed by their invoice number within the company. */
fun Route.invoices() {
    val invoiceService by inject<InvoiceService>()

    get("/invoices") {
        call.respond(invoiceService.list(call.company.id))
    }

    post("/invoice") {
        call.respond(CreatedInvoice(invoiceService.create(call.company.id, jsonBody<InvoiceInput>())))
    }

    route("/invoice/{number}") {
        get {
            call.respond(invoiceService.get(call.company.id, intParam("number")).toOutput())
        }

        patch {
            invoiceService.update(call.company.id, intParam("number"), jsonBody<InvoiceInput>())
            call.respond(HttpStatusCode.NoContent)
        }

        delete {
            invoiceService.delete(call.company.id, intParam("number"))
            call.respondText("")
        }
    }
}
