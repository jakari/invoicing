
import React, { useEffect, useState } from "react"
import {NavLink} from 'react-router-dom'
import {Button, Message} from 'semantic-ui-react'
import { RouteComponentProps } from "react-router"
import Loader from 'components/loader'
import {FormattedMessage} from "react-intl"
import { Invoice } from "records"
import { useSettings } from "state/user-settings"
import { useInvoiceApi } from "api/invoices"

export function ViewInvoice({match}: RouteComponentProps<{invoice: string}>) {
  const [invoice, setInvoice] = useState<Invoice | undefined>()
  const {templates} = useSettings()
  const {get} = useInvoiceApi()

  useEffect(() => {
     get(match.params.invoice).then(invoice => setInvoice(invoice))
  }, [])

  if (!invoice) {
    return <Loader />
  }

  const customer = invoice.customer!

  return <div>
    <h1 className="ui header">
      <FormattedMessage id="invoice.view.header" values={{invoiceNumber: invoice.invoiceNumber}} />
    </h1>
    <div className="ui stackable grid">
      <div className="eight wide column">
        <FormattedMessage id="invoice.template">
          {(txt) => (
            <h4 className="ui horizontal divider header">
              <i className="address book icon" />
              {txt}
            </h4>
          )}
        </FormattedMessage>
        <Message visible>{templates.find(template => invoice.template === template.name)!.title}</Message>
        <FormattedMessage id="customer.information">
          {(txt) => (
            <h4 className="ui horizontal divider header">
              <i className="address book icon" />
              {txt}
            </h4>
          )}
        </FormattedMessage>
        <table className="ui definition table">
          <tbody>
          <tr>
            <td><FormattedMessage id="customer.company" /></td>
            <td>{ customer.name }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="customer.phone" /></td>
            <td>{ customer.phone }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="customer.email" /></td>
            <td>{ customer.email }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="customer.vat" /></td>
            <td>{ customer.vat }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="customer.street_name" /></td>
            <td>
              { customer.streetName }<br />
              { customer.postCode } { customer.city }
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <div className="eight wide column">
        <FormattedMessage id="invoice.information">
          {(txt) => (
            <h4 className="ui horizontal divider header">
              <i className="address book icon" />
              {txt}
            </h4>
          )}
        </FormattedMessage>
        <table className="ui definition table">
          <tbody>
          <tr>
            <td><FormattedMessage id="invoice.invoice_number" /></td>
            <td>{ invoice.invoiceNumber }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.reference_number" /></td>
            <td>{ invoice.referenceNumber }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.created" /></td>
            <td>{ invoice.created }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.due" /></td>
            <td>{ invoice.due }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.delivery" /></td>
            <td>{ invoice.delivery }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.remarking_time" /></td>
            <td>
              { invoice.remarkingTime }
              &nbsp;
              <FormattedMessage id="invoice.remarking_time_days_suffix" />
            </td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.interest_on_arrears" /></td>
            <td>{ invoice.interestOnArrears } %</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.conditions_of_payment" /></td>
            <td>{ invoice.conditionsOfPayment }</td>
          </tr>
          <tr>
            <td><FormattedMessage id="invoice.customer_reference" /></td>
            <td>{ invoice.customerReference }</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
    <table className="ui celled invoice items table">
      <thead>
      <tr>
        <FormattedMessage id="invoice.items.header.name" tagName="th" />
        <FormattedMessage id="invoice.items.header.amount" tagName="th" />
        <FormattedMessage id="invoice.items.header.price" tagName="th" />
        <FormattedMessage id="invoice.items.header.tax" tagName="th" />
        <FormattedMessage id="invoice.total" tagName="th" />
      </tr>
      </thead>
      <tbody>
      {invoice.items.map((item, key) => (<tr key={key}>
          <td>{ item.description }</td>
          <td className="amount">{ item.amount }</td>
          <td className="price">{ item.price.toFixed(2) }</td>
          <td className="tax">{ item.tax }</td>
          <td className="total">{ item.total.toFixed(2) }</td>
        </tr>))}
      </tbody>
      <tfoot>
      <tr>
        <th colSpan={4} className="footer header">
          <h4 className="ui right floated header">
            <FormattedMessage id="invoice.total" />
          </h4>
        </th>
        <th className="footer value">{ invoice.total.toFixed(2) }</th>
      </tr>
      </tfoot>
    </table>

    <div className="ui hidden divider" />

    <a href={`/api/invoice/lasku-${invoice.invoiceNumber}.pdf`} target="_blank">
      <Button primary>
        <FormattedMessage id="invoice.print" />
      </Button>
    </a>
    {/*<Button primary onClick={this.sendEmail}>
      <FormattedMessage id="invoice.send_email" />
    </Button>*/}
    <NavLink to={`/invoice/${invoice.invoiceNumber}/edit`}>
      <Button><FormattedMessage id="invoice.edit" /></Button>
    </NavLink>
  </div>
}
