package com.reskator.invoicing.invoice

import com.icegreen.greenmail.util.GreenMail
import com.icegreen.greenmail.util.ServerSetupTest
import com.reskator.invoicing.config.MailerConfig
import com.reskator.invoicing.settings.InvoiceSettings
import jakarta.mail.Multipart
import jakarta.mail.Part
import java.math.BigDecimal
import java.nio.file.Path
import java.time.LocalDate
import kotlin.test.AfterTest
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContentEquals
import kotlin.test.assertEquals

class SmtpInvoiceSenderTest {
    private val mail = GreenMail(ServerSetupTest.SMTP)
    private val settings = InvoiceSettings(Path.of(javaClass.getResource("/settings/default-settings.yml")!!.toURI()).toString())
    private val pdf = "%PDF-1.4 test".toByteArray()

    private val invoice = Invoice(
        id = 1, company = 1, customer = 1, invoiceNumber = 7, referenceNumber = 1009,
        created = LocalDate.of(2026, 1, 1), due = LocalDate.of(2026, 1, 15), status = "PENDING",
        remarkingTime = 14, interestOnArrears = 18, customerReference = null, delivery = null, conditionsOfPayment = null,
        template = "finnish", customerName = "Asiakas Oy", customerAdditionalName = "", customerContactPerson = "",
        customerStreetName = "Katu 1", customerPostCode = "00100", customerCity = "Helsinki",
        customerEmail = "saved@example.local", customerVat = null, customerPhone = null,
        items = listOf(InvoiceItem(1, "Työ", 1, Money(100), BigDecimal("25.50")))
    )

    @BeforeTest
    fun start() {
        mail.setUser("laskut@firma.local", "pw")
        mail.start()
    }

    @AfterTest
    fun stop() = mail.stop()

    @Test
    fun `mails the pdf to the email saved on the invoice`() {
        val sender = SmtpInvoiceSender(MailerConfig("127.0.0.1", ServerSetupTest.SMTP.port, "laskut@firma.local", "pw"), settings) { pdf }

        sender.send(invoice)

        val message = mail.receivedMessages.single()
        // B5: the address saved on the invoice, not the customer's current one
        assertEquals("saved@example.local", message.allRecipients.single().toString())
        assertEquals("Firma Oy - Laskut <laskut@firma.local>", message.from.single().toString())
        assertEquals("Firma Oy, Lasku", message.subject)
        val parts = message.content as Multipart
        assertEquals("Hei! Liitteenä laskunne.", parts.getBodyPart(0).content.toString())
        val attachment = parts.getBodyPart(1)
        assertEquals(Part.ATTACHMENT, attachment.disposition)
        assertEquals("lasku-7.pdf", attachment.fileName)
        assertEquals("application/pdf", attachment.contentType.substringBefore(';'))
        assertContentEquals(pdf, attachment.inputStream.readBytes())
    }
}
