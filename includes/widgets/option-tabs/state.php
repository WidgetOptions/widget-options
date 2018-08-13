<?php
/**
 * Roles Widget Options
 *
 * @copyright   Copyright (c) 2015, Jeffrey Carandang
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Roles Widget Options Tab
 *
 * @since 1.0
 * @return void
 */

 /**
 * Called on 'extended_widget_opts_tabs'
 * create new tab navigation for alignment options
 */
function widgetopts_tab_state( $args ){ ?>
    <li class="extended-widget-opts-tab-roles">
        <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-roles" title="<?php _e( 'Roles', 'widget-options' );?>" ><span class="dashicons dashicons-admin-users"></span> <span class="tabtitle"><?php _e( 'Roles', 'widget-options' );?></span></a>
    </li>
<?php
}
add_action( 'extended_widget_opts_tabs', 'widgetopts_tab_state' );

/**
 * Called on 'extended_widget_opts_tabcontent'
 * create new tab content options for alignment options
 */
function widgetopts_tabcontent_state( $args ){
    $roles          = get_editable_roles();
    $state   = '';
    if( isset( $args['params']['roles'][ 'state' ] ) ){
        $state = $args['params']['roles'][ 'state' ];
    }
    ?>
    <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-roles" class="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-roles">
        <p class="widgetopts-subtitle"><?php _e( 'User Login State', 'widget-options' );?></p>
        <p>
            <select class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][roles][state]">
                <option value=""><?php _e( 'Select Visibility Option', 'widget-options' );?></option>
                <option value="in" <?php if( $state == 'in' ){ echo 'selected="selected"'; }?> ><?php _e( 'Show only for Logged-in Users', 'widget-options' );?></option>
                <option value="out" <?php if( $state == 'out' ){ echo 'selected="selected"'; }?>><?php _e( 'Show only for Logged-out Users', 'widget-options' );?></option>
            </select>
        </p>
        <p><small><?php _e( 'Restrict widget visibility for logged-in and logged-out users. ', 'widget-options' );?></small></p> 
    </div>
<?php
}
add_action( 'extended_widget_opts_tabcontent', 'widgetopts_tabcontent_state'); ?>
