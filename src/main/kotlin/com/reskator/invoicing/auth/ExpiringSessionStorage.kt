package com.reskator.invoicing.auth

import io.ktor.server.sessions.*
import java.util.concurrent.ConcurrentHashMap

/**
 * In-memory session storage that drops sessions [SESSION_LIFETIME] after they were written, so sessions
 * that are never logged out of don't pile up. Expired entries are purged on every write.
 */
class ExpiringSessionStorage(private val clock: () -> Long = System::currentTimeMillis) : SessionStorage {
    private class Entry(val value: String, val writtenAt: Long)

    private val sessions = ConcurrentHashMap<String, Entry>()

    override suspend fun write(id: String, value: String) {
        val now = clock()
        sessions.values.removeIf { it.isExpired(now) }
        sessions[id] = Entry(value, now)
    }

    override suspend fun read(id: String): String {
        val entry = sessions[id] ?: throw NoSuchElementException("Session $id not found")
        if (entry.isExpired(clock())) {
            sessions.remove(id, entry)
            throw NoSuchElementException("Session $id expired")
        }

        return entry.value
    }

    override suspend fun invalidate(id: String) {
        sessions.remove(id)
    }

    /** Number of stored sessions, for tests. */
    internal val size get() = sessions.size

    private fun Entry.isExpired(now: Long) = now - writtenAt > SESSION_LIFETIME.inWholeMilliseconds
}
