
export function calculateTotal(invoice) {
  const value = invoice.items.reduce(
    (acc, item) => {
      if (isNaN(acc) || isNaN(item.total)) return NaN;
      return acc + item.total;
    },
    0
  );

  if (isNaN(value)) return 0;
  return roundToTwo(value).toFixed(2);
}

export function calculateItemTotal(item) {
  if (isNaN(item.price) || isNaN(item.tax) || isNaN(item.amount)) {
    return 0;
  }

  const price = item.price || 0;
  const tax = (item.tax / 100) + 1;

  return roundToTwo((item.amount * price) * tax);
}

export function shouldRecalculate(name) {
  return ['price', 'amount', 'tax'].includes(name)
}

export const recalcItem = (name, item) => (!shouldRecalculate(name) ? item : {...item, total: calculateItemTotal(item)})
export const recalcInvoice = (name, invoice) => (!shouldRecalculate(name) ? invoice : {...invoice, total: calculateTotal(invoice)})

function roundToTwo(num) {
  return +(Math.round(num + "e+2")  + "e-2");
}
