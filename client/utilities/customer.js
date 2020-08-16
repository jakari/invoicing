
import {noInterruptFetch} from "utilities";
import {CustomerRecord} from "../records";

export function searchCustomer(name) {
  return function (dispatch) {
    return noInterruptFetch(dispatch, `/api/customers/${name}`)
      .then(response => response.json())
      .then(customer => new CustomerRecord(customer));
  }
}
