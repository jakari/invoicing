
import React from 'react';
import 'style.css'
import {hot} from "react-hot-loader";
import App from 'app';

export default process.env.NODE_ENV === 'production' ?
  App :
  hot(module)(App);
