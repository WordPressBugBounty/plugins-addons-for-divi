import $ from 'jquery';
import React, { Component } from 'react';
import '@AssetsLibs/twentytwenty/twentytwenty.min.js';
import { get_responsive_styles } from '@ModuleHelper';

class DTQ_Image_Compare extends Component {
    
    static slug = "ba_image_compare";

    static css(props) {
        const additionalCss = [];
        let label_height = [];
        let label_width = [];
        let arrow_color = '';
        if ("handle-1" === props.handle_style) {
            arrow_color = props.handle_color;
        } else if ("handle-2" === props.handle_style) {
            arrow_color = props.arrow_color;
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-image-compare .twentytwenty-handle",
                    declaration: `background-color: ${props.handle_color};`
                }
            ]);

        }

        additionalCss.push([
            {
                selector: `%%order_class%% .dtq-image-compare .twentytwenty-horizontal .twentytwenty-handle:before,
            %%order_class%% .dtq-image-compare .twentytwenty-horizontal .twentytwenty-handle:after,
            %%order_class%% .dtq-image-compare .twentytwenty-vertical .twentytwenty-handle:before,
            %%order_class%% .dtq-image-compare .twentytwenty-vertical .twentytwenty-handle:after`,
                declaration: `background: ${props.handle_color};`
            }
        ]);

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-compare .twentytwenty-handle",
                declaration: `border: 3px solid ${props.handle_color};`
            }
        ]);


        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-compare .twentytwenty-right-arrow",
                declaration: `border-left: 6px solid ${arrow_color};`
            }
        ]);

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-compare .twentytwenty-left-arrow",
                declaration: `border-right: 6px solid ${arrow_color};`
            }
        ]);

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-compare .twentytwenty-down-arrow",
                declaration: `border-top: 6px solid ${arrow_color};`
            }
        ]);

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-compare .twentytwenty-up-arrow",
                declaration: `border-bottom: 6px solid ${arrow_color};`
            }
        ]);

        // handle_color end

        // Label
        if ("initial" !== props.label_height) {
            label_height = get_responsive_styles(
                props,
                "label_height",
                "%%order_class%% .twentytwenty-overlay div:before",
                { primary: "height", important: false },
                { default: "initial" }
            );
        }

        if ("initial" !== props.label_width) {
            label_width = get_responsive_styles(
                props,
                "label_width",
                "%%order_class%% .twentytwenty-overlay div:before",
                { primary: "width", important: false },
                { default: "initial" }
            );
        }

        let label_padding = get_responsive_styles(
            props,
            "label_padding",
            "%%order_class%% .twentytwenty-overlay div:before",
            { primary: "padding", important: false },
            { default: "5px|20px|5px|20px" }
        );

        if (props.show_label === "on_hover") {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .twentytwenty-before-label, %%order_class%% .twentytwenty-after-label",
                    declaration: `opacity:0;`
                }
            ]);
            additionalCss.push([
                {
                    selector:
                        "%%order_class%%:hover .twentytwenty-before-label, %%order_class%%:hover .twentytwenty-after-label",
                    declaration: `opacity:1;`
                }
            ]);
        }

        additionalCss.push([
            {
                selector: "%%order_class%% .twentytwenty-before-label:before",
                declaration: `background-color: ${props.before_label_bg}`
            }
        ]);

        additionalCss.push([
            {
                selector: "%%order_class%% .twentytwenty-after-label:before",
                declaration: `background-color: ${props.after_label_bg}`
            }
        ]);

        return additionalCss
            .concat(label_padding)
            .concat(label_width)
            .concat(label_height);
    }

    componentDidMount() {
        this.initImageCompare(1000);
    }

    componentDidUpdate(prevProps) {
        if (this.propsChanged(prevProps)) {
            this.initImageCompare(300);
        }
    }

    propsChanged(prevProps) {
        const propsToCheck = ['before_img', 'before_label', 'after_img', 'after_label', 'orientation', 'offset_pct', 'move_on_hover', 'overlay', 'show_label'];
        return propsToCheck.some(prop => this.props[prop] !== prevProps[prop]);
    }

    initImageCompare(delay) {
        const { move_on_hover, moduleInfo, offset_pct, orientation, before_label, after_label, overlay } = this.props;
        const selectorSuffix = this.constructSelector(moduleInfo);

        const parent = $(`.dtq-image-compare-container-${selectorSuffix}`);
        this.resetTwentyTwenty(parent);

        setTimeout(() => {
            parent.twentytwenty({
                default_offset_pct: offset_pct,
                move_slider_on_hover: move_on_hover === 'on',
                orientation: orientation,
                before_label: before_label,
                after_label: after_label,
                no_overlay: overlay !== 'on'
            });
        }, delay);
    }

    resetTwentyTwenty(element) {
        if (element.hasClass('twentytwenty-container')) {
            element.unwrap();
            element.find('.twentytwenty-overlay, .twentytwenty-handle').remove();
            element.removeClass('twentytwenty-container');
        }
    }

    constructSelector({ order, address }) {
        return `${order}${address.split('.').join('')}`;
    }

    render() {
        const { handle_style, __compare } = this.props;
        const selectorSuffix = this.constructSelector(this.props.moduleInfo);

        return (
            <div className={`dtq-image-compare ${handle_style}`}>
                <div className={`dtq-image-compare-container-${selectorSuffix}`}>
                    <div dangerouslySetInnerHTML={{ __html: __compare }} />
                </div>
            </div>
        );
    }
}

export default DTQ_Image_Compare;