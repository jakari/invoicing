package com.reskator.invoicing

import kotlinx.serialization.Serializable

@Serializable
data class Violation(val property: String, val message: String)

class ValidationException(val violations: List<Violation>) : RuntimeException("Validation failed")

/** Collects violations with Symfony's messages, so all of them are reported at once. */
class Violations {
    private val list = mutableListOf<Violation>()

    fun add(property: String, message: String) {
        list += Violation(property, message)
    }

    /** NotBlank (whitespace counts as blank) and the column's length. */
    fun notBlank(property: String, value: String?, max: Int = 255): String {
        if (value.isNullOrBlank()) add(property, "This value should not be blank.") else length(property, value, max)
        return value.orEmpty()
    }

    /** NotNull and the column's length; empty strings are fine. */
    fun notNull(property: String, value: String?, max: Int = 255): String {
        if (value == null) add(property, "This value should not be null.") else length(property, value, max)
        return value.orEmpty()
    }

    fun optional(property: String, value: String?, max: Int = 255): String? {
        if (value != null) length(property, value, max)
        return value
    }

    private fun length(property: String, value: String, max: Int) {
        if (value.length > max) add(property, "This value is too long. It should have $max characters or less.")
    }

    fun throwIfAny() {
        if (list.isNotEmpty()) throw ValidationException(list)
    }
}
