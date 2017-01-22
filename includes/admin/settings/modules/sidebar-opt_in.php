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
if( !function_exists( 'widgetopts_settings_sidebar_opt_in' ) ):
	function widgetopts_settings_sidebar_opt_in(){ ?>
		<div id="widgetopts-sidebar-widget-optin" class="postbox widgetopts-sidebar-widget">
			<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Get Widget Management Tips', 'widget-options' );?></span></h3>
			<div class="inside">
				<form action="https://phpbits.us12.list-manage.com/subscribe/post?u=5597485458f20da5305e44c55&amp;id=65b77b0af4" method="post" class="validate" target="_blank" novalidate>
					<p>
						<?php _e( 'Get additional tips and resources on how to manage your widgets better + latest news and release updates of Widget Options plugin.', 'widget-options' );?>
					</p>
					<p>
						<?php _e( 'Email Address:', 'widget-options' );?><br  />
						<input name="EMAIL" class="required email widefat" placeholder="<?php _e( 'email@domain.com', 'widget-options' );?>" />
					</p>
					<p>
						<button class="button-secondary"><?php _e( 'Subscribe', 'widget-options' );?></button>
					</p>
				</form>
			</div>
		</div>

	    <?php
	}
	add_action( 'widgetopts_module_sidebar', 'widgetopts_settings_sidebar_opt_in', 20 );
endif;
?>
