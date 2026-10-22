package com.reskator.invoicing.routes

import com.reskator.invoicing.NotFoundException
import com.reskator.invoicing.auth.UserSession
import com.reskator.invoicing.company.CompanyRepository
import com.reskator.invoicing.plugins.USER_AUTH
import com.reskator.invoicing.plugins.companyRoutes
import com.reskator.invoicing.settings.SettingsService
import io.ktor.http.*
import io.ktor.server.auth.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import io.ktor.server.sessions.*
import org.koin.ktor.ext.inject

fun Route.api() {
    val settingsService by inject<SettingsService>()
    val companies by inject<CompanyRepository>()

    authenticate(USER_AUTH) {
        get("/api/invoice/settings") {
            call.respond(settingsService.response(call.principal<UserSession>()!!.companyId))
        }

        // Only remembers the selection for the settings; company-scoped routes take the company from the URL
        post("/api/select-company/{company}") {
            val company = companies.find(intParam("company")) ?: throw NotFoundException("Company not found")
            call.sessions.set(call.principal<UserSession>()!!.copy(companyId = company.id))
            call.respond(HttpStatusCode.NoContent)
        }

        companyRoutes {
            customers()
            invoices()
            printing()
        }
    }
}
