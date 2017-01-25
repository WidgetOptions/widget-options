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
if( !function_exists( 'widgetopts_settings_more_plugins' ) ):
	function widgetopts_settings_more_plugins(){ ?>
		<div id="widgetopts-sidebar-widget-more_plugins" class="postbox widgetopts-sidebar-widget">
			<h3 class="hndle ui-sortable-handle"><span><?php _e( 'More Free Plugins', 'widget-options' );?></span></h3>
			<div class="inside">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<td scope="row">
								<a href="https://wordpress.org/plugins/forty-four/" target="_blank">
									<span class="dashicons dashicons-editor-unlink"></span><br />
									<?php _e( 'Forty Four - 404 Page and 301 SEO Redirection Plugin', 'widget-options' );?>
								</a>
							</td>
							<td scope="row">
								<a href="https://wordpress.org/plugins/easy-profile-widget/" target="_blank">
									<span class="dashicons dashicons-admin-users"></span><br />
									<?php _e( 'Easy Profile Info Widget Plugin', 'widget-options' );?>
								</a>
							</td>
						</tr>
						<tr valign="top">
							<td scope="row">
								<a href="https://wordpress.org/plugins/wp-image-hover-lite/" target="_blank">
									<span class="dashicons dashicons-share"></span><br />
									<?php _e( 'Social Icons Image Hover', 'widget-options' );?>
								</a>
							</td>
							<td scope="row">
								<a href="https://wordpress.org/plugins/wp-author-box-lite/" target="_blank">
									<span class="dashicons dashicons-nametag"></span><br />
									<?php _e( 'Author Box Plugin', 'widget-options' );?>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
				<p>
					<a class="button-secondary" href="https://profiles.wordpress.org/phpbits/#content-plugins" target="_blank"><?php _e( 'View all Free Plugins', 'widget-options' );?></a>
				</p>
			</div>
		</div>

	    <?php
	}
	add_action( 'widgetopts_module_sidebar', 'widgetopts_settings_more_plugins', 25 );
endif;
?>
