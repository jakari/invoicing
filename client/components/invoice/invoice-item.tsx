import React  from "react"
import { recalcItem } from "../../utilities/invoice"
import { InvoiceItem as InvoiceItemRecord } from "../../records"
import { StringInput } from "../inputs/string-input"
import {NumberInput, TaxInput} from "../number-input"
import { moneyFormatter } from "../../utilities/money-formatter"

interface Props {
  index: number
  item: InvoiceItemRecord
  edit: (key: number, item: InvoiceItemRecord) => void
  remove: (item: InvoiceItemRecord) => void
}


export function InvoiceItem({index, item, edit, remove}: Props) {
  const changeValue = <R extends keyof InvoiceItemRecord>(index: number, key: R) => (value: InvoiceItemRecord[R]) => {
    edit(index, recalcItem({...item, [key]: value}))
  }

  return <tr>
    <td>
      <div className="ui fluid transparent action input">
        <StringInput
          required
          value={item.description}
          onChange={description => edit(index, {...item, description})}
        />
        <div onClick={() => remove(item)} className="ui compact mini red icon button">
          <i className="trash alternate icon" />
        </div>
      </div>
    </td>
    <td className="amount">
      <div className="ui fluid transparent input">
        <NumberInput
          required={true}
          value={item.amount}
          onChange={changeValue(index, "amount")}
        />
      </div>
    </td>
    <td className="price">
      <div className="ui fluid transparent input">
        <NumberInput
          required
          value={item.price}
          onChange={changeValue(index, "price")}
        />
      </div>
    </td>
    <td className="tax">
      <div className="ui fluid transparent input">
        <TaxInput
          required={true}
          value={item.tax}
          onChange={changeValue(index, "tax")}
        />
      </div>
    </td>
    <td className="total">{moneyFormatter.format(item.totalWithoutTax)}</td>
  </tr>
}
