
import {jsonFetch as jf, rawFetch as rf} from "utilities"
import { calculateItemTotal, calculateTotal } from "../utilities/invoice"
import {dateToString} from "utilities/date"
import { defaultInvoice, Invoice, InvoiceItem } from "records"
import { Dispatch } from "redux"
import { AppState } from "client/reducers"
import { Company } from "client/reducers/invoices"

export const listInvoices = () => async (dispatch: Dispatch) => dispatch({
  type: 'SET_INVOICES',
  data: await jf('/api/invoices')(dispatch)
})

export const getInvoice = (id: string) =>  async (dispatch: Dispatch) => {
  const invoiceWithoutTotals: Invoice = await jf(`/api/invoice/${id}`)(dispatch)
  const invoiceWithItemTotals: Invoice = {
    ...invoiceWithoutTotals,
    items: invoiceWithoutTotals.items.map((item): InvoiceItem => ({...item, total: calculateItemTotal(item)}))
  }

  return {
    ...invoiceWithItemTotals,
    total: calculateTotal(invoiceWithItemTotals),
  }
}

export const saveInvoice = (invoice: Invoice) => async (dispatch: Dispatch) => {
  await rf(`/api/invoice/${invoice.invoiceNumber}`, {
    method: 'PATCH',
    body: JSON.stringify(invoice)
  })(dispatch);

  return dispatch({
    type: 'UNSET_OPEN_INVOICE'
  })
}

export function createInvoice(invoice: Invoice) {
  return rf(`/api/invoice`, {
    method: 'POST',
    body: JSON.stringify(invoice)
  })
}


export function sendInvoiceEmail(invoice: Invoice) {
  return rf(`/api/invoice/${invoice.invoiceNumber}/send-email`, {
    method: 'POST',
    body: JSON.stringify(invoice)
  })
}

export function getEmptyInvoice() {
  return function (_: Dispatch, getState: () => AppState) {
    const {settings, templates} = getState().invoices;

    const now = new Date();
    const due = new Date();
    due.setDate(due.getDate()+settings.default_due);

    return {
      ...defaultInvoice,
      remarkingTime: settings.remarking_time,
      interestOnArrears: settings.late_interest,
      created: dateToString(now),
      due: dateToString(due),
      template: templates[0].name
    }
  }
}

export function getSettings() {
  return (dispatch: Dispatch) => jf('/api/invoice/settings')(dispatch)
    .then(settings => {
      dispatch({
        type: 'SET_SETTINGS',
        data: settings
      });
      dispatch({type: 'SET_AUTHENTICATED'});
    });
}

export function selectCompany(company: Company) {
  return (dispatch: Dispatch) => {
    rf(`/api/select-company/${company.id}`, {method: "POST"})(dispatch)
      .then(() => dispatch({type: 'SET_SELECTED_COMPANY', data: company}));
  }
}
