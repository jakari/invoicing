import {createReducer} from "utilities";
import {List, Record} from 'immutable';
import {CustomerRecord, InvoiceItemRecord, InvoiceRecord} from "records";

const initial = new Record({
  // List of invoices
  invoices: [],
  // The selected invoice
  open_invoice: null,
  // Default settings for invoices
  settings: null
});

export default createReducer(new initial(), {
  SET_INVOICES: (state, invoices) => state.set('invoices', invoices),
  UNSET_OPEN_INVOICE: (state) => state.set('open_invoice', null),
  SET_SETTINGS: (state, settings) => state.set('settings', settings)
});
