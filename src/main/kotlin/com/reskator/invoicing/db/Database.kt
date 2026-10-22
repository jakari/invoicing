package com.reskator.invoicing.db

import com.reskator.invoicing.config.DbConfig
import com.zaxxer.hikari.HikariConfig
import com.zaxxer.hikari.HikariDataSource
import org.jdbi.v3.core.Jdbi
import org.jdbi.v3.core.kotlin.KotlinPlugin
import org.jdbi.v3.postgres.PostgresPlugin

fun createJdbi(db: DbConfig): Jdbi {
    val config = HikariConfig().apply {
        jdbcUrl = "jdbc:postgresql://${db.host}:${db.port}/${db.database}" + (db.options?.let { "?$it" } ?: "")
        username = db.username
        password = db.password
        maximumPoolSize = 6
    }

    return Jdbi.create(HikariDataSource(config))
        .installPlugin(PostgresPlugin())
        .installPlugin(KotlinPlugin())
}
