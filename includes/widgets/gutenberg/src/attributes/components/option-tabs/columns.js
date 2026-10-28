const { __ } = wp.i18n;
import { TabPanel } from "@wordpress/components";

const onSelect = (tabName) => {};

const ColumsTabPanel = (props) => {
  /* columns */
  const handleInputChangeDesktop = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["column"] == undefined ||
      (_attribute["column"] != undefined && _attribute["column"].length == 0)
    ) {
      _attribute["column"] = {};
    }

    _attribute["column"]["desktop"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeTablet = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (_attribute["column"] == undefined) {
      _attribute["column"] = {};
    }

    _attribute["column"]["tablet"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeMobile = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (_attribute["column"] == undefined) {
      _attribute["column"] = {};
    }

    _attribute["column"]["mobile"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  /* clearfix */
  const handleInputChangeClearDesktop = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (_attribute["clearfix"] == undefined) {
      _attribute["clearfix"] = {};
    }

    if (event.target.checked) {
      _attribute["clearfix"]["desktop"] = 1;
    } else {
      delete _attribute["clearfix"]["desktop"];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeClearTablet = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (_attribute["clearfix"] == undefined) {
      _attribute["clearfix"] = {};
    }

    if (event.target.checked) {
      _attribute["clearfix"]["tablet"] = 1;
    } else {
      delete _attribute["clearfix"]["tablet"];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeClearMobile = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (_attribute["clearfix"] == undefined) {
      _attribute["clearfix"] = {};
    }

    if (event.target.checked) {
      _attribute["clearfix"]["mobile"] = 1;
    } else {
      delete _attribute["clearfix"]["mobile"];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const validLicense = true;
  let validLicenseClass = props.validLicense ? "" : "disabled-section";

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

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-columns"}
      className={
        "extended-widget-opts-tabcontent extended-widget-opts-tabcontent-columns " +
        validLicenseClass
      }
      style={{ "margin-left": "16px" }}
    >
      {checkLicense(props.validLicense)}

      <table className="form-table">
        <tbody>
          <tr valign="top">
            <td scope="row" style={{ "padding-top": "0" }}>
              <strong>{__("Devices")}</strong>
            </td>
            <td style={{ "padding-top": "0" }}>
              <strong>{__("Columns")}</strong>
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
                name={"extended_widget_opts[column][desktop]"}
                value={
                  props.extended_widget_opts["column"] != undefined &&
                  props.extended_widget_opts["column"]["desktop"] != undefined
                    ? props.extended_widget_opts["column"]["desktop"]
                    : ""
                }
              >
                <optgroup label={__("One Column")}>
                  <option value="12">1/1</option>
                </optgroup>
                <optgroup label={__("Two Columns")}>
                  <option value="6">1/2</option>
                </optgroup>
                <optgroup label={__("Three Columns")}>
                  <option value="4">1/3</option>
                  <option value="8">2/3</option>
                </optgroup>
                <optgroup label={__("Four Columns")}>
                  <option value="3">1/4</option>
                  <option value="7">2/4</option>
                  <option value="9">3/4</option>
                </optgroup>
              </select>
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-tablet"></span>{" "}
              {__("Tablet")}
            </td>
            <td>
              <select
                className="widefat"
                name={"extended_widget_opts[column][tablet]"}
                value={
                  props.extended_widget_opts["column"] != undefined &&
                  props.extended_widget_opts["column"]["tablet"] != undefined
                    ? props.extended_widget_opts["column"]["tablet"]
                    : ""
                }
              >
                <optgroup label={__("One Column")}>
                  <option value="12">1/1</option>
                </optgroup>
                <optgroup label={__("Two Columns")}>
                  <option value="6">1/2</option>
                </optgroup>
                <optgroup label={__("Three Columns")}>
                  <option value="4">1/3</option>
                  <option value="8">2/3</option>
                </optgroup>
                <optgroup label={__("Four Columns")}>
                  <option value="3">1/4</option>
                  <option value="7">2/4</option>
                  <option value="9">3/4</option>
                </optgroup>
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
                name={"extended_widget_opts[column][mobile]"}
                value={
                  props.extended_widget_opts["column"] != undefined &&
                  props.extended_widget_opts["column"]["mobile"] != undefined
                    ? props.extended_widget_opts["column"]["mobile"]
                    : ""
                }
              >
                <optgroup label={__("One Column")}>
                  <option value="12">1/1</option>
                </optgroup>
                <optgroup label={__("Two Columns")}>
                  <option value="6">1/2</option>
                </optgroup>
                <optgroup label={__("Three Columns")}>
                  <option value="4">1/3</option>
                  <option value="8">2/3</option>
                </optgroup>
                <optgroup label={__("Four Columns")}>
                  <option value="3">1/4</option>
                  <option value="7">2/4</option>
                  <option value="9">3/4</option>
                </optgroup>
              </select>
            </td>
          </tr>
        </tbody>
      </table>

      <p style={{ "padding-left": "0px", "margin-top": "30px" }}>
        <strong>{__("Clear Floating Options")}</strong> <br />
        <small>
          {__(
            "If you are having floating issues with any devices for this widget, check the clearfix option to fix the floating issue."
          )}
        </small>
      </p>
      <table className="form-table">
        <tbody>
          <tr valign="top">
            <td scope="row">
              <strong>{__("Devices")}</strong>
            </td>
            <td>
              <strong>{__("Clearfix")}</strong>
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-desktop"></span>{" "}
              <label
                for={
                  "extended_widget_opts-" + props.widgetId + "-clearfix-desktop"
                }
              >
                {__("Desktop")}
              </label>
            </td>
            <td>
              <input
                type="checkbox"
                name={"extended_widget_opts[clearfix][desktop]"}
                checked={
                  props.extended_widget_opts["clearfix"] != undefined &&
                  props.extended_widget_opts["clearfix"]["desktop"] == 1
                    ? true
                    : false
                }
              />
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-tablet"></span>{" "}
              <label
                for={
                  "extended_widget_opts-" + props.widgetId + "-clearfix-tablet"
                }
              >
                {__("Tablet")}
              </label>
            </td>
            <td>
              <input
                type="checkbox"
                name={"extended_widget_opts[clearfix][tablet]"}
                id={
                  "extended_widget_opts-" + props.widgetId + "-clearfix-tablet"
                }
                checked={
                  props.extended_widget_opts["clearfix"] != undefined &&
                  props.extended_widget_opts["clearfix"]["tablet"] == 1
                    ? true
                    : false
                }
              />
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span className="dashicons dashicons-smartphone"></span>{" "}
              <label
                for={
                  "extended_widget_opts-" + props.widgetId + "-clearfix-mobile"
                }
              >
                {__("Mobile")}
              </label>
            </td>
            <td>
              <input
                type="checkbox"
                name={"extended_widget_opts[clearfix][mobile]"}
                id={
                  "extended_widget_opts-" + props.widgetId + "-clearfix-mobile"
                }
                checked={
                  props.extended_widget_opts["clearfix"] != undefined &&
                  props.extended_widget_opts["clearfix"]["mobile"] == 1
                    ? true
                    : false
                }
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  );
};

export default ColumsTabPanel;
