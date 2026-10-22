package com.reskator.invoicing

import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.Inputs
import com.reskator.invoicing.support.Inputs.customer
import com.reskator.invoicing.support.Inputs.invoice
import com.reskator.invoicing.support.Inputs.item
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.json
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import io.ktor.client.*
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import kotlinx.coroutines.async
import kotlinx.coroutines.awaitAll
import kotlinx.coroutines.coroutineScope
import kotlinx.serialization.json.Json
import kotlinx.serialization.json.int
import kotlinx.serialization.json.jsonArray
import kotlinx.serialization.json.jsonObject
import kotlinx.serialization.json.jsonPrimitive
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertEquals

class InvoiceApiTest {
    private var companyA = 0
    private var companyB = 0

    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        Fixtures.user()
        companyA = Fixtures.company("Company A")
        companyB = Fixtures.company("Company B")
        for (company in listOf(companyA, companyB)) {
            Fixtures.parameter(company, "initial_invoice_number", "1")
            Fixtures.parameter(company, "initial_reference_number", "100")
        }
    }

    private suspend fun HttpClient.create(company: Int, body: Map<String, Any?> = invoice()): Int {
        val response = post("/api/company/$company/invoice") { json(Inputs.json(body)) }
        assertEquals(HttpStatusCode.OK, response.status, response.bodyAsText())
        return Json.parseToJsonElement(response.bodyAsText()).jsonObject["invoiceNumber"]!!.jsonPrimitive.int
    }

    /**
     * Company B's invoice 1 gets invoice.id 1 and company A's invoice 1 gets invoice.id 2,
     * so the two companies' invoice numbers overlap and id differs from invoice_number.
     */
    private suspend fun HttpClient.createOverlappingInvoices() {
        create(companyB, invoice("customer" to customer("name" to "B Customer"), "items" to listOf(item("B work", 10, 1000, 0))))
        create(
            companyA,
            invoice(
                "customer" to customer("name" to "A Customer"),
                "items" to listOf(item("A work", 1, 0.02, 25.5), item("A work", 1, 0.02, 25.5))
            )
        )
    }

    private fun query(sql: String): List<Map<String, Any?>> = TestDatabase.jdbi.withHandle<List<Map<String, Any?>>, Exception> {
        it.createQuery(sql).mapToMap().list()
    }

    @Test
    fun `creates an invoice and its customer`() = appTest {
        val client = sessionClient().login()

        assertEquals(1, client.create(companyA))

        val invoice = Json.parseToJsonElement(client.get("/api/company/$companyA/invoice/1").bodyAsText()).jsonObject
        assertEquals("Asiakas Oy", invoice["customer"]!!.jsonObject["name"]!!.jsonPrimitive.content)
        assertEquals("1009", invoice["referenceNumber"].toString())
        assertEquals("100", invoice["items"]!!.jsonArray[0].jsonObject["price"].toString())
    }

    @Test
    fun `responses have the json the client expects`() = appTest {
        val client = sessionClient().login()

        val created = client.post("/api/company/$companyA/invoice") {
            json(
                Inputs.json(
                    invoice(
                        "customer" to customer("vat" to null, "phone" to null),
                        "items" to listOf(item("Työ", 3, 12.34, 25.5), item("Tarvike", 1, 100, "24"))
                    )
                )
            )
        }
        client.create(
            companyA,
            invoice(
                "customer" to customer("name" to "Toinen Oy", "vat" to "FI123", "phone" to "040"),
                "customerReference" to "Viite", "delivery" to "Nouto", "conditionsOfPayment" to "14 pv netto"
            )
        )

        assertEquals("""{"invoiceNumber":1}""", created.bodyAsText())
        assertEquals(
            """{"created":"2026-01-01","due":"2026-01-15","customer":{"id":1,"name":"Asiakas Oy","additionalName":"","contactPerson":"","streetName":"Katu 1","postCode":"00100","city":"Helsinki","email":"asiakas@example.local"},"invoiceNumber":1,"referenceNumber":1009,"items":[{"id":1,"description":"Työ","amount":3,"price":12.34,"tax":"25.50"},{"id":2,"description":"Tarvike","amount":1,"price":100,"tax":"24.00"}],"remarkingTime":14,"interestOnArrears":18,"template":"finnish"}""",
            client.get("/api/company/$companyA/invoice/1").bodyAsText()
        )
        assertEquals(
            """{"created":"2026-01-01","due":"2026-01-15","customer":{"id":2,"name":"Toinen Oy","additionalName":"","contactPerson":"","streetName":"Katu 1","postCode":"00100","city":"Helsinki","email":"asiakas@example.local","vat":"FI123","phone":"040"},"invoiceNumber":2,"referenceNumber":1012,"items":[{"id":3,"description":"Työ","amount":1,"price":100,"tax":"25.50"}],"remarkingTime":14,"interestOnArrears":18,"customerReference":"Viite","delivery":"Nouto","conditionsOfPayment":"14 pv netto","template":"finnish"}""",
            client.get("/api/company/$companyA/invoice/2").bodyAsText()
        )
        assertEquals(
            """[{"customer":"Toinen Oy","invoiceNumber":2,"referenceNumber":1012,"created":"01.01.2026","due":"15.01.2026","total":125.5},{"customer":"Asiakas Oy","invoiceNumber":1,"referenceNumber":1009,"created":"01.01.2026","due":"15.01.2026","total":170.46}]""",
            client.get("/api/company/$companyA/invoices").bodyAsText()
        )
        val patched = client.patch("/api/company/$companyA/invoice/1") { json(Inputs.json(invoice())) }
        assertEquals(HttpStatusCode.NoContent, patched.status)
        val deleted = client.delete("/api/company/$companyA/invoice/1")
        assertEquals(HttpStatusCode.OK, deleted.status)
        assertEquals("", deleted.bodyAsText())
    }

    /**
     * B1: the list joined items on invoice_number instead of invoice.id.
     * B7: the list total rounds each item's tax like the printed invoice: 2 × (2 + round(0.51)) = 6 cents.
     */
    @Test
    fun `lists totals of the invoice's own items`() = appTest {
        val client = sessionClient().login()
        client.createOverlappingInvoices()

        assertEquals(
            """[{"customer":"A Customer","invoiceNumber":1,"referenceNumber":1009,"created":"01.01.2026","due":"15.01.2026","total":0.06}]""",
            client.get("/api/company/$companyA/invoices").bodyAsText()
        )
    }

    @Test
    fun `lists the newest invoices first`() = appTest {
        val client = sessionClient().login()
        client.create(companyA, invoice("created" to "2026-01-01"))
        client.create(companyA, invoice("created" to "2026-02-01"))
        client.create(companyA, invoice("created" to "2026-01-01"))

        val numbers = Json.parseToJsonElement(client.get("/api/company/$companyA/invoices").bodyAsText()).jsonArray
            .map { it.jsonObject["invoiceNumber"]!!.jsonPrimitive.int }

        assertEquals(listOf(2, 3, 1), numbers)
    }

    /** B2: invoices were looked up by number alone, and numbers overlap between companies. */
    @Test
    fun `gets the invoice of the company in the url`() = appTest {
        val client = sessionClient().login()
        client.createOverlappingInvoices()

        val invoice = Json.parseToJsonElement(client.get("/api/company/$companyA/invoice/1").bodyAsText()).jsonObject

        assertEquals("A Customer", invoice["customer"]!!.jsonObject["name"]!!.jsonPrimitive.content)
    }

    /** B2 */
    @Test
    fun `updates only the invoice of the company in the url`() = appTest {
        val client = sessionClient().login()
        client.createOverlappingInvoices()

        val response = client.patch("/api/company/$companyA/invoice/1") {
            json(Inputs.json(invoice("customer" to customer("id" to 2, "name" to "A Customer"), "delivery" to "Updated")))
        }

        assertEquals(HttpStatusCode.NoContent, response.status, response.bodyAsText())
        assertEquals(
            listOf(mapOf("company" to companyA, "delivery" to "Updated"), mapOf("company" to companyB, "delivery" to null)),
            query("SELECT company, delivery FROM invoice ORDER BY company")
        )
    }

    /** B2 */
    @Test
    fun `another company's invoice is not found`() = appTest {
        val client = sessionClient().login()
        client.create(companyB)

        assertEquals(HttpStatusCode.NotFound, client.get("/api/company/$companyA/invoice/1").status)
        assertEquals(HttpStatusCode.NotFound, client.patch("/api/company/$companyA/invoice/1") { json(Inputs.json(invoice())) }.status)
        assertEquals(HttpStatusCode.NotFound, client.delete("/api/company/$companyA/invoice/1").status)
        assertEquals(1, query("SELECT id FROM invoice").size)
    }

    /** B3: deleting an invoice with items failed, and would have deleted every company's invoice with that number. */
    @Test
    fun `deletes the invoice with its items only from the company in the url`() = appTest {
        val client = sessionClient().login()
        client.createOverlappingInvoices()

        assertEquals(HttpStatusCode.OK, client.delete("/api/company/$companyA/invoice/1").status)

        assertEquals(listOf(mapOf("company" to companyB)), query("SELECT company FROM invoice"))
        assertEquals(1, query("SELECT id FROM invoice_item").size)
    }

    /** B4: an invoice could be saved with another company's customer. */
    @Test
    fun `another company's customer cannot be used on an invoice`() = appTest {
        val client = sessionClient().login()
        client.create(companyB, invoice("customer" to customer("name" to "B Customer")))
        val customerB = query("SELECT id FROM customer").single()["id"] as Int

        val response = client.post("/api/company/$companyA/invoice") {
            json(Inputs.json(invoice("customer" to customer("id" to customerB, "name" to "Hijacked"))))
        }

        assertEquals(HttpStatusCode.NotFound, response.status)
        assertEquals(listOf(mapOf("name" to "B Customer")), query("SELECT name FROM customer"))
        assertEquals(1, query("SELECT id FROM invoice").size)
    }

    /** B6: concurrent invoices got the same number. */
    @Test
    fun `concurrent invoices get their own numbers`() = appTest {
        val client = sessionClient().login()

        val numbers = coroutineScope { (1..6).map { async { client.create(companyA) } }.awaitAll() }

        assertEquals((1..6).toList(), numbers.sorted())
        assertEquals(6, query("SELECT DISTINCT reference_number FROM invoice").size)
    }

    /** B8: a company without parameters failed with a 500. */
    @Test
    fun `uses the default settings without parameters`() = appTest {
        TestDatabase.jdbi.useHandle<Exception> { it.execute("DELETE FROM parameters") }
        val client = sessionClient().login()

        assertEquals(1, client.create(companyA))
        assertEquals(listOf(mapOf("reference_number" to 1009)), query("SELECT reference_number FROM invoice"))
    }

    @Test
    fun `numbers continue from the company's latest invoice`() = appTest {
        Fixtures.parameter(companyA, "unrelated", "x")
        TestDatabase.jdbi.useHandle<Exception> {
            it.execute("UPDATE parameters SET value = '41' WHERE company = ? AND key = 'initial_invoice_number'", companyA)
            it.execute("UPDATE parameters SET value = '500' WHERE company = ? AND key = 'initial_reference_number'", companyA)
        }
        val client = sessionClient().login()

        assertEquals(41, client.create(companyA))
        assertEquals(42, client.create(companyA))
        assertEquals(1, client.create(companyB))
        // 500 → 5005, then 501 → 5018
        assertEquals(
            listOf(mapOf("invoice_number" to 41, "reference_number" to 5005), mapOf("invoice_number" to 42, "reference_number" to 5018)),
            query("SELECT invoice_number, reference_number FROM invoice WHERE company = $companyA ORDER BY id")
        )
    }

    @Test
    fun `saving updates, adds and removes items`() = appTest {
        val client = sessionClient().login()
        client.create(companyA, invoice("items" to listOf(item("Keep"), item("Remove"))))

        client.patch("/api/company/$companyA/invoice/1") {
            json(Inputs.json(invoice("items" to listOf(item("Kept", amount = 2, price = 5, tax = "24", id = 1), item("New")))))
        }

        assertEquals(
            listOf(
                mapOf("id" to 1, "description" to "Kept", "amount" to 2, "price" to 500),
                mapOf("id" to 3, "description" to "New", "amount" to 1, "price" to 10000)
            ),
            query("SELECT id, description, amount, price FROM invoice_item ORDER BY id")
        )
    }

    @Test
    fun `saving keeps the customer details on the invoice up to date`() = appTest {
        val client = sessionClient().login()
        client.create(companyA)

        client.patch("/api/company/$companyA/invoice/1") {
            json(Inputs.json(invoice("customer" to customer("id" to 1, "name" to "Renamed Oy", "city" to "Espoo"))))
        }

        assertEquals(
            listOf(mapOf("customer_name" to "Renamed Oy", "customer_city" to "Espoo")),
            query("SELECT customer_name, customer_city FROM invoice")
        )
        assertEquals(listOf(mapOf("name" to "Renamed Oy")), query("SELECT name FROM customer"))
    }

    @Test
    fun `an item of another invoice cannot be changed`() = appTest {
        val client = sessionClient().login()
        client.create(companyB, invoice("items" to listOf(item("B item"))))
        client.create(companyA)

        val response = client.patch("/api/company/$companyA/invoice/1") {
            json(Inputs.json(invoice("items" to listOf(item("Hijacked", id = 1)))))
        }

        assertEquals(HttpStatusCode.NotFound, response.status)
        assertEquals(listOf(mapOf("description" to "B item")), query("SELECT description FROM invoice_item WHERE id = 1"))
    }

    @Test
    fun `rejects an invalid invoice with all violations`() = appTest {
        val client = sessionClient().login()

        val response = client.post("/api/company/$companyA/invoice") {
            json(
                Inputs.json(
                    invoice(
                        "items" to emptyList<Any>(), "remarkingTime" to 400, "template" to "../secret",
                        "customer" to customer("name" to "", "city" to null)
                    )
                )
            )
        }

        assertEquals(HttpStatusCode.BadRequest, response.status)
        assertEquals(
            """{"violations":[{"property":"customer.name","message":"This value should not be blank."},{"property":"customer.city","message":"This value should not be blank."},{"property":"items","message":"This collection should contain 1 element or more."},{"property":"remarkingTime","message":"This value should be 360 or less."},{"property":"template","message":"This value is not valid."}]}""",
            response.bodyAsText()
        )
        assertEquals(0, query("SELECT id FROM invoice").size)
    }

    @Test
    fun `rejects an empty invoice`() = appTest {
        val client = sessionClient().login()

        val response = client.post("/api/company/$companyA/invoice") { json("{}") }

        assertEquals(HttpStatusCode.BadRequest, response.status)
        assertEquals(
            """{"violations":[{"property":"created","message":"This value should not be blank."},{"property":"due","message":"This value should not be blank."},{"property":"customer","message":"This value should not be blank."},{"property":"items","message":"This value should not be blank."},{"property":"remarkingTime","message":"This value should not be blank."},{"property":"interestOnArrears","message":"This value should not be blank."},{"property":"template","message":"This value should not be blank."}]}""",
            response.bodyAsText()
        )
    }

    @Test
    fun `rejects invalid items`() = appTest {
        val client = sessionClient().login()

        val response = client.post("/api/company/$companyA/invoice") {
            json(Inputs.json(invoice("items" to listOf(mapOf("description" to " ", "amount" to 1, "price" to 1, "tax" to 100)))))
        }

        assertEquals(
            """{"violations":[{"property":"items[0].description","message":"This value should not be blank."},{"property":"items[0].tax","message":"This value should be 99.99 or less."}]}""",
            response.bodyAsText()
        )
    }

    /** The client sends prices and taxes as strings, and amounts as numbers. */
    @Test
    fun `accepts prices and taxes as strings`() = appTest {
        val client = sessionClient().login()

        client.create(companyA, invoice("items" to listOf(item("Työ", 3, "12.345", "25.5"))))

        assertEquals(
            listOf(mapOf("amount" to 3, "price" to 1235, "tax" to java.math.BigDecimal("25.50"))),
            query("SELECT amount, price, tax FROM invoice_item")
        )
    }

    @Test
    fun `invalid json is a bad request`() = appTest {
        val client = sessionClient().login()

        assertEquals(HttpStatusCode.BadRequest, client.post("/api/company/$companyA/invoice") { json("{") }.status)
        assertEquals(HttpStatusCode.BadRequest, client.post("/api/company/$companyA/invoice") { json(Inputs.json(invoice("created" to "1.1.2026"))) }.status)
    }
}
