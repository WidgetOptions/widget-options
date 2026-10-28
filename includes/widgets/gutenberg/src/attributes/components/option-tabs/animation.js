import { __ } from "@wordpress/i18n";
import { TabPanel } from "@wordpress/components";
import React from "react";

const onSelect = (tabName) => {};

const AnimationTabPanel = (props) => {
  const validLicense = true;
  let validLicenseClass = props.validLicense ? "" : "disabled-section";
  let widget_options = { ...props.widgetopts_get_settings };
  let selected = "";
  let options_values = "";
  let desktop_clear = "";
  let tablet_clear = "";
  let types = [];
  let options_role = "";
  let roles = [];
  let miscs = [];
  let key = "";
  let value = "";
  let misc_values = [];
  let taxonomies = [];
  let terms_values = [];
  let taxLoop = [];
  let tax_values = [];
  let args = [];
  let authors = [];
  let options_author_pages = 0;
  let fields = [];
  let acf_values = "";
  let pages_values;
  const liMisc = React.useRef(null);
  const liClass = React.useRef(null);
  const liAnimation = React.useRef(null);
  const liLogic = React.useRef(null);
  let check = "";
  let fixed = "";
  let link = "";
  let target = "";
  let nofollow = "";
  let is_url = "";
  let urls = "";
  let id = "";
  let classes_data = "";
  let predefined = [];
  let animation = "";
  let event = "";
  let speed = "";
  let offset = "";
  let hidden = "";
  let link_title = "";
  let http = "";
  let nocache = "";
  let logic = "";

  let _animation_array = {
    "Attention Seekers": [
      "bounce",
      "flash",
      "pulse",
      "rubberBand",
      "shake",
      "swing",
      "tada",
      "wobble",
      "jello",
    ],
    "Bouncing Entrances": [
      "bounceIn",
      "bounceInDown",
      "bounceInLeft",
      "bounceInRight",
      "bounceInUp",
    ],

    "Fading Entrances": [
      "fadeIn",
      "fadeInDown",
      "fadeInDownBig",
      "fadeInLeft",
      "fadeInLeftBig",
      "fadeInRight",
      "fadeInRightBig",
      "fadeInUp",
      "fadeInUpBig",
    ],
    Flippers: ["flip", "flipInX", "flipInY", "flipOutX", "flipOutY"],
    Lightspeed: ["lightSpeedIn", "lightSpeedOut"],

    "Rotating Entrances": [
      "rotateIn",
      "rotateInDownLeft",
      "rotateInDownRight",
      "rotateInUpLeft",
      "rotateInUpRight",
    ],
    "Sliding Entrances": [
      "slideInUp",
      "slideInDown",
      "slideInLeft",
      "slideInRight",
    ],
    "Zoom Entrances": [
      "zoomIn",
      "zoomInDown",
      "zoomInLeft",
      "zoomInRight",
      "zoomInUp",
    ],
    Specials: ["hinge", "rollIn"],
  };

  let animation_array = [...Object.entries(_animation_array)];

  const checkLicense = (isLicenseValid) => {
    if (isLicenseValid) {
      return "";
    } else {
      return (
        <div className="extended-widget-opts-demo-warning">
          <p className="widgetopts-unlock-features">
            <span className="dashicons dashicons-lock"></span>
            <br />
            Unlock all Features
            <br />
            <a
              href="https://widget-options.com/?utm_source=wordpressadmin&utm_medium=widgettabs&utm_campaign=widgetoptsprotab"
              className="button-primary"
              target="_blank"
            >
              Learn More
            </a>
          </p>
        </div>
      );
    }
  };

  const checkSelectedRoles = (rolesSelected, role, roleName, roleInfo) => {
    if (
      (rolesSelected != undefined &&
        rolesSelected != null &&
        rolesSelected != "") ||
      (role[roleName] != undefined &&
        role[roleName] != "" &&
        role[roleName] != null)
    ) {
      return (
        <option value={roleName} selected="selected">
          {roleInfo["name"]}
        </option>
      );
    } else {
      return "";
    }
  };

  const clickTabs = (el, tabName) => {
    Array.from(
      document.querySelectorAll(".extended-widget-opts-settings-tab-settings")
    ).forEach((ele) => ele.classList.remove("ui-tabs-active"));

    Array.from(
      document.querySelectorAll(
        ".extended-widget-opts-inside-tabs .extended-widget-opts-settings-tabcontent-2"
      )
    ).forEach((ele) => (ele.style.display = "none"));

    document.getElementById(
      "extended-widget-opts-settings-tab-" + props.widgetId + "-" + tabName
    ).style.display = "block";

    el.current.classList.add("ui-tabs-active");
  };

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-class"}
      className="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-class"
      style={{ "margin-left": "16px" }}
    >
      <div className="extended-widget-opts-settings-tabs extended-widget-opts-inside-tabs">
        <input
          type="hidden"
          id="extended-widget-opts-styling-selectedtab"
          value={selected}
          name={"extended_widget_opts[class][selected]"}
        />

        <div className="extended-widget-opts-clearfix"></div>

        <div
          id={
            "extended-widget-opts-settings-tab-" + props.widgetId + "-animation"
          }
          class={
            "extended-widget-opts-settings-tabcontent-2 extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent " +
            validLicenseClass
          }
        >
          {checkLicense(props.validLicense)}

          <div class="widget-opts-animation">
            <p style={{ "margin-top": "5px" }}>
              <label for={"opts-class-animation-" + props.widgetId}>
                {__("Animation Type")}
              </label>
              <br />
              <select
                class="widefat"
                id={"opts-class-animation-" + props.widgetId}
                name={"extended_widget_opts[class][animation]"}
                value={""}
              >
                <option value="">{__("None")}</option>
              </select>
              <small>
                <em>{__("The type of animation for this event.")}</em>
              </small>
            </p>

            <p>
              <label for={"opts-class-event-" + props.widgetId}>
                {__("Animation Event")}
              </label>
              <br />
              <select
                class="widefat"
                id={"opts-class-event-" + props.widgetId}
                name={"extended_widget_opts[class][event]"}
                value={""}
              >
                <option value="enters">{__("Element Enters Screen")}</option>
                <option value="onScreen">{__("Element In Screen")}</option>
                <option value="pageLoad">{__("Page Load")}</option>
              </select>
              <small>
                <em>{__("The event that triggers the animation")}</em>
              </small>
            </p>

            <p>
              <label for={"opts-class-speed-" + props.widgetId}>
                {__("Animation Speed")}
              </label>
              <br />
              <input
                type="text"
                id={"opts-class-speed-" + props.widgetId}
                class="widefat"
                name={"extended_widget_opts[class][speed]"}
                value={""}
              />
              <small>
                <em>
                  {__("How many seconds the incoming animation should lasts.")}
                </em>
              </small>
            </p>

            <p>
              <label for={"opts-class-offset-" + props.widgetId}>
                {__("Screen Offset")}
              </label>
              <br />
              <input
                type="text"
                id={"opts-class-offset-" + props.widgetId}
                class="widefat"
                name={"extended_widget_opts[class][offset]"}
                value={""}
              />
              <small>
                <em>
                  {__(
                    "How many pixels above the bottom of the screen must the widget be before animating."
                  )}
                </em>
              </small>
            </p>

            <p>
              <label for={"opts-class-hidden-" + props.widgetId}>
                {__("Hide Before Animation")}
              </label>
              <br />
              <input
                type="checkbox"
                name={"extended_widget_opts[class][hidden]"}
                id={"opts-class-hidden-" + props.widgetId}
                value="1"
              />
              <label for={"opts-class-hidden-" + props.widgetId}>
                {__("Enabled")}
              </label>
              <br />
              <small>
                <em>{__("Hide widget before animating.")}</em>
              </small>
            </p>

            <p>
              <label for={"opts-class-delay-" + props.widgetId}>
                {__("Animation Delay")}
              </label>
              <br />
              <input
                type="text"
                id={"opts-class-delay-" + props.widgetId}
                class="widefat"
                name={"extended_widget_opts[class][delay]"}
                value={""}
              />
              <small>
                <em>
                  {__(
                    "Number of seconds after the event to start the animation."
                  )}
                </em>
              </small>
            </p>
          </div>
        </div>
      </div>

      <style>
        {`
        .extended-widget-opts-inside-tabs .ui-tabs-nav li.extended-widget-opts-settings-tab-settings a {
            padding: 6px 4px !important;
        }
        .extended-widget-opts-tabs .ui-tabs-nav li.extended-widget-opts-settings-tab-settings.ui-tabs-active a {
            padding-bottom: 9px !important;
        }
        `}
      </style>
    </div>
  );
};

export default AnimationTabPanel;
