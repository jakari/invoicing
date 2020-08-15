import {jsonFetch, rawFetch as rf} from "../utilities";

export function getCustomers() {
  return function (dispatch) {
    return jsonFetch(dispatch, `/api/customers`);
  }
}

export function getCustomer(customerId) {
  return function (dispatch) {
    return jsonFetch(dispatch, `/api/customer/${customerId}`);
  }
}
