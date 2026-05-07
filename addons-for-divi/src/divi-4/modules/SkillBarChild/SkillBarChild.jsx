
import React, { Component } from "react";
import {
    get_responsive_styles,
    _getCustomBgCss
} from "@ModuleHelper";
import "./style.css";

class DTQ_Skill_Bar_Child extends Component {
    static slug = "ba_skill_bar_child";

    static css(props) {
        let additionalCss = [],
            text_spacing = props.text_spacing ? props.text_spacing : "12px",
            level = props.level ? props.level : "30%",
            bar_height = props.bar_height ? props.bar_height : "30px",
            bar_height_tablet = props.bar_height_tablet,
            bar_height_phone = props.bar_height_phone,
            bar_height_last_edited = props.bar_height_last_edited,
            bar_height_responsive_status =
                bar_height_last_edited &&
                bar_height_last_edited.startsWith("on"),
            text_placement = props.text_placement ? props.text_placement : "in",
            bar_radius = props.bar_radius ? props.bar_radius : "40px",
            use_name = props.use_name ? props.use_name : "on",
            name_spacing = [],
            level_spacing = [];

        if (use_name === "off") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-skillbar__inner__text",
                    declaration: `justify-content: flex-end;`
                }
            ]);
        } else {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-skillbar__inner__text",
                    declaration: `justify-content: space-between;`
                }
            ]);
        }

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-skillbar__inner",
                declaration: `
          width: ${level};
          height: ${bar_height};`
            }
        ]);

        if (bar_height_tablet && bar_height_responsive_status) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-skillbar__inner",
                    device: "tablet",
                    declaration: ` height: ${bar_height_tablet};`
                }
            ]);
        }

        if (bar_height_phone && bar_height_responsive_status) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-skillbar__inner",
                    device: "phone",
                    declaration: ` height: ${bar_height_phone};`
                }
            ]);
        }

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-skillbar__wrapper",
                declaration: `border-radius: ${bar_radius};`
            }
        ]);

        if (props.name_spacing) {
            name_spacing = get_responsive_styles(
                props,
                "name_spacing",
                ".dtq-skill %%order_class%% .dtq-skillbar__name",
                { primary: "margin-left", important: false },
                { default: "0" }
            );
        }

        if (props.level_spacing) {
            level_spacing = get_responsive_styles(
                props,
                "level_spacing",
                ".dtq-skill %%order_class%% .dtq-skillbar__level",
                { primary: "margin-right", important: false },
                { default: "0" }
            );
        }

        if (text_placement === "out") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-skillbar__inner__text",
                    declaration: `
                position: absolute;
                top: -${text_spacing};
                width: ${level};
                height: auto!important;
                transform: translateY(-100%);`
                }
            ]);
        } else if (text_placement === "in") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-skillbar__inner__text",
                    declaration: `
                width: 100%;
                height: 100%;`
                }
            ]);
        }

        let level_bg = _getCustomBgCss(
            props,
            "level",
            "%%order_class%% .dtq-skillbar__inner",
            "%%order_class%%:hover .dtq-skillbar__inner",
            "#5b2cff"
        );

        let bar_bg = _getCustomBgCss(
            props,
            "bar",
            "%%order_class%% .dtq-skillbar__wrapper",
            "%%order_class%%:hover .dtq-skillbar__wrapper",
            "#dddddd"
        );

        return additionalCss
            .concat(level_bg)
            .concat(bar_bg)
            .concat(level_spacing)
            .concat(name_spacing);
    }

    _renderName = () => {
        let name = this.props.name ? this.props.name : "Web Design",
            use_name = this.props.use_name ? this.props.use_name : "on";

        if (use_name === "on") {
            return <span className="dtq-skillbar__name">{name}</span>;
        }
    };

    _renderLevel = () => {
        let level = this.props.level ? this.props.level : "30%",
            is_hide_level = this.props.is_hide_level
                ? this.props.is_hide_level
                : "off";

        if (is_hide_level === "off") {
            return <span className="dtq-skillbar__level">{level}</span>;
        }
    };

    _renderInnerText = pos => {
        let use_name = this.props.use_name ? this.props.use_name : "on",
            is_hide_level = this.props.is_hide_level
                ? this.props.is_hide_level
                : "off";

        if (use_name === "on" || is_hide_level === "off") {
            return (
                <div className="dtq-skillbar__inner__text">
                    {this._renderName()}
                    {this._renderLevel()}
                </div>
            );
        }
    };

    render() {
        return (
            <div className="dtq-module dtq-child dtq-skillbar">
                <div className="dtq-skillbar__wrapper">
                    <div className="dtq-skillbar__inner">
                        {this._renderInnerText()}
                    </div>
                </div>
            </div>
        );
    }
}

export default DTQ_Skill_Bar_Child;
