import { atom } from "recoil"
import { AppConfiguration, defaultInvoiceSettings, Invoice, Company } from "records"

export const userSettingsState = atom<AppConfiguration>({
  key: 'userSettingsState',
  default: {
    // Default settings for invoices
    default_values: defaultInvoiceSettings,
    // List of available templates
    templates: [],
    companies: []
  }
})

export const selectedCompanyState = atom<Company | undefined>({
  key: "selectedCompanyState",
  default: undefined
})

export const mainLoadingState = atom<boolean>({
  key: 'mainLoadingState',
  default: false,
})

export const openInvoiceState = atom<Invoice | undefined>({
  key: "openInvoiceState",
  default: undefined
})
