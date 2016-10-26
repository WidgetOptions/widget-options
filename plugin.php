<?php

/*
Plugin Name: Widget Options
Plugin URI: https://wordpress.org/plugins/widget-options
Description: Additional Widget options for better widget control. Get <strong><a href="https://phpbits.net/plugin/extended-widget-options/" target="_blank" >Extended Widget Options for WordPress</a></strong> for complete widget controls. Thanks!
Version: 3.0
Author: Phpbits Creative Studio
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
require_once( dirname( __FILE__ ) . '/core/functions.new.settings.php');
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

		if( !get_option( '_widgetopts_default_registered_' ) ){
			//activate free version modules
			add_option( 'widgetopts_tabmodule-visibility', 'activate' );
			add_option( 'widgetopts_tabmodule-devices', 'activate' );
			add_option( 'widgetopts_tabmodule-alignment', 'activate' );
			add_option( 'widgetopts_tabmodule-hide_title', 'activate' );
			add_option( 'widgetopts_tabmodule-classes', 'activate' );
			add_option( 'widgetopts_tabmodule-logic', 'activate' );

			//add free version settings
			$defaults = array(
					'visibility' 	=> 	array(
						'post_type'		=> '1',
						'taxonomies'	=> '1',
						'misc'			=> '1'
					),
					'classes' 		=> 	array(
						'id'			=> '1',
						'type'			=> 'both'
					),
			);

			//upgraded settings from previous version
			$options    = get_option('extwopts_class_settings');
			if( isset( $options['class_field'] ) ){
				$defaults['classes']['type'] = $options['class_field'];
			}
			if( isset( $options['classlists'] ) ){
				$defaults['classes']['classlists'] = $options['classlists'];
			}

			add_option( 'widgetopts_tabmodule-settings', serialize( $defaults ) );

			add_option( '_widgetopts_default_registered_', '1' );
		}
	}
}

//add settings link on plugin page
if( !function_exists( 'extended_widget_opts_filter_plugin_actions' ) ){
  add_action( 'plugin_action_links_' . plugin_basename(__FILE__) , 'extended_widget_opts_filter_plugin_actions' );
  function extended_widget_opts_filter_plugin_actions($links){
    $links[]  = '<a href="'. esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) ) .'">' . __( 'Settings', 'extended-widget-options' ) . '</a>';
    return $links;
  }
}
