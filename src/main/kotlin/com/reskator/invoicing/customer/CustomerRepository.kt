package com.reskator.invoicing.customer

import org.jdbi.v3.core.Handle
import org.jdbi.v3.core.kotlin.bindKotlin
import org.jdbi.v3.core.kotlin.mapTo

class CustomerRepository {
    private val columns = "id, name, additional_name, contact_person, street_name, post_code, city, email, vat, phone"

    fun getAll(handle: Handle, company: Int): List<Customer> =
        handle.createQuery("SELECT $columns FROM customer WHERE company = :company ORDER BY name ASC")
            .bind("company", company)
            .mapTo<Customer>()
            .list()

    fun search(handle: Handle, company: Int, name: String): List<Customer> =
        handle.createQuery("SELECT $columns FROM customer WHERE company = :company AND name ILIKE CONCAT('%', :name, '%') ORDER BY name ASC")
            .bind("company", company)
            .bind("name", name)
            .mapTo<Customer>()
            .list()

    fun find(handle: Handle, company: Int, id: Int): Customer? =
        handle.createQuery("SELECT $columns FROM customer WHERE company = :company AND id = :id")
            .bind("company", company)
            .bind("id", id)
            .mapTo<Customer>()
            .findOne()
            .orElse(null)

    fun create(handle: Handle, company: Int, details: CustomerDetails): Int =
        handle.createQuery(
            """
            INSERT INTO customer (company, name, additional_name, contact_person, street_name, post_code, city, email, vat, phone)
            VALUES (:company, :name, :additionalName, :contactPerson, :streetName, :postCode, :city, :email, :vat, :phone)
            RETURNING id
            """
        )
            .bind("company", company)
            .bindKotlin(details)
            .mapTo<Int>()
            .one()

    /** Returns false when the company has no such customer. */
    fun update(handle: Handle, company: Int, id: Int, details: CustomerDetails): Boolean =
        handle.createUpdate(
            """
            UPDATE customer SET name = :name, additional_name = :additionalName, contact_person = :contactPerson,
                street_name = :streetName, post_code = :postCode, city = :city, email = :email, vat = :vat, phone = :phone
            WHERE company = :company AND id = :id
            """
        )
            .bind("company", company)
            .bind("id", id)
            .bindKotlin(details)
            .execute() == 1
}
