/*
 * Block Editor Toolbar
 *
 * Copyright (c) 2023 Boholweb WP
 *
 */

/* Add widget options attribute to all existing blocks, in Toolbar */

const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { BlockControls, InspectorControls } = wp.blockEditor;
// const { ToolbarGroup, ToolbarButton, ToolbarDropdownMenu, Toolbar } =
//   wp.components;

import {
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  __experimentalText as Text,
  __experimentalHeading as Heading,
  ToolbarDropdownMenu,
} from "@wordpress/components";

import classnames from "classnames";
import { more, alignCenter, grid } from "@wordpress/icons";

/**
 * Add Widget Options Button to Toolbar
 */
const toolbarButton = createHigherOrderComponent((BlockEdit) => {
  return (props) => {
    const { attributes, setAttributes } = props;
    const { paragraphAttribute } = attributes;

    return (
      <Fragment>
        <BlockControls group="block">
          <ToolbarDropdownMenu
            icon="welcome-widgets-menus"
            label="Widget Options"
            controls={[
              {
                title: "Columns",
                icon: grid,
                onClick: () => console.log("Columns"),
              },
              {
                title: "Alignment",
                icon: alignCenter,
                onClick: () => console.log("Alignment"),
              },
              {
                title: "Role",
                icon: "admin-users",
                onClick: () => console.log("Role"),
              },
              {
                title: "Visibility",
                icon: "visibility",
                onClick: () => console.log("Visibility"),
              },
              {
                title: "Devices",
                icon: "smartphone",
                onClick: () => console.log("Devices"),
              },
              {
                title: "Days & Dates",
                icon: "calendar-alt",
                onClick: () => console.log("Days & Dates"),
              },
              {
                title: "Styling",
                icon: "art",
                onClick: () => console.log("Styling"),
              },
              {
                title: "Class,ID & Logic",
                icon: "admin-generic",
                onClick: () => console.log("Class,ID & Logic"),
              },
            ]}
          />
        </BlockControls>
        <BlockEdit {...props} />
      </Fragment>
    );
  };
}, "toolbarButton");

wp.hooks.addFilter(
  "editor.BlockEdit",
  "widgetopts-attributes/toolbar-button",
  toolbarButton
);
