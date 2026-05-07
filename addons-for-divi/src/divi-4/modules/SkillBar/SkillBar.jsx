import React, { Component } from "react";
import { get_responsive_styles } from "@ModuleHelper";
import "./style.css";

class DTQ_Skill_Bar extends Component {
    static slug = "ba_skill_bar";

    static css(props) {
        let additionalCss = [];

        let name_spacing = get_responsive_styles(
            props,
            "name_spacing",
            "%%order_class%% .dtq-skillbar__name",
            { primary: "margin-left", important: false },
            { default: "15px" }
        );

        let level_spacing = get_responsive_styles(
            props,
            "level_spacing",
            "%%order_class%% .dtq-skillbar__level",
            { primary: "margin-right", important: false },
            { default: "15px" }
        );

        let title_spacing_bottom = get_responsive_styles(
            props,
            "title_spacing_bottom",
            "%%order_class%% .dtq-skill__title",
            { primary: "margin-bottom", important: false },
            { default: "10px" }
        );

        let bar_spacing_bottom = get_responsive_styles(
            props,
            "bar_spacing_bottom",
            "%%order_class%% .ba_skill_bar_child",
            { primary: "margin-bottom", important: true },
            { default: "20px" }
        );

        return additionalCss
            .concat(level_spacing)
            .concat(name_spacing)
            .concat(bar_spacing_bottom)
            .concat(title_spacing_bottom);
    }

    _renderTitle = () => {
        if (this.props.title) {
            let Title = this.props.title_level ? this.props.title_level : "h3";
            return (
                <Title className="dtq-skill__title">{this.props.title}</Title>
            );
        }
    };

    render() {
        return (
            <div className="dtq-module dtq-parent dtq-skill">
                {this._renderTitle()}
                {this.props.content}
            </div>
        );
    }
}

export default DTQ_Skill_Bar;
