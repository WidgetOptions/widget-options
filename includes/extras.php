<?php
/**
 * Extra Functions
 *
 * Collections of extra functions to avoid repeatition
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       4.0
 */

function widgetopts_sanitize_array( &$array ) {
    foreach ($array as &$value) {
        if( !is_array($value) ) {
			// sanitize if value is not an array
            $value = sanitize_text_field( $value );
		}else{
			// go inside this function again
            widgetopts_sanitize_array($value);
		}
    }

    return $array;
}

function widgetopts_is_checked( $array, $key ){
	return ( isset( $array[$key] ) && '1' == $array[$key] ) ? 'checked="checked"' : '';
}

/*
 * Check if http or https available on link
 */
function widgetopts_addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}


/**
 * Register Global Variables for easier access
 *
 *
 * @since 5.0
 * @return array
 */

function widgetopts_global_pages() {
	$pages = get_option( 'widgetopts_global_pages' );

	if( empty( $pages ) ) {
        $pages  = get_posts( array(
                                'post_type'     => 'page',
                                'post_status'   => 'publish',
                                'numberposts'   => -1,
                                'orderby'       => 'title',
                                'order'         => 'ASC',
                                'fields'        => array('ID', 'name')
                            ));
	}

	return apply_filters( 'widgetopts_get_global_pages', $pages );
}
?>
