<?php
/**
 * Install Function
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       3.0
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//add settings link on plugin page
if( !function_exists( 'widgetopts_filter_plugin_actions' ) ){
  add_action( 'plugin_action_links_' . plugin_basename( WIDGETOPTS_PLUGIN_FILE ) , 'widgetopts_filter_plugin_actions' );
  function widgetopts_filter_plugin_actions($links){
    $links[]  = '<a href="'. esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) ) .'">' . __( 'Settings', 'widget-options' ) . '</a>';
    return $links;
  }
}

//register default values
if( !function_exists( 'widgetopts_register_defaults' ) ){
	register_activation_hook( WIDGETOPTS_PLUGIN_FILE, 'widgetopts_register_defaults' );
  	add_action( 'plugins_loaded', 'widgetopts_register_defaults' );
	function widgetopts_register_defaults(){
		if( is_admin() ){

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
				delete_transient( 'widgetopts_tabs_transient' ); //remove transient for settings
			}

		}
	}
}

?>
