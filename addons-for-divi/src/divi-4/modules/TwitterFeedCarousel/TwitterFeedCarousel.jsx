import $ from "jquery";
import React, { Component } from "react";
import '../../../../assets/libs/slick/slick.min.js';
import {
    _getCarouselCss, _getCarouselSettings, _getCustomBgCss
} from "@ModuleHelper";
import "./style.css";

class DTQ_TwitterFeedCarousel extends Component {
  static slug = "ba_twitter_feed_carousel";

  static css(props) {
    let additionalCss = [];

    if (!props.border_color_all_tweets) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-item-inner",
          declaration: `border-color: #efefef;`,
        },
      ]);
    }

    if (!props.border_width_all_tweets) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-item-inner",
          declaration: `border-width:1px;`,
        },
      ]);
    }

    let avatar_position = props.avatar_position,
      avatar_offset_x = props.avatar_offset_x,
      avatar_placement = props.avatar_placement,
      avatar_spacing = props.avatar_spacing,
      alignment = props.alignment;

    //avatar spacing
    if (avatar_position === "normal") {
      if (alignment === "center") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
            declaration: `margin-bottom: ${avatar_spacing};`,
          },
        ]);
      } else if (alignment === "left") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
            declaration: `margin-right: ${avatar_spacing};`,
          },
        ]);
      } else if (alignment === "right") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
            declaration: `margin-left: ${avatar_spacing};`,
          },
        ]);
      }
    }

    // avatar position
    if (avatar_position === "absolute") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
          declaration: `position: absolute; z-index: 99;`,
        },
      ]);

	  // avatar Offset x
	  let translateX = '-50%';
	  if( ! avatar_offset_x ) {
		additionalCss.push([
			{
			  selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
			  declaration: `left: 50%;`,
			},
		  ]);
	  } else {
		translateX = '0';
		avatar_offset_x = parseInt( props.avatar_offset_x );
		if( avatar_offset_x < 0 ) {
			additionalCss.push([
				{
				selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
				declaration: `
					left: ${ Math.abs(50 + avatar_offset_x) }%;
				`,
				},
			]);
		} else {
			additionalCss.push([
				{
				selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
				declaration: `
					right: ${ 50 - avatar_offset_x }%;
				`,
				},
			]);
		}

	  }

      if (avatar_placement === "top") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
            declaration: `top: 0; transform : translateX(${translateX}) translateY(-50%);`,
          },
        ]);
      } else if (avatar_placement === "bottom") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-feed-avatar-wrapper",
            declaration: `bottom:0;transform : translateX(${translateX}) translateY(50%);`,
          },
        ]);
      }
    }

    // Description spacing
    let description_spacing = props.description_spacing;
    let description_spacing_last_edited = props.description_spacing_last_edited;
    let description_spacing_responsive_status =
      description_spacing_last_edited &&
      description_spacing_last_edited.startsWith("on");
    let description_spacing_tablet = props.description_spacing_tablet;
    let description_spacing_phone = props.description_spacing_phone;

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-inner-twitter-feed-content p",
        declaration: `margin-bottom: ${description_spacing};`,
      },
    ]);

    if (
      typeof description_spacing_tablet !== "undefined" &&
      description_spacing_responsive_status
    ) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-inner-twitter-feed-content p",
          device: "tablet",
          declaration: `margin-bottom: ${description_spacing};`,
        },
      ]);
    }

    if (
      typeof description_spacing_phone !== "undefined" &&
      description_spacing_responsive_status
    ) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-inner-twitter-feed-content p",
          device: "phone",
          declaration: `margin-bottom: ${description_spacing_phone};`,
        },
      ]);
    }

    // Twitter icon
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-feed-icon span",
        declaration: `width: ${props.twitter_icon_size}; height: ${props.twitter_icon_size};`,
      },
    ]);

    // Avatar size
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-feed-avatar",
        declaration: `width: ${props.avatar_size}; height: ${props.avatar_size};`,
      },
    ]);

    // Footer Alignment
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-feed-footer",
        declaration: `display: flex; justify-content: ${props.footer_alignment};`,
      },
    ]);

    let favorite_color = props.favorite_color;
    let favorite_icon_color = props.favorite_icon_color;
    let favorite_font_size = props.favorite_font_size;
    let favorite_icon_size = props.favorite_icon_size;

    let retweet_color = props.retweet_color;
    let retweet_icon_color = props.retweet_icon_color;
    let retweet_icon_size = props.retweet_icon_size;
    let retweet_font_size = props.retweet_font_size;

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-favorite",
        declaration: `color: ${favorite_color};`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-favorite span",
        declaration: `color: ${favorite_icon_color};`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-favorite",
        declaration: `font-size: ${favorite_font_size} !important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-favorite span",
        declaration: `font-size: ${favorite_icon_size} !important;`,
      },
    ]);

    // Retweets
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-retweet",
        declaration: `color: ${retweet_color};`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-retweet span",
        declaration: `color: ${retweet_icon_color};`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-retweet",
        declaration: `font-size: ${retweet_font_size} !important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-tweet-retweet span",
        declaration: `font-size: ${retweet_icon_size} !important;`,
      },
    ]);

    // Tweets Item Padding
    let content_padding = props.content_padding;
    let content_padding_tablet = props.content_padding_tablet;
    let content_padding_phone = props.content_padding_phone;
    let content_padding_responsive_status =
      props.content_padding_last_edited &&
      props.content_padding_last_edited.startsWith("on");

    content_padding = content_padding.split("|");

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-feed-inner-wrapper",
        declaration: `padding:
				${content_padding[0]}
				${content_padding[1]}
				${content_padding[2]}
				${content_padding[3]};
			`,
      },
    ]);

    if (
      typeof content_padding_tablet !== "undefined" &&
      content_padding_responsive_status
    ) {
      content_padding_tablet = content_padding_tablet.split("|");

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-inner-wrapper",
          device: "tablet",
          declaration: `padding:
					${content_padding[0]}
					${content_padding[1]}
					${content_padding[2]}
					${content_padding[3]};
				`,
        },
      ]);
    }

    if (
      typeof content_padding_phone !== "undefined" &&
      content_padding_responsive_status
    ) {
      content_padding_phone = content_padding_phone.split("|");

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-inner-wrapper",
          device: "phone",
          declaration: `padding:
					${content_padding[0]}
					${content_padding[1]}
					${content_padding[2]}
					${content_padding[3]};
				`,
        },
      ]);
    }

    // Footer Padding
    let footer_padding = props.footer_padding.split("|");
    let footer_padding_tablet = props.footer_padding_tablet;
    let footer_padding_phone = props.footer_padding_phone;
    let footer_padding_responsive_status =
      props.footer_padding_last_edited &&
      props.footer_padding_last_edited.startsWith("on");

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-feed-footer-wrapper",
        declaration: `
                padding-top   : ${footer_padding[0]};
                padding-right : ${footer_padding[1]};
                padding-bottom: ${footer_padding[2]};
                padding-left  : ${footer_padding[3]};
			`,
      },
    ]);

    if (
      typeof footer_padding_tablet !== "undefined" &&
      footer_padding_responsive_status
    ) {
      footer_padding_tablet = footer_padding_tablet.split("|");

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-footer-wrapper",
          device: "tablet",
          declaration: `
                    padding-top   : ${footer_padding_tablet[0]};
                    padding-right : ${footer_padding_tablet[1]};
                    padding-bottom: ${footer_padding_tablet[2]};
                    padding-left  : ${footer_padding_tablet[3]};
				`,
        },
      ]);
    }

    if (
      typeof footer_padding_phone !== "undefined" &&
      footer_padding_responsive_status
    ) {
      footer_padding_phone = footer_padding_phone.split("|");

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-feed-footer-wrapper",
          device: "phone",
          declaration: `
                    padding-top   : ${footer_padding_phone[0]};
                    padding-right : ${footer_padding_phone[1]};
                    padding-bottom: ${footer_padding_phone[2]};
                    padding-left  : ${footer_padding_phone[3]};
				`,
        },
      ]);
    }

    let carouselCss = _getCarouselCss(props),
      tweets_item_bg_style = _getCustomBgCss(
        props,
        "tweets_item",
        "%%order_class%% .dtq-twitter-feed-item-inner",
        "%%order_class%% .dtq-twitter-feed-item-inner:hover"
      );

    return additionalCss.concat(carouselCss).concat(tweets_item_bg_style);
  }

  constructor(props) {
    super(props);
    this.carousel = React.createRef();
  }

  componentDidUpdate(prevProps) {
    let settings = _getCarouselSettings(this.props, "jQuery");

    //Destory
    if (typeof this.carousel.current.slick !== "undefined") {
      this.carousel.current.slick.unslick();
    }

    // Init
    $(this.carousel.current).slick(settings);
  }

  rawMarkup() {
    var rawMarkup = this.props.__twitterfeed;
    return { __html: rawMarkup };
  }

  render() {
    let is_center = this.props.is_center,
      center_mode_type = this.props.center_mode_type,
      is_equal_height = this.props.is_equal_height,
      alignment = this.props.alignment,
      custom_cursor = this.props.custom_cursor,
      classes = [];

    if (is_center === "on") {
      classes.push(`dtq-centered dtq-centered--${center_mode_type}`);
    }

    if (custom_cursor === "on") {
      classes.push("dtq-cursor");
    }

    classes.push(`dtq-carousel dtq-twitter-feed-carousel`);
    classes.push(`dtq-twitter-${alignment}`);
    classes.push(`equal-height-${is_equal_height}`);

    return (
      <div
        className={`${classes.join(" ")}`}
        ref={this.carousel}
        dangerouslySetInnerHTML={this.rawMarkup()}
      />
    );
  }
}

export default DTQ_TwitterFeedCarousel;
