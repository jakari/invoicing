import React, {Component} from 'react';
import {InvoiceItemRecord} from "../records";
import {recalcInvoice, recalcItem} from "../utilities/invoice";
import {Button} from 'semantic-ui-react';
import SelectCustomer from '../containers/select-customer';

export default class InvoiceForm extends Component {
  constructor(props) {
    super(props);

    this.state = {invoice: props.invoice};
  }

  submit = e => {
    e.preventDefault();
    this.props.submit(this.state.invoice);
  }

  changeInvoice = e => this.setState({
    invoice: this.state.invoice.set(e.target.name, e.target.value)
  });
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

  render() {
    const {
      customer,
      created,
      due,
      items
    } = this.state.invoice;

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
                <div onClick={this.removeItem(key)} className="ui mini red button">Remove</div>
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
            <h4 className="ui right floated header">Total</h4>
          </th>
          <th className="footer value">
            { this.state.invoice.total }
          </th>
        </tr>
        </tfoot>
      </table>
      {this.areItemsEmpty() && <div className="ui red basic label">
        At least 1 invoice item should be added to be able to create a invoice.
      </div>}
      <div>
        <Button
          type="submit"
          primary
          disabled={!customer || this.areItemsEmpty()}>
          Save
        </Button>
      </div>
    </form>;
  }
}
