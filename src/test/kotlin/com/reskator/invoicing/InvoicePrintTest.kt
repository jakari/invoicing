package com.reskator.invoicing

import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.Inputs
import com.reskator.invoicing.support.Inputs.customer
import com.reskator.invoicing.support.Inputs.invoice
import com.reskator.invoicing.support.Inputs.item
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.TestDirs
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.json
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import io.ktor.client.*
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertFalse

class InvoicePrintTest {
    private var companyA = 0
    private var companyB = 0

    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        Fixtures.user()
        companyA = Fixtures.company("Company A")
        companyB = Fixtures.company("Company B")
    }

    private fun printTest(block: suspend io.ktor.server.testing.ApplicationTestBuilder.(HttpClient) -> Unit) =
        appTest(TestDirs().withProjectTemplates()) { block(sessionClient().login()) }

    private suspend fun HttpClient.create(company: Int, body: Map<String, Any?> = invoice()) {
        val response = post("/api/company/$company/invoice") { json(Inputs.json(body)) }
        assertEquals(HttpStatusCode.OK, response.status, response.bodyAsText())
    }

    private suspend fun HttpClient.print(company: Int, number: Int = 1) =
        get("/api/company/$company/invoice/$number/print_html").bodyAsText()

    private fun useTemplate(template: String) = TestDatabase.jdbi.useHandle<Exception> {
        it.execute("UPDATE invoice SET template = ?", template)
    }

    @Test
    fun `prints the invoice as html`() = printTest { client ->
        client.create(companyA)

        val response = client.get("/api/company/$companyA/invoice/1/print_html")

        assertEquals(HttpStatusCode.OK, response.status)
        assertEquals(ContentType.Text.Html, response.contentType()?.withoutParameters())
        assertContains(response.bodyAsText(), "Asiakas Oy")
    }

    /** The output the PHP app's Twig templates gave. */
    @Test
    fun `renders the templates like the php app did`() = printTest { client ->
        client.create(
            companyA,
            invoice(
                "items" to listOf(item("Työ", 3, 12.34, 25.5), item("Tarvike", 1, 1000, "24")),
                "customerReference" to "Tilaus 7"
            )
        )

        val html = client.print(companyA)

        // Dates and the payment term
        assertContains(html, "<td>01.01.2026</td>")
        assertContains(html, "<td>15.01.2026</td>")
        assertContains(html, "<td>14 pv</td>")
        // numberformat("0.00"), money and the totals filters
        assertContains(html, """<td class="tax">25,50</td>""")
        assertContains(html, """<td class="tax">24,00</td>""")
        assertContains(html, """<td class="price">12,34</td>""")
        assertContains(html, """<td class="price">37,02</td>""")
        assertContains(html, """<td class="price">1 000,00</td>""")
        assertContains(html, """<td class="total">1 037,02 €</td>""")
        assertContains(html, """<td class="total">249,44 €</td>""")
        assertContains(html, """<td class="total">1 286,46 €</td>""")
        // The settings file and the invoice
        assertContains(html, "<div>Firma Oy</div>")
        assertContains(html, "IBAN: FI00 1234 5600 0007 85")
        assertContains(html, "<td>Tilaus 7</td>")
        assertContains(html, "1009")
        // base64image embeds the image from the template directory
        assertContains(html, """<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" />""")
    }

    @Test
    fun `optional customer details are left out when empty`() = printTest { client ->
        client.create(companyA, invoice("customer" to customer("additionalName" to "", "vat" to "")))
        assertFalse("""<div class="customer-additional-name">""" in client.print(companyA))
        assertFalse("""<div class="customer-vat">""" in client.print(companyA))

        client.create(companyA, invoice("customer" to customer("additionalName" to "Osasto 2", "vat" to "FI123")))
        assertContains(client.print(companyA, 2), """<div class="customer-additional-name">Osasto 2</div>""")
        assertContains(client.print(companyA, 2), """<div class="customer-vat">FI123</div>""")
    }

    @Test
    fun `escapes html`() = printTest { client ->
        client.create(companyA, invoice("customer" to customer("name" to "Smith & <Sons>")))

        val html = client.print(companyA)

        assertContains(html, "Smith &amp; &lt;Sons&gt;")
        assertFalse("<Sons>" in html)
    }

    /** B5: the bank transfer slip printed the customer's current details instead of the ones saved on the invoice. */
    @Test
    fun `prints the customer details saved on the invoice`() = printTest { client ->
        client.create(companyA)
        client.patch("/api/company/$companyA/customer/1") {
            json(Inputs.json(customer("id" to 1, "name" to "Renamed Oy", "streetName" to "Uusi katu 2")))
        }

        for (template in listOf("finnish", "english")) {
            useTemplate(template)
            val html = client.print(companyA)

            assertContains(html, "Asiakas Oy", message = template)
            assertFalse("Renamed Oy" in html, template)
            assertFalse("Uusi katu 2" in html, template)
        }
    }

    /** B2 */
    @Test
    fun `prints the invoice of the company in the url`() = printTest { client ->
        client.create(companyB, invoice("customer" to customer("name" to "B Customer")))
        client.create(companyA, invoice("customer" to customer("name" to "A Customer")))

        val html = client.print(companyA)

        assertContains(html, "A Customer")
        assertFalse("B Customer" in html)
    }

    /** B2 */
    @Test
    fun `another company's invoice is not printed`() = printTest { client ->
        client.create(companyB)

        assertEquals(HttpStatusCode.NotFound, client.get("/api/company/$companyA/invoice/1/print_html").status)
    }
}
