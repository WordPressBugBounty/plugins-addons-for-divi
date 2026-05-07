// External Dependencies
import React, { Component } from "react";
import { Tooltip } from 'react-tippy';
// Internal Dependencies
import "./style.css";


class DTQ_Logo_Grid_Child extends Component {

  static slug = "ba_logo_grid_child";

  render_logo = () => {
    let logo_url = this.props.logo_url;

    if ( logo_url ) {
        return <img src={logo_url} alt={this.props.brand_name} />;
    }

  };



  render() {

    let tooltip_text = this.props.tooltip_text ? this.props.tooltip_text : 'Tooltip!',
        use_tooltip  = this.props.use_tooltip ? this.props.use_tooltip : 'off',
        theme        = this.props.theme ? this.props.theme : 'dark',
        position     = this.props.position ? this.props.position : 'top',
        animation    = this.props.animation ? this.props.animation : 'scale',
        html         = '';


    if( use_tooltip === 'on' ) {

        html = ( <Tooltip
            title = {tooltip_text}
            position = {position}
            trigger = "mouseenter"
            arrow = "true"
            animation = {animation}
            theme = {theme}
            className = "dtq-logo-grid__item"
            style = {{display: "block"}}
        >
            <div className="dtq-logo-grid__item__inner">
                {this.render_logo()}
            </div>
        </Tooltip> );

    } else {

        html = ( <div className="dtq-module dtq-child dtq-logo-grid__item">
            <div className="dtq-logo-grid__item__inner">
                {this.render_logo()}
            </div>
        </div> );
    }

    return html;

  }
}

export default DTQ_Logo_Grid_Child;
