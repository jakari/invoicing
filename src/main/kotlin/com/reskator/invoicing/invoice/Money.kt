package com.reskator.invoicing.invoice

import java.math.BigDecimal
import java.math.RoundingMode

/** An amount in cents, as the database stores prices. The API sends and receives euros. */
data class Money(val cents: Long) {
    operator fun plus(other: Money) = Money(cents + other.cents)

    /** Euros for JSON: whole euros without decimals (100, not 100.00). */
    fun toEuros(): BigDecimal = BigDecimal.valueOf(cents, 2).stripTrailingZeros().let { if (it.signum() == 0) BigDecimal.ZERO else it }

    /** "1 234,56", as the invoice templates show amounts. */
    fun format(): String = formatNumber(BigDecimal.valueOf(cents, 2), 2, ",", " ")

    override fun toString() = format()

    companion object {
        val ZERO = Money(0)

        /** Rounds to whole cents half away from zero. */
        fun fromEuros(euros: BigDecimal) = Money(euros.movePointRight(2).setScale(0, RoundingMode.HALF_UP).longValueExact())
    }
}

/**
 * Rounds half away from zero to [decimals] places and groups thousands with [thousandsSeparator].
 */
fun formatNumber(value: BigDecimal, decimals: Int, decimalPoint: String, thousandsSeparator: String): String {
    val rounded = value.setScale(decimals, RoundingMode.HALF_UP)
    val digits = rounded.abs().toPlainString()
    val whole = digits.substringBefore('.')
    val fraction = digits.substringAfter('.', "")
    val grouped = whole.reversed().chunked(3).joinToString(thousandsSeparator).reversed()
    val sign = if (rounded.signum() < 0) "-" else ""

    return sign + grouped + if (decimals > 0) decimalPoint + fraction else ""
}
