import { InvoiceItem } from "records"

export function calculateTotal(items: InvoiceItem[]): number {
  const value = items.reduce(
    (acc, item) => {
      if (isNaN(acc) || isNaN(item.total)) return NaN;
      return acc + item.total
    },
    0
  );

  if (isNaN(value)) return 0
  return roundToTwo(value)
}

export function recalcItem(item: InvoiceItem): InvoiceItem {
  const price = item.price || 0
  // @ts-ignore Sanitize input from API with parsefloat
  const tax = parseFloat(item.tax)
  const taxMultiplier = (tax / 100) + 1
  const totalWithoutTax = price * item.amount

  return {
    ...item,
    tax,
    total: roundToTwo(totalWithoutTax * taxMultiplier),
    totalWithoutTax: roundToTwo(totalWithoutTax)
  }
}

function roundToTwo(num: number): number {
  return +(Math.round(Number(num.toString() + "e+2"))  + "e-2");
}
