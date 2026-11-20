<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('wp_insert_post_data', 'widgetopts_elementor_filter_saved_content', 10, 2);
add_filter('get_the_excerpt', 'widgetopts_elementor_filter_excerpt', 11, 2);

/**
 * Elementor stores one plain-text copy for all visitors. Remove any element
 * whose visibility is conditional, together with its descendants.
 */
function widgetopts_elementor_public_elements(array $elements, array $fields, &$has_rules): array
{
    $public = array();

    foreach ($elements as $index => $element) {
        if (!is_array($element)) {
            continue;
        }

        $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : array();
        if (widgetopts_elementor_has_visibility_rules($settings, $fields, $has_rules)) {
            continue;
        }

        if (isset($element['elements']) && is_array($element['elements'])) {
            $element['elements'] = widgetopts_elementor_public_elements($element['elements'], $fields, $has_rules);
        }

        $public[$index] = $element;
    }

    return $public;
}

function widgetopts_elementor_visibility_fields(array $options, $post_id): array
{
    $visibility = ($options['visibility'] ?? '') === 'activate' ? ($options['settings']['visibility'] ?? array()) : array();
    // The single-post renderer also checks post types and categories directly.
    $single = ($options['visibility'] ?? '') === 'activate' && get_post_type($post_id) !== 'page';

    return array(
        'widgetopts_roles_state' => ($options['state'] ?? '') === 'activate',
        'widgetopts_logic' => ($options['logic'] ?? '') === 'activate',
        'widgetopts_logic_snippet_id' => ($options['logic'] ?? '') === 'activate',
        'widgetopts_acf_field' => ($options['acf'] ?? '') === 'activate',
        'widgetopts_visibility' => $single || isset($visibility['misc']) || isset($visibility['post_type']) || isset($visibility['taxonomies']),
        'widgetopts_misc' => isset($visibility['misc']),
        'widgetopts_pages' => isset($visibility['post_type']),
        'widgetopts_types' => $single || isset($visibility['post_type']),
        'widgetopts_taxonomies' => isset($visibility['taxonomies']),
        'widgetopts_tax_category' => $single || isset($visibility['taxonomies']),
    );
}

function widgetopts_elementor_has_visibility_rules(array $settings, array $fields, &$has_rules): bool
{
    foreach ($settings as $key => $value) {
        if ($key === 'widgetopts_roles_state' && !in_array($value, array('in', 'out'), true)) {
            continue;
        }
        if ($key === 'widgetopts_visibility' && $value !== 'show') {
            continue;
        }
        if (strpos((string) $key, 'widgetopts_tax_') === 0 && $key !== 'widgetopts_tax_category') {
            $key = 'widgetopts_taxonomies';
        }
        if (isset($fields[$key]) && !empty($value)) {
            $has_rules = true;
            if ($fields[$key]) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Return a public copy for an Elementor post that has conditional elements.
 * Null leaves WordPress's existing content or excerpt alone.
 */
function widgetopts_elementor_public_plain_content(int $post_id, &$source = null, bool $for_excerpt = false): ?string
{
    if (get_post_meta($post_id, '_elementor_edit_mode', true) !== 'builder') {
        return null;
    }

    $data = get_post_meta($post_id, '_elementor_data', true);
    global $widget_options;
    $fields = widgetopts_elementor_visibility_fields($widget_options, $post_id);
    // Third-party widgets can produce different plain text for a signed-in user.
    $key = hash('sha256', serialize(array(1, get_current_blog_id(), $post_id, get_current_user_id(), get_locale(), $fields, $data)));
    static $memo = array();
    $public_request = !is_user_logged_in() && empty($_COOKIE) && !is_preview();
    $cache_name = 'widgetopts_public_excerpt_' . $post_id;
    if ($for_excerpt) {
        $saved = get_post_meta($post_id, '_widgetopts_public_content_signature', true);
        if (($public_request || is_user_logged_in()) && is_string($saved) && $saved !== '') {
            $content = get_post_field('post_content', $post_id, 'raw');
            if (hash_equals($saved, widgetopts_elementor_content_signature($key, $content))) {
                return $content;
            }
        }
        if (array_key_exists($key, $memo)) {
            return $memo[$key];
        }
        if ($public_request) {
            $cached = get_transient($cache_name);
            if (is_array($cached) && ($cached['source'] ?? null) === $key && is_string($cached['text'] ?? null)) {
                return $memo[$key] = $cached['text'];
            }
        }
    }

    $elements = is_array($data) ? $data : json_decode((string) $data, true);
    if (!is_array($elements)) {
        return '';
    }

    $has_rules = false;
    $public = widgetopts_elementor_public_elements($elements, $fields, $has_rules);
    // A saved copy may still omit a widget whose module has since been disabled.
    if (!$has_rules) {
        $memo[$key] = null;
        return null;
    }

    // A widget such as a post list can request another excerpt while rendering.
    static $rendering = array();
    if (isset($rendering[$post_id])) {
        return '';
    }

    $elementor = \Elementor\Plugin::$instance;
    if (!isset($elementor->db, $elementor->dynamic_tags)
        || !is_callable(array($elementor->db, 'get_plain_text_from_data'))) {
        return '';
    }

    $rendering[$post_id] = true;
    $dynamic_tags = $elementor->dynamic_tags;
    $mode = $dynamic_tags->get_parsing_mode();
    $buffer_level = ob_get_level();
    $dynamic_tags->set_parsing_mode(\Elementor\Core\DynamicTags\Manager::MODE_REMOVE);

    try {
        $text = $elementor->db->get_plain_text_from_data($public);
        $source = $key;
        if ($for_excerpt) {
            $memo[$key] = $text;
            // A cookie-free guest can validate the saved copy or cache a separate
            // public excerpt when a layout or module change makes that copy stale.
            if ($public_request) {
                if ($text === get_post_field('post_content', $post_id, 'raw')) {
                    update_post_meta($post_id, '_widgetopts_public_content_signature', widgetopts_elementor_content_signature($key, $text));
                } else {
                    // One bounded entry per post for older copies or a module change.
                    set_transient($cache_name, array('source' => $key, 'text' => $text), DAY_IN_SECONDS);
                }
            }
        }
        return $text;
    } catch (\Throwable $error) {
        return '';
    } finally {
        while (ob_get_level() > $buffer_level) {
            ob_end_clean();
        }

        $dynamic_tags->set_parsing_mode($mode);
        unset($rendering[$post_id]);
    }
}

// Bind the saved copy to its inputs and its actual bytes. A failed or concurrent
// post update can cause a cache miss, but cannot validate a stale public copy.
function widgetopts_elementor_content_signature(string $source, string $content): string
{
    return hash_hmac('sha256', $source . $content, wp_salt('auth'));
}

function widgetopts_elementor_filter_saved_content(array $data, array $postarr): array
{
    if (empty($postarr['ID']) || !isset($data['post_content'])) {
        return $data;
    }

    // Elementor DB::save_plain_text() calls wp_update_post in this mode.
    $dynamic_tags = \Elementor\Plugin::$instance->dynamic_tags ?? null;
    if (!$dynamic_tags || $dynamic_tags->get_parsing_mode() !== \Elementor\Core\DynamicTags\Manager::MODE_REMOVE) {
        return $data;
    }

    $post_id = (int) $postarr['ID'];
    $source = null;
    $content = widgetopts_elementor_public_plain_content($post_id, $source);
    if ($content !== null) {
        $data['post_content'] = sanitize_post_field('post_content', wp_slash($content), $post_id, 'db');
        if ($source !== null) {
            $signature = widgetopts_elementor_content_signature($source, wp_unslash($data['post_content']));
            update_post_meta($post_id, '_widgetopts_public_content_signature', $signature);
        }
    }

    return $data;
}

function widgetopts_elementor_filter_excerpt(string $excerpt, WP_Post $post): string
{
    if (trim($post->post_excerpt) !== '') {
        return $excerpt;
    }

    // Replace excerpts generated from copies saved before visibility filtering.
    $source = null;
    $content = widgetopts_elementor_public_plain_content((int) $post->ID, $source, true);
    if ($content === null) {
        return $excerpt;
    }

    return wp_trim_words(
        strip_shortcodes($content),
        (int) apply_filters('excerpt_length', (int) _x('55', 'excerpt_length')),
        apply_filters('excerpt_more', ' [&hellip;]')
    );
}
