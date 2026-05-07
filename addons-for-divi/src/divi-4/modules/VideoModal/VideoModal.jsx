// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles,
    _getCustomBgCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_Video_Popup extends Component {
    static slug = "ba_video_popup";

    static css(props) {
        let additionalCss = [],
            wave_bg = props.wave_bg,
            img_height = [],
            image_overlay = [],
            icon_alignment = [],
            icon_height = [],
            icon_width = [],
            icon_size = [],
            text_box_height = [],
            text_box_width = [],
            use_animation = props.use_animation,
            icon_bg = props.icon_bg,
            icon_bg__hover = props.icon_bg__hover,
            icon_color = props.icon_color,
            icon_radius = props.icon_radius,
            icon_color__hover = props.icon_color__hover,
            icon_size__hover = props.icon_size__hover,
            icon_opacity = props.icon_opacity,
            trigger_element = props.trigger_element,
            icon_opacity__hover = props.icon_opacity__hover,
            use_text_box = props.use_text_box,
            icon_spacing = [];

        let content_align = get_responsive_styles(
            props,
            "icon_alignment",
            "%%order_class%% .dtq-video-popup-trigger",
            { primary: "justify-content", important: true },
            { default: "center" }
        );

        if ("text" !== trigger_element) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-video-popup svg",
                    declaration: `fill: ${icon_color};`
                }
            ]);

            if (icon_color__hover) {
                additionalCss.push([
                    {
                        selector: "%%order_class%%:hover .dtq-video-popup svg",
                        declaration: `fill: ${icon_color__hover};`
                    }
                ]);
            }

            icon_height = get_responsive_styles(
                props,
                "icon_height",
                "%%order_class%% .dtq-video-popup .dtq-video-popup-icon",
                { primary: "height", important: false },
                { default: "initial" }
            );

            icon_width = get_responsive_styles(
                props,
                "icon_width",
                "%%order_class%% .dtq-video-popup .dtq-video-popup-icon",
                { primary: "width", important: false },
                { default: "initial" }
            );

            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-video-popup .dtq-video-popup-icon",
                    declaration: `border-radius: ${icon_radius};`
                }
            ]);

            icon_size = get_responsive_styles(
                props,
                "icon_size",
                "%%order_class%% .dtq-video-popup svg",
                { primary: "width", important: false },
                { default: "60px" }
            );

            if (icon_size__hover) {
                additionalCss.push([
                    {
                        selector: "%%order_class%%:hover .dtq-video-popup svg",
                        declaration: `width: ${icon_size__hover};`
                    }
                ]);
            }

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-video-popup svg",
                    declaration: `opacity: ${icon_opacity};`
                }
            ]);

            if (icon_opacity__hover) {
                additionalCss.push([
                    {
                        selector: "%%order_class%%:hover .dtq-video-popup svg",
                        declaration: `opacity: ${icon_opacity__hover};`
                    }
                ]);
            }

            if (icon_bg) {
                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .dtq-video-popup .dtq-video-popup-icon",
                        declaration: `background: ${icon_bg};`
                    }
                ]);
            }

            if (icon_bg__hover) {
                additionalCss.push([
                    {
                        selector:
                            "%%order_class%%:hover .dtq-video-popup .dtq-video-popup-icon",
                        declaration: `background: ${icon_bg__hover};`
                    }
                ]);
            }

            if (props.hover_enabled === 1) {
                if (icon_opacity__hover) {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-video-popup svg",
                            declaration: `opacity: ${icon_opacity__hover};`
                        }
                    ]);
                }
                if (icon_size__hover) {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-video-popup svg",
                            declaration: `width: ${icon_size__hover}!important;`
                        }
                    ]);
                }
                if (icon_color__hover) {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-video-popup svg",
                            declaration: `fill: ${icon_color__hover};`
                        }
                    ]);
                }

                if (icon_bg__hover) {
                    additionalCss.push([
                        {
                            selector: "%%order_class%% .dtq-video-popup svg",
                            declaration: `background: ${icon_bg};`
                        }
                    ]);
                }
            }
        }

        if ("icon" !== trigger_element) {
            if ("on" === use_text_box) {
                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .dtq-video-popup .dtq-video-popup-text",
                        declaration: `border-radius: ${props.text_box_radius};`
                    }
                ]);

                text_box_height = get_responsive_styles(
                    props,
                    "text_box_height",
                    "%%order_class%% .dtq-video-popup .dtq-video-popup-text",
                    { primary: "height", important: false },
                    { default: "80px" }
                );

                text_box_width = get_responsive_styles(
                    props,
                    "text_box_width",
                    "%%order_class%% .dtq-video-popup .dtq-video-popup-text",
                    { primary: "width", important: false },
                    { default: "80px" }
                );

                additionalCss.push([
                    {
                        selector:
                            "%%order_class%% .dtq-video-popup .dtq-video-popup-text",
                        declaration: `background: ${props.text_box_bg};`
                    }
                ]);

                if (props.text_box_bg__hover) {
                    additionalCss.push([
                        {
                            selector:
                                "%%order_class%%:hover .dtq-video-popup .dtq-video-popup-text",
                            declaration: `background: ${props.text_box_bg__hover};`
                        }
                    ]);
                }
            }

            if ("on" === props.use_overlay) {
                additionalCss.push([
                    {
                        selector: "%%order_class%% .dtq-video-popup-trigger",
                        declaration: `justify-content: center; position: absolute; left: 0; top: 0;`
                    }
                ]);
            }
        }

        if ("icon_text" === trigger_element) {
            icon_spacing = get_responsive_styles(
                props,
                "icon_spacing",
                "%%order_class%% .dtq-video-popup-icon",
                { primary: "margin-right", important: false },
                { default: "20px" }
            );
        }

        if ("on" === props.use_overlay) {
            img_height = get_responsive_styles(
                props,
                "img_height",
                "%%order_class%% .dtq-video-popup-figure",
                { primary: "height", important: false },
                { default: "auto" }
            );

            image_overlay = _getCustomBgCss(
                props,
                "image",
                "%%order_class%% .dtq-video-popup-figure:before",
                "%%order_class%%:hover .dtq-video-popup-figure:before"
            );

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-video-popup-trigger",
                    declaration: `justify-content: center; position: absolute; left: 0; top: 0;`
                }
            ]);
        }

        // Animation
        if ("on" === use_animation) {
            let selector = "%%order_class%% .dtq-video-popup a:after";
            if ("icon_text" === trigger_element) {
                selector =
                    "%%order_class%% .dtq-video-popup .dtq-video-popup-icon:after";
            }

            if ("icon" !== trigger_element) {
                additionalCss.push([
                    {
                        selector,
                        declaration: `border-radius: ${props.text_box_radius};`
                    }
                ]);
            }
            if ("text" !== trigger_element) {
                additionalCss.push([
                    {
                        selector,
                        declaration: `border-radius: ${props.icon_radius};`
                    }
                ]);
            }

            additionalCss.push([
                {
                    selector,
                    declaration: `
                        content: "";
                        -webkit-box-shadow: 0 0 0 15px ${wave_bg}, 0 0 0 30px ${wave_bg}, 0 0 0 45px ${wave_bg};
                        box-shadow: 0 0 0 15px ${wave_bg}, 0 0 0 30px ${wave_bg}, 0 0 0 45px ${wave_bg};`
                }
            ]);
        }

        // Custom Icon
        if ("custom" === props.icon) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-video-popup svg",
                    declaration: `width: 100%; height: 100%;`
                }
            ]);
        }

        return additionalCss
            .concat(icon_spacing)
            .concat(content_align)
            .concat(image_overlay)
            .concat(icon_size)
            .concat(icon_height)
            .concat(icon_width)
            .concat(text_box_height)
            .concat(text_box_width)
            .concat(icon_alignment)
            .concat(img_height);
    }

    renderTrigger = () => {
        let icons = {
            "1": `<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 511.999 511.999"><g><path d="M443.86,196.919L141.46,10.514C119.582-2.955,93.131-3.515,70.702,9.016c-22.429,12.529-35.819,35.35-35.819,61.041  v371.112c0,38.846,31.3,70.619,69.77,70.829c0.105,0,0.21,0.001,0.313,0.001c12.022-0.001,24.55-3.769,36.251-10.909 c9.413-5.743,12.388-18.029,6.645-27.441c-5.743-9.414-18.031-12.388-27.441-6.645c-5.473,3.338-10.818,5.065-15.553,5.064 c-14.515-0.079-30.056-12.513-30.056-30.898V70.058c0-11.021,5.744-20.808,15.364-26.183c9.621-5.375,20.966-5.135,30.339,0.636 l302.401,186.405c9.089,5.596,14.29,14.927,14.268,25.601c-0.022,10.673-5.261,19.983-14.4,25.56L204.147,415.945 c-9.404,5.758-12.36,18.049-6.602,27.452c5.757,9.404,18.048,12.36,27.452,6.602l218.611-133.852  c20.931-12.769,33.457-35.029,33.507-59.55C477.165,232.079,464.729,209.767,443.86,196.919z"/></g></svg>`,

            "2": `<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 494.148 494.148"><g><g><path d="M405.284,201.188L130.804,13.28C118.128,4.596,105.356,0,94.74,0C74.216,0,61.52,16.472,61.52,44.044v406.124 c0,27.54,12.68,43.98,33.156,43.98c10.632,0,23.2-4.6,35.904-13.308l274.608-187.904c17.66-12.104,27.44-28.392,27.44-45.884 C432.632,229.572,422.964,213.288,405.284,201.188z"/> </g></g></svg>`,

            "3": `<svg viewBox="0 0 494.942 494.942" xmlns="http://www.w3.org/2000/svg"><path d="m35.353 0 424.236 247.471-424.236 247.471z"/></svg>`,

            "4": `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60"><path d="M30,0C13.458,0,0,13.458,0,30s13.458,30,30,30s30-13.458,30-30S46.542,0,30,0z M45.563,30.826l-22,15 C23.394,45.941,23.197,46,23,46c-0.16,0-0.321-0.038-0.467-0.116C22.205,45.711,22,45.371,22,45V15c0-0.371,0.205-0.711,0.533-0.884 c0.328-0.174,0.724-0.15,1.031,0.058l22,15C45.836,29.36,46,29.669,46,30S45.836,30.64,45.563,30.826z"/> <g></g></svg>`,

            "5": `<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 485 485"><g><path d="M413.974,71.026C368.171,25.225,307.274,0,242.5,0S116.829,25.225,71.026,71.026C25.225,116.829,0,177.726,0,242.5 s25.225,125.671,71.026,171.474C116.829,459.775,177.726,485,242.5,485s125.671-25.225,171.474-71.026 C459.775,368.171,485,307.274,485,242.5S459.775,116.829,413.974,71.026z M242.5,455C125.327,455,30,359.673,30,242.5 S125.327,30,242.5,30S455,125.327,455,242.5S359.673,455,242.5,455z"/><polygon points="181.062,336.575 343.938,242.5 181.062,148.425"/></g></svg>`,

            "6": `<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 310 310"><g><path d="M297.917,64.645c-11.19-13.302-31.85-18.728-71.306-18.728H83.386c-40.359,0-61.369,5.776-72.517,19.938 C0,79.663,0,100.008,0,128.166v53.669c0,54.551,12.896,82.248,83.386,82.248h143.226c34.216,0,53.176-4.788,65.442-16.527 C304.633,235.518,310,215.863,310,181.835v-53.669C310,98.471,309.159,78.006,297.917,64.645z M199.021,162.41l-65.038,33.991 c-1.454,0.76-3.044,1.137-4.632,1.137c-1.798,0-3.592-0.484-5.181-1.446c-2.992-1.813-4.819-5.056-4.819-8.554v-67.764 c0-3.492,1.822-6.732,4.808-8.546c2.987-1.814,6.702-1.938,9.801-0.328l65.038,33.772c3.309,1.718,5.387,5.134,5.392,8.861 C204.394,157.263,202.325,160.684,199.021,162.41z"/></g></svg>`,

            "custom": `<img src="${this.props.custom_play_icon}" alt="icon">`
        };

        console.log(this.props.custom_play_icon);

        return (
            <a href="/">
                {("icon" === this.props.trigger_element ||
                    "icon_text" === this.props.trigger_element) && (
                    <span
                        className="dtq-video-popup-icon"
                        dangerouslySetInnerHTML={{
                            __html: icons[this.props.icon]
                        }}
                    ></span>
                )}
                {("text" === this.props.trigger_element ||
                    "icon_text" === this.props.trigger_element) && (
                    <span className="dtq-video-popup-text">
                        {this.props.text}
                    </span>
                )}
                
            </a>
        );
    };

    render() {
        return (
            <div className="dtq-module dtq-video-popup">
                <div className={`dtq-video-popup-trigger`}>
                    {this.renderTrigger()}
                </div>
                {"on" === this.props.use_overlay && (
                    <div className="dtq-video-popup-figure">
                        <img src={this.props.image} alt="" />
                    </div>
                )}
            </div>
        );
    }
}

export default DTQ_Video_Popup;
