// External Dependencies
import React, { Component } from "react";
// Internal Dependencies
import "./style.css";

class DTQ_Logo_Grid extends Component {

    static slug = "ba_logo_grid";

    static css( props ) {

        let additionalCss                  = [],

            grid_height                    = props.grid_height,
            grid_height_tablet             = props.grid_height_tablet,
            grid_height_phone              = props.grid_height_phone,
            grid_height_last_edited        = props.grid_height_last_edited,
            grid_height_responsive_status  = grid_height_last_edited && grid_height_last_edited.startsWith("on"),

            logo_size                      = props.logo_size,
            logo_size_tablet               = props.logo_size_tablet,
            logo_size_phone                = props.logo_size_phone,
            logo_size_last_edited          = props.logo_size_last_edited,
            logo_size_responsive_status    = logo_size_last_edited && logo_size_last_edited.startsWith("on"),

            grid_gap                       = props.grid_gap ? props.grid_gap : '5px',
            grid_gap_tablet                = props.grid_gap_tablet ? props.grid_gap_tablet : grid_gap,
            grid_gap_phone                 = props.grid_gap_phone ? props.grid_gap_phone : grid_gap_tablet,

            column_count                   = props.column_count ? props.column_count : '4',
            column_count_tablet            = props.column_count_tablet ? props.column_count_tablet : column_count,
            column_count_phone             = props.column_count_phone ? props.column_count_phone : column_count_tablet;


	additionalCss.push([{
		selector: "%%order_class%% .dtq-logo-grid__item",
		declaration: `overflow: ${props.logo_overflow};`
	}] );


    // grid height
    if( grid_height ) {
        additionalCss.push([{
            selector: "%%order_class%% .ba_logo_grid_child",
            declaration: `height: ${grid_height};`
        }] );
    }

    if( grid_height_tablet && grid_height_responsive_status ) {
        additionalCss.push( [{
            selector: "%%order_class%% .ba_logo_grid_child",
            device: "tablet",
            declaration: `height: ${grid_height_tablet};`
        }] );
    }

    if( grid_height_phone && grid_height_responsive_status ) {
        additionalCss.push( [{
            selector: "%%order_class%% .ba_logo_grid_child",
            device: "phone",
            declaration: `height: ${grid_height_phone};`
        }] );
    }

    //  LOgo Size
    if( logo_size ) {
        additionalCss.push([{
            selector: "%%order_class%% .dtq-logo-grid__item img",
            declaration: `width: ${logo_size};`
        }] );

        if( logo_size_tablet && logo_size_responsive_status ) {
            additionalCss.push( [{
                selector: "%%order_class%% .dtq-logo-grid__item img",
                device: "tablet",
                declaration: `width: ${logo_size_tablet};`
            }] );
        }

        if( logo_size_phone && logo_size_responsive_status ) {
            additionalCss.push( [{
                selector: "%%order_class%% .dtq-logo-grid__item img",
                device: "phone",
                declaration: `width: ${logo_size_phone};`
            }] );
        }

    } else {
        additionalCss.push([{
            selector: "%%order_class%% .dtq-logo-grid__item img",
            declaration: `width: 100%;`
        }] );
    }


    // grid gap & grid column
    additionalCss.push( [{
        selector: "%%order_class%% .dtq-logo-grid",
        declaration: `margin: -${grid_gap};`
    }] );

    additionalCss.push( [{
        selector: "%%order_class%% .dtq-logo-grid",
        device: "tablet",
        declaration: `margin: -${grid_gap_tablet};`
    }] );

    additionalCss.push( [{
        selector: "%%order_class%% .dtq-logo-grid",
        device: "phone",
        declaration: `margin: -${grid_gap_phone};`
    }] );

    additionalCss.push( [{
        selector: "%%order_class%% .ba_logo_grid_child",
        declaration: `
            flex: 0 0 calc(100%/${column_count});
            padding: ${grid_gap};`
    }] );

    additionalCss.push( [{
        selector: "%%order_class%% .ba_logo_grid_child",
        device: "tablet",
        declaration: `
            flex: 0 0 calc(100%/${column_count_tablet});
            padding: ${grid_gap_tablet};`
    }] );

    additionalCss.push( [{
        selector: "%%order_class%% .ba_logo_grid_child",
        device: "phone",
        declaration: `
            flex: 0 0 calc(100%/${column_count_phone});
            padding: ${grid_gap_phone};`
      }] );

    return additionalCss;
  }

  render() {
    let { content, image_hover } = this.props;
    return (
      <div className={`dtq-module dtq-parent dtq-logo-grid ${image_hover}`}>{content}</div>
    );
  }
}

export default DTQ_Logo_Grid;
