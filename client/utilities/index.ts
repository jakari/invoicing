import {setLoadingAction, unsetLoadingAction} from "../actions/util";
import { ChangeEvent, Component } from "react"
import { Dispatch } from "redux"

export function createReducer<T>(initialState: T, reducers: any) {
  return (state = initialState, action: any) => {
    const reducer = reducers[action.type];
    return reducer ? reducer(state, action.data) : state;
  };
}

export function changeHandler(this: Component, e: ChangeEvent<HTMLInputElement>)   {
  const { name, value } = e.target;

  this.setState({ [name]: value });
}

export const jsonFetch = (url: string, options?: RequestInit) => (dispatch: Dispatch) =>
  rawFetch(url, options)(dispatch)
    .then(response => response.status === 204 || response.json())

export const rawFetch = (url: string, options?: RequestInit) => (dispatch: Dispatch) => {
  dispatch(setLoadingAction())
  return noInterruptFetch(url, options)(dispatch)
    .then(setUnloading(dispatch))
}

export const noInterruptFetch = (url: string, options?: RequestInit) => (dispatch: Dispatch) => {
  return authorizedFetch(url, options)
    .then(assertAuthorized(dispatch))
}

const authorizedFetch = (url: string, options?: RequestInit) => fetch(url, {
  ...options,
  headers: {
    ...options?.headers,
    'content-type': 'application/json'
  },
  credentials: 'include'
})

const assertAuthorized = (dispatch: Dispatch) => (response: Response) => {
  if (response.status === 401) {
    dispatch({type: 'SET_NOT_AUTHENTICATED'});
    // Fail silently
    return Promise.reject()
  }

  return response
}

const setUnloading = (dispatch: Dispatch) => (response: Response) => {
  dispatch(unsetLoadingAction())
  return response
}
