
import React from 'react'
import InvoiceForm from '../components/invoice-form'
import {FormattedMessage} from "react-intl"
import {getEmptyInvoice} from "state/selectors/invoices"
import { RouteComponentProps } from "react-router"
import {useInvoiceApi} from "api/invoices"

export function CreateInvoice({history}: RouteComponentProps) {
  const invoiceApi = useInvoiceApi()

  return <div>
    <h1 className="ui header"><FormattedMessage id="invoice.new" /></h1>
    <InvoiceForm invoice={getEmptyInvoice()}
                 submit={invoiceApi.create}
                 cancel={() => history.goBack()}
    />
  </div>
}
