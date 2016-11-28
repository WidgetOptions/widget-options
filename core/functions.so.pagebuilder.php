<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles action to disable widgets on admin
 * run on __construct function
 */
if( !class_exists( 'PHPBITS_extendedWidgetsSOSupport' ) ):
class PHPBITS_extendedWidgetsSOSupport {

    private $settings = array(), $widgetopts_tabs = array();

    public function __construct() {
        global $pagenow;

        /*
         * Check for transient. If none, then execute Query
         */
        if ( false === ( $widgetopts_tabs = get_transient( 'widgetopts_tabs_transient' ) ) ) {

            $widgetopts_tabs = array(
                'visibility'    => get_option( 'widgetopts_tabmodule-visibility' ),
                'devices'       => get_option( 'widgetopts_tabmodule-devices' ),
                'alignment'     => get_option( 'widgetopts_tabmodule-alignment' ),
                'hide_title'    => get_option( 'widgetopts_tabmodule-hide_title' ),
                'classes'       => get_option( 'widgetopts_tabmodule-classes' ),
                'logic'         => get_option( 'widgetopts_tabmodule-logic' ),
                'siteorigin'    => get_option( 'widgetopts_tabmodule-siteorigin' )
            );
            $widgetopts_tabs = maybe_serialize($widgetopts_tabs);
          // Put the results in a transient. Expire after 4 weeks.
          set_transient( 'widgetopts_tabs_transient', $widgetopts_tabs, 4 * WEEK_IN_SECONDS );
        }

        //fix for https://github.com/phpbits/widget-options/issues/7
        if( is_serialized( $widgetopts_tabs ) ){
        	$this->widgetopts_tabs = unserialize( $widgetopts_tabs );
        }elseif( is_array( $widgetopts_tabs ) ){
        	$this->widgetopts_tabs = $widgetopts_tabs;
        }

        $this->settings = unserialize( get_option( 'widgetopts_tabmodule-settings' ) );

        if( isset( $this->widgetopts_tabs['siteorigin'] ) && 'activate' == $this->widgetopts_tabs['siteorigin'] ){
            add_filter( 'siteorigin_panels_data', array( $this, 'panels_data' ), 10, 4 );
            add_filter( 'siteorigin_panels_widget_classes', array( $this, 'widget_classes' ), 10, 4 );
        }
    }

    function panels_data( $panels_data, $post_id ){
        if( !is_admin() ){
            if( isset( $panels_data['widgets'] ) && !empty( $panels_data['widgets'] ) && is_array( $panels_data['widgets'] ) ){

                global $current_user;

                foreach ( $panels_data['widgets'] as $key => $widgets ) {
                    if( isset( $widgets['extended_widget_opts'] ) && !empty( $widgets['extended_widget_opts'] ) ){

                        if( isset( $panels_data['widgets'][$key] ) && 'activate' == $this->widgetopts_tabs['logic'] ){
                            // display widget logic
                            if( isset( $widgets['extended_widget_opts']['class'] ) && isset( $widgets['extended_widget_opts']['class']['logic'] ) && !empty( $widgets['extended_widget_opts']['class']['logic'] ) ){
                                $display_logic = stripslashes( trim( $widgets['extended_widget_opts']['class']['logic'] ) );
                                $display_logic = apply_filters( "extended_widget_options_logic_override", $display_logic );
                                if ( $display_logic === false ){
                                    unset( $panels_data['widgets'][$key]);
                                }
                                if ( $display_logic === true ){
                                    // return true;
                                }
                                if ( stristr($display_logic,"return")===false ){
                                    $display_logic="return (" . $display_logic . ");";
                                }
                                if ( !eval( $display_logic ) ){
                                    unset( $panels_data['widgets'][$key]);
                                }
                            }
                        }

                    }
                }
            }
        }
        return $panels_data;
    }

    function widget_classes( $classes, $widget, $instance, $widget_info ) {
        if( isset( $instance['extended_widget_opts'] ) ){
            $get_classes    = widgetopts_classes_generator( $instance['extended_widget_opts'], $this->widgetopts_tabs, $this->settings, true );
            $get_classes[]  = 'widgetopts-SO';

            $classes        = apply_filters( 'widgetopts_siteorigin_panels_widget_classes', array_merge( $classes, $get_classes ), $widget_info );
        }

        return $classes;
    }
}
new PHPBITS_extendedWidgetsSOSupport();
endif;
?>
