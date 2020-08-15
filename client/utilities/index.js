import {setLoadingAction, unsetLoadingAction} from "../actions/util";

export function createReducer(initialState, reducers) {
  return (state = initialState, action) => {
    const reducer = reducers[action.type];
    return reducer ? reducer(state, action.data) : state;
  };
}

export function changeHandler(e) {
  const { name, value } = e.target;

  this.setState({ [name]: value });
};

export function jsonFetch (...props) {
  return rawFetch(...props)
    .then(response => response.json())
}

export function rawFetch(dispatch, url, options) {
  options = options || {};

  options.headers = {
    ...(options.headers || {}),
    'content-type': 'application/json'
  };

  dispatch(setLoadingAction());

  options.credentials = 'include';
  return fetch(url, options)
    .then(response => {
      dispatch(unsetLoadingAction());
      if (response.status === 401) {
        dispatch({
          type: 'SET_NOT_AUTHENTICATED'
        });

        // Fail silently
        return new Promise(() => {});
      }

      return response;
    });
}
