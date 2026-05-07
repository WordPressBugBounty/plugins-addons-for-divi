// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles, renderFontStyle, render_swapped_image,
    _getButtonsStyles,
    _getCustomBgCss,
    _getOverlayStyleCss
} from "@ModuleHelper";
import "./style.css";

class DTQ_ImageCarouselChild extends Component {
    static slug = "ba_image_carousel_child";

    constructor(props) {
        super(props);
        this.state = { width: 0 };
        this.updateWindowDimensions = this.updateWindowDimensions.bind(this);
    }

    componentDidMount() {
        this.updateWindowDimensions();
        window.addEventListener("resize", this.updateWindowDimensions);
    }

    componentWillUnmount() {
        window.removeEventListener("resize", this.updateWindowDimensions);
    }

    updateWindowDimensions() {
        this.setState({ width: window.innerWidth });
    }

    static css(props) {
        const additionalCss = [];

        let content_pos_x = props.content_pos_x
                ? props.content_pos_x
                : "center",
            content_pos_y = props.content_pos_y
                ? props.content_pos_y
                : "center",
            content_type = props.content_type ? props.content_type : "normal",
            content_alignment = props.content_alignment
                ? props.content_alignment
                : "left",
            content_offset_x = [],
            content_offset_y = [],
            image_height = props.image_height ? props.image_height : "auto",
            image_height_tablet = props.image_height_tablet,
            image_height_phone = props.image_height_phone,
            image_height_last_edited = props.image_height_last_edited,
            image_height_responsive_status =
                image_height_last_edited &&
                image_height_last_edited.startsWith("on"),
            content_padding = props.content_padding,
            content_padding_tablet = props.content_padding_tablet,
            content_padding_phone = props.content_padding_phone,
            content_padding_last_edited = props.content_padding_last_edited,
            content_padding_responsive_status =
                content_padding_last_edited &&
                content_padding_last_edited.startsWith("on");

        if (content_type === "absolute") {
            content_padding = props.content_padding
                ? props.content_padding
                : "10px|20px|10px|20px";
        } else {
            content_padding = props.content_padding
                ? props.content_padding
                : "15px|0|15px|0";
        }

        let contentPadding = content_padding.split("|");

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-carousel-item .content-inner",
                declaration: `text-align: ${content_alignment};`
            }
        ]);

        // Button
        let btn_spacing_top = get_responsive_styles(
            props,
            "btn_spacing_top",
            "%%order_class%% .dtq-btn-wrap",
            { primary: "padding-top", important: true },
            { default: "15px" }
        );

        // image height
        if (image_height !== "auto") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-image-carousel-item figure",
                    declaration: `height: ${image_height};`
                }
            ]);
            if (image_height_tablet && image_height_responsive_status) {
                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .dtq-image-carousel-item figure",
                        device: "tablet",
                        declaration: `height: ${image_height_tablet}!important;`
                    }
                ]);
            }

            if (image_height_phone && image_height_responsive_status) {
                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .dtq-image-carousel-item figure",
                        device: "phone",
                        declaration: `height: ${image_height_phone}!important;`
                    }
                ]);
            }

            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-image-carousel-item figure img",
                    declaration: `height: 100%; object-fit: cover;width:100%;`
                }
            ]);
        }

        // Texts
        let title_bottom_spacing = get_responsive_styles(
            props,
            "title_bottom_spacing",
            "%%order_class%% .dtq-image-carousel-item h3",
            { primary: "padding-bottom", important: true },
            { default: "5px" }
        );

        //content
        if (content_type === "absolute") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .content--absolute",
                    declaration: `
                    align-items: ${content_pos_x};
                    justify-content: ${content_pos_y};`
                }
            ]);

            if (content_pos_x === "flex-start") {
                content_offset_x = get_responsive_styles(
                    props,
                    "content_offset_x",
                    "%%order_class%% .content--absolute",
                    { primary: "padding-left", important: false },
                    { default: "0px" }
                );
            } else if (content_pos_x === "flex-end") {
                content_offset_x = get_responsive_styles(
                    props,
                    "content_offset_x",
                    "%%order_class%% .content--absolute",
                    { primary: "padding-right", important: false },
                    { default: "0px" }
                );
            }

            if (content_pos_y === "flex-start") {
                content_offset_y = get_responsive_styles(
                    props,
                    "content_offset_y",
                    "%%order_class%% .content--absolute",
                    { primary: "padding-top", important: false },
                    { default: "0px" }
                );
            } else if (content_pos_y === "flex-end") {
                content_offset_y = get_responsive_styles(
                    props,
                    "content_offset_y",
                    "%%order_class%% .content--absolute",
                    { primary: "padding-bottom", important: false },
                    { default: "0px" }
                );
            }
        }

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-carousel-item .content .content-inner",
                declaration: `
                padding-top: ${contentPadding[0]};
                padding-right: ${contentPadding[1]};
                padding-bottom: ${contentPadding[2]};
                padding-left: ${contentPadding[3]};`
            }
        ]);

        let content_width = get_responsive_styles(
            props,
            "content_width",
            "%%order_class%% .dtq-image-carousel-item .content .content-inner",
            { primary: "width", important: false },
            { default: "100%" }
        );

        if (content_padding_tablet && content_padding_responsive_status) {
            let contentPaddingTablet = content_padding_tablet.split("|");
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-image-carousel-item .content .content-inner",
                    device: "tablet",
                    declaration: `
                    padding-top: ${contentPaddingTablet[0]};
                    padding-right: ${contentPaddingTablet[1]};
                    padding-bottom: ${contentPaddingTablet[2]};
                    padding-left: ${contentPaddingTablet[3]};`
                }
            ]);
        }

        if (content_padding_phone && content_padding_responsive_status) {
            let contentPaddingPhone = content_padding_phone.split("|");
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-image-carousel-item .content .content-inner",
                    device: "phone",
                    declaration: `
                    padding-top: ${contentPaddingPhone[0]};
                    padding-right: ${contentPaddingPhone[1]};
                    padding-bottom: ${contentPaddingPhone[2]};
                    padding-left: ${contentPaddingPhone[3]};`
                }
            ]);
        }

        // Content Background
        let content_bg_style = _getCustomBgCss(
            props,
            "content",
            "%%order_class%% .dtq-image-carousel-item .content .content-inner",
            "%%order_class%% .dtq-image-carousel-item .content .content-inner:hover"
        );

        // Overlay Styles
        let overlay_styles = _getOverlayStyleCss(
            props,
            "photo",
            "%%order_class%% .dtq-image-carousel-item"
        );

        let buttonStyles = _getButtonsStyles(
            "button",
            props,
            "%%order_class%% .dtq-btn-wrap .dtq-btn-img-carousel"
        );

        let overlayIconStyle = renderFontStyle(
            props,
            "overlay_icon",
            "%%order_class%% .dtq-overlay .dtq-overlay-icon"
        );

        return additionalCss
            .concat(overlayIconStyle)
            .concat(buttonStyles)
            .concat(btn_spacing_top)
            .concat(title_bottom_spacing)
            .concat(content_offset_x)
            .concat(content_offset_y)
            .concat(content_bg_style)
            .concat(overlay_styles)
            .concat(content_width);
    }

    render_figure = url => {
        const utils = window.ET_Builder.API.Utils;
        let overlay_icon = this.props.overlay_icon
            ? utils.processFontIcon(this.props.overlay_icon)
            : "";

        return (
            <figure>
                <div className="dtq-overlay">
                    <i className="dtq-overlay-icon">{overlay_icon}</i>
                </div>
                <img src={url} alt="" />
            </figure>
        );
    };

    render_title = () => {
        if (this.props.title) {
            let Title = this.props.title_level ? this.props.title_level : "h3";
            return (
                <Title
                    className="dtq-image-title"
                    dangerouslySetInnerHTML={{ __html: this.props.title }}
                />
            );
        }
    };

    render_sub_title = () => {
        if (this.props.sub_title) {
            let Subtitle = this.props.subtitle_level
                ? this.props.subtitle_level
                : "h5";
            return (
                <Subtitle
                    className="dtq-image-subtitle"
                    dangerouslySetInnerHTML={{ __html: this.props.sub_title }}
                />
            );
        }
    };

    render_content = ({ title, sub_title, content_type, use_button }) => {
        if (!title && !sub_title && !use_button) return;

        content_type = content_type ? content_type : "normal";

        return (
            <div className={`content content--${content_type}`}>
                <div className="content-inner">
                    {this.render_title()}
                    {this.render_sub_title()}
                    {this.render_button()}
                </div>
            </div>
        );
    };

    render_button = () => {
        let props = this.props;
        const utils = window.ET_Builder.API.Utils;

        if (props.use_button === "on") {
            const button_link =
                typeof props.button_link !== "undefined"
                    ? props.button_link
                    : "";
            const button_icon =
                typeof props.button_icon !== "undefined"
                    ? utils.processFontIcon(props.button_icon)
                    : "5";
            const button_target =
                "new_tab" === props.button_link_target ? "_blank" : "";
            const button_rel = utils.linkRel(props.button_rel);
            const button_classes = {
                et_pb_button: true,
                et_pb_custom_button_icon: props.button_icon
            };

            return (
                <div className="dtq-btn-wrap">
                    <a
                        className={`${utils.classnames(
                            button_classes
                        )} dtq-btn-default dtq-btn-img-carousel`}
                        href={`${button_link}`}
                        target={`${button_target}`}
                        rel={button_rel}
                        data-icon={button_icon}
                    >
                        {this.props.button_text
                            ? this.props.dynamic.button_text.render()
                            : "Click Here"}
                    </a>
                </div>
            );
        }
    };

    render() {
        let content_type = this.props.content_type
                ? this.props.content_type
                : "normal",
            content_position = this.props.content_position
                ? this.props.content_position
                : "bottom",
            image_hover_animation = this.props.image_hover_animation
                ? this.props.image_hover_animation
                : "none",
            is_bottom = true;

        if (content_type === "normal") {
            if (content_position === "top") {
                is_bottom = false;
            }
        }

        return (
            <div
                className={`dtq-carousel-item dtq-image-carousel-item dtq-hover--${image_hover_animation}`}
            >
                {!is_bottom && this.render_content(this.props)}
                {render_swapped_image(
                    "photo",
                    this.props,
                    this.render_figure,
                    this.state
                )}
                {is_bottom && this.render_content(this.props)}
            </div>
        );
    }
}

export default DTQ_ImageCarouselChild;
