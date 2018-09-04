
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import InvoiceForm from '../components/invoice-form';
import {createInvoice, getEmptyInvoice} from "actions/invoices";
import {FormattedMessage} from "react-intl";

class CreateInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: this.props.getEmptyInvoice()};
  }

  saveInvoice = invoice => this.props.createInvoice(invoice)
    .then(response => response.text())
    .then(invoiceNumber => this.props.history.push({pathname: '/invoice/' + invoiceNumber}));

  render() {
    return <div>
      <h1 className="ui header"><FormattedMessage id="invoice.new" /></h1>
      <InvoiceForm invoice={this.state.invoice}
                   submit={this.saveInvoice}
                   cancel={() => this.props.history.goBack()}
      />
    </div>
  }
}

export default connect(null, {createInvoice, getEmptyInvoice})(CreateInvoice);
