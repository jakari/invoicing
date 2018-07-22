
import {jsonFetch as jf, rawFetch as rf} from "utilities";

export function loginUser(username, password) {
  return dispatch =>
    jf(dispatch, '/api/login', {
      method: 'POST',
      body: JSON.stringify({username, password}),
    })
      .then(settings => {
        dispatch({
          type: 'SET_SETTINGS',
          data: settings
        });
        dispatch({type: 'SET_AUTHENTICATED'});
      });
}

export function logout() {
  return dispatch => {
    dispatch({type: 'SET_NOT_AUTHENTICATED'});
    rf(dispatch, '/api/logout', {method: 'POST'});
  }
}
