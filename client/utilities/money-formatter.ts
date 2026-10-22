import Big from "big.js"

const formatter = new Intl.NumberFormat('fi-FI', {currency: "EUR", style: "currency"})

// The values are in cents precision, which a number holds well enough for display
export const moneyFormatter = {
  format: (value: Big | string) => formatter.format(Number(value.toString()))
}
