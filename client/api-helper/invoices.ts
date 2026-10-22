
import { Invoice, InvoiceItem } from "../records"
import { useApi } from "."
import { useCompanyPath } from "./company"
import { calculateTotal, recalcItem } from "../utilities/invoice"

// The API sends the amount and the price as JSON numbers and expects the amount back as an integer
type ApiItem = Readonly<{id: number, description: string, amount: number, price: number | string, tax: string}>
type ApiInvoice = Omit<Invoice, 'items' | 'total'> & {items: ApiItem[]}

const fromApiItem = (item: ApiItem): InvoiceItem => recalcItem({
  ...item,
  amount: String(item.amount),
  price: String(item.price),
  total: '0',
  totalWithoutTax: '0'
})

const toApi = (invoice: Invoice) => ({
  ...invoice,
  items: invoice.items.map(item => ({...item, amount: Number(item.amount)}))
})

export const useInvoiceApi = () => {
  const api = useApi()
  const companyPath = useCompanyPath()

  return {
    create: (invoice: Invoice) => api.post<{invoiceNumber: number}>(companyPath(`/invoice`), toApi(invoice)),
    list: () => api.get(companyPath("/invoices")),
    get: async (id: string): Promise<Invoice> => {
      const invoiceWithoutTotals = await api.get<ApiInvoice>(companyPath(`/invoice/${id}`))
      const items = invoiceWithoutTotals.items.map(fromApiItem)

      return {
        ...invoiceWithoutTotals,
        items,
        total: calculateTotal(items).toString(),
      }
    },
    save: (invoice: Invoice) => api.patch(companyPath(`/invoice/${invoice.invoiceNumber}`), toApi(invoice)),
    pdfUrl: (invoiceNumber: number) => companyPath(`/invoice/lasku-${invoiceNumber}.pdf`)
  }
}
