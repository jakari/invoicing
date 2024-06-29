import React from 'react';
import {NumericFormat} from 'react-number-format';

export function NumberInput({value, onChange, required}: {value: number, onChange: (value: number) => void, required: boolean}) {
    const inputProps: any = {};

    if (required) {
        inputProps.isAllowed = (values: any) => {
          return values.value === "" || !isNaN(values.floatValue)
        }
    }

    return <NumericFormat
        {...inputProps}
        thousandSeparator=" "
        decimalSeparator=","
        decimalScale={2}
        value={value}
        onValueChange={({floatValue}) => {
            floatValue !== undefined && onChange(floatValue)
        }}
    />
}

export function TaxInput({value, onChange, required}: {value: number, onChange: (value: number) => void, required: boolean}) {
    const inputProps: any = {};

    if (required) {
        inputProps.isAllowed = (values: any) => {
          return values.value === "" || !isNaN(values.floatValue)
        }
    }

    return <NumericFormat
        {...inputProps}
        thousandSeparator=""
        decimalSeparator=","
        value={value}
        onValueChange={({floatValue}) => {
            floatValue !== undefined && onChange(floatValue)
        }}
    />
}
