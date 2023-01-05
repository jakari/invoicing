
import React, { FormEvent } from "react"
import { userLoginCallback } from "../api-helper/user"
import { Button } from 'semantic-ui-react'
import {FormattedMessage, injectIntl, WrappedComponentProps} from "react-intl";
import { useInput } from "../utilities/input-hook"

function Login({intl: {formatMessage}}: WrappedComponentProps) {
  const loginUser = userLoginCallback()
  const [username, bindUsername] = useInput("")
  const [password, bindPassword] = useInput("")

  const performLogin = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    loginUser(username, password)
  }

  return <div className="ui middle aligned center aligned login grid">
    <div className="column">
      <h2 className="ui teal image header">
        <div className="content">
          <FormattedMessage id="login.header" />
        </div>
      </h2>
      <form className="ui large form" onSubmit={performLogin}>
        <div className="ui stacked segment">
          <div className="field">
            <div className="ui left input">
              <input type="text"
                     name="username"
                     placeholder={formatMessage({id: 'login.placeholder.username'})}
                     {...bindUsername}
              />
            </div>
          </div>
          <div className="field">
            <div className="ui left input">
              <input type="password"
                     name="password"
                     placeholder={formatMessage({id: 'login.placeholder.password'})}
                     {...bindPassword}
              />
            </div>
          </div>
          <Button color="teal" type="submit" size="large" fluid>
            <FormattedMessage id="login.login" />
          </Button>
        </div>
        <div className="ui error message"/>
      </form>
    </div>
  </div>
}

export default injectIntl(Login)