
import {jsonFetch as jf, rawFetch as rf} from "utilities";
import { Dispatch } from "redux"

export function loginUser(username: string, password: string) {
  return (dispatch: Dispatch) => jf('/api/login', {
      method: 'POST',
      body: JSON.stringify({username, password}),
    })(dispatch)
      .then((settings: any) => {
        dispatch({
          type: 'SET_SETTINGS',
          data: settings
        });
        dispatch({type: 'SET_AUTHENTICATED'});
      });
}

export function logout() {
  return (dispatch: Dispatch) => {
    dispatch({type: 'SET_NOT_AUTHENTICATED'});
    rf('/api/logout', {method: 'POST'})(dispatch)
  }
}
