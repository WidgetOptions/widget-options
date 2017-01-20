<?php
/**
 * Purchase Key Validation Sidebar Metabox
 * Settings > Widget Options
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       4.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create Metabox for Purchase Validation
 *
 * @since 4.0
 * @return void
 */
if( !function_exists( 'widgetopts_settings_validation_form' ) ):
	function widgetopts_settings_validation_form(){
	    // $status 		= get_option( 'widgetopts_license_status' );
	    // $license_key 	= get_option( 'widgetopts_license_key' );
	    // $license 		= (array) get_option( 'widgetopts_license_data' );
		$item_shortname = 'widgetopts_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( WIDGETOPTS_PLUGIN_NAME ) ) );
		$license_data 	= get_option( $item_shortname . '_license_active' );
		$formdata 		= get_option( 'widgetopts_license_keys' );
		$now 			= date('Y-m-d H:i:s');
		// print_r( $license_data );
		?>
	    <div id="widgetopts-sidebar-widget-purchase_validation" class="postbox widgetopts-sidebar-widget" style="border-color: #ffb310; border-width: 2px;">
			<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Validate License', 'widget-options' );?></span></h3>
			<div class="inside">
				<form enctype="multipart/form-data" method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) );?>" id="widgetopts-module-settings-license">
					<?php wp_nonce_field( 'widgetopts_license_nonce', 'widgetopts_license_nonce_field' ); ?>
					<p>
						<?php _e( 'Validate your purchase license code here for automatic updates access.', 'widget-options' );?>
					</p>
					<p>
						<strong><?php _e( 'Extended Widget Options', 'widget-options' );?></strong>
					</p>
					<table class="form-table widgetopts-settings-license">
						<tbody>
							<tr>
								<td colspan="2">
									<input type="text" class="widefat widgetopts-license-key" id="widgetopts-license-extended" placeholder="<?php _e( 'Add License Key', 'widget-options' );?>" name="widgetopts_license_settings[extended]" value="<?php echo ( is_array( $formdata ) && isset( $formdata['extended'] ) ) ? $formdata['extended'] : ''; ?>" />
								</td>
							</tr>
							<tr class="widgetopts-even widgetopts-license-extended-response">
								<td scope="row" class="td-left">
									<?php if( !empty( $license_data ) && is_object( $license_data ) && isset( $license_data->license ) && $license_data->license == 'valid' ){
										_e( 'Valid until ' . date( 'M d, Y', strtotime( $license_data->expires ) ), 'widget-options' );
									} ?>
								</td>
								<td class="td-right">
									<?php if( !empty( $license_data ) && is_object( $license_data ) && isset( $license_data->license ) && $license_data->license == 'valid' ){ ?>
										<button class="button button-secondary widgetopts-license_deactivate" id="widgetopts-license-btn-extended" data-target="widgetopts-license-extended"><?php _e( 'Deactivate', 'widget-options' ); ?></button>
									<?php } ?>
								</td>
							</tr>
							<?php do_action( 'widgetopts_settings_license_form', $formdata );?>
						</tbody>
					</table>
					<p>
						<button class="button button-primary" type="submit"><?php _e( 'Activate License', 'widget-options' ); ?></button>
					</p>
				</form>
			</div>
		</div>
	    <?php
	}
	add_action( 'widgetopts_module_sidebar', 'widgetopts_settings_validation_form', 10 );
endif;

if( !function_exists( 'widgetopts_settings_purchase_validation' ) ):
	function widgetopts_settings_purchase_validation(){
		global $extended_license;
		if( !isset( $_POST['widgetopts_license_settings'] ) || !isset( $_POST['widgetopts_license_nonce_field'] ) ){
			return;
		}

		if( ! wp_verify_nonce( $_POST['widgetopts_license_nonce_field'], 'widgetopts_license_nonce' ) ) {

			wp_die( __( 'Nonce verification failed', 'widget-options' ), __( 'Error', 'widget-options' ), array( 'response' => 403 ) );

		}

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$optiondata = get_option( 'widgetopts_license_keys' );
		$postdata 	= widgetopts_sanitize_array( $_POST['widgetopts_license_settings'] );

		if( is_array( $postdata ) && !empty( $postdata ) && isset( $postdata['extended'] ) ){
			$item_shortname = 'widgetopts_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( WIDGETOPTS_PLUGIN_NAME ) ) );
			$details 		= get_option( $item_shortname . '_license_active' );

			if ( is_object( $details ) && 'valid' === $details->license ) {
				//reset if already valid
				$postdata['extended'] = $optiondata['extended'];
			}else{
				$extended_license->activate_license( $postdata['extended'], sanitize_text_field( $_POST['widgetopts_license_nonce_field'] ) );
				update_option( $item_shortname . '_license_key', $postdata['extended'] );
			}
		}

		do_action( 'widgetopts_settings_license_activation', $postdata );

		update_option( 'widgetopts_license_keys', $postdata );

	}
	add_action( 'admin_init', 'widgetopts_settings_purchase_validation' );
endif;
?>
