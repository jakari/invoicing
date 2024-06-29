
import { Invoice } from "../records"
import { useApi } from "."
import { calculateTotal, recalcItem } from "../utilities/invoice"

export const useInvoiceApi = () => {
  const api = useApi()

  return {
    create: (invoice: Invoice) => api.post<{invoiceNumber: number}>(`/api/invoice`, invoice),
    list: () => api.get("/api/invoices"),
    get: async (id: string) => {
      const invoiceWithoutTotals = await api.get<Invoice>(`/api/invoice/${id}`)
      const invoiceWithItemTotals: Invoice = {
        ...invoiceWithoutTotals,
        items: invoiceWithoutTotals.items.map(recalcItem)
      }

      console.log(invoiceWithItemTotals.items.map(a => a.tax))

      return {
        ...invoiceWithItemTotals,
        total: calculateTotal(invoiceWithItemTotals.items),
      }
    },
    save: (invoice: Invoice) => api.patch(`/api/invoice/${invoice.invoiceNumber}`, invoice)
  }
}
