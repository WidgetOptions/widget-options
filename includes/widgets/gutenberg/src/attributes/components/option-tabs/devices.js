const { __ } = wp.i18n;
import { TabPanel } from "@wordpress/components";

const onSelect = (tabName) => {};

const DevicesTabPanel = (props) => {
  let desktop = "";
  let tablet = "";
  let mobile = "";
  let options_role = "";

  const handleInputChangeDeviceHideShow = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    _attribute["devices"]["options"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeDeviceDesktop = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    _attribute["devices"]["desktop"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeDeviceDesktopCheckbox = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    if (event.target.checked) {
      _attribute["devices"]["desktop"] = 1;
    } else {
      delete _attribute["devices"]["desktop"];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeDeviceTablet = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    _attribute["devices"]["tablet"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeDeviceTabletCheckbox = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    if (event.target.checked) {
      _attribute["devices"]["tablet"] = 1;
    } else {
      delete _attribute["devices"]["tablet"];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeDeviceMobile = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    _attribute["devices"]["mobile"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeDeviceMobileCheckbox = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["devices"] == undefined ||
      (_attribute["devices"] != undefined && _attribute["devices"].length == 0)
    ) {
      _attribute["devices"] = {};
    }

    if (event.target.checked) {
      _attribute["devices"]["mobile"] = 1;
    } else {
      delete _attribute["devices"]["mobile"];
    }

    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-devices"}
      className="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-devices"
      style={{ "margin-left": "16px" }}
    >
      <p style={{ "margin-top": "10px" }}>
        <strong>{__("Hide/Show")}</strong>
        <select
          class="widefat"
          name={"extended_widget_opts[devices][options]"}
          onChange={handleInputChangeDeviceHideShow}
          value={
            props.extended_widget_opts["devices"] != undefined
              ? props.extended_widget_opts["devices"]["options"]
              : ""
          }
        >
          <option value="hide">{__("Hide on checked devices")}</option>
          <option value="show">{__("Show on checked devices")}</option>
        </select>
      </p>

      <table class="form-table">
        <tbody>
          <tr valign="top">
            <td scope="row">
              <strong>{__("Devices")}</strong>
            </td>
            <td>&nbsp;</td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span class="dashicons dashicons-desktop"></span>{" "}
              <label
                for={
                  "extended_widget_opts-" + props.widgetId + "-devices-desktop"
                }
              >
                {__("Desktop")}
              </label>
            </td>
            <td>
              <input
                type="checkbox"
                name={"extended_widget_opts[devices][desktop]"}
                value="1"
                id={
                  "extended_widget_opts-" + props.widgetId + "-devices-desktop"
                }
                onChange={handleInputChangeDeviceDesktopCheckbox}
                checked={
                  props.extended_widget_opts["devices"] != undefined &&
                  props.extended_widget_opts["devices"]["desktop"] == 1
                    ? true
                    : false
                }
              />
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span class="dashicons dashicons-tablet"></span>{" "}
              <label
                for={
                  "extended_widget_opts-" + props.widgetId + "-devices-table"
                }
              >
                {__("Tablet")}
              </label>
            </td>
            <td>
              <input
                type="checkbox"
                name={"extended_widget_opts[devices][tablet]"}
                value="1"
                id={"extended_widget_opts-" + props.widgetId + "-devices-table"}
                onChange={handleInputChangeDeviceTabletCheckbox}
                checked={
                  props.extended_widget_opts["devices"] != undefined &&
                  props.extended_widget_opts["devices"]["tablet"] == 1
                    ? true
                    : false
                }
              />
            </td>
          </tr>
          <tr valign="top">
            <td scope="row">
              <span class="dashicons dashicons-smartphone"></span>{" "}
              <label
                for={
                  "extended_widget_opts-" + props.widgetId + "-devices-mobile"
                }
              >
                {__("Mobile")}
              </label>
            </td>
            <td>
              <input
                type="checkbox"
                name={
                  "extended_widget_opts-" +
                  props.widgetId +
                  "[extended_widget_opts][devices][mobile]"
                }
                value="1"
                id={
                  "extended_widget_opts-" + props.widgetId + "-devices-mobile"
                }
                onChange={handleInputChangeDeviceMobileCheckbox}
                checked={
                  props.extended_widget_opts["devices"] != undefined &&
                  props.extended_widget_opts["devices"]["mobile"] == 1
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

export default DevicesTabPanel;
