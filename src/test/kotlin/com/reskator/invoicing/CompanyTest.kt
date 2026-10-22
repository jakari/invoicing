package com.reskator.invoicing

import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.InvoiceFixtures
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertFalse

class CompanyTest {
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
    fun `selected company is reported in the settings`() = appTest {
        val client = sessionClient().login()

        val response = client.post("/api/select-company/$companyA")

        assertEquals(HttpStatusCode.NoContent, response.status)
        // Captured from the PHP app
        assertEquals(
            """{"default_values":{"default_due":14,"late_interest":18,"remarking_time":8,"initial_reference_number":100,"initial_invoice_number":1},"templates":[{"name":"finnish","title":"Suomi"},{"name":"english","title":"Englanti"}],"companies":[{"id":1,"name":"Company A"},{"id":2,"name":"Company B"}],"selected_company":{"id":1,"name":"Company A"}}""",
            client.get("/api/invoice/settings").bodyAsText()
        )
    }

    /** B9: the PHP app accepted any id and then failed every request with a 500. */
    @Test
    fun `selecting an unknown company is not found`() = appTest {
        val client = sessionClient().login()
        client.post("/api/select-company/$companyA")

        assertEquals(HttpStatusCode.NotFound, client.post("/api/select-company/999").status)
        assertContains(client.get("/api/invoice/settings").bodyAsText(), """"selected_company":{"id":1""")
    }

    @Test
    fun `selecting requires login`() = appTest {
        assertEquals(HttpStatusCode.Unauthorized, client.post("/api/select-company/$companyA").status)
    }

    @Test
    fun `company routes require login`() = appTest {
        assertEquals(HttpStatusCode.Unauthorized, client.get("/api/company/$companyA/customers").status, "existing")
        assertEquals(HttpStatusCode.Unauthorized, client.get("/api/company/999/customers").status, "unknown")
    }

    @Test
    fun `an unknown company in the url is not found`() = appTest {
        val client = sessionClient().login()

        val response = client.get("/api/company/999/customers")

        assertEquals(HttpStatusCode.NotFound, response.status)
        assertEquals("", response.bodyAsText())
    }

    /** An open tab keeps working on its own company whatever another tab selects. */
    @Test
    fun `the company in the url wins over the selected company`() = appTest {
        InvoiceFixtures.customer(companyA, "A Customer")
        InvoiceFixtures.customer(companyB, "B Customer")
        val client = sessionClient().login()
        client.post("/api/select-company/$companyB")

        val customers = client.get("/api/company/$companyA/customers").bodyAsText()

        assertContains(customers, "A Customer")
        assertFalse("B Customer" in customers)
    }
}
