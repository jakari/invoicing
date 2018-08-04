
import {connect} from 'react-redux';
import React, {Component} from 'react';
import { Input, Button } from 'semantic-ui-react';
import {searchCustomer} from "../utilities/customer";
import {CustomerRecord} from "../records";


class SelectCustoemr extends Component {
  timeout = null;

  constructor(props) {
    super(props);

    this.state = {
      customer: props.customer,
      name: props.customer ? props.customer.name : '',
      selected: !!props.customer,
      loading: false
    };
  }

  onSearch = async e => {
    const name = e.target.value;
    let customer = null;
    let loading = true;

    if (e.target.value.length > 0) {
      if (this.timeout) {
        clearTimeout(this.timeout);
      }
      this.timeout = setTimeout(async () => {
        try {
          customer = await this.props.searchCustomer(name);
          this.setCustomerState(customer);
        } catch (e) {
          this.setNewCustomer();
        } finally {
          // Timeout might have been cleared by quick blurring etc. Check that
          // async is not obsolete
          if (this.timeout) {
            this.timeout = null;
          }
        }
      }, 150);
    } else {
      loading = false;
    }

    this.setState({name, loading});
  };

  onChange = e => {
    const {name, value} = e.target;
    const customer = this.state.customer.set(name, value);

    this.setState({customer});
    this.props.onChange(customer);
  };
  onNameSearchKeyPress = e => {
    if (e.charCode === 13) {
      e.preventDefault();
      e.target.blur();
    }
  };

  setNewCustomer = () => {
    this.setCustomerState(new CustomerRecord({name: this.state.name}));
  };

  setCustomerState = (customer) => {
    this.setState({customer, loading: false});
    this.props.onChange(customer);
  };

  onNameBlur = () => {
    if (this.timeout) {
      clearTimeout(this.timeout);
      this.timeout = null;
    }

    let selected = false;

    if (!this.state.customer && this.state.name) {
      selected = true;
      this.setNewCustomer();
    } else if (this.state.customer) {
      selected = true;
      this.setCustomerState(this.state.customer);
    }

    this.setState({selected: selected, name: ''});
  };

  unselectCustomer = () => {
    this.setState({customer: null, selected: false});
    this.props.onChange(null);
  };

  getNameLabel = () => {
    if (!this.state.selected) return {content: 'Not selected', color: 'teal'};
    if (this.state.customer.id) return {content: 'Selected', color: 'blue'};
    else return {content: 'Creating new customer', color: 'yellow'};
  };

  render() {
    return <div className="eight wide column">
      <h3 className="ui horizontal divider header">
        <i className="address book icon" />
        Customer information
      </h3>
        {!this.state.selected &&
          <div>
            <Input
              fluid
              size="huge"
              icon='search'
              name="name-search"
              loading={this.state.loading}
              placeholder='Customer name'
              value={this.state.name}
              onChange={this.onSearch}
              onBlur={this.onNameBlur}
              onKeyPress={this.onNameSearchKeyPress}
            />
            <div className="ui hidden divider" />
          </div>}
      <div>
        <div className="ui hidden divider" />
        {
          !!this.state.name &&
          !this.state.loading &&
          !this.state.customer &&
          <h4 className="ui center aligned header">Customer not found</h4>
        }
        {
          this.state.customer &&
          <div>
            {this.state.loading && <div className="ui active inverted dimmer">
              <div className="ui loader" />
            </div>}
            {!this.state.customer.id
              ? <div className="required field">
                  <label>Name</label>
                  <Input
                    name="name"
                    label={this.getNameLabel()}
                    labelPosition='right'
                    placeholder='Customer name'
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
            <div className="required field">
              <label>Street name</label>
              <input type="text" name="streetName"
                     value={this.state.customer.streetName} onChange={this.onChange}
                     placeholder="Street name" required/>
            </div>
            <div className="two fields">
              <div className="required field">
                <label>Post code</label>
                <input type="text" name="postCode" value={this.state.customer.postCode}
                       onChange={this.onChange} placeholder="Postcode" required/>
              </div>
              <div className="field">
                <label>City</label>
                <input type="text" name="city" value={this.state.customer.city}
                       onChange={this.onChange} placeholder="city" required/>
              </div>
            </div>
            <div className="required field">
              <label>Customer email</label>
              <input type="email" name="email" value={this.state.customer.email} onChange={this.onChange} placeholder="Customer email" required/>
            </div>
            {this.state.selected && <Button
              type='button'
              color="red"
              onClick={this.unselectCustomer}
            >
              Remove selection
            </Button>}
          </div>
        }
      </div>
    </div>;
  }
}
export default connect(null, {searchCustomer})(SelectCustoemr);
