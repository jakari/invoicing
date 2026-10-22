package com.reskator.invoicing

import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.PASSWORD
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.TestDirs
import com.reskator.invoicing.support.testEnv
import com.reskator.invoicing.support.USERNAME
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import io.ktor.client.*
import io.ktor.client.plugins.cookies.*
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.server.testing.*
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals

class AuthTest {
    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        Fixtures.user()
    }

    @Test
    fun `api requires login with an empty 401`() = appTest {
        val response = client.get("/api/invoice/settings")

        assertEquals(HttpStatusCode.Unauthorized, response.status)
        assertEquals("", response.bodyAsText())
    }

    @Test
    fun `login with a wrong password or an unknown user fails`() = appTest {
        for (body in listOf("""{"username":"$USERNAME","password":"wrong"}""", """{"username":"nobody","password":"$PASSWORD"}""")) {
            val response = client.post("/api/login") { setBody(body) }
            assertEquals(HttpStatusCode.Unauthorized, response.status)
            assertEquals("""{"error":"Invalid credentials."}""", response.bodyAsText())
        }
    }

    @Test
    fun `login responds with the settings`() = appTest {
        Fixtures.company("Company A")
        Fixtures.company("Company B")

        val response = client.post("/api/login") { setBody("""{"username":"$USERNAME","password":"$PASSWORD"}""") }

        assertEquals(HttpStatusCode.OK, response.status)
        assertEquals(ContentType.Application.Json, response.contentType()?.withoutParameters())
        // The settings the client expects
        assertEquals(
            """{"default_values":{"default_due":14,"late_interest":18,"remarking_time":8,"initial_reference_number":100,"initial_invoice_number":1},"templates":[{"name":"finnish","title":"Suomi"},{"name":"english","title":"Englanti"}],"companies":[{"id":1,"name":"Company A"},{"id":2,"name":"Company B"}]}""",
            response.bodyAsText()
        )
    }

    @Test
    fun `users with 2y bcrypt hashes can log in`() = appTest {
        // The existing users' hashes have the $2y$ prefix; this one is of "legacy-password" with cost 4
        Fixtures.userWithHash("legacy-user", "\$2y\$04\$b9qDaJzkDQm/HLih9Y3FIegeMxCZLRrbT3XsgulwD52p.VCMBfk76")

        sessionClient().login("legacy-user", "legacy-password")
    }

    @Test
    fun `logout responds 204 and ends the session`() = appTest {
        val client = sessionClient().login()
        assertEquals(HttpStatusCode.OK, client.get("/api/invoice/settings").status)

        assertEquals(HttpStatusCode.NoContent, client.post("/api/logout").status)
        assertEquals(HttpStatusCode.Unauthorized, client.get("/api/invoice/settings").status)
    }

    @Test
    fun `logout revokes the session cookie`() = appTest {
        val client = sessionClient().login()
        val cookie = client.cookies("http://localhost/").single { it.name == "INVOICING_SESSION" }

        client.post("/api/logout")

        val reused = createClient {}.get("/api/invoice/settings") { header(HttpHeaders.Cookie, "${cookie.name}=${cookie.value}") }
        assertEquals(HttpStatusCode.Unauthorized, reused.status)
    }

    @Test
    fun `session cookie is http only and same site lax`() = appTest {
        val setCookie = client.post("/api/login") { setBody("""{"username":"$USERNAME","password":"$PASSWORD"}""") }
            .headers[HttpHeaders.SetCookie]!!

        assertContains(setCookie, "HttpOnly")
        assertContains(setCookie, "SameSite=Lax")
        assertContains(setCookie, "Max-Age=43200")
    }

    /** Dokku's nginx terminates TLS and forwards plain HTTP with X-Forwarded-Proto. */
    @Test
    fun `production sets a secure cookie behind a tls terminating proxy`() = testApplication {
        application { module(testEnv(TestDirs()).copy(environment = "prod"), TestDatabase.jdbi) }

        val response = client.post("/api/login") {
            header("X-Forwarded-Proto", "https")
            setBody("""{"username":"$USERNAME","password":"$PASSWORD"}""")
        }

        assertEquals(HttpStatusCode.OK, response.status)
        assertContains(response.headers[HttpHeaders.SetCookie]!!, "Secure")
    }

    @Test
    fun `invalid login json is a bad request`() = appTest {
        assertEquals(HttpStatusCode.BadRequest, client.post("/api/login") { setBody("{") }.status)
    }
}
