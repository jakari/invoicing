package com.reskator.invoicing.plugins

import com.reskator.invoicing.auth.AuthService
import com.reskator.invoicing.auth.UserRepository
import com.reskator.invoicing.company.CompanyRepository
import com.reskator.invoicing.config.Env
import com.reskator.invoicing.customer.CustomerRepository
import com.reskator.invoicing.customer.CustomerService
import com.reskator.invoicing.db.createJdbi
import com.reskator.invoicing.invoice.InvoiceRenderer
import com.reskator.invoicing.invoice.InvoiceSender
import com.reskator.invoicing.invoice.MockInvoiceSender
import com.reskator.invoicing.invoice.PdfRenderer
import com.reskator.invoicing.invoice.SmtpInvoiceSender
import com.reskator.invoicing.invoice.InvoiceRepository
import com.reskator.invoicing.invoice.InvoiceService
import com.reskator.invoicing.invoice.ParameterService
import com.reskator.invoicing.settings.InvoiceSettings
import com.reskator.invoicing.settings.SettingsService
import io.ktor.server.application.*
import org.jdbi.v3.core.Jdbi
import org.koin.dsl.module
import org.koin.dsl.onClose
import org.koin.ktor.plugin.Koin
import org.koin.logger.slf4jLogger

fun appModule(env: Env, jdbi: Jdbi? = null) = module {
    single { env }
    single { jdbi ?: createJdbi(env.db) }
    single { UserRepository(get()) }
    single { AuthService(get()) }
    single { CompanyRepository(get()) }
    single { InvoiceSettings(env.invoiceSettings) }
    single { SettingsService(get(), get()) }
    single { CustomerRepository() }
    single { InvoiceRepository() }
    single { CustomerService(get(), get(), get()) }
    single { ParameterService(get()) }
    single { InvoiceService(get(), get(), get(), get()) }
    single { InvoiceRenderer(env.invoiceTemplateDir, get()) }
    single { PdfRenderer() } onClose { it?.close() }
    single<InvoiceSender> {
        val renderer = get<InvoiceRenderer>()
        val pdfRenderer = get<PdfRenderer>()
        env.mailer?.let { SmtpInvoiceSender(it, get()) { invoice -> pdfRenderer.render(renderer.render(invoice)) } } ?: MockInvoiceSender
    }
}

fun Application.configureKoin(env: Env, jdbi: Jdbi? = null) {
    install(Koin) {
        slf4jLogger()
        modules(appModule(env, jdbi))
    }
}
