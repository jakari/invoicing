package com.reskator.invoicing.invoice

import com.microsoft.playwright.Browser
import com.microsoft.playwright.Page
import com.microsoft.playwright.Playwright
import com.microsoft.playwright.options.WaitUntilState
import java.util.concurrent.Callable
import java.util.concurrent.Executors

/**
 * Prints HTML to an A4 PDF with headless Chromium, laid out in a 1588×2244 viewport at scale 2. Playwright objects may only be used from the thread that created them, so one thread owns the
 * browser and renders one PDF at a time. The browser starts on the first PDF and is restarted if it dies.
 */
class PdfRenderer : AutoCloseable {
    private val thread = Executors.newSingleThreadExecutor { Thread(it, "pdf-renderer").apply { isDaemon = true } }
    private var playwright: Playwright? = null
    private var browser: Browser? = null

    fun render(html: String): ByteArray = thread.submit(Callable {
        val options = Browser.NewContextOptions().setViewportSize(1588, 2244).setDeviceScaleFactor(2.0)
        browser().newContext(options).use { context ->
            val page = context.newPage()
            page.setContent(html, Page.SetContentOptions().setWaitUntil(WaitUntilState.NETWORKIDLE))
            page.pdf(Page.PdfOptions().setFormat("A4"))
        }
    }).get()

    private fun browser(): Browser {
        browser?.takeIf { it.isConnected }?.let { return it }
        playwright?.close()

        return Playwright.create().also { playwright = it }.chromium().launch().also { browser = it }
    }

    override fun close() {
        thread.submit { playwright?.close() }.get()
        thread.shutdown()
    }
}
