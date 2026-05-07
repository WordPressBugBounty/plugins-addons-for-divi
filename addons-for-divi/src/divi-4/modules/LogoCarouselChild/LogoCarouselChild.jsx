// External Dependencies
import React, { Component } from "react";
import { renderFontStyle, render_swapped_image, _getOverlayStyleCss } from '@ModuleHelper';
// Internal Dependencies
import "./style.css";


class DTQ_LogoCarouselChild extends Component {

    static slug = "ba_logo_carousel_child";

    constructor(props) {
        super(props);
        this.state = { width: 0};
        this.updateWindowDimensions = this.updateWindowDimensions.bind(this);
    }

    componentDidMount() {
        this.updateWindowDimensions();
        window.addEventListener('resize', this.updateWindowDimensions);
    }

    componentWillUnmount() {
        window.removeEventListener('resize', this.updateWindowDimensions);
    }

    updateWindowDimensions() {
        this.setState({ width: window.innerWidth});
    }

    static css( props ) {

        // Overlay Styles
        let overlay_styles = _getOverlayStyleCss( props, 'logo', '%%order_class%% .dtq-carousel-item' );
        let overlayIconStyle = renderFontStyle(
            props,
            "overlay_icon",
            "%%order_class%% .dtq-overlay .dtq-overlay-icon"
        );
        return overlay_styles.concat( overlayIconStyle );
    }

    render_logo = ( url ) => {

        let props        = this.props,
            is_link      = props.is_link ? props.is_link : 'off',
            link_options = props.link_options ? props.link_options : "off|off",
            link_url     = props.link_url;

        if ( is_link === "on" ) {

            let linkOptions = link_options.split("|"),
                linkTarget  = linkOptions[0] === "off" ? "_self" : "_blank",
                attr        = {};

            if (linkOptions[1] === "on") {
                attr["rel"] = "nofollow";
            }

            return (
                <a href={link_url} target={linkTarget} {...attr}>
                    <img src={url} alt='' />
                </a>
            );
        }
        return <img src={url} alt='' />;
    };



    render() {

        const utils      = window.ET_Builder.API.Utils;
        let overlay_icon = this.props.overlay_icon ? utils.processFontIcon( this.props.overlay_icon ) : '';

        return (
            <div className="dtq-carousel-item dtq-logo-carousel-item">
                <div className="dtq-overlay"><i className="dtq-overlay-icon">{overlay_icon}</i></div>
                { render_swapped_image( 'logo', this.props, this.render_logo, this.state ) }
            </div>
        );
    }
}

export default DTQ_LogoCarouselChild;
