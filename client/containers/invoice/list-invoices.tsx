
import React, { useEffect, useState } from "react"
import {FormattedMessage} from "react-intl"
import { RouteComponentProps } from "react-router"
import { useInvoiceApi } from "../../api-helper/invoices"
import { Invoice } from "../../records"

export function ListInvoices({history}: RouteComponentProps) {
  const [invoices, setInvoices] = useState<Invoice[]>([])
  const invoiceApi = useInvoiceApi()

  useEffect(() => {
    invoiceApi.list().then(setInvoices)
  }, [])

  const viewInvoice = (invoiceNumber: number) => () => history.push(
    '/invoice/' + invoiceNumber
  );

    return <div>
      <h1 className="ui header">
        <FormattedMessage id="list.header" />
      </h1>
      <table className="ui single line table">
        <thead>
        <tr>
          <th><FormattedMessage id="invoice.customer" /></th>
          <th><FormattedMessage id="invoice.invoice_number" /></th>
          <th><FormattedMessage id="invoice.reference_number" /></th>
          <th><FormattedMessage id="invoice.created" /></th>
          <th><FormattedMessage id="invoice.due" /></th>
        </tr>
        </thead>
        <tbody>
          {invoices.map((invoice, key) =>
            <tr key={key} className="invoice list row" onClick={viewInvoice(invoice.invoiceNumber)}>
              <td>{invoice.customer}</td>
              <td>{invoice.invoiceNumber}</td>
              <td>{invoice.referenceNumber}</td>
              <td>{invoice.created}</td>
              <td>{invoice.due}</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
}
