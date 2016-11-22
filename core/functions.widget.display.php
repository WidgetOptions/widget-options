<?php
/**
 * Handles additional widget tab options
 * run on __construct function
 */
if( !class_exists( 'PHPBITS_extendedWidgetsDisplay' ) ):
class PHPBITS_extendedWidgetsDisplay {

    private $widgetopts_tabs = array() , $settings = array() ;

    public function __construct() {

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
                'logic'         => get_option( 'widgetopts_tabmodule-logic' )
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

        add_filter( 'widget_display_callback', array( &$this, 'widget_display' ), 50, 3 );
        add_filter( 'dynamic_sidebar_params', array( &$this,'add_classes_to_widget' ) );
        add_filter( 'widget_title', array( &$this,'remove_widget_title' ), 10, 4 );
        add_action( 'wp_enqueue_scripts', array( &$this,'enqueue' ) );
    }

    function enqueue(){
        wp_enqueue_style( 'ext-widget-opts', plugins_url( 'assets/css/extended-widget-options.css' , dirname(__FILE__) ) , array(), null );
    }

    function remove_widget_title( $widget_title, $instance = array(), $widget_id = '' ) {
        // print_r( $instance );
    	if ( 'activate' == $this->widgetopts_tabs['hide_title'] && is_array( $instance ) && !empty( $instance ) ){
            foreach ( $instance as $key => $value) {
                if( substr( $key, 0, 20 ) == 'extended_widget_opts' ){
                    $opts       = ( isset( $instance[ $key ] ) ) ? $instance[ $key ] : array();

                    if( isset( $opts['class'] ) && isset( $opts['class']['title'] ) && '1' == $opts['class']['title'] ){
                        return;
                    }

                    break;
                }
            }
            return $widget_title;
        }else{
            return ( $widget_title );
        }

    }

    function widget_display( $instance, $widget, $args ){
        global $current_user;

        $hidden     = false;
        $opts       = ( isset( $instance[ 'extended_widget_opts-'. $widget->id ] ) ) ? $instance[ 'extended_widget_opts-'. $widget->id ] : array();
        $visibility = array( 'show' => array(), 'hide' => array() );


        //wordpress pages
        $visibility         = isset( $opts['visibility'] ) ? $opts['visibility'] : '';
        $visibility_opts    = isset( $opts['visibility']['options'] ) ? $opts['visibility']['options'] : 'hide';

        $is_misc = ( 'activate' == $this->widgetopts_tabs['visibility'] && isset( $this->settings['visibility'] ) && isset( $this->settings['visibility']['misc'] ) ) ? true : false;
        $is_types = ( 'activate' == $this->widgetopts_tabs['visibility'] && isset( $this->settings['visibility'] ) && isset( $this->settings['visibility']['post_type'] ) ) ? true : false;
        $is_tax = ( 'activate' == $this->widgetopts_tabs['visibility'] && isset( $this->settings['visibility'] ) && isset( $this->settings['visibility']['taxonomies'] ) ) ? true : false;

        if ( $is_misc && ( ( is_home() && is_front_page() ) || is_front_page() ) ) {
            if( isset( $visibility['misc']['home'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['home'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_home', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc && is_home() ) { //filter for blog page
            if( isset( $visibility['misc']['blog'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['blog'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_blog', $hidden );
            if( $hidden ){
                return false;
            }

        }elseif ( $is_tax && is_category() ) {
            if( !isset( $visibility['categories'] ) ){
                $visibility['categories'] = array();
            }
            if( !isset( $visibility['categories']['all_categories'] ) && $visibility_opts == 'hide' && array_key_exists( get_query_var('cat') , $visibility['categories']) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( !isset( $visibility['categories']['all_categories'] ) && $visibility_opts == 'show' && !array_key_exists( get_query_var('cat') , $visibility['categories']) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }elseif( isset( $visibility['categories']['all_categories'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide to all categories
            }elseif( isset( $visibility['categories']['all_categories'] ) && $visibility_opts == 'show' ){
                $hidden = false; //hide to all categories
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_categories', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_tax && is_tax() ) {
            $term = get_queried_object();
            if( !isset( $visibility['taxonomies'] ) ){
                $visibility['taxonomies'] = array();
            }

            if( $visibility_opts == 'hide' && array_key_exists( $term->taxonomy , $visibility['taxonomies']) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( $visibility_opts == 'show' && !array_key_exists( $term->taxonomy , $visibility['taxonomies']) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_taxonomies', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc &&is_archive() ) {
            if( isset( $visibility['misc']['archives'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['archives'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_archives', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc && is_404() ) {
            if( isset( $visibility['misc']['404'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['404'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_404', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc && is_search() ) {
            if( isset( $visibility['misc']['search'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['search'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_search', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( is_single() && !is_page() ) {
            $type = get_post_type();
            if( $is_types && !isset( $visibility['types'] ) ){
                $visibility['types'] = array();
            }
            if( $visibility_opts == 'hide' && array_key_exists( $type , $visibility['types']) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( $visibility_opts == 'show' && !array_key_exists( $type , $visibility['types']) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }

            // do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_types', $hidden );


            //hide posts assign on category
            if( !isset( $visibility['categories'] ) ){
                $visibility['categories'] = array();
            }
            if( isset( $visibility['categories']['all_categories'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide to all categories
            }elseif( isset( $visibility['categories']['all_categories'] ) && $visibility_opts == 'show' ){
                $hidden = false; //hide to all categories
            }elseif( !isset( $visibility['categories']['all_categories'] ) && !empty( $visibility['categories'] ) ) {
                $cats           = wp_get_post_categories( get_the_ID() );
                if( is_array( $cats ) && !empty( $cats ) ){
                    $checked_cats   = array_keys( $visibility['categories'] );
                    $intersect      = array_intersect( $cats , $checked_cats );
                    if( !empty( $intersect ) && $visibility_opts == 'hide' ){
                        $hidden = true;
                    }elseif( !empty( $intersect ) && $visibility_opts == 'show' ){
                        $hidden = false;
                    }
                }
            }

            // do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_post_category', $hidden );

            if( $hidden ){
                return false;
            }
            // echo $type;
        }elseif ( $is_types && is_page() ) {
            global $post;

            //do post type condition first
            if( isset( $visibility['types'] ) && isset( $visibility['types']['page'] ) ){
                if( $visibility_opts == 'hide' && array_key_exists( 'page' , $visibility['types']) ){
                    $hidden = true; //hide if exists on hidden pages
                }elseif( $visibility_opts == 'show' && !array_key_exists( 'page' , $visibility['types']) ){
                    $hidden = true; //hide if doesn't exists on visible pages
                }
            }else{
                //do per pages condition
                if( !isset( $visibility['pages'] ) ){
                    $visibility['pages'] = array();
                }
                if( $visibility_opts == 'hide' && array_key_exists( $post->ID , $visibility['pages']) ){
                    $hidden = true; //hide if exists on hidden pages
                }elseif( $visibility_opts == 'show' && !array_key_exists( $post->ID , $visibility['pages']) ){
                    $hidden = true; //hide if doesn't exists on visible pages
                }
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'extended_widget_options_page', $hidden );
            if( $hidden ){
                return false;
            }
        }

        //end wordpress pages
        if( 'activate' == $this->widgetopts_tabs['logic'] ){
            // display widget logic
            if( isset( $opts['class'] ) && isset( $opts['class']['logic'] ) && !empty( $opts['class']['logic'] ) ){
                $display_logic = stripslashes( trim( $opts['class']['logic'] ) );
                $display_logic = apply_filters( 'widget_options_logic_override', $display_logic );
                if ( $display_logic === false ){
                    return false;
                }
                if ( $display_logic === true ){
                    return true;
                }
                if ( stristr($display_logic,"return")===false ){
                    $display_logic="return (" . $display_logic . ");";
                }
                if ( !eval( $display_logic ) ){
                    return false;
                }
            }
        }

        if( 'activate' == $this->widgetopts_tabs['hide_title'] ){
            //hide widget title
            if( isset( $instance['title'] ) && isset( $opts['class'] ) && isset( $opts['class']['title'] ) && '1' == $opts['class']['title'] ){
                $instance['title'] = '';
            }
        }

        return $instance;
    }

    //add custom widget classes
    function add_classes_to_widget($params){
        global $wp_registered_widget_controls;
        $classe_to_add  = '';
        $id_base        = $wp_registered_widget_controls[ $params[0]['widget_id'] ]['id_base'];
        $instance       = get_option( 'widget_' . $id_base );

        if( isset( $wp_registered_widget_controls[ $params[0]['widget_id'] ]['params'][0]['number'] ) ){
            $num = $wp_registered_widget_controls[ $params[0]['widget_id'] ]['params'][0]['number'];
        }elseif( isset( $wp_registered_widget_controls[ $params[0]['widget_id'] ]['callback'][0]->number ) ){
            $num = $wp_registered_widget_controls[ $params[0]['widget_id'] ]['callback'][0]->number;
        }else{
            $num = substr( $params[0]['widget_id'], -1 );
        }
        if( isset( $instance[ $num ] ) ){
            $opts           = ( isset( $instance[ $num ][ 'extended_widget_opts-'. $params[0]['widget_id'] ] ) ) ? $instance[ $num ][ 'extended_widget_opts-'. $params[0]['widget_id'] ] : array();
        }else{
            $opts = array();
        }

        $devices        = isset( $opts['devices'] ) ? $opts['devices'] : '';
        $alignment      = isset( $opts['alignment'] ) ? $opts['alignment'] : '';
        $custom_class   = isset( $opts['class'] ) ? $opts['class'] : '';
        $abbr           = array(
                            'mobile'    =>  'xs',
                            'tablet'    =>  'sm',
                            'desktop'   =>  'md',
                        );
        if( isset( $devices['options'] ) ){
            unset( $devices['options'] );
        }

        if( 'activate' == $this->widgetopts_tabs['devices'] ){
            if( !empty( $devices ) ){
                $device_opts    = ( isset( $opts['devices']['options'] ) ) ? $opts['devices']['options'] : 'hide';
                $classe_to_add .= 'extendedwopts-' . $device_opts . ' ';

                foreach ($devices as $key => $value) {
                    $classe_to_add .= 'extendedwopts-' . $key . ' ';
                }
            }
        }

        if( 'activate' == $this->widgetopts_tabs['alignment'] ){
            //alignment
            if( !empty( $alignment ) ){
                foreach ($alignment as $k => $v) {
                    if( 'default' != $v ){
                        $classe_to_add .= 'extendedwopts-' . $abbr[ $k ] . '-'. $v . ' ';
                    }
                }
            }
        }

        if( 'activate' == $this->widgetopts_tabs['classes'] && isset( $this->settings['classes'] ) ){
            //don't add the IDs when the setting is set to NO
            if( isset( $this->settings['classes']['id'] ) ){
                if( is_array( $custom_class ) && isset( $custom_class['id'] ) ){
                    $params[0]['before_widget'] = preg_replace( '/id="[^"]*/', "id=\"{$custom_class['id']}", $params[0]['before_widget'], 1 );
                }
            }
        }

        if( 'activate' == $this->widgetopts_tabs['classes'] && isset( $this->settings['classes'] ) ){
            //classes & ID
            $options    = get_option('extwopts_class_settings');
            $predefined = array();
            if( isset( $this->settings['classes'] ) && isset( $this->settings['classes']['classlists'] ) && !empty( $this->settings['classes']['classlists'] ) ){
                $predefined = $this->settings['classes']['classlists'];
            }

            //don't add any classes when settings is set to predefined or hide
            if( !isset( $this->settings['classes']['type'] ) ||
                    ( isset(  $this->settings['classes']['type'] ) && !in_array(  $this->settings['classes']['type'] , array( 'hide', 'predefined' ) ) ) ){
                    if( is_array( $custom_class ) && isset( $custom_class['classes'] ) && !empty( $custom_class['classes'] ) ){
                        $classe_to_add .= $custom_class['classes'] .' ';
                    }
            }

            //don't add any classes when settings is set to text or hide
            if( !isset(  $this->settings['classes']['type'] ) ||
                    ( isset(  $this->settings['classes']['type'] ) && !in_array( $this->settings['classes']['type'] , array( 'hide', 'text' ) ) ) ){
                    if( is_array( $predefined ) && !empty( $predefined ) ){
                        $predefined = array_unique( $predefined );
                        if( isset( $custom_class['predefined'] ) && is_array( $custom_class['predefined'] ) ){
                            $filtered = array_intersect( $predefined, $custom_class['predefined'] );
                            if( !empty( $filtered ) ){
                                $classe_to_add .= implode( ' ', $filtered );
                                $classe_to_add .= ' ';
                            }
                        }
                    }
            }
        }

        $classes                    = 'class="'.$classe_to_add;
        $params[0]['before_widget'] = str_replace('class="',$classes,$params[0]['before_widget']);
        return $params;
    }
}
new PHPBITS_extendedWidgetsDisplay();
endif;
?>
