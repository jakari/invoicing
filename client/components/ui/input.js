import React from "react";

export default function Input(props) {
    return <input
        {...props}
        name="description"
        required
        className="ui input"
        type="text" />;
}
