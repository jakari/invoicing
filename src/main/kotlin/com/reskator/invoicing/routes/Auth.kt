package com.reskator.invoicing.routes

import com.reskator.invoicing.auth.AuthService
import com.reskator.invoicing.auth.LoginInput
import com.reskator.invoicing.auth.UserSession
import com.reskator.invoicing.settings.SettingsService
import io.ktor.http.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import io.ktor.server.sessions.*
import org.koin.ktor.ext.inject

fun Route.auth() {
    val authService by inject<AuthService>()
    val settingsService by inject<SettingsService>()

    post("/api/login") {
        val input = jsonBody<LoginInput>()
        val session = authService.authenticate(input.username, input.password)
        if (session == null) {
            call.respondText("""{"error":"Invalid credentials."}""", ContentType.Application.Json, HttpStatusCode.Unauthorized)
        } else {
            call.sessions.set(session)
            call.respond(settingsService.response(session.companyId))
        }
    }

    route("/api/logout") {
        handle {
            call.sessions.clear<UserSession>()
            call.respond(HttpStatusCode.NoContent)
        }
    }
}
