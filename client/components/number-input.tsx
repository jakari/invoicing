import React from 'react';
import NumberFormat from 'react-number-format';

export default function NumberInput({value, onChange, required}: {value: number, onChange: (value: number) => void, required: boolean}) {
    const inputProps: any = {};

    if (required) {
        inputProps.isAllowed = (values: any) => {
          return values.value === "" || !isNaN(values.floatValue)
        }
    }

    return <NumberFormat
        {...inputProps}
        thousandSeparator=" "
        decimalSeparator=","
        decimalScale={2}
        value={value}
        onValueChange={({floatValue}) => {
            onChange(floatValue)
        }}
    />
}
