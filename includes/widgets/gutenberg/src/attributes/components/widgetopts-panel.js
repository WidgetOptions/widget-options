import { TabPanel } from "@wordpress/components";
import ColumsTabPanel from "./option-tabs/columns";
import AlignmentTabPanel from "./option-tabs/alignment";
import RolesTabPanel from "./option-tabs/roles";
import VisibilityTabPanel from "./option-tabs/visibility";
import DevicesTabPanel from "./option-tabs/devices";
import DatesTabPanel from "./option-tabs/days-dates";
import StylingTabPanel from "./option-tabs/styling";
import SettingsTabPanel from "./option-tabs/settings";
import AnimationTabPanel from "./option-tabs/animation";
import BehaviorTabPanel from "./option-tabs/behavior";

const onSelect = (tabName) => {};

const WidgetOptionsPanel = (props) => {
  const tabName = props.tabName;

  const checkActiveTab = () => {
    let activeTab =
      tabName == "columns" ? (
        <ColumsTabPanel {...props} />
      ) : tabName == "alignment" ? (
        <AlignmentTabPanel {...props} />
      ) : tabName == "role" ? (
        <RolesTabPanel {...props} />
      ) : tabName == "visibility" ? (
        <VisibilityTabPanel {...props} />
      ) : tabName == "devices" ? (
        <DevicesTabPanel {...props} />
      ) : tabName == "dates" ? (
        <DatesTabPanel {...props} />
      ) : tabName == "styling" ? (
        <StylingTabPanel {...props} />
      ) : tabName == "logic" ? (
        <SettingsTabPanel {...props} />
      ) : tabName == "animation" ? (
        <AnimationTabPanel {...props} />
      ) : tabName == "behavior" ? (
        <BehaviorTabPanel {...props} />
      ) : (
        <div>
          <h3>{props.tabName} Loading...</h3>
        </div>
      );
    return activeTab;
  };

  return checkActiveTab();
};

export default WidgetOptionsPanel;
