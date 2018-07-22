
import {jsonFetch as jf, rawFetch as rf} from "utilities";
import {calculateItemTotal, calculateTotal} from "../utilities/invoice";
import {CustomerRecord, InvoiceItemRecord, InvoiceRecord} from "../records";
import {List} from "immutable";

export function listInvoices() {
  return async dispatch => dispatch({
    type: 'SET_INVOICES',
    data: await jf(dispatch, '/api/invoices')
  });
}

export function getInvoice(id) {
  return async dispatch => {
    const invoice = await jf(dispatch, `/api/invoice/${id}`);

    invoice.items.forEach(item => item.total = calculateItemTotal(item));
    invoice.total = calculateTotal(invoice);

    return new InvoiceRecord({
      ...invoice,
      customer: new CustomerRecord(invoice.customer),
      items: List(invoice.items.map(item => new InvoiceItemRecord(item)))
    });
  }
}

export function saveInvoice(invoice) {
  return async dispatch => {
    await rf(dispatch, `/api/invoice/${invoice.invoiceNumber}`, {
      method: 'PATCH',
      body: JSON.stringify(invoice)
    });

    return dispatch({
      type: 'UNSET_OPEN_INVOICE'
    })
  }
}


export function createInvoice(invoice) {
  return function (dispatch) {
    return rf(dispatch, `/api/invoice`, {
      method: 'POST',
      body: JSON.stringify(invoice)
    });
  }
}

export function getSettings() {
  return dispatch => jf(dispatch, '/api/invoice/settings')
    .then(settings => {
      dispatch({
        type: 'SET_SETTINGS',
        data: settings
      });
      dispatch({type: 'SET_AUTHENTICATED'});
    });
}
