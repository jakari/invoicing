
import React, { useEffect, useState } from "react"
import { FormattedMessage, injectIntl, WrappedComponentProps } from "react-intl"
import { Customer } from "../records"
import { RouteComponentProps } from "react-router"
import { useCustomerApi } from "../api-helper/customers"

const ListCustomersComponent = ({history, intl: {formatMessage}}: WrappedComponentProps & RouteComponentProps) => {
  const [customers, setCustomers] = useState<Customer[]>([])
  const { getCustomers } = useCustomerApi()
  const [searchTerm, setSearchTerm]  = useState("")

  useEffect(() => {
    getCustomers().then(setCustomers)
  }, [])

  return (
    <div>
      <h1 className="ui header">
        <FormattedMessage id="customers.list" />
      </h1>
      <div className="ui big icon input">
        <i className="search icon" />
        <input type="text" placeholder={formatMessage({id: "customers.search"})} value={searchTerm} onChange={e => setSearchTerm(e.target.value.toLowerCase())} />
      </div>
      <table className="ui basic selectable small table">
        <tbody>
        {customers
          .filter(customer => searchTerm === "" || customer.name.toLowerCase().includes(searchTerm))
          .map(customer => (
            <tr key={customer.id}>
              <td onClick={() => {
                history.push(`/customer/${customer.id}`)}}>{customer.name}</td>
            </tr>
          ))
        }
        </tbody>
      </table>
    </div>
  )
}

export const ListCustomers = injectIntl(ListCustomersComponent)
