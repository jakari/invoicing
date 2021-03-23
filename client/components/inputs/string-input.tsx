import React, { useState } from "react"
import { Props } from "./index"


export function StringInput({value: initial, onChange, required}: Props<string>) {
  const [value, setValue] = useState(initial)

  return <input
    type="text"
    className="ui input"
    required={required}
    value={value}
    onChange={(e) => setValue(e.target.value)}
    onBlur={() => onChange(value)}
  />
}
