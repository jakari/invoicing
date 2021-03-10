import { ChangeEvent, useState } from "react"

export const useInput = (initial: string):
  [string, {value: string, onChange: (event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => void}] => {
  const [value, setValue] = useState(initial)

  return [
    value,
    {
      value,
      onChange: (event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => setValue(event.target.value)
    }
  ]
}

export const useNamedInput = <T>(object: T, setValue: (object: T) => void) => {
  return (name: keyof T) => ({
    value: object[name],
    onChange: (event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) => setValue({...object, [name]: event.target.value}),
    name
  })
}

export const useComponentInput = <T extends unknown>(initial: T): [T, {value: T, onChange: (value: T) => void}] => {
  const [value, setValue] = useState(initial)

  return [
    value,
    {
      value,
      onChange: (value: T) => setValue(value)
    }
  ]
}
