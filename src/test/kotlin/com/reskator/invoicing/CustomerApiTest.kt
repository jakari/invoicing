package com.reskator.invoicing

import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.InvoiceFixtures
import com.reskator.invoicing.support.InvoiceFixtures.Item
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.json
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals

class CustomerApiTest {
    private var companyA = 0
    private var companyB = 0

    private fun customerJson(id: Int, name: String, extra: String = "") =
        """{"id":$id,"name":"$name","additionalName":"","contactPerson":"","streetName":"Uusi katu 2","postCode":"00200","city":"Espoo","email":"uusi@example.local"$extra}"""

    private fun customerName(id: Int) = TestDatabase.jdbi.withHandle<String, Exception> {
        it.createQuery("SELECT name FROM customer WHERE id = :id").bind("id", id).mapTo(String::class.java).one()
    }

    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        Fixtures.user()
        companyA = Fixtures.company("Company A")
        companyB = Fixtures.company("Company B")
    }

    @Test
    fun `lists the company's customers by name`() = appTest {
        InvoiceFixtures.customer(companyA, "Toinen Oy", vat = "FI123", phone = "040")
        InvoiceFixtures.customer(companyB, "B Customer")
        InvoiceFixtures.customer(companyA, "Asiakas Oy")
        val client = sessionClient().login()

        val response = client.get("/api/company/$companyA/customers")

        assertEquals(HttpStatusCode.OK, response.status)
        // Nulls are left out
        assertEquals(
            """[{"id":3,"name":"Asiakas Oy","additionalName":"","contactPerson":"","streetName":"Katu 1","postCode":"00100","city":"Helsinki","email":"asiakas@example.local"},{"id":1,"name":"Toinen Oy","additionalName":"","contactPerson":"","streetName":"Katu 1","postCode":"00100","city":"Helsinki","email":"asiakas@example.local","vat":"FI123","phone":"040"}]""",
            response.bodyAsText()
        )
        assertEquals("no-store", response.headers[HttpHeaders.CacheControl])
    }

    @Test
    fun `searches customers by part of the name ignoring case`() = appTest {
        InvoiceFixtures.customer(companyA, "Asiakas Oy")
        InvoiceFixtures.customer(companyA, "Toinen Oy", vat = "FI123", phone = "040")
        InvoiceFixtures.customer(companyB, "Toinen B")
        val client = sessionClient().login()

        val response = client.get("/api/company/$companyA/customers/toi")

        assertEquals(
            """[{"id":2,"name":"Toinen Oy","additionalName":"","contactPerson":"","streetName":"Katu 1","postCode":"00100","city":"Helsinki","email":"asiakas@example.local","vat":"FI123","phone":"040"}]""",
            response.bodyAsText()
        )
    }

    @Test
    fun `search without matches is an empty 404`() = appTest {
        InvoiceFixtures.customer(companyA, "Asiakas Oy")
        val client = sessionClient().login()

        val response = client.get("/api/company/$companyA/customers/zzz")

        assertEquals(HttpStatusCode.NotFound, response.status)
        assertEquals("", response.bodyAsText())
    }

    @Test
    fun `gets a customer with totals without tax of its invoices`() = appTest {
        val customer = InvoiceFixtures.customer(companyA, "Asiakas Oy")
        InvoiceFixtures.invoice(companyA, customer, 1, 1009, listOf(Item("Työ", 3, 1234, "25.50"), Item("Tarvike", 1, 10000, "24.00")))
        val client = sessionClient().login()

        val response = client.get("/api/company/$companyA/customer/$customer")

        // Totals without tax
        assertEquals(
            """{"id":1,"name":"Asiakas Oy","additionalName":"","contactPerson":"","streetName":"Katu 1","postCode":"00100","city":"Helsinki","email":"asiakas@example.local","invoices":[{"customer":"Asiakas Oy","invoiceNumber":1,"referenceNumber":1009,"created":"01.01.2026","due":"15.01.2026","total":137.02}]}""",
            response.bodyAsText()
        )
    }

    /** B4: customers were fetched and updated by id with no company check. */
    @Test
    fun `another company's customer is not found`() = appTest {
        val customerB = InvoiceFixtures.customer(companyB, "B Customer")
        val client = sessionClient().login()

        assertEquals(HttpStatusCode.NotFound, client.get("/api/company/$companyA/customer/$customerB").status)
        val patch = client.patch("/api/company/$companyA/customer/$customerB") { json(customerJson(customerB, "Hijacked")) }
        assertEquals(HttpStatusCode.NotFound, patch.status)
        assertEquals("B Customer", customerName(customerB))
    }

    @Test
    fun `updates the customer`() = appTest {
        val customer = InvoiceFixtures.customer(companyA, "Asiakas Oy")
        val client = sessionClient().login()

        val response = client.patch("/api/company/$companyA/customer/$customer") {
            json(customerJson(customer, "Uusi Oy", ""","vat":"FI999","phone":null"""))
        }

        assertEquals(HttpStatusCode.NoContent, response.status)
        assertEquals(
            customerJson(customer, "Uusi Oy", ""","vat":"FI999""""),
            client.get("/api/company/$companyA/customers").bodyAsText().removeSurrounding("[", "]")
        )
    }

    @Test
    fun `updates the customer in the url, not the id in the body`() = appTest {
        val first = InvoiceFixtures.customer(companyA, "First")
        val second = InvoiceFixtures.customer(companyA, "Second")
        val client = sessionClient().login()

        client.patch("/api/company/$companyA/customer/$first") { json(customerJson(second, "Renamed")) }

        assertEquals("Renamed", customerName(first))
        assertEquals("Second", customerName(second))
    }

    @Test
    fun `rejects an invalid customer`() = appTest {
        val customer = InvoiceFixtures.customer(companyA, "Asiakas Oy")
        val client = sessionClient().login()

        val response = client.patch("/api/company/$companyA/customer/$customer") {
            json("""{"name":" ","additionalName":"","contactPerson":"","streetName":"Katu","postCode":"12345678901","city":"Espoo"}""")
        }

        assertEquals(HttpStatusCode.BadRequest, response.status)
        assertEquals(
            """{"violations":[{"property":"name","message":"This value should not be blank."},{"property":"postCode","message":"This value is too long. It should have 10 characters or less."},{"property":"email","message":"This value should not be null."}]}""",
            response.bodyAsText()
        )
        assertEquals("Asiakas Oy", customerName(customer))
    }
}
