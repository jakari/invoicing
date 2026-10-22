package com.reskator.invoicing.company

import kotlinx.serialization.Serializable
import org.jdbi.v3.core.Jdbi
import org.jdbi.v3.core.kotlin.mapTo
import org.jdbi.v3.core.kotlin.withHandleUnchecked

@Serializable
data class Company(val id: Int, val name: String)

class CompanyRepository(private val jdbi: Jdbi) {
    fun getAll(): List<Company> = jdbi.withHandleUnchecked {
        it.createQuery("SELECT id, name FROM company ORDER BY id").mapTo<Company>().list()
    }

    fun find(id: Int): Company? = jdbi.withHandleUnchecked {
        it.createQuery("SELECT id, name FROM company WHERE id = :id").bind("id", id).mapTo<Company>().findOne().orElse(null)
    }
}
