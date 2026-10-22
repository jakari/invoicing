plugins {
    alias(libs.plugins.kotlin.jvm)
    alias(libs.plugins.kotlin.serialization)
    alias(libs.plugins.ktor)
}

group = "com.reskator"
version = "1.0.0"

kotlin {
    jvmToolchain(21)
}

application {
    mainClass.set("com.reskator.invoicing.ApplicationKt")
}

ktor {
    fatJar {
        archiveFileName.set("invoicing.jar")
    }
}

repositories {
    mavenCentral()
}

dependencies {
    implementation(libs.ktor.server.core)
    implementation(libs.ktor.server.netty)
    implementation(libs.ktor.server.auth)
    implementation(libs.ktor.server.sessions)
    implementation(libs.ktor.server.content.negotiation)
    implementation(libs.ktor.server.status.pages)
    implementation(libs.ktor.server.compression)
    implementation(libs.ktor.server.caching.headers)
    implementation(libs.ktor.server.call.logging)
    implementation(libs.ktor.serialization.kotlinx.json)
    implementation(libs.koin.ktor)
    implementation(libs.koin.logger.slf4j)
    implementation(libs.jdbi.core)
    implementation(libs.jdbi.kotlin)
    implementation(libs.jdbi.postgres)
    implementation(libs.postgresql)
    implementation(libs.hikari)
    implementation(libs.logback)
    implementation(libs.dotenv)
    implementation(libs.clikt)
    implementation(libs.bcrypt)
    implementation(libs.pebble)
    implementation(libs.snakeyaml)
    implementation(libs.playwright)
    implementation(libs.angus.mail)

    testImplementation(libs.ktor.server.test.host)
    testImplementation(libs.kotlin.test)
    testImplementation(libs.testcontainers.postgresql)
    testImplementation(libs.greenmail)
}

tasks.test {
    useJUnitPlatform()
    // Testcontainers on podman: use the podman machine socket when DOCKER_HOST is not set
    if (System.getenv("DOCKER_HOST") == null) {
        val socket = runCatching {
            providers.exec {
                commandLine("podman", "machine", "inspect", "--format", "{{.ConnectionInfo.PodmanSocket.Path}}")
            }.standardOutput.asText.get().trim()
        }.getOrNull()
        if (!socket.isNullOrBlank()) {
            environment("DOCKER_HOST", "unix://$socket")
            environment("TESTCONTAINERS_RYUK_DISABLED", "true")
        }
    }
}

tasks.named<JavaExec>("run") {
    standardInput = System.`in`
}
