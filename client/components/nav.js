
import React from 'react';
import { NavLink } from 'react-router-dom'
import { connect } from 'react-redux'
import { logout } from "actions/auth";

function Nav({logout}) {
  return <div className="ui inverted menu">
    <div className="ui container">
      <div className="header item">
        Invoicing
      </div>
      <NavLink className="item"
               exact
               to="/invoice/create">
        Create
      </NavLink>
      <NavLink className="item"
               exact
               to="/">
        List
      </NavLink>
      <NavLink className="item"
               to="/"
               activeClassName=""
               onClick={logout}>
        Logout
      </NavLink>
    </div>
  </div>;
}

export default connect(null, {logout})(Nav);
