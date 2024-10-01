const { __ } = wp.i18n;

import { Panel, PanelBody, PanelRow } from "@wordpress/components";
import WidgetOptionsPanel from "./widgetopts-panel";

const onSelect = (tabName, props) => {};

const WidgetOptionsTab = (props) => {
  let widget_options = { ...props.widgetopts_get_settings };
  let is_customizer = document.body.classList.contains("wp-customizer");

  var _tabs = [
    {
      name: "visibility",
      title: "Page Visibility",
      className: "tab-visibility",
      icon: "visibility",
      active:
        widget_options["visibility"] == "activate" && props.editor == "widget",
    },
    {
      name: "columns",
      title: "Columns",
      className: "tab-columns",
      icon: "grid-view",
      active: props.editor == "widget",
    },
    {
      name: "alignment",
      title: "Alignment",
      className: "tab-alignment",
      icon: "editor-aligncenter",
      active:
        widget_options["alignment"] == "activate" &&
        (props.editor == "post" || is_customizer), //will be remove in gutenberg
    },
    {
      name: "role",
      title: "Roles",
      className: "tab-role",
      icon: "admin-users",
      active: widget_options["roles"] == "activate",
    },
    {
      name: "devices",
      title: "Devices",
      className: "tab-devices",
      icon: "smartphone",
      active: widget_options["devices"] == "activate",
    },
    {
      name: "dates",
      title: "Days & Dates",
      className: "tab-dates",
      icon: "calendar-alt",
      active: true, //widget_options["dates"] == "activate",
    },
    {
      name: "styling",
      title: "Styling",
      className: "tab-styling",
      icon: "art",
      active: true && props.editor == "widget", //widget_options["styling"] == "activate" && props.editor == "widget",
    },
    {
      name: "behavior",
      title: "Behavior",
      className: "tab-behavior",
      icon: "admin-generic",
      active: widget_options["classes"] == "activate",
    },
    {
      name: "logic",
      title: props.editor == "widget" ? "Logic & ACF" : "Logic",
      className: "tab-logic",
      icon: "code-standards",
      active:
        widget_options["logic"] == "activate" ||
        (props.editor == "widget" && widget_options["acf"] == "activate"),
    },
    {
      name: "animation",
      title: "Animation",
      className: "tab-animation",
      icon: "admin-customizer",
      active: widget_options["animation"] == "activate",
    },
  ];

  var tabs = _tabs.filter(function (value, index) {
    if (value.active) {
      return true;
    }
    return false;
  });

  return tabs.map(function (tab, index) {
    return (
      <Panel
        className={
          "widgetopts-tab-panel extended-widget-opts-tabs ui-tabs ui-corner-all ui-widget ui-widget-content " +
          tab.className +
          ""
        }
        activeClass="active-tab"
        onSelect={(tabName) => onSelect(tabName, props)}
      >
        <PanelBody
          title={__(tab.title)}
          initialOpen={false}
          icon={tab.icon}
          scrollAfterOpen="true"
        >
          <PanelRow>
            <WidgetOptionsPanel
              tabName={tab.name}
              {...props}
              is_customizer={is_customizer}
            />
          </PanelRow>
        </PanelBody>
      </Panel>
    );
  });
};

export default WidgetOptionsTab;
