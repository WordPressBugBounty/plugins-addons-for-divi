// External Dependencies
import React, { Component } from "react";

class DTQ_Gradient_Heading extends Component {
    static slug = "ba_gradient_heading";

    static css(props) {
        let primary_color = props.primary_color,
            angle = props.angle,
            primary_color_location = props.primary_color_location,
            secondary_color = props.secondary_color,
            secondary_color_location = props.secondary_color_location,
            gradient_type = props.gradient_type,
            radial_position = props.radial_position,
            title_font_weight = props.title_font.split("|")[1],
            additionalCss = [];

        if ("700" === title_font_weight) {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-gradient-heading",
                    declaration: `font-weight: bold;`
                }
            ]);
        }

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-gradient-heading",
                declaration: `
		font-size: ${props.title_font_size};
		line-height: ${props.title_line_height};
		color: ${primary_color};`
            }
        ]);

        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-gradient-heading",
                declaration: `
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-color: transparent;
        `
            }
        ]);

        if (gradient_type === "linear") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-gradient-heading",
                    declaration: `
            background-image: linear-gradient(${angle}deg, ${primary_color} ${primary_color_location}%, ${secondary_color} ${secondary_color_location}%);
        `
                }
            ]);
        } else {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-gradient-heading",
                    declaration: `
            background-image: radial-gradient(at ${radial_position}, ${primary_color} ${primary_color_location}%, ${secondary_color} ${secondary_color_location}%);`
                }
            ]);
        }

        return additionalCss;
    }

    _renderTitle = ({ title, html_tag, use_link, link_url, link_options }) => {
        let TitleTag = html_tag;

        if (use_link === "on") {
            let linkOptions = link_options.split("|");
            let linkTarget = linkOptions[0] === "off" ? "_self" : "_blank";

            let attr = {};
            if (linkOptions[1] === "on") {
                attr["rel"] = "nofollow";
            }

            return (
                <TitleTag className="dtq-gradient-heading">
                    <a
                        target={linkTarget}
                        href={link_url}
                        {...attr}
                        dangerouslySetInnerHTML={{ __html: title }}
                    />
                </TitleTag>
            );
        } else {
            return (
                <TitleTag
                    className="dtq-gradient-heading"
                    dangerouslySetInnerHTML={{ __html: title }}
                />
            );
        }
    };

    render() {
        return this._renderTitle(this.props);
    }
}

export default DTQ_Gradient_Heading;
