import React, { Component } from "react";
import {
    get_responsive_styles, renderFontStyle, _getCustomBgCss
} from "@ModuleHelper";
import "./style.css";

class DTQ_InlineNotice extends Component {
    static slug = "ba_alert";

    static css(props) {
        let additionalCss = [];

        let alertsData = {
            danger: {
                color: "#721c24",
                background: "#f8d7da",
                linkColor: "#491217"
            },
            warning: {
                color: "#856404",
                background: "#fff3cd",
                linkColor: "#533f03"
            },
            info: {
                color: "#0c5460",
                background: "#d1ecf1",
                linkColor: "#062c33"
            },
            ltdark: {
                color: "#1b1e21",
                background: "#d6d8d9",
                linkColor: "#040505"
            },
            dark: {
                color: "#ffffff",
                background: "#626686",
                linkColor: "#ffffff"
            },
            light: {
                color: "#818182",
                background: "#fefefe",
                linkColor: "#686868"
            }
        };

        if (!props.background_color) {
            additionalCss.push([
                {
                    selector: "%%order_class%%",
                    declaration: `background-color: ${
                        alertsData[props.alert_type].background
                    };`
                }
            ]);
        }
        additionalCss.push([
            {
                selector: "%%order_class%%, %%order_class%% .dtq-alert-title",
                declaration: `
					color: ${alertsData[props.alert_type].color};`
            }
        ]);

        additionalCss.push([
            {
                selector:
                    "%%order_class%% a, %%order_class%% .dtq-alert-dismiss, %%order_class%% strong, %%order_class%% b",
                declaration: `color: ${alertsData[props.alert_type].linkColor};`
            }
        ]);

        if (!props.custom_padding) {
            additionalCss.push([
                {
                    selector: "%%order_class%%",
                    declaration: `padding: 20px;`
                }
            ]);
        }

        // Align Items
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-alert",
                declaration: `align-items: ${props.align_items};`
            }
        ]);

        // Icon Size
        let icon_size = [];
        let iconStyle = [];
        if ("on" === props.use_icon) {
            iconStyle = renderFontStyle(
                props,
                "icon",
                "%%order_class%% .dtq-alert-icon"
            );

            icon_size = get_responsive_styles(
                props,
                "icon_size",
                "%%order_class%% .dtq-alert-icon",
                { primary: "font-size", important: false },
                { default: "40px" }
            );
        } else {
            icon_size = get_responsive_styles(
                props,
                "icon_size",
                "%%order_class%% .dtq-alert-icon img",
                { primary: "width", important: false },
                { default: "40px" }
            );
        }

        // Icon Color
        if ("on" === props.use_icon && props.icon_color) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-alert-icon",
                    declaration: `color: ${props.icon_color};`
                }
            ]);

            if (1 === props.hover_enabled && props.icon_color__hover) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-alert-icon i",
                        declaration: `color: ${props.icon_color__hover};`
                    }
                ]);
            }
        }

        // Icon Spacing Gap
        let icon_spacing = get_responsive_styles(
            props,
            "icon_spacing",
            "%%order_class%% .dtq-alert-icon",
            { primary: "margin-right", important: false },
            { default: "20px" }
        );

        // Icon Box
        let icon_width = [],
            icon_bg = [],
            icon_height = [];

        if ("on" === props.use_icon_box) {
            if (!props.icon_bg_color) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-alert-icon",
                        declaration: `background-color: rgba(0,0,0,.1);`
                    }
                ]);
            } else {
                icon_bg = _getCustomBgCss(
                    props,
                    "icon",
                    "%%order_class%% .dtq-alert-icon",
                    "%%order_class%%:hover .dtq-alert-icon"
                );
            }
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-alert-icon",
                    declaration: `
						display: flex;
						align-items: center;
						justify-content: center;
					`
                }
            ]);
            icon_width = get_responsive_styles(
                props,
                "icon_width",
                "%%order_class%% .dtq-alert-icon",
                { primary: "width", important: false },
                { default: "80px" }
            );
            icon_height = get_responsive_styles(
                props,
                "icon_height",
                "%%order_class%% .dtq-alert-icon",
                { primary: "height", important: false },
                { default: "80px" }
            );
        } else {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-alert-icon",
                    declaration: `
						overflow: visible!important;
						border-radius: 0 0 0 0!important;
					`
                }
            ]);
        }

        // Dismiss
        let dismiss_size = get_responsive_styles(
            props,
            "dismiss_size",
            "%%order_class%% .dtq-alert-dismiss",
            { primary: "font-size", important: false },
            { default: "22px" }
        );
        let dismiss_spacing = get_responsive_styles(
            props,
            "dismiss_spacing",
            "%%order_class%% .dtq-alert-dismiss",
            { primary: "margin-left", important: false },
            { default: "20px" }
        );

        if (props.dismiss_color) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-alert-dismiss",
                    declaration: `color: ${props.dismiss_color};`
                }
            ]);

            if (1 === props.hover_enabled && props.dismiss_color__hover) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-alert-dismiss",
                        declaration: `color: ${props.dismiss_color__hover};`
                    }
                ]);
            }
        }

        let title_spacing = [];
        if (props.title_spacing) {
            title_spacing = get_responsive_styles(
                props,
                "title_spacing",
                "%%order_class%% .dtq-alert .dtq-alert-title",
                { primary: "padding-bottom", important: false },
                { default: "0px" }
            );
        }

        return additionalCss
            .concat(icon_size)
            .concat(icon_spacing)
            .concat(dismiss_size)
            .concat(icon_height)
            .concat(icon_bg)
            .concat(iconStyle)
            .concat(title_spacing)
            .concat(dismiss_spacing)
            .concat(icon_width);
    }

    render_icon = () => {
        let props = this.props,
            use_icon = props.use_icon,
            utils = window.ET_Builder.API.Utils,
            Icon = props.icon ? utils.processFontIcon(props.icon) : "";

        if (use_icon === "on" && Icon) {
            return (
                <div className="dtq-alert-icon">
                    <i className="dtq-et-icon">{Icon}</i>
                </div>
            );
        }
    };

    render_title = () => {
        if (this.props.title) {
            let Title = this.props.title_level ? this.props.title_level : "h5";
            return (
                <Title className="dtq-alert-title">
                    {this.props.dynamic.title.render()}
                </Title>
            );
        }
    };

    render_description = () => {
        if (this.props.description) {
            return (
                <div className="dtq-alert-desc">
                    {this.props.dynamic.description.render()}
                </div>
            );
        }
    };

    render_figure = () => {
        if ("on" === this.props.use_icon) {
            return this.render_icon();
        }

        if (this.props.image) {
            return (
                <div className="dtq-alert-icon">
                    <img src={this.props.dynamic.image.value} alt="" />
                </div>
            );
        }
    };

    render_dismiss = () => {
        if ("on" === this.props.show_dismiss) {
            return (
                <div className="dtq-alert-dismiss">
                    {`✕`}
                </div>
            );
        }
    };

    render() {
        return (
            <div
                className={`dtq-module dtq-alert dtq-alert-${this.props.alert_type}`}
            >
                {this.render_figure()}
                <div className="dtq-alert-content">
                    {this.render_title()}
                    {this.render_description()}
                </div>
                {this.render_dismiss()}
            </div>
        );
    }
}

export default DTQ_InlineNotice;
