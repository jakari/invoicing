
export interface Company {
  id: number
  name: string
}

export type Customer = Readonly<{
  id: number
  name: string
  additionalName: string
  contactPerson: string
  streetName: string
  postCode: string
  city: string
  email: string
  vat: string
  phone: string
}>
export const defaultCustomer: Customer = {
  id: 0,
  name: '',
  additionalName: "",
  contactPerson: "",
  streetName: '',
  postCode: '',
  city: '',
  email: '',
  vat: '',
  phone: ''
}

export type CustomerWithInvoiceList = Customer & {invoices: CustomerInvoiceList[]}

export type CustomerInvoiceList = Readonly<{
  invoiceNumber: string,
  referenceNumber: string,
  created: string,
  due: string
}>
export const defaultCustomerInvoiceList: CustomerInvoiceList = {
  invoiceNumber: "",
  referenceNumber: "",
  created: "",
  due: ""
}

export type InvoiceList = Readonly<{customer: number} & CustomerInvoiceList>
export const defaultInvoiceList: InvoiceList = {
  customer: 0,
  invoiceNumber: "",
  referenceNumber: "",
  created: "",
  due: ""
}

export type InvoiceItem = Readonly<{
  id?: number
  description: string
  amount: number
  price: number
  tax: number
  total: number
  totalWithoutTax: number
}>

export const defaultInvoiceItem: InvoiceItem = {
  description: '',
  amount: 1,
  price: 0,
  tax: 25.5,
  total: 0,
  totalWithoutTax: 0
}

export type Invoice = Readonly<{
  created: string
  due: string
  customer: Customer | null
  customerName: string
  customerStreetName: string
  customerPostCode: string
  customerCity: string
  customerVat: string
  invoiceNumber: number
  referenceNumber: number
  items: InvoiceItem[]
  total: number
  remarkingTime: number
  interestOnArrears: number
  customerReference: string
  delivery: string
  conditionsOfPayment: string
  template: string
}>

export const defaultInvoice: Invoice = {
  created: '',
  due: '',
  customer: null,
  customerName: '',
  customerStreetName: '',
  customerPostCode: '',
  customerCity: '',
  customerVat: '',
  invoiceNumber: 0,
  referenceNumber: 0,
  items: [defaultInvoiceItem],
  total: 0,
  remarkingTime: 0,
  interestOnArrears: 0,
  customerReference: "",
  delivery: "",
  conditionsOfPayment: "",
  template: ''
}

export type InvoiceSettings = Readonly<{
  default_due: number
  late_interest: number
  remarking_time: number
}>

export const defaultInvoiceSettings: InvoiceSettings = {
  default_due: 0,
  late_interest: 0,
  remarking_time: 0
}

export interface Template {
  name: string
  title: string
}

export type AppConfiguration = Readonly<{
  // Default settings for invoices
  default_values: InvoiceSettings
  // List of available templates
  templates: Template[]
  companies: Company[]
  selected_company?: Company
}>
