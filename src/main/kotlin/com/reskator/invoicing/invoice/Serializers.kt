package com.reskator.invoicing.invoice

import kotlinx.serialization.ExperimentalSerializationApi
import kotlinx.serialization.KSerializer
import kotlinx.serialization.SerializationException
import kotlinx.serialization.descriptors.PrimitiveKind
import kotlinx.serialization.descriptors.PrimitiveSerialDescriptor
import kotlinx.serialization.encoding.Decoder
import kotlinx.serialization.encoding.Encoder
import kotlinx.serialization.json.JsonDecoder
import kotlinx.serialization.json.JsonEncoder
import kotlinx.serialization.json.JsonPrimitive
import kotlinx.serialization.json.JsonUnquotedLiteral
import java.math.BigDecimal
import java.time.LocalDate
import java.time.format.DateTimeFormatter
import java.time.format.DateTimeParseException

/** Money as a JSON number of euros. */
object EurosSerializer : KSerializer<Money> {
    override val descriptor = PrimitiveSerialDescriptor("Euros", PrimitiveKind.DOUBLE)

    @OptIn(ExperimentalSerializationApi::class)
    override fun serialize(encoder: Encoder, value: Money) =
        (encoder as JsonEncoder).encodeJsonElement(JsonUnquotedLiteral(value.toEuros().toPlainString()))

    override fun deserialize(decoder: Decoder): Money {
        val element = (decoder as JsonDecoder).decodeJsonElement()
        val euros = (element as? JsonPrimitive)?.content?.toBigDecimalOrNull() ?: throw SerializationException("Invalid amount")
        return Money.fromEuros(euros)
    }
}

/** Dates as `d.m.Y` (01.01.2026), as the invoice lists show them. */
object ListDateSerializer : KSerializer<LocalDate> {
    private val format = DateTimeFormatter.ofPattern("dd.MM.yyyy")
    override val descriptor = PrimitiveSerialDescriptor("ListDate", PrimitiveKind.STRING)
    override fun serialize(encoder: Encoder, value: LocalDate) = encoder.encodeString(value.format(format))
    override fun deserialize(decoder: Decoder): LocalDate = LocalDate.parse(decoder.decodeString(), format)
}

/** Dates as `Y-m-d` (2026-01-01), as invoices are sent and received. */
object IsoDateSerializer : KSerializer<LocalDate> {
    override val descriptor = PrimitiveSerialDescriptor("IsoDate", PrimitiveKind.STRING)
    override fun serialize(encoder: Encoder, value: LocalDate) = encoder.encodeString(value.toString())
    override fun deserialize(decoder: Decoder): LocalDate = try {
        LocalDate.parse(decoder.decodeString())
    } catch (e: DateTimeParseException) {
        throw SerializationException("Invalid date", e)
    }
}

/** A tax percentage, received as a JSON number or string. */
object TaxSerializer : KSerializer<BigDecimal> {
    override val descriptor = PrimitiveSerialDescriptor("Tax", PrimitiveKind.STRING)

    /** Two decimals, as numeric(4,2) returns them ("25.50"). */
    override fun serialize(encoder: Encoder, value: BigDecimal) = encoder.encodeString(value.setScale(2).toPlainString())

    override fun deserialize(decoder: Decoder): BigDecimal {
        val element = (decoder as JsonDecoder).decodeJsonElement()
        return (element as? JsonPrimitive)?.content?.toBigDecimalOrNull() ?: throw SerializationException("Invalid tax")
    }
}
