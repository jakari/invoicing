package com.reskator.invoicing.invoice

import java.math.BigDecimal
import kotlin.test.Test
import kotlin.test.assertEquals

class PhpNumberFormatTest {
    @Test
    fun `formats tax percentages like the templates' number_format(2, ",", "")`() {
        assertEquals("25,50", phpNumberFormat(BigDecimal("25.5"), 2, ",", ""))
        assertEquals("24,00", phpNumberFormat(BigDecimal("24"), 2, ",", ""))
        assertEquals("0,00", phpNumberFormat(BigDecimal.ZERO, 2, ",", ""))
    }

    @Test
    fun `rounds half away from zero and groups thousands`() {
        assertEquals("1,235", phpNumberFormat(BigDecimal("1.2345"), 3, ",", " "))
        assertEquals("-2", phpNumberFormat(BigDecimal("-1.5"), 0, ",", " "))
        assertEquals("12 345 678.9", phpNumberFormat(BigDecimal("12345678.9"), 1, ".", " "))
        assertEquals("999", phpNumberFormat(BigDecimal("999"), 0, ",", " "))
        assertEquals("1 000", phpNumberFormat(BigDecimal("999.5"), 0, ",", " "))
    }

    @Test
    fun `negative values that round to zero have no sign like php`() {
        assertEquals("0,00", phpNumberFormat(BigDecimal("-0.001"), 2, ",", " "))
    }
}
