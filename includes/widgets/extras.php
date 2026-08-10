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
                    $classes[] = sanitize_html_class('extendedwopts-' . $device_opts);

                    foreach ($devices as $key => $value) {
                        $classes[] = sanitize_html_class('extendedwopts-' . $key);
                    }
                }
            }

            if( 'activate' == $tabs['alignment'] ){
                //alignment
                if( !empty( $alignment ) ){
                    foreach ($alignment as $k => $v) {
                        if( 'default' != $v ){
                            $classes[] = sanitize_html_class('extendedwopts-' . $abbr[ $k ] . '-'. $v);
                        }
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
                        $tmpClasses = explode(" ", $custom_class['classes']);
                        if(!empty($tmpClasses)){
                            foreach($tmpClasses as $c) {
                                $classes[] = sanitize_html_class($c);
                            }
                        }
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

            if( $so && 'activate' == $tabs['hide_title'] ){
                //add fixed class to widget
                if( isset( $custom_class['title'] ) && !empty( $custom_class['title'] ) ){
                    $classes[] = 'widgetopts-hide_title';
                }
            }

            return apply_filters( 'widgetopts_get_classes', $classes );
        }
    }
}

/**
 * Apply an ID and/or extra classes to the first opening tag of a markup string.
 *
 * Never rewrite markup by searching for `id="` / `class="` anywhere in the
 * string: those substrings also occur *inside* other attributes' values, and
 * replacing them there breaks the quoting of the surrounding tag. Contributor
 * supplied block markup such as
 *
 *     <p title="id=" class='" tabindex=1 autofocus onfocus=alert(1)//'>x</p>
 *
 * is inert as written, but a naive `id="[^"]*` replacement closes the `title`
 * value early and promotes the rest of the single quoted `class` value into
 * live attributes — stored XSS.
 *
 * @since 4.2.6
 *
 * @param string $html           Markup to modify.
 * @param string $id_to_add      ID to set, already sanitized. Empty to leave alone.
 * @param string $classes_to_add Space separated classes to add, already sanitized.
 * @return string Modified markup, or the input untouched when no tag was found.
 */
if( !function_exists( 'widgetopts_apply_tag_attributes' ) ){
    function widgetopts_apply_tag_attributes( $html, $id_to_add = '', $classes_to_add = '' ){
        if( !is_string( $html ) || '' === $html ){
            return $html;
        }

        $id_to_add      = is_string( $id_to_add ) ? trim( $id_to_add ) : '';
        $classes_to_add = is_string( $classes_to_add ) ? trim( $classes_to_add ) : '';

        if( '' === $id_to_add && '' === $classes_to_add ){
            return $html;
        }

        // Preferred path (WP 6.2+): a real HTML parser owns the quoting, so
        // nothing we write can escape its attribute value.
        if( class_exists( 'WP_HTML_Tag_Processor' ) ){
            $processor = new WP_HTML_Tag_Processor( $html );
            if( !$processor->next_tag() ){
                return $html;
            }

            if( '' !== $id_to_add ){
                $processor->set_attribute( 'id', $id_to_add );
            }

            if( '' !== $classes_to_add ){
                $classes = preg_split( '/\s+/', $classes_to_add, -1, PREG_SPLIT_NO_EMPTY );
                foreach( (array) $classes as $class ){
                    $processor->add_class( $class );
                }
            }

            return $processor->get_updated_html();
        }

        return widgetopts_apply_tag_attributes_fallback( $html, $id_to_add, $classes_to_add );
    }
}

/**
 * Attribute writer for installs without the HTML API (WordPress below 6.2).
 *
 * Tokenizes the first opening tag and rebuilds it. Quoted values are consumed
 * atomically, so a `>` or a stray quote inside somebody else's attribute value
 * can no longer be mistaken for the end of the tag. When the tokenizer cannot
 * account for the attribute area character for character the markup is left
 * untouched rather than risk mangling it.
 *
 * @since 4.2.6
 *
 * @param string $html           Markup to modify.
 * @param string $id_to_add      ID to set, or '' to leave alone.
 * @param string $classes_to_add Space separated classes to add, or ''.
 * @return string Modified markup, or the input untouched.
 */
if( !function_exists( 'widgetopts_apply_tag_attributes_fallback' ) ){
    function widgetopts_apply_tag_attributes_fallback( $html, $id_to_add, $classes_to_add ){
        $attribute = '(?:\s+[^\s"\'>\/=]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s"\'=<>`]*))?)';
        $tag_re    = '#<([a-zA-Z][^\s\/>]*)(' . $attribute . '*)\s*(\/?)>#';

        if( !preg_match( $tag_re, $html, $tag, PREG_OFFSET_CAPTURE ) ){
            return $html;
        }

        $attrs  = $tag[2][0];
        $parsed = array();

        if( '' !== $attrs ){
            preg_match_all(
                '#\s+([^\s"\'>\/=]+)(?:\s*=\s*("[^"]*"|\'[^\']*\'|[^\s"\'=<>`]*))?#',
                $attrs,
                $tokens,
                PREG_SET_ORDER
            );

            $consumed = 0;
            foreach( $tokens as $token ){
                $consumed += strlen( $token[0] );
                $parsed[]  = array(
                    'name'  => $token[1],
                    'value' => isset( $token[2] ) ? widgetopts_unquote_attribute_value( $token[2] ) : null,
                );
            }

            //anything unaccounted for is markup we do not understand
            if( $consumed !== strlen( $attrs ) ){
                return $html;
            }
        }

        $has_id    = false;
        $has_class = false;

        foreach( $parsed as $index => $attr ){
            $name = strtolower( $attr['name'] );

            if( 'id' === $name && !$has_id && '' !== $id_to_add ){
                $parsed[ $index ]['value'] = $id_to_add;
                $has_id = true;
            } elseif( 'class' === $name && !$has_class && '' !== $classes_to_add ){
                $parsed[ $index ]['value'] = trim( $classes_to_add . ' ' . (string) $attr['value'] );
                $has_class = true;
            }
        }

        if( '' !== $id_to_add && !$has_id ){
            $parsed[] = array( 'name' => 'id', 'value' => $id_to_add );
        }

        if( '' !== $classes_to_add && !$has_class ){
            $parsed[] = array( 'name' => 'class', 'value' => $classes_to_add );
        }

        $rebuilt = '';
        foreach( $parsed as $attr ){
            $rebuilt .= ' ' . $attr['name'];
            if( null !== $attr['value'] ){
                // Values are re-emitted double quoted, so any double quote
                // carried over from a single quoted value must be encoded.
                $rebuilt .= '="' . str_replace( '"', '&quot;', $attr['value'] ) . '"';
            }
        }

        $new_tag = '<' . $tag[1][0] . $rebuilt . ( '' !== $tag[3][0] ? ' /' : '' ) . '>';

        return substr_replace( $html, $new_tag, $tag[0][1], strlen( $tag[0][0] ) );
    }
}

/**
 * Strip the surrounding quotes from a raw attribute value token.
 *
 * @since 4.2.6
 *
 * @param string $raw Raw value as captured, possibly quoted.
 * @return string Unquoted value.
 */
if( !function_exists( 'widgetopts_unquote_attribute_value' ) ){
    function widgetopts_unquote_attribute_value( $raw ){
        $length = strlen( $raw );

        if( $length >= 2 && ( '"' === $raw[0] || "'" === $raw[0] ) && $raw[ $length - 1 ] === $raw[0] ){
            return substr( $raw, 1, -1 );
        }

        return $raw;
    }
}

//add is_active_sidebar support
if( !function_exists( 'widgetopts_sidebars_widgets' ) ){
	add_action( 'wp_loaded', 'widgetopts_sidebars_widgets_action' );
	function widgetopts_sidebars_widgets_action() {
        if( apply_filters( 'widgetopts_is_active_sidebar_support', true ) ){
    		add_filter( 'sidebars_widgets', 'widgetopts_sidebars_widgets' );
        }
	}
	function widgetopts_sidebars_widgets( $sidebars ) {
		if ( is_admin() ) {
			return $sidebars;
		}
        
		global $wp_registered_widgets;
        $checked = array();

		foreach ( $sidebars as $s => $sidebar ) {
			if ( $s == 'wp_inactive_widgets' || strpos( $s, 'orphaned_widgets' ) === 0 || empty( $sidebar ) ) {
				continue;
			}

			foreach ( $sidebar as $w => $widget ) {
				// $widget is the id of the widget
				if ( ! isset( $wp_registered_widgets[ $widget ] ) ) {
					continue;
				}

				if ( isset( $checked[ $widget ] ) ) {
					$show = $checked[ $widget ];
				} else {
					$opts = $wp_registered_widgets[ $widget ];
					$id_base = is_array( $opts['callback'] ) || $opts['callback'] instanceof ArrayAccess ? $opts['callback'][0]->id_base : $opts['callback'];

					if ( ! is_string( $id_base ) ) {
						continue;
					}

					$instance = get_option( 'widget_' . $id_base );

					if ( ! $instance || ! is_array( $instance ) ) {
						continue;
					}

					if ( isset( $instance['_multiwidget'] ) && $instance['_multiwidget'] ) {
						$number = $opts['params'][0]['number'];
						if ( ! isset( $instance[ $number ] ) ) {
							continue;
						}

						$instance = $instance[ $number ];
						unset( $number );
					}

					unset( $opts );

					$show = widgetopts_display_callback( $instance, (object) array( 'id' => $widget ), '' );

					$checked[ $widget ] = $show ? true : false;
				}

				if ( ! $show ) {
					unset( $sidebars[ $s ][ $w ] );
				}

				unset( $widget );
			}
			unset( $sidebar );
		}

		return $sidebars;
	}
}

?>
