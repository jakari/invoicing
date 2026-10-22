import React from 'react';
import {NumericFormat} from 'react-number-format';

export function NumberInput({value, onChange, required, decimalScale = 2}: {value: string, onChange: (value: string) => void, required: boolean, decimalScale?: number}) {
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
        decimalScale={decimalScale}
        value={value}
        valueIsNumericString
        onValueChange={({floatValue, value}) => {
            floatValue !== undefined && onChange(value)
        }}
    />
}

export function TaxInput({value, onChange, required}: {value: string, onChange: (value: string) => void, required: boolean}) {
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
        valueIsNumericString
        onValueChange={({floatValue, value}) => {
            const actualValue = value === "" ? "0" : floatValue !== undefined ? value : undefined
            actualValue !== undefined && onChange(actualValue)
        }}
    />
}
