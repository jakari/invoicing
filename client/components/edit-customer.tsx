
import React, { ChangeEvent, FormEvent } from "react"
import { FormattedMessage, injectIntl, WrappedComponentProps } from "react-intl"
import { connect, ConnectedProps } from "react-redux"
import { getCustomer, saveCustomer } from "actions/customers"
import { Customer, defaultCustomer } from "../records"
import { RouteComponentProps } from "react-router"

type Props = ConnectedProps<typeof connector> & RouteComponentProps<{customerId: string}> & WrappedComponentProps
interface State {
  customer: Customer
  loaded: boolean
}

class ViewCustomerComponent extends React.PureComponent<Props, State> {
  constructor(props: Props) {
    super(props)

    this.state = {customer: defaultCustomer, loaded: false}
  }

  componentDidMount = () => {
    const {getCustomer, match} = this.props
    getCustomer(match.params.customerId).then(customer => this.setState({customer}))
  }

  changeCustomer = (e: ChangeEvent<HTMLInputElement>) => this.setState({
    customer: {
      ...this.state.customer,
      [e.target.name as keyof Customer]: e.target.value
    }
  })

  private cancel = () => this.props.history.goBack()
  private submit = async (e: FormEvent) => {
    e.preventDefault()
    await this.props.saveCustomer(this.state.customer)
    this.props.history.push(`/customer/${this.state.customer.id}`)
  }

  render() {
    const {customer} = this.state;

    return <div>
      {customer
        ? <div>
          <h1 className="ui header">Asiakastiedot</h1>
          <div className="ui divider" />
          <form onSubmit={this.submit}>
            <div className="ui form">
              <div className="ui stackable grid">
                <div className="eight wide column">
                  <div className="field">
                    <label><FormattedMessage id="customer.company" /></label>
                    <input type="text" value={customer.name} name="name" onChange={this.changeCustomer} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.email" /></label>
                    <input type="text" value={customer.email} name="email" onChange={this.changeCustomer} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.vat" /></label>
                    <input type="text" value={customer.vat} name="vat" onChange={this.changeCustomer} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.phone" /></label>
                    <input type="text" value={customer.phone} name="phone" onChange={this.changeCustomer} />
                  </div>
                </div>
                <div className="eight wide column">
                  <div className="field">
                    <label><FormattedMessage id="customer.street_name" /></label>
                    <input type="text" value={customer.streetName} name="streetName" onChange={this.changeCustomer} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.post_code" /></label>
                    <input type="text" value={customer.postCode} name="postCode" onChange={this.changeCustomer} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.city" /></label>
                    <input type="text" value={customer.city} name="city" onChange={this.changeCustomer} />
                  </div>
                </div>
              </div>
              <div className="ui hidden divider" />
              <button className="ui button primary"><FormattedMessage id="button.save" /></button>
              <button  className="ui button" onClick={this.cancel} type="button"><FormattedMessage id="button.cancel" /></button>
            </div>
          </form>
        </div>
        : <div>Ladataan...</div>
      }
    </div>
  }
}

const connector = connect(null, {getCustomer, saveCustomer})
export const EditCustomer = connector(injectIntl(ViewCustomerComponent))
