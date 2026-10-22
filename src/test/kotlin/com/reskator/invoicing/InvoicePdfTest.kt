package com.reskator.invoicing

import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.Inputs
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.TestDirs
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.json
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertTrue

class InvoicePdfTest {
    private var companyA = 0
    private var companyB = 0

    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        Fixtures.user()
        companyA = Fixtures.company("Company A")
        companyB = Fixtures.company("Company B")
    }

    @Test
    fun `downloads the invoice as a pdf`() = appTest(TestDirs().withProjectTemplates()) {
        val client = sessionClient().login()
        client.post("/api/company/$companyA/invoice") { json(Inputs.json(Inputs.invoice())) }

        val response = client.get("/api/company/$companyA/invoice/lasku-1.pdf")

        assertEquals(HttpStatusCode.OK, response.status)
        assertEquals(ContentType.Application.Pdf, response.contentType())
        val pdf = response.readRawBytes()
        assertEquals("%PDF-", String(pdf, 0, 5))
        // One A4 page: 595 × 842 points
        assertTrue(Regex("/MediaBox \\[0 0 595(\\.\\d+)? 842(\\.\\d+)?]").containsMatchIn(String(pdf, Charsets.ISO_8859_1)))
    }

    /** B2 */
    @Test
    fun `another company's invoice is not downloaded or sent`() = appTest(TestDirs().withProjectTemplates()) {
        val client = sessionClient().login()
        client.post("/api/company/$companyB/invoice") { json(Inputs.json(Inputs.invoice())) }

        assertEquals(HttpStatusCode.NotFound, client.get("/api/company/$companyA/invoice/lasku-1.pdf").status)
        assertEquals(HttpStatusCode.NotFound, client.post("/api/company/$companyA/invoice/1/send-email").status)
    }

    @Test
    fun `sending with the mock mailer responds 200`() = appTest(TestDirs().withProjectTemplates()) {
        val client = sessionClient().login()
        client.post("/api/company/$companyA/invoice") { json(Inputs.json(Inputs.invoice())) }

        val response = client.post("/api/company/$companyA/invoice/1/send-email")

        assertEquals(HttpStatusCode.OK, response.status)
        assertEquals("", response.bodyAsText())
    }
}
