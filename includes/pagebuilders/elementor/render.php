<?php
/**
 * Extends funtionality to Elementor Pagebuilder
 *
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       4.3
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( !function_exists( 'widgetopts_elementor_render' ) ){
	add_action( 'elementor/widget/render_content', 'widgetopts_elementor_render', 10, 2 );
	function widgetopts_elementor_render( $content, $widget ){
   		if ( !Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			global $widget_options;
			$settings 	= $widget->get_settings();

			$hidden     = false;
			$visibility_opts    = isset( $settings['widgetopts_visibility'] ) ? $settings['widgetopts_visibility'] : 'hide';

			$tax_opts   = ( isset( $widget_options['settings'] ) && isset( $widget_options['settings']['taxonomies_keys'] ) ) ? $widget_options['settings']['taxonomies_keys'] : array();
			$is_misc    = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['misc'] ) ) ? true : false;
	        $is_types   = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['post_type'] ) ) ? true : false;
	        $is_tax     = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['taxonomies'] ) ) ? true : false;

			//pages
			if ( $is_misc && ( ( is_home() && is_front_page() ) || is_front_page() ) ) {
				if( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && in_array( 'home', $settings['widgetopts_misc'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide if checked on hidden pages
	            }elseif( ( !isset( $settings['widgetopts_misc'] ) || ( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && !in_array( 'home', $settings['widgetopts_misc'] ) ) ) && $visibility_opts == 'show' ){
	                $hidden = true; //hide if not checked on visible pages
	            }

	            //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_home', $hidden );
	            if( $hidden ){
	                return false;
	            }
	        }elseif ( $is_misc && is_home() ) {
				if( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && in_array( 'blog', $settings['widgetopts_misc'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide if checked on hidden pages
	            }elseif( ( !isset( $settings['widgetopts_misc'] ) || ( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && !in_array( 'blog', $settings['widgetopts_misc'] ) ) ) && $visibility_opts == 'show' ){
	                $hidden = true; //hide if not checked on visible pages
	            }

	            //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_blog', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_tax && is_category() ) {
				//category page
				if( !isset( $settings['widgetopts_tax_category'] ) ){
	                $settings['widgetopts_tax_category'] = array();
	            }
				if( !isset( $settings['widgetopts_taxonomies'] ) ){
	                $settings['widgetopts_taxonomies'] = array();
	            }

	            $cat_lists = $settings['widgetopts_tax_category'];
	            if( !in_array( 'category', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'hide' && in_array( get_query_var('cat') , $cat_lists ) ){
	                $hidden = true; //hide if exists on hidden pages
	            }elseif( !in_array( 'category', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'show' && !in_array( get_query_var('cat') , $cat_lists ) ){
	                $hidden = true; //hide if doesn't exists on visible pages
	            }elseif( in_array( 'category', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide to all categories
	            }elseif( in_array( 'category', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'show' ){
	                $hidden = false; //hide to all categories
	            }
				//
	            // //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_categories', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_tax && is_tag() ) {
				if( !isset( $settings['widgetopts_tax_post_tag'] ) ){
	                $settings['widgetopts_tax_post_tag'] = array();
	            }
				if( !isset( $settings['widgetopts_taxonomies'] ) ){
	                $settings['widgetopts_taxonomies'] = array();
	            }

	            if( in_array( 'post_tag', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide to all tags
	            }elseif( in_array( 'post_tag', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'show' ){
	                $hidden = false; //hide to all tags
	            }
				//
	            // //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_tags', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_tax && is_tax() ) {
				//taxonomies page
				if( !isset( $settings['widgetopts_taxonomies'] ) ){
	                $settings['widgetopts_taxonomies'] = array();
	            }
				// print_r( $term_lists );
	            if( in_array( 'community-category', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide to all tags
	            }elseif( !in_array( 'community-category', $settings['widgetopts_taxonomies'] ) && $visibility_opts == 'show' ){
	                $hidden = true; //hide to all tags
	            }

	            //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_taxonomies', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_misc && is_archive() ) {
				//archives page
				if( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && in_array( 'archives', $settings['widgetopts_misc'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide if checked on hidden pages
	            }elseif( ( !isset( $settings['widgetopts_misc'] ) || ( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && !in_array( 'archives', $settings['widgetopts_misc'] ) ) ) && $visibility_opts == 'show' ){
	                $hidden = true; //hide if not checked on visible pages
	            }

	            //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_archives', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_misc && is_404() ) {
				//404 page
				if( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && in_array( '404', $settings['widgetopts_misc'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide if checked on hidden pages
	            }elseif( ( !isset( $settings['widgetopts_misc'] ) || ( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && !in_array( '404', $settings['widgetopts_misc'] ) ) ) && $visibility_opts == 'show' ){
	                $hidden = true; //hide if not checked on visible pages
	            }

	            //do return to bypass other conditions
	            $hidden = apply_filters( 'widget_options_visibility_404', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_misc && is_search() ) {
				if( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && in_array( 'search', $settings['widgetopts_misc'] ) && $visibility_opts == 'hide' ){
	                $hidden = true; //hide if checked on hidden pages
	            }elseif( ( !isset( $settings['widgetopts_misc'] ) || ( isset( $settings['widgetopts_misc'] ) && is_array( $settings['widgetopts_misc'] ) && !in_array( 'search', $settings['widgetopts_misc'] ) ) ) && $visibility_opts == 'show' ){
					$hidden = true;
				}

	            //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_search', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}elseif ( is_single() && !is_page() ) {
				global $wp_query;
				$post = $wp_query->post;

	            if( !isset( $settings['widgetopts_types'] ) || ( $is_types && !isset( $settings['widgetopts_types'] ) ) ){
	                $settings['widgetopts_types'] = array();
	            }

	            if( $visibility_opts == 'hide' && in_array( $post->post_type , $settings['widgetopts_types']) ){
	                $hidden = true; //hide if exists on hidden pages
	            }elseif( $visibility_opts == 'show' && !in_array( $post->post_type , $settings['widgetopts_types']) ){
	                $hidden = true; //hide if doesn't exists on visible pages
	            }

	            // do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_single', $hidden );


	            // $taxonomy_names  = get_post_taxonomies( $post->ID );
	            // $array_intersect = array_intersect( $tax_opts, $taxonomy_names );

	            if( !isset( $settings['widgetopts_tax_category'] ) ){
	                $settings['widgetopts_tax_category'] = array();
	            }

				if( isset( $settings['widgetopts_tax_category'] ) && !empty( $settings['widgetopts_tax_category'] ) ){
					$cats	= wp_get_post_categories( $post->ID );

	                if( is_array( $cats ) && !empty( $cats ) ){
	                    $checked_cats   = $settings['widgetopts_tax_category'];
	                    $intersect      = array_intersect( $cats , $checked_cats );
	                    if( !empty( $intersect ) && $visibility_opts == 'hide' ){
	                        $hidden = true;
	                    }elseif( !empty( $intersect ) && $visibility_opts == 'show' ){
	                        $hidden = false;
	                    }

						$hidden = apply_filters( 'widgetopts_elementor_visibility_single_category', $hidden );
	                }
				}

	            if( $hidden ){
	                return false;
	            }
			}elseif ( $is_types && is_page() ) {
				global $wp_query;

				$post = $wp_query->post;

	            //do post type condition first
	            if( isset( $settings['widgetopts_types'] ) ){
	                if( $visibility_opts == 'hide' && in_array( 'page', $settings['widgetopts_types'] ) ){
	                    $hidden = true; //hide if exists on hidden pages
	                }elseif( $visibility_opts == 'show' && !in_array( 'page', $settings['widgetopts_types'] ) ){
	                    $hidden = true; //hide if doesn't exists on visible pages
	                }
	            }else{
					// print_r( $settings['widgetopts_pages'] );
					//do per pages condition
	                if( !isset( $settings['widgetopts_pages'] ) ){
	                    $settings['widgetopts_pages'] = array();
	                }

	                if( $visibility_opts == 'hide' && in_array( $post->ID , $settings['widgetopts_pages'] ) ){
	                    $hidden = true; //hide if exists on hidden pages
	                }elseif( $visibility_opts == 'show' && !in_array( $post->ID , $settings['widgetopts_pages'] ) ){
	                    $hidden = true; //hide if doesn't exists on visible pages
	                }
	            }

	            // //do return to bypass other conditions
	            $hidden = apply_filters( 'widgetopts_elementor_visibility_page', $hidden );
	            if( $hidden ){
	                return false;
	            }
			}

			//widget logic
			if( 'activate' == $widget_options['logic'] ){
				if( isset( $settings['widgetopts_logic'] ) && !empty( $settings['widgetopts_logic'] ) ){
					//do widget logic
					$display_logic = stripslashes( trim( $settings['widgetopts_logic'] ) );
	                $display_logic = apply_filters( 'widget_options_logic_override', $display_logic );
	                $display_logic = apply_filters( 'extended_widget_options_logic_override', $display_logic );
	                if ( $display_logic === false ){
	                    return false;
	                }
	                if ( $display_logic === true ){
	                    return $content;
	                }
	                if ( stristr($display_logic,"return")===false ){
	                    $display_logic="return (" . $display_logic . ");";
	                }
	                if ( !eval( $display_logic ) ){
	                    return false;
	                }
				}
			}
		}

		return $content;
	}
}

if( !function_exists( 'widgetopts_elementor_before_render' ) ){
	add_action( 'elementor/frontend/widget/before_render', 'widgetopts_elementor_before_render', 10, 2 );
	function widgetopts_elementor_before_render( $element ){
		$enabled = array( 'button', 'button_plus', 'eael-creative-button', 'cta' );
		if ( in_array( $element->get_name(), $enabled ) ) {
			global $widget_options;
			if( 'activate' == $widget_options['sliding'] ){
				$settings = $element->get_settings();
				if( isset( $settings['widgetopts_open_sliding'] ) && 'on' == $settings['widgetopts_open_sliding'] ){
					$element->add_render_attribute( 'button', 'class', 'sl-widgetopts-open' );
				}
			}
		}
	}
}
?>
