package com.reskator.invoicing.support

import com.reskator.invoicing.config.DbConfig
import com.reskator.invoicing.db.MigrationService
import com.reskator.invoicing.db.createJdbi
import org.jdbi.v3.core.Jdbi
import org.testcontainers.containers.PostgreSQLContainer

/**
 * A Postgres container whose schema is created by the app's migrations. MigrationTest checks those
 * match the schema the Symfony app's Doctrine migrations created (src/test/resources/doctrine-schema.sql).
 */
object TestDatabase {
    private val container = PostgreSQLContainer("postgres:10-alpine").apply { start() }

    val config = DbConfig(container.host, container.firstMappedPort, container.databaseName, container.username, container.password)

    val jdbi: Jdbi by lazy {
        createJdbi(config).also { MigrationService(it).migrate() }
    }

    private var databases = 0

    /** A new, empty database in the same container. */
    fun createDatabase(): Jdbi {
        val name = "scratch_${++databases}"
        jdbi.useHandle<Exception> { it.execute("CREATE DATABASE $name") }
        return createJdbi(config.copy(database = name))
    }

    /** Loads the schema as the Symfony app's Doctrine migrations left it. */
    fun loadDoctrineSchema(jdbi: Jdbi) {
        val dump = TestDatabase::class.java.getResource("/doctrine-schema.sql")!!.readText()
        jdbi.useHandle<Exception> { handle ->
            dump.lines().filterNot { it.startsWith("--") }.joinToString("\n")
                .split(";\n")
                .map { it.trim() }
                .filter { it.isNotEmpty() && !it.startsWith("SELECT pg_catalog.set_config") && !it.startsWith("COMMENT ON EXTENSION") }
                .forEach { handle.execute(it) }
        }
    }

    /** Empties all tables. */
    fun reset() {
        jdbi.useHandle<Exception> {
            it.execute("TRUNCATE invoice_item, invoice, customer, parameters, company, users RESTART IDENTITY CASCADE")
            it.execute("ALTER SEQUENCE invoice_id_seq RESTART")
        }
    }
}
