
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import InvoiceForm from '../components/invoice-form';
import {getInvoice, saveInvoice} from "actions/invoices";
import Loader from 'components/loader';

class EditInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: null, loading: false};
  }

  async componentDidMount() {
    const invoice = await this.props.getInvoice(this.props.match.params.invoice);
    this.setState({invoice});
  }

  save = async (...props) => {
    this.setState({loading: true});
    await this.props.saveInvoice(...props);

    this.props.history.push({pathname: '/invoice/' + this.state.invoice.invoiceNumber});
  };


  render() {
    if (!this.state.invoice || this.state.loading) {
      return <Loader />;
    }

    return <div>
      <h1 className="ui header">Invoice { this.state.invoice.invoiceNumber }</h1>
      <InvoiceForm invoice={this.state.invoice}
                   submit={this.save}
                   cancel={() => this.props.history.goBack()}

      />
    </div>
  }
}

export default connect(null, {getInvoice, saveInvoice})(EditInvoice);
