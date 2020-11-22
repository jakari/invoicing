import {jsonFetch} from "../utilities";
import { Customer } from "client/records"

export const getCustomers = () => jsonFetch(`/api/customers`)
export const getCustomer = (customerId: string) => jsonFetch(`/api/customer/${customerId}`)
export const saveCustomer = (customer: Customer) => jsonFetch(
  `/api/customer/${customer.id}`,
  {method: "PATCH", body: JSON.stringify(customer)}
  )
