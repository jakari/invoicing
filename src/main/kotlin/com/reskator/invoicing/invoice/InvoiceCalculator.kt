package com.reskator.invoicing.invoice

import java.math.BigDecimal
import java.math.RoundingMode

/** Invoice sums as the PHP app's InvoiceCalculator made them: the tax is rounded to cents per item. */
object InvoiceCalculator {
    private val HUNDRED = BigDecimal(100)

    fun itemTotalWithoutTax(item: InvoiceItem) = Money(item.price.cents * item.amount)

    fun itemTax(item: InvoiceItem) = Money(
        BigDecimal.valueOf(itemTotalWithoutTax(item).cents).multiply(item.tax)
            .divide(HUNDRED).setScale(0, RoundingMode.HALF_UP).longValueExact()
    )

    fun itemTotal(item: InvoiceItem) = itemTotalWithoutTax(item) + itemTax(item)

    fun totalWithoutTax(items: List<InvoiceItem>) = items.fold(Money.ZERO) { sum, item -> sum + itemTotalWithoutTax(item) }

    fun taxTotal(items: List<InvoiceItem>) = items.fold(Money.ZERO) { sum, item -> sum + itemTax(item) }

    fun total(items: List<InvoiceItem>) = items.fold(Money.ZERO) { sum, item -> sum + itemTotal(item) }
}
