package com.reskator.invoicing.plugins

import io.ktor.server.application.*
import io.ktor.server.response.*

/** Headers added to every response, replacing what a fronting nginx would otherwise set. */
val SecurityHeaders = createApplicationPlugin("SecurityHeaders") {
    onCall { call ->
        call.response.header("X-Content-Type-Options", "nosniff")
    }
}
