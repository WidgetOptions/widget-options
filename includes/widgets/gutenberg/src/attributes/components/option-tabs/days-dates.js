const { __ } = wp.i18n;
import { TabPanel, DateTimePicker } from "@wordpress/components";

const onSelect = (tabName) => {};

const DatesTabPanel = (props) => {
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
  let months = [
    __("January"),
    __("February"),
    __("March"),
    __("April"),
    __("May"),
    __("June"),
    __("July"),
    __("August"),
    __("September"),
    __("October"),
    __("November"),
    __("December"),
  ];
  let args = [];
  let checked = "";
  let options_dates = "";
  let annual = true;

  const change_date_format = (_dateString) => {
    let new_date = "";
    if (_dateString.trim() != "") {
      let _parseDate = Date.parse(_dateString);
      let _dates = new Date(_parseDate);

      let _m =
        (_dates.getMonth() < 9 ? "0" : "") + (_dates.getMonth() + 1).toString();
      let _d = (_dates.getDate() < 10 ? "0" : "") + _dates.getDate().toString();
      let _y = _dates.getFullYear();
      new_date = `${_y}-${_m}-${_d}`;
    }
    return new_date;
  };

  let to =
    props.extended_widget_opts["dates"] != undefined
      ? change_date_format(props.extended_widget_opts["dates"]["to"])
      : "";

  let from =
    props.extended_widget_opts["dates"] != undefined
      ? change_date_format(props.extended_widget_opts["dates"]["from"])
      : "";

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
      id={"extended-widget-opts-tab-" + props.widgetId + "-days"}
      className={
        "extended-widget-opts-tabcontent extended-widget-opts-tabcontent-days " +
        validLicenseClass
      }
      style={{ "margin-left": "16px" }}
    >
      {checkLicense(props.validLicense)}

      <div>
        <p style={{ "margin-top": "10px" }}>
          <strong>{__("Hide/Show")}</strong>
          <select
            class="widefat"
            name={"extended_widget_opts[days][options]"}
            value={""}
          >
            <option value="hide">{__("Hide on checked days")}</option>
            <option value="show">{__("Show on checked days")}</option>
          </select>
        </p>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <td scope="row">
                <strong>{__("Days")}</strong>
              </td>
              <td>&nbsp;</td>
            </tr>
            {days.map(function (day, key) {
              if (
                args["params"] != undefined &&
                args["params"]["days"] != undefined
              ) {
                if (args["params"]["days"][day.toLowerCase()] != undefined) {
                  checked = 'checked="checked"';
                } else {
                  checked = "";
                }
              } else {
                checked = "";
              }

              return (
                <tr valign="top">
                  <td scope="row">
                    <label
                      for={
                        "extended_widget_opts-" +
                        props.widgetId +
                        "-days-" +
                        day.toLowerCase()
                      }
                    >
                      {day}
                    </label>
                  </td>
                  <td>
                    <input
                      type="checkbox"
                      name={
                        "extended_widget_opts[days][" + day.toLowerCase() + "]"
                      }
                      id={
                        "extended_widget_opts-" +
                        props.widgetId +
                        "-days-" +
                        key
                      }
                      value="1"
                    />
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
        <br />
      </div>

      {widget_options["settings"]["dates"] != undefined &&
      widget_options["settings"]["dates"]["date_range"] != undefined &&
      "1" == widget_options["settings"]["dates"]["date_range"] ? (
        <div>
          <p>
            <strong>{__("Hide/Show")}</strong>
            <select
              class="widefat"
              name={"extended_widget_opts[dates][options]"}
              value={""}
            >
              <option value="hide">{__("Hide on date range")}</option>
              <option value="show">{__("Show on date range")}</option>
            </select>
          </p>
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <td scope="row">
                  <strong>{__("From: ")}</strong>
                </td>
                <td>
                  <input
                    type="date"
                    pattern="\d{4}-\d{2}-\d{2}"
                    name={"extended_widget_opts[dates][from]"}
                    className="widefat extended-widget-opts-date-from"
                  />
                </td>
              </tr>
              <tr valign="top">
                <td scope="row">
                  <strong>{__("To: ")}</strong>
                </td>
                <td>
                  <input
                    type="date"
                    pattern="\d{4}-\d{2}-\d{2}"
                    name={"extended_widget_opts[dates][to]"}
                    className="widefat extended-widget-opts-date-to"
                  />
                </td>
              </tr>
              <tr valign="top">
                <td colspan="2">
                  <input
                    type="checkbox"
                    id={
                      "extended_widget_opts-block-" + props.widgetId + "-annual"
                    }
                    name={"extended_widget_opts[dates][annual]"}
                    value="1"
                  />
                  <label
                    for={
                      "extended_widget_opts-block-" + props.widgetId + "-annual"
                    }
                  >
                    Ignore Year parameter
                  </label>
                  <p>
                    Enabling this option makes the date range repeat yearly.
                    Only use on date ranges less than a year.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      ) : (
        ""
      )}
    </div>
  );
};

export default DatesTabPanel;
