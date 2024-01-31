import React, { KeyboardEvent, useEffect, useState } from "react"
import { Customer, defaultCustomer } from "../../records"
import { useSearchcustomers } from "../../api-helper/customers"
import { injectIntl, WrappedComponentProps } from "react-intl"

interface Props extends  WrappedComponentProps {
  selectCustomer: (customer: Customer | null) => void,
  searchTerm: (term: String, foundCount: number) => void
}

const useDebounce = (value: string, delay: number) => {
  const [debouncedValue, setDebouncedValue] = useState(value)

  useEffect(() => {
    const handler = setTimeout(() => setDebouncedValue(value), delay)
    return () => clearTimeout(handler)
  }, [value])

  return debouncedValue
}

function SelectCustomer({selectCustomer: selectCustomerCallback, intl: {formatMessage}, searchTerm: searchTermCallback}: Props) {
  const searchCustomers = useSearchcustomers()
  const [searchTerm, setSearchTerm] = useState("")
  const [results, setResults] = useState<Customer[]>([])
  const [searching, setSearching] = useState(false)
  const [pendingResults, setPendingResults] = useState(false)
  const debouncedSearchTerm = useDebounce(searchTerm, 150)
  const [currentCustomer, setCurrentCustomer] = useState<Customer | null>(null)

  const selectCustomer = (customer: Customer | null) => {
    selectCustomerCallback(customer)
  }

  useEffect(() => {
    if (debouncedSearchTerm) {
      setSearching(true)
      searchCustomers(debouncedSearchTerm)
        .then(customers => {
          setResults(customers)
          if (customers[0]) setCurrentCustomer(customers[0])
          setSearching(false)
          setPendingResults(false)
        })
    } else setResults([])
  }, [debouncedSearchTerm])

  useEffect(() => {
    const customerCount =
      searchTerm.length == 0 || pendingResults
        ? -1
        : results.length
    searchTermCallback(searchTerm, customerCount)
  }, [searchTerm, results, pendingResults])

  const createNewCustomer = () => {
    selectCustomer(searchTerm.length > 0 ? {...defaultCustomer, name: searchTerm} : null)
  }

  const onKeyPress = (e: KeyboardEvent<HTMLInputElement>) => {
    switch (e.key) {
      case "Enter":
        if (currentCustomer) {
          selectCustomer(currentCustomer)
        }
        break
      case "Escape":
        createNewCustomer()
        e.currentTarget.blur()
        break
      case "ArrowUp":
        if (currentCustomer) {
          const current = results.indexOf(currentCustomer)
          if (current === 0) setCurrentCustomer(results[results.length - 1])
          else setCurrentCustomer(results[current - 1])
        }
        break
      case "ArrowDown":
        if (currentCustomer) {
          const current = results.indexOf(currentCustomer)
          if (current === results.length - 1) setCurrentCustomer(results[0])
          else setCurrentCustomer(results[current + 1])
        }
        break
    }
  }

  return (
    <div>
      <div className={"ui fluid search" + (searching ? " loading double" : "")}>
        <div className="ui fluid huge icon input">
          <input
            className="prompt"
            type="text"
            placeholder={formatMessage({id: 'invoice.select_customer.customer_name'})}
            value={searchTerm}
            onChange={e => {
              setSearchTerm(e.target.value)
              setPendingResults(true)
            }}
            onKeyDown={onKeyPress}
            autoComplete="nope"
          />
          <i className="search icon" />
        </div>
        {
          !!results.length
          && <div className="results transition visible">
            {results.map(customer => (
              <a
                key={"customer-" + customer.id}
                className={"result" + (customer === currentCustomer ? " active" : "")}
                onClick={() => selectCustomer(customer)}
              >
                <div className="content">
                  <div className="title">{customer.name}</div>
                  {customer.additionalName && <div className="description">{customer.additionalName}</div>}
                  <div className="description">{customer.streetName}</div>
                  <div className="description">{customer.postCode} {customer.city}</div>
                  <div className="description">{customer.phone}</div>
                </div>
              </a>
            ))}
          </div>
        }
      </div>
    </div>
  )
}

export const SelectCustomerInput = injectIntl(SelectCustomer)
