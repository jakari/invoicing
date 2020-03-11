
import ReactDOM from 'react-dom';
import React from 'react';
import 'semantic-ui-css/semantic.min.css';
import {Provider} from "react-redux";
import {applyMiddleware, combineReducers, createStore} from "redux";
import reducers from "./reducers";
import thunk from "redux-thunk";
import App from './app';

const store = createStore(
  combineReducers(reducers),
  applyMiddleware(thunk)
);

ReactDOM.render(
  <Provider store={store}><App /></Provider>,
  document.getElementById('root')
);
