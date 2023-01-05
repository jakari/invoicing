
import React, { FormEvent, useEffect, useState } from "react"
import { FormattedMessage, injectIntl, WrappedComponentProps } from "react-intl"
import { Customer } from "../records"
import { RouteComponentProps } from "react-router"
import { useCustomerApi } from "../api-helper/customers"
import { useNamedInput } from "../utilities/input-hook"

type Props = RouteComponentProps<{customerId: string}> & WrappedComponentProps

function ViewCustomerComponent({match, history}: Props) {
  const [customer, setCustomer] = useState<Customer | null>(null)
  const {getCustomer, saveCustomer} = useCustomerApi()
  const namedInput = useNamedInput<Customer>(customer!, setCustomer)

  useEffect(() => {
    getCustomer(match.params.customerId).then(setCustomer)
  }, [match.params.customerId])

  const submit = async (e: FormEvent) => {
    e.preventDefault()
    await saveCustomer(customer!)
    history.push(`/customer/${customer!.id}`)
  }

  return (
    <div>
      {customer
        ? <div>
          <h1 className="ui header">Asiakastiedot</h1>
          <div className="ui divider" />
          <form onSubmit={submit}>
            <div className="ui form">
              <div className="ui stackable grid">
                <div className="eight wide column">
                  <div className="field">
                    <label><FormattedMessage id="customer.company" /></label>
                    <input type="text" {...namedInput("name")} required={true} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.additional_name" /></label>
                    <input type="text" {...namedInput("additionalName")} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.contact_person" /></label>
                    <input type="text" {...namedInput("contactPerson")} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.email" /></label>
                    <input type="text" {...namedInput("email")} />
                  </div>
                </div>
                <div className="eight wide column">
                  <div className="field">
                    <label><FormattedMessage id="customer.vat" /></label>
                    <input type="text" {...namedInput("vat")} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.phone" /></label>
                    <input type="text" {...namedInput("phone")} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.street_name" /></label>
                    <input type="text" {...namedInput("streetName")} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.post_code" /></label>
                    <input type="text" {...namedInput("postCode")} />
                  </div>
                  <div className="field">
                    <label><FormattedMessage id="customer.city" /></label>
                    <input type="text" {...namedInput("city")} />
                  </div>
                </div>
              </div>
              <div className="ui hidden divider" />
              <button className="ui button primary"><FormattedMessage id="button.save" /></button>
              <button  className="ui button" onClick={history.goBack} type="button"><FormattedMessage id="button.cancel" /></button>
            </div>
          </form>
        </div>
        : <div>Ladataan...</div>
      }
    </div>
  )
}

export const EditCustomer = injectIntl(ViewCustomerComponent)
