
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import InvoiceForm from '../components/invoice-form';
import {getInvoice, saveInvoice} from "actions/invoices";
import Loader from 'components/loader';

class EditInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: null};
  }

  async componentDidMount() {
    const invoice = await this.props.getInvoice(this.props.match.params.invoice);
    this.setState({invoice});
  }

  render() {
    if (!this.state.invoice) {
      return <Loader />;
    }

    return <div>
      <h1 className="ui header">Invoice { this.state.invoice.invoiceNumber }</h1>
      <InvoiceForm invoice={this.state.invoice} submit={this.props.saveInvoice}  />
    </div>
  }
}

export default connect(null, {getInvoice, saveInvoice})(EditInvoice);
