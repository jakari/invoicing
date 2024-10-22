import React, { FormEvent, useRef, useState } from "react"
import { calculateTotal } from "../../utilities/invoice"
import {Button} from 'semantic-ui-react'
import {SelectCustomer} from './select-customer'
import Loader from '../loader'
import { FormattedMessage, injectIntl, WrappedComponentProps } from "react-intl"
import { defaultInvoiceItem, Invoice } from "../../records"
import { userSettingsState } from "../../state/atoms"
import { useRecoilValue } from "recoil"
import { useInput, useComponentInput } from "../../utilities/input-hook"
import { InvoiceItem } from "./invoice-item"
import {v4 as uuidv4} from "uuid"
import {moneyFormatter} from "../../utilities/money-formatter"

interface Props extends WrappedComponentProps {
  invoice: Invoice
  submit: (invoice: Invoice) => void
  cancel: () => void
}

interface Key<T> {
  key: string
  value: T
}

const useKeyedArrayInput = <T extends Object>(
  initial: Array<T>
) => {
  const applyValue = (value: T): Key<T> => ({key: uuidv4(), value})
  const [array, setValue] = useState(initial.map(applyValue))

  return {
    value: array.map(item => item.value),
    array: array,
    edit: (index: number, value: T) => {
      setValue([
        ...array.slice(0, index),
        {key: array[index].key, value: value},
        ...array.slice(index + 1)
      ])
    },
    add: (item: T) => setValue([...array, applyValue(item)]),
    remove: (item: T) => {
      const index = array.findIndex(current => current.value === item)
      setValue([...array.slice(0, index), ...array.slice(index + 1)])
    }
  }
}

function InvoiceForm({invoice: injectedInvoice, submit, cancel: cancelInvoice, intl: {formatMessage}}: Props) {
  const {templates} = useRecoilValue(userSettingsState)
  const formEl = useRef<HTMLFormElement>(null);
  const [loading, setLoading] = useState(false)

  const [customer, customerInput] = useComponentInput(injectedInvoice.customer)
  const [created, createdInput] = useInput(injectedInvoice.created)
  const [due, dueInput] = useInput(injectedInvoice.due)
  const {value: items, array: itemsArray, edit: editItem, add: addItem, remove: removeItem} = useKeyedArrayInput(injectedInvoice.items)
  const [delivery, deliveryInput] = useInput(injectedInvoice.delivery)
  const [remarkingTime, remarkingTimeInput] = useInput(injectedInvoice.remarkingTime.toString())
  const [interestOnArrears, interestOnArrearsInput] = useInput(injectedInvoice.interestOnArrears.toString())
  const [conditionsOfPayment, conditionsOfPaymentInput] = useInput(injectedInvoice.conditionsOfPayment)
  const [customerReference, customerReferenceInput] = useInput(injectedInvoice.customerReference)
  const [template, templateInput] = useInput(injectedInvoice.template)

  const total = calculateTotal(items)

  const submitForm = (e: FormEvent) => {
    e.preventDefault()
    setLoading(true)
    submit({
      customer,
      customerName: customer?.name || "",
      customerStreetName: customer?.streetName || "",
      customerPostCode: customer?.postCode || "",
      customerCity: customer?.city || "",
      customerVat: customer?.vat || "",
      invoiceNumber: injectedInvoice.invoiceNumber,
      referenceNumber: injectedInvoice.referenceNumber,
      created,
      due,
      items,
      delivery,
      remarkingTime: parseInt(remarkingTime),
      interestOnArrears: parseInt(interestOnArrears),
      conditionsOfPayment,
      customerReference,
      template,
      total
    })
  }

  const cancel = () => {
    if (confirm('Are you sure you want to cancel?')) {
      cancelInvoice()
    }
  };

  if (loading) {
    return <Loader />;
  }

  return (
    <form onSubmit={submitForm} autoComplete="off" ref={formEl}>
      <div className="ui form stackable grid">
        <div className="ui sixteen wide column">
          <FormattedMessage id="invoice.template">
            {(txt) => (
              <h4 className="ui horizontal divider header">
                <i className="address book icon" />
                {txt}
              </h4>
            )}
          </FormattedMessage>
          <select {...templateInput} required>
            {templates.map(({title, name}) => <option key={"template-" + name} value={name}>{title}</option>)}
          </select>
        </div>
        <SelectCustomer {...customerInput} customer={customer} />
        <div className="ui eight wide column">
          <FormattedMessage id="invoice.information">
            {(txt) => (
              <h4 className="ui horizontal divider header">
                <i className="address book icon" />
                {txt}
              </h4>
            )}
          </FormattedMessage>
          <div className="two fields">
            <div className="required field">
              <FormattedMessage id="invoice.created" tagName="label" />
              <input
                type="date"
                name="created"
                {...createdInput}
                required
              />
            </div>
            <div className="required field">
              <FormattedMessage id="invoice.due" tagName="label" />
              <input
                type="date"
                name="due"
                {...dueInput}
                required
              />
            </div>
          </div>
          <div className="two fields">
            <div className="field">
              <FormattedMessage id="invoice.delivery" tagName="label" />
              <input type="text"
                     name="delivery"
                     {...deliveryInput}
                     placeholder={formatMessage({id: 'invoice.delivery'})}
                     max="255"
              />
            </div>
            <div className="required field">
              <FormattedMessage id="invoice.remarking_time" tagName="label" />
              <div className="ui right labeled input">
                <input type="number"
                       name="remarkingTime"
                       {...remarkingTimeInput}
                       placeholder={formatMessage({id: 'invoice.remarking_time'})}
                       required
                       pattern="\d+"
                       min="0"
                       max="360" />
                <FormattedMessage id="invoice.remarking_time_days_suffix">
                  {(txt) => (
                    <div className="ui basic label">{txt}</div>
                  )}
                </FormattedMessage>
              </div>
            </div>
          </div>
          <div className="two fields">
            <div className="required field">
              <FormattedMessage id="invoice.interest_on_arrears" tagName="label" />
              <div className="ui right labeled input">
                  <input type="number"
                         name="interestOnArrears"
                         {...interestOnArrearsInput}
                         placeholder={formatMessage({id: 'invoice.interest_on_arrears'})}
                         required
                         pattern="\d+"
                         min="0"
                         max="100" />
                <div className="ui basic label">%</div>
              </div>
            </div>
            <div className="field">
              <FormattedMessage id="invoice.conditions_of_payment" tagName="label" />
              <input type="text"
                     name="conditionsOfPayment"
                     {...conditionsOfPaymentInput}
                     placeholder={formatMessage({id: 'invoice.conditions_of_payment'})}
                     max="255" />
            </div>
          </div>
          <div className="field">
            <FormattedMessage id="invoice.customer_reference" tagName="label" />
            <input type="text"
                   name="customerReference"
                   {...customerReferenceInput}
                   placeholder={formatMessage({id: 'invoice.customer_reference'})}
            />
          </div>
        </div>
      </div>
      <table className="ui celled invoice items table">
        <thead>
        <tr>
          <FormattedMessage id="invoice.items.header.name" tagName="th" />
          <FormattedMessage id="invoice.items.header.amount" tagName="th" />
          <FormattedMessage id="invoice.items.header.price" tagName="th" />
          <FormattedMessage id="invoice.items.header.tax" tagName="th" />
          <FormattedMessage id="invoice.total" tagName="th" />
        </tr>
        </thead>
        <tbody>
        {itemsArray.map(({key, value}, index) => (
          <InvoiceItem key={key} index={index} item={value} edit={editItem} remove={removeItem} />
        ))}
        </tbody>
        <tfoot>
        <tr>
          <td colSpan={4} className="footer header">
            <button onClick={() => addItem(Object.assign({}, defaultInvoiceItem))} type="button" className="ui mini left floated button">
              <FormattedMessage id="invoice.form.add_item" />
            </button>
            {items.length === 0 && <div className="ui left pointing red label">
              <FormattedMessage id="invoice.form.error.at_least_one_item_required" />
            </div>}
            <h4 className="ui right floated header">
              <FormattedMessage id="invoice.total" />
            </h4>
          </td>
          <td className="footer value">
            { moneyFormatter.format(total) }
          </td>
        </tr>
        </tfoot>
      </table>
      <div>
        <Button
          type="submit"
          primary
          disabled={!customer || items.length === 0}
        >
          <FormattedMessage id="button.save" />
        </Button>
        <Button
          type="button"
          onClick={cancel}
        >
          <FormattedMessage id="button.cancel" />
        </Button>
      </div>
    </form>
  )
}

export default injectIntl(InvoiceForm)
