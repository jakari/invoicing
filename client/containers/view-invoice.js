
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import {getInvoice, sendInvoiceEmail} from "actions/invoices";
import {Link} from 'react-router-dom';
import {Button} from 'semantic-ui-react';
import Loader from 'components/loader';
import {FormattedMessage} from "react-intl";

class ViewInvoice extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: null};
  }

  async componentDidMount() {
    const invoice = await this.props.getInvoice(this.props.match.params.invoice);
    this.setState({invoice});
  }

  sendEmail = () => this.props.sendInvoiceEmail(this.state.invoice);

  render() {
    const {invoice} = this.state;

    if (!invoice) {
      return <Loader />;
    }

    return <div>
      <h1 className="ui header">
        <FormattedMessage id="invoice.view.header" values={{invoiceNumber: invoice.invoiceNumber}} />
      </h1>
      <div className="ui stackable grid">
        <div className="eight wide column">
          <FormattedMessage id="invoice.customer_information">
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
              <td><FormattedMessage id="customer.name" /></td>
              <td>{ invoice.customer.name }</td>
            </tr>
            <tr>
              <td><FormattedMessage id="customer.address" /></td>
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
              <td><FormattedMessage id="invoice.hesitation_cost" /></td>
              <td>{ invoice.hesitationCostOfInterest }</td>
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
            <td className="price">{ item.price }</td>
            <td className="tax">{ item.tax }</td>
            <td className="total">{ item.total }</td>
          </tr>))}
        </tbody>
        <tfoot>
        <tr>
          <th colSpan="4" className="footer header">
            <h4 className="ui right floated header">
              <FormattedMessage id="invoice.total" />
            </h4>
          </th>
          <th className="footer value">{ invoice.total }</th>
        </tr>
        </tfoot>
      </table>

      <div className="ui hidden divider" />

      <a href={`/api/invoice/${this.state.invoice.invoiceNumber}/print_pdf`} target="_blank">
        <Button primary>
          <FormattedMessage id="invoice.print" />
        </Button>
      </a>
      <Button primary onClick={this.sendEmail}>
        <FormattedMessage id="invoice.send_email" />
      </Button>
      <Link to={`/invoice/${invoice.invoiceNumber}/edit`}>
        <Button><FormattedMessage id="invoice.edit" /></Button>
      </Link>
  </div>;
  }
}

export default connect(null, {getInvoice, sendInvoiceEmail})(ViewInvoice);