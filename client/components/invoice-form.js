import React, {Component} from 'react';
import {InvoiceItemRecord} from "../records";
import {connect} from 'react-redux';
import {recalcInvoice, recalcItem} from "../utilities/invoice";
import {Button, Dropdown} from 'semantic-ui-react';
import SelectCustomer from '../containers/select-customer';
import Loader from './loader';
import NumberInput from "./number-input";
import Input from "./ui/input";
import {FormattedMessage, injectIntl} from "react-intl";

class InvoiceForm extends Component {
  constructor(props) {
    super(props);

    this.state = { invoice: props.invoice, loading: false};
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

  onTemplateChange = (_, data) => {
    this.setState({invoice: this.state.invoice.set("template", data.value)});
  }

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
  onPlainInputChangeValue = index => e => {
    const {name, value} = e.target;

    if (!e.target.checkValidity()) {
        return;
    }

    this.onChangeValue(index)(name)(value);
  };
  onChangeValue = index => name => value => {
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
      customerReference,
      template
    } = this.state.invoice;

    const {formatMessage} = this.props.intl;

    if (this.state.loading) {
      return <Loader />;
    }

    const templates = this.props.templates
      .map(template => ({text: template.title, value: template.name}));

    return <form onSubmit={this.submit} autoComplete="nope">
      <div className="ui form stackable grid">
        <Dropdown
          placeholder={formatMessage({id: 'invoice.select_template'})}
          name="template"
          fluid
          selection
          required
          options={templates}
          value={template}
          onChange={this.onTemplateChange}
        />
        <SelectCustomer
          customer={this.state.invoice.customer}
          onChange={this.changeCustomer}
        />
        <div className="ui eight wide column">
          <FormattedMessage id="invoice.information">
            {(txt) => (
              <h4 className="ui horizontal divider header">
                <i className="address book icon" />
                {txt}
              </h4>
            )}
          </FormattedMessage>
          <div className="two fields">
            <div className="required field">
              <FormattedMessage id="invoice.created" tagName="label" />
              <input
                type="date"
                name="created"
                value={created}
                onChange={this.changeInvoice}
                required
              />
            </div>
            <div className="required field">
              <FormattedMessage id="invoice.due" tagName="label" />
              <input
                type="date"
                name="due"
                value={due}
                onChange={this.changeInvoice}
                required
              />
            </div>
          </div>
          <div className="two fields">
            <div className="field">
              <FormattedMessage id="invoice.delivery" tagName="label" />
              <input type="text"
                     name="delivery" value={delivery || ''}
                     onChange={this.changeInvoice}
                     placeholder={formatMessage({id: 'invoice.delivery'})}
                     max="255"
              />
            </div>
            <div className="required field">
              <FormattedMessage id="invoice.remarking_time" tagName="label" />
              <div className="ui right labeled input">
                <input type="number"
                       name="remarkingTime"
                       value={remarkingTime}
                       onChange={this.changeInvoice}
                       placeholder={formatMessage({id: 'invoice.remarking_time'})}
                       required
                       pattern="\d+"
                       min="0"
                       max="360" />
                <FormattedMessage id="invoice.remarking_time_days_suffix">
                  {(txt) => (
                    <div className="ui basic label">{txt}</div>
                  )}
                </FormattedMessage>
              </div>
            </div>
          </div>
          <div className="two fields">
            <div className="required field">
              <FormattedMessage id="invoice.hesitation_cost" tagName="label" />
              <div className="ui right labeled input">
                  <input type="number"
                         name="hesitationCostOfInterest"
                         value={hesitationCostOfInterest}
                         onChange={this.changeInvoice}
                         placeholder={formatMessage({id: 'invoice.hesitation_cost'})}
                         required
                         pattern="\d+"
                         min="0"
                         max="100" />
                <div className="ui basic label">%</div>
              </div>
            </div>
            <div className="field">
              <FormattedMessage id="invoice.conditions_of_payment" tagName="label" />
              <input type="text"
                     name="conditionsOfPayment"
                     value={conditionsOfPayment || ''}
                     onChange={this.changeInvoice}
                     placeholder={formatMessage({id: 'invoice.conditions_of_payment'})}
                     max="255" />
            </div>
          </div>
          <div className="field">
            <FormattedMessage id="invoice.customer_reference" tagName="label" />
            <input type="text"
                   name="customerReference"
                   value={customerReference || ''}
                   onChange={this.changeInvoice}
                   placeholder={formatMessage({id: 'invoice.customer_reference'})}
            />
          </div>
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
        {items.map((item, key) => {
          const valueChanger = this.onChangeValue(key);
          const plainInputValueChanger = this.onPlainInputChangeValue(key);

          return <tr key={key}>
            <td>
              <div className="ui fluid transparent action input">
                <input
                  name="description"
                  required
                  onChange={plainInputValueChanger}
                  value={item.description}
                  className="ui input"
                  type="text"
                />
                <div onClick={this.removeItem(key)} className="ui compact mini red icon button">
                  <i className="trash alternate icon" />
                </div>
              </div>
            </td>
            <td className="amount">
              <div className="ui fluid transparent input">
                <input name="amount" required onChange={plainInputValueChanger} value={item.amount} className="ui input" type="text" pattern="\d+" />
              </div>
            </td>
            <td className="price">
              <div className="ui fluid transparent input">
                  <NumberInput required onValueChange={valueChanger('price')} value={item.price} customInput={Input} />
              </div>
            </td>
            <td className="tax">
              <div className="ui fluid transparent input">
                <input name="tax" required onChange={plainInputValueChanger} value={item.tax} className="ui input" type="text" pattern="\d+" />
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
              <FormattedMessage id="invoice.form.add_item" />
            </button>
            {this.areItemsEmpty() && <div className="ui left pointing red label">
              <FormattedMessage id="invoice.form.error.at_least_one_item_required" />
            </div>}
            <h4 className="ui right floated header">
              <FormattedMessage id="invoice.total" />
            </h4>
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
          disabled={!customer || this.areItemsEmpty()}
        >
          <FormattedMessage id="invoice.form.save" />
        </Button>
        <Button
          type="button"
          onClick={this.cancel}
        >
          <FormattedMessage id="invoice.form.cancel" />
        </Button>
      </div>
    </form>;
  }
}

export default connect(
  state => ({
    templates: state.invoices.templates
  })
)(injectIntl(InvoiceForm));
