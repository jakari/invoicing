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
  const tax = (item.tax / 100) + 1

  return {
    ...item,
    total: roundToTwo((item.amount * price) * tax)
  }
}

function roundToTwo(num: number): number {
  return +(Math.round(Number(num.toString() + "e+2"))  + "e-2");
}
