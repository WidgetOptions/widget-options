<?php
/**
 * Extras Module
 * Settings > Widget Options :: Extras
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       4.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extras Module for Widget Area Options
 *
 * @since 4.5
 * @global $widget_options
 * @return void
 */
if( !function_exists( 'widgetopts_settings_gutenberg' ) ):
	function widgetopts_settings_gutenberg(){
	    global $widget_options;
	    //avoid issue after update
	    if( !isset( $widget_options['gutenberg'] ) ){
	        $widget_options['gutenberg'] = '';
	    }

		$gutenberg = ( isset( $widget_options['settings']['gutenberg'] ) ) ? $widget_options['settings']['gutenberg'] : array();?>
	    <li class="widgetopts-module-card <?php echo ( $widget_options['gutenberg'] == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-gutenberg" data-module-id="gutenberg">
			<div class="widgetopts-module-card-content">
				<h2><?php _e( 'Gutenberg Block Options', 'widget-options' );?></h2>
				<div class="widgetopts-pro-label"><?php _e( 'BETA', 'widget-options' );?></div>
				<p class="widgetopts-module-desc">
					<?php _e( 'Manage your blocks better using Block Options. Added on each Block tab and easily accessible.', 'widget-options' );?>
				</p>

				<div class="widgetopts-module-actions hide-if-no-js">
					<?php if( $widget_options['gutenberg'] == 'activate' ){ ?>
						<button class="button button-secondary widgetopts-toggle-settings"><?php _e( 'Configure Settings', 'widget-options' );?></button>
						<button class="button button-secondary widgetopts-toggle-activation"><?php _e( 'Disable', 'widget-options' );?></button>
					<?php }else{ ?>
						<button class="button button-secondary widgetopts-toggle-settings"><?php _e( 'Learn More', 'widget-options' );?></button>
						<button class="button button-primary widgetopts-toggle-activation"><?php _e( 'Enable', 'widget-options' );?></button>
					<?php } ?>

				</div>
			</div>

			<?php widgetopts_modal_start( $widget_options['gutenberg'] ); ?>
				<span class="dashicons widgetopts-dashicons widgetopts-no-top dashicons-edit"></span>
				<h3 class="widgetopts-modal-header"><?php _e( 'Gutenberg Block Options', 'widget-options' );?></h3>
				<p>
					<?php _e( 'Enable this <strong>Block Options</strong> to help you better manage you Gutenberg blocks. This will give you visibility restriction per devices and ability to set conditional display logic tags.', 'widget-options' );?>
				</p>

				<table class="form-table widgetopts-settings-section">
					<tr>
						<th scope="row">
							<label for="widgetopts-gutenberg-opened"><?php _e( 'Show Options', 'widget-options' );?></label>
						</th>
						<td>
							<input type="checkbox" id="widgetopts-gutenberg-opened" name="gutenberg[opened]" <?php echo widgetopts_is_checked( $gutenberg, 'opened' ) ?> value="1" />
							<label for="widgetopts-gutenberg-opened"><?php _e( 'Enable Initial Open', 'widget-options' );?></label>
							<p class="description">
								<?php _e( 'Check this option if you want block options expanded automatically.', 'widget-options' );?>
							</p>
						</td>
					</tr>
				</table>
			<?php widgetopts_modal_end( $widget_options['gutenberg'] ); ?>

		</li>
	    <?php
	}
	add_action( 'widgetopts_module_cards', 'widgetopts_settings_gutenberg', 204 );
endif;
?>
