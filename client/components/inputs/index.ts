
export interface Props<T> {
  value: T
  onChange: (value: T) => void
  required?: boolean
}
