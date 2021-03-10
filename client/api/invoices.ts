
import { Invoice } from "../records"
import { useApi } from "api"
import { calculateTotal, recalcItem } from "utilities/invoice"

export const useInvoiceApi = () => {
  const api = useApi()

  return {
    create: (invoice: Invoice) => api.post(`/api/invoice`, invoice),
    list: () => api.get("/api/invoices"),
    get: async (id: string) => {
      const invoiceWithoutTotals = await api.get<Invoice>(`/api/invoice/${id}`)
      const invoiceWithItemTotals: Invoice = {
        ...invoiceWithoutTotals,
        items: invoiceWithoutTotals.items.map(recalcItem)
      }

      return {
        ...invoiceWithItemTotals,
        total: calculateTotal(invoiceWithItemTotals.items),
      }
    },
    save: (invoice: Invoice) => api.patch(`/api/invoice/${invoice.invoiceNumber}`, invoice)
  }
}
