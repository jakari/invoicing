
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import InvoiceForm from '../components/invoice-form'
import {InvoiceRecord} from "../records";
import {createInvoice} from "actions/invoices";

class CreateInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: new InvoiceRecord()};
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

export default connect(null, {createInvoice})(CreateInvoice);
