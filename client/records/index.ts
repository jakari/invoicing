
export type Auth = Readonly<{isAuthenticated: Boolean|null}>
export type Util = Readonly<{loading: false}>

export type Customer = Readonly<{
  id: number
  name: string
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
  streetName: '',
  postCode: '',
  city: '',
  email: '',
  vat: '',
  phone: ''
}

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
}>

export const defaultInvoiceItem: InvoiceItem = {
  description: '',
  amount: 1,
  price: 0,
  tax: 24,
  total: 0
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
  items: [],
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
  // List of invoices
  invoices: InvoiceList[]
  // The selected invoice
  open_invoice: Invoice | null
  // Default settings for invoices
  settings: InvoiceSettings
  // List of available templates
  templates: Template[]
  companies: string[]
  selectedCompany: string | undefined
}>

export const defaultAppConfiguration: AppConfiguration = {
  // List of invoices
  invoices: [],
  // The selected invoice
  open_invoice: null,
  // Default settings for invoices
  settings: defaultInvoiceSettings,
  // List of available templates
  templates: [],
  companies: [],
  selectedCompany: undefined
}
