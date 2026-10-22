package com.reskator.invoicing.auth

import at.favre.lib.crypto.bcrypt.BCrypt
import kotlinx.serialization.Serializable
import kotlin.time.Duration.Companion.hours

/** Sessions end this long after login, however active the user is. */
val SESSION_LIFETIME = 12.hours

/**
 * A logged in user. [companyId] is the company selected last, which the settings report as `selected_company`;
 * company-scoped requests take their company from the URL instead.
 */
@Serializable
data class UserSession(val userId: Int, val issuedAt: Long, val companyId: Int? = null) {
    fun isExpired(now: Long = System.currentTimeMillis()) = now - issuedAt > SESSION_LIFETIME.inWholeMilliseconds
}

@Serializable
data class LoginInput(val username: String = "", val password: String = "")

/** Logs in the users of the `users` table (replaces Symfony's json_login with the entity user provider). */
class AuthService(private val users: UserRepository) {
    fun authenticate(username: String, password: String): UserSession? {
        val user = users.findByUsername(username)
        // Verify against a dummy hash for unknown users too, so response times don't reveal which usernames exist
        val verified = BCrypt.verifyer().verify(password.toCharArray(), (user?.password ?: DUMMY_HASH).toCharArray()).verified

        return if (user != null && verified) UserSession(user.id, System.currentTimeMillis()) else null
    }

    companion object {
        private val DUMMY_HASH = hash("dummy password")

        /** Cost 12, as the Symfony app's encoder used. */
        fun hash(password: String): String = BCrypt.withDefaults().hashToString(12, password.toCharArray())
    }
}
