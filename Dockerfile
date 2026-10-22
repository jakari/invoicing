# Client
FROM docker.io/library/node:20 AS frontend
WORKDIR /app
COPY package.json package-lock.json vite.config.js tsconfig.json ./
RUN npm ci
COPY client client
RUN npm run build

# Ktor application
FROM docker.io/library/eclipse-temurin:21-jdk AS backend
WORKDIR /app
COPY gradlew settings.gradle.kts build.gradle.kts ./
COPY gradle gradle
RUN ./gradlew --no-daemon dependencies > /dev/null
COPY src/main src/main
RUN ./gradlew --no-daemon buildFatJar

FROM docker.io/library/eclipse-temurin:21-jre
WORKDIR /app
COPY --from=backend /app/build/libs/invoicing.jar invoicing.jar
# The Chromium build the bundled Playwright version expects, with its system libraries and fonts
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
RUN java -cp invoicing.jar com.microsoft.playwright.CLI install --with-deps chromium && rm -rf /var/lib/apt/lists/*
COPY --from=frontend /app/dist dist
ENV PORT=5000 \
    WEB_ROOT=/app/dist \
    INVOICE_SETTINGS=/storage/default-settings.yml \
    INVOICE_TEMPLATE_DIR=/storage/templates \
    PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1
EXPOSE 5000
CMD ["java", "-jar", "invoicing.jar"]
