<?php
/**
 * API to interact with global settings & styles.
 *
 * @package gutenberg
 */

/**
 * Returns the stylesheet resulting of merging core, theme, and user data.
 *
 * @param array $types Types of styles to load. Optional.
 *                     It accepts as values: 'variables', 'presets', 'styles', 'base-layout-styles.
 *                     If empty, it'll load the following:
 *                     - for themes without theme.json: 'variables', 'presets', 'base-layout-styles'.
 *                     - for themes with theme.json: 'variables', 'presets', 'styles'.
 *
 * @return string Stylesheet.
 */
function gutenberg_get_global_stylesheet( $types = array() ) {
	// Ignore cache when `WP_DEBUG` is enabled, so it doesn't interfere with the theme developers workflow.
	$can_use_cached = empty( $types ) && ! WP_DEBUG;
	$cache_key      = 'gutenberg_get_global_stylesheet';
	$cache_group    = 'theme_json';
	if ( $can_use_cached ) {
		$cached = wp_cache_get( $cache_key, $cache_group );
		if ( $cached ) {
			return $cached;
		}
	}
	$tree = WP_Theme_JSON_Resolver_Gutenberg::get_merged_data();

	$supports_theme_json = wp_theme_has_theme_json();
	if ( empty( $types ) && ! $supports_theme_json ) {
		$types = array( 'variables', 'presets', 'base-layout-styles' );
	} elseif ( empty( $types ) ) {
		$types = array( 'variables', 'presets', 'styles' );
	}

	/*
	 * If variables are part of the stylesheet,
	 * we add them.
	 *
	 * This is so themes without a theme.json still work as before 5.9:
	 * they can override the default presets.
	 * See https://core.trac.wordpress.org/ticket/54782
	 */
	$styles_variables = '';
	if ( in_array( 'variables', $types, true ) ) {
		/*
		 * We only use the default, theme, and custom origins.
		 * This is because styles for blocks origin are added
		 * at a later phase (render cycle) so we only render the ones in use.
		 * @see wp_add_global_styles_for_blocks
		 */
		$origins          = array( 'default', 'theme', 'custom' );
		$styles_variables = $tree->get_stylesheet( array( 'variables' ), $origins );
		$types            = array_diff( $types, array( 'variables' ) );
	}

	/*
	 * For the remaining types (presets, styles), we do consider origins:
	 *
	 * - themes without theme.json: only the classes for the presets defined by core
	 * - themes with theme.json: the presets and styles classes, both from core and the theme
	 */
	$styles_rest = '';
	if ( ! empty( $types ) ) {
		/*
		 * We only use the default, theme, and custom origins.
		 * This is because styles for blocks origin are added
		 * at a later phase (render cycle) so we only render the ones in use.
		 * @see wp_add_global_styles_for_blocks
		 */
		$origins = array( 'default', 'theme', 'custom' );
		if ( ! $supports_theme_json ) {
			$origins = array( 'default' );
		}
		$styles_rest = $tree->get_stylesheet( $types, $origins );
	}
	$stylesheet = $styles_variables . $styles_rest;
	if ( $can_use_cached ) {
		wp_cache_set( $cache_key, $stylesheet, $cache_group );
	}
	return $stylesheet;
}

/**
 * Function to get the settings resulting of merging core, theme, and user data.
 *
 * @param array $path    Path to the specific setting to retrieve. Optional.
 *                       If empty, will return all settings.
 * @param array $context {
 *     Metadata to know where to retrieve the $path from. Optional.
 *
 *     @type string $block_name Which block to retrieve the settings from.
 *                              If empty, it'll return the settings for the global context.
 *     @type string $origin     Which origin to take data from.
 *                              Valid values are 'all' (core, theme, and user) or 'base' (core and theme).
 *                              If empty or unknown, 'all' is used.
 * }
 *
 * @return array The settings to retrieve.
 */
function gutenberg_get_global_settings( $path = array(), $context = array() ) {
	if ( ! empty( $context['block_name'] ) ) {
		$new_path = array( 'blocks', $context['block_name'] );
		foreach ( $path as $subpath ) {
			$new_path[] = $subpath;
		}
		$path = $new_path;
	}

	// This is the default value when no origin is provided or when it is 'all'.
	$origin = 'custom';
	if (
		! wp_theme_has_theme_json() ||
		( isset( $context['origin'] ) && 'base' === $context['origin'] )
	) {
		$origin = 'theme';
	}

	$cache_group = 'theme_json';
	$cache_key   = 'gutenberg_get_global_settings_' . $origin;
	$settings    = wp_cache_get( $cache_key, $cache_group );

	if ( false === $settings || WP_DEBUG ) {
		$settings = WP_Theme_JSON_Resolver_Gutenberg::get_merged_data( $origin )->get_settings();
		wp_cache_set( $cache_key, $settings, $cache_group );
	}

	return _wp_array_get( $settings, $path, $settings );
}

/**
 * Gets the global styles custom css from theme.json.
 *
 * @return string
 */
function gutenberg_get_global_styles_custom_css() {
	// Ignore cache when `WP_DEBUG` is enabled, so it doesn't interfere with the theme developers workflow.
	$can_use_cached = ! WP_DEBUG;
	$cache_key      = 'gutenberg_get_global_custom_css';
	$cache_group    = 'theme_json';
	if ( $can_use_cached ) {
		$cached = wp_cache_get( $cache_key, $cache_group );
		if ( $cached ) {
			return $cached;
		}
	}

	if ( ! wp_theme_has_theme_json() ) {
		return '';
	}

	$tree       = WP_Theme_JSON_Resolver_Gutenberg::get_merged_data();
	$stylesheet = $tree->get_custom_css();

	if ( $can_use_cached ) {
		wp_cache_set( $cache_key, $stylesheet, $cache_group );
	}

	return $stylesheet;
}

/**
 * Adds global style rules to the inline style for each block.
 *
 * @return void
 */
function gutenberg_add_global_styles_for_blocks() {
	$tree        = WP_Theme_JSON_Resolver_Gutenberg::get_merged_data();
	$block_nodes = $tree->get_styles_block_nodes();
	foreach ( $block_nodes as $metadata ) {
		$block_css = $tree->get_styles_for_block( $metadata );

		if ( ! wp_should_load_separate_core_block_assets() ) {
			wp_add_inline_style( 'global-styles', $block_css );
			continue;
		}

		$stylesheet_handle = 'global-styles';
		if ( isset( $metadata['name'] ) ) {
			/*
			 * These block styles are added on block_render.
			 * This hooks inline CSS to them so that they are loaded conditionally
			 * based on whether or not the block is used on the page.
			 */
			if ( str_starts_with( $metadata['name'], 'core/' ) ) {
				$block_name        = str_replace( 'core/', '', $metadata['name'] );
				$stylesheet_handle = 'wp-block-' . $block_name;
			}
			wp_add_inline_style( $stylesheet_handle, $block_css );
		}

		// The likes of block element styles from theme.json do not have  $metadata['name'] set.
		if ( ! isset( $metadata['name'] ) && ! empty( $metadata['path'] ) ) {
			$result = array_values(
				array_filter(
					$metadata['path'],
					function ( $item ) {
						if ( strpos( $item, 'core/' ) !== false ) {
							return true;
						}
						return false;
					}
				)
			);
			if ( isset( $result[0] ) ) {
				if ( str_starts_with( $result[0], 'core/' ) ) {
					$block_name        = str_replace( 'core/', '', $result[0] );
					$stylesheet_handle = 'wp-block-' . $block_name;
				}
				wp_add_inline_style( $stylesheet_handle, $block_css );
			}
		}
	}
}

/**
 * Private function to clean the caches used by gutenberg_get_global_settings method.
 *
 * @access private
 */
function _gutenberg_clean_theme_json_caches() {
	wp_cache_delete( 'wp_theme_has_theme_json', 'theme_json' );
	wp_cache_delete( 'gutenberg_get_global_stylesheet', 'theme_json' );
	wp_cache_delete( 'gutenberg_get_global_settings_custom', 'theme_json' );
	wp_cache_delete( 'gutenberg_get_global_settings_theme', 'theme_json' );
	wp_cache_delete( 'gutenberg_get_global_custom_css', 'theme_json' );
	WP_Theme_JSON_Resolver_Gutenberg::clean_cached_data();
}
add_action( 'start_previewing_theme', '_gutenberg_clean_theme_json_caches' );
add_action( 'switch_theme', '_gutenberg_clean_theme_json_caches' );
