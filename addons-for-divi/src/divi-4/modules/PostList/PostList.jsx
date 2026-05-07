// External Dependencies
import React, { Component } from "react";
import { get_responsive_styles, _getCustomBgCss } from "@ModuleHelper";
// Internal Dependencies
import "./style.css";


class DTQ_Post_List extends Component {
  static slug = "ba_post_list";

  static css(props) {
    let additionalCss = [],
      alignment = props.alignment,
      list_type = props.list_type,
      show_thumb = props.show_thumb,
      show_icon = props.show_icon,
      items = props.items ? props.items : "4",
      items_tablet = props.items_tablet ? props.items_tablet : items,
      items_phone = props.items_phone ? props.items_phone : items_tablet,
      item_spacing = props.item_spacing ? props.item_spacing : "20px",
      item_spacing_tablet = props.item_spacing_tablet
        ? props.item_spacing_tablet
        : item_spacing,
      item_spacing_phone = props.item_spacing_phone
        ? props.item_spacing_phone
        : item_spacing_tablet,
      item_spacing_last_edited = props.item_spacing_last_edited,
      item_spacing_responsive_status =
        item_spacing_last_edited && item_spacing_last_edited.startsWith("on"),
	  image_width = [],
     image_height = [],
      image_spacing = props.image_spacing,
      image_spacing_tablet = props.image_spacing_tablet,
      image_spacing_phone = props.image_spacing_phone,
      image_spacing_last_edited = props.image_spacing_last_edited,
      image_spacing_responsive_status =
        image_spacing_last_edited && image_spacing_last_edited.startsWith("on"),
      icon_color = props.icon_color,
      icon_size = props.icon_size,
      icon_size_tablet = props.icon_size_tablet,
      icon_size_phone = props.icon_size_phone,
      icon_size_last_edited = props.icon_size_last_edited,
      icon_size_responsive_status =
        icon_size_last_edited && icon_size_last_edited.startsWith("on"),
      icon_spacing = props.icon_spacing,
      icon_spacing_tablet = props.icon_spacing_tablet,
      icon_spacing_phone = props.icon_spacing_phone,
      icon_spacing_last_edited = props.icon_spacing_last_edited,
      icon_spacing_responsive_status =
        icon_spacing_last_edited && icon_spacing_last_edited.startsWith("on"),
      meta_spacing = props.meta_spacing,
      meta_spacing_tablet = props.meta_spacing_tablet,
      meta_spacing_phone = props.meta_spacing_phone,
      meta_spacing_last_edited = props.meta_spacing_last_edited,
      meta_spacing_responsive_status =
        meta_spacing_last_edited && meta_spacing_last_edited.startsWith("on"),
      excerpt_spacing = props.excerpt_spacing,
      excerpt_spacing_tablet = props.excerpt_spacing_tablet,
      excerpt_spacing_phone = props.excerpt_spacing_phone,
      excerpt_spacing_last_edited = props.excerpt_spacing_last_edited,
      excerpt_spacing_responsive_status =
        excerpt_spacing_last_edited &&
        excerpt_spacing_last_edited.startsWith("on"),
      item_padding = props.item_padding,
      item_padding_tablet = props.item_padding_tablet,
      item_padding_phone = props.item_padding_phone,
      item_padding_responsive_status =
        props.item_padding_last_edited &&
        props.content_padding_last_edited.startsWith("on"),
      item_padding_ar = item_padding.split("|");

    let spacing_term = "bottom";
    if (alignment === "left") {
      spacing_term = "right";
    } else if (alignment === "right") {
      spacing_term = "left";

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child-inner",
          declaration: `flex-direction: row-reverse;`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-meta",
          declaration: `justify-content: flex-end;`,
        },
      ]);
    } else {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-meta",
          declaration: `justify-content: center;`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child-inner",
          declaration: `flex-direction: column;align-items: center;`,
        },
      ]);
    }

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-post-list-child-inner",
        declaration: `text-align:${alignment}!important;`,
      },
    ]);

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-post-list-child-inner",
        declaration: `
                    padding-top: ${item_padding_ar[0]};
                    padding-right: ${item_padding_ar[1]};
                    padding-bottom: ${item_padding_ar[2]};
                    padding-left: ${item_padding_ar[3]};`,
      },
    ]);

    if (item_padding_tablet && item_padding_responsive_status) {
      item_padding_tablet = item_padding_tablet.split("|");

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child-inner",
          device: "tablet",
          declaration: `
                        padding-top: ${item_padding_tablet[0]};
                        padding-right: ${item_padding_tablet[1]};
                        padding-bottom: ${item_padding_tablet[2]};
                        padding-left: ${item_padding_tablet[3]};`,
        },
      ]);
    }

    if (item_padding_phone && item_padding_responsive_status) {
      item_padding_phone = item_padding_phone.split("|");

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child-inner",
          device: "phone",
          declaration: `
                        padding-top: ${item_padding_phone[0]};
                        padding-right: ${item_padding_phone[1]};
                        padding-bottom: ${item_padding_phone[2]};
                        padding-left: ${item_padding_phone[3]};`,
        },
      ]);
    }

    if (list_type === "grid") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child",
          declaration: `
                        flex: 0 0 calc(100%/${items});
                        max-width: calc(100%/${items});
                        padding: ${item_spacing};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-parent",
          declaration: `margin: -${item_spacing};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child",
          device: "tablet",
          declaration: `
                        flex: 0 0 calc(100%/${items_tablet});
                        max-width: calc(100%/${items_tablet});
                        padding: ${item_spacing_tablet};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-parent",
          device: "tablet",
          declaration: `margin: -${items_tablet};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child",
          device: "phone",
          declaration: `
                        flex: 0 0 calc(100%/${items_phone});
                        max-width: calc(100%/${items_phone});
                        padding: ${item_spacing_phone};`,
        },
      ]);

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-parent",
          device: "phone",
          declaration: `margin: -${item_spacing_phone};`,
        },
      ]);
    } else if (list_type === "list") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-child",
          declaration: `padding-bottom: ${item_spacing}!important;`,
        },
      ]);

      if (item_spacing_tablet && item_spacing_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-child",
            device: "tablet",
            declaration: `padding-bottom: ${item_spacing_tablet}!important;`,
          },
        ]);
      }

      if (item_spacing_phone && item_spacing_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-child",
            device: "phone",
            declaration: `padding-bottom: ${item_spacing_phone}!important;`,
          },
        ]);
      }
    }

    // texts
    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-post-list-meta",
        declaration: `padding-top: ${meta_spacing}!important;`,
      },
    ]);

    if (meta_spacing_tablet && meta_spacing_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-meta",
          device: "tablet",
          declaration: `padding-top: ${meta_spacing_tablet}!important;`,
        },
      ]);
    }

    if (meta_spacing_phone && meta_spacing_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-meta",
          device: "phone",
          declaration: `padding-top: ${meta_spacing_phone}!important;`,
        },
      ]);
    }

    additionalCss.push([
      {
        selector: "%%order_class%% .dtq-post-list-content p",
        declaration: `padding-top: ${excerpt_spacing}!important;`,
      },
    ]);

    if (excerpt_spacing_tablet && excerpt_spacing_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-content p",
          device: "tablet",
          declaration: `padding-top: ${excerpt_spacing_tablet}!important;`,
        },
      ]);
    }

    if (excerpt_spacing_phone && excerpt_spacing_responsive_status) {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-content p",
          device: "phone",
          declaration: `padding-top: ${excerpt_spacing_phone}!important;`,
        },
      ]);
    }

    // thumbnail
    if (show_thumb === "on") {
      image_height = get_responsive_styles(
        props,
        "image_height",
        "%%order_class%% .dtq-post-list-thumb img",
        { primary: "height" },
        { default: "60px" }
      );

      image_width = get_responsive_styles(
        props,
        "image_width",
        "%%order_class%% .dtq-post-list-thumb img",
        { primary: 'width' },
        { default: "60px" }
      );

      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-thumb",
          declaration: `margin-${spacing_term}: ${image_spacing};`,
        },
      ]);

      if (image_spacing_tablet && image_spacing_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-thumb",
            device: "tablet",
            declaration: `margin-${spacing_term}: ${image_spacing_tablet};`,
          },
        ]);
      }

      if (image_spacing_phone && image_spacing_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-thumb",
            device: "phone",
            declaration: `margin-${spacing_term}: ${image_spacing_phone};`,
          },
        ]);
      }
    }

    // list icon
    if (show_icon === "on") {
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-icon",
          declaration: `font-size: ${icon_size}; color: ${icon_color};`,
        },
      ]);

      if (icon_size_tablet && icon_size_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-icon",
            device: "tablet",
            declaration: `font-size: ${icon_size_tablet};`,
          },
        ]);
      }

      if (icon_size_phone && icon_size_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-icon",
            device: "phone",
            declaration: `font-size: ${icon_size_phone};`,
          },
        ]);
      }
      additionalCss.push([
        {
          selector: "%%order_class%% .dtq-post-list-icon",
          declaration: `margin-${spacing_term}: ${icon_spacing};`,
        },
      ]);

      if (icon_spacing_tablet && icon_spacing_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-icon",
            device: "tablet",
            declaration: `margin-${spacing_term}: ${icon_spacing_tablet};`,
          },
        ]);
      }

      if (icon_spacing_phone && icon_spacing_responsive_status) {
        additionalCss.push([
          {
            selector: "%%order_class%% .dtq-post-list-icon",
            device: "phone",
            declaration: `margin-${spacing_term}: ${icon_spacing_phone};`,
          },
        ]);
      }
    }

    // post bg
    let post_bg = _getCustomBgCss(
      props,
      "post",
      "%%order_class%% .dtq-post-list-child-inner",
      "%%order_class%%:hover .dtq-post-list-child-inner"
    );

    return additionalCss
      .concat(post_bg)
      .concat(image_height)
      .concat(image_width);
  }

  render() {
    return (
      <div className={`dtq-module dtq-post-list type-${this.props.list_type}`}>
        <ul
          className='dtq-post-list-parent'
          dangerouslySetInnerHTML={{ __html: this.props.__posts }}
        />
      </div>
    );
  }
}

export default DTQ_Post_List;
