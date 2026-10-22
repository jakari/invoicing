package com.reskator.invoicing.invoice

import java.math.BigDecimal
import kotlin.test.Test
import kotlin.test.assertEquals

class InvoiceCalculatorTest {
    private fun item(amount: Int, cents: Long, tax: String) =
        InvoiceItem(id = null, description = "x", amount = amount, price = Money(cents), tax = BigDecimal(tax))

    @Test
    fun `item totals round the tax of each item`() {
        val item = item(3, 1234, "25.50")

        assertEquals(Money(3702), InvoiceCalculator.itemTotalWithoutTax(item))
        // 3702 · 0.255 = 944.01
        assertEquals(Money(944), InvoiceCalculator.itemTax(item))
        assertEquals(Money(4646), InvoiceCalculator.itemTotal(item))
    }

    @Test
    fun `half a cent of tax rounds up`() {
        // 100 · 0.255 = 25.5
        assertEquals(Money(26), InvoiceCalculator.itemTax(item(1, 100, "25.50")))
        // 2 · 0.255 = 0.51
        assertEquals(Money(1), InvoiceCalculator.itemTax(item(1, 2, "25.50")))
    }

    @Test
    fun `invoice totals are sums of the item totals`() {
        val items = listOf(item(3, 1234, "25.50"), item(1, 10000, "24.00"))

        assertEquals(Money(13702), InvoiceCalculator.totalWithoutTax(items))
        assertEquals(Money(3344), InvoiceCalculator.taxTotal(items))
        assertEquals(Money(17046), InvoiceCalculator.total(items))
        assertEquals(Money(0), InvoiceCalculator.total(emptyList()))
    }
}
