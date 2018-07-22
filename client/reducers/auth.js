
import {createReducer} from "utilities";
import {AuthRecord} from "../records";

export default createReducer(new AuthRecord(), {
  SET_NOT_AUTHENTICATED: state => state.set('isAuthenticated', false),
  SET_AUTHENTICATED: state => state.set('isAuthenticated', true),
});
