import React  from "react"
import { recalcItem } from "../../utilities/invoice"
import { InvoiceItem as InvoiceItemRecord } from "../../records"
import { FloatNumberInput, IntNumberInput } from "components/inputs/number-input"
import { StringInput } from "components/inputs/string-input"

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
        <IntNumberInput
          required={true}
          value={item.amount}
          onChange={changeValue(index, "amount")}
        />
      </div>
    </td>
    <td className="price">
      <div className="ui fluid transparent input">
        <FloatNumberInput
          required
          value={item.price}
          onChange={changeValue(index, "price")}
        />
      </div>
    </td>
    <td className="tax">
      <div className="ui fluid transparent input">
        <IntNumberInput
          required={true}
          value={item.tax}
          onChange={changeValue(index, "tax")}
        />
      </div>
    </td>
    <td className="total">{item.total.toFixed(2)}</td>
  </tr>;
}
