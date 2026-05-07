//External Dependencies
import React, { Component } from "react";
import { get_responsive_styles, _getButtonsStyles } from "@ModuleHelper";
//Internal Dependencies
import "./style.css";


class DTQ_Dual_Button extends Component {
  static slug = "ba_dual_button";

  static css(props) {
    const additionalCss = [];

    if (props.connector_type !== "empty") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-btn__connector",
          declaration: `
            width: ${props.connector_size};
            height: ${props.connector_size};
            background: ${props.connector_bg};
            color: ${props.connector_text_color};
            font-size: ${props.connector_text_size};
            border-radius: ${props.connector_radius};
            box-shadow: 0 0 0 ${props.connector_border_width} ${props.connector_border_color};`,
        },
      ]);
    }

    // advanced border radius
    if (props.btn_a_radius) {
      let btn_a_radius = props.btn_a_radius.split("|");
      additionalCss.push([
        {
          selector:
            "body #page-container %%order_class%% .dtq-dual-btn .dtq-btn-wrap .btn-el--primary, .et-db #et-boc %%order_class%% .dtq-dual-btn .dtq-btn-wrap .btn-el--primary",
          declaration: `
                    border-top-left-radius: ${btn_a_radius[1]}!important;
                    border-top-right-radius: ${btn_a_radius[2]}!important;
                    border-bottom-right-radius: ${btn_a_radius[3]}!important;
                    border-bottom-left-radius: ${btn_a_radius[4]}!important;`,
        },
      ]);
    }
    if (props.btn_b_radius) {
      let btn_b_radius = props.btn_b_radius.split("|");
      additionalCss.push([
        {
          selector:
            "body #page-container %%order_class%% .dtq-dual-btn .dtq-btn-wrap .btn-el--secondary, .et-db #et-boc %%order_class%% .dtq-dual-btn .dtq-btn-wrap .btn-el--secondary",
          declaration: `
                    border-top-left-radius: ${btn_b_radius[1]}!important;
                    border-top-right-radius: ${btn_b_radius[2]}!important;
                    border-bottom-right-radius: ${btn_b_radius[3]}!important;
                    border-bottom-left-radius: ${btn_b_radius[4]}!important;`,
        },
      ]);
    }

    //buttons gap
    let button_gap = props.button_gap,
      button_gap_tablet = props.button_gap_tablet,
      button_gap_phone = props.button_gap_phone,
      button_gap_last_edited = props.button_gap_last_edited,
      button_gap_responsive_status =
        button_gap_last_edited && button_gap_last_edited.startsWith("on");

    additionalCss.push([
      {
        selector: "%%order_class%% .btn-el--secondary",
        declaration: `margin-left: calc(${button_gap} / 2);`,
      },
    ]);
    if (button_gap_tablet && button_gap_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .btn-el--secondary",
          device: "tablet",
          declaration: `margin-left: calc(${button_gap_tablet} / 2);`,
        },
      ]);
    }

    if (button_gap_phone && button_gap_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .btn-el--secondary",
          device: "phone",
          declaration: `margin-left: calc(${button_gap_phone} / 2);`,
        },
      ]);
    }
    additionalCss.push([
      {
        selector: "%%order_class%% .btn-el--primary",
        declaration: `margin-right: calc(${button_gap} / 2);`,
      },
    ]);

    if (button_gap_tablet && button_gap_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .btn-el--primary",
          device: "tablet",
          declaration: `margin-right: calc(${button_gap_tablet} / 2);`,
        },
      ]);
    }

    if (button_gap_phone && button_gap_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .btn-el--primary",
          device: "phone",
          declaration: `margin-right: calc(${button_gap_phone} / 2);`,
        },
      ]);
    }

    let primaryButtonStyles = _getButtonsStyles(
      "btn_a",
      props,
      "%%order_class%% .dtq-dual-btn .btn-el--primary"
    );
    let secondaryButtonStyles = _getButtonsStyles(
      "btn_b",
      props,
      "%%order_class%% .dtq-dual-btn .btn-el--secondary"
    );

    let btn_alignment = get_responsive_styles(
        props,
        "btn_alignment",
        "%%order_class%% .dtq-dual-btn",
        { primary: "justify-content", important: true },
        { default: "left" }
      );
    return additionalCss
      .concat(primaryButtonStyles)
      .concat(btn_alignment)
      .concat(secondaryButtonStyles);
  }

  _renderConnector = (props) => {
    let { connector_type, connector_text, connector_icon } = props;
    const utils = window.ET_Builder.API.Utils;
    const connectorIcon = connector_icon
      ? utils.processFontIcon(this.props.connector_icon)
      : "5";
    return (
      <div className={`dtq-btn__connector dtq-btn__connector--${connector_type}`}>
        {connector_type === "text" && connector_text}
        {connector_type === "icon" && (
          <i className='dtq-et-icon' data-icon={connectorIcon}></i>
        )}
      </div>
    );
  };

  _renderButtonA = () => {
    let props = this.props;
    const utils = window.ET_Builder.API.Utils;
    const button_link =
      typeof props.btn_a_link !== "undefined" ? props.btn_a_link : "#";
    const button_icon =
      typeof props.btn_a_icon !== "undefined"
        ? utils.processFontIcon(props.btn_a_icon)
        : "P";
    const button_target = props.btn_a_link_target;
    const button_classes = {
      et_pb_button: true,
      et_pb_custom_button_icon: props.btn_a_icon,
    };

    return (
      <a
        className={`${utils.classnames(button_classes)} btn-el btn-el--primary`}
        href={`${button_link}`}
        target={`${button_target}`}
        data-icon={button_icon}
      >
        {this.props.dynamic.btn_a_text.render() }
      </a>
    );
  };

  _renderButtonB = () => {
    let props = this.props;
    const utils = window.ET_Builder.API.Utils;
    const button_link =
      typeof props.btn_b_link !== "undefined" ? props.btn_b_link : "#";
    const button_icon =
      typeof props.btn_b_icon !== "undefined"
        ? utils.processFontIcon(props.btn_b_icon)
        : "P";
    const button_target = props.btn_b_link_target;
    const button_classes = {
      et_pb_button: true,
      et_pb_custom_button_icon: props.btn_b_icon,
    };

    return (
      <a
        className={`${utils.classnames(
          button_classes
        )} btn-el btn-el--secondary`}
        href={`${button_link}`}
        target={`${button_target}`}
        data-icon={button_icon}
      >
        {this.props.dynamic.btn_b_text.render() }
      </a>
    );
  };

  render() {
    let { connector_type } = this.props;
    return (
      <div className={`dtq-module dtq-dual-btn`}>
        <div className='dtq-btn-wrap'>
          {this._renderButtonA()}
          {connector_type !== "empty" && this._renderConnector(this.props)}
        </div>
        <div className='dtq-btn-wrap'>{this._renderButtonB()}</div>
      </div>
    );
  }
}

export default DTQ_Dual_Button;
