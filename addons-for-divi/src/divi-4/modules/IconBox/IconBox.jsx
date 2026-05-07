// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles,
    renderFontStyle,
    _getAbsoluteElementStyles,
    _getBadgeStyles,
    _getCustomBgCss
} from "@ModuleHelper";
import "./style.css";

class DTQ_Icon_Box extends Component {
    static slug = "ba_icon_box";

    static css(props) {

        let additionalCss = [],
            icon_absolute_styles = [],
            icon__placement = props.icon__placement,
            use_image = props.use_image,
            hover_enabled = props.hover_enabled,
            icon_color__hover = props.icon_color__hover,
            icon_bg_rotate = props.icon_bg_rotate,
            is_negative = icon_bg_rotate.startsWith("-"),
            icon_size = [],
            icon_width = [],
            icon_height = [],
            icon_color = props.icon_color,
            icon_spacing = [],
            icon_padding = [],
            alignment = props.content_alignment,
            title_spacing = [],
            ifMigrated = window.ETBuilderBackendDynamic.ifMigrated;

        // Default Design
        if (ifMigrated) {
            if (!props.border_color_all_box) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-iconbox-inner",
                        declaration: `border-color: #EBEBEB;`
                    }
                ]);
            }
            if (!props.border_width_all_box) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-iconbox-inner",
                        declaration: `border-width: 1px;`
                    }
                ]);
            }

            if (!props.custom_padding) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-iconbox-inner",
                        declaration: `padding: 60px 30px 60px 30px;`
                    }
                ]);
            }
        }

        if (props.custom_margin) {
            additionalCss.push([
                {
                    selector: "%%order_class%%.et_pb_module",
                    declaration: `margin-bottom: 0!important;`
                }
            ]);
        }

        // alignment
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-iconbox",
                declaration: `text-align: ${alignment};`
            }
        ]);

        if (alignment === "right") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-iconbox__icon-wrap",
                    declaration: `justify-content: flex-end;`
                }
            ]);
        } else if (alignment === "center") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-iconbox__icon-wrap",
                    declaration: `justify-content: center;`
                }
            ]);
        }

        // Icon spacing
        if (icon__placement === "normal") {
            icon_spacing = get_responsive_styles(
                props,
                "icon_spacing",
                "%%order_class%% .dtq-iconbox__icon-wrap",
                { primary: "margin-bottom", important: false },
                { default: "10px" }
            );
        }

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-iconbox .dtq-iconbox__icon i, %%order_class%% .dtq-iconbox .dtq-iconbox__icon img",
                declaration: `transform: rotate(${
                    !is_negative ? "-" : ""
                }${Math.abs(parseInt(icon_bg_rotate))}deg);`
            }
        ]);

        // icon width & height
        if ("auto" !== icon_width) {
            icon_width = get_responsive_styles(
                props,
                "icon_width",
                "%%order_class%% .dtq-iconbox .dtq-iconbox__icon",
                { primary: "width", important: false },
                { default: "auto" }
            );
        }

        if ("auto" !== icon_height) {
            icon_height = get_responsive_styles(
                props,
                "icon_height",
                "%%order_class%% .dtq-iconbox .dtq-iconbox__icon",
                { primary: "height", important: false },
                { default: "auto" }
            );
        }

        // icon padding
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-iconbox .dtq-iconbox__icon",
                declaration: `transform: rotate(${icon_bg_rotate});`
            }
        ]);

        // Icon
        let iconStyle = [];
        if (use_image === "off") {
            iconStyle = renderFontStyle(
                props,
                "icon",
                "%%order_class%% .dtq-iconbox__icon i"
            );

            // size
            icon_size = get_responsive_styles(
                props,
                "icon_size",
                "%%order_class%% .dtq-iconbox__icon i",
                { primary: "font-size", important: false },
                { default: "60px" }
            );

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-iconbox__icon i",
                    declaration: `color: ${icon_color};`
                }
            ]);

            if (icon_color__hover) {
                additionalCss.push([
                    {
                        selector: "%%order_class%%:hover .dtq-iconbox__icon i",
                        declaration: `color: ${icon_color__hover};`
                    }
                ]);

                if (hover_enabled === 1) {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-iconbox__icon i",
                            declaration: `color: ${icon_color__hover};`
                        }
                    ]);
                }
            }
        } else {
            icon_padding = get_responsive_styles(
                props,
                "icon_padding",
                "%%order_class%% .dtq-iconbox .dtq-iconbox__icon",
                { primary: "padding", important: true },
                { default: "0px|0px|0px|0px" }
            );

            icon_size = get_responsive_styles(
                props,
                "icon_size",
                "%%order_class%% .dtq-iconbox__icon img",
                { primary: "width", important: false },
                { default: "60px" }
            );
        }

        if (icon__placement === "absolute") {
            icon_absolute_styles = _getAbsoluteElementStyles(
                props,
                "icon",
                "%%order_class%% .dtq-iconbox__icon",
                { position: "left_top", offset_x: "50%", offset_y: "50%" }
            );
        }

        // text
        title_spacing = get_responsive_styles(
            props,
            "title_spacing",
            "%%order_class%% .dtq-iconbox__title",
            { primary: "padding-bottom", important: false },
            { default: "10px" }
        );

        //badge
        let badge_styles = _getBadgeStyles(
            props,
            "badge",
            "%%order_class%% .dtq-iconbox__badge",
            "%%order_class%%:hover .dtq-iconbox__badge",
            {
                position: "right_top",
                offset_x: "15px",
                offset_y: "15px",
                badge_padding: "5px|15px|5px|15px",
                bg: "#F3B325"
            }
        );

        // box bg
        let icon_bg = _getCustomBgCss(
            props,
            "icon",
            "%%order_class%% .dtq-iconbox__icon",
            "%%order_class%%:hover .dtq-iconbox__icon"
        );

        return additionalCss
            .concat(iconStyle)
            .concat(icon_absolute_styles)
            .concat(badge_styles)
            .concat(icon_height)
            .concat(icon_width)
            .concat(icon_size)
            .concat(icon_spacing)
            .concat(title_spacing)
            .concat(icon_padding)
            .concat(icon_bg);
    }

    _renderIcon = () => {
        let use_image = this.props.use_image,
            icon = this.props.icon,
            icon_image = this.props.icon_image,
            html = "";

        if (use_image === "off") {
            const utils = window.ET_Builder.API.Utils;
            const _icon = icon ? utils.processFontIcon(icon) : "";

            html = <i className="dtq-icon dtq-et-icon">{_icon}</i>;
        } else if (use_image === "on") {
            html = <img className="dtq-icon-image" src={icon_image} alt="" />;
        }

        if (icon || icon_image) {
            return (
                <div className="dtq-iconbox__icon-wrap">
                    <div className="dtq-iconbox__icon">{html}</div>
                </div>
            );
        }
    };

    _renderTitle = () => {
        if (this.props.title) {
            let Title = this.props.title_level ? this.props.title_level : "h2";
            return (
                <Title className="dtq-iconbox__title">
                    {this.props.dynamic.title.render()}
                </Title>
            );
        }
    };

    _renderDescription = () => {
        if (this.props.description) {
            return (
                <p className="dtq-iconbox__desc">
                    {this.props.dynamic.description.render()}
                </p>
            );
        }
    };

    _renderBadge = () => {
        if (this.props.badge_text) {
            return (
                <div className="dtq-iconbox__badge">
                    {this.props.dynamic.badge_text.render()}
                </div>
            );
        }
    };

    render() {
        return (
            <div className="dtq-module dtq-iconbox">
                {this.props.icon__placement === "absolute"
                    ? this._renderIcon()
                    : ""}
                {this._renderBadge()}
                <div className="dtq-iconbox-inner dtq-bg-support">
                    {this.props.icon__placement !== "absolute"
                        ? this._renderIcon()
                        : ""}
                    {this._renderTitle()}
                    {this._renderDescription()}
                </div>
            </div>
        );
    }
}

export default DTQ_Icon_Box;
