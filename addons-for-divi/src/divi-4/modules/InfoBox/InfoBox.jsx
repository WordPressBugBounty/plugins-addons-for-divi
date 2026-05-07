// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles,
    renderFontStyle,
    render_swapped_image,
    _getButtonsStyles,
    _getOverlayStyleCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_Info_Box extends Component {
    static slug = "ba_info_box";

    constructor(props) {
        super(props);

        this.state = {
            width: 0,
            isOverlay: true,
            videoId: null,
            videoType: "",
            isPlaying: false
        };
        this.updateWindowDimensions = this.updateWindowDimensions.bind(this);
    }

    componentDidMount() {
        this.updateWindowDimensions();
        window.addEventListener("resize", this.updateWindowDimensions);
        this.process_video_src();
    }

    componentWillUnmount() {
        window.removeEventListener("resize", this.updateWindowDimensions);
    }

    updateWindowDimensions() {
        this.setState({ width: window.innerWidth });
    }

    static css(props, a) {
        const additionalCss = [];

        let image_width = [],
            image_width_flex = [],
            image_height = [],
            icon_height = [],
            icon_width = [],
            icon_size = [],
            icon_bg__hover = props.icon_bg__hover,
            icon_color = props.icon_color ? props.icon_color : "#333",
            icon_color__hover = props.icon_color__hover,
            icon_padding = props.icon_padding
                ? props.icon_padding
                : "0px|0px|0px|0px",
            icon_bg = props.icon_bg ? props.icon_bg : "transparent",
            btn_spacing_top = props.btn_spacing_top
                ? props.btn_spacing_top
                : "15px",
            vo_icon_color = props.vo_icon_color,
            vo_icon_size = props.vo_icon_size,
            main_figure = props.main_figure,
            figure_placement = props.figure_placement,
            vo_bg = props.vo_bg;

        if ("video" !== main_figure) {
            if ("top" !== figure_placement) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-info-box",
                        declaration: `display: flex;`
                    }
                ]);
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-info-box-content",
                        declaration: `flex: 1 1;`
                    }
                ]);
            }

            if ("right" === figure_placement) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-info-box",
                        declaration: `flex-direction: row-reverse;`
                    }
                ]);
            }
        }

        // Figure Image
        if ("image" === main_figure) {
            image_width = get_responsive_styles(
                props,
                "image_width",
                "%%order_class%% .dtq-info-box .dtq-info-box-figure",
                { primary: "width", important: true },
                { default: "100%" }
            );
            image_width_flex = get_responsive_styles(
                props,
                "image_width",
                "%%order_class%% .dtq-info-box .dtq-info-box-figure",
                { primary: "flex", important: true },
                { default: "100%" }
            );

            if ("off" === props.equalize_content) {
                if ("top" !== figure_placement && image_height !== "auto") {
                    image_height = get_responsive_styles(
                        props,
                        "image_height",
                        "%%order_class%% .dtq-info-box .dtq-info-box-figure",
                        { primary: "width", important: true },
                        { default: "auto" }
                    );
                    additionalCss.push([
                        {
                            selector:
                                "%%order_class%% .dtq-info-box .dtq-info-box-figure img",
                            declaration:
                                "height: 100%; object-fit: cover;width:100%;"
                        }
                    ]);
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-info-box",
                            declaration: `align-items: ${props.align_items};`
                        }
                    ]);
                }
            } else {
                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .dtq-info-box .dtq-info-box-figure img",
                        declaration:
                            "height: 100%; object-fit: cover;width:100%;"
                    }
                ]);
            }
        }

        // Button
        let buttonStyles = _getButtonsStyles(
            "button",
            props,
            "%%order_class%% .dtq-info-box .dtq-btn-info-box"
        );
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-info-box-btn",
                declaration: `padding-top: ${btn_spacing_top};`
            }
        ]);

        // texts
        let title_bottom_spacing = get_responsive_styles(
            props,
            "title_bottom_spacing",
            "%%order_class%% .dtq-info-box-title",
            { primary: "padding-bottom", important: true },
            { default: "10px" }
        );

        let content_alignment = get_responsive_styles(
            props,
            "content_alignment",
            "%%order_class%%",
            { primary: "text-align", important: true },
            { default: "left" }
        );

        // Content padding
        let content_padding = get_responsive_styles(
            props,
            "content_padding",
            "%%order_class%% .dtq-info-box-content",
            { primary: "padding", important: true },
            { default: "15px|0px|0px|0px" }
        );

        // Icon
        let iconStyle = [];
        if ("icon" === main_figure) {
            iconStyle = renderFontStyle(
                props,
                "icon",
                "%%order_class%% .dtq-info-box-icon"
            );

            if ("on" === props.use_icon_box) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-info-box-icon",
                        declaration: `background: ${icon_bg};`
                    }
                ]);
                if (icon_bg__hover) {
                    additionalCss.push([
                        {
                            selector:
                                "%%order_class%% .dtq-info-box-icon:hover",
                            declaration: `background: ${icon_bg__hover};`
                        }
                    ]);
                }
                icon_height = get_responsive_styles(
                    props,
                    "icon_height",
                    "%%order_class%% .dtq-info-box-icon",
                    { primary: "height", important: false },
                    { default: "80px" }
                );
                icon_width = get_responsive_styles(
                    props,
                    "icon_width",
                    "%%order_class%% .dtq-info-box-icon",
                    { primary: "width", important: false },
                    { default: "80px" }
                );
            }
        }

        icon_size = get_responsive_styles(
            props,
            "icon_size",
            "%%order_class%% .dtq-info-box-icon i",
            { primary: "font-size", important: false },
            { default: "45px" }
        );

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-info-box-icon i",
                declaration: `color: ${icon_color};`
            }
        ]);

        if (icon_color__hover) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-info-box-icon:hover i",
                    declaration: `color: ${icon_color__hover};`
                }
            ]);
        }

        // Icon Padding
        icon_padding = get_responsive_styles(
            props,
            "icon_padding",
            "%%order_class%% .dtq-info-box-icon",
            { primary: "padding", important: false },
            { default: "0px|0px|0px|0px" }
        );

        // Overlay Styles
        let overlay_styles = _getOverlayStyleCss(
            props,
            "photo",
            "%%order_class%%"
        );

        // Video Icon
        if (vo_icon_color) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .et_pb_video_overlay .et_pb_video_play",
                    declaration: `color: ${vo_icon_color};`
                }
            ]);
        }

        if (vo_icon_size) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .et_pb_video_overlay .et_pb_video_play",
                    declaration: `font-size: ${vo_icon_size};`
                }
            ]);
        }

        if (vo_bg) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .et_pb_video_overlay_hover:hover",
                    declaration: `background: ${vo_bg};`
                }
            ]);
        }
        let overlayIconStyle = renderFontStyle(
            props,
            "overlay_icon",
            "%%order_class%% .dtq-overlay .dtq-overlay-icon"
        );
        return additionalCss
            .concat(overlayIconStyle)
            .concat(icon_size)
            .concat(content_alignment)
            .concat(title_bottom_spacing)
            .concat(icon_padding)
            .concat(iconStyle)
            .concat(content_padding)
            .concat(image_width)
            .concat(image_width_flex)
            .concat(buttonStyles)
            .concat(overlay_styles)
            .concat(icon_height)
            .concat(icon_width);
    }

    process_video_src = () => {
        let src = this.props.video;
        let youtube_pattern = /^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w-]+\?v=|embed\/|v\/)?)([\w-]+)(\S+)?$/g;
        let vimeo_pattern = /(http|https)?:\/\/(www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^/]*)\/videos\/|)(\d+)(?:|\/\?)/g;
        let is_youtube = youtube_pattern.test(src);
        let is_vimeo = vimeo_pattern.test(src);

        if (is_youtube) {
            let _src = src.split("/");
            let yt_id = _src[_src.length - 1];
            if (yt_id.startsWith("watch?v=")) {
                yt_id = yt_id.split("=")[1];
            }

            yt_id = yt_id.split("&")[0];
            this.setState({ videoType: "youtube", videoId: yt_id });
        } else if (is_vimeo) {
            let _src = src.split("/");
            let vm_id = _src[_src.length - 1];
            vm_id = vm_id.split("&")[0];
            this.setState({ videoType: "vimeo", videoId: vm_id });
        } else {
            this.setState({ videoType: "video" });
        }
    };

    onOverlayClick = () => {
        this.setState({
            isOverlay: false,
            isPlaying: true
        });

        if (this.state.videoType === "video") {
            this.refs.video.play();
        }
    };

    render_figure = url => {
        let main_figure = this.props.main_figure
                ? this.props.main_figure
                : "image",
            overlay_src = this.props.vo_src ? this.props.vo_src : "",
            photo = this.props.photo,
            utils = window.ET_Builder.API.Utils;

        if (main_figure === "image") {
            let overlay_icon = this.props.overlay_icon
                ? utils.processFontIcon(this.props.overlay_icon)
                : "";

            if (photo) {
                return (
                    <React.Fragment>
                        <div className="dtq-overlay">
                            <i className="dtq-overlay-icon">{overlay_icon}</i>
                        </div>
                        <img className="dtq-info-box-img" src={url} alt="" />
                    </React.Fragment>
                );
            }
        } else if (main_figure === "icon") {
            let Icon = this.props.icon
                ? utils.processFontIcon(this.props.icon)
                : "";

            if (this.props.icon) {
                return (
                    <div className="dtq-info-box-icon">
                        <i className="dtq-et-icon">{Icon}</i>
                    </div>
                );
            }
        } else {
            return (
                <div class="dtq-info-box-video et_pb_video">
                    <div class="et_pb_video_box dtq-info-box-video-wrap">
                        {this.state.videoType === "youtube" && (
                            <div
                                className="fluid-video-wrapper"
                                style={{ paddingTop: "75%" }}
                            >
                                <iframe
                                    width="560"
                                    title={` `}
                                    src={`https://www.youtube.com/embed/${
                                        this.state.videoId
                                    }${
                                        this.state.isPlaying
                                            ? "?autoplay=1"
                                            : ""
                                    }`}
                                    frameborder="0"
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        )}

                        {this.state.videoType === "vimeo" && (
                            <div
                                className="fluid-video-wrapper"
                                style={{ paddingTop: "75%" }}
                            >
                                <iframe
                                    title={` `}
                                    src={`https://player.vimeo.com/video/${
                                        this.state.videoId
                                    }${
                                        this.state.isPlaying
                                            ? "?autoplay=1"
                                            : ""
                                    }`}
                                    frameborder="0"
                                    allow="autoplay; fullscreen"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        )}

                        {this.state.videoType === "video" && this.props.video && (
                            <video controls ref="video">
                                <source
                                    type="video/mp4"
                                    src={`${this.props.video}`}
                                />
                            </video>
                        )}
                    </div>

                    {this.props.vo_src && (
                        <div
                            class="et_pb_video_overlay"
                            onClick={this.onOverlayClick}
                            style={{
                                backgroundImage: `url(${overlay_src})`,
                                opacity: this.state.isOverlay ? 1 : 0,
                                visibility: this.state.isOverlay
                                    ? "visible"
                                    : "hidden"
                            }}
                        >
                            <div class="et_pb_video_overlay_hover">
                                <a href={`void;`} class="et_pb_video_play">
                                    <span style={{ fontSize: 0 }}>
                                        Overlay icon
                                    </span>
                                </a>
                            </div>
                        </div>
                    )}
                </div>
            );
        }
    };

    render_title = ({ title }) => {
        if (title) {
            let Title = this.props.title_level ? this.props.title_level : "h2";
            return (
                <Title className="dtq-info-box-title">
                    {this.props.dynamic.title.render()}
                </Title>
            );
        }
    };

    render_MCE = ({ body_content }) => {
        if (body_content) {
            if (this.props.dynamic.body_content.value) {
                return (
                    <div className="dtq-mce-content">
                        {this.props.dynamic.body_content.render()}
                    </div>
                );
            } else {
                return (
                    <div
                        className="dtq-mce-content"
                        dangerouslySetInnerHTML={{ __html: body_content }}
                    ></div>
                );
            }
        }
    };

    render_button = () => {
        let props = this.props;
        let use_button = props.use_button ? props.use_button : "off";
        const utils = window.ET_Builder.API.Utils;

        if (use_button === "off") return;

        const button_link = props.button_link ? props.button_link : "#";
        const button_icon =
            props.button_icon !== "undefined"
                ? utils.processFontIcon(props.button_icon)
                : "5";
        const button_target =
            "new_tab" === props.button_link_target ? "_blank" : "";
        const rel = utils.linkRel(props.button_rel);

        const btn_classes = {
            et_fb_editing_enabled: true,
            et_pb_button: true,
            et_pb_custom_button_icon: props.button_icon
        };

        return (
            <div className="dtq-info-box-btn">
                <a
                    className={`${utils.classnames(
                        btn_classes
                    )} dtq-btn-default dtq-btn-info-box`}
                    href={`${button_link}`}
                    target={`${button_target}`}
                    rel={rel}
                    data-icon={button_icon}
                >
                    {this.props.dynamic.button_text.render()}
                </a>
            </div>
        );
    };

    render() {
        let img_anim = this.props.img_anim ? this.props.img_anim : "none";

        return (
            <div className={`dtq-info-box dtq-hover--${img_anim}`}>
                {(this.props.photo || this.props.icon || this.props.video) && (
                    <div className={`dtq-info-box-figure`}>
                        {render_swapped_image(
                            "photo",
                            this.props,
                            this.render_figure,
                            this.state
                        )}
                    </div>
                )}
                <div className={`dtq-info-box-content`}>
                    {this.render_title(this.props)}
                    {this.render_MCE(this.props)}
                    {this.render_button(this.props)}
                </div>
            </div>
        );
    }
}

export default DTQ_Info_Box;
