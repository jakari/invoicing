package com.reskator.invoicing.invoice

import com.reskator.invoicing.config.MailerConfig
import com.reskator.invoicing.settings.InvoiceSettings
import jakarta.activation.DataHandler
import jakarta.mail.Authenticator
import jakarta.mail.Message
import jakarta.mail.PasswordAuthentication
import jakarta.mail.Session
import jakarta.mail.Transport
import jakarta.mail.internet.InternetAddress
import jakarta.mail.internet.MimeBodyPart
import jakarta.mail.internet.MimeMessage
import jakarta.mail.internet.MimeMultipart
import jakarta.mail.util.ByteArrayDataSource
import java.util.Properties

fun interface InvoiceSender {
    fun send(invoice: Invoice)
}

/** USE_MOCK_MAILER: sends nothing. */
object MockInvoiceSender : InvoiceSender {
    override fun send(invoice: Invoice) = Unit
}

/**
 * E-mails the invoice as a PDF attachment to the e-mail address saved on the invoice, with the sender, subject
 * and body from the `email` section of the settings file. Port 465 uses SSL, other ports STARTTLS.
 */
class SmtpInvoiceSender(
    private val mailer: MailerConfig,
    private val settings: InvoiceSettings,
    private val pdf: (Invoice) -> ByteArray
) : InvoiceSender {
    private val session = Session.getInstance(
        Properties().apply {
            put("mail.smtp.host", mailer.host)
            put("mail.smtp.port", mailer.port.toString())
            put("mail.smtp.auth", "true")
            if (mailer.port == 465) put("mail.smtp.ssl.enable", "true") else put("mail.smtp.starttls.enable", "true")
        },
        object : Authenticator() {
            override fun getPasswordAuthentication() = PasswordAuthentication(mailer.username, mailer.password)
        }
    )

    @Suppress("UNCHECKED_CAST")
    override fun send(invoice: Invoice) {
        val email = settings.section("email")
        val from = email["from"] as Map<String, Any?>

        val message = MimeMessage(session).apply {
            setFrom(InternetAddress(from["address"].toString(), from["name"]?.toString(), "UTF-8"))
            setRecipient(Message.RecipientType.TO, InternetAddress(invoice.customerEmail))
            setSubject(email["subject"].toString(), "UTF-8")
            setContent(
                MimeMultipart(
                    MimeBodyPart().apply { setText(email["body"].toString(), "UTF-8") },
                    MimeBodyPart().apply {
                        dataHandler = DataHandler(ByteArrayDataSource(pdf(invoice), "application/pdf"))
                        fileName = "lasku-${invoice.invoiceNumber}.pdf"
                        disposition = MimeBodyPart.ATTACHMENT
                    }
                )
            )
        }

        Transport.send(message)
    }
}
