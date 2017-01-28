<?php
/**
 * AJAX Functions
 *
 * Process AJAX actions.
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       4.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Save Options
 *
 * @since 1.0
 * @return void
 */
function widgetopts_ajax_save_settings(){
    $response = array( 'errors' => array() );

	if( !isset( $_POST['method'] ) ) return;
	if( !isset( $_POST['nonce'] ) ) return;

	if ( ! wp_verify_nonce( $_POST['nonce'], 'widgetopts-settings-nonce' ) ) {
		return;
	}

	switch ( $_POST['method'] ) {
		case 'activate':
		case 'deactivate':
				if( !isset( $_POST['module'] ) ) return;

				//update options
				update_option( 'widgetopts_tabmodule-' . sanitize_text_field( $_POST['module'] ), sanitize_text_field( $_POST['method'] ) );

				//update global variable
				widgetopts_update_option( sanitize_text_field( $_POST['module'] ), sanitize_text_field( $_POST['method'] ) );
			break;

		case 'save':
				$response['messages'] = array( __( 'Settings saved successfully.', 'widget-options' ) );
				if( !isset( $_POST['data'] ) ) return;
				parse_str( $_POST['data']['--widgetopts-form-serialized-data'], $params );
				$sanitized = widgetopts_sanitize_array( $params );
				update_option( 'widgetopts_tabmodule-settings', maybe_serialize( $sanitized ) );

				//reset options
				widgetopts_update_option( 'settings', $sanitized );
			break;

		default:
			# code...
			break;
	}
	$response['source'] 	= 'WIDGETOPTS_Response';
	$response['response'] 	= 'success';
	$response['closeModal'] = true;
	$response 				= (object) $response;

	//let devs do there action
	do_action( 'widget_options_before_ajax_print', sanitize_text_field( $_POST['method'] ) );

	echo json_encode( $response );
	die();
}
add_action( 'wp_ajax_widgetopts_ajax_settings',  'widgetopts_ajax_save_settings' );

/* Hide the rating div
 * @return json string
 *
 */
if( !function_exists( 'widgetopts_ajax_hide_rating' ) ):
	function widgetopts_ajax_hide_rating(){
	    update_option('widgetopts_RatingDiv','yes');
	    echo json_encode(array("success")); exit;
	}
	add_action( 'wp_ajax_widgetopts_hideRating', 'widgetopts_ajax_hide_rating' );
endif;
?>
