import React, { Component } from "react";
import {
    get_pattern,
    get_responsive_styles,
    hex2rgba,
    renderFontStyle
} from "@ModuleHelper";
import {
    SHAPE_1,
    SHAPE_10,
    SHAPE_11,
    SHAPE_12,
    SHAPE_13,
    SHAPE_14,
    SHAPE_15,
    SHAPE_16,
    SHAPE_17,
    SHAPE_18,
    SHAPE_2,
    SHAPE_3,
    SHAPE_4,
    SHAPE_5,
    SHAPE_6,
    SHAPE_7,
    SHAPE_8,
    SHAPE_9
} from "./shapes";
import "./style.css";

class DTQ_Advanced_Divider extends Component {
    static slug = "ba_advanced_divider";

    static css(props) {
        let additionalCss = [],
            use_mask = props.use_mask,
            use_shape = props.use_shape,
            mask_url = props.mask_url,
            active_element = props.active_element,
            text_alignment = [],
            content_alignment = props.content_alignment,
            content_alignment_tablet = props.content_alignment_tablet,
            content_alignment_phone = props.content_alignment_phone,
            content_alignment_last_edited = props.content_alignment_last_edited,
            content_alignment_responsive_status =
                content_alignment_last_edited &&
                content_alignment_last_edited.startsWith("on"),
            mask_size = props.mask_size,
            mask_pos = props.mask_pos,
            mask_hz_pos = props.mask_hz_pos,
            mask_vr_pos = props.mask_vr_pos,
            icon_color = props.icon_color,
            icon_size = props.icon_size,
            icon_bg = props.icon_bg,
            img_width = props.img_width,
            border_type = props.border_type,
            border_style_classic = props.border_style_classic,
            border_style_pattern = props.border_style_pattern,
            border_height = props.border_height,
            border_color = props.border_color,
            border_gap = props.border_gap,
            border_weight = props.border_weight,
            mask_repeat = props.mask_repeat,
            shape_weight = props.shape_weight,
            shape_color = props.shape_color,
            icon_padding = [],
            shape_margin = [],
            text_background = [],
            shape_width = [];

        if (active_element !== "image" && use_mask === "on" && mask_url) {
            let selector = "%%order_class%% .dtq-divider__icon i";
            if (active_element === "text") {
                selector = "%%order_class%% .dtq-divider__text";
            }

            additionalCss.push([
                {
                    selector,
                    declaration: `
                color: transparent!important;
                background-image: url('${mask_url}');
                background-size: ${mask_size};
                background-repeat: ${mask_repeat};
                -webkit-background-clip: text;
                -moz-background-clip: text;
                -o-background-clip: text;
                background-clip: text;`
                }
            ]);

            if (mask_pos !== "custom") {
                additionalCss.push([
                    {
                        selector,
                        declaration: `background-position: ${mask_pos};`
                    }
                ]);
            } else {
                additionalCss.push([
                    {
                        selector,
                        declaration: `background-position: ${mask_vr_pos} ${mask_hz_pos};`
                    }
                ]);
            }
        }

        if (use_shape === "off") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-divider",
                    declaration: `align-items: center;`
                }
            ]);
            if (content_alignment === "left") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-divider__element",
                        declaration: `padding-right: ${border_gap};`
                    }
                ]);
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-divider__border-start",
                        declaration: `display: none;`
                    }
                ]);
            } else if (content_alignment === "right") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-divider__element",
                        declaration: `padding-left: ${border_gap};`
                    }
                ]);
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-divider__border-end",
                        declaration: `display: none;`
                    }
                ]);
            } else if (content_alignment === "center") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-divider__element",
                        declaration: `padding-right: ${border_gap};padding-left: ${border_gap};`
                    }
                ]);
            }

            if (
                content_alignment_tablet &&
                content_alignment_responsive_status
            ) {
                if (content_alignment_tablet === "left") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__element",
                            device: "tablet",
                            declaration: `padding-right: ${border_gap};`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-start",
                            device: "tablet",
                            declaration: `display: none;`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-end",
                            device: "tablet",
                            declaration: `display: block;`
                        }
                    ]);
                } else if (content_alignment_tablet === "right") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__element",
                            device: "tablet",
                            declaration: `padding-left: ${border_gap};`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-end",
                            device: "tablet",
                            declaration: `display: none;`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-start",
                            device: "tablet",
                            declaration: `display: block;`
                        }
                    ]);
                } else if (content_alignment_tablet === "center") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__element",
                            device: "tablet",
                            declaration: `padding-right: ${border_gap};padding-left: ${border_gap};`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-end, %%order_class%% .dtq-divider__border-start",
                            device: "tablet",
                            declaration: `display: block;`
                        }
                    ]);
                }
            }

            if (
                content_alignment_phone &&
                content_alignment_responsive_status
            ) {
                if (content_alignment_phone === "left") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__element",
                            device: "phone",
                            declaration: `padding-right: ${border_gap};`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-start",
                            device: "phone",
                            declaration: `display: none;`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-end",
                            device: "phone",
                            declaration: `display: block;`
                        }
                    ]);
                } else if (content_alignment_phone === "right") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__element",
                            device: "phone",
                            declaration: `padding-left: ${border_gap};`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-end",
                            device: "phone",
                            declaration: `display: none;`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-start",
                            device: "phone",
                            declaration: `display: block;`
                        }
                    ]);
                } else if (content_alignment_phone === "center") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__element",
                            device: "phone",
                            declaration: `padding-right: ${border_gap};padding-left: ${border_gap};`
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border-start, %%order_class%% .dtq-divider__border-end",
                            device: "phone",
                            declaration: `display: block;`
                        }
                    ]);
                }
            }

            // Border Offset
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-divider__border",
                    declaration: `margin-top: ${props.border_offset};`
                }
            ]);

            // Border type
            if (border_type !== "none") {
                if (border_color.startsWith("#")) {
                    border_color = hex2rgba(border_color);
                }

                if (border_type === "classic") {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border",
                            declaration: `border-top: ${border_weight} ${border_style_classic} ${border_color};`
                        }
                    ]);
                } else if (border_type === "pattern") {
                    let pattern_bg = get_pattern(
                        border_style_pattern,
                        border_color,
                        border_weight
                    );

                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__border",
                            declaration: `
                        background-image: url("${pattern_bg}");
                        height: ${border_height};
                        background-size: ${border_height} 100%;`
                        }
                    ]);
                }
            }
        } else {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-divider",
                    declaration: `flex-direction: column;`
                }
            ]);

            text_alignment = get_responsive_styles(
                props,
                "content_alignment",
                "%%order_class%% .dtq-divider",
                { primary: "align-items" },
                { default: "center", important: false }
            );

            // shape margin
            shape_margin = get_responsive_styles(
                props,
                "shape_margin",
                "%%order_class%% .dtq-divider__shape",
                { primary: "margin" },
                { default: "0px|0px|0px|0px" }
            );

            // shape width
            shape_width = get_responsive_styles(
                props,
                "shape_width",
                "%%order_class%% .dtq-divider__shape svg",
                { primary: "width" },
                { default: "280px", important: true }
            );

            //shape weight & color
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-divider__shape svg *",
                    declaration: `stroke-width: ${shape_weight}!important;stroke: ${shape_color}!important;`
                }
            ]);
        }

        // Icon
        let iconStyle = [];
        if (active_element === "icon") {

            iconStyle = renderFontStyle(
                props,
                "icon",
                "%%order_class%% .dtq-divider__icon i"
            );

            icon_padding = get_responsive_styles(
                props,
                "icon_padding",
                "%%order_class%% .dtq-divider__icon i",
                { primary: "padding" },
                { default: "0px|0px|0px|0px" }
            );

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-divider__icon i",
                    declaration: `
                font-size: ${icon_size};`
                }
            ]);

            if (use_mask === "off") {
                if (props.icon_bg) {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-divider__icon i",
                            declaration: `background: ${icon_bg};`
                        }
                    ]);
                }
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-divider__icon i",
                        declaration: ` color: ${icon_color};`
                    }
                ]);
            }
        }

        // Image
        if (active_element === "image") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-divider__element img",
                    declaration: `width: ${img_width};`
                }
            ]);
        }

        // Text Style
        let text_padding = get_responsive_styles(
            props,
            "text_padding",
            "%%order_class%% .dtq-divider__text span",
            { primary: "padding" },
            { default: "0|0|0|0", important: false }
        );

        let text_radius = props.text_radius.split("|");
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-divider__text span",
                declaration: `border-radius: ${text_radius[1]} ${text_radius[2]} ${text_radius[3]} ${text_radius[4]};`
            }
        ]);

        if (props.text_background) {
            text_background = get_responsive_styles(
                props,
                "text_background",
                "%%order_class%% .dtq-divider__text span",
                { primary: "background" },
                { default: "transparent", important: false }
            );
        }

        return additionalCss
            .concat(iconStyle)
            .concat(text_alignment)
            .concat(icon_padding)
            .concat(text_radius)
            .concat(text_background)
            .concat(shape_margin)
            .concat(text_padding)
            .concat(shape_width);
    }

    _renderTitle = () => {
        if (this.props.title) {
            let Title = this.props.title_level ? this.props.title_level : "h2";
            return (
                <Title className="dtq-divider__text dtq-divider__element">
                    <span>{this.props.dynamic.title.render()}</span>
                </Title>
            );
        }
    };

    _renderIcon = () => {
        if (this.props.icon) {
            const utils = window.ET_Builder.API.Utils;
            const _icon = utils.processFontIcon(this.props.icon);
            return (
                <div className="dtq-divider__icon dtq-divider__element">
                    <i className='dtq-icon dtq-et-icon'>{_icon}</i>
                </div>
            );
        }
    };

    _renderImage = () => {
        if (this.props.img_url) {
            return (
                <div className="dtq-divider__image dtq-divider__element">
                    <img src={this.props.img_url} alt="" />
                </div>
            );
        }
    };

    _renderElement = () => {
        if (this.props.active_element === "text") {
            return this._renderTitle();
        } else if (this.props.active_element === "icon") {
            return this._renderIcon();
        } else if (this.props.active_element === "image") {
            return this._renderImage();
        }
    };

    _renderShape = () => {
        let shapes = {
            shape_1: SHAPE_1,
            shape_2: SHAPE_2,
            shape_3: SHAPE_3,
            shape_4: SHAPE_4,
            shape_5: SHAPE_5,
            shape_6: SHAPE_6,
            shape_7: SHAPE_7,
            shape_8: SHAPE_8,
            shape_9: SHAPE_9,
            shape_10: SHAPE_10,
            shape_11: SHAPE_11,
            shape_12: SHAPE_12,
            shape_13: SHAPE_13,
            shape_14: SHAPE_14,
            shape_15: SHAPE_15,
            shape_16: SHAPE_16,
            shape_17: SHAPE_17,
            shape_18: SHAPE_18
        };

        return { __html: shapes[this.props.shape] };
    };

    render() {
        let { use_shape } = this.props;
        return (
            <div className={`dtq-module dtq-divider`}>
                {use_shape === "off" && (
                    <div className="dtq-divider__border dtq-divider__border-start"></div>
                )}
                {this._renderElement()}
                {use_shape === "off" && (
                    <div className="dtq-divider__border dtq-divider__border-end"></div>
                )}
                {use_shape === "on" && (
                    <div
                        className="dtq-divider__shape"
                        dangerouslySetInnerHTML={this._renderShape()}
                    />
                )}
            </div>
        );
    }
}
export default DTQ_Advanced_Divider;
