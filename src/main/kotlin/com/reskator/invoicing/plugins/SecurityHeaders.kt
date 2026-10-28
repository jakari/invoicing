package com.reskator.invoicing.plugins

import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.response.*

/** Headers added to every response, replacing what a fronting nginx would otherwise set. */
val SecurityHeaders = createApplicationPlugin("SecurityHeaders") {
    onCall { call ->
        call.response.header("X-Content-Type-Options", "nosniff")
        // Cloudflare caches by file extension (lasku-1.pdf), keyed by URL alone, ignoring the session cookie
        if (call.request.path().startsWith("/api/")) call.response.header(HttpHeaders.CacheControl, "no-store")
    }
}
