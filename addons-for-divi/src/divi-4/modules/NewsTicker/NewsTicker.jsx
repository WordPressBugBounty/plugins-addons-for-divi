// External Dependencies
import React, { Component } from "react";
import { _getCustomBgCss } from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_News_Ticker extends Component {
    static slug = "ba_news_ticker";

    static css(props) {
        let additionalCss = [],
            title_pos = props.title_pos,
            speed = props.speed,
            use_bullet = props.use_bullet,
            bullet_color = props.bullet_color,
            item_spacing = props.item_spacing,
            pause_on_hover = props.pause_on_hover,
            title_padding = props.title_padding.split("|"),
            title_padding_tablet = props.title_padding_tablet,
            title_padding_phone = props.title_padding_phone,
            title_padding_responsive_status =
                props.title_padding_last_edited &&
                props.title_padding_last_edited.startsWith("on"),
            slide_dir = props.slide_dir === "right" ? "reverse" : "normal";

        if (use_bullet === "on") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-wrap li a",
                    declaration: `display: inline-block;position:relative;`
                }
            ]);

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-wrap li a:before",
                    declaration: `
                    content: '';
                    position: absolute;
                    height: 6px;
                    width: 6px;
                    background: ${bullet_color};
                    top: 50%;
                    left: -15px;
                    transform: translateY(-50%);
                    border-radius: 50%;`
                }
            ]);
        }

        // Title Padding
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-news-title",
                declaration: `
                padding-top: ${title_padding[0]};
                padding-right: ${title_padding[1]};
                padding-bottom: ${title_padding[2]};
                padding-left: ${title_padding[3]};`
            }
        ]);

        // Title Padding tablet
        if (title_padding_tablet && title_padding_responsive_status) {
            title_padding_tablet = title_padding_tablet.split("|");
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-title",
                    device: "tablet",
                    declaration: `
                    padding-top: ${title_padding_tablet[0]};
                    padding-right: ${title_padding_tablet[1]};
                    padding-bottom: ${title_padding_tablet[2]};
                    padding-left: ${title_padding_tablet[3]};`
                }
            ]);
        }

        // Title Padding phone
        if (title_padding_phone && title_padding_responsive_status) {
            title_padding_phone = title_padding_phone.split("|");
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-title",
                    device: "phone",
                    declaration: `
                    padding-top: ${title_padding_phone[0]};
                    padding-right: ${title_padding_phone[1]};
                    padding-bottom: ${title_padding_phone[2]};
                    padding-left: ${title_padding_phone[3]};`
                }
            ]);
        }

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-news-wrap",
                declaration: `
                animation: ${speed} linear 0s infinite ${slide_dir} none running news-move;`
            }
        ]);

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-news-wrap li",
                declaration: `padding: 0 ${item_spacing};`
            }
        ]);

        if (pause_on_hover === "on") {
            additionalCss.push([
                {
                    selector: "%%order_class%%:hover .dtq-news-wrap",
                    declaration: `
                    -webkit-animation-play-state: paused!important;
                    animation-play-state: paused!important;`
                }
            ]);
        }

        if (title_pos === "right") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-tricker",
                    declaration: `flex-direction: row-reverse;`
                }
            ]);

            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-title",
                    declaration: `margin-left: 10px;`
                }
            ]);
        } else {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-news-title",
                    declaration: `margin-right: 10px;`
                }
            ]);
        }

        // title bg
        let title_bg = _getCustomBgCss(
            props,
            "title",
            "%%order_class%% .dtq-news-title",
            "%%order_class%%:hover .dtq-news-title"
        );

        return additionalCss.concat(title_bg);
    }

    _renderTitle = () => {
        if (this.props.use_title === "on") {
            return <div className="dtq-news-title">{this.props.title}</div>;
        }
    };

    render() {
        let order = this.props.moduleInfo.order,
            address = this.props.moduleInfo.address.split(".").join(""),
            uid = order + address;

        setTimeout(function() {
            let parent = document.getElementById(`dtq-news-tricker-${uid}`);
            let parent_width = parent.clientWidth;
            parent.style.setProperty("--width", parent_width + "px");
        }, 500);

        return (
            <div className="dtq-module dtq-news-tricker">
                {this._renderTitle()}
                <div
                    id={`dtq-news-tricker-${uid}`}
                    className="dtq-news-container"
                >
                    <ul
                        className="dtq-news-wrap"
                        dangerouslySetInnerHTML={{ __html: this.props.__news }}
                    />
                </div>
            </div>
        );
    }
}

export default DTQ_News_Ticker;
