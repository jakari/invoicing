import {createReducer} from "utilities"
import { AppConfiguration, defaultInvoiceSettings, InvoiceList } from "records"

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

export interface Company {
  id: number
  name: string
}


export default createReducer(defaultAppConfiguration, {
  SET_INVOICES: (state: AppConfiguration, invoices: InvoiceList[]) => ({...state, invoices}),
  UNSET_OPEN_INVOICE: (state: AppConfiguration) => ({...state, open_invoice: null}),
  SET_SETTINGS: (_: AppConfiguration, settings: any) => ({
    invoices: [],
    open_invoice: null,
    settings: settings.default,
    templates: settings.templates,
    companies: settings.companies,
    selectedCompany: settings.selected_company
  }),
  SET_SELECTED_COMPANY: (state: AppConfiguration, company: Company) => ({...state, selectedCompany: company})
})
