package com.reskator.invoicing.plugins

import com.reskator.invoicing.NotFoundException
import com.reskator.invoicing.company.Company
import com.reskator.invoicing.company.CompanyRepository
import io.ktor.server.application.*
import io.ktor.server.auth.*
import io.ktor.server.routing.*
import io.ktor.util.*
import org.koin.ktor.ext.get

private val CompanyKey = AttributeKey<Company>("company")

/**
 * Resolves the `{companyId}` of a company-scoped route, so every request names the company it was made for
 * and an open tab keeps working on its own company whatever another tab selects. An unknown company is a 404.
 * Every user may use every company (there is no user↔company relation); restricting access would go here.
 */
private val CompanyScope = createRouteScopedPlugin("CompanyScope") {
    val companies = application.get<CompanyRepository>()

    // After authentication, so logged out requests get a 401 without revealing which companies exist. The pipeline
    // goes on after the 401 challenge, and throwing then would turn the response into a 404.
    on(AuthenticationChecked) { call ->
        if (call.isHandled) return@on
        val company = call.parameters["companyId"]?.toIntOrNull()?.let(companies::find) ?: throw NotFoundException("Company not found")
        call.attributes.put(CompanyKey, company)
    }
}

/** The company of a route inside [companyRoutes]. */
val ApplicationCall.company: Company get() = attributes[CompanyKey]

/** Routes under `/api/company/{companyId}`. */
fun Route.companyRoutes(build: Route.() -> Unit) = route("/api/company/{companyId}") {
    install(CompanyScope)
    build()
}
