import { __ } from "@wordpress/i18n";
import { TabPanel } from "@wordpress/components";

const onSelect = (tabName) => {};

const RolesTabPanel = (props) => {
  const validLicense = true;
  let validLicenseClass = props.validLicense ? "" : "disabled-section";
  let widget_options = { ...props.widgetopts_get_settings };
  let tablet = "";
  let mobile = "";
  let desktop_clear = "";
  let tablet_clear = "";
  let mobile_clear = "";
  let options_role = "";
  let roles =
    props.widgetopts_ajax_roles_search != undefined &&
    props.widgetopts_ajax_roles_search.results != undefined
      ? props.widgetopts_ajax_roles_search.results
      : [];

  /* alignment onchange event handler */
  const handleInputChangeState = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["roles"] == undefined ||
      (_attribute["roles"] != undefined && _attribute["roles"].length == 0)
    ) {
      _attribute["roles"] = {};
    }

    _attribute["roles"]["state"] = event.target.value;
    props.onUpdateDynamicAttribute(_attribute, props.widgetId);
  };

  const handleInputChangeRolesSelected = (event) => {
    let _attribute = { ...props.extended_widget_opts };
    if (
      _attribute["roles"] == undefined ||
      (_attribute["roles"] != undefined && _attribute["roles"].length == 0)
    ) {
      _attribute["roles"] = {};
    }

    _attribute["roles"]["selected"] = event.target.value;
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

  const checkState = (widgetoptsState) => {
    if (widgetoptsState != undefined && widgetoptsState == "activate") {
      return (
        <div>
          <p
            className="widgetopts-subtitle"
            style={{ "padding-top": "0", "margin-top": "10px" }}
          >
            {__("User Login State")}
          </p>
          <p style={{ "margin-bottom": "0px" }}>
            <small>
              {__(
                "Restrict widget visibility for logged-in and logged-out users. "
              )}
            </small>
          </p>
          <p>
            <select
              className="widefat"
              name={"extended_widget_opts[roles][state]"}
              onChange={handleInputChangeState}
              value={
                props.extended_widget_opts["roles"] != undefined
                  ? props.extended_widget_opts["roles"]["state"]
                  : ""
              }
            >
              <option value="">{__("Select Visibility Option")}</option>
              <option value="in">{__("Show only for Logged-in Users")}</option>
              <option value="out">
                {__("Show only for Logged-out Users")}
              </option>
            </select>
          </p>
        </div>
      );
    } else {
      return "";
    }
  };

  const checkRoles = (widgetoptsRoles) => {
    return (
      <div>
        {checkLicense(props.validLicense)}

        <p style={{ "margin-top": "30px" }} className="widgetopts-subtitle">
          {__("User Roles")}
        </p>
        <p>
          <small>{__("Restrict widget visibility per user roles.")}</small>
        </p>

        <p>
          <strong>{__("Hide/Show")}</strong>
          <select
            className="widefat"
            name={"extended_widget_opts[roles][options]"}
            value={""}
          >
            <option value="hide">{__("Hide on searched roles")}</option>
            <option value="show">{__("Show on searched roles")}</option>
          </select>
        </p>

        <div
          className="extended-widget-opts-inner-roles"
          style={{
            maxHeight: "230px",
            padding: "5px 0px",
            overflow: "auto",
          }}
        >
          <p className="extended-widget-opts-parent-option">
            <strong>{__("Roles")}</strong>
            <br />
            <small>{__("Search for Roles")}</small>
            <br />
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
              className="widefat widgetopts-select2 extended-widget-opts-roles-dropdown extended-widget-opts-select2-dropdown"
              name={"extended_widget_opts[roles][selected][]"}
              data-namespace={"extended_widget_opts-" + props.widgetId + ""}
              multiple="multiple"
            ></select>
          </p>
        </div>
      </div>
    );
  };

  const checkSelectedRoles = (role, id, roleInfo) => {
    if (role != undefined && role.includes(id)) {
      return (
        <option value={id} selected>
          {roleInfo.text}
        </option>
      );
    } else {
      return <option value={id}>{roleInfo.text}</option>;
    }
  };

  return (
    <div
      id={"extended-widget-opts-tab-" + props.widgetId + "-roles"}
      className={
        "extended-widget-opts-tabcontent extended-widget-opts-tabcontent-roles "
      }
      style={{ "margin-left": "16px" }}
    >
      {checkState(widget_options["state"])}

      <div className={validLicenseClass}>
        {checkRoles(widget_options["roles"])}
      </div>
    </div>
  );
};

export default RolesTabPanel;
