
import React from 'react';
import { NavLink } from 'react-router-dom'
import { connect } from 'react-redux'
import { logout } from "actions/auth";
import {FormattedMessage} from 'react-intl';

function Nav({logout}) {
  return <div className="ui inverted menu">
    <div className="ui container">
      <div className="header item">
        <FormattedMessage id="invoicing.title" />
      </div>
      <NavLink className="item"
               exact
               to="/invoice/create">
        <FormattedMessage id="nav.create" />
      </NavLink>
      <NavLink className="item"
               exact
               to="/">
        <FormattedMessage id="nav.list" />
      </NavLink>
      <NavLink className="item"
               to="/"
               activeClassName=""
               onClick={logout}>
        <FormattedMessage id="nav.logout" />
      </NavLink>
    </div>
  </div>;
}

export default connect(null, {logout})(Nav);
