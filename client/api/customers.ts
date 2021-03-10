
import { useApi } from "api"
import { Customer, CustomerWithInvoiceList } from "../records"

export const useSearchcustomers = (): (name: string) => Promise<Customer[]> => {
  const {get} = useApi({useMainLoading: false})

  return (name: string) => get(`/api/customers/${name}`)
}

export const useCustomerApi = () => {
  const {get, patch} = useApi()

  return {
    getCustomers: () => get<Customer[]>("/api/customers"),
    getCustomer: (customerId: string) => get<CustomerWithInvoiceList>(`/api/customer/${customerId}`),
    saveCustomer: (customer: Customer) => patch(`/api/customer/${customer.id}`, customer)
  }
}
