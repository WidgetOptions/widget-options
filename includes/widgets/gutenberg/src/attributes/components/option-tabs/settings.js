import { __ } from "@wordpress/i18n";
import { TabPanel, ComboboxControl } from "@wordpress/components";
import React, { useState, useMemo, useEffect, useCallback } from "react";

const { PanelBody } = wp.components;

const onSelect = (tabName) => {};

const SettingsTabPanel = (props) => {
  const validLicense = true;
  
  // Dynamic snippets state
  const [snippets, setSnippets] = useState(props.widgetopts_snippets || []);
  const [snippetsLoaded, setSnippetsLoaded] = useState(false);
  
  // Refresh snippets from server
  const refreshSnippets = useCallback(() => {
    if (typeof jQuery !== 'undefined' && typeof ajaxurl !== 'undefined') {
      jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: { action: 'widgetopts_get_snippets_ajax' },
        success: function(response) {
          if (response.success && response.data && response.data.snippets) {
            setSnippets(response.data.snippets);
            setSnippetsLoaded(true);
          }
        }
      });
    }
  }, []);

  // Auto-refresh snippets when component mounts
  useEffect(() => {
    refreshSnippets();
  }, [refreshSnippets]);
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
  let fields = props.widgetopts_acf_get_field_groups
    ? props.widgetopts_acf_get_field_groups
    : [];
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

  const handleInputChangeVisibilityACFVisbility = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["acf"] == undefined ||
      (_attribute["visibility"]["acf"] != undefined &&
        _attribute["visibility"]["acf"].length == 0)
    ) {
      _attribute["visibility"]["acf"] = {};
    }

    _attribute["visibility"]["acf"]["visibility"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeVisibilityACFField = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["acf"] == undefined ||
      (_attribute["visibility"]["acf"] != undefined &&
        _attribute["visibility"]["acf"].length == 0)
    ) {
      _attribute["visibility"]["acf"] = {};
    }

    _attribute["visibility"]["acf"]["field"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeVisibilityACFCondition = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["acf"] == undefined ||
      (_attribute["visibility"]["acf"] != undefined &&
        _attribute["visibility"]["acf"].length == 0)
    ) {
      _attribute["visibility"]["acf"] = {};
    }

    _attribute["visibility"]["acf"]["condition"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeVisibilityACFConditionalValue = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["acf"] == undefined ||
      (_attribute["visibility"]["acf"] != undefined &&
        _attribute["visibility"]["acf"].length == 0)
    ) {
      _attribute["visibility"]["acf"] = {};
    }

    _attribute["visibility"]["acf"]["value"] = event.target.value;
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

        {/* <ul
          id={"settings-tabs-" + props.widgetId}
          className="extended-widget-opts-settings-tabnav-ul ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header"
        >
          {widget_options["logic"] == "activate" ? (
            <li
              ref={liLogic}
              className="extended-widget-opts-settings-tab-settings ui-tabs-active"
              onClick={() => clickTabs(liLogic, "logic")}
            >
              <a
                href={
                  "#extended-widget-opts-settings-tab-" +
                  props.widgetId +
                  "-logic"
                }
                title={__("Display Logic")}
              >
                {__("Logic")}
              </a>
            </li>
          ) : (
            ""
          )}

          {widget_options["acf"] == "activate" && props.editor == "widget" ? (
            <li
              ref={liAnimation}
              className="extended-widget-opts-settings-tab-settings"
              onClick={() => clickTabs(liAnimation, "acf")}
            >
              <a
                href={
                  "#extended-widget-opts-settings-tab-" +
                  props.widgetId +
                  "-acf"
                }
                title={__("ACF")}
              >
                {__("ACF")}
              </a>
            </li>
          ) : (
            ""
          )}
          <div className="extended-widget-opts-clearfix"></div>
        </ul> */}
        <div className="extended-widget-opts-clearfix"></div>

        {"activate" == widget_options["logic"] ? (
          <PanelBody
            title={__("Display Logic")}
            className="margin-x-minus-16"
            initialOpen={true}
          >
            <div
              id={
                "extended-widget-opts-settings-tab-" + props.widgetId + "-logic"
              }
              class="extended-widget-opts-settings-tabcontent-2 extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent"
            >
              <div class="widget-opts-logic" onClick={() => refreshSnippets()}>

                {/* Show Legacy Logic section OR Snippet dropdown — mutually exclusive */}
                {props.extended_widget_opts["class"] != undefined &&
                 props.extended_widget_opts["class"]["logic"] != undefined &&
                 props.extended_widget_opts["class"]["logic"] != "" &&
                 (!props.extended_widget_opts["class"]["logic_snippet_id"] || props.extended_widget_opts["class"]["logic_snippet_id"] == "") ? (
                  <div style={{ marginTop: "10px", marginBottom: "10px" }}>
                    <p style={{ margin: "0 0 5px" }}>
                      <strong>{__("Legacy Display Logic Code")}</strong>
                    </p>
                    <textarea
                      readOnly
                      rows={4}
                      value={props.extended_widget_opts["class"]["logic"]}
                      style={{
                        width: "100%",
                        background: "#f9f2f4",
                        color: "#c7254e",
                        fontFamily: "monospace",
                        fontSize: "12px",
                        cursor: "not-allowed",
                        resize: "vertical",
                      }}
                      onClick={(e) => e.target.select()}
                    />
                    <input
                      type="hidden"
                      name={"extended_widget_opts[class][logic]"}
                      value={props.extended_widget_opts["class"]["logic"]}
                    />
                    <p style={{
                      marginTop: "8px",
                      padding: "8px 12px",
                      background: "#fff3cd",
                      borderLeft: "4px solid #ffc107",
                      color: "#856404",
                      fontSize: "12px",
                    }}>
                      {__("This block uses legacy inline display logic that needs migration.")}
                      {props.widgetopts_can_manage_snippets ? (
                        <span>
                          <br />
                          <a
                            href={(props.widgetopts_migration_url || "/wp-admin/options-general.php?page=widgetopts_migration")}
                            target="_blank"
                            rel="noopener noreferrer"
                          >
                            {__("Go to Migration Page")} &rarr;
                          </a>
                        </span>
                      ) : ""}
                    </p>
                    <button
                      type="button"
                      style={{
                        marginTop: "8px",
                        padding: "5px 15px",
                        background: "#dc3545",
                        color: "#fff",
                        border: "none",
                        borderRadius: "3px",
                        cursor: "pointer",
                        fontSize: "12px",
                      }}
                      onClick={() => {
                        var opts = { ...props.extended_widget_opts };
                        if (opts["class"] == undefined || (opts["class"] != undefined && opts["class"].length == 0)) {
                          opts["class"] = {};
                        }
                        opts["class"]["logic"] = "";
                        opts["class"]["logic_cleared"] = "1";
                        props.onUpdateDynamicAttribute(opts, props.widgetId);
                      }}
                    >
                      {__("Clear Legacy Logic")}
                    </button>
                  </div>
                ) : (
                  <div>
                    <p style={{ "margin-top": "10px", "margin-bottom": "0px" }}>
                      <small>
                        {__("Select a Display Logic Snippet to control when this block is displayed.")}
                      </small>
                    </p>
                    <ComboboxControl
                      label={__("Select Snippet")}
                      hideLabelFromVision={true}
                      value={
                        props.extended_widget_opts["class"] != undefined &&
                        props.extended_widget_opts["class"]["logic_snippet_id"] != undefined
                          ? String(props.extended_widget_opts["class"]["logic_snippet_id"])
                          : ""
                      }
                      options={[
                        { value: "", label: __("— No Logic (Always Show) —") },
                        ...(snippets || []).map((snippet) => ({
                          value: String(snippet.id),
                          label: snippet.title
                        }))
                      ]}
                      onChange={(value) => {
                        const syntheticEvent = {
                          target: { value: value || "" }
                        };
                        handleInputChange(syntheticEvent, "class", "logic_snippet_id");
                      }}
                      __experimentalRenderItem={({ item }) => (
                        <span>{item.label}</span>
                      )}
                    />

                    {snippets && snippets.length > 0 &&
                      props.extended_widget_opts["class"] != undefined &&
                      props.extended_widget_opts["class"]["logic_snippet_id"] != undefined &&
                      props.extended_widget_opts["class"]["logic_snippet_id"] != "" ? (
                      <p style={{ "margin-top": "5px" }}>
                        <small>
                          {snippets.find(s => s.id == props.extended_widget_opts["class"]["logic_snippet_id"])?.description || ""}
                        </small>
                      </p>
                    ) : (
                      ""
                    )}
                  </div>
                )}

                {props.widgetopts_can_manage_snippets ? (
                  <p style={{ "margin-top": "15px" }}>
                    <a 
                      href={props.widgetopts_snippets_url || "/wp-admin/edit.php?post_type=widgetopts_snippet"} 
                      target="_blank"
                      className="button button-small"
                      style={{ textDecoration: "none" }}
                    >
                      {__("Manage Logic Snippets")}
                    </a>
                  </p>
                ) : (
                  ""
                )}
              </div>
            </div>
          </PanelBody>
        ) : (
          ""
        )}

        {widget_options["acf"] != undefined &&
        "activate" == widget_options["acf"] &&
        props.editor == "widget" ? (
          <PanelBody
            title={__("ACF")}
            className="margin-x-minus-16"
            initialOpen={false}
          >
            <div
              id={
                "extended-widget-opts-settings-tab-" + props.widgetId + "-acf"
              }
              class="extended-widget-opts-settings-tabcontent-2 extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent extended-widget-opts-tabcontent-acf"
            >
              {widget_options["acf"] != undefined &&
              "activate" == widget_options["acf"] ? (
                <div
                  id={
                    "extended-widget-opts-visibility-tab-" +
                    props.widgetId +
                    "-acf"
                  }
                  className="extended-widget-opts-visibility-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-inner-tabcontent"
                >
                  <p style={{ "margin-top": "10px" }}>
                    <strong>{__("Hide/Show")}</strong>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[visibility][acf][visibility]"}
                      onChange={handleInputChangeVisibilityACFVisbility}
                      value={
                        props.extended_widget_opts["visibility"] != undefined &&
                        props.extended_widget_opts["visibility"]["acf"] !=
                          undefined &&
                        props.extended_widget_opts["visibility"]["acf"][
                          "visibility"
                        ] != undefined
                          ? props.extended_widget_opts["visibility"]["acf"][
                              "visibility"
                            ]
                          : ""
                      }
                    >
                      <option value="hide">
                        {__("Hide when Condition's met")}
                      </option>
                      <option value="show">
                        {__("Show when Condition's met")}
                      </option>
                    </select>
                  </p>

                  <p>
                    <strong>{__("Choose ACF Field")}</strong>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[visibility][acf][field]"}
                      onChange={handleInputChangeVisibilityACFField}
                      value={
                        props.extended_widget_opts["visibility"] != undefined &&
                        props.extended_widget_opts["visibility"]["acf"] !=
                          undefined &&
                        props.extended_widget_opts["visibility"]["acf"][
                          "field"
                        ] != undefined
                          ? props.extended_widget_opts["visibility"]["acf"][
                              "field"
                            ]
                          : ""
                      }
                    >
                      <option value="">
                        {__("Select Field", "widget-options")}
                      </option>

                      {(function () {
                        let keys = Object.keys(fields);
                        return keys.map(function (field, i) {
                          return (
                            <optgroup label={fields[field]["title"]}>
                              {fields[field]["fields"].map(function (f) {
                                return (
                                  <option value={f["key"]}>{f["label"]}</option>
                                );
                              })}
                            </optgroup>
                          );
                        });
                      })()}
                    </select>
                  </p>
                  <p>
                    <strong>{__("Condition")}</strong>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[visibility][acf][condition]"}
                      onChange={handleInputChangeVisibilityACFCondition}
                      value={
                        props.extended_widget_opts["visibility"] != undefined &&
                        props.extended_widget_opts["visibility"]["acf"] !=
                          undefined &&
                        props.extended_widget_opts["visibility"]["acf"][
                          "condition"
                        ] != undefined
                          ? props.extended_widget_opts["visibility"]["acf"][
                              "condition"
                            ]
                          : ""
                      }
                    >
                      <option value="">{__("Select Condition")}</option>
                      <optgroup label={__("Conditional")}>
                        <option value="equal">{__("Is Equal to")}</option>
                        <option value="not_equal">
                          {__("Is Not Equal to")}
                        </option>
                        <option value="contains">{__("Contains")}</option>
                        <option value="not_contains">
                          {__("Does Not Contain")}
                        </option>
                      </optgroup>
                      <optgroup label={__("Value Based")}>
                        <option value="empty">{__("Is Empty")}</option>
                        <option value="not_empty">{__("Is Not Empty")}</option>
                      </optgroup>
                    </select>
                  </p>
                  <p>
                    <strong>{__("Conditional Value")}</strong>
                    <textarea
                      name={"extended_widget_opts[visibility][acf][value]"}
                      id={props.widgetId + "-opts-acf-value"}
                      className="widefat widgetopts-acf-conditional"
                      onChange={handleInputChangeVisibilityACFConditionalValue}
                      value={
                        props.extended_widget_opts["visibility"] != undefined &&
                        props.extended_widget_opts["visibility"]["acf"] !=
                          undefined &&
                        props.extended_widget_opts["visibility"]["acf"][
                          "value"
                        ] != undefined
                          ? props.extended_widget_opts["visibility"]["acf"][
                              "value"
                            ]
                          : ""
                      }
                    ></textarea>
                  </p>
                </div>
              ) : (
                ""
              )}
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

export default SettingsTabPanel;
