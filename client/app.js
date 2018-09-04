
import React, {Component} from 'react';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';
import AuthRoute from 'components/auth-route';
import ListInvoices from 'containers/list-invoices'
import EditInvoice from 'containers/edit-invoice'
import CreateInvoice from 'containers/create-invoice'
import Login from 'containers/login'
import 'style.css'
import ViewInvoice from 'containers/view-invoice';
import {getSettings} from "actions/invoices";
import {connect} from 'react-redux';
import 'app.scss';
import Loader from 'components/loader';
import Nav from 'components/nav';
import {addLocaleData} from 'react-intl';
import fi from 'react-intl/locale-data/fi';
import messages from 'i18n/fi'
import {IntlProvider} from 'react-intl'

addLocaleData([...fi]);

const addContainer = Component => renderProps => <div className="ui container"><Component {...renderProps} /></div>;

class Root extends Component {
  constructor(props) {
    super(props);

    this.props.getSettings();
  }

  render() {
    return <IntlProvider locale={'fi'} messages={messages}>
      {this.renderApp()}
      </IntlProvider>;
  }

  renderApp() {
    if (this.props.authenticated === null) {
      return <Loader />;
    }

    if (!this.props.authenticated) {
      return <Login />;
    }

    return <Router>
        <div>
          {/* Wrap Nav to pathless Route to prevent render blocking */}
          <Route component={Nav}/>
          <Switch>
            <AuthRoute exact path="/invoice/:invoice/edit" component={addContainer(EditInvoice)} />
            <AuthRoute exact path="/invoice/create" component={addContainer(CreateInvoice)} />
            <AuthRoute exact path="/invoice/:invoice" component={addContainer(ViewInvoice)} />
            <AuthRoute exact path="/" component={addContainer(ListInvoices)} />
          </Switch>
        </div>
      </Router>;
  }
}

export default connect(
  state => ({
    authenticated: state.auth.isAuthenticated,
    loading: state.loader
  }),
  {getSettings}
)(Root)
