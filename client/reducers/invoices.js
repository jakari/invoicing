import {createReducer} from "utilities";
import {Record} from 'immutable';
import {
  InvoiceSettingsRecord
} from "records";

const initial = new Record({
  // List of invoices
  invoices: [],
  // The selected invoice
  open_invoice: null,
  // Default settings for invoices
  settings: null,
  // List of available templates
  templates: [],

  companies: [],

  selectedCompany: undefined
});

export default createReducer(new initial(), {
  SET_INVOICES: (state, invoices) => state.set('invoices', invoices),
  UNSET_OPEN_INVOICE: (state) => state.set('open_invoice', null),
  SET_SETTINGS: (state, settings) => state
    .set('settings', new InvoiceSettingsRecord(settings.default))
    .set('templates', settings.templates)
    .set('companies', settings.companies)
    .set('selectedCompany', settings.selected_company),
  SET_SELECTED_COMPANY: (state, company) => state.set('selectedCompany', company)
});
