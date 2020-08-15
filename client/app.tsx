
import React, {Component} from 'react'
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom'
import AuthRoute from 'components/auth-route'
import ListInvoices from 'containers/list-invoices'
import EditInvoice from 'containers/edit-invoice'
import CreateInvoice from 'containers/create-invoice'
import Login from 'containers/login'
import 'style.css'
import ViewInvoice from 'containers/view-invoice'
import {getSettings} from "actions/invoices"
import { connect, ConnectedProps } from "react-redux"
import 'app.sass'
import Loader from 'components/loader'
import Nav from 'components/nav'
import messages from 'i18n/fi'
import {IntlProvider} from 'react-intl'
import {SelectCompany} from "./containers/select-company"
import {ListCustomers} from "./components/list-customers"
import {ViewCustomer} from "./components/view-customer"

type Props = ConnectedProps<typeof connected>

const RootApp = React.memo(({isCompanySelected}: {isCompanySelected: boolean}) => (
  isCompanySelected
    ? <Switch>
      <AuthRoute exact path="/invoice/:invoice/edit" component={EditInvoice} />
      <AuthRoute exact path="/invoice/create" component={CreateInvoice} />
      <AuthRoute exact path="/invoice/:invoice" component={ViewInvoice} />
      <AuthRoute exact path="/customers" component={ListCustomers} />
      <AuthRoute exact path="/customer/:customerId" component={ViewCustomer} />
      <AuthRoute exact path="/" component={ListInvoices} />
    </Switch>
    : <SelectCompany/>
))

class Root extends Component<Props> {
  constructor(props: Props) {
    super(props);

    this.props.getSettings();
  }

  render() {
    return <IntlProvider locale={'fi'} messages={messages}>
      {this.renderApp()}
      </IntlProvider>;
  }

  renderApp() {
    if (!this.props.authenticated) {
      return <Login />
    }

    return <Router>
      {this.props.loading && <Loader />}
        <div style={{display: this.props.loading ? 'none' : ''}}>
        {/* Wrap Nav to pathless Route to prevent render blocking */}
        <Route component={Nav}/>
        <div className="ui container">
          <RootApp isCompanySelected={this.props.isCompanySelected} />
        </div>
      </div>
    </Router>
  }
}

const connected = connect(
  (state: any) => ({
    authenticated: state.auth.isAuthenticated,
    loading: state.util.loading,
    isCompanySelected: state.invoices.selectedCompany !== undefined
  }),
  {getSettings}
)

export default connected(Root)
