# invoicing

Invoicing is an invoicing system that's easy to use and simple.

It's intended for small companies and it doesn't have many special features but it should be quite good and simple for that since as an small business you'll would more likely use your time to something more valuable than invoicing bullshit.

Kotlin/Ktor on PostgreSQL, with a React client (`client/`). Invoices are printed to PDF with Playwright's headless Chromium.

## Settings and templates

Each deployment has its own settings file and invoice templates, outside the repository:

- `INVOICE_SETTINGS`: the settings file, copied from `default-settings.example.yml`: default invoice values, company and bank details, e-mail texts, and the list of templates.
- `INVOICE_TEMPLATE_DIR`: the invoice templates, `<template name>.peb`, and the images they embed with `base64image`. `templates/` has examples.

The templates are [Pebble](https://pebbletemplates.io) templates. They get `invoice`, `company` and `bank` (the settings file's sections), and these filters besides Pebble's own: `money` ("1 234,56"), `itemTotal`, `itemTotalWithoutTax`, `total`, `totalWithoutTax`, `taxTotal`, and `base64image` for an image file in the template directory. Numbers are formatted in Finnish (`numberformat("0.00")` gives "25,50"). The settings and templates are read on every use, so edits apply without a restart.

## Database migrations

Schema changes are SQL files in `src/main/resources/migrations`, named `version-up-<yyyyMMddHHmmss>.sql`. The first ones are the Symfony app's Doctrine migrations under their original versions, recorded in Doctrine's `migration_versions` table, so a database the PHP app migrated carries on where it left off.

They are installed by hand with the `migrate` command:

```
./gradlew run --args=migrate                                  # locally
podman compose exec app java -jar invoicing.jar migrate       # podman/docker
dokku run <app> java -jar invoicing.jar migrate               # production
```

## Adding a user

```
./gradlew run --args="user create"
podman compose exec app java -jar invoicing.jar user create
dokku run <app> java -jar invoicing.jar user create
```

The command asks for the username and password. Users created by the PHP app keep their passwords.

## Development with podman/docker

Put your settings file at `storage/default-settings.yml` and your templates in `storage/templates/` (`storage/` is not committed), then:

```
podman compose up -d --build
podman compose exec app java -jar invoicing.jar migrate
podman compose exec app java -jar invoicing.jar user create
podman compose exec db psql -U invoicing -c "INSERT INTO company(name) VALUES('Yritys Oy')"
```

The application is at http://localhost:8080. Invoices are not e-mailed (`USE_MOCK_MAILER`).

## Running locally

Copy `.env.example` to `.env`, fill it in and run:

```
./gradlew run
```

Client with hot reload: `npm install && npm start` (Vite proxies `/api` to `localhost:8080`). `npm run build` builds it into `dist/`, which the application serves.

## Tests

```
./gradlew test
```

Tests start Postgres with Testcontainers and create the schema with the migrations; `MigrationTest` checks it matches the schema the Doctrine migrations created (`src/test/resources/doctrine-schema.sql`). On macOS the podman machine socket is used automatically when `DOCKER_HOST` is not set. The PDF tests download Chromium on their first run.

## Deployment (Dokku)

The `Dockerfile` builds the client and the application, and installs Chromium. Set the environment variables from `.env.example` (`DATABASE_URL` comes from the postgres plugin), and mount a storage directory at `/storage` with `default-settings.yml` and `templates/`:

```
dokku storage:mount <app> /var/lib/dokku/data/storage/<app>:/storage
```

Run `migrate` after deploying a release that adds migrations.

### Moving from the PHP app

1. Back up the database.
2. Copy the deployed `default-settings.yml` and the templates with their images (`src/Invoicing/Bundle/AppBundle/Resources/views/invoice/`) to the storage directory, under `templates/`. Rename the templates from `.twig` to `.peb` and change their Twig-only parts:
   - `date("d.m.Y")` → `date("dd.MM.yyyy")`
   - remove `{% set difference = date(invoice.due).diff(invoice.created) %}`, and replace `{{ difference.days }}` with `{{ invoice.paymentTermDays }}`
   - `number_format(2, ",", "")` → `numberformat("0.00")`
   - `"@InvoicingAppBundle/Resources/views/invoice/aaa.gif"|base64image` → `"aaa.gif"|base64image`
3. Set `APP_SECRET`, `USE_MOCK_MAILER`, and for e-mail `MAILER_HOST`, `MAILER_PORT`, `MAILER_USER` and `MAILER_PASSWORD` (Gmail: `smtp.gmail.com`, port 465). The `DATABASE_*`, `MAILER_TRANSPORT`, `SERVER_NAME` and `NODE_BIN` variables are no longer used.
4. Deploy, and run `migrate`: it installs only the migrations the PHP app had not run.

Everyone logs in again after the switch.
