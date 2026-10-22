package com.reskator.invoicing

import com.reskator.invoicing.db.MigrationService
import com.reskator.invoicing.support.TestDatabase
import org.jdbi.v3.core.Jdbi
import kotlin.test.Test
import kotlin.test.assertEquals

class MigrationTest {
    /** The Doctrine migrations under app/DoctrineMigrations, by version. */
    private val doctrineVersions = listOf(
        20180703190343, 20180722205127, 20200304090647, 20200310134819, 20200315201311,
        20200815201613, 20210212085350, 20240629203743, 20261022120000
    )

    /** Columns, indexes, constraints, sequences and types of the public schema. */
    private fun schema(jdbi: Jdbi): List<String> = jdbi.withHandle<List<String>, Exception> {
        fun query(sql: String) = it.createQuery(sql).mapTo(String::class.java).list()

        val columns = query(
            """
            SELECT table_name || '.' || column_name || ' ' || udt_name || ' ' ||
                   coalesce(character_maximum_length::text, '') || ' ' || coalesce(numeric_precision::text || ',' || numeric_scale::text, '') || ' ' ||
                   is_nullable || ' ' || coalesce(column_default, '')
            FROM information_schema.columns WHERE table_schema = 'public'
            """
        )
        val indexes = query("SELECT tablename || ': ' || indexdef FROM pg_indexes WHERE schemaname = 'public'")
        val constraints = query(
            """
            SELECT cl.relname || ': ' || co.conname || ' ' || pg_get_constraintdef(co.oid)
            FROM pg_constraint co JOIN pg_class cl ON cl.oid = co.conrelid
            JOIN pg_namespace n ON n.oid = cl.relnamespace WHERE n.nspname = 'public'
            """
        )
        val sequences = query("SELECT sequence_name || ' ' || data_type FROM information_schema.sequences WHERE sequence_schema = 'public'")
        val enums = query("SELECT t.typname || ' ' || e.enumlabel FROM pg_enum e JOIN pg_type t ON t.oid = e.enumtypid")
        val comments = query(
            "SELECT c.relname || ' ' || d.objsubid || ' ' || d.description FROM pg_description d JOIN pg_class c ON c.oid = d.objoid"
        )

        (columns + indexes + constraints + sequences + enums + comments).sorted()
    }

    private fun versions(jdbi: Jdbi) = jdbi.withHandle<List<Long>, Exception> {
        it.createQuery("SELECT version::bigint FROM migration_versions ORDER BY version").mapTo(Long::class.java).list()
    }

    @Test
    fun `migrations create the schema Doctrine created`() {
        val doctrine = TestDatabase.createDatabase().also { TestDatabase.loadDoctrineSchema(it) }
        val fresh = TestDatabase.createDatabase()

        MigrationService(fresh).migrate()
        MigrationService(fresh).migrate()

        assertEquals(schema(doctrine), schema(fresh))
        assertEquals(doctrineVersions, versions(fresh))
    }

    @Test
    fun `a database Doctrine migrated is up to date`() {
        val doctrine = TestDatabase.createDatabase().also { TestDatabase.loadDoctrineSchema(it) }
        doctrine.useHandle<Exception> { handle ->
            doctrineVersions.forEach { handle.execute("INSERT INTO migration_versions (version) VALUES (?)", it.toString()) }
        }
        val before = schema(doctrine)

        MigrationService(doctrine).migrate()

        assertEquals(before, schema(doctrine))
        assertEquals(doctrineVersions, versions(doctrine))
    }

    @Test
    fun `installs only the migrations Doctrine had not run`() {
        val doctrine = TestDatabase.createDatabase().also { TestDatabase.loadDoctrineSchema(it) }
        doctrine.useHandle<Exception> { handle ->
            handle.execute("DROP INDEX invoice_company_invoice_number")
            doctrineVersions.dropLast(1).forEach { handle.execute("INSERT INTO migration_versions (version) VALUES (?)", it.toString()) }
        }

        MigrationService(doctrine).migrate()

        assertEquals(schema(TestDatabase.createDatabase().also { MigrationService(it).migrate() }), schema(doctrine))
        assertEquals(doctrineVersions, versions(doctrine))
    }
}
