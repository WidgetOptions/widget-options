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

function widgetopts_settings_support_box(){ ?>
    <div id="widgetopts-sidebar-widget-support" class="postbox widgetopts-sidebar-widget">
		<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Need Help on Managing Widgets?', 'widget-options' );?></span></h3>
		<div class="inside">
			<p>
				<?php _e( 'In need of any assistance or having issue using Extended Widget Options Plugin? We are very happy to help you out, just click the button below and we will answer your concerns professionally. Thanks!', 'widget-options' );?>
			</p>
			<p>
				<a class="button-secondary" href="https://phpbits.net/contact/?utm_source=wordpressadmin&amp;utm_medium=widget&amp;utm_campaign=widgetoptssupportcta" target="_blank"><?php _e( 'Open Support Ticket', 'widget-options' );?></a>
			</p>
		</div>
	</div>
    <?php
}
add_action( 'widgetopts_module_sidebar', 'widgetopts_settings_support_box', 20 );
?>
