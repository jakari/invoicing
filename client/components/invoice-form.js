import React, {Component} from 'react';
import {InvoiceItemRecord} from "../records";
import {recalcInvoice, recalcItem} from "../utilities/invoice";
import {Button} from 'semantic-ui-react';
import SelectCustomer from '../containers/select-customer';
import Loader from './loader';

export default class InvoiceForm extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: props.invoice, loading: false};
  }

  submit = e => {
    e.preventDefault();
    this.setState({loading: true});
    this.props.submit(this.state.invoice);
  }

  changeInvoice = e => {
    const value = typeof e.target.value === 'string' && !e.target.value
      ? null
      : e.target.value;

    this.setState({
      invoice: this.state.invoice.set(e.target.name, value)
    });
  };

  changeCustomer = customer =>
    this.setState({invoice: this.state.invoice.set('customer', customer)});

  addItem = () => this.setState({
    invoice: this.state.invoice.set(
      'items',
      this.state.invoice.items.push(new InvoiceItemRecord())
    )
  });
  removeItem = index => () => this.setState({
    invoice: this.state.invoice.set(
      'items',
      this.state.invoice.items.remove(index)
    )
  });
  changeItem = index => e => {
    const {name, value} = e.target;

    if (!e.target.checkValidity()) {
      return;
    }

    const item = this.state.invoice.items.get(index).set(name, value);
    const items = this.state.invoice.items.set(index, recalcItem(name, item));

    this.setState({
      invoice: recalcInvoice(name, this.state.invoice.set('items', items))
    });
  };

  areItemsEmpty = () => this.state.invoice.items.size === 0;

  cancel = () => {
    if (confirm('Are you sure you want to cancel?')) {
      this.props.cancel();
    }
  };

  render() {
    const {
      customer,
      created,
      due,
      items,
      delivery,
      remarkingTime,
      hesitationCostOfInterest,
      conditionsOfPayment,
      customerReference
    } = this.state.invoice;

    if (this.state.loading) {
      return <Loader />;
    }

    return <form onSubmit={this.submit} autoComplete="nope">
      <div className="ui form stackable grid">
        <SelectCustomer
          customer={this.state.invoice.customer}
          onChange={this.changeCustomer}
        />
        <div className="ui eight wide column">
          <h3 className="ui horizontal divider header">
            <i className="bar chart icon" />
            Invoice information
          </h3>
          <div className="two fields">
            <div className="required field">
              <label>Invoice date</label>
              <input type="date" name="created" value={created} onChange={this.changeInvoice} placeholder="created" required />
            </div>
            <div className="required field">
              <label>Invoice due</label>
              <input type="date" name="due" value={due} onChange={this.changeInvoice} placeholder="due" required />
            </div>
          </div>
          <div className="two fields">
            <div className="field">
              <label>Delivery</label>
              <input type="text"
                     name="delivery" value={delivery || ''}
                     onChange={this.changeInvoice}
                     placeholder="Delivery" max="255"
              />
            </div>
            <div className="required field">
              <label>Remarking time</label>
              <div className="ui labeled input">
                <input type="number"
                       name="remarkingTime"
                       value={remarkingTime}
                       onChange={this.changeInvoice}
                       placeholder="Remarking time"
                       required
                       pattern="\d+"
                       min="0"
                       max="360" />
                <div className="ui basic label">days</div>
              </div>
            </div>
          </div>
          <div className="two fields">
            <div className="required field">
              <label>Hesitation cost of interest</label>
              <div className="ui right labeled input">
                  <input type="number"
                         name="hesitationCostOfInterest"
                         value={hesitationCostOfInterest}
                         onChange={this.changeInvoice}
                         placeholder="Hesitation cost of interest"
                         required
                         pattern="\d+"
                         min="0"
                         max="100" />
                <div className="ui basic label">%</div>
              </div>
            </div>
            <div className="field">
              <label>Conditions of payment</label>
              <input type="text"
                     name="conditionsOfPayment"
                     value={conditionsOfPayment || ''}
                     onChange={this.changeInvoice}
                     placeholder="Conditions of payment"
                     max="255" />
            </div>
          </div>
          <div className="field">
            <label>Customer reference</label>
            <input type="text" name="customerReference" value={customerReference || ''} onChange={this.changeInvoice} placeholder="Customer reference" />
          </div>
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
        {items.map((item, key) => {
          const changer = this.changeItem(key);

          return <tr key={key}>
            <td>
              <div className="ui fluid transparent action input">
                <input name="description" required onChange={changer} value={item.description} className="ui input" type="text" />
                <div onClick={this.removeItem(key)} className="ui compact mini red icon button">
                  <i className="trash alternate icon" />
                </div>
              </div>
            </td>
            <td className="amount">
              <div className="ui fluid transparent input">
                <input name="amount" required onChange={changer} value={item.amount} className="ui input" type="text" pattern="\d+" />
              </div>
            </td>
            <td className="price">
              <div className="ui fluid transparent input">
                <input name="price" onChange={changer} value={item.price} className="ui input" type="text" />
              </div>
            </td>
            <td className="tax">
              <div className="ui fluid transparent input">
                <input name="tax" required onChange={changer} value={item.tax} className="ui input" type="text" pattern="\d+" />
              </div>
            </td>
            <td className="total">{item.total.toFixed(2)}</td>
          </tr>;
        })}
        </tbody>
        <tfoot>
        <tr>
          <th colSpan="4" className="footer header">
            <button onClick={this.addItem} type="button" className="ui mini left floated button">
              Add item
            </button>
            {this.areItemsEmpty() && <div className="ui left pointing red label">
              At least 1 invoice item should be added to be able to create a invoice.
            </div>}
            <h4 className="ui right floated header">Total</h4>
          </th>
          <th className="footer value">
            { this.state.invoice.total }
          </th>
        </tr>
        </tfoot>
      </table>
      <div>
        <Button
          type="submit"
          primary
          disabled={!customer || this.areItemsEmpty()}>
          Save
        </Button>
        <Button
          type="button"
          onClick={this.cancel}>
          Cancel
        </Button>
      </div>
    </form>;
  }
}
