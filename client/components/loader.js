
import React from 'react';

export default function loader() {
  return <div className="ui basic segment for-loader">
    <div className="ui active transition visible inverted dimmer">
      <div className="content">
        <div className="ui inverted text loader">Loading</div>
      </div>
    </div>
  </div>;
}
