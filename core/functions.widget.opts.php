<?php
/**
 * Handles additional widget tab options
 * run on __construct function
 */
if( !class_exists( 'PHPBITS_extendedWidgets' ) ):
class PHPBITS_extendedWidgets {
    public function  __construct() {
        add_action( 'admin_enqueue_scripts', array( &$this,'enqueue_scripts') );
        add_action( 'customize_controls_enqueue_scripts', array( &$this,'enqueue_scripts') );
        if ( is_admin() ){
            add_filter( 'widget_update_callback', array( &$this,'ajax_update_callback'), 10, 3);
            add_action( 'in_widget_form', array( &$this, 'widget_options'), 10, 3 );
        }
    }

    function enqueue_scripts(){
        wp_enqueue_style( 'widgetopts-jquery-ui', plugins_url( '../assets/css/jqueryui/1.11.4/themes/ui-lightness/jquery-ui.css' , __FILE__ ) , array(), null );
    	wp_enqueue_style( 'extended-widget-options-css', plugins_url( '../assets/css/extended-widgets.css' , __FILE__ ) , array(), null );

        wp_enqueue_script(
            'jquery-extended-widgets-options',
            plugins_url( 'assets/js/admin.js' , dirname(__FILE__) ),
            array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'jquery-ui-datepicker'),
            '',
            true
        );

        wp_localize_script( 'jquery-extended-widgets-options', 'widgetopts10n', array( 'opts_page' => esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) ), 'translation' => array( 'manage_settings' => __( 'Manage Widget Options', 'extended-widget-options' ) )) );
    }

    function widget_options( $widget, $return, $instance ){
        global $wp_registered_widget_controls;
        $width = (isset( $wp_registered_widget_controls[$widget->id]['width'] )) ? (int) $wp_registered_widget_controls[$widget->id]['width'] : 250;
        $opts = ( isset( $instance[ 'extended_widget_opts-'. $widget->id ] ) ) ? $instance[ 'extended_widget_opts-'. $widget->id ] : array();
        $is_siteorigin = get_option( 'widgetopts_tabmodule-siteorigin' );

        /** change widget names for SO Pagebuilder support **/
        if( isset( $widget->id ) && 'temp' == $widget->id ){
            $namespace  = 'widgets['. $widget->number .']';
            $optsname   = 'widgets['. $widget->number .'][extended_widget_opts_name]';
            $opts       = ( isset( $instance[ 'extended_widget_opts' ] ) ) ? $instance[ 'extended_widget_opts'] : array();
            $widget->id = $widget->number;

            //create siteorigin pagebuilder variable
            echo '<input type="hidden" name="'. $namespace .'[siteorigin]" value="1" />';
        }else{
            $namespace = 'extended_widget_opts-'. $widget->id;
            $optsname   = 'extended_widget_opts_name';
        }

        // print_r( $instance );
        $args = array(
                    'width'     =>  $width,
                    'id'        =>  $widget->id,
                    'params'    =>  $opts,
                    'namespace' =>  $namespace
                );
        $selected = 0;
        if( isset( $opts['tabselect'] ) ){
            $selected = $opts['tabselect'];
        }
        ?>
        <input type="hidden" name="extended_widget_opts_name" value="extended_widget_opts-<?php echo $widget->id;?>">
        <div class="extended-widget-opts-form <?php if( $width < 490 && $width > 440 ){ echo 'extended-widget-opts-form-large'; }else if( $width < 440 ){ echo 'extended-widget-opts-form-small'; }?>">
            <div class="extended-widget-opts-tabs">
                <ul class="extended-widget-opts-tabnav-ul">
                    <?php do_action( 'extended_widget_opts_tabs', $args );?>
                    <div class="extended-widget-opts-clearfix"></div>
                </ul>

                <?php do_action( 'extended_widget_opts_tabcontent', $args );?>
                <input type="hidden" id="extended-widget-opts-selectedtab" value="<?php echo $selected;?>" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][tabselect]" />
                <div class="extended-widget-opts-clearfix"></div>
            </div><!--  end .extended-widget-opts-tabs -->
        </div><!-- end .extended-widget-opts-form -->

        <?php if( 'activate' == $is_siteorigin ){?>
            <script type="text/javascript">
    			jQuery(document).ready(function($){
    				if($('.so-content .extended-widget-opts-tabs').length > 0){
                        $('.extended-widget-opts-tabs').tabs({ active: 1 });
            	    	$('.extended-widget-opts-settings-tabs').tabs({ active: 0 });
    				}
    			});
			</script>
        <?php } else{ ?>
            <style type="text/css">
                .so-content.panel-dialog .extended-widget-opts-form{ display: none; }
            </style>
        <?php } ?>

        <?php
    }

    /*
     * Update Options
     */
     function ajax_update_callback( $instance, $new_instance, $this_widget){
         if( isset($_POST['extended_widget_opts_name']) ||
             ( !isset( $_POST['extended_widget_opts_name'] ) && isset( $new_instance['siteorigin'] ) )
         ){
             //check if from SO pagebuilder
             if( is_array( $new_instance ) && isset( $new_instance['extended_widget_opts'] ) && isset( $new_instance['siteorigin'] ) ){
                 $name       = 'extended_widget_opts';
                 $options    = $this->sanitize( $new_instance );
             }else{
                 $name 		= strip_tags( $_POST['extended_widget_opts_name'] );
                 $options 	= $_POST[ $name ];
             }
             if( isset( $options['extended_widget_opts'] ) ){
                 $instance[ $name ] = $this->sanitize( $options['extended_widget_opts'] );
             }
         }
         return $instance;
     }

    /**
     * A custom sanitization function that will take the incoming input, and sanitize
     * the input before handing it back to WordPress to save to the database.
     *
     * @since    1.0.0
     *
     * @param    array    $input        The address input.
     * @return   array    $new_input    The sanitized input.
     */
    function sanitize( $input ) {
        if (!is_array($input) || !count($input)) {
            return array();
        }
        // Initialize the new array that will hold the sanitize values
        $new_input = array();
        // Loop through the input and sanitize each of the values
        foreach ( $input as $key => $val ) {
            if( !is_array( $val ) ){
                $new_input[ $key ] = sanitize_text_field( $val );
            }else if( is_array( $val ) ){
                $new_input[ $key ] = $this->sanitize( $val );
            }
        }
        return $new_input;
    }
}
new PHPBITS_extendedWidgets();
endif;
?>
