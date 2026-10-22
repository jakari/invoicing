package com.reskator.invoicing.plugins

import io.ktor.serialization.kotlinx.json.*
import io.ktor.server.application.*
import io.ktor.server.plugins.contentnegotiation.*
import kotlinx.serialization.json.Json

/** Nulls are left out of responses. */
val json = Json {
    explicitNulls = false
    ignoreUnknownKeys = true
}

fun Application.configureSerialization() {
    install(ContentNegotiation) {
        json(json)
    }
}
