
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import {loginUser} from 'actions/auth';
import {changeHandler} from "../utilities";
import { Button } from 'semantic-ui-react'
import {FormattedMessage, injectIntl} from "react-intl";

class Login extends Component {
  constructor(props) {
    super(props);

    this.state = {
      username: '',
      password: ''
    };

    this.handleChange = changeHandler.bind(this);
  }

  loginUser = (e) => {
    e.stopPropagation();
    e.preventDefault();

    this.props.loginUser(this.state.username, this.state.password);
  };

  render() {
    const {formatMessage} = this.props.intl;

    return <div className="ui middle aligned center aligned login grid">
      <div className="column">
        <h2 className="ui teal image header">
          <div className="content">
            <FormattedMessage id="login.header" />
          </div>
        </h2>
        <form className="ui large form" onSubmit={this.loginUser}>
          <div className="ui stacked segment">
            <div className="field">
              <div className="ui left input">
                <input type="text"
                       name="username"
                       placeholder={formatMessage({id: 'login.placeholder.username'})}
                       onChange={this.handleChange}
                />
              </div>
            </div>
            <div className="field">
              <div className="ui left input">
                <input type="password"
                       name="password"
                       placeholder={formatMessage({id: 'login.placeholder.password'})}
                       onChange={this.handleChange}
                />
              </div>
            </div>
            <Button color="teal" type="submit" size="large" fluid onClick={this.loginUser}>
              <FormattedMessage id="login.login" />
            </Button>
          </div>
          <div className="ui error message"/>
        </form>
      </div>
    </div>;
  }
}

export default injectIntl(connect(null, {loginUser})(Login))