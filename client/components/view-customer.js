
import React from 'react';
import {FormattedMessage} from "react-intl";
import {connect} from "react-redux";
import {getCustomer} from "actions/customers";
import {CustomerInvoiceList, Customer} from "records";
import {NavLink} from "react-router-dom";
import {Button} from "semantic-ui-react";

class ViewCustomerComponent extends React.Component {
  constructor(props) {
    super(props);

    this.state = {customer: null}
  }

  componentDidMount = () => {
    const {getCustomer, match} = this.props;
    getCustomer(match.params.customerId)
      .then(customer => this.setState({
        customer,
        invoices: customer.invoices
      }));
  }

  viewInvoice = invoiceNumber => () => this.props.history.push(
    '/invoice/' + invoiceNumber
  );

  render() {
    const {customer, invoices} = this.state

    return <div>
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
              {invoices.map((invoice, key) =>
                <tr key={key} className="invoice list row" onClick={this.viewInvoice(invoice.invoiceNumber)}>
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
  }
}

export const ViewCustomer = connect(null, {getCustomer})(ViewCustomerComponent)
