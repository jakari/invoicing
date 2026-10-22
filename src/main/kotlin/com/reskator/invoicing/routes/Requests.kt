package com.reskator.invoicing.routes

import com.reskator.invoicing.NotFoundException
import com.reskator.invoicing.plugins.json
import io.ktor.server.plugins.*
import io.ktor.server.request.*
import io.ktor.server.routing.*
import kotlinx.serialization.SerializationException

fun RoutingContext.intParam(name: String) =
    call.parameters[name]?.toIntOrNull() ?: throw NotFoundException()

/** Parses the JSON body whatever the Content-Type, as the PHP app did. */
suspend inline fun <reified T> RoutingContext.jsonBody(): T = try {
    json.decodeFromString<T>(call.receiveText())
} catch (e: SerializationException) {
    throw BadRequestException("Invalid JSON body", e)
} catch (e: IllegalArgumentException) {
    throw BadRequestException("Invalid JSON body", e)
}
