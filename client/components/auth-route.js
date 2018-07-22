
import React from 'react';
import { Route } from 'react-router-dom';

function AuthRoute({ component: Component, ...rest }) {
  return <Route
    {...rest}
    render={renderProps => <div>
      <Component {...renderProps} />
    </div>}
  />;
}

export default AuthRoute;
