
import React, { PureComponent, ChangeEvent } from "react"
import { Button } from 'semantic-ui-react'
import { FormattedMessage, injectIntl, WrappedComponentProps } from "react-intl"
import { Customer, defaultCustomer } from "../../records"
import { useSearchcustomers } from "../../api-helper/customers"
import { SelectCustomerInput } from "../../components/customer/select-customer-input"

type InputProps = WrappedComponentProps & ProxyProps

interface ProxyProps {
  customer: Customer | null
  value: Customer | null
  onChange: (customer: Customer | null) => void
}

interface Props extends InputProps {
  searchCustomer: (value: string) => Promise<Customer[]>
}

interface State {
  customer: Customer | null
  customers: Customer[]
  foundCount: number
  name: string
  selected: boolean
  loading: boolean
}

class SelectCustomerComponent extends PureComponent<Props, State> {
  private t: any

  constructor(props: Props) {
    super(props);
    this.t = this.props.intl.formatMessage;

    this.state = {
      customer: props.customer,
      foundCount: -1,
      name: props.customer ? props.customer.name : '',
      selected: !!props.customer,
      loading: false,
      customers: []
    };
  }

  onChange = (e: ChangeEvent<HTMLInputElement>) => {
    const {name, value} = e.target
    const customer: Customer = {...this.state.customer, [name]: value} as Customer

    this.setState({customer});
    this.props.onChange(customer);
  };

  selectCustomer = (customer: Customer | null) => {
    if (customer) {
      this.setState({customer, selected: true})
    } else this.setState({name: '', customer: null, selected: false})
    this.props.onChange(customer)
  }

  setNewCustomer = () => this.selectCustomer({...defaultCustomer, name: this.state.name})

  unselectCustomer = () => {
    this.setState({customer: null, selected: false, name: ""});
    this.props.onChange(null);
  };

  setSearchTerm = (term: string, foundCount: number) => this.setState({name: term, foundCount})

  getNameLabel = (): {content: string, color: string} => {
    if (!this.state.selected) return {content: this.t({id: 'invoice.select_customer.not_selected'}), color: 'teal'};
    if (this.state.customer?.id) return {content: this.t({id: 'invoice.select_customer.selected'}), color: 'blue'};
    else return {content: this.t({id: 'invoice.select_customer.creating_new'}), color: 'yellow'};
  };

  render() {
    const nameLabel = this.getNameLabel()

    return <div className="eight wide column">
      <FormattedMessage id="customer.information">
        {(txt) => (
          <h4 className="ui horizontal divider header">
            <i className="address book icon" />
            {txt}
          </h4>
        )}
      </FormattedMessage>
      {!this.state.selected &&
        <div>
          <SelectCustomerInput selectCustomer={this.selectCustomer} searchTerm={this.setSearchTerm} />
          <div className="ui hidden divider" />
        </div>}
      <div>
        <div className="ui hidden divider" />
        {
          !!this.state.name &&
          !this.state.loading &&
          this.state.foundCount === 0 &&
          !this.state.customer && (
            <div className="ui center aligned container">
              <h4 className="ui center aligned header">
                <FormattedMessage id="invoice.select_customer.not_found"/>
              </h4>
              <Button
                type="button"
                color="blue"
                onClick={this.setNewCustomer}
              >
                <FormattedMessage id="invoice.select_customer.create_new"/>
              </Button>
            </div>
          )
        }
        {
          this.state.customer &&
            <div>
              {this.state.loading && <div className="ui active inverted dimmer">
                  <div className="ui loader"/>
              </div>}
              {!this.state.customer.id
              ? <div className="required field">
                  <FormattedMessage id="customer.company" tagName="label" />
                  <div className="ui right labeled input">
                    <input
                      type="text"
                      name="name"
                      placeholder={this.t({id: 'customer.company'})}
                      value={this.state.customer.name}
                      onChange={this.onChange}
                      autoComplete="off"
                      required
                    />
                    <div className={`ui ${nameLabel.color} label`}>{nameLabel.content}</div>
                  </div>
                </div>
              :
              <div>
                <div className="ui big label">
                  {this.state.customer.name}
                </div>
              </div>}
            <div className="ui hidden divider" />
            <div className="field">
              <FormattedMessage id="customer.additional_name" tagName="label" />
              <input type="text" name="additionalName"
                     value={this.state.customer.additionalName}
                     onChange={this.onChange}
                     autoComplete="off"
                     placeholder={this.t({id: 'customer.additional_name'})}
              />
            </div>
            <div className="required field">
              <FormattedMessage id="customer.street_name" tagName="label" />
              <input type="text" name="streetName"
                     value={this.state.customer.streetName}
                     onChange={this.onChange}
                     autoComplete="off"
                     placeholder={this.t({id: 'customer.street_name'})}
                     required
              />
            </div>
            <div className="two fields">
              <div className="required field">
                <FormattedMessage id="customer.post_code" tagName="label" />
                <input type="text"
                       name="postCode"
                       autoComplete="off"
                       value={this.state.customer.postCode}
                       onChange={this.onChange}
                       placeholder={this.t({id: 'customer.post_code'})}
                       maxLength={10}
                       required
                />
              </div>
              <div className="required field">
                <FormattedMessage id="customer.city" tagName="label" />
                <input type="text"
                       name="city"
                       autoComplete="off"
                       value={this.state.customer.city}
                       onChange={this.onChange}
                       placeholder={this.t({id: 'customer.city'})}
                       required
                />
              </div>
            </div>
            <div className="fields">
              <div className="twelve wide field">
                <FormattedMessage id="customer.email" tagName="label" />
                <input type="email"
                       name="email"
                       autoComplete="off"
                       value={this.state.customer.email}
                       onChange={this.onChange}
                       placeholder={this.t({id: 'customer.email'})}
                />
              </div>
              <div className="four wide field">
                <FormattedMessage id="customer.vat" tagName="label" />
                <input type="text"
                       name="vat"
                       autoComplete="off"
                       value={this.state.customer.vat}
                       onChange={this.onChange}
                       placeholder={this.t({id: 'customer.vat'})}
                />
              </div>
            </div>
            <div className="fields">
              <div className="eight wide field">
                <FormattedMessage id="customer.contact_person" tagName="label" />
                <input type="text"
                       name="contactPerson"
                       autoComplete="off"
                       value={this.state.customer.contactPerson}
                       onChange={this.onChange}
                       placeholder={this.t({id: 'customer.contact_person'})}
                />
              </div>
              <div className="six wide field">
              <FormattedMessage id="customer.phone" tagName="label" />
              <input type="text"
                     name="phone"
                     autoComplete="off"
                     value={this.state.customer.phone}
                     onChange={this.onChange}
                     placeholder={this.t({id: 'customer.phone'})}
              /></div>
            </div>
            {this.state.selected && <Button
              type='button'
              color="red"
              onClick={this.unselectCustomer}
            >
              <FormattedMessage id="invoice.select_customer.remove_selection" />
            </Button>}
          </div>
        }
      </div>
    </div>
  }
}

const InjectedComponent = injectIntl(SelectCustomerComponent)

export function SelectCustomer(props: ProxyProps) {
  const searchcustomers = useSearchcustomers()

  return <InjectedComponent {...props} searchCustomer={searchcustomers} />
}
