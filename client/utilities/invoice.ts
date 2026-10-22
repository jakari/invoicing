import { InvoiceItem } from "records"
import Big from "big.js"

export function calculateTotal(items: InvoiceItem[]): Big {
  return roundToTwo(items.reduce((acc, item) => acc.add(item.total), Big('0')))
}

export function calculateTotalWithoutTax(items: InvoiceItem[]): Big {
  return roundToTwo(items.reduce((acc, item) => acc.add(item.totalWithoutTax), Big('0')))
}

// Like the server's InvoiceCalculator: the tax is rounded to cents per item
export function recalcItem(item: InvoiceItem): InvoiceItem {
  const totalWithoutTax = roundToTwo(Big(item.price).mul(item.amount))
  const taxAmount = roundToTwo(totalWithoutTax.mul(item.tax).div('100'))

  return {
    ...item,
    tax: Big(item.tax).toString(),
    total: totalWithoutTax.add(taxAmount).toString(),
    totalWithoutTax: totalWithoutTax.toString()
  }
}

function roundToTwo(num: Big): Big {
  return num.round(2, Big.roundHalfUp)
}
