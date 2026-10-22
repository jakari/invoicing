package com.reskator.invoicing.invoice

import java.math.BigDecimal
import kotlin.test.Test
import kotlin.test.assertEquals

class NumberFormatTest {
    @Test
    fun `formats tax percentages like the templates' number_format(2, ",", "")`() {
        assertEquals("25,50", formatNumber(BigDecimal("25.5"), 2, ",", ""))
        assertEquals("24,00", formatNumber(BigDecimal("24"), 2, ",", ""))
        assertEquals("0,00", formatNumber(BigDecimal.ZERO, 2, ",", ""))
    }

    @Test
    fun `rounds half away from zero and groups thousands`() {
        assertEquals("1,235", formatNumber(BigDecimal("1.2345"), 3, ",", " "))
        assertEquals("-2", formatNumber(BigDecimal("-1.5"), 0, ",", " "))
        assertEquals("12 345 678.9", formatNumber(BigDecimal("12345678.9"), 1, ".", " "))
        assertEquals("999", formatNumber(BigDecimal("999"), 0, ",", " "))
        assertEquals("1 000", formatNumber(BigDecimal("999.5"), 0, ",", " "))
    }

    @Test
    fun `negative values that round to zero have no sign`() {
        assertEquals("0,00", formatNumber(BigDecimal("-0.001"), 2, ",", " "))
    }
}
