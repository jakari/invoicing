package com.reskator.invoicing.plugins

import com.reskator.invoicing.NotFoundException
import com.reskator.invoicing.ValidationException
import com.reskator.invoicing.Violation
import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.plugins.*
import io.ktor.server.plugins.statuspages.*
import io.ktor.server.response.*
import kotlinx.serialization.Serializable

@Serializable
data class ValidationErrors(val violations: List<Violation>)

fun Application.configureStatusPages() {
    install(StatusPages) {
        exception<NotFoundException> { call, _ -> call.respond(HttpStatusCode.NotFound, "") }
        exception<ValidationException> { call, e ->
            call.respond(HttpStatusCode.BadRequest, ValidationErrors(e.violations))
        }
        exception<BadRequestException> { call, _ -> call.respond(HttpStatusCode.BadRequest, "") }
        exception<Throwable> { call, cause ->
            call.application.log.error("Unhandled: ${call.request.httpMethod.value} ${call.request.uri}", cause)
            call.respond(HttpStatusCode.InternalServerError, "")
        }
    }
}
