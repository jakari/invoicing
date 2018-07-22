
import React from 'react';
import {Component} from 'react';
import {connect} from 'react-redux';
import {loginUser} from 'actions/auth';
import {changeHandler} from "../utilities";
import { Button } from 'semantic-ui-react'

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
    return <div className="ui middle aligned center aligned login grid">
      <div className="column">
        <h2 className="ui teal image header">
          <div className="content">
            Login
          </div>
        </h2>
        <form className="ui large form" onSubmit={this.loginUser}>
          <div className="ui stacked segment">
            <div className="field">
              <div className="ui left input">
                <input type="text" name="username" placeholder="Username" onChange={this.handleChange} />
              </div>
            </div>
            <div className="field">
              <div className="ui left input">
                <input type="password" name="password" placeholder="Password"  onChange={this.handleChange} />
              </div>
            </div>
            <Button color="teal" type="submit" size="large" fluid onClick={this.loginUser}>Login</Button>
          </div>
          <div className="ui error message"/>
        </form>
      </div>
    </div>;
  }
}

export default connect(null, {loginUser})(Login)