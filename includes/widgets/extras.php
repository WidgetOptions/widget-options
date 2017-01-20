<?php
/**
 * Extra Functions
 *
 * Collections of extra functions to avoid repeatition
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       4.0
 */

 //create separate function returning classes for reuse
if( !function_exists( 'widgetopts_classes_generator' ) ){
    function widgetopts_classes_generator( $opts, $tabs, $settings, $so = false ){
        if( !empty( $opts ) && is_array( $opts ) ){
            $classes        = array();
            $devices        = isset( $opts['devices'] )     ? $opts['devices'] : '';
            $alignment      = isset( $opts['alignment'] )   ? $opts['alignment'] : '';
            $columns        = isset( $opts['column'] )      ? $opts['column'] : '';
            $clearfix       = isset( $opts['clearfix'] )    ? $opts['clearfix'] : '';
            $custom_class   = isset( $opts['class'] )       ? $opts['class'] : '';
            $abbr           = array(
                                'mobile'    =>  'xs',
                                'tablet'    =>  'sm',
                                'desktop'   =>  'md',
                            );
            if( isset( $devices['options'] ) ){
                unset( $devices['options'] );
            }

            if( 'activate' == $tabs['devices'] ){
                //devices visibility
                if( !empty( $devices ) ){
                    $device_opts    = ( isset( $opts['devices']['options'] ) ) ? $opts['devices']['options'] : 'hide';
                    $classes[] = 'extendedwopts-' . $device_opts ;

                    foreach ($devices as $key => $value) {
                        $classes[] = 'extendedwopts-' . $key;
                    }
                }
            }

            if( 'activate' == $tabs['alignment'] ){
                //alignment
                if( !empty( $alignment ) ){
                    foreach ($alignment as $k => $v) {
                        if( 'default' != $v ){
                            $classes[] = 'extendedwopts-' . $abbr[ $k ] . '-'. $v ;
                        }
                    }
                }
            }

            if( isset( $tabs['columns'] ) && 'activate' == $tabs['columns'] ){
                //set classes for columns
                if( !empty( $columns ) ){
                    $classes[] = 'extendedwopts-col';

                    foreach ($columns as $ckey => $cvalue) {
                        if( $cvalue == '7' ){
                            $cvalue = 6;
                        }
                        $classes[] = 'col-' . $abbr[ $ckey ] . '-'. $cvalue ;
                    }
                }

                //set clearfix for floating
                if( !empty( $clearfix ) ){
                    foreach ($clearfix as $c_key => $c_value) {
                        $classes[] = 'clearfix-'. $c_key;
                    }
                }
            }

            if( 'activate' == $tabs['classes'] && isset( $settings['classes'] ) ){
                //classes & ID
                // $options    = get_option('extwopts_class_settings');
                $predefined = array();
                if( isset( $settings['classes'] ) && isset( $settings['classes']['classlists'] ) && !empty( $settings['classes']['classlists'] ) ){
                    $predefined = $settings['classes']['classlists'];
                }

                //don't add any classes when settings is set to predefined or hide
                if( !isset( $settings['classes']['type'] ) ||
                    ( isset(  $settings['classes']['type'] ) && !in_array(  $settings['classes']['type'] , array( 'hide', 'predefined' ) ) ) ){
                    if( is_array( $custom_class ) && isset( $custom_class['classes'] ) && !empty( $custom_class['classes'] ) ){
                        $classes[] = $custom_class['classes'];
                    }
                }

                //don't add any classes when settings is set to text or hide
                if( !isset(  $settings['classes']['type'] ) ||
                    ( isset(  $settings['classes']['type'] ) && !in_array( $settings['classes']['type'] , array( 'hide', 'text' ) ) ) ){
                    if( is_array( $predefined ) && !empty( $predefined ) ){
                        $predefined = array_unique( $predefined );
                        if( isset( $custom_class['predefined'] ) && is_array( $custom_class['predefined'] ) ){
                            $filtered = array_intersect( $predefined, $custom_class['predefined'] );
                            if( !empty( $filtered ) ){
                                $classes = array_merge( $classes,  $filtered );
                                // $classes[] = implode( ' ', $filtered );
                                // $classes[] = ' ';
                            }
                        }
                    }
                }
            }

            if( isset( $tabs['fixed'] ) && 'activate' == $tabs['fixed'] ){
                //add fixed class to widget
                if( isset( $custom_class['fixed'] ) && !empty( $custom_class['fixed'] ) ){
                    $classes[] = 'widgetopts-fixed-this';
                }
            }

            if( isset( $tabs['animation'] ) && 'activate' == $tabs['animation'] ){
                //add animation class
                if( isset( $custom_class['animation'] ) && !empty( $custom_class['animation'] ) ){
                    $classes[] = 'widgetopts-animate';
                }
                if( isset( $custom_class['hidden'] ) && !empty( $custom_class['hidden'] ) ){
                    $classes[] = 'widgetopts-animate-hide';
                }
            }

            if( $so && 'activate' == $tabs['hide_title'] ){
                //add fixed class to widget
                if( isset( $custom_class['title'] ) && !empty( $custom_class['title'] ) ){
                    $classes[] = 'widgetopts-hide_title';
                }
            }

            return $classes;
        }
    }
}

//create separate function returning classes for reuse
if( !function_exists( 'widgetopts_styles_generator' ) ){
    function widgetopts_styles_generator( $widget_id, $opts, $tabs, $settings, $so = false ){
        if( isset( $tabs['styling'] ) && 'activate' == $tabs['styling'] ){
            //styling
            $style      = '';
            $styling    = isset( $opts['styling'] ) ? $opts['styling'] : '';
            $element    = '.widget';

            if( $so ){
                $element = '.so-panel';
            }

            if( !empty( $styling ) ){
                $style = '<style type="text/css">';

                if( isset( $settings['styling'] ) && isset( $settings['styling']['widgets'] ) ){
                    if( isset( $styling['bg_image'] ) && !empty( $styling['bg_image'] ) ){
                        $style .= $element .'#'. $widget_id .'{ background-image: url('. $styling['bg_image'] .') !important; -webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover; }';
                    }
                    if( isset( $styling['background'] ) && !empty( $styling['background'] ) ){
                        $style .= $element .'#'. $widget_id .'{ background-color: '. $styling['background'] .' !important; }';
                    }
                    if( isset( $styling['background_hover'] ) && !empty( $styling['background_hover'] ) ){
                        $style .= $element .'#'. $widget_id .':hover{ background-color: '. $styling['background_hover'] .' !important; }';
                    }
                    if( isset( $styling['heading'] ) && !empty( $styling['heading'] ) ){
                        $style .= $element .'#'. $widget_id .' .widgettitle, '. $element .'#'. $widget_id .' .widget-title, '. $element .'#'. $widget_id .' h1, '. $element .'#'. $widget_id .' h2, '. $element .'#'. $widget_id .' h3, '. $element .'#'. $widget_id .' h4, '. $element .'#'. $widget_id .' h5, '. $element .'#'. $widget_id .' h6{ color: '. $styling['heading'] .' !important; }';
                    }
                    if( isset( $styling['text'] ) && !empty( $styling['text'] ) ){
                        $style .= $element .'#'. $widget_id .', '. $element .'#'. $widget_id .' p, '. $element .'#'. $widget_id .' li{ color: '. $styling['text'] .' !important; }';
                    }
                    if( isset( $styling['links'] ) && !empty( $styling['links'] ) ){
                        $style .= $element .'#'. $widget_id .' a{ color: '. $styling['links'] .' !important; }';
                    }
                    if( isset( $styling['links_hover'] ) && !empty( $styling['links_hover'] ) ){
                        $style .= $element .'#'. $widget_id .' a:hover{ color: '. $styling['links_hover'] .' !important; }';
                    }
                    if( isset( $styling['border_width'] ) && !empty( $styling['border_width'] ) ){
                        $style .= $element .'#'. $widget_id .'{ border-width: '. $styling['border_width'] .'px !important; }';
                    }
                    if( isset( $styling['border_color'] ) && !empty( $styling['border_color'] ) ){
                        $style .= $element .'#'. $widget_id .'{ border-color: '. $styling['border_color'] .' !important; }';
                    }
                    if( isset( $styling['border_type'] ) && !empty( $styling['border_type'] ) ){
                        $style .= $element .'#'. $widget_id .'{ box-sizing: border-box; border-style: '. $styling['border_type'] .' !important; }';
                    }
                }
                if( isset( $settings['styling'] ) && isset( $settings['styling']['forms'] ) ){
                    $style .= $element .'#'. $widget_id .' input, '. $element .'#'. $widget_id .' textarea{';
                        if( isset( $styling['background_input'] ) && !empty( $styling['background_input'] ) ){
                            $style .= 'background: '. $styling['background_input'] .' !important;';
                        }
                        if( isset( $styling['text_input'] ) && !empty( $styling['text_input'] ) ){
                            $style .= 'color: '. $styling['text_input'] .' !important;';
                        }
                        if( isset( $styling['border_color_input'] ) && !empty( $styling['border_color_input'] ) ){
                            $style .= 'border-color: '. $styling['border_color_input'] .' !important;';
                        }
                        if( isset( $styling['border_type_input'] ) && !empty( $styling['border_type_input'] ) ){
                            $style .= 'border-style: '. $styling['border_type_input'] .' !important;';
                        }
                        if( isset( $styling['border_width_input'] ) && !empty( $styling['border_width_input'] ) ){
                            $style .= 'border-width: '. $styling['border_width_input'] .'px !important;';
                        }
                    $style .= '}';

                    $style .= $element .'#'. $widget_id .' input[type="submit"], '. $element .'#'. $widget_id .' button{';
                        if( isset( $styling['background_submit'] ) && !empty( $styling['background_submit'] ) ){
                            $style .= 'background: '. $styling['background_submit'] .' !important;';
                        }
                        if( isset( $styling['text_submit'] ) && !empty( $styling['text_submit'] ) ){
                            $style .= 'color: '. $styling['text_submit'] .' !important;';
                        }
                        if( isset( $styling['border_color_submit'] ) && !empty( $styling['border_color_submit'] ) ){
                            $style .= 'border-color: '. $styling['border_color_submit'] .' !important;';
                        }
                        if( isset( $styling['border_type_submit'] ) && !empty( $styling['border_type_submit'] ) ){
                            $style .= 'border-style: '. $styling['border_type_submit'] .' !important;';
                        }
                        if( isset( $styling['border_width_submit'] ) && !empty( $styling['border_width_submit'] ) ){
                            $style .= 'border-width: '. $styling['border_width_submit'] .'px !important;';
                        }
                    $style .= '}';
                    if( isset( $styling['background_submit_hover'] ) && !empty( $styling['background_submit_hover'] ) ){
                        $style .= $element .'#'. $widget_id .' input[type="submit"]:hover, '. $element .'#'. $widget_id .' button:hover{ background: '. $styling['background_submit_hover'] .' !important; }';
                    }
                }

                if( isset( $settings['styling'] ) && isset( $settings['styling']['other'] ) ){
                    if( isset( $styling['list_border_color'] ) && !empty( $styling['list_border_color'] ) ){
                        $style .= $element .'#'. $widget_id .' li{ border-color: '. $styling['list_border_color'] .' !important; }';
                    }
                    if( isset( $styling['table_border_color'] ) && !empty( $styling['table_border_color'] ) ){
                        $style .= $element .'#'. $widget_id .' table td, '. $element .'#'. $widget_id .' table th{ border-color: '. $styling['table_border_color'] .' !important; }';
                    }
                }

                return $style .= '</style>';
            }
        }
    }
}

 ?>
