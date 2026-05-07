// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles,
    _getAbsoluteElementStyles,
    _getCustomBgCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_Number extends Component {
    static slug = "ba_number";

    static css(props) {
        let additionalCss = [],
            number_height = [],
            number_width = [],
            number_bg = [],
            number_absolute_styles = [],
            number_rotate = props.number_rotate;

        if ("on" === props.use_box) {
            number_height = get_responsive_styles(
                props,
                "number_height",
                "%%order_class%% .dtq-number-wrap",
                { primary: "height", important: false },
                { default: "100px" }
            );

            number_width = get_responsive_styles(
                props,
                "number_width",
                "%%order_class%% .dtq-number-wrap",
                { primary: "width", important: false },
                { default: "100px" }
            );

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-number-wrap",
                    declaration: `
                        transform: rotate(${number_rotate});
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        border-style: solid;`
                }
            ]);

            number_bg = _getCustomBgCss(
                props,
                "number",
                "%%order_class%% .dtq-number-wrap",
                "%%order_class%%:hover .dtq-number-wrap"
            );

        }

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-number-wrap",
                declaration: `text-align: ${props.number_alignment};`
            }
        ]);

        if( props.number_alignment === 'center' ) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-number-wrap",
                    declaration: `margin-right: auto;margin-left: auto;`
                }
            ]);
        } else if( props.number_alignment === 'right' ){
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-number-wrap",
                    declaration: `margin-left: auto;`
                }
            ]);
        }

        let title_spacing = [];
        if (props.number_placement === "_default") {
            title_spacing = get_responsive_styles(
                props,
                "title_spacing",
                "%%order_class%% .dtq-number-title",
                { primary: "margin-top", important: false },
                { default: "10px" }
            );
        }
        if (props.number_placement === "absolute") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-number-wrap",
                    declaration: `z-index: 9!important;`
                }
            ]);
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-number-title",
                    declaration: `z-index: 999!important;position: relative;`
                }
            ]);

            number_absolute_styles = _getAbsoluteElementStyles(
                props,
                "number",
                "%%order_class%% .dtq-number-wrap",
                { position: "left_top", offset_x: "50px", offset_y: "50px" }
            );
        }

        return additionalCss
            .concat(title_spacing)
            .concat(number_absolute_styles)
            .concat(number_bg)
            .concat(number_height)
            .concat(number_width);
    }

    _renderNumber = () => {
        let number = this.props.number;
        if (number) {
            return (
                <div className="dtq-number-wrap">
                    <div className="dtq-number-text">{number}</div>
                </div>
            );
        }
    };

    _renderTitle = () => {
        let title = this.props.title;
        if (title) {
            return (
                <div className="dtq-number-title">
                    <h3>{title}</h3>
                </div>
            );
        }
    };

    render() {
        return (
            <div className="dtq-module dtq-number">
                {this._renderNumber()}
                {this._renderTitle()}
            </div>
        );
    }
}

export default DTQ_Number;
