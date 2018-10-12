<?php
/**
 * Pages Visibility Widget Options
 *
 * @copyright   Copyright (c) 2015, Jeffrey Carandang
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Visibility Widget Options Tab
 *
 * @since 1.0
 * @return void
 */

 /**
 * Called on 'extended_widget_opts_tabs'
 * create new tab navigation for visibility options
 */
function widgetopts_tab_visibility( $args ){ ?>
    <li class="extended-widget-opts-tab-visibility">
        <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-visibility" title="<?php _e( 'Visibility', 'widget-options' );?>" ><span class="dashicons dashicons-visibility"></span> <span class="tabtitle"><?php _e( 'Visibility', 'widget-options' );?></span></a>
    </li>
<?php
}
add_action( 'extended_widget_opts_tabs', 'widgetopts_tab_visibility' );

/**
 * Called on 'extended_widget_opts_tabcontent'
 * create new tab content options for visibility options
 */
function widgetopts_tabcontent_visibility( $args ){
    global $widget_options, $widgetopts_taxonomies, $widgetopts_pages, $widgetopts_types, $widgetopts_categories;

    $checked    = "";
    $main       = "";
    $selected   = 0;
    $tax_opts   = (array) get_option( 'extwopts_taxonomy_settings' );
    $pages      = ( !empty( $widgetopts_pages ) )       ? $widgetopts_pages         : array();
    $taxonomies = ( !empty( $widgetopts_taxonomies ) )  ? $widgetopts_taxonomies    : array();
    $types      = ( !empty( $widgetopts_types ) )       ? $widgetopts_types         : array();
    $categories = ( !empty( $widgetopts_categories ) )  ? $widgetopts_categories    : array();

    //declare miscellaneous pages - wordpress default pages
    $misc       = array(
                    'home'      =>  __( 'Home/Front', 'widget-options' ),
                    'blog'      =>  __( 'Blog', 'widget-options' ),
                    'archives'  =>  __( 'Archives', 'widget-options' ),
                    // 'single'    =>  __( 'Single Post', 'widget-options' ),
                    '404'       =>  __( '404', 'widget-options' ),
                    'search'    =>  __( 'Search', 'widget-options' )
                );

    //unset builtin post types
    foreach ( array( 'revision', 'attachment', 'nav_menu_item' ) as $unset ) {
        unset( $types[ $unset ] );
    }

    //pro version only
    // $get_terms = array();
    // if( !empty( $widget_options['settings']['taxonomies'] ) && is_array( $widget_options['settings']['taxonomies'] ) ){
    //     foreach ( $widget_options['settings']['taxonomies'] as $tax_opt => $vall ) {
    //         $tax_name = 'widgetopts_taxonomy_'. $tax_opt;
    //         global $$tax_name;
    //         $get_terms[ $tax_opt ] = $$tax_name;
    //     }
    // }


    //get save values
    $options_values = '';
    $misc_values    = array();
    $pages_values   = array();
    $types_values   = array();
    $cat_values     = array();
    $tax_values     = array();
    $terms_values   = array();
    $acf_values   = array();
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

        if( isset( $args['params']['visibility']['tax_terms'] ) ){
            $terms_values = $args['params']['visibility']['tax_terms'];
        }

        if( isset( $args['params']['visibility']['acf'] ) ){
            $acf_values = $args['params']['visibility']['acf'];
        }

        if( isset( $args['params']['visibility']['selected'] ) ){
            $selected = $args['params']['visibility']['selected'];
        }

        if( isset( $args['params']['visibility']['main'] ) ){
            $main = $args['params']['visibility']['main'];
        }
    }

    ?>
    <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-visibility" class="extended-widget-opts-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-tabcontent-visibility">
        <div class="extended-widget-opts-visibility-m-tabs extended-widget-opts-inside-tabs">
            <input type="hidden" id="extended-widget-opts-visibility-m-selectedtab" value="<?php echo $main;?>" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][main]" />

            <ul class="extended-widget-opts-visibility-m-tabnav-ul">
                <li class="extended-widget-opts-visibility-tab-main">
                    <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-main" title="<?php _e( 'Home, Blog, Search, etc..', 'widget-options' );?>" ><?php _e( 'WordPress Pages', 'widget-options' );?></a>
                </li>
                <?php if( isset( $widget_options['acf'] ) && 'activate' == $widget_options['acf'] ):?>
                    <li class="extended-widget-opts-visibility-tab-main">
                        <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-acf" title="<?php _e( 'Advanced Custom Fields', 'widget-options' );?>" ><?php _e( 'ACF', 'widget-options' );?></a>
                    </li>
                <?php endif;?>
            </ul>

            <div id="extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-main" class="extended-widget-opts-visibility-tabcontent extended-widget-opts-inner-tabcontent">
                <p><strong><?php _e( 'Hide/Show', 'widget-options' );?></strong>
                <select class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][options]">
                    <option value="hide" <?php if( $options_values == 'hide' ){ echo 'selected="selected"'; }?> ><?php _e( 'Hide on checked pages', 'widget-options' );?></option>
                    <option value="show" <?php if( $options_values == 'show' ){ echo 'selected="selected"'; }?>><?php _e( 'Show on checked pages', 'widget-options' );?></option>
                </select>
                </p>

                <div class="extended-widget-opts-visibility-tabs extended-widget-opts-inside-tabs">
                    <input type="hidden" id="extended-widget-opts-visibility-selectedtab" value="<?php echo $selected;?>" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][selected]" />
                    <!--  start tab nav -->
                    <ul class="extended-widget-opts-visibility-tabnav-ul">
                        <?php if( isset( $widget_options['settings']['visibility'] ) &&
                                  isset( $widget_options['settings']['visibility']['misc'] ) &&
                                  '1' == $widget_options['settings']['visibility']['misc'] ){ ?>
                            <li class="extended-widget-opts-visibility-tab-visibility">
                                <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-misc" title="<?php _e( 'Home, Blog, Search, etc..', 'widget-options' );?>" ><?php _e( 'Misc', 'widget-options' );?></a>
                            </li>
                        <?php } ?>

                        <?php if( isset( $widget_options['settings']['visibility'] ) &&
                                  isset( $widget_options['settings']['visibility']['post_type'] ) &&
                                  '1' == $widget_options['settings']['visibility']['post_type'] ){ ?>
                            <li class="extended-widget-opts-visibility-tab-visibility">
                                <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-types" title="<?php _e( 'Pages & Custom Post Types', 'widget-options' );?>" ><?php _e( 'Post Types', 'widget-options' );?></a>
                            </li>
                        <?php } ?>

                        <?php if( isset( $widget_options['settings']['visibility'] ) &&
                                      isset( $widget_options['settings']['visibility']['taxonomies'] ) &&
                                      '1' == $widget_options['settings']['visibility']['taxonomies'] ){ ?>
                            <li class="extended-widget-opts-visibility-tab-visibility">
                                <a href="#extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-tax" title="<?php _e( 'Categories, Tags & Taxonomies', 'widget-options' );?>" ><?php _e( 'Taxonomies', 'widget-options' );?></a>
                            </li>
                        <?php } ?>
                        <div class="extended-widget-opts-clearfix"></div>
                    </ul><!--  end tab nav -->
                    <div class="extended-widget-opts-clearfix"></div>

                    <?php if( isset( $widget_options['settings']['visibility'] ) &&
                              isset( $widget_options['settings']['visibility']['misc'] ) &&
                              '1' == $widget_options['settings']['visibility']['misc'] ){ ?>
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
                                        <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][misc][<?php echo $key;?>]" id="<?php echo $args['id'];?>-opts-misc-<?php echo $key;?>" value="1" <?php echo $checked;?> />
                                        <label for="<?php echo $args['id'];?>-opts-misc-<?php echo $key;?>"><?php echo $value;?></label>
                                    </p>
                                <?php } ?>
                            </div>
                        </div><!--  end misc tab content -->
                    <?php } ?>

                    <?php if( isset( $widget_options['settings']['visibility'] ) &&
                              isset( $widget_options['settings']['visibility']['post_type'] ) &&
                              '1' == $widget_options['settings']['visibility']['post_type'] ){ ?>
                        <!--  start types tab content -->
                        <div id="extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-types" class="extended-widget-opts-visibility-tabcontent extended-widget-opts-inner-tabcontent">
                            <div class="extended-widget-opts-inner-lists" style="height: 230px;padding: 5px;overflow:auto;">
                                <h4 id="extended-widget-opts-pages"><?php _e( 'Pages', 'widget-options' );?> +/-</h4>
                                <div class="extended-widget-opts-pages">
                                    <?php 
                                    $page_class = new WidgetOpts_Pages_Checkboxes();
                                    $page_checkboxes = $page_class->walk( $widgetopts_pages, 0, $args );
                                    if ( $page_checkboxes ) {
                                        echo '<div class="widgetopts-ul-pages">' . $page_checkboxes . '</div>';
                                    }
                                    ?>
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
                                            <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][types][<?php echo $ptype;?>]" id="<?php echo $args['id'];?>-opts-types-<?php echo $ptype;?>" value="1" <?php echo $checked;?> />
                                            <label for="<?php echo $args['id'];?>-opts-types-<?php echo $ptype;?>"><?php echo stripslashes( $type->labels->name );?></label>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div><!--  end types tab content -->
                    <?php } ?>

                    <?php if( isset( $widget_options['settings']['visibility'] ) &&
                                  isset( $widget_options['settings']['visibility']['taxonomies'] ) &&
                                  '1' == $widget_options['settings']['visibility']['taxonomies'] ){ ?>
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
                                            <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][categories][<?php echo $cat->cat_ID;?>]" id="<?php echo $args['id'];?>-opts-categories-<?php echo $cat->cat_ID;?>" value="1" <?php echo $checked;?> />
                                            <label for="<?php echo $args['id'];?>-opts-categories-<?php echo $cat->cat_ID;?>"><?php echo $cat->cat_name;?></label>
                                        </p>
                                    <?php } ?>
                                </div>

                                <h4 id="extended-widget-opts-taxonomies"><?php _e( 'Taxonomies', 'widget-options' );?> +/-</h4>
                                <div class="extended-widget-opts-taxonomies">
                                    <?php foreach ( $taxonomies as $taxonomy ) {
                                            if( isset( $tax_values[ $taxonomy->name ] ) && $tax_values[ $taxonomy->name ] == '1' ){
                                                $checked = 'checked="checked"';
                                            }else{
                                                $checked = '';
                                            }
                                        ?>
                                        <p>
                                            <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][taxonomies][<?php echo $taxonomy->name;?>]" id="<?php echo $args['id'];?>-opts-taxonomies-<?php echo $taxonomy->name;?>" value="1" <?php echo $checked;?> />
                                            <label for="<?php echo $args['id'];?>-opts-taxonomies-<?php echo $taxonomy->name;?>"><?php echo $taxonomy->label;?></label> <?php if( isset( $taxonomy->object_type ) && isset( $taxonomy->object_type[0] ) ){ echo ' <small>- '. $taxonomy->object_type[0] .'</small>'; } ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div><!--  end tax tab content -->
                    <?php } ?>
                    </div><!--  end .extended-widget-opts-visibility-tabs -->
            </div><!-- End WordPress Pages tab -->

            <?php if( isset( $widget_options['acf'] ) && 'activate' == $widget_options['acf'] ):?>
                <div id="extended-widget-opts-visibility-tab-<?php echo $args['id'];?>-acf" class="extended-widget-opts-visibility-tabcontent extended-widget-opts-inner-tabcontent">
                    <?php
                    $fields = array();

                    if ( function_exists( 'acf_get_field_groups' ) ) {
                        $groups = acf_get_field_groups();
                        if ( is_array( $groups ) ) {
                            foreach ( $groups as $group ) {
                                $fields[ $group['ID'] ] = array( 'title' => $group['title'], 'fields' => acf_get_fields( $group ) );
                            }
                        }
                    }else{
                        $groups = apply_filters( 'acf/get_field_groups', array() );
                        if ( is_array( $groups ) ) {
                            foreach ( $groups as $group ) {
                                $fields[ $group['id'] ] = array( 'title' => $group['title'], 'fields' => apply_filters( 'acf/field_group/get_fields', array(), $group['id'] ) );
                            }
                        }
                    }
                    ?>
                    <p><strong><?php _e( 'Hide/Show', 'widget-options' );?></strong>
                    <select class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][acf][visibility]">
                        <option value="hide" <?php echo ( isset( $acf_values['visibility'] ) && $acf_values['visibility'] == 'hide' ) ? 'selected="selected"' : ''?> ><?php _e( 'Hide when Condition\'s met', 'widget-options' );?></option>
                        <option value="show" <?php echo ( isset( $acf_values['visibility'] ) && $acf_values['visibility'] == 'show' ) ? 'selected="selected"' : ''?>><?php _e( 'Show when Condition\'s met', 'widget-options' );?></option>
                    </select>
                    </p>

                    <p><strong><?php _e( 'Choose ACF Field', 'widget-options' );?></strong>
                    <select class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][acf][field]">
                        <option value=""><?php _e( 'Select Field', 'widget-options' );?></option>
                        <?php 
                        if( !empty( $fields ) ){
                            foreach( $fields as $k => $field ){?>
                                <optgroup label="<?php echo $field['title'];?>">
                                    <?php foreach( $field['fields'] as $key => $f ){ ?>
                                        <option value="<?php echo $f['key'];?>" <?php echo ( isset( $acf_values['field'] ) && $acf_values['field'] == $f['key'] ) ? 'selected="selected"' : ''?> ><?php echo $f['label'];?></option>
                                    <?php } ?>
                                </optgroup>
                        <?php } 
                        } ?>
                    </select>
                    </p>
                    <p><strong><?php _e( 'Condition', 'widget-options' );?></strong>
                        <select class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][acf][condition]">
                            <option value=""><?php _e( 'Select Condition', 'widget-options' );?></option>
                            <optgroup label="<?php _e( 'Conditional', 'widget-options' );?>">
                                <option value="equal" <?php echo ( isset( $acf_values['condition'] ) && $acf_values['condition'] == 'equal' ) ? 'selected="selected"' : ''?> ><?php _e( 'Is Equal to', 'widget-options' );?></option>
                                <option value="not_equal" <?php echo ( isset( $acf_values['condition'] ) && $acf_values['condition'] == 'not_equal' ) ? 'selected="selected"' : ''?> ><?php _e( 'Is Not Equal to', 'widget-options' );?></option>
                                <option value="contains" <?php echo ( isset( $acf_values['condition'] ) && $acf_values['condition'] == 'contains' ) ? 'selected="selected"' : ''?> ><?php _e( 'Contains', 'widget-options' );?></option>
                                <option value="not_contains" <?php echo ( isset( $acf_values['condition'] ) && $acf_values['condition'] == 'not_contains' ) ? 'selected="selected"' : ''?> ><?php _e( 'Does Not Contain', 'widget-options' );?></option>
                            </optgroup>
                            <optgroup label="<?php _e( 'Value Based', 'widget-options' );?>">
                                <option value="empty" <?php echo ( isset( $acf_values['condition'] ) && $acf_values['condition'] == 'empty' ) ? 'selected="selected"' : ''?> ><?php _e( 'Is Empty', 'widget-options' );?></option>
                                <option value="not_empty" <?php echo ( isset( $acf_values['condition'] ) && $acf_values['condition'] == 'not_empty' ) ? 'selected="selected"' : ''?> ><?php _e( 'Is Not Empty', 'widget-options' );?></option>
                            </optgroup>
                        </select>
                    </p>
                    <p><strong><?php _e( 'Conditional Value', 'widget-options' );?></strong>
                        <textarea name="<?php echo $args['namespace'];?>[extended_widget_opts][visibility][acf][value]" id="<?php echo $args['id'];?>-opts-acf-value" class="widefat widgetopts-acf-conditional"><?php echo (isset( $acf_values['value'] )) ? $acf_values['value'] : ''?></textarea>
                    </p>
                </div><?php endif; ?>
        </div><!--  end main tab -->

    </div>
<?php
}
add_action( 'extended_widget_opts_tabcontent', 'widgetopts_tabcontent_visibility'); ?>
