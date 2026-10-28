const { __ } = wp.i18n;
import { TabPanel } from "@wordpress/components";
import React from "react";

const { PanelBody } = wp.components;

const onSelect = (tabName) => {};

const StylingTabPanel = (props) => {
  const validLicense = true;
  let validLicenseClass = props.validLicense ? "" : "disabled-section";
  let desktop = "";
  let tablet = "";
  let mobile = "";
  let options_role = "";
  let widget_options = { ...props.widgetopts_get_settings };
  let days = [
    __("Monday"),
    __("Tuesday"),
    __("Wednesday"),
    __("Thursday"),
    __("Friday"),
    __("Saturday"),
    __("Sunday"),
  ];
  let args = [];
  let checked = "";
  let options_dates = "";
  let from = "";
  let to = "";
  let annual = true;
  let selected = "";
  let bg_image = "";
  let background = "";
  let background_hover = "";
  let heading = "";
  let text = "";
  let links = "";
  let links_hover = "";
  let border_color = "";
  let border_type = "";
  let border_width = "";
  let background_input = "";
  let text_input = "";
  let border_color_input = "";
  let border_type_input = "";
  let border_width_input = "";
  let background_submit = "";
  let background_submit_hover = "";
  let text_submit = "";
  let border_color_submit = "";
  let border_type_submit = "";
  let border_width_submit = "";
  let list_border_color = "";
  let table_border_color = "";
  const liWidget = React.useRef(null);
  const liForms = React.useRef(null);
  const liOthers = React.useRef(null);

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
      document.querySelectorAll(".extended-widget-opts-styling-tab-styling")
    ).forEach((ele) => ele.classList.remove("ui-tabs-active"));

    Array.from(
      document.querySelectorAll(
        ".extended-widget-opts-inside-tabs .extended-widget-opts-styling-tabcontent-2"
      )
    ).forEach((ele) => (ele.style.display = "none"));

    document.getElementById(
      "extended-widget-opts-styling-tab-" + props.widgetId + "-" + tabName
    ).style.display = "block";

    el.current.classList.add("ui-tabs-active");
  };

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-styling"}
      className={
        "extended-widget-opts-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-tabcontent-styling " +
        validLicenseClass
      }
      style={{ width: "100%" }}
    >
      {checkLicense(props.validLicense)}

      <div class="extended-widget-opts-styling-tabs extended-widget-opts-inside-tabs">
        <input
          type="hidden"
          id="extended-widget-opts-styling-selectedtab"
          value={selected}
          name={
            "extended_widget_opts-" +
            props.widgetId +
            "[extended_widget_opts][styling][selected]"
          }
        />

        <PanelBody
          title={__("Widget")}
          className="margin-x-minus-16"
          initialOpen={true}
        >
          <div
            id={
              "extended-widget-opts-styling-tab-" + props.widgetId + "-widget"
            }
            class="border-0 padding-0 extended-widget-opts-styling-tabcontent-2 extended-widget-opts-styling-tabcontent extended-widget-opts-inner-tabcontent"
          >
            <p
              style={{ "margin-top": "15px" }}
              class="widgetopts-subtitle margin-bottom-0"
            >
              {__("Background Image")}
            </p>

            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <td colspan="2" style={{ "margin-bottom": "1em" }}>
                    <input
                      type="text"
                      class="widefat extended_widget_opts-bg-image"
                      name={"extended_widget_opts[styling][bg_image]"}
                      placeholder={__("Image Url")}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td colspan="2" class="alright">
                    <input
                      style={{ "margin-right": "2px" }}
                      type="button"
                      class="button-primary extended_widget_opts-bg_uploader"
                      id={
                        "extended_widget_opts-" +
                        props.widgetId +
                        "-bg_uploader"
                      }
                      value={__("Upload")}
                      data-uploader_title="Choose Image"
                      data-uploader_button_text={__("Use Image")}
                      data-widget-id={props.widgetId}
                    />
                    <input
                      type="button"
                      class="button-secondary extended_widget_opts-remove_image"
                      value={__("Remove")}
                      data-widget-id={props.widgetId}
                    />
                  </td>
                </tr>
              </tbody>
            </table>
            <br />

            <p
              style={{ "padding-top": "30px", "padding-bottom": "10px" }}
              class="widgetopts-subtitle"
            >
              {__("Widget Styling Options")}
            </p>

            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <td scope="row">{__("Background Color")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][background]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Hover Background Color")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][background_hover]"}
                      value={
                        props.extended_widget_opts["styling"] != undefined
                          ? props.extended_widget_opts["styling"][
                              "background_hover"
                            ]
                          : ""
                      }
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Headings")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][heading]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Text")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][text]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Links")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][links]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Links Hover")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][links_hover]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Color")}</td>
                  <td>
                    <input
                      type="text"
                      class="widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][border_color]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Style")}</td>
                  <td>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[styling][border_type]"}
                      value={""}
                    >
                      <option value="">{__("Default")}</option>
                      <option value="solid">{__("Solid")}</option>
                      <option value="dashed">{__("Dashed")}</option>
                      <option value="dotted">{__("Dotted")}</option>
                      <option value="double">{__("Double")}</option>
                    </select>
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Width")}</td>
                  <td>
                    <input
                      type="text"
                      size="5"
                      class="inputsize5"
                      name={"extended_widget_opts[styling][border_width]"}
                      value={""}
                    />
                    px
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </PanelBody>

        <PanelBody
          title={__("Forms")}
          className="margin-x-minus-16"
          initialOpen={false}
        >
          <div
            id={"extended-widget-opts-styling-tab-" + props.widgetId + "-form"}
            class="border-0 padding-0 extended-widget-opts-styling-tabcontent-2 extended-widget-opts-styling-tabcontent extended-widget-opts-inner-tabcontent"
          >
            <p style={{ "margin-top": "15px" }} class="widgetopts-subtitle">
              {__("Textbox & Textarea Styling Options")}
            </p>

            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <td scope="row">{__("Background")}</td>
                  <td>
                    <input
                      type="text"
                      className="widget-opts-color widefat widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][background_input]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Text")}</td>
                  <td>
                    <input
                      type="text"
                      className="widget-opts-color widefat widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][text_input]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Color")}</td>
                  <td>
                    <input
                      type="text"
                      className="widget-opts-color widefat widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][border_color_input]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Style")}</td>
                  <td>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[styling][border_type_input]"}
                      value={""}
                    >
                      <option value="">{__("Default")}</option>
                      <option value="solid">{__("Solid")}</option>
                      <option value="dashed">{__("Dashed")}</option>
                      <option value="dotted">{__("Dotted")}</option>
                      <option value="double">{__("Double")}</option>
                    </select>
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Width")}</td>
                  <td>
                    <input
                      type="text"
                      size="5"
                      class="inputsize5"
                      name={"extended_widget_opts[styling][border_width_input]"}
                      value={""}
                    />
                    px
                  </td>
                </tr>
              </tbody>
            </table>

            <p
              style={{
                "padding-top": "40px",
                "padding-bottom": "10px",
                "margin-bottom": "5px",
              }}
              class="widgetopts-subtitle"
            >
              {__("Submit Button Styling Options")}
            </p>

            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <td scope="row">{__("Background")}</td>
                  <td>
                    <input
                      type="text"
                      className="widget-opts-color widefat widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][background_submit]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Hover Background")}</td>
                  <td>
                    <input
                      type="text"
                      className="widget-opts-color widefat widget-opts-event-trigger"
                      name={
                        "extended_widget_opts[styling][background_submit_hover]"
                      }
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Text")}</td>
                  <td>
                    <input
                      type="text"
                      className="widget-opts-color widefat widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][text_submit]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Color")}</td>
                  <td>
                    <input
                      type="text"
                      className="widefat widget-opts-color widget-opts-event-trigger"
                      name={
                        "extended_widget_opts[styling][border_color_submit]"
                      }
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Style")}</td>
                  <td>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[styling][border_type_submit]"}
                      value={""}
                    >
                      <option value="">{__("Default")}</option>
                      <option value="solid">{__("Solid")}</option>
                      <option value="dashed">{__("Dashed")}</option>
                      <option value="dotted">{__("Dotted")}</option>
                      <option value="double">{__("Double")}</option>
                    </select>
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Width")}</td>
                  <td>
                    <input
                      type="text"
                      size="5"
                      class="inputsize5"
                      name={
                        "extended_widget_opts[styling][border_width_submit]"
                      }
                      value={""}
                    />
                    px
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </PanelBody>

        <PanelBody
          title={__("Others")}
          className="margin-x-minus-16"
          initialOpen={false}
        >
          <div
            id={
              "extended-widget-opts-styling-tab-" + props.widgetId + "-others"
            }
            class="border-0 padding-0 extended-widget-opts-styling-tabcontent-2 extended-widget-opts-styling-tabcontent extended-widget-opts-inner-tabcontent"
          >
            <p style={{ "margin-top": "25px", "text-align": "left" }}>
              <small>
                {__(
                  "Styling will only reflect if the element and style is available on your theme."
                )}
              </small>
            </p>

            <p class="widgetopts-subtitle">{__("Lists")}</p>

            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <td scope="row">{__("Border Color")}</td>
                  <td>
                    <input
                      type="text"
                      className="widefat widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][list_border_color]"}
                      value={""}
                    />
                  </td>
                </tr>
                <tr valign="top">
                  <td scope="row">{__("Border Style")}</td>
                  <td>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[styling][list_border_type]"}
                      value={""}
                    >
                      <option value="">{__("Default")}</option>
                      <option value="solid">{__("Solid")}</option>
                      <option value="dashed">{__("Dashed")}</option>
                      <option value="dotted">{__("Dotted")}</option>
                      <option value="double">{__("Double")}</option>
                    </select>
                  </td>
                </tr>

                <tr valign="top">
                  <td scope="row">{__("Border Width")}</td>
                  <td>
                    <input
                      type="text"
                      size="5"
                      class="inputsize5"
                      name={"extended_widget_opts[styling][list_border_width]"}
                      value={""}
                    />
                    px
                  </td>
                </tr>
              </tbody>
            </table>

            <p class="widgetopts-subtitle">{__("Table")}</p>

            <table class="form-table">
              <tbody>
                <tr valign="top">
                  <td scope="row">{__("Border Color")}</td>
                  <td>
                    <input
                      type="text"
                      className="widefat widget-opts-color widget-opts-event-trigger"
                      name={"extended_widget_opts[styling][table_border_color]"}
                      value={""}
                    />
                  </td>
                </tr>

                <tr valign="top">
                  <td scope="row">{__("Border Style")}</td>
                  <td>
                    <select
                      class="widefat"
                      name={"extended_widget_opts[styling][table_border_type]"}
                      value={""}
                    >
                      <option value="">{__("Default")}</option>
                      <option value="solid">{__("Solid")}</option>
                      <option value="dashed">{__("Dashed")}</option>
                      <option value="dotted">{__("Dotted")}</option>
                      <option value="double">{__("Double")}</option>
                    </select>
                  </td>
                </tr>

                <tr valign="top">
                  <td scope="row">{__("Border Width")}</td>
                  <td>
                    <input
                      type="text"
                      size="5"
                      class="inputsize5"
                      name={"extended_widget_opts[styling][table_border_width]"}
                      value={""}
                    />
                    px
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </PanelBody>

        {/**do_action( 'extended_widgetopts_tabcontents_styling', $args );**/}
        <div class="extended-widget-opts-clearfix"></div>
      </div>

      <style>
        {`
      .extended-widget-opts-styling-tabcontent .form-table tr {
        display: flex;
        flex-direction: column;
      }
      .extended-widget-opts-styling-tabcontent .form-table td { 
        padding-top: 5px;
        padding-bottom: 0px;
       }
       `}
      </style>
    </div>
  );
};

export default StylingTabPanel;
