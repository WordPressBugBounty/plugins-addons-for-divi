
import React, { Component } from "react";
import {
    get_responsive_styles,
    renderFontStyle,
    render_swapped_image,
    _getCustomBgCss,
    _getOverlayStyleCss
} from "@ModuleHelper";
import { ICONS } from "./socialIcons";
import "./style.css";

class DTQ_Advanced_Team extends Component {
    static slug = "ba_advanced_team";

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

        let links_margin_top = props.links_margin_top,
            links_position = props.links_position,
            links_margin_between = props.links_margin_between,
            social_icon_color = props.social_icon_color,
            social_icon_color_hover = props.social_icon_color__hover
                ? props.social_icon_color__hover
                : social_icon_color,
            links_radius = props.links_radius,
            links_bg = props.links_bg,
            links_bg_hover = props.links_bg__hover
                ? props.links_bg__hover
                : links_bg,
            links_icon_size = props.links_icon_size,
            links_height = props.links_height,
            links_width = props.links_width,
            photo_width = props.photo_width,
            photo_width_tablet = props.photo_width_tablet,
            photo_width_phone = props.photo_width_phone,
            photo_width_last_edited = props.photo_width_last_edited,
            photo_width_responsive_status =
                photo_width_last_edited &&
                photo_width_last_edited.startsWith("on"),
            photo_height = [],
            content_on_hover = props.content_on_hover,
            hover_speed = props.hover_speed,
            photo_alignment = props.photo_alignment,
            content_alignment = props.content_alignment,
            use_photo_abs = props.use_photo_abs,
            photo_offset_x = props.photo_offset_x,
            photo_offset_y = props.photo_offset_y,
            photo_placement = props.photo_placement,
            photo__placement = photo_placement.split("_");

        let overlayIconStyle = renderFontStyle(
            props,
            "overlay_icon",
            "%%order_class%% .dtq-overlay .dtq-overlay-icon"
        );

        if (props.custom_margin) {
            additionalCss.push([
                {
                    selector: "%%order_class%%.et_pb_module",
                    declaration: `margin-bottom: 0!important;`
                }
            ]);
        }

        if (content_on_hover === "off" && use_photo_abs === "on") {
            photo_width = photo_width !== "auto" ? photo_width : "50%";
        }

        if (content_on_hover === "off" && links_position === "photo") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team-social",
                    declaration: `
                    position: absolute;
                    top: 25px;
                    left : 0px;
                    width: 100%;
                    z-index: 9999;
                    justify-content: center;
                `
                }
            ]);

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team .dtq-team-social li",
                    declaration: `transform: translateY(-20px); transition: .3s;opacity: 0;`
                }
            ]);

            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-team:hover .dtq-team-social li",
                    declaration: ` transform: translateX(0) translateY(0); opacity: 1;`
                }
            ]);

            for (let i = 0; i < 10; i++) {
                additionalCss.push([
                    {
                        selector: `%%order_class%% .dtq-team .dtq-team-social li:nth-child(${i +
                            1})`,
                        declaration: `transition-delay: .${i}s;`
                    }
                ]);
            }
        }

        if (content_on_hover === "on") {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-team-content, %%order_class%% .dtq-team-content *",
                    declaration: `
                transition: ${hover_speed} all ease-in-out;`
                }
            ]);
        }

        // Text spacing
        let name_bottom_spacing = get_responsive_styles(
            props,
            "name_bottom_spacing",
            "%%order_class%% .dtq-team-content h3",
            { primary: "padding-bottom", important: true },
            { default: "0px" }
        );

        let job_bottom_spacing = get_responsive_styles(
            props,
            "job_bottom_spacing",
            "%%order_class%% .dtq-team-content-job-title",
            { primary: "padding-bottom", important: true },
            { default: "10px" }
        );

        // Content Padding
        let content_padding = get_responsive_styles(
            props,
            "content_padding",
            "%%order_class%% .dtq-team-content",
            { primary: "padding" },
            { default: "0px|0px|0px|0px" }
        );

        // Photo
        if (content_on_hover === "off" && use_photo_abs === "on") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team figure",
                    declaration: `position: absolute; z-index: 99;`
                }
            ]);

            // Photo Offset x
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team figure",
                    declaration: `${photo__placement[0]}: ${photo_offset_x};`
                }
            ]);

            // Photo Offset y
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team figure",
                    declaration: `${photo__placement[1]}: ${photo_offset_y};`
                }
            ]);

            if (photo_placement === "right_top") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-team figure",
                        declaration: `transform : translateX(50%) translateY(-50%);`
                    }
                ]);
            } else if (photo_placement === "right_bottom") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-team figure",
                        declaration: `transform : translateX(50%) translateY(50%);`
                    }
                ]);
            } else if (photo_placement === "left_bottom") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-team figure",
                        declaration: `transform : translateX(-50%) translateY(50%);`
                    }
                ]);
            } else if (photo_placement === "left_top") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-team figure",
                        declaration: `transform : translateX(-50%) translateY(-50%);`
                    }
                ]);
            }
        }

        // Photo Width & height
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-team figure",
                declaration: `width: ${photo_width};`
            }
        ]);

        if (photo_width_tablet && photo_width_responsive_status) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team figure",
                    device: "tablet",
                    declaration: `width: ${photo_width_tablet}!important;`
                }
            ]);
        }

        if (photo_width_phone && photo_width_responsive_status) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team figure",
                    device: "phone",
                    declaration: `width: ${photo_width_phone}!important;`
                }
            ]);
        }

        if (props.photo_height !== "auto") {
            photo_height = get_responsive_styles(
                props,
                "photo_height",
                "%%order_class%% .dtq-team figure",
                { primary: "height" },
                { default: "auto" }
            );

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team figure img",
                    declaration: `height: 100%; object-fit: cover;width:100%;`
                }
            ]);
        }

        // photo alignment
        if (use_photo_abs === "off") {
            if (photo_alignment === "center") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-team figure",
                        declaration: `margin-left: auto; margin-right: auto;`
                    }
                ]);
            } else if (photo_alignment === "right") {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-team figure",
                        declaration: `margin-left: auto;`
                    }
                ]);
            }
        }

        // Social Icons
        if (content_on_hover === "on" || links_position === "content") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team-social",
                    declaration: `padding-top: ${links_margin_top}!important;`
                }
            ]);
        }

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-team-social .dtq-icon",
                declaration: `
                border-radius: ${links_radius}!important;
                background-color: ${links_bg};
                height: ${links_height};
                width: ${links_width};`
            }
        ]);

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-team-social .dtq-icon svg",
                declaration: `
				  fill: ${social_icon_color}!important;
				  width: ${links_icon_size}!important;`
            }
        ]);

        if (content_alignment === "left") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team-social .dtq-icon",
                    declaration: `margin-right: ${links_margin_between};`
                }
            ]);
        } else if (content_alignment === "right") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team-social .dtq-icon",
                    declaration: `margin-left: ${links_margin_between};`
                }
            ]);
        } else {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-team-social .dtq-icon",
                    declaration: `margin-left: ${links_margin_between}; margin-right: ${links_margin_between};`
                }
            ]);
        }

        // Social Icons hover
        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-team-social .dtq-icon:hover svg",
                declaration: `fill: ${social_icon_color_hover}!important; background-color: ${links_bg_hover};`
            }
        ]);

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-team-social .dtq-icon:hover",
                declaration: `background-color: ${links_bg_hover};`
            }
        ]);

        // Overlay Styles
        let overlay_styles = _getOverlayStyleCss(
            props,
            "photo",
            "%%order_class%%"
        );
        let content_bg = _getCustomBgCss(
            props,
            "content",
            "%%order_class%% .dtq-team-content",
            "%%order_class%%:hover .dtq-team-content"
        );

        return additionalCss
            .concat(overlayIconStyle)
            .concat(overlay_styles)
            .concat(name_bottom_spacing)
            .concat(job_bottom_spacing)
            .concat(photo_height)
            .concat(content_bg)
            .concat(content_padding);
    }

    render_figure = url => {
        return <img src={url} alt="" />;
    };

    // Social links Support
    socialLinks = [
        { type: "website", name: "Website", icon: "website" },
        { type: "email", name: "Email", icon: "email" },
        { type: "facebook", name: "Facebook", icon: "facebook" },
        { type: "twitter", name: "Twitter", icon: "twitter" },
        { type: "linkedin", name: "Linkedin", icon: "linkedin" },
        { type: "instagram", name: "Instagram", icon: "instagram" },
        { type: "github", name: "Github", icon: "github" },
        { type: "behance", name: "Behance", icon: "behance" },
        { type: "dribbble", name: "Dribbble", icon: "dribbble" }
    ];

    render_links = props => {
        let availableLinkTypes = this.socialLinks.filter(
            item => !!props[item.type]
        );

        return (
            !!availableLinkTypes.length && (
                <ul
                    className={`dtq-team-social item-${props.content_alignment}`}
                >
                    {availableLinkTypes.map((item, index) => {
                        let href_prefix = "";
                        if (item.type === "email") {
                            href_prefix = "mailto:";
                        }
                        return (
                            <li key={index}>
                                <a
                                    className={`dtq-icon`}
                                    href={href_prefix + props[item.type]}
                                >
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: ICONS[item.icon]
                                        }}
                                    ></span>
                                </a>
                            </li>
                        );
                    })}
                </ul>
            )
        );
    };

    render_bio = () => {
        if (this.props.short_bio) {
            return (
                <div className="dtq-team-content-bio">
                    {this.props.dynamic.short_bio.render()}
                </div>
            );
        }
    };

    render() {
        let props = this.props,
            member_name = props.member_name,
            job_title = props.job_title,
            links_position = props.links_position
                ? props.links_position
                : "content",
            content_on_hover = props.content_on_hover,
            hover_style = props.hover_style,
            css_classes = [],
            photo_hover_animation = props.photo_hover_animation
                ? props.photo_hover_animation
                : "none",
            content_alignment = props.content_alignment
                ? props.content_alignment
                : "left",
            utils = window.ET_Builder.API.Utils,
            overlay_icon = this.props.overlay_icon
                ? utils.processFontIcon(this.props.overlay_icon)
                : "";

        if (content_on_hover === "on") {
            css_classes.push(hover_style);
        }
        css_classes.push(`dtq-hover--${photo_hover_animation}`);

        return (
            <div
                className={`dtq-module dtq-team dtq-bg-support ${css_classes.join(
                    " "
                )}`}
            >
                {content_on_hover === "off" &&
                    links_position === "photo" &&
                    this.render_links(this.props)}

                <figure className="dtq-figure dtq-team-figure">
                    <div className="dtq-overlay">
                        <i className="dtq-overlay-icon">{overlay_icon}</i>
                    </div>
                    {render_swapped_image(
                        "photo",
                        this.props,
                        this.render_figure,
                        this.state
                    )}
                </figure>

                <div
                    className={`dtq-team-content content-${content_alignment}`}
                >
                    <div className="flex-top">
                        {member_name && (
                            <h3 className="dtq-team-content-name">
                                {this.props.dynamic.member_name.render()}
                            </h3>
                        )}
                        {job_title && (
                            <div className="dtq-team-content-job-title">
                                {this.props.dynamic.job_title.render()}
                            </div>
                        )}
                        {this.render_bio()}
                    </div>
                    {(content_on_hover === "on" ||
                        links_position === "content") &&
                        this.render_links(this.props)}
                </div>
            </div>
        );
    }
}

export default DTQ_Advanced_Team;
