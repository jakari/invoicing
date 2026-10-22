package com.reskator.invoicing.auth

import org.jdbi.v3.core.Jdbi
import org.jdbi.v3.core.kotlin.mapTo
import org.jdbi.v3.core.kotlin.withHandleUnchecked

data class User(val id: Int, val username: String, val password: String)

/** The `users` table of the Symfony app; passwords are bcrypt hashes. */
class UserRepository(private val jdbi: Jdbi) {
    fun findByUsername(username: String): User? = jdbi.withHandleUnchecked {
        it.createQuery("SELECT id, username, password FROM users WHERE username = :username")
            .bind("username", username)
            .mapTo<User>()
            .findOne()
            .orElse(null)
    }

    fun create(username: String, passwordHash: String): Int = jdbi.withHandleUnchecked {
        it.createQuery("INSERT INTO users (username, password) VALUES (:username, :password) RETURNING id")
            .bind("username", username)
            .bind("password", passwordHash)
            .mapTo<Int>()
            .one()
    }
}
