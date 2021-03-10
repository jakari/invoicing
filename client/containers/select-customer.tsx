
import React, { PureComponent, KeyboardEvent, ChangeEvent } from "react"
import { Input, Button } from 'semantic-ui-react';
import { FormattedMessage, injectIntl, WrappedComponentProps } from "react-intl"
import { Customer, defaultCustomer } from "records"
import { useSearchcustomers } from "api/customers"

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
  name: string
  selected: boolean
  loading: boolean
}

class SelectCustomerComponent extends PureComponent<Props, State> {
  timeout: any = null
  private t: any

  constructor(props: Props) {
    super(props);
    this.t = this.props.intl.formatMessage;

    this.state = {
      customer: props.customer,
      name: props.customer ? props.customer.name : '',
      selected: !!props.customer,
      loading: false,
      customers: []
    };
  }

  onSearch = async (e: ChangeEvent<HTMLInputElement>) => {
    const name = e.target.value;

    if (e.target.value.length > 0) {
      this.setState({loading: true})

      if (this.timeout) {
        clearTimeout(this.timeout)
      }
      this.timeout = setTimeout(async () => {
        try {
          const customers = await this.props.searchCustomer(name)
          this.setState({customers})
        } catch (e) {
        }

        this.setState({loading: false})

        // Timeout might have been cleared by quick blurring etc. Check that
        // async is not obsolete
        if (this.timeout) {
          this.timeout = null;
        }

        // this.setState({loading: false})
      }, 150);
    }

    this.setState({name});
  };

  onChange = (e: ChangeEvent<HTMLInputElement>) => {
    const {name, value} = e.target
    const customer: Customer = {...this.state.customer, [name]: value} as Customer

    this.setState({customer});
    this.props.onChange(customer);
  };
  onNameSearchKeyPress = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.charCode === 13) {
      e.preventDefault();
      (e.target as any)?.blur();
    }
  };

  selectCustomer = (customer: Customer) => {
    this.setState({customer, selected: true});
    this.props.onChange(customer);
  }

  setNewCustomer = () => this.selectCustomer({...defaultCustomer, name: this.state.name})

  onNameBlur = () => {
    if (this.timeout) {
      clearTimeout(this.timeout);
      this.timeout = null;
    }

    // Dirty hack to allow an eventual click in the result field before actual blur
    setTimeout(() => {
      if (!this.state.customer && this.state.name) this.setNewCustomer();
      else this.setState({name: '', customers: []})
    }, 100)
  };

  unselectCustomer = () => {
    this.setState({customer: null, selected: false});
    this.props.onChange(null);
  };

  getNameLabel = () => {
    if (!this.state.selected) return {content: this.t({id: 'invoice.select_customer.not_selected'}), color: 'teal'};
    if (this.state.customer?.id) return {content: this.t({id: 'invoice.select_customer.selected'}), color: 'blue'};
    else return {content: this.t({id: 'invoice.select_customer.create_new'}), color: 'yellow'};
  };

  render() {
    const {formatMessage: t} = this.props.intl
    const {customers, loading} = this.state

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
          <div className={"ui fluid search" + (loading ? " loading double" : "")}>
            <div className="ui fluid huge icon input">
              <input
                className="prompt"
                type="text"
                placeholder={t({id: 'invoice.select_customer.customer_name'})}
                value={this.state.name}
                onChange={this.onSearch}
                onKeyPress={this.onNameSearchKeyPress}
                autoComplete="nope"
                onBlur={this.onNameBlur}
              />
              <i className="search icon" />
            </div>
            {
              !!customers.length
                && <div className="results transition visible">
                  {customers.map(customer => (
                    <a key={"customer-" + customer.id} className="result" onClick={() => this.selectCustomer(customer)}>
                      <div className="content">
                        <div className="title">{customer.name}</div>
                        {customer.additionalName && <div className="description">{customer.additionalName}</div>}
                        <div className="description">{customer.streetName}</div>
                        <div className="description">{customer.postCode} {customer.city}</div>
                        <div className="description">{customer.phone}</div>
                      </div>
                    </a>
                  ))}
                </div>
            }
          </div>
          <div className="ui hidden divider" />
        </div>}
      <div>
        <div className="ui hidden divider" />
        {
          !!this.state.name &&
          !this.state.loading &&
          !this.state.customer &&
          <h4 className="ui center aligned header">
            <FormattedMessage id="invoice.select_customer.not_found" />
          </h4>
        }
        {
          this.state.customer &&
          <div>
            {this.state.loading && <div className="ui active inverted dimmer">
              <div className="ui loader" />
            </div>}
            {!this.state.customer.id
              ? <div className="required field">
                  <FormattedMessage id="customer.company" tagName="label" />
                  <Input
                    name="name"
                    label={this.getNameLabel()}
                    labelPosition='right'
                    placeholder={this.t({id: 'customer.company'})}
                    value={this.state.customer.name}
                    onChange={this.onChange}
                    required
                  />
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
              <div className="required six wide field">
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
