import { __ } from "@wordpress/i18n";
import { TabPanel } from "@wordpress/components";
import React from "react";

const { PanelBody } = wp.components;

const onSelect = (tabName) => {};

const BehaviorTabPanel = (props) => {
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

  const handleInputChange = (event, key1, key2) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute[key1] == undefined ||
      (_attribute[key1] != undefined && _attribute[key1].length == 0)
    ) {
      _attribute[key1] = {};
    }

    _attribute[key1][key2] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeCheckbox = (event, key1, key2) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute[key1] == undefined ||
      (_attribute[key1] != undefined && _attribute[key1].length == 0)
    ) {
      _attribute[key1] = {};
    }

    if (event.target.checked) {
      _attribute[key1][key2] = 1;
    } else {
      delete _attribute[key1][key2];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

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
      style={{ width: "100%" }}
    >
      <div className="extended-widget-opts-settings-tabs extended-widget-opts-inside-tabs">
        <input
          type="hidden"
          id="extended-widget-opts-styling-selectedtab"
          value={selected}
          name={"extended_widget_opts[class][selected]"}
        />

        <div className="extended-widget-opts-clearfix"></div>

        {props.editor == "widget" ? (
          <>
            <PanelBody
              title={__("Fixed Widget")}
              className="margin-x-minus-16"
              initialOpen={true}
            >
              <div
                id={
                  "extended-widget-opts-settings-tab-" +
                  props.widgetId +
                  "-title"
                }
                className="extended-widget-opts-settings-tabcontent-2 extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent"
                style={{ display: "block" }}
              >
                <div className="widget-opts-title">
                  <div
                    class={
                      "widgetopts-widget-opts-wrapper " + validLicenseClass
                    }
                  >
                    {checkLicense(props.validLicense)}

                    <div class="widgetopts-fixed-widget-opts">
                      <p class="widgetopts-subtitle">{__("Fixed Widget")}</p>
                      <p>
                        <input
                          type="checkbox"
                          name={"extended_widget_opts[class][fixed]"}
                          id={"opts-class-fixed-" + props.widgetId}
                          value="1"
                        />
                        <label for={"opts-class-fixed-" + props.widgetId}>
                          {__("Check to fixed widget on scroll")}
                        </label>
                      </p>
                    </div>

                    <div class="widgetopts-cache-widget-opts">
                      <p class="widgetopts-subtitle">{__("Widget Cache")}</p>
                      <p>
                        <input
                          type="checkbox"
                          name={"extended_widget_opts[class][nocache]"}
                          id={"opts-class-cache-" + props.widgetId}
                          value="1"
                        />
                        <label for={"opts-class-cache-" + props.widgetId}>
                          {__("Do not cache this widget")}
                        </label>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </PanelBody>

            <PanelBody
              title={__("Link Widget")}
              className="margin-x-minus-16"
              initialOpen={false}
            >
              <div
                id={
                  "extended-widget-opts-settings-tab-" +
                  props.widgetId +
                  "-title"
                }
                className="extended-widget-opts-settings-tabcontent-2 extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent"
                style={{ display: "block" }}
              >
                <div className="widget-opts-title">
                  <div
                    class={
                      "widgetopts-widget-opts-wrapper " + validLicenseClass
                    }
                  >
                    {checkLicense(props.validLicense)}

                    <div class="widgetopts-links-widget-opts">
                      <p class="widgetopts-subtitle">{__("Link Widget")}</p>
                      <table class="form-table">
                        <tbody>
                          <tr valign="top">
                            <td scope="row" style={{ padding: "15px 3px" }}>
                              <strong>{__("Link:")}</strong>
                            </td>
                            <td style={{ padding: "15px 3px" }}>
                              <input
                                type="text"
                                class="widefat"
                                name={"extended_widget_opts[class][link]"}
                                value={""}
                              />
                            </td>
                          </tr>
                          <tr valign="top">
                            <td scope="row" style={{ padding: "15px 3px" }}>
                              &nbsp;
                            </td>
                            <td style={{ padding: "15px 3px" }}>
                              <input
                                type="checkbox"
                                id={"opts-class-target-" + props.widgetId}
                                name={"extended_widget_opts[class][target]"}
                                value="1"
                              />
                              <label
                                class="opts-label-small"
                                for={"opts-class-target-" + props.widgetId}
                              >
                                {__("Open to new tab")}
                              </label>
                            </td>
                          </tr>
                          <tr valign="top">
                            <td scope="row" style={{ padding: "15px 3px" }}>
                              &nbsp;
                            </td>
                            <td style={{ padding: "15px 3px" }}>
                              <input
                                type="checkbox"
                                id={"opts-class-nofollow-" + props.widgetId}
                                name={"extended_widget_opts[class][nofollow]"}
                                value="1"
                              />
                              <label
                                class="opts-label-small"
                                for={"opts-class-nofollow-" + props.widgetId}
                              >
                                {__('rel="nofollow"')}
                              </label>
                            </td>
                          </tr>
                          <tr valign="top">
                            <td scope="row" style={{ padding: "15px 3px" }}>
                              &nbsp;
                            </td>
                            <td style={{ padding: "15px 3px" }}>
                              <input
                                type="checkbox"
                                id={"opts-class-totitle-" + props.widgetId}
                                name={"extended_widget_opts[class][link_title]"}
                                value="1"
                              />
                              <label
                                class="opts-label-small"
                                for={"opts-class-totitle-" + props.widgetId}
                              >
                                {__("Apply to title only")}
                              </label>
                            </td>
                          </tr>
                          <tr valign="top">
                            <td scope="row" style={{ padding: "15px 3px" }}>
                              &nbsp;
                            </td>
                            <td style={{ padding: "15px 3px" }}>
                              <input
                                type="checkbox"
                                id={"opts-class-http-" + props.widgetId}
                                name={"extended_widget_opts[class][http]"}
                                value="1"
                              />
                              <label
                                class="opts-label-small"
                                for={"opts-class-http-" + props.widgetId}
                              >
                                {__("Do not add http")}
                              </label>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </PanelBody>
          </>
        ) : (
          ""
        )}

        {"activate" == widget_options["classes"] ? (
          <PanelBody
            title={__("Class & ID")}
            className="margin-x-minus-16"
            initialOpen={false}
          >
            <div
              id={
                "extended-widget-opts-settings-tab-" + props.widgetId + "-class"
              }
              class="extended-widget-opts-settings-tabcontent-2 extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent"
            >
              <div class="widget-opts-class">
                <table class="form-table">
                  <tbody>
                    {widget_options["settings"]["classes"] != undefined &&
                    widget_options["settings"]["classes"]["id"] != undefined &&
                    "1" == widget_options["settings"]["classes"]["id"] ? (
                      <tr valign="top" class="widgetopts_id_fld">
                        <td scope="row">
                          <strong>{__("Widget CSS ID:")}</strong>
                          <br />
                          <input
                            type="text"
                            id={"opts-class-id-" + props.widgetId}
                            class="widefat"
                            name={"extended_widget_opts[class][id]"}
                            onChange={(event) =>
                              handleInputChange(event, "class", "id")
                            }
                            value={
                              props.extended_widget_opts["class"] != undefined
                                ? props.extended_widget_opts["class"]["id"]
                                : ""
                            }
                          />
                        </td>
                      </tr>
                    ) : (
                      ""
                    )}

                    {widget_options["settings"]["classes"] != undefined ||
                    (widget_options["settings"]["classes"] != undefined &&
                      widget_options["settings"]["classes"]["type"] !=
                        undefined &&
                      widget_options["settings"]["classes"]["type"] != "hide" &&
                      widget_options["settings"]["classes"]["type"] !=
                        "predefined") ? (
                      <tr valign="top">
                        <td scope="row">
                          <strong>{__("Widget CSS Classes:")}</strong>
                          <br />
                          <input
                            type="text"
                            id={"opts-class-classes-" + props.widgetId}
                            class="widefat"
                            name={"extended_widget_opts[class][classes]"}
                            onChange={(event) =>
                              handleInputChange(event, "class", "classes")
                            }
                            value={
                              props.extended_widget_opts["class"] != undefined
                                ? props.extended_widget_opts["class"]["classes"]
                                : ""
                            }
                          />
                          <small>
                            <em>{__("Separate each class with space.")}</em>
                          </small>
                        </td>
                      </tr>
                    ) : (
                      ""
                    )}
                    {widget_options["settings"]["classes"] != undefined ||
                    (widget_options["settings"]["classes"] != undefined &&
                      widget_options["settings"]["classes"]["type"] !=
                        undefined &&
                      widget_options["settings"]["classes"]["type"] != "hide" &&
                      widget_options["settings"]["classes"]["type"] != "text")
                      ? predefined != undefined &&
                        Array.isArray(predefined) &&
                        predefined.length > 0
                        ? (function (_predefined, _args) {
                            let predef = _predefined.filter(
                              (value, index, array) =>
                                array.indexOf(value) === index
                            );
                            return (
                              <tr valign="top">
                                <td scope="row">
                                  <strong>
                                    {__("Available Widget Classes:")}
                                  </strong>
                                  <br />
                                  <div
                                    class="extended-widget-opts-class-lists"
                                    style={{
                                      maxHeight: "230px",
                                      padding: "5px",
                                      overflow: "auto",
                                    }}
                                  >
                                    {predef.map(function (value, key) {
                                      let checked = "";
                                      if (
                                        _args["params"]["class"][
                                          "predefined"
                                        ] != undefined &&
                                        Array.isArray(
                                          _args["params"]["class"]["predefined"]
                                        ) &&
                                        _args["params"]["class"][
                                          "predefined"
                                        ].includes(value)
                                      ) {
                                        checked = 'checked="checked"';
                                      }

                                      return (
                                        <p>
                                          <input
                                            type="checkbox"
                                            name={
                                              "extended_widget_opts[class][predefined][]"
                                            }
                                            id={
                                              props.widgetId +
                                              "-opts-class-" +
                                              key
                                            }
                                            // onChange={(event) =>
                                            //   handleInputChange(event, "class", "predefined")
                                            // }
                                            // value={
                                            //   props.extended_widget_opts["class"] != undefined
                                            //     ? props.extended_widget_opts["class"]["classes"]
                                            //     : ""
                                            // }
                                          />
                                          <label
                                            for={
                                              props.widgetId +
                                              "-opts-class-" +
                                              key
                                            }
                                          >
                                            {value}
                                          </label>
                                        </p>
                                      );
                                    })}
                                  </div>
                                </td>
                              </tr>
                            );
                          })(predefined, args)
                        : ""
                      : ""}
                  </tbody>
                </table>
              </div>
            </div>
          </PanelBody>
        ) : (
          ""
        )}
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

export default BehaviorTabPanel;
