
import React, { useEffect } from "react"
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom'
import { ListInvoices, CreateInvoice, EditInvoice } from 'containers/invoice'
import Login from 'containers/login'
import 'style.css'
import { ViewInvoice } from 'containers/view-invoice'
import 'app.sass'
import Loader from 'components/loader'
import { Nav } from 'components/nav'
import messages from 'i18n/fi'
import {IntlProvider} from 'react-intl'
import {SelectCompany} from "./containers/select-company"
import {ListCustomers} from "./components/list-customers"
import {ViewCustomer} from "./components/view-customer"
import { EditCustomer } from "components/edit-customer"
import { RecoilRoot, useRecoilValue } from "recoil"
import { isAuthenticatedState } from "state/auth"
import { isCompanySelectedValue } from "state/user-settings"
import { mainLoadingState } from "state/atoms"
import { fetchUserSettings } from "api/user"

const RootApp = () => {
  const isCompanySelected = isCompanySelectedValue()

  return (
    isCompanySelected
      ? <Switch>
        <Route exact path="/invoice/:invoice/edit" component={EditInvoice} />
        <Route exact path="/invoice/create" component={CreateInvoice} />
        <Route exact path="/invoice/:invoice" component={ViewInvoice} />
        <Route exact path="/customers" component={ListCustomers} />
        <Route exact path="/customer/:customerId" component={ViewCustomer} />
        <Route exact path="/customer/:customerId/edit" component={EditCustomer} />
        <Route exact path="/" component={ListInvoices} />
      </Switch>
      : <SelectCompany />
  )
}

function RecoilRootComponent() {
  return (
    <RecoilRoot>
      <Root />
    </RecoilRoot>
  )
}

function Root() {
  const getSettings = fetchUserSettings()
  useEffect(() => {
    getSettings()
  }, [])

  const isAuthenticated = useRecoilValue(isAuthenticatedState)
  const isLoading = useRecoilValue(mainLoadingState)

  return <IntlProvider locale={'fi'} messages={messages}>
    {
      isAuthenticated
        ? <Router>
            {isLoading && <Loader />}
            <div style={{display: isLoading ? 'none' : ''}}>
              {/* Wrap Nav to pathless Route to prevent render blocking */}
              <Route component={Nav}/>
              <div className="ui container">
                <RootApp />
              </div>
            </div>
          </Router>
        : <Login />
    }
  </IntlProvider>
}

export default RecoilRootComponent
