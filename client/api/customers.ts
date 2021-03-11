
import { useApi } from "api"
import { Customer, CustomerWithInvoiceList } from "../records"
import { ResponseError } from "utilities/api"

export const useSearchcustomers = (): (name: string) => Promise<Customer[]> => {
  const {get} = useApi({useMainLoading: false})

  return (name: string) => get<Customer[]>(`/api/customers/${name}`)
    .catch((e: Error) => {
       if (e instanceof ResponseError && e.response.status == 404) return []
       else throw e
    })
}

export const useCustomerApi = () => {
  const {get, patch} = useApi()

  return {
    getCustomers: () => get<Customer[]>("/api/customers"),
    getCustomer: (customerId: string) => get<CustomerWithInvoiceList>(`/api/customer/${customerId}`),
    saveCustomer: (customer: Customer) => patch(`/api/customer/${customer.id}`, customer)
  }
}
