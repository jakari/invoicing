package com.reskator.invoicing.db

import org.jdbi.v3.core.Handle
import org.jdbi.v3.core.Jdbi
import org.jdbi.v3.core.kotlin.mapTo
import org.jdbi.v3.core.kotlin.useTransactionUnchecked
import org.slf4j.LoggerFactory
import java.nio.file.FileSystemAlreadyExistsException
import java.nio.file.FileSystems
import java.nio.file.Path
import kotlin.io.path.listDirectoryEntries
import kotlin.io.path.name

/**
 * Installs the `migrations/version-up-<14 digit version>.sql` resources newer than the latest version in
 * `migration_versions`, all in one transaction (ported from verkkokauppa-ktor's MigrationService).
 */
class MigrationService(private val jdbi: Jdbi) {
    private val log = LoggerFactory.getLogger(MigrationService::class.java)

    fun migrate() {
        jdbi.useTransactionUnchecked {
            createVersionTableIfNeeded(it)
            val notInstalledVersions = getPendingVersions(getLatestVersion(it))
            installMigrations(it, notInstalledVersions)
        }
    }

    /** Same definition as in the existing databases: there is no executed_at column. */
    private fun createVersionTableIfNeeded(handle: Handle) {
        handle.execute("create table if not exists migration_versions(version varchar(255) not null primary key)")
    }

    /** Versions are 14 digits, so the varchar sorts like a number. */
    private fun getLatestVersion(handle: Handle) =
        handle.createQuery("SELECT version::bigint FROM migration_versions ORDER BY version DESC LIMIT 1")
            .mapTo<Long>()
            .findOne()
            .orElse(-1)

    private fun getPendingVersions(latestVersion: Long): List<MigrationVersion> {
        val regex = "^version-up-(\\d{14})\\.sql$".toRegex()
        return migrationFilenames()
            .mapNotNull { filename ->
                val version = regex.find(filename)?.groupValues?.get(1)?.toLong()?.let { MigrationVersion(filename, it) }
                if (version == null) {
                    log.warn("Unable extract version from filename $filename")
                }

                version
            }
            .filter { it.version > latestVersion }
            .sortedBy { it.version }
    }

    /** Lists the migration resources from the classes directory or, when running the fat jar, from inside the jar. */
    private fun migrationFilenames(): List<String> {
        val uri = javaClass.getResource("/$DIRECTORY")?.toURI() ?: return emptyList()
        if (uri.scheme != "jar") return Path.of(uri).listDirectoryEntries().map { it.name }

        val fileSystem = try {
            FileSystems.newFileSystem(uri, emptyMap<String, Any>())
        } catch (e: FileSystemAlreadyExistsException) {
            FileSystems.getFileSystem(uri)
        }
        return fileSystem.getPath("/$DIRECTORY").listDirectoryEntries().map { it.name }
    }

    private fun installMigrations(handle: Handle, migrations: List<MigrationVersion>) {
        if (migrations.isEmpty()) {
            log.info("Database up to date, no pending migrations")
        } else {
            log.info("Installing following versions: ${migrations.map { it.version }.joinToString()}")
            migrations.forEach { migration -> installMigration(handle, migration) }
        }
    }

    private fun installMigration(handle: Handle, migration: MigrationVersion) {
        @Suppress("SqlSourceToSinkFlow")
        handle.createScript(migration.getScript()).executeAsSeparateStatements()
        handle.execute("insert into migration_versions (version) values(?)", migration.version.toString())
    }

    companion object {
        const val DIRECTORY = "migrations"
    }
}

data class MigrationVersion(val filename: String, val version: Long) {
    fun getScript() = MigrationVersion::class.java
        .getResourceAsStream("/${MigrationService.DIRECTORY}/$filename")
        ?.bufferedReader()
        ?.use { it.readText() }
        ?: throw IllegalStateException("No resource $filename")
}
