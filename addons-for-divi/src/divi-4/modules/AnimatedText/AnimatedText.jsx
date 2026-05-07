import $ from 'jquery';
import React, { Component, createRef } from "react";
import Typed from "typed.js";
import "@AssetsLibs/text-animation/text-animation.min.js";
import "@AssetsLibs/slick/slick.min.js";
import { get_responsive_styles } from "@ModuleHelper";

class DTQ_Animated_Text extends Component {
    static slug = "ba_animated_text";
    animatedTextRef = createRef();
    typed = null;

    static css(props) {
        let additionalCss = [];

        let text_alignment = get_responsive_styles(
            props,
            "text_alignment",
            "%%order_class%%",
            { primary: "text-align", important: false },
            { default: "left" }
        );

        let text_alignment_alt = [];
        if (props.layout === "inline") {
            text_alignment_alt = get_responsive_styles(
                props,
                "text_alignment",
                "%%order_class%% .dtq-animated-text-head",
                { primary: "justify-content", important: false },
                { default: "left" }
            );
        }

        // Prefix
        if (props.prefix_stroke) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
                    declaration: `
				-webkit-text-stroke-width: ${props.prefix_stroke};
				-webkit-text-stroke-color: ${props.prefix_stroke_color};`,
                },
            ]);
        }

        if (props.prefix_text_color) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
                    declaration: `-webkit-text-fill-color: ${props.prefix_text_color};`,
                },
            ]);
        }

        let prefix_padding = get_responsive_styles(
            props,
            "prefix_padding",
            "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
            { primary: "padding", important: false },
            { default: "0|0|0|0" }
        );

        let prefix_margin = get_responsive_styles(
            props,
            "prefix_margin",
            "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
            { primary: "margin", important: false },
            { default: "0|0|0|0" }
        );

        let prefix_bg = get_responsive_styles(
            props,
            "prefix_bg",
            "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
            { primary: "background", important: false },
            { default: "transparent" }
        );

        let prefix_radius = props.prefix_radius.split("|");
        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
                declaration: `border-radius: ${prefix_radius[1]} ${prefix_radius[2]}  ${prefix_radius[3]}  ${prefix_radius[4]};`,
            },
        ]);

        // Suffix
        if (props.suffix_stroke) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-module .dtq-animated-text-suffix span",
                    declaration: `
				-webkit-text-stroke-width: ${props.suffix_stroke};
				-webkit-text-stroke-color: ${props.suffix_stroke_color};`,
                },
            ]);
        }

        if (props.suffix_text_color) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-module .dtq-animated-text-suffix span",
                    declaration: `-webkit-text-fill-color: ${props.suffix_text_color};`,
                },
            ]);
        }

        let suffix_padding = get_responsive_styles(
            props,
            "suffix_padding",
            "%%order_class%% .dtq-module .dtq-animated-text-suffix span",
            { primary: "padding", important: false },
            { default: "0|0|0|0" }
        );

        let suffix_margin = get_responsive_styles(
            props,
            "suffix_margin",
            "%%order_class%% .dtq-module .dtq-animated-text-suffix span",
            { primary: "margin", important: false },
            { default: "0|0|0|0" }
        );

        let suffix_bg = get_responsive_styles(
            props,
            "suffix_bg",
            "%%order_class%% .dtq-module .dtq-animated-text-suffix span",
            { primary: "background", important: false },
            { default: "transparent" }
        );

        let suffix_radius = props.suffix_radius.split("|");
        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-module .dtq-animated-text-prefix span",
                declaration: `border-radius: ${suffix_radius[1]} ${suffix_radius[2]}  ${suffix_radius[3]}  ${suffix_radius[4]};`,
            },
        ]);

        // Animated Text
        if (props.animated_stroke) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-module .dtq-animated-text-main",
                    declaration: `
				-webkit-text-stroke-width: ${props.animated_stroke};
				-webkit-text-stroke-color: ${props.animated_stroke_color};`,
                },
            ]);
        }

        if (props.main_text_color) {
            additionalCss.push([
                {
                    selector:
                        "%%order_class%% .dtq-module .dtq-animated-text-main",
                    declaration: `-webkit-text-fill-color: ${props.main_text_color};`,
                },
            ]);
        }

        let animated_padding = get_responsive_styles(
            props,
            "animated_padding",
            "%%order_class%% .dtq-module .dtq-animated-text-main",
            { primary: "padding", important: false },
            { default: "0|0|0|0" }
        );

        let animated_margin = get_responsive_styles(
            props,
            "animated_margin",
            "%%order_class%% .dtq-module .dtq-animated-text-main",
            { primary: "margin", important: false },
            { default: "0|0|0|0" }
        );

        let animated_bg = get_responsive_styles(
            props,
            "animated_bg",
            "%%order_class%% .dtq-module .dtq-animated-text-main",
            { primary: "background", important: false },
            { default: "transparent" }
        );

        let animated_radius = props.animated_radius.split("|");
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-module .dtq-animated-text-main",
                declaration: `border-radius: ${animated_radius[1]} ${animated_radius[2]}  ${animated_radius[3]}  ${animated_radius[4]};`,
            },
        ]);

        // Cursor
        if (props.show_cursor === "on") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-text-animation:after",
                    declaration: `
			  		display: block;
				  right: -${props.cursor_gap};
				  width: ${props.cursor_width};
				  background: ${props.cursor_color};
				  height: ${props.cursor_height};
				  `,
                },
            ]);
        }

        // Others
        additionalCss.push([
            {
                selector: "%%order_class%% .dtq-animated-text-slide li.text-in",
                declaration: `animation: ${props.slide_animation} 700ms;`,
            },
        ]);

        if (props.layout === "inline") {
            additionalCss.push([
                {
                    selector: "%%order_class%% .dtq-animated-text-head",
                    declaration: `display: flex; align-items: center;`,
                },
            ]);
        }

        return additionalCss
            .concat(text_alignment)
            .concat(prefix_padding)
            .concat(prefix_bg)
            .concat(prefix_margin)
            .concat(animated_padding)
            .concat(animated_bg)
            .concat(text_alignment_alt)
            .concat(animated_margin)
            .concat(suffix_padding)
            .concat(suffix_bg)
            .concat(suffix_margin);
    }

    componentDidMount() {
        this.initializeAnimations();
        this.setupJQueryAnimations();
    }

    componentDidUpdate(prevProps) {
        if (this.props.animation_type === "typed") {
            this.initializeAnimations();
        } else {
            this.setupJQueryAnimations();
        }
    }

    componentWillUnmount() {
        this.destroyTyped();
        this.destroyJQueryAnimations();
    }

    initializeAnimations = () => {
        if (this.props.animation_type === "typed") {
            this.initializeTyped();
        }
    };

    initializeTyped = () => {
        const { animated_text, animation_speed, start_delay, back_speed, back_delay, use_loop } = this.props;

        const options = {
            strings: this.parseAnimatedText(animated_text),
            typeSpeed: parseInt(animation_speed, 10),
            loop: use_loop === "on",
            showCursor: true,
            startDelay: parseInt(start_delay, 10),
            backSpeed: parseInt(back_speed, 10),
            backDelay: parseInt(back_delay, 10),
        };

        this.destroyTyped();

        if (this.animatedTextRef.current) {
            this.typed = new Typed(this.animatedTextRef.current, options);
        }
    };

    destroyTyped = () => {
        if (this.typed) {
            this.typed.destroy();
            this.typed = null;
        }
    };

    setupJQueryAnimations = () => {
        const { animation_type, slide_gap, tilt_delay, tilt_in, tilt_out, tilt_shuffle, tilt_reverse, tilt_sync, moduleInfo } = this.props;
        const uid = `${moduleInfo.order}${moduleInfo.address.replace(/\./g, "")}`;

        if ("tilt" === animation_type) {
            setTimeout(function() {
                $(`#dtq-animated-text-${uid} .dtq-animated-text-tilt`).textillate({
                    in: {
                        effect: tilt_in,
                        delayScale: 1.5,
                        delay: parseInt(tilt_delay),
                        sync: tilt_sync[0] === "on",
                        reverse: tilt_reverse[0] === "on",
                        shuffle: tilt_shuffle[0] === "on"
                    },
                    out: {
                        effect: tilt_out,
                        delayScale: 1.5,
                        delay: parseInt(tilt_delay),
                        sync: tilt_sync[1] === "on",
                        reverse: tilt_reverse[1] === "on",
                        shuffle: tilt_shuffle[1] === "on"
                    },
                    loop: true
                });

            }, 500);
        }

        if (animation_type === "slide") {
            setTimeout(function() {
                $(`#dtq-animated-text-${uid} .dtq-animated-text-slide`).slick({
                    autoplay: true,
                    autoplaySpeed: slide_gap,
                    infinite: true
                });
            }, 500);
        }
    };

    destroyJQueryAnimations = () => {
        const { moduleInfo } = this.props;
        const uid = `${moduleInfo.order}${moduleInfo.address.replace(/\./g, "")}`;

        if ($(`#dtq-animated-text-${uid} .dtq-animated-text-tilt`).data('textillate')) {
            $(`#dtq-animated-text-${uid} .dtq-animated-text-tilt`).textillate('stop');
        }

        if ($(`#dtq-animated-text-${uid} .dtq-animated-text-slide`).hasClass('slick-initialized')) {
            $(`#dtq-animated-text-${uid} .dtq-animated-text-slide`).slick('unslick');
        }
    };

    parseAnimatedText = (animatedText) => {
        try {
            return JSON.parse(animatedText).map(el => el.value);
        } catch (error) {
            return animatedText.split("|");
        }
    };

    renderAnimationContent = () => {
        const { animation_type, animated_text } = this.props;

        switch (animation_type) {
            case "typed":
                return <div className="dtq-animated-text-main" ref={this.animatedTextRef} />;

            case "tilt":
                return (
                    <div className='dtq-animated-text-tilt'>
                        <ul className='texts dtq-animated-text-main'>
                            {this.parseAnimatedText(animated_text).map((text, index) => (
                                <li key={index}>{text}</li>
                            ))}
                        </ul>
                    </div>
                );

            case "slide":
                return (
                    <ul className="dtq-animated-text-slide">
                        {this.parseAnimatedText(animated_text).map((text, index) => (
                            <li key={index} className="text-in">{text}</li>
                        ))}
                    </ul>
                );

            default:
                return null;
        }
    };

    render() {
        const { prefix, suffix, layout, moduleInfo, animated_level, animation_type } = this.props;
        const uid = `${moduleInfo.order}${moduleInfo.address.replace(/\./g, "")}`;
        const Title = animated_level || "h3";

        return (
            <div className='dtq-module dtq-animated-text' id={`dtq-animated-text-${uid}`}>
                <Title className='dtq-animated-text-head'>
                    {prefix && (
                        <div className={`dtq-animated-text-prefix`}>
                            <span>{prefix}</span>
                            {layout === "inline" ? "\u00A0" : ""}
                        </div>
                    )}
                    
                    {this.renderAnimationContent()}

                    {suffix && (
                        <div className={`dtq-animated-text-suffix`}>
                            {layout === "inline" ? "\u00A0" : ""}
                            <span>{suffix}</span>
                        </div>
                    )}
                </Title>
            </div>
        );
    }
}

export default DTQ_Animated_Text;

