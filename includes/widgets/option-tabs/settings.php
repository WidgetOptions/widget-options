<?php
/**
 * Settings Widget Options
 *
 * @copyright   Copyright (c) 2015, Jeffrey Carandang
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Settings Widget Options Tab
 *
 * @since 1.0
 * @return void
 */

 /**
 * Called on 'extended_widget_opts_tabs'
 * create new tab navigation for alignment options
 */
if( !function_exists( 'widgetopts_tab_settings' ) ):
   function widgetopts_tab_settings( $args ){ ?>
       <li class="extended-widget-opts-tab-class">
          <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-class" title="<?php _e( 'Class,ID & Logic', 'widget-options' );?>" ><span class="dashicons dashicons-admin-generic"></span> <span class="tabtitle"><?php _e( 'Other Settings', 'widget-options' );?></span></a>
      </li>
   <?php
   }
   add_action( 'extended_widget_opts_tabs', 'widgetopts_tab_settings' );
endif;

/**
 * Called on 'extended_widget_opts_tabcontent'
 * create new tab content options for alignment options
 */
if( !function_exists( 'widgetopts_tabcontent_settings' ) ):
   function widgetopts_tabcontent_settings( $args ){
       global $widget_options;

       $id         = '';
       $classes    = '';
       $logic      = '';
       $selected   = 0;
       $check      = '';
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
           if( isset( $args['params']['class']['title'] ) && $args['params']['class']['title'] == '1' ){
               $check = 'checked="checked"';
           }
       }

       $predefined = array();
       if( isset( $widget_options['settings']['classes'] ) && isset( $widget_options['settings']['classes']['classlists'] ) && !empty( $widget_options['settings']['classes']['classlists'] ) ){
           $predefined = $widget_options['settings']['classes']['classlists'];
       }
       ?>
       <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-class" class="extended-widget-opts-tabcontent extended-widget-opts-inside-tabcontent extended-widget-opts-tabcontent-class">

           <div class="extended-widget-opts-settings-tabs extended-widget-opts-inside-tabs">
               <input type="hidden" id="extended-widget-opts-settings-selectedtab" value="<?php echo $selected;?>" name="<?php echo $args['namespace'];?>[extended_widget_opts][class][selected]" />
               <!--  start tab nav -->
               <ul class="extended-widget-opts-settings-tabnav-ul">
                   <?php if( 'activate' == $widget_options['hide_title'] ){ ?>
                       <li class="extended-widget-opts-settings-tab-title">
                           <a href="#extended-widget-opts-settings-tab-<?php echo $args['id'];?>-title" title="<?php _e( 'Misc', 'widget-options' );?>" ><?php _e( 'Misc', 'widget-options' );?></a>
                       </li>
                   <?php } ?>

                   <?php if( 'activate' == $widget_options['classes'] ){ ?>
                       <li class="extended-widget-opts-settings-tab-class">
                           <a href="#extended-widget-opts-settings-tab-<?php echo $args['id'];?>-class" title="<?php _e( 'Class & ID', 'widget-options' );?>" ><?php _e( 'Class & ID', 'widget-options' );?></a>
                       </li>
                   <?php } ?>

                    <li class="extended-widget-opts-settings-tab-animation">
                        <a href="#extended-widget-opts-settings-tab-<?php echo $args['id'];?>-animation" title="<?php _e( 'Animation', 'widget-options' );?>" ><?php _e( 'Animation', 'widget-options' );?></a>
                    </li>

                   <?php if( 'activate' == $widget_options['logic'] ){ ?>
                       <li class="extended-widget-opts-settings-tab-logic">
                           <a href="#extended-widget-opts-settings-tab-<?php echo $args['id'];?>-logic" title="<?php _e( 'Display Logic', 'widget-options' );?>" ><?php _e( 'Logic', 'widget-options' );?></a>
                       </li>
                   <?php } ?>
                   <div class="extended-widget-opts-clearfix"></div>
               </ul><!--  end tab nav -->
               <div class="extended-widget-opts-clearfix"></div>

               <?php if( 'activate' == $widget_options['hide_title'] ){ ?>
                   <!--  start title tab content -->
                   <div id="extended-widget-opts-settings-tab-<?php echo $args['id'];?>-title" class="extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent">
                       <div class="widget-opts-title">
                           <?php if( 'activate' == $widget_options['hide_title'] ){ ?>
                               <p class="widgetopts-subtitle"><?php _e( 'Hide Widget Title', 'widget-options' );?></p>
                               <p>
                                   <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][class][title]" id="opts-class-title-<?php echo $args['id'];?>" value="1" <?php echo $check;?> />
                                   <label for="opts-class-title-<?php echo $args['id'];?>"><?php _e( 'Check to hide widget title', 'widget-options' );?></label>
                               </p>
                           <?php } ?>
                       </div>
                   </div><!--  end title tab content -->
               <?php } ?>

               <?php if( 'activate' == $widget_options['classes'] ){ ?>
                   <!--  start class tab content -->
                   <div id="extended-widget-opts-settings-tab-<?php echo $args['id'];?>-class" class="extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent">
                       <div class="widget-opts-class">
                           <table class="form-table">
                           <tbody>
                               <?php if( isset( $widget_options['settings']['classes'] ) && ( isset( $widget_options['settings']['classes']['id'] ) && '1' == $widget_options['settings']['classes']['id'] ) ){?>
                                   <tr valign="top" class="widgetopts_id_fld">
                                       <td scope="row">
                                           <strong><?php _e( 'Widget CSS ID:', 'widget-options' );?></strong><br />
                                           <input type="text" id="opts-class-id-<?php echo $args['id'];?>" class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][class][id]" value="<?php echo $id;?>" />
                                       </td>
                                   </tr>
                               <?php } ?>

                               <?php if( !isset( $widget_options['settings']['classes'] ) ||
                                        ( isset( $widget_options['settings']['classes'] ) && isset( $widget_options['settings']['classes']['type'] ) && !in_array( $widget_options['settings']['classes']['type'] , array( 'hide', 'predefined' ) ) ) ){?>
                                   <tr valign="top">
                                       <td scope="row">
                                           <strong><?php _e( 'Widget CSS Classes:', 'widget-options' );?></strong><br />
                                           <input type="text" id="opts-class-classes-<?php echo $args['id'];?>" class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][class][classes]" value="<?php echo $classes;?>" />
                                           <small><em><?php _e( 'Separate each class with space.', 'widget-options' );?></em></small>
                                       </td>
                                   </tr>
                               <?php } ?>
                               <?php if( !isset( $widget_options['settings']['classes'] ) ||
                                        ( isset( $widget_options['settings']['classes'] ) && isset( $widget_options['settings']['classes']['type'] ) && !in_array( $widget_options['settings']['classes']['type'] , array( 'hide', 'text' ) ) ) ){?>
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
                                                               <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][class][predefined][]" id="<?php echo $args['id'];?>-opts-class-<?php echo $key;?>" value="<?php echo $value;?>" <?php echo $checked;?> />
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
               <?php } ?>

               <!--  start Animation tab demo -->
               <?php 
                    $animation_array = array(
                        'Attention Seekers' => array(
                                                'bounce',
                                                'flash',
                                                'pulse',
                                                'rubberBand',
                                                'shake',
                                                'swing',
                                                'tada',
                                                'wobble',
                                                'jello'
                                            ) ,
                        'Bouncing Entrances' => array(
                                                'bounceIn',
                                                'bounceInDown',
                                                'bounceInLeft',
                                                'bounceInRight',
                                                'bounceInUp',
                                            ),

                        'Fading Entrances'   => array(
                                                'fadeIn',
                                                'fadeInDown',
                                                'fadeInDownBig',
                                                'fadeInLeft',
                                                'fadeInLeftBig',
                                                'fadeInRight',
                                                'fadeInRightBig',
                                                'fadeInUp',
                                                'fadeInUpBig'
                                            ),
                        'Flippers'          => array(
                                                'flip',
                                                'flipInX',
                                                'flipInY',
                                                'flipOutX',
                                                'flipOutY'
                                            ),
                        'Lightspeed'        => array(
                                                'lightSpeedIn',
                                                'lightSpeedOut'
                                            ),

                        'Rotating Entrances' => array(
                                                'rotateIn',
                                                'rotateInDownLeft',
                                                'rotateInDownRight',
                                                'rotateInUpLeft',
                                                'rotateInUpRight'
                                            ),
                        'Sliding Entrances' => array(
                                                'slideInUp',
                                                'slideInDown',
                                                'slideInLeft',
                                                'slideInRight'
                                            ),
                        'Zoom Entrances'    => array(
                                                'zoomIn',
                                                'zoomInDown',
                                                'zoomInLeft',
                                                'zoomInRight',
                                                'zoomInUp'
                                            ),
                        'Specials'          => array(
                                                'hinge',
                                                'rollIn'
                                            )
                    ); ?>
                    <!--  start animation tab content -->
                    <div id="extended-widget-opts-settings-tab-<?php echo $args['id'];?>-animation" class="extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent">
                        <div class="widget-opts-animation">
                            <div class="extended-widget-opts-demo-feature">
                                <div class="extended-widget-opts-demo-warning">
                                    <p class="widgetopts-unlock-features">
                                        <span class="dashicons dashicons-lock"></span><br>
                                        Unlock all Features<br>
                                        <a href="https://widget-options.com/?utm_source=wordpressadmin&amp;utm_medium=widgettabs&amp;utm_campaign=widgetoptsprotab" class="button-primary" target="_blank">Learn More</a>      
                                    </p>
                                </div>
                                <p>
                                    <label for="opts-class-animation-<?php echo $args['id'];?>"><?php _e( 'Animation Type', 'widget-options' );?></label>
                                    <br />
                                    <select class="widefat" readonly>
                                        <option value=""><?php _e( 'None', 'widget-options' );?></option>
                                        <?php foreach( $animation_array as $group => $anims ){ ?>
                                            <optgroup label="<?php _e( $group, 'widget-options' );?>">
                                                <?php foreach( $anims as $anim => $aname ){ ?>
                                                    <option value="<?php echo $aname;?>"><?php _e( $aname, 'widget-options' )?></option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php } ?>
                                    </select>
                                    <small><em><?php _e( 'The type of animation for this event.', 'widget-options' );?></em></small>
                                </p>

                                <p>
                                    <label for="opts-class-event-<?php echo $args['id'];?>"><?php _e( 'Animation Event', 'widget-options' );?></label>
                                    <br  />
                                    <select class="widefat" readonly>
                                        <option value="enters"><?php _e( 'Element Enters Screen', 'widget-options' );?></option>
                                        <option value="onScreen"><?php _e( 'Element In Screen', 'widget-options' );?></option>
                                        <option value="pageLoad"><?php _e( 'Page Load', 'widget-options' );?></option>
                                    </select>
                                    <small><em><?php _e( 'The event that triggers the animation', 'widget-options' );?></em></small>
                                </p>

                                <p>
                                    <label for="opts-class-speed-<?php echo $args['id'];?>"><?php _e( 'Animation Speed', 'widget-options' );?></label>
                                    <br  />
                                    <input type="text" class="widefat" readonly />
                                    <small><em><?php _e( 'How many seconds the incoming animation should lasts.', 'widget-options' );?></em></small>
                                </p>

                                <p>
                                    <label for="opts-class-offset-<?php echo $args['id'];?>"><?php _e( 'Screen Offset', 'widget-options' );?></label>
                                    <br  />
                                    <input type="text" readonly />
                                    <small><em><?php _e( 'How many pixels above the bottom of the screen must the widget be before animating.', 'widget-options' );?></em></small>
                                </p>

                                <p>
                                    <label for="opts-class-hidden-<?php echo $args['id'];?>"><?php _e( 'Hide Before Animation', 'widget-options' );?></label>
                                    <br  />
                                    <input type="checkbox" value="1" readonly />
                                    <label for="opts-class-hidden-<?php echo $args['id'];?>"><?php _e( 'Enabled', 'widget-options' );?></label><br />
                                    <small><em><?php _e( 'Hide widget before animating.', 'widget-options' );?></em></small>
                                </p>

                                <p>
                                    <label for="opts-class-delay-<?php echo $args['id'];?>"><?php _e( 'Animation Delay', 'widget-options' );?></label>
                                    <br  />
                                    <input type="text" class="widefat" readonly />
                                    <small><em><?php _e( 'Number of seconds after the event to start the animation.', 'widget-options' );?></em></small>
                                </p>
                            </div>
                        </div>
                    </div><!--  end animation tab content -->
               <!--  end Animation tab demo -->

               <?php if( 'activate' == $widget_options['logic'] ){ ?>
                   <!--  start logic tab content -->
                   <div id="extended-widget-opts-settings-tab-<?php echo $args['id'];?>-logic" class="extended-widget-opts-settings-tabcontent extended-widget-opts-inner-tabcontent">
                       <div class="widget-opts-logic">
                           <p><small><?php _e( 'The text field lets you use <a href="http://codex.wordpress.org/Conditional_Tags" target="_blank">WP Conditional Tags</a>, or any general PHP code.', 'widget-options' );?></small></p>
                           <textarea class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][class][logic]"><?php echo stripslashes( $logic );?></textarea>

                           <?php if( !isset( $widget_options['settings']['logic'] ) ||
                                    ( isset( $widget_options['settings']['logic']  ) && !isset( $widget_options['settings']['logic']['notice']  ) ) ){ ?>
                                         <p><a href="#" class="widget-opts-toggler-note"><?php _e( 'Click to Toggle Note', 'widget-options' );?></a></p>
                                         <p class="widget-opts-toggle-note"><small><?php _e( 'PLEASE NOTE that the display logic you introduce is EVAL\'d directly. Anyone who has access to edit widget appearance will have the right to add any code, including malicious and possibly destructive functions. There is an optional filter <em>"widget_options_logic_override"</em> which you can use to bypass the EVAL with your own code if needed.', 'widget-options' );?></small></p>
                           <?php } ?>
                       </div>
                   </div><!--  end logiv tab content -->
               <?php } ?>

           </div><!-- end .extended-widget-opts-settings-tabs -->


       </div>
   <?php
   }
   add_action( 'extended_widget_opts_tabcontent', 'widgetopts_tabcontent_settings');
endif; ?>
