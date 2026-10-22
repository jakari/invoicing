package com.reskator.invoicing

import com.github.ajalt.clikt.core.main
import com.reskator.invoicing.command.Cli
import com.reskator.invoicing.config.Env
import com.reskator.invoicing.plugins.*
import io.ktor.server.application.*
import io.ktor.server.engine.*
import io.ktor.server.netty.*
import io.ktor.server.plugins.calllogging.*
import org.jdbi.v3.core.Jdbi

fun main(args: Array<String>) {
    if (args.isNotEmpty()) {
        Cli().main(args)
        return
    }

    val env = Env.load()
    embeddedServer(Netty, port = env.port, host = "0.0.0.0") { module(env) }
        .start(wait = true)
}

fun Application.module(env: Env, jdbi: Jdbi? = null) {
    env.checkFiles()
    install(CallLogging)
    install(SecurityHeaders)
    configureKoin(env, jdbi)
    configureSerialization()
    configureSecurity(env)
    configureStatusPages()
    configureRouting(env)
}
