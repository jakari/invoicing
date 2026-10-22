package com.reskator.invoicing.invoice

import java.math.BigDecimal
import kotlin.test.Test
import kotlin.test.assertEquals

class MoneyTest {
    @Test
    fun `euros from json round to cents half away from zero`() {
        assertEquals(Money(1234), Money.fromEuros(BigDecimal("12.34")))
        assertEquals(Money(1235), Money.fromEuros(BigDecimal("12.345")))
        assertEquals(Money(-1235), Money.fromEuros(BigDecimal("-12.345")))
        assertEquals(Money(10000), Money.fromEuros(BigDecimal("100")))
    }

    @Test
    fun `euros for json drop trailing zeros`() {
        assertEquals("100", Money(10000).toEuros().toPlainString())
        assertEquals("125.5", Money(12550).toEuros().toPlainString())
        assertEquals("0.06", Money(6).toEuros().toPlainString())
        assertEquals("0", Money(0).toEuros().toPlainString())
    }

    @Test
    fun `formats like number_format with comma and space`() {
        assertEquals("170,46", Money(17046).format())
        assertEquals("0,05", Money(5).format())
        assertEquals("1 234 567,89", Money(123456789).format())
        assertEquals("-1 234,50", Money(-123450).format())
    }
}
