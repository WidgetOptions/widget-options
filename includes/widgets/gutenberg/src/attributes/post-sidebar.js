/*
 * Block Editor for Post Sidebar
 *
 * Copyright (c) 2023 Boholweb WP
 *
 */

/* Add widget options attribute to all existing blocks, in Sidebar */

const { __ } = wp.i18n;

const { createHigherOrderComponent } = wp.compose;
const { Fragment, Component, useState } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;

import WidgetOptionsTab from "./components/widgetopts-tab";
import { useDispatch, useSelect, subscribe } from "@wordpress/data";

let initialize_click_event = false;
let totalFetchData = 8;
let saveCachedCounter = 0;
let waitingCachedCounter = 0;
let intervalExecutionTime = 0;
let setCacheTime = 0;
let done_Saved = false;
var validLicense = false;
var isFetchDataDone = 0;
var nowFetchingData = false;
let widgetopts_types = {},
  widgetopts_taxonomies = {},
  widgetopts_acf_get_field_groups = {},
  widgetopts_get_settings = {},
  widgetopts_pages = [],
  widgetopts_terms = [],
  widgetopts_users = [],
  widgetopts_ajax_roles_search = {};

if (window.jQuery != undefined) {
  jQuery(document).ready(function ($) {
    if (
      jQuery("body").hasClass("wp-admin") &&
      (jQuery("body").hasClass("post-new-php") ||
        jQuery("body").hasClass("post-php"))
    ) {
      let selected_tab = "Alignment";
      $(document).on(
        "click",
        ".edit-post-sidebar .widget.is-opened form.form.widgetopts-form .ui-tabs button.components-panel__body-toggle",
        function () {
          let _cselected_tab = $(this).text();
          // if (selected_tab.toLowerCase() == _cselected_tab.toLowerCase()) {
          //   return;
          // }

          selected_tab = _cselected_tab;

          (function () {
            let intervalId = null;
            intervalId = setInterval(() => {
              if ($(".widget.is-opened form.form.widgetopts-form").length > 0) {
                clearInterval(intervalId);
                if (window.wpWidgetOpts != undefined) {
                  window.wpWidgetOpts.loaded(
                    $(".widget.is-opened form.form.widgetopts-form"),
                    "updated"
                  );
                }

                if (
                  $(
                    ".extended-widget-opts-tabs .extendedwopts-roles-author .select2-search__field"
                  ).length > 0
                ) {
                  $(
                    ".extended-widget-opts-tabs .extendedwopts-roles-author .select2-search__field"
                  ).attr("placeholder", "Search Authors");
                }
              }
            }, 500);
          })();

          /* select2 onchange event */
          $(document).on(
            "change",
            "select.extended-widget-opts-select2-dropdown:not(.currently-executed)",
            function (e) {
              $(this).addClass("currently-executed");

              e.currentTarget.dispatchEvent(
                new Event("change", {
                  view: window,
                  bubbles: true,
                  cancelable: false,
                })
              );

              $(this).removeClass("currently-executed");
            }
          );
        }
      );

      $(document).on(
        "focus",
        ".extended-widget-opts-tabs .extendedwopts-roles-author .select2-search__field",
        function () {
          $(this).attr("placeholder", "Search Authors");
        }
      );

      $(document).on(
        "blur",
        ".extended-widget-opts-tabs .extendedwopts-roles-author .select2-search__field",
        function () {
          //$(this).attr("placeholder", "");
        }
      );

      $(document).on("blur", "button.media-button-select", function (e) {
        let selector = document.querySelector(
          ".block-editor-widgetopts-container.is-opened .extended-widget-opts-tabcontent-styling input.extended_widget_opts-bg-image"
        );
        $(selector).addClass("extended-widgetopts-uploading-image");
      });

      $(document).on(
        "change",
        ".block-editor-widgetopts-container.is-opened .extended-widget-opts-tabcontent-styling input.extended_widget_opts-bg-image.extended-widgetopts-uploading-image",
        function (e) {
          $(this).removeClass("extended-widgetopts-uploading-image");

          let _reservedVal = $(this).val();
          $(this).val("");

          let triggerReact = new Event("change", { bubbles: true });
          let woptsInputValueSetter = Object.getOwnPropertyDescriptor(
            window.HTMLInputElement.prototype,
            "value"
          ).set;

          woptsInputValueSetter.call(e.currentTarget, _reservedVal);
          e.currentTarget.dispatchEvent(triggerReact);
        }
      );

      $(document).on("focusin", ".wp-picker-clear", function () {
        $(this)
          .parent()
          .find("input.widget-opts-event-trigger")
          .each(function () {
            $(this).val("").change();
          });
      });

      $(document).on(
        "change",
        ".wp-picker-container .widget-opts-event-trigger",
        function (e) {
          e.preventDefault();

          if ($(e.target).hasClass("manual-trigger-event")) {
            $(e.target).removeClass("manual-trigger-event");
            return;
          }

          let _reservedVal = $(e.target).val();
          $(e.target).val(0);

          let triggerReact = new Event("change", { bubbles: true });
          let woptsInputValueSetter = Object.getOwnPropertyDescriptor(
            window.HTMLInputElement.prototype,
            "value"
          ).set;

          $(e.target).addClass("manual-trigger-event");

          woptsInputValueSetter.call(e.target, _reservedVal);
          e.target.dispatchEvent(triggerReact);
        }
      );

      //toggle accordions
      $(document).on(
        "click",
        ".extended-widget-opts-inner-lists .h4-taxo",
        function (e) {
          var getid = $(this).attr("id");
          $(this)
            .parent()
            .find("." + getid + ", .h4-taxo>small:nth-child(3)")
            .slideToggle(150);
        }
      );

      $(document).on(
        "click",
        ".extended-widget-opts-inner-lists h4",
        function (e) {
          var getid = $(this).attr("id");
          $(this).find("small:nth-child(2)").slideToggle(260);
        }
      );
    }
  });
}

/**
 * Add Custom Select to Image Sidebar
 */
const withSidebarTabPost = (BlockEdit) => {
  //this part will be executed only once for all blocks
  const fetchData = (prop) => {
    if (nowFetchingData) {
      return;
    } else {
      nowFetchingData = true;
    }

    const d = new Date();

    wp.ajax.post("widgetopts_get_settings_ajax", {}).then(function (response) {
      isFetchDataDone++;
      widgetopts_get_settings = response;

      if (isFetchDataDone == totalFetchData) {
        prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
      }
    });

    wp.ajax.post("widgetopts_get_types", {}).then(function (response) {
      isFetchDataDone++;
      widgetopts_types = response;

      if (isFetchDataDone == totalFetchData) {
        prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
      }
    });

    wp.ajax.post("widgetopts_get_taxonomies", {}).then(function (response) {
      isFetchDataDone++;
      widgetopts_taxonomies = response;

      if (isFetchDataDone == totalFetchData) {
        prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
      }
    });

    wp.ajax
      .post("widgetopts_acf_get_field_groups", {})
      .then(function (response) {
        isFetchDataDone++;
        widgetopts_acf_get_field_groups = response;

        if (isFetchDataDone == totalFetchData) {
          prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
        }
      });

    wp.ajax
      .post("widgetopts_ajax_roles_search_block", {})
      .then(function (response) {
        isFetchDataDone++;
        widgetopts_ajax_roles_search = response;

        if (isFetchDataDone == totalFetchData) {
          prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
        }
      });

    wp.ajax.post("widgetopts_get_pages", {}).then(function (response) {
      isFetchDataDone++;
      widgetopts_pages = response;

      if (isFetchDataDone == totalFetchData) {
        prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
      }
    });

    wp.ajax.post("widgetopts_get_terms", {}).then(function (response) {
      isFetchDataDone++;
      widgetopts_terms = response;

      if (isFetchDataDone == totalFetchData) {
        prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
      }
    });

    wp.ajax.post("widgetopts_get_users", {}).then(function (response) {
      isFetchDataDone++;
      widgetopts_users = response;

      if (isFetchDataDone == totalFetchData) {
        prop.setAttributes({ ...prop.attributes, dateUpdated: d.getTime() });
      }
    });
  };

  const clone_object = (_obj) => {
    if (_obj == undefined || _obj == null) {
      return {};
    }

    let new_obj = Object.create(_obj);
    let keys = Object.keys(_obj);
    for (let i = 0; i < keys.length; i++) {
      if (Object(_obj[keys[i]]) === _obj[keys[i]]) {
        new_obj[keys[i]] = { ..._obj[keys[i]] };
      } else {
        new_obj[keys[i]] = _obj[keys[i]];
      }
    }

    return { ...new_obj };
  };

  var selector = document.querySelector(
    ".edit-post-header__settings button.components-button.is-primary.editor-post-publish-button"
  );

  const _return = (props) => {
    let isWpCustomizer = document.body.classList.contains("wp-customizer");
    let isWidgetBlockEditor = document.body.classList.contains("widgets-php");
    //If it is a widget block editor return immediately
    if (isWidgetBlockEditor || isWpCustomizer) {
      return <BlockEdit {...props} />;
    }

    // if (
    //   (props.attributes.__internalWidgetId == undefined &&
    //     props.name === "core/widget-area") ||
    //   (props.attributes.__internalWidgetId == undefined &&
    //     props.__unstableParentLayout != undefined)
    // ) {
    //   return <BlockEdit {...props} />;
    // }

    const { editEntityRecord, saveEditedEntityRecord } = useDispatch("core");

    fetchData(props);

    if (Object.keys(widgetopts_get_settings).length === 0) {
      return <BlockEdit {...props} />;
    } else {
      if (
        widgetopts_get_settings["hide_page_and_post_block"] != "activate" ||
        (widgetopts_get_settings["hide_page_and_post_block"] == "activate" &&
          widgetopts_get_settings["settings"] != undefined &&
          widgetopts_get_settings["settings"]["hide_page_and_post_block"] !=
            undefined &&
          widgetopts_get_settings["settings"]["hide_page_and_post_block"][
            "page_and_post_block"
          ] == "1")
      ) {
        return <BlockEdit {...props} />;
      }
    }

    if (props.name === "core/paragraph") {
      // Check if the block content is empty
      const isEmpty =
        !props.attributes.content || props.attributes.content.trim() === "";

      if (isEmpty) {
        return (
          <Fragment>
            <BlockEdit {...props} />
          </Fragment>
        );
      }
    }

    let _myprops = {};
    let id_base = -1;
    if (props.attributes.__internalWidgetId != undefined)
      id_base = props.attributes.__internalWidgetId.split("-")[0];

    let widget_opts = {
      id_base: id_base,
      column: {
        desktop: "12",
        tablet: "12",
        mobile: "12",
      },
      alignment: {
        desktop: "default",
        tablet: "default",
        mobile: "default",
      },
      roles: {
        state: "",
        options: "hide",
      },
      visibility: {
        selected: "0",
        options: "hide",
        acf: {
          visibility: "hide",
          field: "",
          condition: "",
          value: "",
        },
      },
      author_page: {
        author_pages: {
          selections: "1",
        },
      },
      devices: {
        options: "hide",
      },
      days: {
        options: "hide",
      },
      dates: {
        options: "hide",
        from: "",
        to: "",
      },
      styling: {
        selected: "0",
        bg_image: "",
        background: "",
        background_hover: "",
        heading: "",
        text: "",
        links: "",
        links_hover: "",
        border_color: "",
        border_type: "",
        border_width: "",
        background_input: "",
        text_input: "",
        border_color_input: "",
        border_type_input: "",
        border_width_input: "",
        background_submit: "",
        background_submit_hover: "",
        text_submit: "",
        border_color_submit: "",
        border_type_submit: "",
        border_width_submit: "",
        list_border_color: "",
        table_border_color: "",
      },
      class: {
        selected: "0",
        link: "",
        id: "",
        classes: "",
        animation: "",
        event: "enters",
        speed: "",
        offset: "",
        delay: "",
        logic: "",
      },
      tabselect: "0",
    };

    if (props.name != undefined && props.name == "core/freeform") {
      if (
        props.attributes != undefined &&
        props.attributes.content != undefined
      ) {
        if (props.attributes.content.includes("<!--start_widgetopts")) {
          let start = props.attributes.content.indexOf("<!--start_widgetopts");
          let end = props.attributes.content.indexOf("end_widgetopts-->");
          let content = props.attributes.content.substring(start, end + 17);
          content = content
            .replace("<!--start_widgetopts ", "")
            .replace(" end_widgetopts-->", "");

          widget_opts = JSON.parse(content.trim());
        }
      }
    }

    /*Note: props.attributes.extended_widget_opts == undefined it means it is currently in post block editor
     * id_base != -1 it means newly added block
     */
    if (
      props.attributes.extended_widget_opts == undefined &&
      id_base != -1 &&
      (props.attributes.instance == undefined ||
        props.attributes.instance.raw == undefined ||
        props.attributes.instance.raw[
          "extended_widget_opts-" + props.attributes.__internalWidgetId
        ] == undefined)
    ) {
      if (
        props.attributes.extended_widget_opts_state == 0 ||
        (done_Saved && props.attributes.extended_widget_opts_state != 0)
      ) {
        done_Saved = false;

        _myprops = useSelect(
          (select) =>
            select("core").getEntityRecord(
              "root",
              "widget",
              props.attributes.__internalWidgetId
            ),
          [props.attributes.__internalWidgetId]
        );
      } else {
        _myprops = useSelect(
          (select) =>
            select("core").getEditedEntityRecord(
              "root",
              "widget",
              props.attributes.__internalWidgetId
            ),
          [props.attributes.__internalWidgetId]
        );
      }

      if (
        window.widgetopts_cached != undefined &&
        window.widgetopts_cached[
          "extended_widget_opts-" + props.attributes.__internalWidgetId
        ] != undefined
      ) {
        if (_myprops.instance == undefined) {
          _myprops.instance =
            props.attributes.instance == undefined
              ? { raw: {} }
              : props.attributes.instance;
        }

        if (_myprops.instance.raw == undefined) {
          _myprops.instance.raw =
            props.attributes.instance.raw == undefined
              ? {}
              : props.attributes.instance.raw;
        }

        _myprops.instance.raw[
          "extended_widget_opts-" + props.attributes.__internalWidgetId
        ] = {
          ...window.widgetopts_cached[
            "extended_widget_opts-" + props.attributes.__internalWidgetId
          ],
        };
      }
    } else {
      if (props.attributes.extended_widget_opts != undefined) {
        _myprops = props.attributes;

        if (
          window.widgetopts_cached != undefined &&
          window.widgetopts_cached["extended_widget_opts-" + props.clientId] !=
            undefined
        ) {
          _myprops["extended_widget_opts"] = {
            ...window.widgetopts_cached[
              "extended_widget_opts-" + props.clientId
            ],
          };
        }
      } else {
        _myprops.instance = props.attributes.instance;
        _myprops.id = props.attributes.__internalWidgetId;
      }
    }

    if (props.attributes.extended_widget_opts != undefined) {
      if (
        _myprops["extended_widget_opts"] == undefined ||
        (_myprops["extended_widget_opts"] != undefined &&
          Object.keys(_myprops["extended_widget_opts"]).length == 0)
      )
        _myprops["extended_widget_opts"] = {
          ...widget_opts,
        };
    } else {
      if (
        _myprops == undefined ||
        _myprops.instance == undefined ||
        _myprops.instance.raw == undefined ||
        _myprops.instance.raw["extended_widget_opts-" + _myprops.id] ==
          undefined
      ) {
        if (_myprops == undefined) {
          _myprops = props.attributes;
        }

        if (_myprops.instance == undefined) {
          _myprops.instance = {};
        }

        if (_myprops.instance.raw == undefined) {
          _myprops.instance.raw = {};
        }

        _myprops.instance.raw["extended_widget_opts-" + _myprops.id] = {
          ...widget_opts,
        };
      }
    }

    const updatePostAttribute = (newValue) => {
      let save_button = document.querySelector(
        ".edit-post-header__settings button.components-button.is-primary.editor-post-publish-button"
      );
      if (save_button) {
        save_button.disabled = true;
      } else {
        save_button = document.querySelector(
          ".edit-post-header__settings button.components-button.is-primary.editor-post-publish-button__button"
        );

        if (save_button) {
          save_button.disabled = true;
        } else {
          save_button = document.querySelector(
            ".edit-post-header button.components-button.is-primary.editor-post-publish-button__button"
          );

          if (save_button) {
            save_button.disabled = true;
          }
        }
      }

      let _attribute = {
        ...props.attributes,
      };

      if (_attribute != undefined) {
        _attribute["extended_widget_opts"] = clone_object({ ...newValue });
      }

      props.setAttributes({
        extended_widget_opts: { ...newValue },
        extended_widget_opts_clientid: props.clientId,
        extended_widget_opts_state: Math.random().toString(),
      });

      if (props.name != undefined && props.name == "core/freeform") {
        if (props.attributes.content.includes("<!--start_widgetopts")) {
          let _props = props.attributes.content.replaceAll(
            /<!--start_widgetopts[\s]+[{\":,}\w\W]*[\s]+end_widgetopts-->/gim,
            `<!--start_widgetopts ${JSON.stringify({
              ...newValue,
            })} end_widgetopts-->`
          );

          props.setAttributes({
            content: _props,
          });
        } else {
          props.setAttributes({
            content: `<div><!--start_widgetopts ${JSON.stringify({
              ...newValue,
            })} end_widgetopts--> ${props.attributes.content} </div>`,
          });
        }
      }

      if (window.widgetopts_cached == undefined) {
        window.widgetopts_cached = {};
      }
      window.widgetopts_cached["extended_widget_opts-" + props.clientId] = {
        ..._attribute["extended_widget_opts"],
      };

      if (save_button) {
        save_button.disabled = false;
      }
    };

    // Example: Update dynamicAttribute on input change
    const handleInputChange = (_attribute, widget_id) => {
      // if (props.attributes.extended_widget_opts != undefined) {
      updatePostAttribute(_attribute);
      // }
    };

    return (
      <>
        <BlockEdit {...props} />
        <InspectorControls>
          <PanelBody
            title={__("Widget Options")}
            className="block-editor-widgetopts-container widget extended-widget-opts-form"
            icon="admin-generic"
          >
            <form
              className="form widgetopts-form"
              method="post"
              id={"widgetopts-form-" + props.attributes.__internalWidgetId}
            >
              <input
                type="hidden"
                name="extended_widget_opts_name"
                value={
                  "extended_widget_opts-" + props.attributes.__internalWidgetId
                }
              />
              <input
                type="hidden"
                name={
                  "extended_widget_opts-" +
                  props.attributes.__internalWidgetId +
                  "[extended_widget_opts][id_base]"
                }
                value={props.attributes.__internalWidgetId}
              />

              {isFetchDataDone >= totalFetchData ? (
                <WidgetOptionsTab
                  widgetId={props.attributes.__internalWidgetId}
                  extended_widget_opts={
                    props.attributes.extended_widget_opts != undefined
                      ? _myprops["extended_widget_opts"]
                      : _myprops.instance.raw[
                          "extended_widget_opts-" + _myprops.id
                        ]
                  }
                  onUpdateDynamicAttribute={handleInputChange.bind(this)}
                  widgetopts_types={widgetopts_types}
                  widgetopts_taxonomies={widgetopts_taxonomies}
                  widgetopts_acf_get_field_groups={
                    widgetopts_acf_get_field_groups
                  }
                  validLicense={validLicense}
                  widgetopts_get_settings={widgetopts_get_settings}
                  widgetopts_pages={widgetopts_pages}
                  widgetopts_terms={widgetopts_terms}
                  widgetopts_users={widgetopts_users}
                  widgetopts_ajax_roles_search={widgetopts_ajax_roles_search}
                  editor={isWidgetBlockEditor === true ? "widget" : "post"}
                />
              ) : (
                <p>Loading...</p>
              )}
            </form>
          </PanelBody>
          <style>
            {`
                button.components-button.has-icon {
                    justify-content: center;
                    min-width: 30px;
                    padding: 6px;
                }
                button.components-button.active-tab {
                    color: var(--wp-components-color-accent,var(--wp-admin-theme-color,#007cba));
                }
                .extended-widget-opts-tabcontent {
                  padding: 0px 0px 15px 0px;
                }
                .extended-widget-opts-inside-tabs .ui-tabs-nav li a {
                  padding: 5px !important;
                }
                .block-editor-widgetopts-container>h2.components-panel__body-title>button.components-panel__body-toggle {
                  padding-left: 40px !important;
                }
                .extended-widget-opts-tabs button.components-panel__body-toggle {
                  padding-left: 28px !important;
                }
                .extended-widget-opts-tabs .components-panel__body .components-panel__body button.components-panel__body-toggle {
                  padding-left: 16px !important;
              }
                .extended-widget-opts-tabs button.components-panel__body-toggle > span.components-panel__icon, .block-editor-widgetopts-container>h2.components-panel__body-title>button.components-panel__body-toggle > span.components-panel__icon {
                  position: absolute !important;
                  text-align: left !important;
                  margin: -25px !important;
                }
                .extended-widget-opts-tabs .border-0 {
                  border: 0px !important;
                }
                .extended-widget-opts-tabs .padding-0 {
                  padding: 0px !important;
                }
                .extended-widget-opts-tabs .height-auto {
                  height: auto !important;
                }
                .extended-widget-opts-tabs .padding-x-0 {
                  padding: 16px 0 !important;
                }
                .extended-widget-opts-tabs .padding-y-0 {
                  padding: 0 16px !important;
                }
                .select2-search .select2-search__field {
                  width: 100% !important;
                }
                .extended-widget-opts-tabs .widgetopts-subtitle {
                  border-top: 0px solid #ddd;
                  border-bottom: 0px solid #ddd;
                  text-align: left !important;
                  background: transparent;
                }
                .extended-widget-opts-tabs .margin-x-minus-16 {
                  margin-left: 0px;
                  margin-right: -16px;
                }
                .extended-widget-opts-tabs .extended-widget-opts-inner-lists {
                  padding: 5px 0px !important;
                }

                .extended-widget-opts-tabs .extended-widget-opts-inner-roles {
                  padding: 5px 0px !important;
                }

                .extended-widget-opts-tabs .form-table td {
                  padding: 7px 0px !important;
                }

                .extended-widget-opts-tabs .extended-widget-opts-tabcontent-columns .form-table td, 
                .extended-widget-opts-tabs .extended-widget-opts-tabcontent-alignment .form-table td {
                  padding: 7px 22px 7px 0px !important;
                }

                .extended-widget-opts-tabs .extended-widget-opts-styling-tabcontent .form-table td {
                  padding: 0px 0px 0 !important;
                  margin-bottom: 1em;
                }

                .extended-widget-opts-tabs .extended-widget-opts-styling-tabcontent .form-table tr>td:nth-child(1) {
                  margin-bottom: 0px;
                }

                .extended-widget-opts-tabs .widgetopts-links-widget-opts .form-table td {
                  padding: 5px 0px !important;
                }

                .extended-widget-opts-tabs .widgetopts_id_fld .form-table td {
                  padding: 8px 0px !important;
                }

                .extended-widget-opts-tabs .form-table {
                  margin-top: 0px;
                }

                .extended-widget-opts-tabs .widgetopts-subtitle {
                  padding-bottom: 0;
                }

                .extended-widget-opts-tabs .margin-bottom-0 {
                  margin-bottom: 0 !important;
                }

                .extended-widget-opts-tabcontent.extended-widget-opts-inside-tabcontent {
                  padding: 0px 0px !important;
                }

                .extended-widget-opts-tabcontent .h4-taxo {
                  font-size: 1em;
                  cursor: pointer;
                }

                .extended-widget-opts-tabs .select2-container .select2-search--inline .select2-search__field {
                  margin-top: 0 !important;
                }

                .extended-widget-opts-tabs .select2-search.select2-search--inline, 
                .extended-widget-opts-tabs ul.select2-selection__rendered, 
                .extended-widget-opts-tabs .select2-container--default .select2-selection--multiple {
                  padding-top: 0 !important;
                  padding-bottom: 0 !important;
                }

                .extended-widget-opts-tabs .select2-search.select2-search--inline {
                  margin-bottom: 0 !important;
                }
                
                .extended-widget-opts-tabs .select2.select2-container ul.select2-selection__rendered {
                  margin-bottom: 0 !important;
                  margin-top: 0px !important;
                }

                .extended-widget-opts-tabs .select2 ul.select2-selection__rendered:not( :has(li.select2-search.select2-search--inline) ) {
                  display: flex !important;
                }

                .extended-widget-opts-tabs .select2-container.select2-container--default .select2-selection--multiple .select2-selection__choice {
                  display: inline-block !important;
                  margin-bottom: 0 !important;
                }

                .extended-widget-opts-tabs .select2-container .select2-selection_rendered .select2-selection__choice {
                  margin-bottom: 0 !important;
                }

                .extended-widget-opts-tabs .extended-widget-opts-inner-lists {
                  overflow: hidden;
                }

                .extended-widget-opts-tabs .multiselect-container{
                  max-height: 150px;
                  overflow: auto;
                  position: relative;
                }

                .extended-widget-opts-tabs .multiselect-native-select .btn-group {
                  justify-content: start;
                  flex-direction: column;
                }

                .extended-widget-opts-tabs .extended-widget-opts-parent-option {
                  max-width: 200px;
                }

                .extended-widget-opts-tabs .extended-widget-opts-inner-roles {
                  max-height: 280px !important;
                }
                `}
          </style>
        </InspectorControls>
      </>
    );
  };

  return _return;
};

wp.domReady(function () {
  wp.hooks.addFilter(
    "editor.BlockEdit",
    "extended-widget-options/sidebar-component",
    withSidebarTabPost
  );
});
