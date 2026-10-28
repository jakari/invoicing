package com.reskator.invoicing

import com.reskator.invoicing.support.TestDirs
import com.reskator.invoicing.support.appTest
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import kotlin.io.path.createDirectories
import kotlin.io.path.writeText
import kotlin.test.Test
import kotlin.test.assertEquals

/** The built client. */
class StaticContentTest {
    private fun dirs() = TestDirs().apply {
        web.resolve("index.html").writeText("<html>app</html>")
        web.resolve("assets").createDirectories().resolve("index.css").writeText("body {}")
    }

    @Test
    fun `serves the client`() = appTest(dirs()) {
        assertEquals("<html>app</html>", client.get("/").bodyAsText())
        assertEquals("body {}", client.get("/assets/index.css").bodyAsText())
    }

    @Test
    fun `client routes fall back to index html`() = appTest(dirs()) {
        val response = client.get("/invoice/1/edit")

        assertEquals(HttpStatusCode.OK, response.status)
        assertEquals("<html>app</html>", response.bodyAsText())
    }

    @Test
    fun `unknown api paths are an empty 404`() = appTest(dirs()) {
        val response = client.get("/api/nothing-here")

        assertEquals(HttpStatusCode.NotFound, response.status)
        assertEquals("", response.bodyAsText())
    }

    /** Cloudflare caches by file extension, keyed by URL only, unless told not to */
    @Test
    fun `api responses are not cached but the client may be`() = appTest(dirs()) {
        assertEquals("no-store", client.get("/api/nothing-here.css").headers[HttpHeaders.CacheControl])
        assertEquals(null, client.get("/assets/index.css").headers[HttpHeaders.CacheControl])
    }
}
