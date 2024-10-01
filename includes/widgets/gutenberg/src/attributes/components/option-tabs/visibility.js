import { __ } from "@wordpress/i18n";
import { TabPanel } from "@wordpress/components";
import React from "react";

const { PanelBody } = wp.components;

const onSelect = (tabName) => {};

const VisibilityTabPanel = (props) => {
  const validLicense = true;
  let validLicenseClass = props.validLicense ? "" : "disabled-section";
  let widget_options = { ...props.widgetopts_get_settings };
  let selected = "";
  let options_values = "";
  let desktop_clear = "";
  let tablet_clear = "";
  let types = props.widgetopts_types;
  let options_role = "";
  let roles = [];
  let miscs = [
    { key: "home", value: __("Home/Front", "widget-options") },
    { key: "blog", value: __("Blog", "widget-options") },
    { key: "archives", value: __("Archives", "widget-options") },
    { key: "single", value: __("Single Post", "widget-options") },
    { key: "404", value: __("404", "widget-options") },
    { key: "search", value: __("Search", "widget-options") },
  ];

  let key = "";
  let value = "";
  let misc_values = [];
  let taxonomies = props.widgetopts_taxonomies;
  let terms_values = [];
  let taxLoop = props.widgetopts_terms;
  let tax_values = [];
  let args = [];
  let authors = props.widgetopts_users;
  let options_author_pages = 0;
  let fields = props.widgetopts_acf_get_field_groups;
  let acf_values = "";
  let pages_values;
  const liMisc = React.useRef(null);
  const liPostType = React.useRef(null);
  const liTaxonomies = React.useRef(null);
  const liAuthor = React.useRef(null);

  let widget_option_local_copy = [];

  //because the settings was removed, we need to add the keys manually
  if (taxonomies != undefined) {
    Object.keys(taxonomies).map(function (tax, i) {
      if (taxonomies[tax].public === true) {
        widget_option_local_copy[taxonomies[tax].name] = 1;
      }
    });
  }

  /* hide/show visibility options event handler*/
  const handleInputChangeVisibilityOption = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    _attribute["visibility"]["options"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeVisibilityMisc = (event, key) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["misc"] == undefined ||
      (_attribute["visibility"]["misc"] != undefined &&
        _attribute["visibility"]["misc"].length == 0)
    ) {
      _attribute["visibility"]["misc"] = {};
    }

    if (event.target.checked) {
      _attribute["visibility"]["misc"][key] = 1;
    } else {
      delete _attribute["visibility"]["misc"][key];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeVisibilityMiscSlug = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["misc"] == undefined ||
      (_attribute["visibility"]["misc"] != undefined &&
        _attribute["visibility"]["misc"].length == 0)
    ) {
      _attribute["visibility"]["misc"] = {};
    }

    _attribute["visibility"]["misc"]["slug_regex"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  /* for post type handler */

  const handleInputChangeVisibilityTypesCheckbox = (event, key) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["types"] == undefined ||
      (_attribute["visibility"]["types"] != undefined &&
        _attribute["visibility"]["types"].length == 0)
    ) {
      _attribute["visibility"]["types"] = {};
    }

    if (event.target.checked) {
      _attribute["visibility"]["types"][key] = 1;
    } else {
      delete _attribute["visibility"]["types"][key];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  /* for taxonomies handler */

  const handleInputChangeVisibilityTaxonomiesCheckbox = (event, key) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["visibility"] == undefined ||
      (_attribute["visibility"] != undefined &&
        _attribute["visibility"].length == 0)
    ) {
      _attribute["visibility"] = {};
    }

    if (
      _attribute["visibility"]["taxonomies"] == undefined ||
      (_attribute["visibility"]["taxonomies"] != undefined &&
        _attribute["visibility"]["taxonomies"].length == 0)
    ) {
      _attribute["visibility"]["taxonomies"] = {};
    }

    if (event.target.checked) {
      _attribute["visibility"]["taxonomies"][key] = 1;
    } else {
      delete _attribute["visibility"]["taxonomies"][key];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  /* for authors handler */
  const handleInputChangeVisibilityAuthors = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["author_page"] == undefined ||
      (_attribute["author_page"] != undefined &&
        _attribute["author_page"].length == 0)
    ) {
      _attribute["author_page"] = {};
    }

    if (
      _attribute["author_page"]["author_pages"] == undefined ||
      (_attribute["author_page"]["author_pages"] != undefined &&
        _attribute["author_page"]["author_pages"].length == 0)
    ) {
      _attribute["author_page"]["author_pages"] = {};
    }

    _attribute["author_page"]["author_pages"]["selections"] =
      event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeSelector2 = (event, key1, key2) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute[key1] == undefined ||
      (_attribute[key1] != undefined && _attribute[key1].length == 0)
    ) {
      _attribute[key1] = {};
    }

    if (
      _attribute[key1][key2] == undefined ||
      (_attribute[key1][key2] != undefined &&
        _attribute[key1][key2].length == 0)
    ) {
      _attribute[key1][key2] = [];
    }

    let _options = [];
    for (let i = 0; i < event.target.options.length; i++) {
      if (event.target.options[i].selected) {
        _options.push(event.target.options[i].value);
      }
    }
    _attribute[key1][key2] = [..._options];

    let _newList = _attribute[key1][key2].filter((c, index) => {
      return _attribute[key1][key2].indexOf(c) === index;
    });
    _attribute[key1][key2] = _newList;

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeSelector2_3 = (event, key1, key2, key3) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute[key1] == undefined ||
      (_attribute[key1] != undefined && _attribute[key1].length == 0)
    ) {
      _attribute[key1] = {};
    }

    if (
      _attribute[key1][key2] == undefined ||
      (_attribute[key1][key2] != undefined &&
        _attribute[key1][key2].length == 0)
    ) {
      _attribute[key1][key2] = {};
    }

    if (
      _attribute[key1][key2][key3] == undefined ||
      (_attribute[key1][key2][key3] != undefined &&
        _attribute[key1][key2][key3].length == 0)
    ) {
      _attribute[key1][key2][key3] = [];
    }

    let _options = [];
    for (let i = 0; i < event.target.options.length; i++) {
      if (event.target.options[i].selected) {
        _options.push(event.target.options[i].value);
      }
    }
    _attribute[key1][key2][key3] = [..._options];

    let _newList = _attribute[key1][key2][key3].filter((c, index) => {
      return _attribute[key1][key2][key3].indexOf(c) === index;
    });
    _attribute[key1][key2][key3] = _newList;

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeSelectorTaxonomiesPage = (event, key1, key2, key3) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute[key1] == undefined ||
      (_attribute[key1] != undefined && _attribute[key1].length == 0)
    ) {
      _attribute[key1] = {};
    }

    if (
      _attribute[key1][key2] == undefined ||
      (_attribute[key1][key2] != undefined &&
        _attribute[key1][key2].length == 0)
    ) {
      _attribute[key1][key2] = {};
    }

    if (
      _attribute[key1][key2][key3] == undefined ||
      (_attribute[key1][key2][key3] != undefined &&
        _attribute[key1][key2][key3].length == 0)
    ) {
      _attribute[key1][key2][key3] = 1;
    }

    let _options = 1;
    for (let i = 0; i < event.target.options.length; i++) {
      if (event.target.options[i].selected) {
        _options = event.target.options[i].value;
        break;
      }
    }
    _attribute[key1][key2][key3] = _options;

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChange = (event, key1, key2) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute[key1] == undefined ||
      (_attribute[key1] != undefined && _attribute[key1].length == 0)
    ) {
      _attribute[key1] = {};
    }

    if (key2 == "urls") {
      _attribute[key1][key2] = event.target.value;
    } else {
      _attribute[key1][key2] = event.target.value;
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

  const clickTabs = (el, tabName) => {
    Array.from(
      document.querySelectorAll(
        ".extended-widget-opts-visibility-tab-visibility"
      )
    ).forEach((ele) => ele.classList.remove("ui-tabs-active"));

    Array.from(
      document.querySelectorAll(
        ".extended-widget-opts-inside-tabs .extended-widget-opts-visibility-tabcontent-2"
      )
    ).forEach((ele) => (ele.style.display = "none"));

    document.getElementById(
      "extended-widget-opts-visibility-tab-" + props.widgetId + "-" + tabName
    ).style.display = "block";

    el.current.classList.add("ui-tabs-active");
  };

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-visibility"}
      className="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-visibility"
    >
      <div className="extended-widget-opts-styling-tabs extended-widget-opts-inside-tabs">
        <input
          type="hidden"
          id="extended-widget-opts-styling-selectedtab"
          value={selected}
          name={"extended_widget_opts[visibility][selected]"}
        />

        {/* <p className="widgetopts-subtitle">{__("WordPress Pages")}</p> */}
        <div
          id={"extended-widget-opts-visibility-tab-" + props.widgetId + "-main"}
          className="extended-widget-opts-visibility-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-inner-tabcontent"
        >
          <p style={{ "margin-left": "16px", "margin-top": "5px" }}>
            <strong>{__("Hide/Show")}</strong>
            <select
              className="widefat"
              name={"extended_widget_opts[visibility][options]"}
              onChange={handleInputChangeVisibilityOption}
              value={
                props.extended_widget_opts["visibility"] != undefined
                  ? props.extended_widget_opts["visibility"]["options"]
                  : ""
              }
            >
              <option value="hide">{__("Hide on selected items")}</option>
              <option value="show">{__("Show on selected items")}</option>
            </select>
          </p>

          <div className="extended-widget-opts-visibility-tabs extended-widget-opts-inside-tabs">
            <input
              type="hidden"
              id="extended-widget-opts-visibility-selectedtab"
              value={selected}
              name={"extended_widget_opts[visibility][selected]"}
            />

            {/* <ul
              id={"visibility-tabs-" + props.widgetId}
              className="extended-widget-opts-visibility-tabnav-ul ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header"
            >
              {widget_options["settings"]["visibility"] != undefined &&
              widget_options["settings"]["visibility"]["misc"] != undefined &&
              "1" == widget_options["settings"]["visibility"]["misc"] ? (
                <li
                  ref={liMisc}
                  className="extended-widget-opts-visibility-tab-visibility ui-tabs-active"
                  onClick={() => clickTabs(liMisc, "misc")}
                >
                  <a
                    href={
                      "#extended-widget-opts-visibility-tab-" +
                      props.widgetId +
                      "-misc"
                    }
                    title={__("Home, Blog, Search, etc..")}
                  >
                    {__("Misc")}
                  </a>
                </li>
              ) : (
                ""
              )}

              {widget_options["settings"]["visibility"] != undefined &&
              widget_options["settings"]["visibility"]["post_type"] !=
                undefined &&
              "1" == widget_options["settings"]["visibility"]["post_type"] ? (
                <li
                  ref={liPostType}
                  className="extended-widget-opts-visibility-tab-visibility"
                  onClick={() => clickTabs(liPostType, "types")}
                >
                  <a
                    href={
                      "#extended-widget-opts-visibility-tab-" +
                      props.widgetId +
                      "-types"
                    }
                    title={__("Pages & Custom Post Types")}
                  >
                    {__("Post Types")}
                  </a>
                </li>
              ) : (
                ""
              )}

              {widget_options["settings"]["visibility"] != undefined &&
              widget_options["settings"]["visibility"]["taxonomies"] !=
                undefined &&
              "1" == widget_options["settings"]["visibility"]["taxonomies"] ? (
                <li
                  ref={liTaxonomies}
                  className="extended-widget-opts-visibility-tab-visibility"
                  onClick={() => clickTabs(liTaxonomies, "tax")}
                >
                  <a
                    href={
                      "#extended-widget-opts-visibility-tab-" +
                      props.widgetId +
                      "-tax"
                    }
                    title={__("Categories, Tags & Taxonomies")}
                  >
                    {__("Taxonomies")}
                  </a>
                </li>
              ) : (
                ""
              )}

              {widget_options["roles"] != undefined &&
              widget_options["roles"] == "activate" &&
              widget_options["settings"]["roles"] != undefined &&
              widget_options["settings"]["roles"]["authors"] != undefined &&
              "1" == widget_options["settings"]["roles"]["authors"] ? (
                <li
                  ref={liAuthor}
                  className="extended-widget-opts-visibility-tab-visibility"
                  onClick={() => clickTabs(liAuthor, "roles")}
                >
                  <a
                    href={
                      "#extended-widget-opts-visibility-tab-" +
                      props.widgetId +
                      "-roles"
                    }
                    title={__("Author")}
                  >
                    {__("Author")}
                  </a>
                </li>
              ) : (
                ""
              )}
              <div className="extended-widget-opts-clearfix"></div>
            </ul> */}
            <div className="extended-widget-opts-clearfix"></div>

            {widget_options["settings"]["visibility"] != undefined &&
            widget_options["settings"]["visibility"]["misc"] != undefined &&
            "1" == widget_options["settings"]["visibility"]["misc"] ? (
              <PanelBody title={__("Pages")} className="margin-x-minus-16">
                <div
                  style={{ border: "0px !important" }}
                  id={
                    "extended-widget-opts-visibility-tab-" +
                    props.widgetId +
                    "-misc"
                  }
                  className="border-0 padding-0 extended-widget-opts-visibility-tabcontent-2 extended-widget-opts-visibility-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-inner-tabcontent"
                >
                  <div className="extended-widget-opts-inner-lists height-auto">
                    <p
                      style={{ "margin-top": "5px" }}
                      className="widgetopts-subtitle"
                    >
                      Default Pages
                    </p>

                    {miscs.map(function (data, key) {
                      return (
                        <p>
                          <input
                            key={key}
                            type="checkbox"
                            data-key={data.key}
                            name={
                              "extended_widget_opts[visibility][misc][" +
                              data.key +
                              "]"
                            }
                            id={props.widgetId + "-opts-misc-" + data.key}
                            onChange={(event) =>
                              handleInputChangeVisibilityMisc(event, data.key)
                            }
                            checked={
                              props.extended_widget_opts["visibility"] !=
                                undefined &&
                              props.extended_widget_opts["visibility"][
                                "misc"
                              ] != undefined &&
                              props.extended_widget_opts["visibility"]["misc"][
                                data.key
                              ] == 1
                                ? true
                                : false
                            }
                          />
                          <label
                            for={props.widgetId + "-opts-misc-" + data.key}
                          >
                            {data.value}
                          </label>
                        </p>
                      );
                    })}

                    <h4
                      className="widgetopts-subtitle margin-bottom-0"
                      id="extended-widget-opts-pages"
                    >
                      {__("Pages")} +/-
                      <br />
                      <small>
                        Type atleast 3 characters to initiate the search
                      </small>
                    </h4>
                    <div className="extended-widget-opts-pages extended-widget-opts-parent-option">
                      <div style={{ "margin-bottom": "10px" }}>
                        <button
                          type="button"
                          class="widgetopts-search-option-btn"
                          style={{
                            width: "75px",
                            "background-color": "#3D434A",
                            color: "#fff",
                            "border-radius": "10px 0px 0 10px",
                            border: "1.5px solid #3D434A",
                          }}
                        >
                          Search
                        </button>
                        <button
                          type="button"
                          class="widgetopts-dropdown-option-btn"
                          style={{
                            "margin-left": "-5px",
                            width: "75px",
                            "border-radius": "0 10px 10px 0",
                            color: "#777A80",
                            "background-color": "#fff",
                            border: "1.5px solid #777A80",
                          }}
                        >
                          Checkbox
                        </button>
                      </div>
                      <select
                        className="widefat widgetopts-select2 extended-widget-opts-select2-dropdown extended-widget-opts-select2-page-dropdown"
                        name={"extended_widget_opts[visibility][pages][]"}
                        data-namespace={
                          "extended_widget_opts-" + props.widgetId
                        }
                        multiple="multiple"
                        value={
                          props.extended_widget_opts["visibility"] !=
                            undefined &&
                          props.extended_widget_opts["visibility"]["pages"] !=
                            undefined
                            ? props.extended_widget_opts["visibility"]["pages"]
                            : []
                        }
                        onChange={(event) =>
                          handleInputChangeSelector2(
                            event,
                            "visibility",
                            "pages"
                          )
                        }
                        key={parseInt(props.widgetId) + 10}
                      >
                        {(function () {
                          let pageLoop =
                            props.widgetopts_pages != undefined
                              ? props.widgetopts_pages
                              : [];
                          if (pageLoop.length > 0) {
                            return pageLoop.map(function (objPage) {
                              let _pages =
                                props.extended_widget_opts["visibility"] !=
                                  undefined &&
                                props.extended_widget_opts["visibility"][
                                  "pages"
                                ] != undefined
                                  ? props.extended_widget_opts["visibility"][
                                      "pages"
                                    ]
                                  : [];

                              if (_pages.includes(objPage.ID)) {
                                return (
                                  <option value={objPage.ID} selected>
                                    {objPage.post_title}
                                  </option>
                                );
                              } else {
                                return (
                                  <option value={objPage.ID}>
                                    {objPage.post_title}
                                  </option>
                                );
                              }
                            });
                          } else {
                            return "";
                          }
                        })()}
                      </select>
                    </div>
                  </div>
                </div>
              </PanelBody>
            ) : (
              ""
            )}

            {widget_options["settings"]["visibility"] != undefined &&
            widget_options["settings"]["visibility"]["post_type"] !=
              undefined &&
            "1" == widget_options["settings"]["visibility"]["post_type"] ? (
              <PanelBody
                title={__("Post Types")}
                className="margin-x-minus-16"
                initialOpen={false}
              >
                <div
                  id={
                    "extended-widget-opts-visibility-tab-" +
                    props.widgetId +
                    "-types"
                  }
                  className="padding-0 border-0 extended-widget-opts-visibility-tabcontent-2 extended-widget-opts-visibility-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-inner-tabcontent extended-widget-opts-tabcontent-pages"
                >
                  <div
                    className="extended-widget-opts-inner-lists height-auto"
                    style={{
                      height: "230px",
                      padding: "5px",
                      overflow: "auto",
                    }}
                  >
                    <h4
                      className="widgetopts-subtitle"
                      id="extended-widget-opts-types"
                      style={{ "margin-top": "5px" }}
                    >
                      {__("Custom Post Types")} +/-
                      <br />
                    </h4>
                    <div class="extended-widget-opts-types">
                      {(function () {
                        let keys = Object.keys(types);
                        return keys.map(function (ptype, i) {
                          return (
                            <p>
                              <input
                                type="checkbox"
                                name={
                                  "extended_widget_opts[visibility][types][" +
                                  ptype +
                                  "]"
                                }
                                id={props.widgetId + "-opts-types-" + ptype}
                                value="1"
                                onChange={(event) =>
                                  handleInputChangeVisibilityTypesCheckbox(
                                    event,
                                    ptype
                                  )
                                }
                                checked={
                                  props.extended_widget_opts["visibility"] !=
                                    undefined &&
                                  props.extended_widget_opts["visibility"][
                                    "types"
                                  ] != undefined &&
                                  props.extended_widget_opts["visibility"][
                                    "types"
                                  ][ptype] == 1
                                    ? true
                                    : false
                                }
                              />
                              <label
                                for={props.widgetId + "-opts-types-" + ptype}
                              >
                                {types[ptype].labels.name}
                              </label>
                            </p>
                          );
                        });
                      })()}
                    </div>
                  </div>
                </div>
              </PanelBody>
            ) : (
              ""
            )}

            {widget_options["settings"]["visibility"] != undefined &&
            widget_options["settings"]["visibility"]["taxonomies"] !=
              undefined &&
            "1" == widget_options["settings"]["visibility"]["taxonomies"] ? (
              <PanelBody
                title={__("Taxonomy Visibility")}
                className="margin-x-minus-16"
                initialOpen={false}
              >
                <div
                  id={
                    "extended-widget-opts-visibility-tab-" +
                    props.widgetId +
                    "-tax"
                  }
                  className="padding-0 border-0 extended-widget-opts-visibility-tabcontent-2 extended-widget-opts-visibility-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-inner-tabcontent extended-widget-opts-tabcontent-taxonomies"
                >
                  <div
                    className="extended-widget-opts-inner-lists height-auto"
                    style={{
                      height: "230px",
                      padding: "5px",
                      overflow: "auto",
                    }}
                  >
                    {widget_option_local_copy != undefined
                      ? Object.keys(widget_option_local_copy).map(
                          function (index, i) {
                            return taxonomies[index] != undefined &&
                              taxonomies[index].label != undefined &&
                              taxonomies[index].label.toLowerCase() ==
                                "categories" ? (
                              <div>
                                <p
                                  className="widgetopts-subtitle h4-taxo margin-bottom-0"
                                  id={
                                    "extended-widget-opts-taxt-" +
                                    props.widgetId
                                  }
                                  style={{ "margin-top": "5px" }}
                                >
                                  {taxonomies[index].label}{" "}
                                  {taxonomies[index].object_type != undefined &&
                                  taxonomies[index].object_type[0] !=
                                    undefined ? (
                                    <small>
                                      - {taxonomies[index].object_type[0]}{" "}
                                    </small>
                                  ) : (
                                    ""
                                  )}{" "}
                                  +/- <br />
                                  <small>
                                    Type atleast 3 characters to initiate the
                                    search for {taxonomies[index].label} name
                                  </small>
                                </p>
                                <div
                                  className={
                                    "extended-widget-opts-taxt-" +
                                    props.widgetId +
                                    " extended-widget-opts-parent-option"
                                  }
                                >
                                  <div style={{ "margin-bottom": "10px" }}>
                                    <button
                                      type="button"
                                      class="widgetopts-search-option-btn"
                                      style={{
                                        width: "75px",
                                        "background-color": "#3D434A",
                                        color: "#fff",
                                        "border-radius": "10px 0px 0 10px",
                                        border: "1.5px solid #3D434A",
                                      }}
                                    >
                                      Search
                                    </button>
                                    <button
                                      type="button"
                                      class="widgetopts-dropdown-option-btn"
                                      style={{
                                        "margin-left": "-5px",
                                        width: "75px",
                                        "border-radius": "0 10px 10px 0",
                                        color: "#777A80",
                                        "background-color": "#fff",
                                        border: "1.5px solid #777A80",
                                      }}
                                    >
                                      Checkbox
                                    </button>
                                  </div>
                                  <select
                                    className="widefat extended-widget-opts-select2-dropdown extended-widget-opts-select2-taxonomy-dropdown"
                                    name={
                                      "extended_widget_opts[visibility][tax_terms][" +
                                      index +
                                      "][]"
                                    }
                                    data-taxonomy={index}
                                    data-namespace={
                                      "extended_widget_opts-" + props.widgetId
                                    }
                                    multiple="multiple"
                                    value={
                                      props.extended_widget_opts[
                                        "visibility"
                                      ] != undefined &&
                                      props.extended_widget_opts["visibility"][
                                        "tax_terms"
                                      ] != undefined &&
                                      props.extended_widget_opts["visibility"][
                                        "tax_terms"
                                      ][index] != undefined
                                        ? props.extended_widget_opts[
                                            "visibility"
                                          ]["tax_terms"][index]
                                        : []
                                    }
                                    onChange={(event) =>
                                      handleInputChangeSelector2_3(
                                        event,
                                        "visibility",
                                        "tax_terms",
                                        index
                                      )
                                    }
                                    key={parseInt(props.widgetId) + 11}
                                  >
                                    {Object.keys(taxLoop).map(
                                      function (objTax) {
                                        return taxLoop[objTax].map(
                                          function (tax, i) {
                                            if (tax.taxonomy != index) {
                                              return;
                                            }

                                            let _term =
                                              props.extended_widget_opts[
                                                "visibility"
                                              ] != undefined &&
                                              props.extended_widget_opts[
                                                "visibility"
                                              ]["tax_terms"] != undefined &&
                                              props.extended_widget_opts[
                                                "visibility"
                                              ]["tax_terms"][index] != undefined
                                                ? props.extended_widget_opts[
                                                    "visibility"
                                                  ]["tax_terms"][index]
                                                : [];
                                            if (_term.includes(tax.term_id)) {
                                              return (
                                                <option
                                                  value={tax.term_id}
                                                  selected
                                                >
                                                  {tax.name}
                                                </option>
                                              );
                                            } else {
                                              return (
                                                <option value={tax.term_id}>
                                                  {tax.name}
                                                </option>
                                              );
                                            }
                                          }
                                        );
                                      }
                                    )}
                                  </select>

                                  <p style={{ "margin-top": "10px" }}>
                                    <strong>{__("Select Pages")}</strong>
                                    <br />
                                    <small>
                                      {__("Select where to show/hide widget.")}
                                    </small>
                                    <br />
                                    <select
                                      class="widefat"
                                      name={
                                        "extended_widget_opts[visibility][tax_terms_page][" +
                                        index +
                                        "]"
                                      }
                                      onChange={(event) =>
                                        handleInputChangeSelectorTaxonomiesPage(
                                          event,
                                          "visibility",
                                          "tax_terms_page",
                                          index
                                        )
                                      }
                                      value={
                                        props.extended_widget_opts[
                                          "visibility"
                                        ] != undefined &&
                                        props.extended_widget_opts[
                                          "visibility"
                                        ]["tax_terms_page"] != undefined &&
                                        props.extended_widget_opts[
                                          "visibility"
                                        ]["tax_terms_page"][index] != undefined
                                          ? props.extended_widget_opts[
                                              "visibility"
                                            ]["tax_terms_page"][index]
                                          : "1"
                                      }
                                    >
                                      <option value="1">
                                        {__("Archive and Single posts")}
                                      </option>
                                      <option value="2">
                                        {__("Archive only")}
                                      </option>
                                      <option value="3">
                                        {__("Single posts only")}
                                      </option>
                                    </select>
                                  </p>
                                </div>
                              </div>
                            ) : (
                              ""
                            );
                          }
                        )
                      : ""}

                    <h4
                      class="widgetopts-subtitle margin-bottom-0"
                      id="extended-widget-opts-taxonomies"
                    >
                      {__("Taxonomies")} +/-
                      <br />
                      <small>
                        Check to hide/show widget on specific taxonomies.
                      </small>
                    </h4>
                    <div class="extended-widget-opts-taxonomies">
                      {(function () {
                        let keys = Object.keys(taxonomies);
                        return keys.map(function (taxonomy, i) {
                          return (
                            <p>
                              <input
                                type="checkbox"
                                name={
                                  "extended_widget_opts[visibility][taxonomies][" +
                                  taxonomies[taxonomy].name +
                                  "]"
                                }
                                id={
                                  props.widgetId +
                                  "-opts-taxonomies-" +
                                  taxonomies[taxonomy].name
                                }
                                value="1"
                                onChange={(event) =>
                                  handleInputChangeVisibilityTaxonomiesCheckbox(
                                    event,
                                    taxonomies[taxonomy].name
                                  )
                                }
                                checked={
                                  props.extended_widget_opts["visibility"] !=
                                    undefined &&
                                  props.extended_widget_opts["visibility"][
                                    "taxonomies"
                                  ] != undefined &&
                                  props.extended_widget_opts["visibility"][
                                    "taxonomies"
                                  ][taxonomies[taxonomy].name] == 1
                                    ? true
                                    : false
                                }
                              />
                              <label
                                for={
                                  props.widgetId +
                                  "-opts-taxonomies-" +
                                  taxonomies[taxonomy].name
                                }
                              >
                                {taxonomies[taxonomy].label}
                              </label>{" "}
                              {taxonomies[taxonomy].object_type != undefined &&
                              taxonomies[taxonomy].object_type[0] !=
                                undefined ? (
                                <small>
                                  - {taxonomies[taxonomy].object_type[0]}
                                </small>
                              ) : (
                                ""
                              )}
                            </p>
                          );
                        });
                      })()}
                    </div>
                  </div>
                </div>
              </PanelBody>
            ) : (
              ""
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default VisibilityTabPanel;
