import React, { Component } from "react";
import { get_responsive_styles, renderFontStyle } from "@ModuleHelper";
import "./style.css";


class DTQ_ContactForm7 extends Component {
  static slug = "ba_cf7_styler";

  static css(props) {
    const additionalCss = [];

    if ("on" === props.use_form_button_fullwidth) {
      additionalCss.push([
        {
          selector:
            "%%order_class%% .dtq-cf7-styler .wpcf7 input[type=submit], %%order_class%% .wpcf7-form button.wpcf7-submit",
          declaration: `width: 100% !important;`,
        },
      ]);
    }

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-form-header-container",
        declaration: `background-color: ${props.form_header_bg}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-cf7-styler",
        declaration: `background-color: ${props.form_bg}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-form-header-container",
        declaration: `margin-bottom: ${props.form_header_bottom}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector:
          "%%order_class%% .dtq-form-header-icon, %%order_class%% .dtq-form-header-image",
        declaration: `background-color: ${props.form_header_img_bg}!important;`,
      },
    ]);

    let form_header_icon_padding = props.form_header_icon_padding.split("|");
    additionalCss.push([
      {
        selector:
          "%%order_class%% .dtq-form-header-icon, %%order_class%% .dtq-form-header-image",
        declaration: `
            padding-top: ${form_header_icon_padding[0]};
            padding-right: ${form_header_icon_padding[1]};
            padding-bottom: ${form_header_icon_padding[2]};
            padding-left: ${form_header_icon_padding[3]};`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-form-header-icon span",
        declaration: `color: ${props.form_header_icon_color}!important;`,
      },
    ]);
    let form_header_icon_size = get_responsive_styles(
      props,
      "form_header_icon_size",
      "%%order_class%% .dtq-form-header-icon span",
      { primary: "font-size", important: false },
      { default: "32px" }
    );

    let iconStyle = renderFontStyle(
        props,
        "header_icon",
        "%%order_class%% .dtq-form-header-icon span"
    );

    let form_header_padding = props.form_header_padding.split("|");
    let form_header_padding_last_edited = props.form_header_padding_last_edited;
    let form_header_padding_responsive_status =
      form_header_padding_last_edited &&
      form_header_padding_last_edited.startsWith("on");
    let form_header_padding_tablet = props.form_header_padding_tablet;
    let form_header_padding_phone = props.form_header_padding_phone;
    let header_title_spacing = props.header_title_spacing;

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-form-header .dtq-form-header-title",
        declaration: `
            padding-bottom : ${header_title_spacing}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-form-header-container",
        declaration: `
            padding-top   : ${form_header_padding[0]}!important;
            padding-right : ${form_header_padding[1]}!important;
            padding-bottom: ${form_header_padding[2]}!important;
            padding-left  : ${form_header_padding[3]}!important;`,
      },
    ]);

    if (form_header_padding_tablet && form_header_padding_responsive_status) {
      let padding_tablet = form_header_padding_tablet.split("|");
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-form-header-container",
          device: "tablet",
          declaration: `
                padding-top   : ${padding_tablet[0]}!important;
                padding-right : ${padding_tablet[1]}!important;
                padding-bottom: ${padding_tablet[2]}!important;
                padding-left  : ${padding_tablet[3]}!important;`,
        },
      ]);
    }

    if (form_header_padding_phone && form_header_padding_responsive_status) {
      let padding_phone = form_header_padding_phone.split("|");
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-form-header-container",
          device: "phone",
          declaration: `
                padding-top   : ${padding_phone[0]}!important;
                padding-right : ${padding_phone[1]}!important;
                padding-bottom: ${padding_phone[2]}!important;
                padding-left  : ${padding_phone[3]}!important;`,
        },
      ]);
    }

    let form_padding = props.form_padding.split("|");
    let form_padding_last_edited = props.form_padding_last_edited;
    let form_padding_responsive_status =
      form_padding_last_edited && form_padding_last_edited.startsWith("on");
    let form_padding_tablet = props.form_padding_tablet;
    let form_padding_phone = props.form_padding_phone;

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-cf7-styler",
        declaration: `
            padding-top   : ${form_padding[0]}!important;
            padding-right : ${form_padding[1]}!important;
            padding-bottom: ${form_padding[2]}!important;
            padding-left  : ${form_padding[3]}!important;`,
      },
    ]);

    if (form_padding_tablet && form_padding_responsive_status) {
      let padding_tablet = form_padding_tablet.split("|");
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-cf7-styler",
          device: "tablet",
          declaration: `
                padding-top   : ${padding_tablet[0]}!important;
                padding-right : ${padding_tablet[1]}!important;
                padding-bottom: ${padding_tablet[2]}!important;
                padding-left  : ${padding_tablet[3]}!important;`,
        },
      ]);
    }

    if (form_padding_phone && form_padding_responsive_status) {
      let padding_phone = form_padding_phone.split("|");
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-cf7-styler",
          device: "phone",
          declaration: `
                padding-top   : ${padding_phone[0]}!important;
                padding-right : ${padding_phone[1]}!important;
                padding-bottom: ${padding_phone[2]}!important;
                padding-left  : ${padding_phone[3]}!important;`,
        },
      ]);
    }

    additionalCss.push([
      {
        selector:
          "%%order_class%% input:not([type=submit]), %%order_class%% select, %%order_class%% .dtq-cf7-styler textarea, %%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio]:not(:checked) + span:before",
        declaration: `background-color: ${props.form_background_color}!important;`,
      },
    ]);

    let form_field_height = [];
    if (props.form_field_height) {
      form_field_height = get_responsive_styles(
        props,
        "form_field_height",
        `%%order_class%% .wpcf7-form-control-wrap select, %%order_class%% .wpcf7-form-control-wrap input[type=text],
			%%order_class%% .wpcf7-form-control-wrap input[type=email], %%order_class%% .wpcf7-form-control-wrap input[type=number], %%order_class%% .wpcf7-form-control-wrap input[type=tel]`,
        { primary: "height" },
        { default: "initial", important: true }
      );
    }

    let form_field_padding = get_responsive_styles(
      props,
      "form_field_padding",
      `.dtq-cf7-styler .wpcf7 input:not([type="submit"]):not([type="checkbox"]):not([type="radio"]),
	  	.dtq-cf7-styler .wpcf7 select,
	  	.dtq-cf7-styler .wpcf7 textarea`,
      { primary: "padding" },
      { default: "initial", important: true }
    );

    additionalCss.push([
      {
        selector:
          "%%order_class%% .dtq-cf7-styler .wpcf7 input:not([type=submit]):focus, %%order_class%% .dtq-cf7-styler .wpcf7 select:focus, %%order_class%% .dtq-cf7-styler .wpcf7 textarea:focus",
        declaration: `border-color: ${props.form_field_active_color}!important;`,
      },
    ]);

    if ("on" === props.cr_custom_styles) {
      additionalCss.push([
        {
          selector:
            "%%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio]:not(:checked) + span:before",
          declaration: `background-color: ${props.cr_background_color}!important;`,
        },
      ]);

      additionalCss.push([
        {
          selector:
            "%%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio] + span:before, %%order_class%% .wpcf7-checkbox input[type=checkbox]:checked + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox]:checked + span:before",
          declaration: `width: ${props.cr_size}!important; height: ${props.cr_size}!important; border-width: ${props.cr_border_size}!important;`,
        },
      ]);

      additionalCss.push([
        {
          selector:
            "%%order_class%% .wpcf7-checkbox input[type=checkbox]:checked + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox]:checked + span:before",
          declaration: `color: ${props.cr_selected_color}!important; font-size: calc( ${props.cr_size} / 1.2 )!important;`,
        },
      ]);

      additionalCss.push([
        {
          selector:
            "%%order_class%% .wpcf7-radio input[type=radio]:checked + span:before",
          declaration: `background-color: ${props.cr_selected_color}!important; box-shadow:inset 0px 0px 0px 4px ${props.cr_selected_color}!important;`,
        },
      ]);

      additionalCss.push([
        {
          selector:
            "%%order_class%% .wpcf7-checkbox input[type=radio] + span:before, %%order_class%% .dtq-cf7-styler .wpcf7-radio input[type=checkbox] + span:before, %%order_class%% .dtq-cf7-styler .wpcf7-acceptance input[type=checkbox] + span:before",
          declaration: `border-color: ${props.cr_border_color}!important;`,
        },
      ]);

      additionalCss.push([
        {
          selector:
            "%%order_class%% .wpcf7-checkbox label, %%order_class%% .wpcf7-radio label, %%order_class%%  .wpcf7-acceptance label",
          declaration: `color: ${props.cr_label_color}!important;`,
        },
      ]);
    }

    additionalCss.push([
      {
        selector: "%%order_class%% span.wpcf7-not-valid-tip",
        declaration: `color: ${props.cf7_message_color}!important; background-color: ${props.cf7_message_bg_color}; border-color: ${props.cf7_border_highlight_color}; padding: ${props.cf7_message_padding}; margin-top: ${props.cf7_message_margin_top}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .wpcf7-mail-sent-ok",
        declaration: `color: ${props.cf7_success_message_color}!important; background-color: ${props.cf7_success_message_color}; border-color: ${props.cf7_success_border_color}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .wpcf7-validation-errors",
        declaration: `color: ${props.cf7_error_message_color}; background-color: ${props.cf7_error_message_bg_color}!important; border-color: ${props.cf7_error_border_color}!important;`,
      },
    ]);

    // Form Field Spacing
    let form_field_spacing = props.form_field_spacing,
      form_field_spacing_last_edited = props.form_field_spacing_last_edited,
      form_field_spacing_responsive_status =
        form_field_spacing_last_edited &&
        form_field_spacing_last_edited.startsWith("on"),
      form_field_spacing_tablet = props.form_field_spacing_tablet,
      form_field_spacing_phone = props.form_field_spacing_phone;

    additionalCss.push([
      {
        selector:
          "%%order_class%% .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)",
        declaration: ` margin-bottom: ${form_field_spacing}!important;`,
      },
    ]);

    if (form_field_spacing_tablet && form_field_spacing_responsive_status) {
      additionalCss.push([
        {
          selector:
            "%%order_class%% .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)",
          device: "tablet",
          declaration: ` margin-bottom: ${form_field_spacing_tablet}!important;`,
        },
      ]);
    }

    if (form_field_spacing_phone && form_field_spacing_responsive_status) {
      additionalCss.push([
        {
          selector:
            "%%order_class%% .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)",
          device: "phone",
          declaration: ` margin-bottom: ${form_field_spacing_phone}!important;`,
        },
      ]);
    }

    // Form Label Spacing
    let form_label_spacing = props.form_label_spacing,
      form_label_spacing_last_edited = props.form_label_spacing_last_edited,
      form_label_spacing_responsive_status =
        form_label_spacing_last_edited &&
        form_label_spacing_last_edited.startsWith("on"),
      form_label_spacing_tablet = props.form_label_spacing_tablet,
      form_label_spacing_phone = props.form_label_spacing_phone;

    additionalCss.push([
      {
        selector:
          "%%order_class%% .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)",
        declaration: ` margin-top: ${form_label_spacing}!important;`,
      },
    ]);

    if (form_label_spacing_tablet && form_label_spacing_responsive_status) {
      additionalCss.push([
        {
          selector:
            "%%order_class%% .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)",
          device: "tablet",
          declaration: ` margin-top: ${form_label_spacing_tablet}!important;`,
        },
      ]);
    }

    if (form_label_spacing_phone && form_label_spacing_responsive_status) {
      additionalCss.push([
        {
          selector:
            "%%order_class%% .dtq-cf7 .wpcf7-form-control:not(.wpcf7-submit)",
          device: "phone",
          declaration: ` margin-top: ${form_label_spacing_phone}!important;`,
        },
      ]);
    }

    return additionalCss
      .concat(iconStyle)
      .concat(form_header_icon_size)
      .concat(form_field_height)
      .concat(form_field_padding);
  }

  _renderHeader(props) {
    if ("on" !== props.use_form_header) {
      return;
    }

    const utils = window.ET_Builder.API.Utils;

    //Icon & Image
    const header_img =
      typeof props.header_img !== "undefined" ? props.header_img : false;
    const image = header_img ? (
      <div className='dtq-form-header-image'>
        <img src={header_img} alt='' />
      </div>
    ) : (
      ""
    );

    const header_icon = utils.processFontIcon(props.header_icon);
    const icon = (
      <div className='dtq-form-header-icon'>
        <span className='et-pb-icon'>{header_icon}</span>
      </div>
    );
    const icon_image = "on" === props.use_icon ? icon : image;

    // Header
    const title =
      typeof props.form_header_title !== "undefined" ? (
        <h2 className='dtq-form-header-title'>{props.form_header_title}</h2>
      ) : (
        ""
      );
    const text =
      typeof props.form_header_text !== "undefined" ? (
        <div className='dtq-form-header-text'>{props.form_header_text}</div>
      ) : (
        ""
      );

    const header_info =
      title || text ? (
        <div className='dtq-form-header-info'>
          {title} {text}
        </div>
      ) : (
        ""
      );

    if (props.form_header_title || props.form_header_text) {
      return (
        <div className='dtq-form-header-container'>
          <div className='dtq-form-header'>
            {icon_image}
            {header_info}
          </div>
        </div>
      );
    }
  }

  render() {
    const props = this.props;

    let cr_custom_class = "";
    if ("on" === props.cr_custom_styles) {
      cr_custom_class = "dtq-cf7-cr";
    }

    let button_alignment = props.button_alignment;
    if ("on" === props.use_form_button_fullwidth) {
      button_alignment = "fullwidth";
    }

    return (
      <div
        className={`dtq-module dtq-cf7 dtq-cf7-styler-container dtq-cf7-styler-button-${button_alignment}`}
      >
        {this._renderHeader(props)}
        <div
          className={`dtq-cf7-styler ${cr_custom_class}`}
          dangerouslySetInnerHTML={{ __html: props.__cf7form }}
        />
      </div>
    );
  }
}

export default DTQ_ContactForm7;
