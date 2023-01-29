<?php
/**
 * Support Sidebar Metabox
 * Settings > Widget Options
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       4.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create Metabox for Support
 *
 * @since 4.0
 * @return void
 */
if( !function_exists( 'widgetopts_settings_support_box' ) ):
	function widgetopts_settings_support_box(){ ?>
		<div id="widgetopts-sidebar-widget-support" class="postbox widgetopts-sidebar-widget">
			<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Need Help on Managing Widgets?', 'widget-options' );?></span></h3>
			<div class="inside">
				<p>
					<?php _e( 'Since you are using the free version, you can get the free support on WordPress.org community forums. Thanks!', 'widget-options' );?>
				</p>
				<p>
					<a class="button-secondary" href="https://wordpress.org/support/plugin/widget-options/" target="_blank"><?php _e( 'Get Free Support', 'widget-options' );?></a>
				</p>
				<p>
					<?php _e( 'Manage your widgets better and get fast professional support by upgrading to Extended Widget Options.', 'widget-options' );?>
				</p>
				<p>
					<a class="button-secondary" href="http://widget-options.com/?utm_source=wordpressadmin&amp;utm_medium=widget&amp;utm_campaign=widgetoptsprocta" target="_blank"><?php _e( 'Get Extended Widget Options', 'widget-options' );?></a>
				</p>
				<p>
					<?php _e( 'You can also join our community via Facebook Group.', 'widget-options' );?>
				</p>
				<p>
					<a class="button-secondary" href="https://www.facebook.com/groups/WPwidgets/" target="_blank"><?php _e( 'Join Facebook Group', 'widget-options' );?></a>
				</p>
			</div>
		</div>

	    <?php
	}
	add_action( 'widgetopts_module_sidebar', 'widgetopts_settings_support_box', 30 );
endif;
?>
