package com.reskator.invoicing.plugins

import com.reskator.invoicing.auth.ExpiringSessionStorage
import com.reskator.invoicing.auth.SESSION_LIFETIME
import com.reskator.invoicing.auth.UserSession
import com.reskator.invoicing.config.Env
import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.auth.*
import io.ktor.server.response.*
import io.ktor.server.sessions.*

const val USER_AUTH = "user"

fun Application.configureSecurity(env: Env) {
    install(Sessions) {
        // Sessions live in memory and the cookie only holds their id, so logout revokes a session for good.
        // A restart logs everyone out, and sessions are dropped from memory once they expire.
        cookie<UserSession>("INVOICING_SESSION", ExpiringSessionStorage()) {
            cookie.path = "/"
            cookie.httpOnly = true
            cookie.secure = env.environment == "prod"
            cookie.maxAgeInSeconds = SESSION_LIFETIME.inWholeSeconds
            cookie.extensions["SameSite"] = "Lax"
            transform(SessionTransportTransformerMessageAuthentication(env.appSecret.toByteArray()))
        }
    }

    install(Authentication) {
        session<UserSession>(USER_AUTH) {
            validate { session ->
                if (session.isExpired()) {
                    sessions.clear<UserSession>()
                    null
                } else {
                    session
                }
            }
            // The client treats an empty 401 as "logged out"
            challenge { call.respond(HttpStatusCode.Unauthorized, "") }
        }
    }
}
