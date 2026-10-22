package com.reskator.invoicing.plugins

import com.reskator.invoicing.config.Env
import com.reskator.invoicing.routes.api
import com.reskator.invoicing.routes.auth
import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.http.content.*
import io.ktor.server.plugins.compression.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import java.io.File

fun Application.configureRouting(env: Env) {
    install(Compression) {
        gzip()
    }

    routing {
        auth()
        api()
        // Unknown API paths are a 404, not the client's index.html
        route("/api/{...}") {
            handle { call.respond(HttpStatusCode.NotFound, "") }
        }
        // The built client; its routes fall back to index.html (replaces dokku/nginx_app.conf)
        staticFiles("/", File(env.webRoot)) {
            default("index.html")
        }
    }
}
