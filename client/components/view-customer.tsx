
import React, { useEffect, useState } from "react"
import { FormattedMessage, WrappedComponentProps } from "react-intl"
import {NavLink} from "react-router-dom"
import {Button} from "semantic-ui-react"
import { RouteComponentProps } from "react-router"
import { useCustomerApi } from "../api-helper/customers"
import { CustomerWithInvoiceList } from "../records"


type Props = RouteComponentProps<{customerId: string}> & WrappedComponentProps

export function ViewCustomer({match, history}: Props) {
  const {getCustomer} = useCustomerApi()
  const [customer, setCustomer] = useState<CustomerWithInvoiceList | null>(null)

  useEffect(() => {
    getCustomer(match.params.customerId).then(setCustomer)
  }, [match.params.customerId])

  const viewInvoice = (invoiceNumber: string) => () => history.push(
    '/invoice/' + invoiceNumber
  )

  return (
    <div>
      {customer
        ? <div>
            <h1 className="ui header">{customer.name}</h1>
            <NavLink to={`/customer/${customer.id}/edit`}>
              <Button><FormattedMessage id="invoice.edit" /></Button>
            </NavLink>
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
                  <td><FormattedMessage id="customer.additional_name" /></td>
                  <td>{ customer.additionalName }</td>
                </tr>
                <tr>
                  <td><FormattedMessage id="customer.contact_person" /></td>
                  <td>{ customer.contactPerson }</td>
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
            <FormattedMessage id="list.header">
              {(txt) => (
                <h4 className="ui horizontal divider header">
                  <i className="address book icon" />
                  {txt}
                </h4>
              )}
            </FormattedMessage>
            <table className="ui single line table">
              <thead>
              <tr>
                <th><FormattedMessage id="invoice.invoice_number" /></th>
                <th><FormattedMessage id="invoice.reference_number" /></th>
                <th><FormattedMessage id="invoice.created" /></th>
                <th><FormattedMessage id="invoice.due" /></th>
              </tr>
              </thead>
              <tbody>
              {customer.invoices.map((invoice, key) =>
                <tr key={key} className="invoice list row" onClick={viewInvoice(invoice.invoiceNumber)}>
                  <td>{invoice.invoiceNumber}</td>
                  <td>{invoice.referenceNumber}</td>
                  <td>{invoice.created}</td>
                  <td>{invoice.due}</td>
                </tr>
              )}
              </tbody>
            </table>
          </div>
        : <div>Ladataan...</div>
      }
    </div>
  )
}
