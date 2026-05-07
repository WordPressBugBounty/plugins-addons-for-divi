// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles,
    renderFontStyle,
    render_swapped_image,
    _getBadgeStyles,
    _getButtonsStyles,
    _getOverlayStyleCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_Review extends Component {
    static slug = "ba_review";

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
        let additionalCss = [],
            img_height = [],
            img_pos = props.img_pos,
            star_spacing = props.star_spacing,
            star_color = props.star_color,
            star_active_color = props.star_active_color,
            rating_text_spacing = props.rating_text_spacing,
            rating_text_color = props.rating_text_color,
            btn_spacing_top = props.btn_spacing_top,
            btn_spacing_top_tablet = props.btn_spacing_top_tablet,
            btn_spacing_top_phone = props.btn_spacing_top_phone,
            btn_spacing_top_last_edited = props.btn_spacing_top_last_edited,
            btn_spacing_top_responsive_status =
                btn_spacing_top_last_edited &&
                btn_spacing_top_last_edited.startsWith("on"),
            img_width = props.img_width,
            img_width_tablet = props.img_width_tablet,
            img_width_phone = props.img_width_phone,
            img_width_last_edited = props.img_width_last_edited,
            img_width_responsive_status =
                img_width_last_edited && img_width_last_edited.startsWith("on");

        // Image position
        if (img_pos === "top") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-review",
                    declaration: `flex-direction: column;`
                }
            ]);
        } else if (img_pos === "bottom") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-review",
                    declaration: `flex-direction: column-reverse;`
                }
            ]);
        } else if (img_pos === "left") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-review",
                    declaration: `flex-direction: row;`
                }
            ]);
        } else if (img_pos === "right") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-review",
                    declaration: `flex-direction: row-reverse;`
                }
            ]);
        }

        // Image height
        if (props.img_height) {
            img_height = get_responsive_styles(
                props,
                "img_height",
                "%%order_class%% .dtq-rating-figure img",
                { primary: "height" },
                { default: "auto" }
            );
        }

        // Image width
        if (img_pos === "left" || img_pos === "right") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-rating-figure",
                    declaration: `flex: 0 0 ${img_width}; max-width: ${img_width};`
                }
            ]);
            if (img_width_tablet && img_width_responsive_status) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-rating-figure",
                        device: "tablet",
                        declaration: `flex: 0 0 ${img_width_tablet}; max-width: ${img_width_tablet};`
                    }
                ]);
            }
            if (img_width_phone && img_width_responsive_status) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-rating-figure",
                        device: "phone",
                        declaration: `flex: 0 0 ${img_width_phone}; max-width: ${img_width_phone};`
                    }
                ]);
            }
        }

        // Image padding
        let img_padding = get_responsive_styles(
            props,
            "img_padding",
            "%%order_class%% .dtq-rating-figure img",
            { primary: "padding" },
            { default: "0px|0px|0px|0px" }
        );

        // texts
        let title_bottom_spacing = get_responsive_styles(
            props,
            "title_bottom_spacing",
            "%%order_class%% .dtq-rating-star-title",
            { primary: "padding-bottom" },
            { default: "0px" }
        );

        // content Alignment
        let content_alignment = get_responsive_styles(
            props,
            "content_alignment",
            "%%order_class%% .dtq-review-content",
            { primary: "text-align", important: true },
            { default: "left" }
        );
        let ratings_content_alignment = get_responsive_styles(
            props,
            "content_alignment",
            "%%order_class%% .dtq-ratings",
            { primary: "justify-content", important: false },
            { default: "left" }
        );

        // Content padding
        let content_padding = get_responsive_styles(
            props,
            "content_padding",
            "%%order_class%% .dtq-review-content",
            { primary: "padding" },
            { default: "15px|0px|0px|0px" }
        );

        // rating bottom spacing
        let rating_bottom_spacing = get_responsive_styles(
            props,
            "rating_bottom_spacing",
            "%%order_class%% .dtq-ratings",
            { primary: "padding-bottom" },
            { default: "0px|0px|0px|0px" }
        );
        // Star size
        let star_size = get_responsive_styles(
            props,
            "star_size",
            "%%order_class%% .dtq-stars-wrap .dtq-star",
            { primary: "font-size" },
            { default: "23px" }
        );

        // star spacing
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-stars-wrap .dtq-star",
                declaration: `
                margin-right:${star_spacing};
                margin-left:${star_spacing};`
            }
        ]);
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-stars-wrap",
                declaration: `
                margin-right:-${star_spacing};
                margin-left:-${star_spacing};`
            }
        ]);

        // star color
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-star",
                declaration: `color:${star_color};`
            }
        ]);
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-stars-act .dtq-star",
                declaration: `color:${star_active_color};`
            }
        ]);

        // Ratings text
        let rating_text_size = get_responsive_styles(
            props,
            "rating_text_size",
            "%%order_class%% .dtq-ratings-number",
            { primary: "font-size" },
            { default: "16px" }
        );

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-ratings-number",
                declaration: `
                padding-left:${rating_text_spacing};
                color:${rating_text_color};`
            }
        ]);

        //Button
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-rating-btn-wrap",
                declaration: `padding-top: ${btn_spacing_top}!important;`
            }
        ]);

        if (btn_spacing_top_tablet && btn_spacing_top_responsive_status) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-rating-btn-wrap",
                    device: "tablet",
                    declaration: `padding-top: ${btn_spacing_top_tablet}!important;`
                }
            ]);
        }

        if (btn_spacing_top_phone && btn_spacing_top_responsive_status) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-rating-btn-wrap",
                    device: "phone",
                    declaration: `padding-top: ${btn_spacing_top_phone}!important;`
                }
            ]);
        }

        let buttonStyles = _getButtonsStyles(
                "button",
                props,
                "%%order_class%% .dtq-review .dtq-rating-btn"
            ),
            overlay_styles = _getOverlayStyleCss(
                props,
                "image",
                "%%order_class%%"
            );

        //badge
        let badge_styles = _getBadgeStyles(
            props,
            "badge",
            "%%order_class%% .dtq-review-badge",
            "%%order_class%%:hover .dtq-review-badge",
            {
                position: "right_top",
                offset_x: "34px",
                offset_y: "15px",
                badge_padding: "5px|15px|5px|15px",
                bg: "#F3B325"
            }
        );

        let overlayIconStyle = renderFontStyle(
            props,
            "overlay_icon",
            "%%order_class%% .dtq-overlay .dtq-overlay-icon"
        );
        return additionalCss
            .concat(overlayIconStyle)
            .concat(content_alignment)
            .concat(ratings_content_alignment)
            .concat(badge_styles)
            .concat(rating_text_size)
            .concat(title_bottom_spacing)
            .concat(img_padding)
            .concat(img_height)
            .concat(star_size)
            .concat(rating_bottom_spacing)
            .concat(content_padding)
            .concat(overlay_styles)
            .concat(buttonStyles);
    }

    render_image = url => {
        let props = this.props,
            utils = window.ET_Builder.API.Utils,
            overlay_icon = props.overlay_icon
                ? utils.processFontIcon(props.overlay_icon)
                : "",
            image_alt = props.image_alt ? props.image_alt : "";

        if (url) {
            return (
                <div className="dtq-rating-figure">
                    {this.render_badge()}
                    <div className="dtq-overlay">
                        <i className="dtq-overlay-icon">{overlay_icon}</i>
                    </div>
                    <img className="dtq-img-cover" src={url} alt={image_alt} />
                </div>
            );
        }
    };

    _renderTitle = () => {
        if (this.props.title) {
            let Title = this.props.title_level ? this.props.title_level : "h3";
            return (
                <Title className="dtq-rating-star-title">
                    {this.props.dynamic.title.render()}
                </Title>
            );
        }
    };

    _renderDescription = () => {
        if (this.props.description) {
            return (
                <div className="dtq-rating-star-desc">
                    {this.props.dynamic.description.render()}
                </div>
            );
        }
    };

    _renderButton = () => {
        let props = this.props,
            utils = window.ET_Builder.API.Utils;

        if (props.use_button === "on") {
            let button_link = props.button_link ? props.button_link : "",
                button_icon = props.button_icon
                    ? utils.processFontIcon(props.button_icon)
                    : "5",
                button_target =
                    "new_tab" === props.button_link_target ? "_blank" : "",
                button_rel = utils.linkRel(props.button_rel),
                button_classes = {
                    et_pb_button: true,
                    et_pb_custom_button_icon: props.button_icon
                };

            return (
                <div className="dtq-rating-btn-wrap">
                    <a
                        className={`${utils.classnames(
                            button_classes
                        )} dtq-btn-default dtq-rating-btn`}
                        href={`${button_link}`}
                        target={`${button_target}`}
                        rel={button_rel}
                        data-icon={button_icon}
                    >
                        {this.props.dynamic.button_text.render()}
                    </a>
                </div>
            );
        }
    };

    _renderStars = icon => {
        let props = this.props,
            stars = [],
            scale = props.scale;

        for (var i = 1; i <= scale; i++) {
            stars.push(
                <span key={i} className="dtq-star">
                    {icon}
                </span>
            );
        }
        return stars;
    };

    _renderRaringsNumber = () => {
        let scale = this.props.scale,
            rating = this.props.rating,
            show_number = this.props.show_number;

        if (show_number === "on") {
            return (
                <div className="dtq-ratings-number">
                    ({rating}/{scale})
                </div>
            );
        }
    };
    render_badge = () => {
        let use_badge = this.props.use_badge;
        if (use_badge !== "off") {
            return (
                <div className={`dtq-review-badge`}>
                    {this.props.dynamic.badge_text.render()}
                </div>
            );
        }
    };
    render() {
        let props = this.props,
            Tag = "div",
            attr = {},
            url = "",
            opt_url = props.link_option_url,
            linkTarget = "",
            img_anim = props.img_anim ? props.img_anim : "none",
            classes = `dtq-hover--${img_anim}`,
            scale = props.scale,
            rating = props.rating,
            width = (100 * parseFloat(rating)) / parseFloat(scale);

        if (typeof opt_url === "string" && opt_url !== "") {
            Tag = "a";
            url = props.link_option_url;
            linkTarget =
                props.link_option_url_new_window === "off" ? "_self" : "_blank";
            attr["target"] = linkTarget;
            attr["href"] = url;
        }

        return (
            <Tag {...attr} className={`dtq-module dtq-review ${classes}`}>
                {render_swapped_image(
                    "image",
                    props,
                    this.render_image,
                    this.state
                )}
                <div className="dtq-review-content">
                    {this._renderTitle()}
                    <div className="dtq-ratings dtq-flex">
                        <div
                            className="dtq-stars-wrap"
                            style={{ "--active-width": width + "%" }}
                        >
                            <div className="dtq-stars-inact">
                                {this._renderStars("☆")}
                            </div>
                            <div className="dtq-stars-act">
                                {this._renderStars("★")}
                            </div>
                        </div>
                        {this._renderRaringsNumber()}
                    </div>
                    {this._renderDescription()}
                    {this._renderButton()}
                </div>
            </Tag>
        );
    }
}

export default DTQ_Review;
