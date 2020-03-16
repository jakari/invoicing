import {Record, List} from "immutable";

export const AuthRecord = new Record({
  isAuthenticated: null
});

export const CustomerRecord = new Record({
  id: 0,
  name: '',
  streetName: '',
  postCode: '',
  city: '',
  email: '',
  vat: '',
  phone: ''
});

export const InvoiceListRecord = new Record({
  customer: null,
  invoiceNumber: null,
  referenceNumber: null,
  created: null,
  due: null
});

export const InvoiceItemRecord = new Record({
  id: null,
  description: '',
  amount: 1,
  price: 0,
  tax: 24,
  total: 0
});

export const InvoiceRecord = new Record({
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
  items: List([new InvoiceItemRecord()]),
  total: 0,
  remarkingTime: 0,
  interestOnArrears: 0,
  customerReference: null,
  delivery: null,
  conditionsOfPayment: null,
  template: ''
});

export const InvoiceSettingsRecord = new Record({
  default_due: 0,
  late_interest: 0,
  remarking_time: 0
});
