<?php

/*
Plugin Name: Widget Options
Plugin URI: https://wordpress.org/plugins/widget-options
Description: Additional Widget options for better widget control. Get <strong><a href="https://phpbits.net/plugin/extended-widget-options/" target="_blank" >Extended Widget Options for WordPress</a></strong> for complete widget controls. Thanks!
Version: 2.0.1
Author: phpbits
Author URI: https://phpbits.net/
Text Domain: widget-options
*/

//avoid direct calls to this file
if ( !function_exists( 'add_action' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/*##################################
	REQUIRE
################################## */
require_once( dirname( __FILE__ ) . '/core/functions.widget.opts.php');
require_once( dirname( __FILE__ ) . '/core/functions.option.tabs.php');
require_once( dirname( __FILE__ ) . '/core/functions.widget.display.php');
require_once( dirname( __FILE__ ) . '/core/functions.notices.php');
require_once( dirname( __FILE__ ) . '/core/functions.settings.php');
require_once( dirname( __FILE__ ) . '/core/function.transient.php');

/**
 * Install
 *
 * Runs on plugin install to populates the settings fields for those plugin
 * pages.
 */
if( !function_exists( 'widgetopts_install' ) ){
	register_activation_hook( __FILE__, 'widgetopts_install' );
	function widgetopts_install() {
		if( !get_option( 'widgetopts_installDate' ) ){
			add_option( 'widgetopts_installDate', date( 'Y-m-d h:i:s' ) );
		}
	}
}
