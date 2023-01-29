<?php
/**
 * Handles additional widget tab options
 * run on __construct function
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Admin Messages
 * @return void
 */
if( !function_exists( 'widgetopts_admin_notices' ) ):
    function widgetopts_admin_notices() {
        if( !current_user_can( 'update_plugins' ) )
            return;

        //show rating notice to page that matters most
        global $pagenow;
        if( !in_array( $pagenow, array( 'widgets.php', 'options-general.php' ) ) ){
            return;
        }

        if( $pagenow == 'options-general.php' && function_exists( 'get_current_screen' ) ){
            $screen = get_current_screen();
            if( isset( $screen->base ) && $screen->base != 'settings_page_widgetopts_plugin_settings' ){
                return;
            }
        }

        $install_date   = get_option( 'widgetopts_installDate' );
        $saved          = get_option( 'widgetopts_RatingDiv' );
        $display_date   = date( 'Y-m-d h:i:s' );
        $datetime1      = new DateTime( $install_date );
        $datetime2      = new DateTime( $display_date );
        $diff_intrval   = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' ) ) / (60*60*24));
        if( 'yes' != $saved && $diff_intrval >= 7 ){
        echo '<div class="widgetopts_notice updated" style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
            <p>Awesome, you\'ve been using <strong>Widget Options</strong> for more than 1 week. <br> May i ask you to give it a <strong>5-star rating</strong> on WordPress? </br>
            This will help to spread its popularity and to make this plugin a better one.
            <br><br>Your help is much appreciated. Thank you very much,<br> ~ Jeffrey Carandang <em>(phpbits)</em>
            <ul>
                <li><a href="https://wordpress.org/support/view/plugin-reviews/widget-options" class="thankyou" target="_blank" title="Ok, you deserved it" style="font-weight:bold;">'. __( 'Ok, you deserved it', 'widget-options' ) .'</a></li>
                <li><a href="javascript:void(0);" class="widgetopts_bHideRating" title="I already did" style="font-weight:bold;">'. __( 'I already did', 'widget-options' ) .'</a></li>
                <li><a href="javascript:void(0);" class="widgetopts_bHideRating" title="No, not good enough" style="font-weight:bold;">'. __( 'No, not good enough, i do not like to rate it!', 'widget-options' ) .'</a></li>
            </ul>
        </div>
        <script>
        jQuery( document ).ready(function( $ ) {

        jQuery(\'.widgetopts_bHideRating\').click(function(){
            var data={\'action\':\'widgetopts_hideRating\'}
                 jQuery.ajax({

            url: "'. admin_url( 'admin-ajax.php' ) .'",
            type: "post",
            data: data,
            dataType: "json",
            async: !0,
            success: function(e) {
                if (e=="success") {
                   jQuery(\'.widgetopts_notice\').slideUp(\'slow\');

                }
            }
             });
            })

        });
        </script>
        ';
        }
    }
    add_action( 'admin_notices', 'widgetopts_admin_notices' );
endif;
?>
