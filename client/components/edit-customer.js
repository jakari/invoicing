
import React from 'react';
import {FormattedMessage} from "react-intl";
import {connect} from "react-redux";
import {getCustomer} from "actions/customers";
import {CustomerRecord} from "../records";

class ViewCustomerComponent extends React.Component {
  constructor(props) {
    super(props);

    this.state = {customer: null}
  }

  componentDidMount = () => {
    const {getCustomer, match} = this.props;
    getCustomer(match.params.customerId).then(customer => this.setState({customer: new CustomerRecord(customer)}));
  }

  changeInvoice = e => {
    const value = typeof e.target.value === 'string' && !e.target.value
      ? null
      : e.target.value;

    this.setState({
      customer: this.state.customer.set(e.target.name, value)
    });
  };

  render() {
    const {customer} = this.state;

    return <div>
      {customer
        ? <div>
          <h1 className="ui header">Asiakastiedot</h1>
          <div className="ui divider" />
          <div className="ui form">
            <div className="field">
              <label><FormattedMessage id="customer.company" /></label>
              <input type="text" value={customer.name} name="name" />
            </div>
            <div className="field">
              <label><FormattedMessage id="customer.email" /></label>
              <input type="text" value={customer.email} name="email" />
            </div>
            <div className="field">
              <label><FormattedMessage id="customer.vat" /></label>
              <input type="text" value={customer.vat} name="vat" />
            </div>
            <div className="field">
              <label><FormattedMessage id="customer.phone" /></label>
              <input type="text" value={customer.phone} name="phone" />
            </div>
            <div className="field">
              <label><FormattedMessage id="customer.street_name" /></label>
              <input type="text" value={customer.streetName} name="streetName" />
            </div>
            <div className="field">
              <label><FormattedMessage id="customer.post_code" /></label>
              <input type="text" value={customer.postCode} name="postCode" />
            </div>
            <div className="field">
              <label><FormattedMessage id="customer.city" /></label>
              <input type="text" value={customer.city} name="city" />
            </div>
          </div>
        </div>
        : <div>Ladataan...</div>
      }
    </div>
  }
}

export const EditCustomer = connect(null, {getCustomer})(ViewCustomerComponent)
