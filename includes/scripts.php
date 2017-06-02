<?php
/**
 * Scripts
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 3.0
 * @return void
 */

function widgetopts_load_scripts(){
	$css_dir = WIDGETOPTS_PLUGIN_URL . 'assets/css/';
      wp_enqueue_style( 'widgetopts-styles', $css_dir . 'widget-options.css' , array(), null );
}
add_action( 'wp_enqueue_scripts', 'widgetopts_load_scripts' );
add_action( 'customize_controls_enqueue_scripts', 'widgetopts_load_scripts' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 3.0
 * @global $widget_options
 * @param string $hook Page hook
 * @return void
 */
if( !function_exists( 'widgetopts_load_admin_scripts' ) ):
      function widgetopts_load_admin_scripts( $hook ) {

            $js_dir  = WIDGETOPTS_PLUGIN_URL . 'assets/js/';
      	$css_dir = WIDGETOPTS_PLUGIN_URL . 'assets/css/';

            // Use minified libraries if SCRIPT_DEBUG is turned off
      	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

            wp_enqueue_style( 'widgetopts-admin-styles', $css_dir . 'admin.css' , array(), null );

            if( !in_array( $hook, apply_filters( 'widgetopts_exclude_jqueryui', array( 'toplevel_page_et_divi_options' ) ) ) ){
                  wp_enqueue_style( 'widgetopts-jquery-ui', $css_dir . 'jqueryui/1.11.4/themes/ui-lightness/jquery-ui.css' , array(), null );
                  wp_enqueue_style( 'jquery-ui' );
            }

            if( in_array( $hook, apply_filters( 'widgetopts_load_liveFilter_scripts', array( 'widgets.php' ) ) ) ){
                  wp_enqueue_script(
                       'jquery-liveFilter',
                       plugins_url( 'assets/js/jquery.liveFilter.js' , dirname(__FILE__) ),
                       array( 'jquery' ),
                       '',
                       true
                  );
            }

            wp_enqueue_script(
                 'jquery-widgetopts-option-tabs',
                 plugins_url( 'assets/js/widgets.js' , dirname(__FILE__) ),
                 array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'jquery-ui-datepicker'),
                 '',
                 true
            );

            $form = '<div id="widgetopts-widgets-chooser">
        	<label class="screen-reader-text" for="widgetopts-search-chooser">'. __( 'Search Sidebar', 'widget-options' ) .'</label>
        	<input type="text" id="widgetopts-search-chooser" class="widgetopts-widgets-search" placeholder="'. __( 'Search sidebar&hellip;', 'widget-options' ) .'" />
            <div class="widgetopts-search-icon" aria-hidden="true"></div>
            <button type="button" class="widgetopts-clear-results"><span class="screen-reader-text">'. __( 'Clear Results', 'widget-options' ) .'</span></button>
            <p class="screen-reader-text" id="widgetopts-chooser-desc">'. __( 'The search results will be updated as you type.', 'widget-options' ) .'</p>
        </div>';

            wp_localize_script( 'jquery-widgetopts-option-tabs', 'widgetopts10n', array( 'opts_page' => esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) ), 'search_form' => $form, 'translation' => array( 'manage_settings' => __( 'Manage Widget Options', 'widget-options' ), 'search_chooser' => __( 'Search sidebar&hellip;', 'widget-options' ) )) );

            if( in_array( $hook, apply_filters( 'widgetopts_load_settings_scripts', array( 'settings_page_widgetopts_plugin_settings' ) ) ) ){
                  wp_register_script(
                        'jquery-widgetopts-settings',
                        $js_dir .'settings'. $suffix .'.js',
                        array( 'jquery' ),
                        '',
                        true
                  );

                  $translation = array(
                        'save_settings'         => __( 'Save Settings', 'widget-options' ),
                        'close_settings'        => __( 'Close', 'widget-options' ),
                        'show_settings'         => __( 'Configure Settings', 'widget-options' ),
                        'hide_settings'         => __( 'Hide Settings', 'widget-options' ),
                        'show_description'      => __( 'Learn More', 'widget-options' ),
                        'hide_description'      => __( 'Hide Details', 'widget-options' ),
                        'show_information'      => __( 'Show Details', 'widget-options' ),
                        'activate'              => __( 'Enable', 'widget-options' ),
                        'deactivate'            => __( 'Disable', 'widget-options' ),
                        'successful_save'       => __( 'Settings saved successfully for %1$s.', 'widget-options' ),
                        'deactivate_btn'        => __( 'Deactivate License', 'widget-options' ),
                        'activate_btn'          => __( 'Activate License', 'widget-options' ),
                        'status_valid' 		=> __( 'Valid', 'widget-options' ),
                        'status_invalid'        => __( 'Invalid', 'widget-options' ),
                  );

                  wp_enqueue_script( 'jquery-widgetopts-settings' );
                  wp_localize_script( 'jquery-widgetopts-settings', 'widgetopts', array( 'translation' => $translation, 'ajax_action' => 'widgetopts_ajax_settings', 'ajax_nonce' => wp_create_nonce( 'widgetopts-settings-nonce' ), ) );
            }
      }
      add_action( 'admin_enqueue_scripts', 'widgetopts_load_admin_scripts', 100 );
endif;
?>
