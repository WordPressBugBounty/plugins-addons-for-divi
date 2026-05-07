import React, { Component } from "react";
import { get_responsive_styles, _getCustomBgCss } from "@ModuleHelper";
import "./style.css";

class DTQ_TwitterFeed extends Component {
  static slug = "ba_twitter_feed";

  static css(props) {
    const additionalCss = [];

    if (!props.border_color_all_tweets) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-grid-item-inner",
          declaration: `border-color: #efefef;`,
        },
      ]);
    }

    if (!props.border_width_all_tweets) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-grid-item-inner",
          declaration: `border-width:1px;`,
        },
      ]);
    }

    // Grid Settings
    let column_gap_x = props.column_gap_x ? props.column_gap_x : "20px",
      column_gap_x_tablet = props.column_gap_x_tablet
        ? props.column_gap_x_tablet
        : column_gap_x,
      column_gap_x_phone = props.column_gap_x_phone
        ? props.column_gap_x_phone
        : column_gap_x_tablet,
      column_gap_y = props.column_gap_y ? props.column_gap_y : "20px",
      column_gap_y_tablet = props.column_gap_y_tablet
        ? props.column_gap_y_tablet
        : column_gap_y,
      column_gap_y_phone = props.column_gap_y_phone
        ? props.column_gap_y_phone
        : column_gap_y_tablet,
      column_count = props.column_count ? props.column_count : "3",
      column_count_tablet = props.column_count_tablet
        ? props.column_count_tablet
        : column_count,
      column_count_phone = props.column_count_phone
        ? props.column_count_phone
        : column_count_tablet;


		additionalCss.push([
			{
			  selector: "%%order_class%% .dtq-twitter-grid",
			  declaration: `
			  		grid-column-gap: ${column_gap_x};
			  		grid-row-gap: ${column_gap_y};
				  -ms-grid-columns: repeat(${column_count},1fr);
				  grid-template-columns: repeat(${column_count},1fr);`,
			},
		  ]);

		  additionalCss.push([
			{
			  selector: "%%order_class%% .dtq-twitter-grid",
			  device: "tablet",
			  declaration: `
			  	grid-column-gap: ${column_gap_x_tablet};
			  	grid-row-gap: ${column_gap_y_tablet};
				  grid-template-columns: repeat(${column_count_tablet}, 1fr);
				  -ms-grid-columns: repeat(${column_count_tablet}, 1fr);`,
			},
		  ]);

		  additionalCss.push([
			{
			  selector: "%%order_class%% .dtq-twitter-grid",
			  device: "phone",
			  declaration: `
			  	grid-column-gap: ${column_gap_x_phone};
			  	grid-row-gap: ${column_gap_y_phone};
				  grid-template-columns: repeat(${column_count_phone}, 1fr);
				  -ms-grid-columns: repeat(${column_count_phone}, 1fr);`,
			},
		  ]);

    let avatar_position = props.avatar_position,
      avatar_offset_y = props.avatar_offset_y,
      avatar_offset_x = props.avatar_offset_x,
      avatar_placement = props.avatar_placement,
      avatar__placement = avatar_placement.split("_"),
      avatar_spacing = props.avatar_spacing,
      alignment = props.alignment;

    //avatar spacing
    if (avatar_position === "normal") {
      if (alignment === "center") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `margin-bottom: ${avatar_spacing};`,
          },
        ]);
      } else if (alignment === "left") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `margin-right: ${avatar_spacing};`,
          },
        ]);
      } else if (alignment === "right") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `margin-left: ${avatar_spacing};`,
          },
        ]);
      }
    }

    // avatar position
    if (avatar_position === "absolute") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
          declaration: `position: absolute; z-index: 99;`,
        },
      ]);

      // avatar Offset x
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
          declaration: `${avatar__placement[0]}: ${avatar_offset_x};`,
        },
      ]);

      // avatar Offset y
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
          declaration: `${avatar__placement[1]}: ${avatar_offset_y};`,
        },
      ]);

      if (avatar_placement === "right_top") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `transform : translateX(50%) translateY(-50%);`,
          },
        ]);
      } else if (avatar_placement === "right_bottom") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `transform : translateX(50%) translateY(50%);`,
          },
        ]);
      } else if (avatar_placement === "left_bottom") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `transform : translateX(-50%) translateY(50%);`,
          },
        ]);
      } else if (avatar_placement === "left_top") {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-twitter-grid-avatar-wrapper",
            declaration: `transform : translateX(-50%) translateY(-50%);`,
          },
        ]);
      }
    }

    // Description spacing
    let description_spacing = get_responsive_styles(
      props,
      "description_spacing",
      "%%order_class%% .dtq-inner-twitter-grid-content p",
      { primary: "margin-bottom", important: true },
      { default: "25px" }
    );

    // Twitter icon
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-grid-icon span",
        declaration: `width: ${props.twitter_icon_size}; height: ${props.twitter_icon_size};`,
      },
    ]);

    // Avater size
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-grid-avatar",
        declaration: `width: ${props.avatar_size}; height: ${props.avatar_size};`,
      },
    ]);

    // Footer Alinmnet
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-twitter-grid-footer",
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
      let content_padding = get_responsive_styles(
        props,
        "content_padding",
        "%%order_class%% .dtq-twitter-grid-inner-wrapper",
        { primary: "padding", important: false },
        { default: "50px|50px|50px|50px" }
      );


    // Footer Padding
      let footer_padding = get_responsive_styles(
        props,
        "footer_padding",
        "%%order_class%% .dtq-twitter-grid-footer-wrapper",
        { primary: "padding", important: true },
        { default: "0px|50px|50px|50px" }
      );


    let tweets_item_bg_style = _getCustomBgCss(
      props,
      "tweets_item",
      "%%order_class%% .dtq-twitter-grid-item-inner",
      "%%order_class%% .dtq-twitter-grid-item-inner:hover"
    );

    return additionalCss
      .concat(content_padding)
      .concat(footer_padding)
      .concat(tweets_item_bg_style)
      .concat(description_spacing);
  }

  componentDidUpdate(prevProps) {}

  rawMarkup() {
    var rawMarkup = this.props.__twitterfeed;
    return { __html: rawMarkup };
  }

  render() {
    let alignment = this.props.alignment,
      classes = [];

    classes.push(`dtq-twitter-grid`);
    classes.push(`dtq-twitter-${alignment}`);

    return (
      <div
        className={`${classes.join(" ")}`}
        dangerouslySetInnerHTML={this.rawMarkup()}
      />
    );
  }
}

export default DTQ_TwitterFeed;
