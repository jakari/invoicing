
import {noInterruptFetch} from "utilities"
import { Dispatch } from "redux"
import { Customer } from "records"

export const searchCustomer = (name: string) => (dispatch: Dispatch): Promise<Customer[]> =>
  noInterruptFetch(`/api/customers/${name}`)(dispatch)
    .then(response => response.json());
