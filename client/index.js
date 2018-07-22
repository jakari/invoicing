
import ReactDOM from 'react-dom';
import React from 'react';
import Root from 'root';
import 'semantic-ui-css/semantic.min.css';
import {Provider} from "react-redux";
import {applyMiddleware, combineReducers, createStore} from "redux";
import reducers from "./reducers";
import thunk from "redux-thunk";

const store = createStore(
  combineReducers(reducers),
  applyMiddleware(thunk)
);

ReactDOM.render(
  <Provider store={store}><Root /></Provider>,
  document.getElementById('root')
);

if (module.hot) {
  module.hot.accept('reducers', () => {
    store.replaceReducer(combineReducers(require('./reducers').default));
  });
}
