package com.reskator.invoicing.settings

import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonElement
import kotlinx.serialization.json.JsonNull
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.JsonPrimitive
import org.yaml.snakeyaml.Yaml
import java.io.File

/**
 * The deployment's settings file (default-settings.yml): default invoice values, company and bank details for the
 * templates, e-mail texts and the template list. Read on every use, as the PHP app did, so edits apply without a restart.
 */
class InvoiceSettings(private val path: String) {
    fun load(): Map<String, Any?> = File(path).reader().use { Yaml().load<Map<String, Any?>>(it) } ?: emptyMap()

    @Suppress("UNCHECKED_CAST")
    fun section(name: String): Map<String, Any?> = load()[name] as? Map<String, Any?> ?: emptyMap()

    /** A value under `default`, such as `initial_invoice_number`. */
    fun default(key: String): Any? = section("default")[key]
}

/** Converts parsed YAML to JSON, keeping numbers and booleans as they were in the file. */
fun yamlToJson(value: Any?): JsonElement = when (value) {
    null -> JsonNull
    is Map<*, *> -> JsonObject(value.entries.associate { (k, v) -> k.toString() to yamlToJson(v) })
    is List<*> -> JsonArray(value.map(::yamlToJson))
    is Number -> JsonPrimitive(value)
    is Boolean -> JsonPrimitive(value)
    else -> JsonPrimitive(value.toString())
}
