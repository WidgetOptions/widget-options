<?php

/*
Plugin Name: Widget Options
Plugin URI: https://wordpress.org/plugins/widget-options
Description: Additional Widget options for better widget control. Get <strong><a href="http://codecanyon.net/item/extended-widget-options-for-wordpress/14024086?ref=phpbits">Extended Widget Options for WordPress</a></strong> for complete widget controls. Thanks!
Version: 1.3
Author: phpbits
Author URI: http://codecanyon.net/user/phpbits/portfolio?ref=phpbits
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
