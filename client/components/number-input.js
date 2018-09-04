import React from 'react';
import NumberFormat from 'react-number-format';

export default function NumberInput(props) {
    const {value, onValueChange, required} = props;
    const inputProps = {};

    if (required) {
        inputProps.isAllowed = values => !isNaN(values.floatValue);
    }

    return <NumberFormat
        {...inputProps}
        thousandSeparator=" "
        decimalSeparator=","
        decimalScale={2}
        value={value}
        onValueChange={({floatValue}) => onValueChange(floatValue)}
    />;
}
