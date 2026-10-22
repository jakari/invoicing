package com.reskator.invoicing.invoice

import com.reskator.invoicing.settings.InvoiceSettings
import io.pebbletemplates.pebble.PebbleEngine
import io.pebbletemplates.pebble.loader.FileLoader
import java.io.File
import java.io.StringWriter
import java.util.Locale

/**
 * Renders an invoice with its Pebble template, `<template>.peb` in the template directory. Templates are read on
 * every render, so they can be edited without a restart, as the settings can.
 */
class InvoiceRenderer(private val templateDir: String, private val settings: InvoiceSettings) {
    private val engine = PebbleEngine.Builder()
        .loader(FileLoader().apply { prefix = templateDir })
        .newLineTrimming(false)
        .cacheActive(false)
        // Finnish number formats: numberformat("0.00") gives "25,50"
        .defaultLocale(Locale.forLanguageTag("fi-FI"))
        .extension(InvoiceExtension(File(templateDir)))
        .build()

    fun render(invoice: Invoice): String {
        check(TEMPLATE_NAME.matches(invoice.template)) { "Invalid template name ${invoice.template}" }
        val model = mapOf(
            "invoice" to invoice,
            "company" to settings.section("company"),
            "bank" to settings.section("bank")
        )

        return StringWriter().also { engine.getTemplate("${invoice.template}.peb").evaluate(it, model) }.toString()
    }
}
