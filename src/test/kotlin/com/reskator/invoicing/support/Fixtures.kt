package com.reskator.invoicing.support

import at.favre.lib.crypto.bcrypt.BCrypt

object Fixtures {
    fun user(username: String = USERNAME, password: String = PASSWORD): Int =
        userWithHash(username, BCrypt.withDefaults().hashToString(4, password.toCharArray()))

    fun userWithHash(username: String, hash: String): Int = TestDatabase.jdbi.withHandle<Int, Exception> {
        it.createQuery("INSERT INTO users (username, password) VALUES (:u, :p) RETURNING id")
            .bind("u", username).bind("p", hash)
            .mapTo(Int::class.java).one()
    }

    fun company(name: String): Int = TestDatabase.jdbi.withHandle<Int, Exception> {
        it.createQuery("INSERT INTO company (name) VALUES (:n) RETURNING id")
            .bind("n", name)
            .mapTo(Int::class.java).one()
    }

    fun parameter(company: Int, key: String, value: String) {
        TestDatabase.jdbi.useHandle<Exception> {
            it.execute("INSERT INTO parameters (company, key, value) VALUES (?, ?, ?)", company, key, value)
        }
    }
}

object InvoiceFixtures {
    data class Item(val description: String, val amount: Int, val priceCents: Int, val tax: String)

    fun customer(company: Int, name: String, vat: String? = null, phone: String? = null): Int =
        TestDatabase.jdbi.withHandle<Int, Exception> {
            it.createQuery(
                """
                INSERT INTO customer (company, name, additional_name, contact_person, street_name, post_code, city, email, vat, phone)
                VALUES (:company, :name, '', '', 'Katu 1', '00100', 'Helsinki', 'asiakas@example.local', :vat, :phone)
                RETURNING id
                """
            )
                .bind("company", company).bind("name", name).bind("vat", vat).bind("phone", phone)
                .mapTo(Int::class.java).one()
        }

    /** Inserts an invoice with the customer's details copied onto it, as the app does, and returns its id. */
    fun invoice(company: Int, customer: Int, number: Int, reference: Int, items: List<Item>, created: String = "2026-01-01"): Int =
        TestDatabase.jdbi.withHandle<Int, Exception> { handle ->
            val id = handle.createQuery(
                """
                INSERT INTO invoice (id, company, customer, invoice_number, reference_number, created, due, status, remarking_time,
                                     interest_on_arrears, template, customer_name, customer_additional_name, customer_contact_person,
                                     customer_street_name, customer_post_code, customer_city, customer_email, customer_vat, customer_phone)
                SELECT nextval('invoice_id_seq'), :company, c.id, :number, :reference, CAST(:created AS date), CAST(:created AS date) + 14,
                       'PENDING', 14, 18, 'finnish', c.name, c.additional_name, c.contact_person, c.street_name, c.post_code,
                       c.city, c.email, c.vat, c.phone
                FROM customer c WHERE c.id = :customer
                RETURNING id
                """
            )
                .bind("company", company).bind("customer", customer).bind("number", number)
                .bind("reference", reference).bind("created", created)
                .mapTo(Int::class.java).one()
            items.forEach { item ->
                handle.execute(
                    "INSERT INTO invoice_item (invoice, description, amount, price, tax) VALUES (?, ?, ?, ?, CAST(? AS numeric))",
                    id, item.description, item.amount, item.priceCents, item.tax
                )
            }
            id
        }
}
