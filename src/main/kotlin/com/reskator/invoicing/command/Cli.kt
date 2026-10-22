package com.reskator.invoicing.command

import com.github.ajalt.clikt.core.CliktCommand
import com.github.ajalt.clikt.core.PrintMessage
import com.github.ajalt.clikt.core.subcommands
import com.github.ajalt.clikt.parameters.options.option
import com.github.ajalt.clikt.parameters.options.prompt
import com.reskator.invoicing.auth.AuthService
import com.reskator.invoicing.auth.UserRepository
import com.reskator.invoicing.config.Env
import com.reskator.invoicing.db.MigrationService
import com.reskator.invoicing.db.createJdbi

class Cli : CliktCommand(name = "invoicing") {
    init {
        subcommands(Migrate(), UserCommand())
    }

    override fun run() = Unit
}

/** Installs pending database migrations from resources/migrations. */
class Migrate : CliktCommand(name = "migrate") {
    override fun run() {
        MigrationService(createJdbi(Env.load().db)).migrate()
    }
}

class UserCommand : CliktCommand(name = "user") {
    init {
        subcommands(CreateUser())
    }

    override fun run() = Unit
}

/** Adds a user who can log in. */
class CreateUser(
    private val users: () -> UserRepository = { UserRepository(createJdbi(Env.load().db)) }
) : CliktCommand(name = "create") {
    private val username by option().prompt("Username")
    private val password by option().prompt("Password", hideInput = true, requireConfirmation = true)

    override fun run() {
        val repository = users()
        if (repository.findByUsername(username) != null) {
            throw PrintMessage("User $username already exists", statusCode = 1, printError = true)
        }

        repository.create(username, AuthService.hash(password))
        echo("User $username created")
    }
}
