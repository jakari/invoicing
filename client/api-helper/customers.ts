
import { useApi } from "."
import { useCompanyPath } from "./company"
import { Customer, CustomerWithInvoiceList } from "../records"
import { ResponseError } from "../utilities/api"

export const useSearchcustomers = (): (name: string) => Promise<Customer[]> => {
  const {get} = useApi({useMainLoading: false})
  const companyPath = useCompanyPath()

  return (name: string) => get<Customer[]>(companyPath(`/customers/${name}`))
    .catch((e: Error) => {
       if (e instanceof ResponseError && e.response.status == 404) return []
       else throw e
    })
}

export const useCustomerApi = () => {
  const {get, patch} = useApi()
  const companyPath = useCompanyPath()

  return {
    getCustomers: () => get<Customer[]>(companyPath("/customers")),
    getCustomer: (customerId: string) => get<CustomerWithInvoiceList>(companyPath(`/customer/${customerId}`)),
    saveCustomer: (customer: Customer) => patch(companyPath(`/customer/${customer.id}`), customer)
  }
}
