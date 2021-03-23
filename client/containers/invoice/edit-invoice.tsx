
import React, { useEffect, useState } from "react"
import InvoiceForm from 'components/invoice/invoice-form'
import {FormattedMessage} from "react-intl"
import { useInvoiceApi } from "api/invoices"
import { Invoice } from "records"
import { RouteComponentProps } from "react-router"
import Loader from "components/loader"


export function EditInvoice({match: {params}, history}: RouteComponentProps<{invoice: string}>) {
  const api = useInvoiceApi()
  const [invoice, setInvoice]  = useState<Invoice | null>(null)

  const save = async (invoice: Invoice) => {
    await api.save(invoice)
    history.push({pathname: '/invoice/' + invoice.invoiceNumber})
  };

  useEffect(() => {
    api.get(params.invoice).then(setInvoice)
  }, [params.invoice])

  if (!invoice) {
    return <Loader />
  }

  return <div>
    <h1 className="ui header">
      <FormattedMessage
        id="invoice.view.header"
        values={{invoiceNumber: invoice.invoiceNumber}}
      />
    </h1>
    <InvoiceForm invoice={invoice}
                 submit={save}
                 cancel={() => history.goBack()}

    />
  </div>
}

