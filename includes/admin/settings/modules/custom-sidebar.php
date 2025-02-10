<?php

/**
 * Widget Area Options Settings Module
 * Settings > Widget Options :: Widget Area Options
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       4.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Create Card Module for Widget Area Options
 *
 * @since 4.2
 * @global $widget_options
 * @return void
 */
if (!function_exists('widgetopts_settings_custom_sidebar')) :
	function widgetopts_settings_custom_sidebar()
	{

?>
		<li class="widgetopts-module-card widgetopts-module-type-pro" id="widgetopts-module-card-custom_sidebar" data-module-id="custom_sidebar">
			<div class="widgetopts-module-card-content">
				<a href="<?php echo apply_filters('widget_options_site_url', trailingslashit(WIDGETOPTS_PLUGIN_WEBSITE) . '');
							?>" target="_blank" class="widgetopts-pro-upsell"></a>
				<h2><?php _e('Custom Widget Area', 'widget-options'); ?></h2>
				<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
				<p class="widgetopts-module-desc">
					<?php _e('Allows to create a dedicated back-end container for your widgets.', 'widget-options'); ?>
				</p>
			</div>
		</li>
<?php
	}
	add_action('widgetopts_module_cards', 'widgetopts_settings_custom_sidebar', 69);
endif;
?>