package com.reskator.invoicing

import ch.qos.logback.classic.Level
import ch.qos.logback.classic.Logger
import ch.qos.logback.classic.spi.ILoggingEvent
import ch.qos.logback.core.read.ListAppender
import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.TestDatabase
import com.reskator.invoicing.support.TestDirs
import com.reskator.invoicing.support.appTest
import com.reskator.invoicing.support.login
import com.reskator.invoicing.support.sessionClient
import com.reskator.invoicing.support.testEnv
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.server.testing.*
import org.slf4j.LoggerFactory
import java.io.FileNotFoundException
import kotlin.io.path.deleteExisting
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertFailsWith
import kotlin.test.assertIs

class StartupTest {
    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        Fixtures.user()
    }

    private fun start(dirs: TestDirs) = testApplication {
        application { module(testEnv(dirs), TestDatabase.jdbi) }
        startApplication()
    }

    /** Dokku keeps the previous release running when the new one fails to start. */
    @Test
    fun `does not start without the settings file`() {
        val dirs = TestDirs().apply { settings.deleteExisting() }

        val error = assertFailsWith<IllegalStateException> { start(dirs) }

        assertContains(error.message!!, "INVOICE_SETTINGS")
        assertContains(error.message!!, dirs.settings.toString())
    }

    @Test
    fun `does not start without the template directory`() {
        val dirs = TestDirs().apply { templates.deleteExisting() }

        val error = assertFailsWith<IllegalStateException> { start(dirs) }

        assertContains(error.message!!, "INVOICE_TEMPLATE_DIR")
        assertContains(error.message!!, dirs.templates.toString())
    }

    @Test
    fun `unexpected errors are logged with their stack trace`() {
        val appender = ListAppender<ILoggingEvent>().apply { start() }
        // The application log is io.ktor.server.Application in production but another logger in tests
        val logger = LoggerFactory.getLogger(Logger.ROOT_LOGGER_NAME) as Logger
        logger.addAppender(appender)
        try {
            appTest { dirs ->
                val client = sessionClient().login()
                dirs.settings.deleteExisting()

                val response = client.get("/api/invoice/settings")

                assertEquals(HttpStatusCode.InternalServerError, response.status)
                assertEquals("", response.bodyAsText())
                val error = appender.list.single { it.level == Level.ERROR }
                assertContains(error.formattedMessage, "GET /api/invoice/settings")
                assertEquals(FileNotFoundException::class.java.name, error.throwableProxy.className)
            }
        } finally {
            logger.detachAppender(appender)
        }
    }
}
