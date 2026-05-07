// External Dependencies
import React, { Component } from "react";
import {
    get_pattern, hex2rgba
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";


class DTQ_Business_Hour_Child extends Component {
  static slug = "ba_business_hour_child";

  static css(props) {
	  let additionalCss = [],
	  separator_type = props.separator_type,
	  separator_weight = props.separator_weight ? props.separator_weight : '1px',
	  separator_height = props.separator_height ? props.separator_height : '10px',
	  separator_gap = props.separator_gap,
	  separator_color = props.separator_color;

	  if( separator_gap ) {
		additionalCss.push([
			{
			  selector: ".dtq-business-hour %%order_class%% .dtq-business-hour-separator",
			  declaration: `
							margin-right: ${separator_gap};margin-left: ${separator_gap};`,
			},
		  ]);
	  }


    // separator type
    if ( separator_type && separator_type !== "relative") {
      if ( separator_color && separator_color.startsWith("#")) {
        separator_color = hex2rgba(separator_color);
      } else {
		separator_color = 'rgb(221 221 221)';
	  }

      let _type = separator_type.split("_");

      if (_type[1] === "border") {
        additionalCss.push([
          {
            selector: ".dtq-business-hour %%order_class%% .dtq-business-hour-separator",
            declaration: `
				border-top: ${separator_weight} ${_type[0]} ${separator_color};
				background-image: initial!important;
				height: initial!important;`,
          },
        ]);
      } else {
        let pattern_bg;
        if (_type[0] === "curved" || _type[0] === "zigzag" ) {
          pattern_bg = get_pattern( _type[0], separator_color, separator_weight );
        }

        additionalCss.push([
          {
            selector: ".dtq-business-hour %%order_class%% .dtq-business-hour-separator",
            declaration: `background-image: url("${pattern_bg}"); border-top: 0!important;`,
          },
        ]);

		if( separator_height ) {
			additionalCss.push([
				{
				  selector: ".dtq-business-hour %%order_class%% .dtq-business-hour-separator",
				  declaration: `
					height: ${separator_height}!important;
					background-size: ${separator_height} 100%;`,
				},
			  ]);
		}
      }
    }

	return additionalCss;
  }

  _render_day = () => {
    if (this.props.day) {
      return <div className='dtq-business-hour-day'>{this.props.day}</div>;
    }
  };

  _render_time = () => {
    if (this.props.time) {
      return <div className='dtq-business-hour-time'>{this.props.time}</div>;
    }
  };

  render() {
    return (
      <div className='dtq-module-child dtq-business-hour-child'>
        {this._render_day()}
        <div className='dtq-business-hour-separator'></div>
        {this._render_time()}
      </div>
    );
  }
}

export default DTQ_Business_Hour_Child;
