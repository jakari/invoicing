
import {rawFetch as f} from "utilities";
import {CustomerRecord} from "../records";

export function searchCustomer(name) {
  return function (dispatch) {
    return f(dispatch, `/api/customers/${name}`)
      .then(response => response.json())
      .then(customer => new CustomerRecord(customer));
  }
}
