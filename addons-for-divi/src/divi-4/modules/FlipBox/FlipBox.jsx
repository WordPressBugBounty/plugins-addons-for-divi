// External Dependencies
import React, { Component } from "react";
import { get_responsive_styles, renderFontStyle, _getCustomBgCss } from "@ModuleHelper";
// Internal Dependencies
import "./style.css";


class DTQ_FlipBox extends Component {
  static slug = "ba_flipbox";

  static css(props) {
    const additionalCss = [];

    console.log(props);

    if ("rotate_3d" === props.animation_type ) {
		additionalCss.push([
			{
			  selector: `%%order_class%% .dtq-flipbox-inner .dtq-flank`,
			  declaration: `
				  background: ${props.flank_color};`,
			},
		  ]);
	  if ("v" === props.direction_alt) {
		additionalCss.push([
			{
			  selector: `%%order_class%% .dtq-flipbox-inner .dtq-flank`,
			  declaration: `
				  transform: rotateX(-90deg) translateZ(calc(${props.main_height} - 100px))!important;`,
			},
		  ]);

	  }
    }



    let main_height = get_responsive_styles(
      props,
      "main_height",
      "%%order_class%% .dtq-flipbox-inner",
      { primary: "height", important: false },
      { default: "300px" }
    );

    additionalCss.push([
      {
        selector: `%%order_class%% .dtq-flipbox-front-card,
          %%order_class%% .dtq-flipbox-back-card,
		  %%order_class%% .dtq-flipbox-card-container`,
        declaration: `transition: all ${props.duration} ease;`,
      },
    ]);

    // Front Side
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-flipbox-front-card",
        declaration: `align-items: ${props.front_align_items};`,
      },
    ]);
    if ("center" !== props.front_img_position) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-front-content",
          declaration: `align-items: ${props.front_align_items};`,
        },
      ]);
    }

    let front_alignment = get_responsive_styles(
      props,
      "front_alignment",
      "%%order_class%% .dtq-flipbox-front-card",
      { primary: "text-align", important: false },
      { default: "auto" }
    );

    let front_padding = get_responsive_styles(
      props,
      "front_padding",
      "%%order_class%% .dtq-flipbox-front-card",
      { primary: "padding", important: false },
      { default: "30px|30px|30px|30px" }
    );

    if ("center" !== props.front_img_position) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-front-content",
          declaration: `display: flex;`,
        },
      ]);
    }

    if ("right" === props.front_img_position) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-front-content",
          declaration: `flex-direction: row-reverse;`,
        },
      ]);
    }
    let frontIconStyle = renderFontStyle(
        props,
        "front_icon",
        "%%order_class%% .dtq-flipbox-icon-front"
    );
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-flipbox-icon-front",
        declaration: `font-size: ${props.front_icon_size};`,
      },
    ]);

    if (props.front_icon_color) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-icon-front",
          declaration: `color: ${props.front_icon_color};`,
        },
      ]);
    }

    let front_img_max_width = [];
    let front_img_flex = [];
    let front_img_width = [];
    if (props.front_img_width) {
      front_img_width = get_responsive_styles(
        props,
        "front_img_width",
        "%%order_class%% .dtq-flipbox-figure-front",
        { primary: "width", important: false },
        { default: "auto" }
      );
      front_img_max_width = get_responsive_styles(
        props,
        "front_img_width",
        "%%order_class%% .dtq-flipbox-figure-front",
        { primary: "max-width", important: false },
        { default: "auto" }
      );
      front_img_flex = get_responsive_styles(
        props,
        "front_img_width",
        "%%order_class%% .dtq-flipbox-figure-front",
        { primary: "flex", important: false },
        { default: "auto" }
      );
    }

    let front_img_height = [];
    let front_img_el_height = [];
    if (props.front_img_height) {
      front_img_height = get_responsive_styles(
        props,
        "front_img_height",
        "%%order_class%% .dtq-flipbox-figure-front",
        { primary: "height", important: false },
        { default: "auto" }
      );
      front_img_el_height = get_responsive_styles(
        props,
        "front_img_height",
        "%%order_class%% .dtq-flipbox-figure-front img",
        { primary: "height", important: false },
        { default: "auto" }
      );
    }
    let front_img_padding = get_responsive_styles(
      props,
      "front_img_padding",
      "%%order_class%% .dtq-flipbox-figure-front img",
      { primary: "padding", important: false },
      { default: "0|0|0|0" }
    );

    let front_ct_padding = get_responsive_styles(
      props,
      "front_ct_padding",
      "%%order_class%% .dtq-flipbox-front-card .dtq-flipbox-content-wrap",
      { primary: "padding", important: false },
      { default: "0|0|0|0" }
    );

    // Back Side
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-flipbox-back-card",
        declaration: `align-items: ${props.back_align_items};`,
      },
    ]);
    if ("center" !== props.back_img_position) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-back-content",
          declaration: `align-items: ${props.back_align_items};`,
        },
      ]);
    }

    let back_alignment = get_responsive_styles(
      props,
      "back_alignment",
      "%%order_class%% .dtq-flipbox-back-card",
      { primary: "text-align", important: false },
      { default: "auto" }
    );
    let back_padding = get_responsive_styles(
      props,
      "back_padding",
      "%%order_class%% .dtq-flipbox-back-card",
      { primary: "padding", important: false },
      { default: "30px|30px|30px|30px" }
    );
    if ("center" !== props.back_img_position) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-back-content",
          declaration: `display: flex;`,
        },
      ]);
    }

    if ("right" === props.back_img_position) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-back-content",
          declaration: `flex-direction: row-reverse;`,
        },
      ]);
    }

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-flipbox-icon-back",
        declaration: `font-size: ${props.back_icon_size};`,
      },
    ]);

    let backIconStyle = renderFontStyle(
        props,
        "back_icon",
        "%%order_class%% .dtq-flipbox-icon-back"
    );

    if (props.back_icon_color) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-flipbox-icon-back",
          declaration: `color: ${props.back_icon_color};`,
        },
      ]);
    }

    let back_img_flex = [];
    let back_img_max_width = [];
    let back_img_width = [];
    if (props.back_img_width) {
      back_img_width = get_responsive_styles(
        props,
        "back_img_width",
        "%%order_class%% .dtq-flipbox-figure-back",
        { primary: "width", important: false },
        { default: "auto" }
      );
      back_img_max_width = get_responsive_styles(
        props,
        "back_img_width",
        "%%order_class%% .dtq-flipbox-figure-back",
        { primary: "max-width", important: false },
        { default: "auto" }
      );
      back_img_flex = get_responsive_styles(
        props,
        "back_img_width",
        "%%order_class%% .dtq-flipbox-figure-back",
        { primary: "flex", important: false },
        { default: "auto" }
      );
    }

    let back_img_height = [];
    let back_img_el_height = [];
    if (props.back_img_height) {
      back_img_height = get_responsive_styles(
        props,
        "back_img_height",
        "%%order_class%% .dtq-flipbox-figure-back",
        { primary: "height", important: false },
        { default: "auto" }
      );
      back_img_el_height = get_responsive_styles(
        props,
        "back_img_height",
        "%%order_class%% .dtq-flipbox-figure-back img",
        { primary: "height", important: false },
        { default: "auto" }
      );
    }
    let back_img_padding = get_responsive_styles(
      props,
      "back_img_padding",
      "%%order_class%% .dtq-flipbox-figure-back img",
      { primary: "padding", important: false },
      { default: "0|0|0|0" }
    );
    let back_ct_padding = get_responsive_styles(
		props,
		"back_ct_padding",
		"%%order_class%% .dtq-flipbox-back-card .dtq-flipbox-content-wrap",
		{ primary: "padding", important: false },
		{ default: "0|0|0|0" }
	  );

	let front_bg = _getCustomBgCss(
        props,
        "front",
        "%%order_class%% .dtq-flipbox-front-card",
        ""
      );

	let back_bg = _getCustomBgCss(
        props,
        "back",
        "%%order_class%% .dtq-flipbox-back-card",
        ""
      );

	let front_img_bg = _getCustomBgCss(
        props,
        "front_img",
        "%%order_class%% .dtq-flipbox-figure-front",
        ""
      );

	let back_img_bg = _getCustomBgCss(
        props,
        "back_img",
        "%%order_class%% .dtq-flipbox-figure-back",
        ""
      );

	// Texts Spacing
    let front_subtitle_spacing = get_responsive_styles(
		props,
		"front_subtitle_spacing",
		"%%order_class%% .dtq-flipbox-subtitle-front",
		{ primary: "margin-top", important: true },
		{ default: "0px" }
	  );

    let back_subtitle_spacing = get_responsive_styles(
		props,
		"back_subtitle_spacing",
		"%%order_class%% .dtq-flipbox-subtitle-back",
		{ primary: "margin-top", important: true },
		{ default: "0px" }
	  );

    let front_desc_spacing = get_responsive_styles(
		props,
		"front_desc_spacing",
		"%%order_class%% .dtq-flipbox-desc-front",
		{ primary: "margin-top", important: true },
		{ default: "0px" }
	  );

    let back_desc_spacing = get_responsive_styles(
		props,
		"back_desc_spacing",
		"%%order_class%% .dtq-flipbox-desc-back",
		{ primary: "margin-top", important: true },
		{ default: "0px" }
	  );

	  let btn_spacing = get_responsive_styles(
		props,
		"btn_spacing",
		"%%order_class%% .dtq-flipbox-btn-wrap",
		{ primary: "margin-top", important: true },
		{ default: "15px" }
	  );


    return additionalCss
      .concat(frontIconStyle)
      .concat(backIconStyle)
      .concat(front_alignment)
      .concat(back_img_padding)
      .concat(front_img_padding)
      .concat(front_img_flex)
      .concat(back_img_flex)
      .concat(back_alignment)
      .concat(front_padding)
      .concat(front_img_width)
      .concat(front_img_max_width)
      .concat(front_img_height)
      .concat(back_img_width)
      .concat(back_img_max_width)
      .concat(back_padding)
      .concat(main_height)
      .concat(front_img_el_height)
      .concat(back_img_el_height)
      .concat(front_bg)
      .concat(back_bg)
      .concat(front_ct_padding)
      .concat(back_ct_padding)
      .concat(front_img_bg)
      .concat(back_img_bg)
      .concat(front_subtitle_spacing)
      .concat(back_subtitle_spacing)
      .concat(front_desc_spacing)
      .concat(back_desc_spacing)
      .concat(btn_spacing)
      .concat(back_img_height);
  }

  render_icon_front = () => {
    let props = this.props;
    const utils = window.ET_Builder.API.Utils,
      Icon = utils.processFontIcon(props.front_icon);

    return (
      <div className='dtq-flipbox-icon dtq-flipbox-icon-front'>
        <i className='dtq-et-icon'>{Icon}</i>
      </div>
    );
  };

  render_icon_back = () => {
    let props = this.props;
    const utils = window.ET_Builder.API.Utils,
      Icon = utils.processFontIcon(props.back_icon);

    return (
      <div className='dtq-flipbox-icon dtq-flipbox-icon-back'>
        <i className='dtq-et-icon'>{Icon}</i>
      </div>
    );
  };

  render_img_front = () => {
    return (
      <div className='dtq-flipbox-img-front'>
        <img src={this.props.front_img} alt='' />
      </div>
    );
  };

  render_img_back = () => {
    return (
      <div className='dtq-flipbox-img-back'>
        <img src={this.props.back_img} alt='' />
      </div>
    );
  };

  render_media_front = () => {
    if ("none" === this.props.front_media_type) {
      return "";
    }

    let front_icon = this.props.front_icon,
      front_media_type = this.props.front_media_type,
      front_img = this.props.front_img,
      media = "";

    if (front_icon || front_img) {
      if ("icon" === front_media_type) {
        media = this.render_icon_front();
      } else if ("image" === front_media_type) {
        media = this.render_img_front();
      }

      return <div className='dtq-flipbox-figure-front'>{media}</div>;
    }
  };

  render_media_back = () => {

    if ("none" === this.props.back_media_type) {
      return "";
    }

    let back_icon = this.props.back_icon,
      back_media_type = this.props.back_media_type,
      back_img = this.props.back_img,
      media = "";

    if (back_icon || back_img) {
      if ("icon" === back_media_type) {
        media = this.render_icon_back();
      } else if ("image" === back_media_type) {
        media = this.render_img_back();
      }
	  return <div className='dtq-flipbox-figure-back'>{media}</div>;
    }
  };

  render_title_front = () => {
    if (this.props.front_title) {
      return (
        <h2 className='dtq-flipbox-title-front'>
          {this.props.dynamic.front_title.render()}
        </h2>
      );
    }
  };

  render_title_back = () => {
    if (this.props.back_title) {
      return (
        <h2 className='dtq-flipbox-title-back'>
          {this.props.dynamic.back_title.render()}
        </h2>
      );
    }
  };

  render_description_front = () => {
    if (this.props.front_description) {
      return (
        <div className='dtq-flipbox-desc-front'>
          {this.props.dynamic.front_description.render()}
        </div>
      );
    }
  };

  render_description_back = () => {
    if (this.props.back_description) {
      return (
        <div className='dtq-flipbox-desc-back'>
          {this.props.dynamic.back_description.render()}
        </div>
      );
    }
  };

  render_subtitle_front = () => {
    if (this.props.front_subtitle) {
      return (
        <h4 className='dtq-flipbox-subtitle-front'>
          {this.props.dynamic.front_subtitle.render()}
        </h4>
      );
    }
  };


  render_subtitle_back = () => {
    if (this.props.back_subtitle) {
      return (
        <h4 className='dtq-flipbox-subtitle-back'>
          {this.props.dynamic.back_subtitle.render()}
        </h4>
      );
    }
  };

  render_button = () => {
    let props = this.props;
    const utils = window.ET_Builder.API.Utils;

    if (props.use_button === "on") {
      const button_link =
        typeof props.back_btn_link !== "undefined" ? props.back_btn_link : "";
      const button_icon =
        typeof props.back_btn_icon !== "undefined"
          ? utils.processFontIcon(props.back_btn_icon)
          : "5";
      const button_target =
        "new_tab" === props.back_btn_link_target ? "_blank" : "";
      const button_classes = {
        et_pb_button: true,
        et_pb_custom_button_icon: props.back_btn_icon,
      };

      return (
        <div className='dtq-flipbox-btn-wrap'>
          <a
            className={`${utils.classnames(button_classes)} dtq-flipbox-btn`}
            href={`${button_link}`}
            target={`${button_target}`}
            data-icon={button_icon}
          >
            {this.props.dynamic.button_text.render()}
          </a>
        </div>
      );
    }
  };

  render() {
    let animation_type = this.props.animation_type,
      animation_3d = this.props.animation_3d,
      direction = this.props.direction,
      direction_alt = this.props.direction_alt,
	  direction_diagonal = this.props.direction_diagonal,
      classes = [];

    classes.push(`dtq-flipbox--${animation_type}`);

    if ("on" === animation_3d) {
      classes.push("dtq-flipbox-3d");
    }

    if (
      "flip" === animation_type ||
      "slide" === animation_type ||
      "push" === animation_type
    ) {
      classes.push(`dtq-${animation_type}-${direction}`);
    }

    if ("diagonal" === animation_type) {
      classes.push(`dtq-${animation_type}-${direction_diagonal}`);
    }

    if ("rotate_3d" === animation_type) {
      classes.push(`dtq-${animation_type}-${direction_alt}`);
    }

    return (
      <div className={`dtq-module dtq-flipbox ${classes.join(" ")}`}>
        <div className='dtq-flipbox-inner'>
          <div className='dtq-flipbox-card-container'>
            <div className='dtq-flipbox-front-card dtq-flipbox-card'>
              <div className='dtq-flipbox-card-inner'>
                <div className='dtq-flipbox-front-content dtq-flipbox-content'>
                  {this.render_media_front()}
                  <div className='dtq-flipbox-content-wrap'>
                    {this.render_title_front()}
					{this.render_subtitle_front()}
                    {this.render_description_front()}
                  </div>
                </div>
              </div>
            </div>

            <div className='dtq-flipbox-back-card dtq-flipbox-card'>
              <div className='dtq-flipbox-card-inner'>
                <div className='dtq-flipbox-back-content dtq-flipbox-content'>
                  {this.render_media_back()}
                  <div className='dtq-flipbox-content-wrap'>
                    {this.render_title_back()}
					          {this.render_subtitle_back()}
                    {this.render_description_back()}
                    {this.render_button()}
                  </div>
                </div>
              </div>
            </div>

            <div className='dtq-flank'></div>
          </div>
        </div>
      </div>
    );
  }
}

export default DTQ_FlipBox;
