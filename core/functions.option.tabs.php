<?php
/**
 * Handles additional widget tab options
 * run on __construct function
 */
if( !class_exists( 'PHPBITS_extendedWidgetsTabs' ) ):
class PHPBITS_extendedWidgetsTabs {
    public function __construct() {

        add_action( 'extended_widget_opts_tabs', array( &$this,'tab_visibility' ) ); 
        add_action( 'extended_widget_opts_tabcontent', array( &$this,'content_visibility' ) ); 

        add_action( 'extended_widget_opts_tabs', array( &$this,'tab_devices' ) ); 
        add_action( 'extended_widget_opts_tabcontent', array( &$this,'content_devices' ) );

        add_action( 'extended_widget_opts_tabs', array( &$this,'tab_alignment' ) ); 
        add_action( 'extended_widget_opts_tabcontent', array( &$this,'content_alignment' ) ); 

        add_action( 'extended_widget_opts_tabs', array( &$this,'tab_class' ) );
        add_action( 'extended_widget_opts_tabcontent', array( &$this,'content_class' ) ); 

        add_action( 'extended_widget_opts_tabs', array( &$this,'tab_gopro' ) );
        add_action( 'extended_widget_opts_tabcontent', array( &$this,'gopro_alignment' ) ); 
    }

    /**
     * Called on 'extended_widget_opts_tabs'
     * create new tab navigation for alignment options
     */
    function tab_alignment( $args ){ ?>
        <li class="extended-widget-opts-tab-alignment">
            <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-alignment" title="<?php _e( 'Alignment', 'widget-options' );?>" ><span class="dashicons dashicons-editor-aligncenter"></span> <span class="tabtitle"><?php _e( 'Alignment', 'widget-options' );?></span></a>
        </li>
    <?php 
    }

    /**
     * Called on 'extended_widget_opts_tabcontent'
     * create new tab content options for alignment options
     */
    function content_alignment( $args ){ 
        $desktop = '';
        $tablet  = '';
        $mobile  = '';
        if( isset( $args['params'] ) && isset( $args['params']['alignment'] ) ){
            if( isset( $args['params']['alignment']['desktop'] ) ){
                $desktop = $args['params']['alignment']['desktop'];
            }
            if( isset( $args['params']['alignment']['tablet'] ) ){
                $tablet = $args['params']['alignment']['tablet'];
            }
            if( isset( $args['params']['alignment']['mobile'] ) ){
                $mobile = $args['params']['alignment']['mobile'];
            }
        }
        ?>
        <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-alignment" class="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-alignment">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <td scope="row"><strong><?php _e( 'Devices', 'widget-options' );?></strong></td>
                        <td><strong><?php _e( 'Alignment', 'widget-options' );?></strong></td>
                    </tr>
                    <tr valign="top">
                        <td scope="row"><span class="dashicons dashicons-desktop"></span> <?php _e( 'All Devices', 'widget-options' );?></td>
                        <td>
                            <select class="widefat" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][alignment][desktop]">
                                <option value="default"><?php _e( 'Default', 'widget-options' );?></option>
                                <option value="center" <?php if( $desktop == 'center' ){ echo 'selected="selected"'; }?> ><?php _e( 'Center', 'widget-options' );?></option>
                                <option value="left" <?php if( $desktop == 'left' ){ echo 'selected="selected"'; }?>><?php _e( 'Left', 'widget-options' );?></option>
                                <option value="right" <?php if( $desktop == 'right' ){ echo 'selected="selected"'; }?>><?php _e( 'Right', 'widget-options' );?></option>
                                <option value="justify" <?php if( $desktop == 'justify' ){ echo 'selected="selected"'; }?>><?php _e( 'Justify', 'widget-options' );?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top" class="widgetopts-topro">
                        <td colspan="2"><small><?php _e( '<em>Upgrade to <a href="https://phpbits.net/plugin/extended-widget-options/" target="_blank">Pro Version</a> for Multiple Devices Alignment and Additional Widget Options.</em>', 'widget-options' );?></small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php 
    }

    /**
     * Called on 'extended_widget_opts_tabs'
     * create new tab navigation for visibility options
     */
    function tab_visibility( $args ){ ?>
        <li class="extended-widget-opts-tab-visibility">
            <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-visibility" title="<?php _e( 'Visibility', 'widget-options' );?>" ><span class="dashicons dashicons-visibility"></span> <span class="tabtitle"><?php _e( 'Visibility', 'widget-options' );?></span></a>
        </li>
    <?php 
    }

    /**
     * Called on 'extended_widget_opts_tabcontent'
     * create new tab content options for visibility options
     */
    function content_visibility( $args ){ 
        $checked    = "";
        $selected   = 0;

        //declare miscellaneous pages - wordpress default pages
        $misc       = array(
                        'home'      =>  __( 'Home/Front', 'widget-options' ),
                        'blog'      =>  __( 'Blog', 'widget-options' ),
                        'archives'  =>  __( 'Archives', 'widget-options' ),
                        'single'    =>  __( 'Single Post', 'widget-options' ),
                        '404'       =>  __( '404', 'widget-options' ),
                        'search'    =>  __( 'Search', 'widget-options' )
                    );

        /*
         * get available pages
         * Check for transient. If none, then execute Query
         */
        if ( false === ( $pages = get_transient( 'widgetopts_pages' ) ) ) {

            $pages  = get_posts( array(
                                'post_type'     => 'page', 
                                'post_status'   => 'publish',
                                'numberposts'   => -1,
                                'orderby'       => 'title', 
                                'order'         => 'ASC',
                                'fields'        => array('ID', 'name')
                    ));

          // Put the results in a transient. Expire after 4 weeks.
          set_transient( 'widgetopts_pages', $pages, 4 * WEEK_IN_SECONDS );
        }

        
        /*
         * get all post types
         * Check for transient. If none, then execute Query
         */
        if ( false === ( $types = get_transient( 'widgetopts_types' ) ) ) {

            $types  = get_post_types( array(
                            'public' => true,
                    ), 'object' );

          // Put the results in a transient. Expire after 10minutes.
          set_transient( 'widgetopts_types', $types, 10 * 60 );
        }

        //unset builtin post types
        foreach ( array( 'revision', 'attachment', 'nav_menu_item' ) as $unset ) {
            unset( $types[ $unset ] );
        }

        /*
         * get post categories
         * Check for transient. If none, then execute Query
         */
        if ( false === ( $categories = get_transient( 'widgetopts_categories' ) ) ) {

            $categories = get_categories( array(
                        'hide_empty'    => false
                    ) );

          // Put the results in a transient. Expire after 4 WEEKS.
          set_transient( 'widgetopts_categories', $categories, 4 * WEEK_IN_SECONDS );

        }
        

        $taxonomies = array();
        

        //get save values
        $options_values = '';
        $misc_values    = array();
        $pages_values   = array();
        $types_values   = array();
        $cat_values     = array();
        $tax_values     = array();
        if( isset( $args['params'] ) && isset( $args['params']['visibility'] ) ){
            if( isset( $args['params']['visibility']['options'] ) ){
                $options_values = $args['params']['visibility']['options'];
            }

            if( isset( $args['params']['visibility']['misc'] ) ){
                $misc_values = $args['params']['visibility']['misc'];
            }

            if( isset( $args['params']['visibility']['pages'] ) ){
                $pages_values = $args['params']['visibility']['pages'];
            }

            if( isset( $args['params']['visibility']['types'] ) ){
                $types_values = $args['params']['visibility']['types'];
            }

            if( isset( $args['params']['visibility']['categories'] ) ){
                $cat_values = $args['params']['visibility']['categories'];
            }

            if( isset( $args['params']['visibility']['taxonomies'] ) ){
                $tax_values = $args['params']['visibility']['taxonomies'];
            }
            if( isset( $args['params']['visibility']['selected'] ) ){
                $selected = $args['params']['visibility']['selected'];
            }
        }

        ?>
        <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-visibility" class="extended-widget-opts-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-tabcontent-visibility">
            <p><strong><?php _e( 'Hide/Show', 'widget-options' );?></strong>
            <select class="widefat" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][options]">
                <option value="hide" <?php if( $options_values == 'hide' ){ echo 'selected="selected"'; }?> ><?php _e( 'Hide on checked pages', 'widget-options' );?></option>
                <option value="show" <?php if( $options_values == 'show' ){ echo 'selected="selected"'; }?>><?php _e( 'Show on checked pages', 'widget-options' );?></option>
            </select>
            </p>

            <div class="extended-widget-opts-visibility-tabs extended-widget-opts-inside-tabs">
                <input type="hidden" id="extended-widget-opts-visibility-selectedtab" value="<?php echo $selected;?>" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][selected]" />
                <!--  start tab nav -->
                <ul class="extended-widget-opts-visibility-tabnav-ul">
                    <li class="extended-widget-opts-visibility-tab-visibility">
                        <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-misc" title="<?php _e( 'Home, Blog, Search, etc..', 'widget-options' );?>" ><?php _e( 'Misc', 'widget-options' );?></a>
                    </li>
                    <li class="extended-widget-opts-visibility-tab-visibility">
                        <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-types" title="<?php _e( 'Pages & Custom Post Types', 'widget-options' );?>" ><?php _e( 'Post Types', 'widget-options' );?></a>
                    </li>
                    <li class="extended-widget-opts-visibility-tab-visibility">
                        <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-tax" title="<?php _e( 'Categories, Tags & Taxonomies', 'widget-options' );?>" ><?php _e( 'Taxonomies', 'widget-options' );?></a>
                    </li>
                    <div class="extended-widget-opts-clearfix"></div>
                </ul><!--  end tab nav -->
                <div class="extended-widget-opts-clearfix"></div>

                <!--  start misc tab content -->
                <div id="extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-misc" class="extended-widget-opts-visibility-tabcontent extended-widget-opts-inner-tabcontent">
                    <div class="extended-widget-opts-misc">
                        <?php foreach ($misc as $key => $value) { 
                            if( isset( $misc_values[ $key ] ) && $misc_values[ $key ] == '1' ){
                                $checked = 'checked="checked"';
                            }else{
                                $checked = '';
                            }
                            ?>
                            <p>
                                <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][misc][<?php echo $key;?>]" id="<?php echo $args['id'];?>-opts-misc-<?php echo $key;?>" value="1" <?php echo $checked;?> />
                                <label for="<?php echo $args['id'];?>-opts-misc-<?php echo $key;?>"><?php echo $value;?></label>
                            </p>
                        <?php } ?>
                    </div>
                </div><!--  end misc tab content -->

                <!--  start types tab content -->
                <div id="extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-types" class="extended-widget-opts-visibility-tabcontent extended-widget-opts-inner-tabcontent">
                    <div class="extended-widget-opts-inner-lists" style="height: 230px;padding: 5px;overflow:auto;">
                        <h4 id="extended-widget-opts-pages"><?php _e( 'Pages', 'widget-options' );?> +/-</h4>
                        <div class="extended-widget-opts-pages">
                            <?php foreach ($pages as $page) { 
                                    if( isset( $pages_values[ $page->ID ] ) && $pages_values[ $page->ID ] == '1' ){
                                        $checked = 'checked="checked"';
                                    }else{
                                        $checked = '';
                                    }
                                ?>
                                <p>
                                    <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][pages][<?php echo $page->ID;?>]" id="<?php echo $args['id'];?>-opts-pages-<?php echo $page->ID;?>" value="1" <?php echo $checked;?> />
                                    <label for="<?php echo $args['id'];?>-opts-pages-<?php echo $page->ID;?>"><?php echo $page->post_title;?></label>
                                </p>
                            <?php } ?>
                        </div>

                        <h4 id="extended-widget-opts-types"><?php _e( 'Custom Post Types', 'widget-options' );?> +/-</h4>
                        <div class="extended-widget-opts-types">
                            <?php foreach ($types as $ptype => $type) { 
                                // if ( ! $type->has_archive ) {
                                //     // don't give the option if there is no archive page
                                //     continue;
                                // }

                                    if( isset( $types_values[ $ptype ] ) && $types_values[ $ptype ] == '1' ){
                                        $checked = 'checked="checked"';
                                    }else{
                                        $checked = '';
                                    }
                                ?>
                                <p>
                                    <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][types][<?php echo $ptype;?>]" id="<?php echo $args['id'];?>-opts-types-<?php echo $ptype;?>" value="1" <?php echo $checked;?> />
                                    <label for="<?php echo $args['id'];?>-opts-types-<?php echo $ptype;?>"><?php echo stripslashes( $type->labels->name );?></label>
                                </p>
                            <?php 
                                /*
                                 * get post type taxonomies
                                 * Check for transient. If none, then execute Query
                                 */
                                if ( false === ( $post_taxes = get_transient( 'widgetopts_post_taxes_'. $ptype ) ) ) {

                                    $post_taxes = get_object_taxonomies( $ptype );

                                  // Put the results in a transient. Expire after 5 minutes.
                                  set_transient( 'widgetopts_post_taxes_'. $ptype, $post_taxes, 5 * 60 );
                                }


                                foreach ( $post_taxes as $post_tax) {
                                    if ( in_array( $post_tax, array( 'category', 'post_format' ) ) ) {
                                        continue;
                                    }
                                    
                                    $taxonomy = get_taxonomy( $post_tax );
                                    $name = $post_tax;

                                    if ( isset( $taxonomy->labels->name ) && ! empty( $taxonomy->labels->name ) ) {
                                        $name = $taxonomy->labels->name . ' <small>'. $type->labels->name .'</small>';
                                    }
                                    
                                    $taxonomies[ $post_tax ] = $name;
                                }
                            } ?>
                        </div>
                    </div>
                </div><!--  end types tab content -->

                <!--  start tax tab content -->
                <div id="extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-tax" class="extended-widget-opts-visibility-tabcontent extended-widget-opts-inner-tabcontent">
                    <div class="extended-widget-opts-inner-lists" style="height: 230px;padding: 5px;overflow:auto;"> 
                        <h4 id="extended-widget-opts-categories"><?php _e( 'Categories', 'widget-options' );?> +/-</h4>
                        <div class="extended-widget-opts-categories">
                            <p>
                                <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][categories][all_categories]" id="<?php echo $args['id'];?>-opts-categories-all" value="1" <?php if( isset( $cat_values['all_categories'] ) ){ echo 'checked="checked"'; };?> />
                                <label for="<?php echo $args['id'];?>-opts-categories-all"><?php _e( 'All Categories', 'widget-options' );?></label>
                            </p>
                            <?php foreach ($categories as $cat) { 
                                    if( isset( $cat_values[ $cat->cat_ID ] ) && $cat_values[ $cat->cat_ID ] == '1' ){
                                        $checked = 'checked="checked"';
                                    }else{
                                        $checked = '';
                                    }
                                ?>
                                <p>
                                    <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][categories][<?php echo $cat->cat_ID;?>]" id="<?php echo $args['id'];?>-opts-categories-<?php echo $cat->cat_ID;?>" value="1" <?php echo $checked;?> />
                                    <label for="<?php echo $args['id'];?>-opts-categories-<?php echo $cat->cat_ID;?>"><?php echo $cat->cat_name;?></label>
                                </p>
                            <?php } ?>
                        </div>

                        <h4 id="extended-widget-opts-taxonomies"><?php _e( 'Taxonomies', 'widget-options' );?> +/-</h4>
                        <div class="extended-widget-opts-taxonomies">
                            <?php foreach ($taxonomies as $tax_key => $tax_label) { 
                                    if( isset( $tax_values[ $tax_key ] ) && $tax_values[ $tax_key ] == '1' ){
                                        $checked = 'checked="checked"';
                                    }else{
                                        $checked = '';
                                    }
                                ?>
                                <p>
                                    <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][visibility][taxonomies][<?php echo $tax_key;?>]" id="<?php echo $args['id'];?>-opts-taxonomies-<?php echo $tax_key;?>" value="1" <?php echo $checked;?> />
                                    <label for="<?php echo $args['id'];?>-opts-taxonomies-<?php echo $tax_key;?>"><?php echo $tax_label;?></label>
                                </p>
                            <?php } ?>
                        </div>
                    </div>    
                </div><!--  end tax tab content -->
            </div>

        </div>
    <?php 
    }

    /**
     * Called on 'extended_widget_opts_tabs'
     * create new tab navigation for devices visibility options
     */
    function tab_devices( $args ){ ?>
        <li class="extended-widget-opts-tab-devices">
            <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-devices" title="<?php _e( 'Devices', 'widget-options' );?>" ><span class="dashicons dashicons-smartphone"></span> <span class="tabtitle"><?php _e( 'Devices', 'widget-options' );?></span></a>
        </li>
    <?php 
    }

    /**
     * Called on 'extended_widget_opts_tabcontent'
     * create new tab content options for devices visibility options
     */
    function content_devices( $args ){ 
        $desktop        = '';
        $tablet         = '';
        $mobile         = '';
        $options_role   = '';
        if( isset( $args['params'] ) && isset( $args['params']['devices'] ) ){
            if( isset( $args['params']['devices']['options'] ) ){
                $options_role = $args['params']['devices']['options'];
            }
            if( isset( $args['params']['devices']['desktop'] ) ){
                $desktop = $args['params']['devices']['desktop'];
            }
            if( isset( $args['params']['devices']['tablet'] ) ){
                $tablet = $args['params']['devices']['tablet'];
            }
            if( isset( $args['params']['devices']['mobile'] ) ){
                $mobile = $args['params']['devices']['mobile'];
            }
        }
        ?>
        <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-devices" class="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-devices">
            <p>
                <strong><?php _e( 'Hide/Show', 'widget-options' );?></strong>
                <select class="widefat" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][devices][options]">
                    <option value="hide" <?php if( $options_role == 'hide' ){ echo 'selected="selected"'; }?> ><?php _e( 'Hide on checked devices', 'widget-options' );?></option>
                    <option value="show" <?php if( $options_role == 'show' ){ echo 'selected="selected"'; }?>><?php _e( 'Show on checked devices', 'widget-options' );?></option>
                </select>
            </p>
            <table class="form-table">
                <tbody>
                     <tr valign="top">
                        <td scope="row"><strong><?php _e( 'Devices', 'widget-options' );?></strong></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr valign="top">
                        <td scope="row">
                            <label for="opts-devices-desktop-<?php echo $args['id'];?>">
                                <span class="dashicons dashicons-desktop"></span> <?php _e( 'Desktop', 'widget-options' );?>
                            </label>
                            </td>
                        <td>
                            <input type="checkbox" id="opts-devices-desktop-<?php echo $args['id'];?>" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][devices][desktop]" value="1" <?php  if( !empty( $desktop ) ){ echo 'checked="checked"'; }?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td scope="row">
                            <label for="opts-devices-tablet-<?php echo $args['id'];?>">
                                <span class="dashicons dashicons-tablet"></span> <?php _e( 'Tablet', 'widget-options' );?>
                            </label>
                        </td>
                        <td>
                            <input type="checkbox" id="opts-devices-tablet-<?php echo $args['id'];?>" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][devices][tablet]" value="1" <?php  if( !empty( $tablet ) ){ echo 'checked="checked"'; }?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <td scope="row">
                            <label for="opts-devices-mobile-<?php echo $args['id'];?>">
                                <span class="dashicons dashicons-smartphone"></span> <?php _e( 'Mobile', 'widget-options' );?>
                            </label>
                        </td>
                        <td>
                            <input type="checkbox" id="opts-devices-mobile-<?php echo $args['id'];?>" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][devices][mobile]" value="1" <?php  if( !empty( $mobile ) ){ echo 'checked="checked"'; }?> />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php 
    }

    /**
     * Called on 'extended_widget_opts_tabs'
     * create new tab navigation for custom class & ID options
     */
    function tab_class( $args ){ ?>
        <li class="extended-widget-opts-tab-class">
            <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-class" title="<?php _e( 'Class,ID & Display Logic', 'widget-options' );?>" ><span class="dashicons dashicons-admin-generic"></span> <span class="tabtitle"><?php _e( 'Class,ID & Logic', 'widget-options' );?></span></a>
        </li>
    <?php 
    }

    function content_class( $args ){ 
        $id         = '';
        $classes    = '';
        $logic      = '';
        $selected   = 0;
        if( isset( $args['params'] ) && isset( $args['params']['class'] ) ){
            if( isset( $args['params']['class']['id'] ) ){
                $id = $args['params']['class']['id'];
            }
            if( isset( $args['params']['class']['classes'] ) ){
                $classes = $args['params']['class']['classes'];
            }
            if( isset( $args['params']['class']['selected'] ) ){
                $selected = $args['params']['class']['selected'];
            }
            if( isset( $args['params']['class']['logic'] ) ){
                $logic = $args['params']['class']['logic'];
            }
        }

        $options    = get_option('extwopts_class_settings');
        $predefined = array();
        if( isset( $options['classlists'] ) && !empty( $options['classlists'] ) ){
            $predefined = $options['classlists'];
        }
        ?>
        <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-class" class="extended-widget-opts-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-tabcontent-class">
            
            <div class="extended-widget-opts-settings-tabs extended-widget-opts-inside-tabs">
                <input type="hidden" id="extended-widget-opts-settings-selectedtab" value="<?php echo $selected;?>" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][class][selected]" />
                <!--  start tab nav -->
                <ul class="extended-widget-opts-settings-tabnav-ul">
                    <li class="extended-widget-opts-settings-tab-class">
                        <a href="#extended-widget-opts-settings-tab-<?php echo $args['id'];?>-class" title="<?php _e( 'Class & ID', 'widget-options' );?>" ><?php _e( 'Class & ID', 'widget-options' );?></a>
                    </li>
                    <li class="extended-widget-opts-settings-tab-logic">
                        <a href="#extended-widget-opts-settings-tab-<?php echo $args['id'];?>-logic" title="<?php _e( 'Display Logic', 'widget-options' );?>" ><?php _e( 'Display Logic', 'widget-options' );?></a>
                    </li>
                    <div class="extended-widget-opts-clearfix"></div>
                </ul><!--  end tab nav -->
                <div class="extended-widget-opts-clearfix"></div>
                <!--  start class tab content -->
                <div id="extended-widget-opts-settings-tab-<?php echo $args['id'];?>-class" class="extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent">
                    <div class="widget-opts-class">
                        <table class="form-table">
                        <tbody>
                            <?php if( !isset( $options['id_field'] ) || ( isset( $options['id_field'] ) && 'no' != $options['id_field'] ) ){?>
                                <tr valign="top">
                                    <td scope="row">
                                        <strong><?php _e( 'Widget CSS ID:', 'widget-options' );?></strong><br />
                                        <input type="text" id="opts-class-id-<?php echo $args['id'];?>" class="widefat" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][class][id]" value="<?php echo $id;?>" />
                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if( !isset( $options['class_field'] ) ||
                                     ( isset( $options['class_field'] ) && !in_array( $options['class_field'] , array( 'hide', 'predefined' ) ) ) ){?>
                                <tr valign="top">
                                    <td scope="row">
                                        <strong><?php _e( 'Widget CSS Classes:', 'widget-options' );?></strong><br />
                                        <input type="text" id="opts-class-classes-<?php echo $args['id'];?>" class="widefat" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][class][classes]" value="<?php echo $classes;?>" />
                                        <small><em><?php _e( 'Separate each class with space.', 'widget-options' );?></em></small>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if( !isset( $options['class_field'] ) ||
                                     ( isset( $options['class_field'] ) && !in_array( $options['class_field'] , array( 'hide', 'text' ) ) ) ){?>
                                <?php if( is_array( $predefined ) && !empty( $predefined ) ){
                                    $predefined = array_unique( $predefined ); //remove dups
                                    ?>
                                        <tr valign="top">
                                            <td scope="row">
                                                <strong><?php _e( 'Available Widget Classes:', 'widget-options' );?></strong><br />
                                                <div class="extended-widget-opts-class-lists" style="max-height: 230px;padding: 5px;overflow:auto;">
                                                    <?php foreach ($predefined as $key => $value) { 
                                                        if(  isset( $args['params']['class']['predefined'] ) &&
                                                             is_array( $args['params']['class']['predefined'] ) &&
                                                             in_array( $value , $args['params']['class']['predefined'] ) ){
                                                            $checked = 'checked="checked"';
                                                        }else{
                                                            $checked = '';
                                                        }
                                                        ?>
                                                        <p>
                                                            <input type="checkbox" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][class][predefined][]" id="<?php echo $args['id'];?>-opts-class-<?php echo $key;?>" value="<?php echo $value;?>" <?php echo $checked;?> />
                                                            <label for="<?php echo $args['id'];?>-opts-class-<?php echo $key;?>"><?php echo $value;?></label>
                                                        </p>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                            <?php } ?>
                        </tbody>
                        </table>
                    </div>
                </div><!--  end class tab content -->

                <!--  start logiv tab content -->
                <div id="extended-widget-opts-settings-tab-<?php echo $args['id'];?>-logic" class="extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent">
                    <div class="widget-opts-logic">
                        <p><small><?php _e( 'The text field lets you use <a href="http://codex.wordpress.org/Conditional_Tags" target="_blank">WP Conditional Tags</a>, or any general PHP code.', 'widget-options' );?></small></p>
                        <textarea class="widefat" name="extended_widget_opts-<?php echo $args['id'];?>[extended_widget_opts][class][logic]"><?php echo stripslashes( $logic );?></textarea>
                        <p><a href="#" class="widget-opts-toggler-note"><?php _e( 'Click to Toggle Note', 'widget-options' );?></a></p>
                        <p class="widget-opts-toggle-note"><small><?php _e( 'PLEASE NOTE that the display logic you introduce is EVAL\'d directly. Anyone who has access to edit widget appearance will have the right to add any code, including malicious and possibly destructive functions. There is an optional filter <em>"widget_options_logic_override"</em> which you can use to bypass the EVAL with your own code if needed.', 'widget-options' );?></small></p>
                    </div>
                </div><!--  end logiv tab content -->

            </div><!-- end .extended-widget-opts-settings-tabs -->

        </div>
    <?php
    }

    /**
     * Called on 'extended_widget_pro_tabs'
     * create new tab navigation for alignment options
     */
    function tab_gopro( $args ){ ?>
        <li class="extended-widget-gopro-tab-alignment">
            <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-gopro">+</a>
        </li>
    <?php 
    }

    function gopro_alignment( $args ){ ?>
        <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-gopro" class="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-gopro">
            <p><strong><?php _e( 'Get a Fully-Packed Widget Options and maximize your widget control!', 'widget-options' );?></strong></p>
            <p><em><?php _e( 'Aside from the free features already available, you will get the following features. ', 'widget-options' );?></em></p>
            <ul>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Display Widget Columns', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'More Alignment Options', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'User Roles Visibility Options', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Show or Hide widgets for specific day', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Show or Hide widgets for date range', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Show or Hide widgets for specific days on the given date range', 'widget-options' );?></li>
            </ul>
            <p><strong><?php _e( 'Brand New Features added on version 2.0', 'widget-options' );?></strong></p>
            <ul>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Widget Styling', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Post & Post Types Extended Terms Support', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Display Widget Logic', 'widget-options' );?></li>
                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'and other improvements...', 'widget-options' );?></li>
            </ul>
            <p><span class="dashicons dashicons-plus"></span> <strong><?php _e( 'PLUGIN LIFETIME UPDATES', 'widget-options' );?></strong></p>
            
            <p><strong><a href="https://phpbits.net/plugin/extended-widget-options/" class="widget-opts-learnmore" target="_blank"><?php _e( 'Learn More', 'widget-options' );?> <span class="dashicons dashicons-arrow-right-alt"></span></a></strong></p>
        </div>
    <?php
    }


    /**
     * Called on 'sidebar_admin_setup'
     * adds in the admin control per widget, but also processes import/export
     */
}
new PHPBITS_extendedWidgetsTabs();
endif;
?>