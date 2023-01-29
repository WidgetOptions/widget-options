<?php
/**
 * Add values to global variables
 *
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       3.3.1
 */

if( !function_exists( 'widgetopts_register_globals' ) ){
    add_action( 'init', 'widgetopts_register_globals', 90 );
    function widgetopts_register_globals(){
        global $widgetopts_taxonomies, $widgetopts_types, $widgetopts_categories;

        $widgetopts_taxonomies 	= widgetopts_global_taxonomies();
        $widgetopts_types 		= widgetopts_global_types();
        $widgetopts_categories 	= widgetopts_global_categories();

    }
}
?>
