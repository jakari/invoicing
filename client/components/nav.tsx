
import React from 'react'
import { NavLink } from 'react-router-dom'
import {FormattedMessage} from 'react-intl'
import { isCompanySelectedValue } from "state/user-settings"
import { useLogout } from "api/user"

export function Nav() {
  const isCompanySelected = isCompanySelectedValue()
  const logout = useLogout()

  return <div className="ui inverted menu">
    <div className="ui container">
      <div className="header item">
        <FormattedMessage id="invoicing.title" />
      </div>
      {isCompanySelected && <>
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
                 exact
                 to="/customers">
          <FormattedMessage id="nav.list_customers" />
        </NavLink>
        </>}
      <div className="right menu">
        <NavLink className="item"
                 to="/"
                 activeClassName=""
                 onClick={logout}>
          <FormattedMessage id="nav.logout" />
        </NavLink>
      </div>
    </div>
  </div>
}

