const { __ } = wp.i18n;
import { TabPanel } from "@wordpress/components";

const onSelect = (tabName) => {};

const AlignmentTabPanel = (props) => {
  /* alignment onchange event handler */
  const handleInputChangeDesktop = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["alignment"] == undefined ||
      (_attribute["alignment"] != undefined &&
        _attribute["alignment"].length == 0)
    ) {
      _attribute["alignment"] = {};
    }

    _attribute["alignment"]["desktop"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeTablet = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["alignment"] == undefined ||
      (_attribute["alignment"] != undefined &&
        _attribute["alignment"].length == 0)
    ) {
      _attribute["alignment"] = {};
    }

    _attribute["alignment"]["tablet"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeMobile = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["alignment"] == undefined ||
      (_attribute["alignment"] != undefined &&
        _attribute["alignment"].length == 0)
    ) {
      _attribute["alignment"] = {};
    }

    _attribute["alignment"]["mobile"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  let validLicenseClass = props.validLicense ? "" : "disabled-section";

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-alignment"}
      className="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-alignment"
      style={{ "margin-left": "16px", width: "100%" }}
    >
      <table className="form-table">
        <tbody>
          <tr valign="top">
            <td scope="row" style={{ "padding-top": "0" }}>
              <strong>{__("Devices")}</strong>
            </td>
            <td style={{ "padding-top": "0" }}>
              <strong>{__("Alignment")}</strong>
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-desktop"></span>{" "}
              {__("Desktop")}
            </td>
            <td>
              <select
                className="widefat"
                name={"extended_widget_opts[alignment][desktop]"}
                onChange={handleInputChangeDesktop}
                value={
                  props.extended_widget_opts["alignment"] != undefined
                    ? props.extended_widget_opts["alignment"]["desktop"]
                    : ""
                }
              >
                <option value="default">{__("Default")}</option>
                <option value="center">{__("Center")}</option>
                <option value="left">{__("Left")}</option>
                <option value="right">{__("Right")}</option>
                <option value="justify">{__("Justify")}</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>

      <table className={"form-table " + validLicenseClass}>
        <tbody>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-tablet"></span>{" "}
              {__("Tablet")}
            </td>
            <td>
              <select
                className="widefat"
                name={"extended_widget_opts[alignment][tablet]"}
                value={
                  props.extended_widget_opts["alignment"] != undefined
                    ? props.extended_widget_opts["alignment"]["tablet"]
                    : ""
                }
              >
                <option value="default">{__("Default")}</option>
                <option value="center">{__("Center")}</option>
                <option value="left">{__("Left")}</option>
                <option value="right">{__("Right")}</option>
                <option value="justify">{__("Justify")}</option>
              </select>
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-smartphone"></span>{" "}
              {__("Mobile")}
            </td>
            <td>
              <select
                className="widefat"
                name={"extended_widget_opts[alignment][mobile]"}
                value={
                  props.extended_widget_opts["alignment"] != undefined
                    ? props.extended_widget_opts["alignment"]["mobile"]
                    : ""
                }
              >
                <option value="default">{__("Default")}</option>
                <option value="center">{__("Center")}</option>
                <option value="left">{__("Left")}</option>
                <option value="right">{__("Right")}</option>
                <option value="justify">{__("Justify")}</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  );
};

export default AlignmentTabPanel;
