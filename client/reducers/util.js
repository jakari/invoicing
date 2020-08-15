
import {createReducer} from "utilities";
import {UtilRecord} from "../records";

export default createReducer(new UtilRecord(), {
  SET_LOADING: state => state.set('loading', true),
  UNSET_LOADING: state => state.set('loading', false),
});
