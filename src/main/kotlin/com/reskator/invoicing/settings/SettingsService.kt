package com.reskator.invoicing.settings

import com.reskator.invoicing.company.Company
import com.reskator.invoicing.company.CompanyRepository
import kotlinx.serialization.json.Json
import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.buildJsonObject
import kotlinx.serialization.json.encodeToJsonElement

/** The settings the client loads after login, in the PHP app's snake_case shape. */
class SettingsService(private val settings: InvoiceSettings, private val companies: CompanyRepository) {
    fun response(selectedCompanyId: Int?): JsonObject {
        val file = settings.load()
        val selected = selectedCompanyId?.let(companies::find)

        return buildJsonObject {
            put("default_values", yamlToJson(file["default"]))
            put("templates", yamlToJson(file["templates"]))
            put("companies", JsonArray(companies.getAll().map { Json.encodeToJsonElement(it) }))
            // Left out when no company is selected, as the PHP serializer skipped nulls
            if (selected != null) put("selected_company", Json.encodeToJsonElement<Company>(selected))
        }
    }
}
