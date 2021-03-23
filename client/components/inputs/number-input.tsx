import React, { useState } from "react"
import NumberFormat from "react-number-format"
import { Props } from "./index"


export function FloatNumberInput({value: initial, onChange, required}: Props<number>) {
  const [value, setValue] = useState(initial)

    return <NumberFormat
      required={required}
      thousandSeparator=" "
      decimalSeparator=","
      decimalScale={2}
      value={value}
      onValueChange={({floatValue}) => {
        if (!Number.isNaN(floatValue)) {
          onChange(floatValue)
          setValue(floatValue)
        } else {
          setValue(0)
          onChange(0)
        }
      }}
    />
}

export function IntNumberInput({value: initial, onChange, required}: Props<number>) {
  const [value, setValue] = useState(initial.toString())

  return <input
    type="text"
    className="ui input"
    required={required}
    pattern="\d*"
    value={value}
    onChange={(e) => {
      const parsed = parseInt(e.target.value)

      if (e.target.value === "") {
        setValue("")
      } else if (!Number.isNaN(parsed)) {
         setValue(parsed.toString())
      }
    }}
    onBlur={() => {
      const output = value === "" ? 0 : parseInt(value)
      setValue(output.toString())
      onChange(output)
    }}
  />
}
