import "./attributes/sidebar";
import "./attributes/post-sidebar";
import "./attributes/customize-sidebar";

function addWidgetOptionAttributes(settings, name) {
  if (settings.attributes) {
    let isWidgetBlockEditor = document.body?.classList.contains("widgets-php");
    let isWpCustomizer = typeof wp.customize !== "undefined";

    //add here the blocks that are serversiderender
    const originalRenderCallback = settings.edit;
    settings.edit = function (props) {
      // Modify or extend the props.attributes here
      if (name == "luckywp/tableofcontents") {
        //if block type is luckywp/tableofcontents use type string
        if (isWidgetBlockEditor || isWpCustomizer) {
          if (props.attributes.extended_widget_opts_block == undefined) {
            props.attributes.extended_widget_opts_block = JSON.stringify({});
          }

          if (isWpCustomizer) {
            if (props.attributes.extended_widget_opts == undefined) {
              props.attributes.extended_widget_opts = JSON.stringify({});
            }
          }
        } else {
          if (props.attributes.extended_widget_opts == undefined) {
            props.attributes.extended_widget_opts = JSON.stringify({});
          }
        }
      } else {
        //if block type is not luckywp/tableofcontents use type object
        if (isWidgetBlockEditor || isWpCustomizer) {
          if (props.attributes.extended_widget_opts_block == undefined) {
            props.attributes.extended_widget_opts_block = {};
          }

          if (isWpCustomizer) {
            if (props.attributes.extended_widget_opts == undefined) {
              props.attributes.extended_widget_opts = {};
            }
          }
        } else {
          if (props.attributes.extended_widget_opts == undefined) {
            props.attributes.extended_widget_opts = {};
          }
        }
      }

      if (props.attributes.extended_widget_opts_state == undefined) {
        props.attributes.extended_widget_opts_state = 0;
      }

      if (props.attributes.extended_widget_opts_clientid == undefined) {
        props.attributes.extended_widget_opts_clientid = "";
      }

      if (props.attributes.dateUpdated == undefined) {
        props.attributes.dateUpdated = "";
      }

      try {
        // Now, call the original render callback with the modified props
        return originalRenderCallback(props);
      } catch (error) {
        return new originalRenderCallback(props);
      }
    };

    if (name == "luckywp/tableofcontents") {
      //if block type is luckywp/tableofcontents use type string
      settings.attributes.extended_widget_opts_block = {
        type: "object",
        default: JSON.stringify({}),
      };
      settings.attributes.extended_widget_opts = isWidgetBlockEditor
        ? {
            type: "object",
          }
        : {
            type: "object",
            default: JSON.stringify({}),
          };
    } else {
      //if block type is not luckywp/tableofcontents use type object
      settings.attributes.extended_widget_opts_block = {
        type: "object",
        default: {},
      };
      settings.attributes.extended_widget_opts = isWidgetBlockEditor
        ? {
            type: "object",
          }
        : {
            type: "object",
            default: {},
          };
    }

    settings.attributes.extended_widget_opts_state = {
      type: "string",
      default: "",
    };

    settings.attributes.extended_widget_opts_clientid = {
      type: "string",
      default: "",
    };

    settings.attributes.dateUpdated = {
      type: "string",
      default: "",
    };
  }
  return settings;
}

wp.domReady(function () {
  wp.hooks.addFilter(
    "blocks.registerBlockType",
    "extended-widget-options/sidebar-component",
    addWidgetOptionAttributes
  );
});
