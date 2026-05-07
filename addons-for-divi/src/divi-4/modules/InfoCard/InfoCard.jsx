// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles, renderFontStyle, render_swapped_image, _getBadgeStyles, _getButtonsStyles,
    _getCustomBgCss, _getOverlayStyleCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";


class DTQ_Card extends Component {
  static slug = "ba_card";

  constructor(props) {
    super(props);
    this.state = { width: 0 };
    this.updateWindowDimensions = this.updateWindowDimensions.bind(this);
  }

  componentDidMount() {
    this.updateWindowDimensions();
    window.addEventListener("resize", this.updateWindowDimensions);
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateWindowDimensions);
  }

  updateWindowDimensions() {
    this.setState({ width: window.innerWidth });
  }

  static css(props) {
    const additionalCss = [];

    let image_width = props.image_width,
      content_padding = [],
      image_width_tablet = props.image_width_tablet,
      image_width_phone = props.image_width_phone,
      icon_padding = [],
      use_icon = props.use_icon,
      image_width_last_edited = props.image_width_last_edited,
      image_width_responsive_status =
        image_width_last_edited && image_width_last_edited.startsWith("on"),
      icon_bg = [],
      icon_color = props.icon_color,
      border_color_all_card = props.border_color_all_card,
      border_style_all_card = props.border_style_all_card,
      border_width_all_card = props.border_width_all_card,
      icon_size = props.icon_size,
      content_overflow = props.content_overflow,
      image_overflow = props.image_overflow,
      image_padding = props.image_padding,
      image_height = [],
      image_position = props.image_position,
      image_position_tablet = props.image_position_tablet,
      image_position_phone = props.image_position_phone,
	  image_position_last_edited = props.image_position_last_edited,
      image_position_responsive_status =
        image_position_last_edited && image_position_last_edited.startsWith("on");

		if( props.custom_margin ) {
			additionalCss.push([
				{
				  selector: "%%order_class%%.et_pb_module",
				  declaration: `margin-bottom: 0!important;`,
				},
			  ]);
		}

        let overlayIconStyle = renderFontStyle(
            props,
            "overlay_icon",
            "%%order_class%% .dtq-overlay .dtq-overlay-icon"
        );

    // Props processing
    if (image_position !== "top") {
      if (image_width === "auto") {
        image_width = "50%";
      }
    } else {
      if (image_width === "auto") {
        image_width = "100%";
      }
    }

    if (!border_color_all_card) {
      additionalCss.push([
        {
          selector: "%%order_class%%",
          declaration: `border-color: #efefef;`,
        },
      ]);
    }

    if (!border_width_all_card) {
      additionalCss.push([
        {
          selector: "%%order_class%%",
          declaration: `border-width:1px;`,
        },
      ]);
    }

    if (!border_style_all_card) {
      additionalCss.push([
        {
          selector: "%%order_class%%",
          declaration: `border-style:solid;`,
        },
      ]);
    }

	// content_alignment
    let content_alignment = get_responsive_styles(
		props,
		"content_alignment",
		"%%order_class%% .dtq-card",
		{ primary: "text-align", important: false },
		{ default: "left" }
	  );

    let figure_alignment = get_responsive_styles(
		props,
		"content_alignment",
		"%%order_class%% .dtq-card-figure",
		{ primary: "align-self", important: false },
		{ default: "left" }
	  );

	// Image/Icon position
	if( 'top' === image_position ) {
		additionalCss.push([
			{
			  selector: "%%order_class%% .dtq-card",
			  declaration: `flex-direction: column;`,
			},
		  ]);
	} else if( 'right' === image_position ) {
		additionalCss.push([
			{
			  selector: "%%order_class%% .dtq-card",
			  declaration: `flex-direction: row-reverse;`,
			},
		  ]);
	}

    if (image_position_tablet && image_position_responsive_status) {

		if( 'top' === image_position_tablet ) {
			additionalCss.push([
				{
				  selector: "%%order_class%% .dtq-card",
				  device: "tablet",
				  declaration: `flex-direction: column;`,
				},
			  ]);
		} else if( 'right' === image_position_tablet ) {
			additionalCss.push([
				{
				  selector: "%%order_class%% .dtq-card",
				  device: "tablet",
				  declaration: `flex-direction: row-reverse;`,
				},
			  ]);
		} else {
			additionalCss.push([
				{
				  selector: "%%order_class%% .dtq-card",
				  device: "tablet",
				  declaration: `flex-direction: row;`,
				},
			  ]);
		}
	  }


    if (image_position_phone && image_position_responsive_status) {
		if( 'top' === image_position_phone ) {
			additionalCss.push([
				{
				  selector: "%%order_class%% .dtq-card",
				  device: "phone",
				  declaration: `flex-direction: column;`,
				},
			  ]);
		} else if( 'right' === image_position_phone ) {
			additionalCss.push([
				{
				  selector: "%%order_class%% .dtq-card",
				  device: "phone",
				  declaration: `flex-direction: row-reverse;`,
				},
			  ]);
		} else {
			additionalCss.push([
				{
				  selector: "%%order_class%% .dtq-card",
				  device: "phone",
				  declaration: `flex-direction: row;`,
				},
			  ]);
		}
	  }

    // Image overflow
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-card-figure",
        declaration: `overflow: ${image_overflow}!important;`,
      },
    ]);

    // wrapper content overflow
    additionalCss.push([
      {
        selector: "%%order_class%%",
        declaration: `overflow: ${content_overflow}!important;`,
      },
    ]);

    // Button
    let btn_spacing_top = get_responsive_styles(
      props,
      "btn_spacing_top",
      "%%order_class%% .dtq-btn-card-wrap",
      { primary: "padding-top", important: true },
      { default: "15px" }
    );

    let buttonStyles = _getButtonsStyles(
      "button",
      props,
      "%%order_class%% .dtq-card .dtq-btn-card"
    );

    // Texts
    let title_bottom_spacing = get_responsive_styles(
      props,
      "title_bottom_spacing",
      "%%order_class%% .dtq-card-title, .et-db #et-boc %%order_class%% .dtq-card-title",
      { primary: "padding-bottom", important: true },
      { default: "10px" }
    );

    if (use_icon === "on") {
      // Icon Padding
      icon_padding = get_responsive_styles(
        props,
        "icon_padding",
        "%%order_class%% .dtq-card .dtq-card-icon",
        { primary: "padding", important: false },
        { default: "25px|25px|25px|25px" }
      );
    } else if (use_icon === "off") {
      // Image Padding
      image_padding = get_responsive_styles(
        props,
        "image_padding",
        "%%order_class%% .dtq-card-figure img",
        { primary: "padding", important: false },
        { default: "0px|0px|0px|0px" }
      );

      // Figure Height
      if( props.custom_height === 'on' ) {
        image_height = get_responsive_styles(
            props,
            "image_height",
            "%%order_class%% .dtq-card-figure",
            { primary: "height", important: false },
            { default: "200px" }
          );
      }


      // Figure  width
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-card-figure",
          declaration: `width: ${image_width}; max-width: ${image_width};`,
        },
      ]);

      // Figure  width tablet
      if (image_width_tablet && image_width_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-card-figure",
            device: "tablet",
            declaration: `max-width: ${image_width_tablet};width: ${image_width_tablet};`,
          },
        ]);
      }

      // Figure  width phone
      if (image_width_phone && image_width_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-card-figure",
            device: "phone",
            declaration: `max-width: ${image_width_phone};width: ${image_width_phone};`,
          },
        ]);
      }
    }

    // Content padding
    content_padding = get_responsive_styles(
      props,
      "content_padding",
      "%%order_class%% .dtq-card-content",
      { primary: "padding" },
      { default: "25px|25px|25px|25px" }
    );

    // Icon
    let iconStyle = [];
    if (use_icon === "on") {

        iconStyle = renderFontStyle(
            props,
            "icon",
            "%%order_class%% .dtq-card-icon"
        );

      icon_bg = _getCustomBgCss(
        props,
        "icon",
        "%%order_class%% .dtq-card-icon",
        "%%order_class%%:hover .dtq-card-icon"
      );
    }

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-card-icon i",
        declaration: `color: ${icon_color}; font-size: ${icon_size};`,
      },
    ]);

    //badge
    let badge_styles = _getBadgeStyles(
      props,
      "badge",
      "%%order_class%% .dtq-card-badge",
      "%%order_class%%:hover .dtq-card-badge",
      {
        position: "right_top",
        offset_x: "34px",
        offset_y: "15px",
        badge_padding: "5px|15px|5px|15px",
        bg: "#F3B325",
      }
    );

    // Overlay Styles
    let overlay_styles = _getOverlayStyleCss(props, "photo", "%%order_class%%" );

    return additionalCss
      .concat(overlayIconStyle)
      .concat(content_alignment)
      .concat(icon_bg)
      .concat(overlay_styles)
      .concat(buttonStyles)
      .concat(content_padding)
      .concat(title_bottom_spacing)
      .concat(figure_alignment)
      .concat(icon_padding)
      .concat(image_height)
      .concat(iconStyle)
      .concat(image_padding)
      .concat(btn_spacing_top)
      .concat(badge_styles);
  }

  render_badge = () => {
    let badge_position = this.props.badge_position
      ? this.props.badge_position
      : "right_bottom";
    let use_badge = this.props.use_badge;

    if (use_badge !== "off") {
      return (
        <div className={`dtq-card-badge pos--${badge_position}`}>
          {this.props.dynamic.badge_text.render()}
        </div>
      );
    }
  };

  render_figure = (url) => {
    let use_icon = this.props.use_icon ? this.props.use_icon : "off";
    const utils = window.ET_Builder.API.Utils;
    let overlay_icon = this.props.overlay_icon
      ? utils.processFontIcon(this.props.overlay_icon)
      : "";

    if (use_icon !== "on" && url) {
      return (
        <div className='dtq-card-figure'>
          {this.render_badge()}
          <div  className='dtq-overlay'><i className="dtq-overlay-icon">{overlay_icon}</i></div>
          <img className='dtq-img-cover dtq-card-figure-img' src={url} alt='' />
        </div>
      );
    }
  };

  render_icon = () => {
    let props = this.props,
      use_icon = props.use_icon ? props.use_icon : "off";

    const utils = window.ET_Builder.API.Utils,
      Icon = props.icon ? utils.processFontIcon(props.icon) : "";

    if (use_icon === "off") return;

    return (
      <div className='dtq-card-icon-wrap'>
        {this.render_badge()}
        <div className='dtq-card-icon'>
          <i className='dtq-et-icon'>{Icon}</i>
        </div>
      </div>
    );
  };

  render_title = () => {
    if (this.props.title) {
      let Title = this.props.title_level ? this.props.title_level : "h3";
      return (
        <Title className='dtq-card-title'>
          {this.props.dynamic.title.render()}
        </Title>
      );
    }
  };

  render_description = () => {
    if (this.props.description) {
      return (
        <div className='dtq-card-desc'>
          {this.props.dynamic.description.render()}
        </div>
      );
    }
  };

  render_content = () => {
    let { title, description, use_button } = this.props;
    use_button = use_button ? use_button : "off";
    description = description ? description : "";

    if (!title && description === "" && use_button === "off") {
      return false;
    }

    return (
      <div className='dtq-card-content'>
        {this.render_title()}
        {this.render_description()}
        {this.render_button()}
      </div>
    );
  };

  render_button = () => {
    let props = this.props;
    const utils = window.ET_Builder.API.Utils;

    if (props.use_button === "on") {
      const button_link =
        typeof props.button_link !== "undefined" ? props.button_link : "";
      const button_icon =
        typeof props.button_icon !== "undefined"
          ? utils.processFontIcon(props.button_icon)
          : "5";
      const button_target =
        "new_tab" === props.button_link_target ? "_blank" : "";
      const button_rel = utils.linkRel(props.button_rel);
      const button_classes = {
        et_pb_button: true,
        et_pb_custom_button_icon: props.button_icon,
      };

      return (
        <div className='dtq-btn-card-wrap'>
          <a
            className={`${utils.classnames(
              button_classes
            )} dtq-btn-default dtq-btn-card`}
            href={`${button_link}`}
            target={`${button_target}`}
            rel={button_rel}
            data-icon={button_icon}
          >{this.props.dynamic.button_text.render()}</a>
        </div>
      );
    }
  };

  render() {
    let props = this.props,
      image_hover_animation = this.props.image_hover_animation
        ? this.props.image_hover_animation
        : "none",
      use_icon = props.use_icon ? props.use_icon : "off";

    let classes = `dtq-hover--${image_hover_animation}  use-icon-${use_icon}`;

    return (
      <div className={`dtq-module dtq-card ${classes}`}>
        {render_swapped_image(
          "photo",
          this.props,
          this.render_figure,
          this.state
        )}
        {this.render_icon()}
        {this.render_content()}
      </div>
    );
  }
}

export default DTQ_Card;
