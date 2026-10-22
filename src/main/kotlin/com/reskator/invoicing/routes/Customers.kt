package com.reskator.invoicing.routes

import com.reskator.invoicing.customer.CustomerInput
import com.reskator.invoicing.customer.CustomerService
import com.reskator.invoicing.plugins.company
import io.ktor.http.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import org.koin.ktor.ext.inject

/** Inside companyRoutes. */
fun Route.customers() {
    val customerService by inject<CustomerService>()

    get("/customers") {
        call.respond(customerService.getAll(call.company.id))
    }

    get("/customers/{name}") {
        val customers = customerService.search(call.company.id, call.parameters["name"].orEmpty())
        if (customers.isEmpty()) call.respond(HttpStatusCode.NotFound, "") else call.respond(customers)
    }

    get("/customer/{id}") {
        call.respond(customerService.get(call.company.id, intParam("id")))
    }

    // Updates the customer in the URL; the PHP app used the id in the body
    patch("/customer/{id}") {
        customerService.update(call.company.id, intParam("id"), jsonBody<CustomerInput>())
        call.respond(HttpStatusCode.NoContent)
    }
}
