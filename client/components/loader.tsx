
import React from 'react'

export default function loader() {
  return <div className="ui basic segment for-loader">
    <div className="ui active transition visible inverted dimmer">
      <div className="ui inverted loader" />
    </div>
  </div>
}
