
import React from 'react';
import {FormattedMessage} from "react-intl";
import {connect} from "react-redux";
import {getCustomers} from "actions/customers";

class ListCustomersComponent extends React.Component {
  constructor(props) {
    super(props);

    this.state = {customers: []}
  }

  componentDidMount = () => this.props.getCustomers().then(customers => this.setState({customers}));

  render() {
    return <div>
      <h1 className="ui header">
        <FormattedMessage id="customers.list" />
      </h1>
      <table className="ui basic selectable small table">
        <tbody>
        {this.state.customers.map(customer => (
          <tr key={customer.id}>
            <td onClick={() => {this.props.history.push(`/customer/${customer.id}`)}}>{customer.name}</td>
          </tr>
        ))}
        </tbody>
      </table>
    </div>
  }
}

export const ListCustomers = connect(null, {getCustomers})(ListCustomersComponent)
