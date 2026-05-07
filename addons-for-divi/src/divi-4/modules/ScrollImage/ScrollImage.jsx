import React, { Component, createRef } from "react";
import { renderFontStyle, _getCustomBgCss } from "@ModuleHelper";
class DTQ_Scroll_Image extends Component {
    static slug = "ba_scroll_image";
    scrollImageRef = createRef();

    static css( props ) {

        let additionalCss                = [],
            show_icon                    = props.show_icon,
            use_image                    = props.use_image,
            use_icon_anim                = props.use_icon_anim,
            icon_size                    = props.icon_size,
            icon_color                   = props.icon_color,
            scroll_speed                 = props.scroll_speed,
            scroll_type                  = props.scroll_type,
            scroll_dir_scroll            = props.scroll_dir_scroll,
            scroll_dir_hover             = props.scroll_dir_hover,
            img_height                   = props.img_height,
            img_height_tablet            = props.img_height_tablet,
            img_height_phone             = props.img_height_phone,
            img_height_last_edited       = props.img_height_last_edited,
            img_height_responsive_status = img_height_last_edited && img_height_last_edited.startsWith("on");

        if( use_icon_anim === 'on' ) {
            let anim_dir = '';
            if( scroll_type === 'on_scroll' ) {
                if( scroll_dir_scroll === 'vertical' ) {
                    anim_dir = "Y";
                } else {
                    anim_dir = "X";
                }
            } else {
                anim_dir = scroll_dir_hover[0];
            }

            additionalCss.push([{
                selector: "%%order_class%% .dtq-scroll-image-icon-el",
                declaration: `
                    animation-name: dtq-scroll-${anim_dir};
                    animation-duration: .5s;
                    animation-iteration-count: infinite;
                    animation-direction: alternate;
                    animation-timing-function: ease-in-out;
                    `
            }] );
        }

        // Icon
        if( show_icon === 'on' ) {
            if( use_image === 'off' ) {
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image-icon-el",
                    declaration: `font-size: ${icon_size};color:${icon_color};`
                }] );
            } else {
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image-icon img",
                    declaration: `width: ${icon_size};`
                }] );
            }
        }

        //Scroll
        if( scroll_type === 'on_scroll' ) {

            if( scroll_dir_scroll === 'vertical' ) {
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image",
                    declaration: `overflow-y: auto;overflow-x:hidden;`
                }] );
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                    declaration: `max-width: 100%;width: 100%;`
                }] );
            } else {
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image",
                    declaration: `overflow-y:hidden;overflow-x: auto;`
                }] );
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                    declaration: `height: 100%; max-width: none;width: auto;`
                }] );
                additionalCss.push([{
                    selector: "%%order_class%% .scroll-figure-wrap",
                    declaration: `height: 100%;width: 100%;`
                }] );
            }
        }
        else if( scroll_type === 'on_hover' ) {

            additionalCss.push([{
                selector: "%%order_class%% .scroll-figure-wrap",
                declaration: `height:100%;width:100%;`
            }] );

            additionalCss.push([{
                selector: "%%order_class%% .dtq-scroll-image",
                declaration: `overflow: hidden;`
            }] );

            additionalCss.push([{
                selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                declaration: `
                    position: absolute;
                    transition: ${scroll_speed};`
            }] );


            if( scroll_dir_hover === 'X_ltr' || scroll_dir_hover === 'X_rtl' ) {
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                    declaration: `height: 100%; max-width: none;width: auto;top:0;`
                }] );


                if( scroll_dir_hover === 'X_ltr' ) {
                    additionalCss.push([{
                        selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                        declaration: `right:0;`
                    }] );
                } else if( scroll_dir_hover === 'X_rtl' ) {
                    additionalCss.push([{
                        selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                        declaration: `left:0;`
                    }] );
                }

            } else {
                additionalCss.push([{
                    selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                    declaration: `max-width: 100%;width: 100%; left:0;`
                }] );

                if( scroll_dir_hover === 'Y_ttb' ) {
                    additionalCss.push([{
                        selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                        declaration: `bottom:0;`
                    }] );
                } else if( scroll_dir_hover === 'Y_btt' ) {
                    additionalCss.push([{
                        selector: "%%order_class%% .dtq-scroll-image .dtq-scroll-image-el",
                        declaration: `top:0;`
                    }] );
                }
            }
        }

        // image height
        additionalCss.push([{
            selector: "%%order_class%% .dtq-scroll-image",
            declaration: `height: ${img_height};`
        }] );

        if( img_height_tablet && img_height_responsive_status ) {
            additionalCss.push( [{
                selector: "%%order_class%% .dtq-scroll-image",
                device: "tablet",
                declaration: `height: ${img_height_tablet};`
            }] );
        }

        if( img_height_phone && img_height_responsive_status ) {
            additionalCss.push( [{
                selector: "%%order_class%% .dtq-scroll-image",
                device: "phone",
                declaration: `wiheightdth: ${img_height_phone};`
            }] );
        }

        // overlay bg
        let overlay_bg = _getCustomBgCss( props, 'overlay', "%%order_class%% .dtq-scroll-image-overlay", '' );

        let iconStyle = renderFontStyle(
            props,
            "icon",
            "%%order_class%% .dtq-scroll-image-icon"
        );



        return additionalCss.concat( overlay_bg ).concat( iconStyle );
    }

    componentDidMount() {
        this.attachEventHandlers();
    }

    componentDidUpdate(prevProps) {
        // Check if properties affecting handlers changed, and re-attach if necessary
        if (prevProps.scroll_dir_hover !== this.props.scroll_dir_hover ||
            prevProps.scroll_type !== this.props.scroll_type) {
            this.attachEventHandlers();
        }
    }

    componentWillUnmount() {
        this.detachEventHandlers();
    }

    attachEventHandlers = () => {
        const node = this.scrollImageRef.current;
        if (!node || this.props.scroll_type !== 'on_hover') return;

        const scrollImage = node.querySelector('.dtq-scroll-image-el');
        if (!scrollImage) return;

        const handleMouseEnter = () => {
            const translateDirection = this.props.scroll_dir_hover[0] === 'X' ? 'translateX' : 'translateY';
            const offset = translateDirection === 'translateX' ? 
                scrollImage.offsetWidth - node.offsetWidth :
                scrollImage.offsetHeight - node.offsetHeight;
            scrollImage.style.transform = `${translateDirection}(${offset}px)`;
        };

        const handleMouseLeave = () => {
            scrollImage.style.transform = '';
        };

        node.addEventListener('mouseenter', handleMouseEnter);
        node.addEventListener('mouseleave', handleMouseLeave);
        this.detachEventHandlers = () => {
            node.removeEventListener('mouseenter', handleMouseEnter);
            node.removeEventListener('mouseleave', handleMouseLeave);
        };
    };

    renderIcon = () => {
        const { show_icon, use_image, icon_image, icon } = this.props;

        const utils = window.ET_Builder.API.Utils;
        const processIcon = this.props.icon ? utils.processFontIcon(icon) : "";

        if (show_icon !== 'on') return null;
        if (use_image === 'on') {
            return (
                <div className="dtq-scroll-image-icon">
                    <img src={icon_image} alt="" />
                </div>
            );
        }
        return (
            <div className="dtq-scroll-image-icon dtq-et-font-icon">
                {processIcon}
            </div>
        );
    };

    render() {
        const { image = '', image_alt = '' } = this.props;

        return (
            <div className="dtq-module dtq-scroll-image" ref={this.scrollImageRef}>
                {this.renderIcon()}
                <div className="scroll-figure-wrap">
                    {image ? (
                        <img
                            className="dtq-scroll-image-el"
                            src={image}
                            alt={image_alt}
                        />
                    ) : "Loading..."}
                </div>
            </div>
        );
    }
}

export default DTQ_Scroll_Image;
