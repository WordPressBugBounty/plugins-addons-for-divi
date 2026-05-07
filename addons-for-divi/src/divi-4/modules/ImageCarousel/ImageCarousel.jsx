// External Dependencies
import React, { Component } from "react";
import Slider from 'react-slick';
import { _getCarouselCss, _getCarouselSettings } from '@ModuleHelper';
import "./style.css";


class DTQ_Image_Carousel extends Component {

    static slug = "ba_image_carousel";

    static css( props ) {

        let additionalCss = [];

        const content_alignment = props.content_alignment ? props.content_alignment : "left";

        additionalCss.push([
            {
                selector:
                    "%%order_class%% .dtq-image-carousel-item .content-inner",
                declaration: `text-align: ${content_alignment};`
            }
        ]);

        return additionalCss.concat(_getCarouselCss(props));
    }

    render() {

        let props            = this.props,
            settings         = _getCarouselSettings( props, 'jsx' ),
            is_center        = props.is_center,
            center_mode_type = props.center_mode_type,
            custom_cursor    = props.custom_cursor,
            classes          = [];

        if( is_center === 'on' ) {
            classes.push( `dtq-centered dtq-centered--${center_mode_type}` );
        }

        if( custom_cursor === 'on' ) {
            classes.push( 'dtq-cursor' );
        }

        return (
            <div className={`dtq-carousel dtq-image-carousel ${classes.join(' ')}`}>
                <Slider {...settings}>{props.content}</Slider>
            </div>
        );
    }
}

export default DTQ_Image_Carousel;
