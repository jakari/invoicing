package com.reskator.invoicing

import com.reskator.invoicing.config.DbConfig
import com.reskator.invoicing.config.Env
import io.github.cdimascio.dotenv.Dotenv
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertFailsWith
import kotlin.test.assertNull

class EnvTest {
    private fun dotenv(vararg values: Pair<String, String>): Dotenv {
        val dir = kotlin.io.path.createTempDirectory("env")
        dir.resolve(".env").toFile().writeText(values.joinToString("\n") { (k, v) -> "$k=$v" })
        return Dotenv.configure().directory(dir.toString()).load()
    }

    private val required = arrayOf(
        "APP_SECRET" to "secret",
        "DATABASE_URL" to "postgres://u:p@db:5432/invoicing",
        "INVOICE_SETTINGS" to "/storage/default-settings.yml",
        "INVOICE_TEMPLATE_DIR" to "/storage/templates",
        "USE_MOCK_MAILER" to "true"
    )

    @Test
    fun `parses dokku style database url`() {
        assertEquals(
            DbConfig("dokku-postgres-invoicing", 5432, "invoicing", "postgres", "s3cr3t"),
            DbConfig.fromUrl("postgres://postgres:s3cr3t@dokku-postgres-invoicing:5432/invoicing")
        )
    }

    @Test
    fun `decodes credentials and keeps options`() {
        assertEquals(
            DbConfig("db.example.local", 5432, "app", "user@x", "p@ss:w/rd", "sslmode=require"),
            DbConfig.fromUrl("postgresql://user%40x:p%40ss%3Aw%2Frd@db.example.local/app?sslmode=require")
        )
    }

    @Test
    fun `rejects invalid urls`() {
        for (url in listOf("localhost:5432/app", "mysql://u:p@h/app", "postgres://u:p@h:5432/", "postgres://h/app")) {
            assertFailsWith<IllegalArgumentException>(url) { DbConfig.fromUrl(url) }
        }
    }

    @Test
    fun `reports all missing variables at once`() {
        val error = assertFailsWith<IllegalStateException> {
            Env.load(Dotenv.configure().directory("/nonexistent").ignoreIfMissing().load())
        }
        assertContains(error.message!!, "DATABASE_URL")
        assertContains(error.message!!, "APP_SECRET")
        assertContains(error.message!!, "INVOICE_TEMPLATE_DIR")
    }

    @Test
    fun `mock mailer needs no smtp settings`() {
        val env = Env.load(dotenv(*required))

        assertNull(env.mailer)
        assertEquals("/storage/templates", env.invoiceTemplateDir)
        assertEquals(8080, env.port)
    }

    @Test
    fun `smtp settings are required without the mock mailer`() {
        val error = assertFailsWith<IllegalStateException> {
            Env.load(dotenv(*required.filter { it.first != "USE_MOCK_MAILER" }.toTypedArray()))
        }
        assertContains(error.message!!, "MAILER_HOST")
    }

    @Test
    fun `reads smtp settings`() {
        val env = Env.load(
            dotenv(
                *required.filter { it.first != "USE_MOCK_MAILER" }.toTypedArray(),
                "MAILER_HOST" to "smtp.mail.local", "MAILER_USER" to "laskut@example.local", "MAILER_PASSWORD" to "pw"
            )
        )

        assertEquals(587, env.mailer!!.port)
        assertEquals("smtp.mail.local", env.mailer!!.host)
    }
}
