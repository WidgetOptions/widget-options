<?php
/**
 * Navigation Fallback
 *
 * Functions required for managing Navigation fallbacks behavior.
 *
 * @package Gutenberg
 * @since 6.3.0
 */

/**
 * Expose additional fields in the embeddable links of the
 * Navigation Fallback REST endpoint.
 *
 * The endpoint may embed the full Navigation Menu object into the
 * response as the `self` link. By default the Posts Controller
 * will only exposes a limited subset of fields but the editor requires
 * additional fields to be available in order to utilise the menu.
 *
 * @param array $schema the schema for the `wp_navigation` post.
 * @return array the modified schema.
 */
function gutenberg_add_fields_to_navigation_fallback_embeded_links( $schema ) {
	// Expose top level fields.
	$schema['properties']['status']['context']  = array_merge( $schema['properties']['status']['context'], array( 'embed' ) );
	$schema['properties']['content']['context'] = array_merge( $schema['properties']['content']['context'], array( 'embed' ) );

	// Expose sub properties of content field.
	$schema['properties']['content']['properties']['raw']['context']           = array_merge( $schema['properties']['content']['properties']['raw']['context'], array( 'embed' ) );
	$schema['properties']['content']['properties']['rendered']['context']      = array_merge( $schema['properties']['content']['properties']['rendered']['context'], array( 'embed' ) );
	$schema['properties']['content']['properties']['block_version']['context'] = array_merge( $schema['properties']['content']['properties']['block_version']['context'], array( 'embed' ) );

	return $schema;
}

add_filter(
	'rest_wp_navigation_item_schema',
	'gutenberg_add_fields_to_navigation_fallback_embeded_links'
);
