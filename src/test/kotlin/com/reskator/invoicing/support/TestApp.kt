package com.reskator.invoicing.support

import com.reskator.invoicing.config.Env
import com.reskator.invoicing.module
import io.ktor.client.*
import io.ktor.client.plugins.cookies.*
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.server.testing.*
import java.nio.file.Files
import java.nio.file.Path
import kotlin.io.path.copyTo
import kotlin.test.assertEquals

const val USERNAME = "tester"

/** A 1×1 transparent gif */
val GIF: ByteArray = java.util.Base64.getDecoder().decode("R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7")
const val PASSWORD = "secret-password"

/** The settings file and template directory of a test app. */
class TestDirs {
    val root: Path = Files.createTempDirectory("invoicing-test")
    val settings: Path = root.resolve("default-settings.yml").also {
        Path.of(TestDirs::class.java.getResource("/settings/default-settings.yml")!!.toURI()).copyTo(it)
    }
    val templates: Path = Files.createDirectories(root.resolve("templates"))

    /** The project's templates/ with stand-ins for the images they embed. */
    fun withProjectTemplates() = apply {
        for (name in listOf("finnish.peb", "english.peb")) Path.of("templates", name).copyTo(templates.resolve(name))
        for (name in listOf("aaa.gif", "aaa-eng.gif")) Files.write(templates.resolve(name), GIF)
    }
    val web: Path = Files.createDirectories(root.resolve("web"))
}

fun testEnv(dirs: TestDirs) = Env(
    environment = "test",
    port = 0,
    appSecret = "test-secret",
    db = TestDatabase.config,
    mailer = null,
    invoiceSettings = dirs.settings.toString(),
    invoiceTemplateDir = dirs.templates.toString(),
    webRoot = dirs.web.toString()
)

fun appTest(dirs: TestDirs = TestDirs(), block: suspend ApplicationTestBuilder.(TestDirs) -> Unit) = testApplication {
    application { module(testEnv(dirs), TestDatabase.jdbi) }
    block(dirs)
}

fun ApplicationTestBuilder.sessionClient(): HttpClient = createClient {
    install(HttpCookies)
    followRedirects = false
}

suspend fun HttpClient.login(username: String = USERNAME, password: String = PASSWORD): HttpClient = apply {
    val response = post("/api/login") { setBody("""{"username":"$username","password":"$password"}""") }
    assertEquals(HttpStatusCode.OK, response.status, response.bodyAsText())
}

/** Sends [json] as the body, like the client's jsonFetch does. */
fun HttpRequestBuilder.json(json: String) {
    contentType(ContentType.Application.Json)
    setBody(json)
}
