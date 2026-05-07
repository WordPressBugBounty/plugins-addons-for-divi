// External Dependencies
import React, { Component } from "react";
import {
    get_responsive_styles,
    _getAbsoluteElementStyles, _getCustomBgCss
} from "@ModuleHelper";
// Internal Dependencies
import "./style.css";

class DTQ_Testimonial extends Component {
  static slug = "ba_testimonial";

  static css(props) {
    let additionalCss = [],
      alignment = props.alignment,
      image_spacing = props.image_spacing,
      image_placement_alt = props.image_placement_alt,
      image_spacing_top = props.image_spacing_top,
      image_spacing_bottom = props.image_spacing_bottom,
      ratings_spacing_top = props.ratings_spacing_top,
      ratings_spacing_bottom = props.ratings_spacing_bottom,
      stars_size = props.stars_size,
      stars_color = props.stars_color,
      stars_spacing_between = props.stars_spacing_between,
      name_bottom_spacing = props.name_bottom_spacing,
      review_bottom_spacing = props.review_bottom_spacing,
      title_bottom_spacing = props.title_bottom_spacing,
      review_top_spacing = props.review_top_spacing,
      icon_placement = props.icon_placement,
      icon_opacity = props.icon_opacity,
      icon_color = props.icon_color,
      icon_bg = props.icon_bg,
      icon_padding = props.icon_padding,
      icon_padding_val = icon_padding.split("|"),
      icon_alignment = props.icon_alignment,
      img_position = props.img_position,
      img_is_center_x = props.img_is_center_x,
      img_is_center_y = props.img_is_center_y,
      img_offset_x = props.img_offset_x,
      img_offset_y = props.img_offset_y,
      image_placement = props.image_placement,
      image__placement = image_placement.split("_"),
      use_custom_icon = props.use_custom_icon ? props.use_custom_icon : "off",
      icon_img = props.icon_img,
      icon_size = [],
      abs_quote_styles = [],
      quote_icon_bg = [],
      content_padding = [],
      icon_height = [],
      icon_width = [],
      icon_top_spacing = [],
      icon_bottom_spacing = [],
      val_y = 0,
      val_x = 0;

    // content
    if (img_position === "left" || img_position === "right") {
      content_padding = get_responsive_styles(
        props,
        "content_padding",
        "%%order_class%% .dtq-testimonial-content",
        { primary: "padding", important: false },
        { default: "30px|30px|30px|30px" }
      );

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-content",
          declaration: `
                    flex: 1 1;`,
        },
      ]);
    }

    // Quote Icon
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-testimonial-inner .dtq-testimonial-icon",
        declaration: `text-align: ${icon_alignment}!important;`,
      },
    ]);

    if (icon_alignment === "right") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-icon",
          declaration: `justify-content: flex-end!important;`,
        },
      ]);
    } else if (icon_alignment === "center") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-icon",
          declaration: `justify-content: center!important;`,
        },
      ]);
    }

    if (use_custom_icon === "off") {
      quote_icon_bg = _getCustomBgCss(
        props,
        "icon",
        "%%order_class%% .dtq-testimonial-icon span",
        "%%order_class%%:hover .dtq-testimonial-icon span",
        ""
      );
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-icon span",
          declaration: `
                    padding-top: ${icon_padding_val[0]};
                    padding-right: ${icon_padding_val[1]};
                    padding-bottom: ${icon_padding_val[2]};
                    padding-left: ${icon_padding_val[3]};
					opacity: ${icon_opacity};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-icon svg",
          declaration: `fill: ${icon_color};`,
        },
      ]);

      // Icon Size
      icon_size = get_responsive_styles(
        props,
        "icon_size",
        "%%order_class%% .dtq-testimonial-icon svg",
        { primary: "width" },
        { default: "70px" }
      );
    } else {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-icon span",
          declaration: `
                    background-color: ${icon_bg};
                    background-image: url(${icon_img});
                    background-position: center;
                    background-repeat: no-repeat;
                    background-size: contain;
                    opacity: ${icon_opacity};`,
        },
      ]);
      // Icon Size
      icon_height = get_responsive_styles(
        props,
        "icon_size",
        "%%order_class%% .dtq-testimonial-icon span",
        { primary: "height" },
        { default: "70px" }
      );
      icon_width = get_responsive_styles(
        props,
        "icon_size",
        "%%order_class%% .dtq-testimonial-icon span",
        { primary: "width" },
        { default: "70px" }
      );
    }

    if (icon_placement !== "absolute") {
      // icon top spacing
      icon_top_spacing = get_responsive_styles(
        props,
        "icon_top_spacing",
        "%%order_class%% .dtq-testimonial-icon span",
        { primary: "margin-top" },
        { default: "40px" }
      );
      // icon bottom spacing
      icon_bottom_spacing = get_responsive_styles(
        props,
        "icon_bottom_spacing",
        "%%order_class%% .dtq-testimonial-icon span",
        { primary: "margin-bottom" },
        { default: "5px" }
      );
    } else {
      abs_quote_styles = _getAbsoluteElementStyles(
        props,
        "icon",
        "%%order_class%% .dtq-icon-absolute",
        {
          position: "right_top",
          offset_x: "34px",
          offset_y: "15px",
        }
      );
    }

    // Image
    if (img_position === "absolute") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-img",
          declaration: `position: absolute; z-index: 99;`,
        },
      ]);

      // Image Offset x
      if (img_is_center_x === "on") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `${image__placement[0]}: 50%;`,
          },
        ]);
      } else {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `${image__placement[0]}: ${img_offset_x};`,
          },
        ]);
      }

      // Image Offset y
      if (img_is_center_y === "on") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `${image__placement[1]}: 50%;`,
          },
        ]);
      } else {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `${image__placement[1]}: ${img_offset_y};`,
          },
        ]);
      }

      if (image_placement === "right_top") {
        if (img_is_center_y === "on") {
          val_y = "-50%";
        }
        if (img_is_center_x === "on") {
          val_x = "50%";
        }
      } else if (image_placement === "right_bottom") {
        if (img_is_center_y === "on") {
          val_y = "50%";
        }
        if (img_is_center_x === "on") {
          val_x = "50%";
        }
      } else if (image_placement === "left_bottom") {
        if (img_is_center_y === "on") {
          val_y = "50%";
        }
        if (img_is_center_x === "on") {
          val_x = "-50%";
        }
      } else if (image_placement === "left_top") {
        if (img_is_center_y === "on") {
          val_y = "-50%";
        }
        if (img_is_center_x === "on") {
          val_x = "-50%";
        }
      }
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-img",
          declaration: `transform : translateX(${val_x}) translateY(${val_y});`,
        },
      ]);
    }

    // image placement left/right
    if (img_position === "relative") {
      if (image_placement_alt !== "top") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-reviewer",
            declaration: `display: flex; align-items: center;`,
          },
        ]);
        additionalCss.push([
          {
            selector:
              "%%order_class%% .dtq-testimonial-inner .dtq-testimonial-reviewer *",
            declaration: `text-align: ${image_placement_alt};`,
          },
        ]);
      }

      if (image_placement_alt === "right") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-reviewer",
            declaration: `flex-direction: row-reverse;`,
          },
        ]);
      }
    }

    // image spacing
    if (img_position === "relative") {
      if (image_placement_alt === "top") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `margin-bottom: ${image_spacing_bottom}; margin-top: ${image_spacing_top};`,
          },
        ]);
      } else if (image_placement_alt === "left") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `margin-right: ${image_spacing};`,
          },
        ]);
      } else if (image_placement_alt === "right") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-img",
            declaration: `margin-left: ${image_spacing};`,
          },
        ]);
      }
    } else if (img_position === "top") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-img",
          declaration: `margin-bottom: ${image_spacing_bottom}; margin-top: ${image_spacing_top};`,
        },
      ]);
    }

    // Ratings
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-testimonial-rating",
        declaration: `
                padding-top: ${ratings_spacing_top};
                padding-bottom: ${ratings_spacing_bottom};
            `,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-testimonial-rating span",
        declaration: ` color: ${stars_color}; font-size: ${stars_size};`,
      },
    ]);

    if (alignment === "center") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-rating span",
          declaration: `
                    margin: 0 calc(${stars_spacing_between} / 2);
                `,
        },
      ]);
    } else if (alignment === "right") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-rating span",
          declaration: `
                    margin-left: ${stars_spacing_between};
                `,
        },
      ]);
    } else {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-rating span",
          declaration: `
                    margin-right: ${stars_spacing_between};
                `,
        },
      ]);
    }

    // text
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-testimonial-reviewer-text h3",
        declaration: `padding-bottom: ${name_bottom_spacing};`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-testimonial-title",
        declaration: `padding-bottom: ${title_bottom_spacing}; display: block;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-testimonial-review",
        declaration: `margin-bottom: ${review_bottom_spacing}; margin-top: ${review_top_spacing};`,
      },
    ]);

    // bubble
    let bubble_bg = [],
      reviewer_position = props.reviewer_position,
      arrow_color = props.arrow_color,
      arrow_placement = props.arrow_placement,
      arrow_position_x = props.arrow_position_x,
      bubble_padding = props.bubble_padding.split("|"),
      bubble_radius = props.bubble_radius.split("|");

    if (props.review_design === "bubble") {
      bubble_bg = _getCustomBgCss(
        props,
        "bubble",
        "%%order_class%% .dtq-testimonial-review",
        "%%order_class%%:hover .dtq-testimonial-review",
        "#efefef"
      );

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-review",
          declaration: `
                    position: relative;
                    border-radius: ${bubble_radius[1]} ${bubble_radius[2]} ${bubble_radius[3]} ${bubble_radius[4]};
                    padding-top: ${bubble_padding[0]};
                    padding-right: ${bubble_padding[1]};
                    padding-bottom: ${bubble_padding[2]};
                    padding-left: ${bubble_padding[3]};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-testimonial-review:after",
          declaration: `
                    content: "";
                    width: 0;
                    height: 0;
                    position: absolute;
                    border-style: solid;`,
        },
      ]);

      if (reviewer_position === "bottom") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-review:after",
            declaration: `
                        border-width: 13px 13px 0 13px;
                        border-color: ${arrow_color} transparent transparent transparent;
                        top: 100%;`,
          },
        ]);
      } else if (reviewer_position === "top") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-review:after",
            declaration: `
                        border-width: 0 13px 13px 13px;
                        border-color: transparent transparent ${arrow_color} transparent;
                        bottom: 100%;`,
          },
        ]);
      }

      if (arrow_placement === "left") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-review:after",
            declaration: `left: ${arrow_position_x};`,
          },
        ]);
      } else if (arrow_placement === "right") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-review:after",
            declaration: `right: ${arrow_position_x};`,
          },
        ]);
      } else if (arrow_placement === "center") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-testimonial-review:after",
            declaration: `left: 50%; transform: translateX(-13px);`,
          },
        ]);
      }
    }

    // Image height
    let image_height = get_responsive_styles(
      props,
      "image_height",
      "%%order_class%% .dtq-testimonial-img",
      {
        primary: "height",
        important: false,
      },
      {
        default: "65px",
        conditional: {
          name: "img_position",
          values: [
            {
              a: "left",
              b: "initial",
            },
            {
              a: "right",
              b: "initial",
            },
          ],
        },
      }
    );

    // Image width
    let image_width = get_responsive_styles(
      props,
      "image_width",
      "%%order_class%% .dtq-testimonial-img",
      {
        primary: "width",
        important: false,
      },
      {
        default: "65px",
        conditional: {
          name: "img_position",
          values: [
            {
              a: "left",
              b: "50%",
            },
            {
              a: "right",
              b: "50%",
            },
          ],
        },
      }
    );

    return additionalCss
      .concat(quote_icon_bg)
      .concat(content_padding)
      .concat(bubble_bg)
      .concat(image_width)
      .concat(image_height)
      .concat(icon_bottom_spacing)
      .concat(icon_top_spacing)
      .concat(icon_size)
      .concat(icon_height)
      .concat(abs_quote_styles)
      .concat(icon_width);
  }

  render_rating = (pos) => {
    let use_rating = this.props.use_rating ? this.props.use_rating : "off";
    let ratings_position = this.props.ratings_position
      ? this.props.ratings_position
      : "_default";

    if (use_rating === "on" && ratings_position === pos) {
      let rating = this.props.rating ? this.props.rating : "1",
        star_count = Array(parseInt(rating)).fill(1);

      return (
        <div className='dtq-testimonial-rating'>
          {star_count.map((el, i) => {
            return <span key={i}>★</span>;
          })}
        </div>
      );
    }
  };

  render_quote = (class_name) => {
	  let icons = {
		  '5': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.579 23.997">
			<g transform="translate(0 -53.918)">
			  <g transform="translate(0 53.918)">
				<g>
				  <path id="Path_14" data-name="Path 14" d="M232.2,58.871l-1.077-4.953c-7.757.356-13.138,3.582-13.138,13.073V77.915h13.551V64.19H226.4C226.4,61.141,228.314,59.349,232.2,58.871Z" transform="translate(-198.616 -53.918)"/>
				  <path id="Path_15" data-name="Path 15" d="M14.211,58.871l-1.073-4.953C5.377,54.274,0,57.5,0,66.991V77.915H13.552V64.19H8.416C8.416,61.141,10.326,59.349,14.211,58.871Z" transform="translate(0 -53.918)"/>
				</g>
			  </g>
			</g>
		  </svg>`,

		  '4': ` <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.453 24.005"><path id="Path_12" data-name="Path 12" d="M.98,24h12.6a.98.98,0,0,0,.981-.98v-12.6a.98.98,0,0,0-.981-.981H8.258V.98A.98.98,0,0,0,7.279,0H4.129A.98.98,0,0,0,3.2.67L.051,10.116A.973.973,0,0,0,0,10.428v12.6A.98.98,0,0,0,.98,24Zm0,0" transform="translate(18.895)"/><path id="Path_13" data-name="Path 13" d="M290.18,24h12.6a.98.98,0,0,0,.98-.98v-12.6a.98.98,0,0,0-.98-.981h-5.319V.98a.98.98,0,0,0-.98-.98h-3.149a.98.98,0,0,0-.93.67l-3.15,9.446a.982.982,0,0,0-.051.31v12.6a.979.979,0,0,0,.98.98Zm0,0" transform="translate(-289.199 0)"/></svg>`,

		  '3': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36.003 24.007">
			<path d="M17.66,18.228v1.582a7.789,7.789,0,0,1-.643,4.1A14.32,14.32,0,0,1,14.2,27.624a31.335,31.335,0,0,1-4.9,3.907,18.986,18.986,0,0,1-5.588,2.324.391.391,0,0,1-.593-.2.7.7,0,0,1,.1-.593,35.575,35.575,0,0,0,2.967-3.313,14.785,14.785,0,0,0,1.879-3.115h-.1a10.7,10.7,0,0,1-3.115-.692,5.451,5.451,0,0,1-1.929-1.385Q1.44,22.679,1.44,18.327v-.1q0-4.352,1.484-6.132a7.047,7.047,0,0,1,5.044-2.077,1.533,1.533,0,0,1,.643-.1H10.49a1.533,1.533,0,0,1,.643.1A7.047,7.047,0,0,1,16.177,12.1Q17.66,13.877,17.66,18.228Zm19.781,0v.1a8.193,8.193,0,0,1-.1,1.484,8.137,8.137,0,0,1-.593,4.1,12.666,12.666,0,0,1-2.868,3.709,28.055,28.055,0,0,1-4.8,3.907A18.986,18.986,0,0,1,23.5,33.855h-.1q-.3.2-.495-.2a.43.43,0,0,1,.1-.593,35.575,35.575,0,0,0,2.967-3.313,14.785,14.785,0,0,0,1.879-3.115h-.1a7.184,7.184,0,0,1-5.143-2.077q-1.385-1.78-1.385-6.231v-.1q0-4.451,1.385-6.132a7.184,7.184,0,0,1,5.143-2.077,1.533,1.533,0,0,1,.643-.1H30.27a1.533,1.533,0,0,1,.643.1A7.047,7.047,0,0,1,35.957,12.1q1.484,1.78,1.484,6.132Z" transform="translate(37.443 33.925) rotate(180)"/>
		  </svg>`,

		  '2': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24">
			<g transform="translate(0 -4)">
			  <g>
				<g>
				  <path d="M0,4V28L12,16V4Z"/>
				  <path d="M20,4V28L32,16V4Z"/>
				</g>
			  </g>
			</g>
		  </svg>`,

		  '1': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.591 24.006">
			<g>
			  <path d="M232.2,72.969l-1.078,4.955c-7.76-.356-13.138-3.584-13.138-13.078V53.918h13.553V67.653H226.4C226.406,70.7,228.317,72.491,232.2,72.969Z" transform="translate(-198.609 -53.918)"/>
			  <path d="M14.216,72.969l-1.078,4.955C5.378,77.569,0,74.341,0,64.846V53.918H13.556V67.654H8.42C8.42,70.7,10.331,72.491,14.216,72.969Z" transform="translate(0 -53.918)"/>
			</g>
		  </svg>`

	  }
    let selected_icon = this.props.selected_icon
        ? this.props.selected_icon
        : "1",
      hide_quote = this.props.hide_quote ? this.props.hide_quote : "off",
      use_custom_icon = this.props.use_custom_icon
        ? this.props.use_custom_icon
        : "off",
      icon = '';

    if (use_custom_icon === "off") {
      icon = icons[selected_icon];
    }

    if (hide_quote === "off") {
      return (
        <div className={`dtq-testimonial-icon ${class_name}`}>
          <span className='dtq-testimonial-icon-wrap' dangerouslySetInnerHTML={{__html: icon }}></span>
        </div>
      );
    }
  };

  render_review = () => {
    let icon_placement = this.props.icon_placement
      ? this.props.icon_placement
      : "background";

    return (
      <div className='dtq-testimonial-review'>
        {icon_placement === "_default"
          ? this.render_quote("dtq-icon-default")
          : ""}
        {this.props.testimonial && this.props.dynamic.testimonial.render()}
      </div>
    );
  };

  render_image = () => {
    if (this.props.image) {
      return (
        <figure className='dtq-testimonial-img'>
          <img className='dtq-img-cover' src={this.props.image} alt='' />
        </figure>
      );
    }
  };

  render_title = () => {
    let url = this.props.company_url,
      url_target = this.props.link_target,
      title = this.props.title;

    if (title) {
      if (url) {
        return (
          <a className='dtq-testimonial-title' target={url_target} href={url}>
            {this.props.dynamic.title.render()}
          </a>
        );
      } else {
        return (
          <div className='dtq-testimonial-title'>
            {this.props.dynamic.title.render()}
          </div>
        );
      }
    }
  };

  render_name = () => {
    let url = this.props.website_url,
      url_target = this.props.link_target,
      name = this.props.name;

    if (name) {
      if (url) {
        return (
          <a target={url_target} href={url}>
            <h3>{this.props.dynamic.name.render()}</h3>
          </a>
        );
      } else {
        return <h3>{this.props.dynamic.name.render()}</h3>;
      }
    }
  };

  render_reviewer = (pos) => {
    let reviewer_position = this.props.reviewer_position
      ? this.props.reviewer_position
      : "bottom";
    let img_position = this.props.img_position
      ? this.props.img_position
      : "relative";

    if (reviewer_position === pos) {
      return (
        <div className='dtq-testimonial-reviewer'>
          {img_position === "relative" ? this.render_image() : ""}

          <div className='dtq-testimonial-reviewer-text'>
            {this.render_name()}
            {this.render_title()}
            {this.render_rating("reviewer")}
          </div>
        </div>
      );
    }
  };

  render() {
    let alignment = this.props.alignment ? this.props.alignment : "center",
      icon_placement = this.props.icon_placement
        ? this.props.icon_placement
        : "background",
      img_position = this.props.img_position
        ? this.props.img_position
        : "relative";

    return (
      <div className={`dtq-module dtq-testimonial dtq-align-${alignment}`}>
        {img_position === "absolute" ? this.render_image() : ""}

        {icon_placement === "absolute"
          ? this.render_quote("dtq-icon-absolute")
          : ""}

        <div
          className={`dtq-testimonial-inner dtq-bg-support img-pos-${img_position}`}
        >
          {img_position === "top" || img_position === "left"
            ? this.render_image()
            : ""}
          {icon_placement === "background"
            ? this.render_quote("dtq-icon-bg")
            : ""}

          <div className='dtq-testimonial-content'>
            {this.render_reviewer("top")}
            {this.render_rating("_default")}
            {this.render_review()}
            {this.render_reviewer("bottom")}
            {this.render_rating("bottom")}
          </div>

          {img_position === "right" ? this.render_image() : ""}
        </div>
      </div>
    );
  }
}

export default DTQ_Testimonial;
