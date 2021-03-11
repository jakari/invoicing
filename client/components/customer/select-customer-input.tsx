import React, { KeyboardEvent, useEffect, useState } from "react"
import { Customer, defaultCustomer } from "records"
import { useSearchcustomers } from "api/customers"
import { injectIntl, WrappedComponentProps } from "react-intl"

interface Props extends  WrappedComponentProps {
  selected: boolean
  selectCustomer: (customer: Customer | null) => void
}

const useDebounce = (value: string, delay: number) => {
  const [debouncedValue, setDebouncedValue] = useState(value)

  useEffect(() => {
    const handler = setTimeout(() => setDebouncedValue(value), delay)
    return () => clearTimeout(handler)
  }, [value])

  return debouncedValue
}

function SelectCustomer({selectCustomer, intl: {formatMessage}}: Props) {
  const searchCustomers = useSearchcustomers()
  const [searchTerm, setSearchTerm] = useState("")
  const [results, setResults] = useState<Customer[]>([])
  const [searching, setSearching] = useState(false)
  const debouncedSearchTerm = useDebounce(searchTerm, 150)
  const [dropdownCustomer, setDropdownCustomer] = useState<Customer | null>(null)

  useEffect(() => {
    if (debouncedSearchTerm) {
      setSearching(true)
      searchCustomers(debouncedSearchTerm)
        .then(customers => {
          setResults(customers)
          if (customers[0]) setDropdownCustomer(customers[0])
          setSearching(false)
        })
    } else setResults([])
  }, [debouncedSearchTerm])

  const createNewCustomer = () => selectCustomer(searchTerm.length > 0 ? {...defaultCustomer, name: searchTerm} : null)

  const onKeyPress = (e: KeyboardEvent<HTMLInputElement>) => {
    switch (e.key) {
      case "Enter":
        if (dropdownCustomer) {
          selectCustomer(dropdownCustomer)
        }
        break
      case "Escape":
        createNewCustomer()
        e.currentTarget.blur()
        break
      case "ArrowUp":
        if (dropdownCustomer) {
          const current = results.indexOf(dropdownCustomer)
          if (current === 0) setDropdownCustomer(results[results.length - 1])
          else setDropdownCustomer(results[current - 1])
        }
        break
      case "ArrowDown":
        if (dropdownCustomer) {
          const current = results.indexOf(dropdownCustomer)
          if (current === results.length - 1) setDropdownCustomer(results[0])
          else setDropdownCustomer(results[current + 1])
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
            onChange={e => setSearchTerm(e.target.value)}
            onKeyDown={onKeyPress}
            autoComplete="nope"
            onBlur={createNewCustomer}
          />
          <i className="search icon" />
        </div>
        {
          !!results.length
          && <div className="results transition visible">
            {results.map(customer => (
              <a key={"customer-" + customer.id} className={"result" + (customer === dropdownCustomer ? " active" : "")} onClick={() => {}}>
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
