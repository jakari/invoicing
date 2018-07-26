
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import InvoiceForm from '../components/invoice-form'
import {createInvoice, getEmptyInvoice} from "actions/invoices";

class CreateInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: this.props.getEmptyInvoice()};
  }

  saveInvoice = invoice => {
    this.props.createInvoice(invoice);
  };

  render() {
    return <div>
      <h1 className="ui header">Create invoice</h1>
      <InvoiceForm invoice={this.state.invoice} submit={this.saveInvoice} />
    </div>
  }
}

export default connect(null, {createInvoice, getEmptyInvoice})(CreateInvoice);
