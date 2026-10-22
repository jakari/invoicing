package com.reskator.invoicing.support

import kotlinx.serialization.json.JsonElement
import kotlinx.serialization.json.JsonNull
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.JsonPrimitive
import kotlinx.serialization.json.JsonArray

/** Request bodies like the client sends them. */
object Inputs {
    fun customer(vararg overrides: Pair<String, Any?>): Map<String, Any?> = mapOf(
        "id" to 0,
        "name" to "Asiakas Oy",
        "additionalName" to "",
        "contactPerson" to "",
        "streetName" to "Katu 1",
        "postCode" to "00100",
        "city" to "Helsinki",
        "email" to "asiakas@example.local",
        "vat" to "",
        "phone" to ""
    ) + overrides

    fun item(description: String = "Työ", amount: Int = 1, price: Any = 100, tax: Any = 25.5, id: Int? = null): Map<String, Any?> =
        mapOf("description" to description, "amount" to amount, "price" to price, "tax" to tax) + (if (id != null) mapOf("id" to id) else emptyMap())

    fun invoice(vararg overrides: Pair<String, Any?>): Map<String, Any?> = mapOf(
        "created" to "2026-01-01",
        "due" to "2026-01-15",
        "customer" to customer(),
        "items" to listOf(item()),
        "remarkingTime" to 14,
        "interestOnArrears" to 18,
        "template" to "finnish"
    ) + overrides

    fun json(value: Any?): String = toJson(value).toString()

    private fun toJson(value: Any?): JsonElement = when (value) {
        null -> JsonNull
        is Map<*, *> -> JsonObject(value.entries.associate { (k, v) -> k.toString() to toJson(v) })
        is List<*> -> JsonArray(value.map(::toJson))
        is Number -> JsonPrimitive(value)
        is Boolean -> JsonPrimitive(value)
        else -> JsonPrimitive(value.toString())
    }
}
