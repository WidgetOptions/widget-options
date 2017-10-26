<?php
/**
 * Widget Area Settings Module
 * Settings > Widget Options :: Widget Area
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       3.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create Card Module for Widget Area
 *
 * @since 3.5
 * @return void
 */
if( !function_exists( 'widgetopts_settings_widget_area' ) ):
	function widgetopts_settings_widget_area(){ ?>
		<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="widget_area">
			<div class="widgetopts-module-card-content">
				<a href="https://widget-options.com/features/widget-area-options/" target="_blank" class="widgetopts-pro-upsell"></a>
				<h2><?php _e( 'Widget Area Options', 'widget-options' );?></h2>
				<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
				<p class="widgetopts-module-desc">
					<?php _e( 'Extra helpful management options below each sidebar widget areas.', 'widget-options' );?>
				</p>
			</div>
		</li>
	    <?php
	}
	add_action( 'widgetopts_module_cards', 'widgetopts_settings_widget_area', 176 );
endif;
?>
