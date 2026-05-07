// External Dependencies
import React, { Component } from "react";
import {
    get_pattern, get_responsive_styles,
    hex2rgba, _getCustomBgCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_Business_Hour extends Component {
    static slug = "ba_business_hour";
    static folder_name = 'dt_lite_modules';

    static css(props) {

        let additionalCss = [],
            separator_type = props.separator_type
                ? props.separator_type
                : "solid_border",
            separator_weight = props.separator_weight
                ? props.separator_weight
                : "1px",
            separator_height = props.separator_height
                ? props.separator_height
                : "10px",
            separator_gap = props.separator_gap ? props.separator_gap : "15px",
            separator_color = props.separator_color
                ? props.separator_color
                : "#dddddd",
            day_text_width = [],
            time_text_width = [];

        if (props.day_text_width !== "auto") {
            day_text_width = get_responsive_styles(
                props,
                "day_text_width",
                "%%order_class%% .dtq-business-hour-day",
                { primary: "flex", important: false },
                { default: "auto" }
            );
        }

        if (props.time_text_width !== "auto") {
            time_text_width = get_responsive_styles(
                props,
                "time_text_width",
                "%%order_class%% .dtq-business-hour-time",
                { primary: "flex", important: false },
                { default: "auto" }
            );
        }

        if (props.show_separator === "off") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-business-hour-separator",
                    declaration: `display: none!important;`
                }
            ]);
        }

        let title_spacing = get_responsive_styles(
            props,
            "title_spacing",
            "%%order_class%% .dtq-business-hour-title",
            { primary: "margin-bottom", important: true },
            { default: "25px" }
        );

        let item_padding = get_responsive_styles(
            props,
            "item_padding",
            "%%order_class%% .dtq-business-hour-child",
            { primary: "padding", important: true },
            { default: "0|0|0|0" }
        );

        let title_padding = get_responsive_styles(
            props,
            "title_padding",
            "%%order_class%% .dtq-business-hour-title",
            { primary: "padding", important: true },
            { default: "0|0|0|0" }
        );

        additionalCss.push([
            {
                selector: "%%order_class%% .ba_business_hour_child",
                declaration: `margin-bottom: ${props.item_spacing}!important;`
            }
        ]);

        if (props.show_divider === "on") {
            let divider_type = props.divider_type.split("_");

            additionalCss.push([
                {
                    selector: "%%order_class%% .ba_business_hour_child",
                    declaration: `
				  padding-bottom: ${props.item_spacing}!important;`
                }
            ]);

            if (divider_type[1] === "border") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .ba_business_hour_child",
                        declaration: `
					  border-bottom: ${props.divider_weight} ${divider_type[0]} ${props.divider_color}!important;`
                    }
                ]);
            } else {
                let divider_color = props.divider_color,
                    divider_weight = props.divider_weight;

                if (divider_color && divider_color.startsWith("#")) {
                    divider_color = hex2rgba(divider_color);
                }

                let _pattern_bg;
                if (
                    divider_type[0] === "curved" ||
                    divider_type[0] === "zigzag"
                ) {
                    _pattern_bg = get_pattern(
                        divider_type[0],
                        divider_color,
                        divider_weight
                    );
                }

                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .ba_business_hour_child:after",
                        declaration: `
				  	content: '';
					position: absolute;
					background-image: url("${_pattern_bg}");
					height: ${props.divider_height};
					background-size: ${props.divider_height} 100%;
					bottom: calc(-${props.divider_height} / 2);
					`
                    }
                ]);
            }
        }

        // Separator
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-business-hour-separator",
                declaration: `margin-right: ${separator_gap};margin-left: ${separator_gap};`
            }
        ]);

        // separator type
        if (props.show_separator === "on") {
            if (separator_color.startsWith("#")) {
                separator_color = hex2rgba(separator_color);
            }

            let _type = separator_type.split("_");

            if (_type[1] === "border") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-business-hour-separator",
                        declaration: `
			  	border-top: ${separator_weight} ${_type[0]} ${separator_color};
			  	height: ${separator_weight};`
                    }
                ]);
            } else {
                let pattern_bg;
                if (_type[0] === "curved" || _type[0] === "zigzag") {
                    pattern_bg = get_pattern(
                        _type[0],
                        separator_color,
                        separator_weight
                    );
                }

                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-business-hour-separator",
                        declaration: `
								background-image: url("${pattern_bg}");
								height: ${separator_height};
								background-size: ${separator_height} 100%;`
                    }
                ]);
            }
        }

        let title_bg = _getCustomBgCss(
            props,
            "title",
            "%%order_class%% .dtq-business-hour-title",
            "%%order_class%% .dtq-business-hour-title:hover"
        );

        let item_bg = _getCustomBgCss(
            props,
            "item",
            "%%order_class%% .dtq-business-hour-child",
            "%%order_class%% .dtq-business-hour-child:hover"
        );

        return additionalCss
            .concat(item_bg)
            .concat(title_bg)
            .concat(title_spacing)
            .concat(item_padding)
            .concat(day_text_width)
            .concat(time_text_width)
            .concat(title_padding);
    }

    render_title = () => {
        if ("on" === this.props.show_title) {
            return (
                <div className="dtq-business-hour-title">
                    <h2>{this.props.title}</h2>
                </div>
            );
        }
    };

    render() {
        return (
            <div className={`dtq-module dtq-business-hour`}>
                {this.render_title()}
                <div className="dtq-business-hour-content">
                    {this.props.content}
                </div>
            </div>
        );
    }
}

export default DTQ_Business_Hour;
