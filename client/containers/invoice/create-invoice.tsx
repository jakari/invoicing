
import React from 'react'
import InvoiceForm from 'components/invoice/invoice-form'
import {FormattedMessage} from "react-intl"
import {getEmptyInvoice} from "state/selectors/invoices"
import { RouteComponentProps } from "react-router"
import {useInvoiceApi} from "api/invoices"
import { Invoice } from "client/records"

export function CreateInvoice({history}: RouteComponentProps) {
  const invoiceApi = useInvoiceApi()

  const create = (invoice: Invoice) => invoiceApi.create(invoice).then(({invoiceNumber}) => history.replace(`/invoice/${invoiceNumber}`))

  return <div>
    <h1 className="ui header"><FormattedMessage id="invoice.new" /></h1>
    <InvoiceForm invoice={getEmptyInvoice()}
                 submit={create}
                 cancel={() => history.goBack()}
    />
  </div>
}
