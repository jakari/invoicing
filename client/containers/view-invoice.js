
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import {getInvoice} from "actions/invoices";
import {Link} from 'react-router-dom';
import {Button} from 'semantic-ui-react';
import Loader from 'components/loader';

class ViewInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: null};
  }

  async componentDidMount() {
    const invoice = await this.props.getInvoice(this.props.match.params.invoice);
    this.setState({invoice});
  }

  render() {
    const {invoice} = this.state;

    if (!invoice) {
      return <Loader />;
    }

    return <div>
      <h1 className="ui header">Invoice { invoice.invoiceNumber }</h1>
      <div className="ui stackable grid">
        <div className="eight wide column">
          <h4 className="ui horizontal divider header">
            <i className="bar chart icon" />
            Customer information
          </h4>
          <table className="ui definition table">
            <tbody>
            <tr>
              <td>Name</td>
              <td>{ invoice.customer.name }</td>
            </tr>
            <tr>
              <td>Customer address</td>
              <td>
                { invoice.customer.streetName }<br />
                { invoice.customer.postCode }<br />
                { invoice.customer.city }<br />
                { invoice.customer.email }
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div className="eight wide column">
          <h4 className="ui horizontal divider header">
            <i className="bar chart icon" />
            Invoice information
          </h4>
          <table className="ui definition table">
            <tbody>
            <tr>
              <td>Invoice number</td>
              <td>{ invoice.invoiceNumber }</td>
            </tr>
            <tr>
              <td>Reference number</td>
              <td>{ invoice.referenceNumber }</td>
            </tr>
            <tr>
              <td>Invoice date</td>
              <td>{ invoice.created }</td>
            </tr>
            <tr>
              <td>Invoice due</td>
              <td>{ invoice.due }</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
      <table className="ui celled invoice items table">
        <thead>
        <tr>
          <th>Item name</th>
          <th>Amount</th>
          <th>Price</th>
          <th>Tax %</th>
          <th>Total</th>
        </tr>
        </thead>
        <tbody>
        {invoice.items.map((item, key) => (<tr key={key}>
            <td>{ item.description }</td>
            <td className="amount">{ item.amount }</td>
            <td className="price">{ item.price }</td>
            <td className="tax">{ item.tax }</td>
            <td className="total">{ item.total }</td>
          </tr>))}
        </tbody>
        <tfoot>
        <tr>
          <th colSpan="4" className="footer header">
            <h4 className="ui right floated header">Total</h4>
          </th>
          <th className="footer value">{ invoice.total }</th>
        </tr>
        </tfoot>
      </table>

      <div className="ui hidden divider" />

      <Link to={`/invoice/${invoice.invoiceNumber}/edit`}>
        <Button color="red">Edit</Button>
      </Link>
  </div>;
  }
}

export default connect(null, {getInvoice})(ViewInvoice);