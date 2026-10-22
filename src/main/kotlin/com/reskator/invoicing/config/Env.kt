package com.reskator.invoicing.config

import io.github.cdimascio.dotenv.Dotenv
import java.io.File
import java.net.URI
import java.net.URLDecoder

data class DbConfig(
    val host: String,
    val port: Int,
    val database: String,
    val username: String,
    val password: String,
    /** Extra JDBC connection options, e.g. `sslmode=require` */
    val options: String? = null
) {
    companion object {
        /** Parses a `postgres://user:password@host:port/database?options` URL, as Dokku's postgres plugin sets it. */
        fun fromUrl(url: String): DbConfig {
            val uri = runCatching { URI(url) }.getOrNull()
            require(uri != null && uri.scheme in listOf("postgres", "postgresql") && uri.host != null && uri.rawUserInfo != null) {
                "DATABASE_URL must look like postgres://user:password@host:port/database"
            }
            val (username, password) = uri.rawUserInfo.split(":", limit = 2).map { URLDecoder.decode(it, Charsets.UTF_8) }
                .let { it[0] to it.getOrElse(1) { "" } }
            val database = uri.rawPath.removePrefix("/").let { URLDecoder.decode(it, Charsets.UTF_8) }
            require(database.isNotEmpty()) { "DATABASE_URL is missing the database name" }

            return DbConfig(uri.host, uri.port.takeIf { it != -1 } ?: 5432, database, username, password, uri.rawQuery)
        }
    }
}

/** SMTP server the invoices are e-mailed through. */
data class MailerConfig(
    val host: String,
    val port: Int,
    val username: String,
    val password: String
)

/**
 * Typed application config from environment variables, with a local `.env` file as fallback (adapted from reskator's Env).
 */
data class Env(
    val environment: String,
    val port: Int,
    val appSecret: String,
    val db: DbConfig,
    /** Null when USE_MOCK_MAILER is set: invoices are then not e-mailed at all. */
    val mailer: MailerConfig?,
    /** The YAML file with the default invoice values, company and bank details, e-mail texts and templates. */
    val invoiceSettings: String,
    /** Directory of the invoice templates (`<template>.peb`) and the images they embed. */
    val invoiceTemplateDir: String,
    /** The built client (`npm run build`), served at `/`. */
    val webRoot: String
) {
    /** Fails at startup instead of on the first request when the deployment's settings or templates are missing. */
    fun checkFiles() {
        check(File(invoiceSettings).isFile) { "INVOICE_SETTINGS: no settings file at $invoiceSettings" }
        check(File(invoiceTemplateDir).isDirectory) { "INVOICE_TEMPLATE_DIR: no template directory at $invoiceTemplateDir" }
    }

    companion object {
        private val REQUIRED = listOf("APP_SECRET", "DATABASE_URL", "INVOICE_SETTINGS", "INVOICE_TEMPLATE_DIR")
        private val REQUIRED_FOR_SMTP = listOf("MAILER_HOST", "MAILER_USER", "MAILER_PASSWORD")

        fun load(dotenv: Dotenv = Dotenv.configure().ignoreIfMissing().load()): Env {
            fun value(key: String) = dotenv[key]?.takeIf { it.isNotBlank() }

            val useMockMailer = value("USE_MOCK_MAILER")?.lowercase() in listOf("true", "1", "yes")
            val missing = (REQUIRED + if (useMockMailer) emptyList() else REQUIRED_FOR_SMTP).filter { value(it) == null }
            check(missing.isEmpty()) { "Missing or empty environment variables: ${missing.joinToString()}" }

            fun get(key: String) = value(key)!!
            fun get(key: String, default: String) = value(key) ?: default

            return Env(
                environment = get("APP_ENV", "prod"),
                port = get("PORT", "8080").toInt(),
                appSecret = get("APP_SECRET"),
                db = DbConfig.fromUrl(get("DATABASE_URL")),
                mailer = if (useMockMailer) null else MailerConfig(
                    host = get("MAILER_HOST"),
                    port = get("MAILER_PORT", "587").toInt(),
                    username = get("MAILER_USER"),
                    password = get("MAILER_PASSWORD")
                ),
                invoiceSettings = get("INVOICE_SETTINGS"),
                invoiceTemplateDir = get("INVOICE_TEMPLATE_DIR"),
                webRoot = get("WEB_ROOT", "dist")
            )
        }
    }
}
