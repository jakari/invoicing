
import React, {Component} from 'react';
import {connect} from 'react-redux';
import {listInvoices} from "actions/invoices";
import {FormattedMessage} from "react-intl";

class ListInvoices extends Component {
  constructor(props) {
    super(props);

    this.props.listInvoices();
  }

  viewInvoice = invoiceNumber => () => this.props.history.push(
    '/invoice/' + invoiceNumber
  );

  render(){
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
          {this.props.invoices.map((invoice, key) =>
            <tr key={key} className="invoice list row" onClick={this.viewInvoice(invoice.invoiceNumber)}>
              <td>{invoice.customer}</td>
              <td>{invoice.invoiceNumber}</td>
              <td>{invoice.referenceNumber}</td>
              <td>{invoice.created}</td>
              <td>{invoice.due}</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>;
  }
}

export default connect(
  state => ({
    invoices: state.invoices.invoices
  }),
  {
    listInvoices
  }
)(ListInvoices)
