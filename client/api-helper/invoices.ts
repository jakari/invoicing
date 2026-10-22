
import { Invoice } from "../records"
import { useApi } from "."
import { useCompanyPath } from "./company"
import { calculateTotal, recalcItem } from "../utilities/invoice"

export const useInvoiceApi = () => {
  const api = useApi()
  const companyPath = useCompanyPath()

  return {
    create: (invoice: Invoice) => api.post<{invoiceNumber: number}>(companyPath(`/invoice`), invoice),
    list: () => api.get(companyPath("/invoices")),
    get: async (id: string) => {
      const invoiceWithoutTotals = await api.get<Invoice>(companyPath(`/invoice/${id}`))
      const invoiceWithItemTotals: Invoice = {
        ...invoiceWithoutTotals,
        items: invoiceWithoutTotals.items.map(recalcItem)
      }

      return {
        ...invoiceWithItemTotals,
        total: calculateTotal(invoiceWithItemTotals.items),
      }
    },
    save: (invoice: Invoice) => api.patch(companyPath(`/invoice/${invoice.invoiceNumber}`), invoice),
    pdfUrl: (invoiceNumber: number) => companyPath(`/invoice/lasku-${invoiceNumber}.pdf`)
  }
}
