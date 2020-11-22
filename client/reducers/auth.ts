
import {createReducer} from "utilities"
import { Auth } from "client/records"

export const defaultAuth: Auth = {isAuthenticated: null}

export default createReducer(defaultAuth, {
  SET_NOT_AUTHENTICATED: (state: Auth) => ({...state, isAuthenticated: false}),
  SET_AUTHENTICATED: (state: Auth) => ({...state, isAuthenticated: true})
})
