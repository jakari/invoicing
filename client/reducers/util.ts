
import {createReducer} from "utilities"
import {Util} from "../records"

export const defaultUtil: Util = {loading: false}

export default createReducer(defaultUtil, {
  SET_LOADING: (state: Util) => ({...state, loading: true}),
  UNSET_LOADING: (state: Util) => ({...state, loading: false})
})
