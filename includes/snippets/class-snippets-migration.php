<?php
/**
 * Display Logic Snippets - Migration System
 *
 * Handles automatic migration from legacy inline logic code to the new
 * snippets-based system. Scans all widgets across all editors and creates
 * snippets from unique logic code patterns.
 *
 * @copyright   Copyright (c) 2024, Widget Options Team
 * @since       5.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Class WidgetOpts_Snippets_Migration
 * 
 * Handles migration of legacy display logic to snippets
 */
class WidgetOpts_Snippets_Migration {

    /**
     * Option name for migration status
     */
    const MIGRATION_OPTION = 'widgetopts_snippets_migration';

    /**
     * Run the complete migration process
     * 
     * @return bool True on success, false on failure
     */
    public static function migrate() {
        // Start migration
        $migration_data = array(
            'status'     => 'in_progress',
            'started_at' => current_time('mysql'),
            'version'    => WIDGETOPTS_VERSION,
        );
        update_option(self::MIGRATION_OPTION, $migration_data);

        try {
            $results = array(
                'snippets_created' => 0,
                'widgets_updated'  => 0,
                'errors'           => array(),
            );

            // Collect all unique logic codes from all sources
            $logic_codes = array_column(self::collect_all_logic_codes_with_locations(), 'code');

            if (empty($logic_codes)) {
                // No logic codes found - mark as completed
                $migration_data['status'] = 'completed';
                $migration_data['completed_at'] = current_time('mysql');
                $migration_data['message'] = __('No legacy logic codes found to migrate.', 'widget-options');
                update_option(self::MIGRATION_OPTION, $migration_data);
                return true;
            }

            // Create snippets for unique codes
            $code_to_snippet_map = self::create_snippets_from_codes($logic_codes, $results);

            // Update all widgets with new snippet IDs
            self::update_classic_widgets($code_to_snippet_map, $results);
            self::update_gutenberg_blocks($code_to_snippet_map, $results);
            self::update_elementor_widgets($code_to_snippet_map, $results);
            self::update_beaver_builder_nodes($code_to_snippet_map, $results);
            self::update_siteorigin_widgets($code_to_snippet_map, $results);

            // Mark migration as completed
            $migration_data['status'] = 'completed';
            $migration_data['completed_at'] = current_time('mysql');
            $migration_data['results'] = $results;
            update_option(self::MIGRATION_OPTION, $migration_data);

            return true;

        } catch (Exception $e) {
            // Mark migration as failed
            $migration_data['status'] = 'failed';
            $migration_data['failed_at'] = current_time('mysql');
            $migration_data['error'] = $e->getMessage();
            update_option(self::MIGRATION_OPTION, $migration_data);

            return false;
        }
    }

    /**
     * All renderers prefer the selected snippet over leftover inline code.
     */
    private static function inline_code($settings, $field = 'logic', $snippet_field = 'logic_snippet_id') {
        $settings = (array) $settings;
        if (!empty($settings[$snippet_field]) || empty($settings[$field]) || !is_string($settings[$field])) {
            return '';
        }
        return trim($settings[$field]);
    }

    private static function assigned_widgets($only_widget = '') {
        global $wp_registered_sidebars, $wp_registered_widgets;

        $assigned = array();
        $registered = did_action('widgets_init') && !doing_action('widgets_init');
        foreach ((array) wp_get_sidebars_widgets() as $sidebar => $widgets) {
            if (!is_array($widgets)) {
                continue;
            }
            $active = $sidebar !== 'wp_inactive_widgets' && strpos($sidebar, 'orphaned_widgets_') !== 0
                && (!$registered || isset($wp_registered_sidebars[$sidebar]));
            foreach ($widgets as $widget_id) {
                if (!is_string($widget_id) || ($only_widget !== '' && $only_widget !== $widget_id)
                    || ($registered && !isset($wp_registered_widgets[$widget_id]))) {
                    continue;
                }
                if ($active) {
                    $assigned[$widget_id] = $sidebar;
                }
            }
        }
        return $assigned;
    }

    /**
     * Get logic code from classic widget
     * 
     * @param string $widget_id Widget ID
     * @return array Array of logic codes
     */
    private static function get_classic_widget_logic_codes($widget_id) {
        // Parse widget ID to get base and number
        preg_match('/^(.+)-(\d+)$/', $widget_id, $matches);
        if (count($matches) !== 3) {
            return array();
        }

        $widget_base = $matches[1];
        $widget_num = (int) $matches[2];

        $widget_options = get_option('widget_' . $widget_base);
        if (!is_array($widget_options) || !isset($widget_options[$widget_num])) {
            return array();
        }

        $instance = $widget_options[$widget_num];
        if (!is_array($instance)) {
            return array();
        }

        $code = self::classic_widget_code($instance, $widget_id);
        $codes = $code === '' ? array() : array($code);

        if ($widget_base === 'block' && !empty($instance['content']) && is_string($instance['content'])) {
            $widget_fallback = empty($instance['extended_widget_opts-' . $widget_id]);
            $codes = array_merge($codes, self::extract_logic_from_blocks(parse_blocks($instance['content']), $widget_fallback));
        }

        return array_unique(array_filter($codes));
    }

    /**
     * Extract logic codes from blocks recursively
     * 
     * @param array $blocks Array of blocks
     * @return array Array of logic codes
     */
    private static function extract_logic_from_blocks($blocks, $widget_fallback = false) {
        $codes = array();
        foreach ($blocks as $index => $block) {
            $rule = self::block_rule($block, $widget_fallback && $index === 0);
            if ($rule) {
                $codes[] = $rule['code'];
            }
            if (!empty($block['innerBlocks'])) {
                $codes = array_merge($codes, self::extract_logic_from_blocks($block['innerBlocks']));
            }
        }
        return $codes;
    }

    /**
     * Match the block renderer's attribute precedence and its first freeform marker.
     * A block widget can also take its options directly from the first top-level block.
     */
    private static function block_rule($block, $widget_fallback = false) {
        $key = isset($block['attrs']['extended_widget_opts']) ? 'extended_widget_opts' : 'extended_widget_opts_block';
        $options = $block['attrs'][$key] ?? null;
        $marker = null;

        if (empty($block['blockName']) && empty($block['attrs']) && is_string($block['innerContent'][0] ?? null)) {
            if (!preg_match(widgetopts_freeform_marker_pattern(), $block['innerContent'][0], $match, PREG_OFFSET_CAPTURE)) {
                return null;
            }
            $marker = $match[1];
            $options = widgetopts_decode_freeform_marker($marker[0]);
            $key = 'extended_widget_opts';
        }

        if (!is_array($options) || ($key === 'extended_widget_opts' && isset($options['id_base'])
            && $options['id_base'] != -1 && (!$widget_fallback || $marker !== null))) {
            return null;
        }
        $code = self::inline_code($options['class'] ?? null);
        return $code === '' ? null : array('code' => $code, 'key' => $key, 'marker' => $marker);
    }

    /**
     * Locate immediate members of an already validated JSON object. Keeping their
     * byte ranges lets migration preserve other settings exactly, including names
     * that PHP cannot represent as stdClass properties.
     */
    private static function json_member_ranges($json) {
        $members = array();
        $depth = 0;
        $offset = 0;
        $start = 0;
        $value = null;
        $name = null;
        $pattern = '/"(?:[^"\\\\]++|\\\\.)*+"|[{}\[\],:]/';

        while (($found = preg_match($pattern, $json, $match, PREG_OFFSET_CAPTURE, $offset)) === 1) {
            list($token, $position) = $match[0];
            $offset = $position + strlen($token);
            if ($depth === 1) {
                if ($token[0] === '"' && $value === null) {
                    $name = json_decode($token, true);
                } elseif ($token === ':') {
                    $value = $offset;
                } elseif ($token === ',' || $token === '}') {
                    if ($value !== null) {
                        $members[] = array('name' => $name, 'start' => $start, 'value' => $value, 'end' => $position);
                    }
                    $start = $offset;
                    $value = null;
                }
            }

            if ($token === '{' || $token === '[') {
                if ($depth === 0) $start = $offset;
                $depth++;
            } elseif ($token === '}' || $token === ']') {
                $depth--;
            }
        }
        return $found === false ? false : $members;
    }

    private static function rewrite_marker_json($json, $code, $snippet_id) {
        $fields = self::json_member_ranges($json);
        if ($fields === false) return false;

        // json_decode and the renderer use the last occurrence of a duplicate key.
        $class = null;
        foreach ($fields as $field) {
            if ($field['name'] === 'class') $class = $field;
        }
        if ($class === null) return false;

        $length = $class['end'] - $class['value'];
        $settings = substr($json, $class['value'], $length);
        $fields = self::json_member_ranges($settings);
        if ($fields === false) return false;

        $replacement = array();
        if ($snippet_id !== null) {
            $replacement = array('logic_backup' => $code, 'logic_snippet_id' => $snippet_id, 'wopt_version' => WIDGETOPTS_VERSION);
        }
        $parts = array();
        foreach ($fields as $field) {
            if ($field['name'] !== 'logic' && !array_key_exists($field['name'], $replacement)) {
                $parts[] = substr($settings, $field['start'], $field['end'] - $field['start']);
            }
        }
        if ($replacement) {
            $encoded = wp_json_encode($replacement, JSON_HEX_TAG);
            if ($encoded === false) return false;
            $parts[] = substr($encoded, 1, -1);
        }
        return substr_replace($json, '{' . implode(',', $parts) . '}', $class['value'], $length);
    }

    private static function rewrite_block_rule(&$block, $rule, $snippet_id) {
        $marker = $rule['marker'];
        if ($marker !== null) {
            $json = self::rewrite_marker_json($marker[0], $rule['code'], $snippet_id);
            if ($json === false) return false;

            // Keep the marker in place so later markers in this chunk stay inactive.
            $block['innerContent'][0] = substr_replace($block['innerContent'][0], $json, $marker[1], strlen($marker[0]));
            $block['innerHTML'] = $block['innerContent'][0];
        } else {
            $settings = &$block['attrs'][$rule['key']]['class'];
            unset($settings['logic']);
            if ($snippet_id !== null) {
                $settings['logic_backup'] = $rule['code'];
                $settings['logic_snippet_id'] = $snippet_id;
                $settings['wopt_version'] = WIDGETOPTS_VERSION;
            }
        }
        return true;
    }

    private static function classic_widget_code($instance, $widget_id) {
        $options = $instance['extended_widget_opts-' . $widget_id] ?? null;
        if (!is_array($options) || !isset($options['class']) || !is_array($options['class'])) {
            return '';
        }
        return self::inline_code($options['class']);
    }

    /**
     * Create snippets from collected codes
     * 
     * @param array $codes Array of logic codes
     * @param array &$results Results array to update
     * @return array Map of code => snippet_id
     */
    private static function create_snippets_from_codes($codes, &$results) {
        $code_to_snippet_map = array();
        $index = 0;

        foreach ($codes as $code) {
            $normalized_code = trim($code);

            // Check if snippet with this code already exists
            $existing_id = WidgetOpts_Snippets_CPT::find_snippet_by_code($normalized_code);

            if ($existing_id) {
                $code_to_snippet_map[$normalized_code] = $existing_id;
                continue;
            }

            // Generate title based on code content
            $title = WidgetOpts_Snippets_CPT::generate_snippet_title($normalized_code, $index);

            // Ensure unique title
            $title = self::ensure_unique_title($title);

            // Create snippet
            $snippet_id = WidgetOpts_Snippets_CPT::create_snippet(
                $title,
                $normalized_code,
                __('Automatically migrated from legacy display logic.', 'widget-options')
            );

            if (!is_wp_error($snippet_id)) {
                $code_to_snippet_map[$normalized_code] = $snippet_id;
                $results['snippets_created']++;
                $index++;
            } else {
                $results['errors'][] = sprintf(
                    __('Failed to create snippet for code: %s', 'widget-options'),
                    substr($normalized_code, 0, 50) . '...'
                );
            }
        }

        return $code_to_snippet_map;
    }

    /**
     * Ensure snippet title is unique
     * 
     * @param string $title Original title
     * @return string Unique title
     */
    private static function ensure_unique_title($title) {
        $original_title = $title;
        $counter = 1;

        while (get_page_by_title($title, OBJECT, WidgetOpts_Snippets_CPT::POST_TYPE)) {
            $counter++;
            $title = $original_title . ' (' . $counter . ')';
        }

        return $title;
    }

    /**
     * Update classic widgets with snippet IDs
     * 
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array to update
     */
    private static function update_classic_widgets($code_to_snippet_map, &$results) {
        global $wpdb;

        // Get all widget options
        $assigned = self::assigned_widgets();
        $widget_options = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} 
             WHERE option_name LIKE 'widget_%'"
        );

        foreach ($widget_options as $option) {
            $widgets = maybe_unserialize($option->option_value);

            if (!is_array($widgets)) {
                continue;
            }

            $updated = false;

            foreach ($widgets as $widget_num => &$instance) {
                $widget_id = substr($option->option_name, strlen('widget_')) . '-' . $widget_num;
                if (!isset($assigned[$widget_id])) continue;
                if (!is_array($instance)) {
                    continue;
                }

                if ($option->option_name === 'widget_block' && !empty($instance['content']) && is_string($instance['content'])) {
                    $blocks = parse_blocks($instance['content']);
                    $widget_fallback = empty($instance['extended_widget_opts-' . $widget_id]);
                    $updated_blocks = self::update_blocks_with_snippets($blocks, $code_to_snippet_map, $results, $widget_fallback);

                    if ($updated_blocks !== false) {
                        $instance['content'] = serialize_blocks($updated_blocks);
                        $updated = true;
                    }
                }

                $code = self::classic_widget_code($instance, $widget_id);
                if (isset($code_to_snippet_map[$code])) {
                    $key = 'extended_widget_opts-' . $widget_id;
                    $instance[$key]['class']['logic_backup'] = $code;
                    unset($instance[$key]['class']['logic']);
                    $instance[$key]['class']['logic_snippet_id'] = $code_to_snippet_map[$code];
                    $instance[$key]['class']['wopt_version'] = WIDGETOPTS_VERSION;
                    $updated = true;
                    $results['widgets_updated']++;
                }
            }

            if ($updated) {
                update_option($option->option_name, $widgets);
            }
        }
    }

    /**
     * Update Gutenberg blocks with snippet IDs
     * 
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array to update
     */
    private static function update_gutenberg_blocks($code_to_snippet_map, &$results) {
        foreach (self::migration_rows() as $post) {
            $updated = array('widgets_updated' => 0, 'errors' => array());
            $blocks = self::update_blocks_with_snippets(parse_blocks($post->post_content), $code_to_snippet_map, $updated);
            foreach ($updated['errors'] as $error) {
                $results['errors'][] = $error;
            }
            if ($blocks === false) continue;

            global $wpdb;
            $saved = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d AND BINARY post_content = %s",
                serialize_blocks($blocks), $post->ID, $post->post_content
            ));
            if ($saved) {
                clean_post_cache($post->ID);
                $results['widgets_updated'] += $updated['widgets_updated'];
            } else {
                $results['errors'][] = sprintf(__('Could not update post #%d. Scan again and retry.', 'widget-options'), $post->ID);
            }
        }
    }

    /**
     * Update blocks recursively with snippet IDs
     * 
     * @param array $blocks Array of blocks
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array
     * @return array|false Updated blocks or false if no changes
     */
    private static function update_blocks_with_snippets($blocks, $code_to_snippet_map, &$results, $widget_fallback = false) {
        $changed = false;
        foreach ($blocks as $index => &$block) {
            $rule = self::block_rule($block, $widget_fallback && $index === 0);
            if ($rule && array_key_exists($rule['code'], $code_to_snippet_map)) {
                if (self::rewrite_block_rule($block, $rule, $code_to_snippet_map[$rule['code']])) {
                    $changed = true;
                    $results['widgets_updated']++;
                } else {
                    $results['errors'][] = __('Could not update the Display Logic rule in a freeform block.', 'widget-options');
                }
            }

            if (!empty($block['innerBlocks'])) {
                $inner = self::update_blocks_with_snippets($block['innerBlocks'], $code_to_snippet_map, $results);
                if ($inner !== false) {
                    $block['innerBlocks'] = $inner;
                    $changed = true;
                }
            }
        }
        return $changed ? $blocks : false;
    }

    /**
     * Decode the passive data structures stored by builders. Unsupported object
     * types and recursive/corrupt records cannot be safely written back.
     */
    private static function decode_builder_data($raw) {
        $data = @unserialize($raw, array('allowed_classes' => array('stdClass'), 'max_depth' => 512));
        if (!is_array($data)) {
            return null;
        }
        // Each value in a serialized tree occupies at least one byte. Keep work
        // proportional to the stored layout, including when inspecting invalid data.
        $remaining = strlen($raw);
        $seen_objects = new SplObjectStorage();
        if (!self::is_builder_tree($data, $remaining, $seen_objects, 0)) {
            return null;
        }
        // PHP array references use R: in serialized data. Compare with a detached
        // copy only when necessary: a plain string may contain that text too.
        if (PHP_VERSION_ID < 70400 && strpos($raw, 'R:') !== false
            && serialize($data) !== serialize(self::detached_tree($data))) {
            return null;
        }
        return $data;
    }

    private static function detached_tree($value) {
        if (is_array($value)) {
            return array_map(array(__CLASS__, 'detached_tree'), $value);
        }
        return is_object($value) ? (object) self::detached_tree((array) $value) : $value;
    }

    private static function is_builder_tree($value, &$remaining, $seen_objects, $depth) {
        $remaining -= is_string($value) ? 1 + strlen($value) : 1;
        if ($remaining < 0 || $depth > 512) {
            return false;
        }
        if (is_object($value)) {
            if (!($value instanceof stdClass) || $seen_objects->offsetExists($value)) {
                return false;
            }
            $seen_objects[$value] = true;
            $value = (array) $value;
        }
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $remaining -= is_string($key) ? 1 + strlen($key) : 1;
                if ((PHP_VERSION_ID >= 70400 && ReflectionReference::fromArrayElement($value, $key) !== null)
                    || !self::is_builder_tree($child, $remaining, $seen_objects, $depth + 1)) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function beaver_inline_code($node) {
        if (!($node instanceof stdClass) || !(($node->settings ?? null) instanceof stdClass)) {
            return '';
        }
        return self::inline_code($node->settings, 'widgetopts_settings_logic', 'widgetopts_logic_snippet_id');
    }

    private static function siteorigin_inline_code($widget) {
        if (!is_array($widget) || !is_array($widget['extended_widget_opts'] ?? null)) {
            return '';
        }
        $settings = $widget['extended_widget_opts']['class'] ?? null;
        return is_array($settings) ? self::inline_code($settings) : '';
    }

    private static $panels_migration_update = null;

    /**
     * Return null for ordinary saves, including writes nested inside another
     * plugin's metadata callback. Only a verified, computed migration gets true.
     */
    public static function check_panels_migration_update($post_id, $value, $previous) {
        global $wp_current_filter;
        $update = self::$panels_migration_update;
        if ($update === null || $update['post_id'] !== (int) $post_id
            || current_filter() !== 'update_post_metadata' || count($wp_current_filter) !== $update['depth']) {
            return null;
        }
        return $update['value'] === maybe_serialize($value) && $update['previous'] === maybe_serialize($previous);
    }

    /**
     * Core's update_post_meta() serializes its previous-value argument again.
     * Older PHP float encodings may differ, and duplicate rows can share a value.
     * Keep the core post-meta contract while matching the effective row's original bytes.
     */
    private static function update_builder_meta($row, $key, $value) {
        global $wpdb, $wp_current_filter;
        $post_id = (int) $row->post_id;
        $meta_id = (int) $row->row_id;
        $planned = $key === 'panels_data' ? maybe_serialize($value) : null;
        $value = sanitize_meta($key, $value, 'post', get_object_subtype('post', $post_id));
        $previous = $key === '_elementor_data' ? $row->meta_value : self::decode_builder_data($row->meta_value);
        if ($key === 'panels_data') {
            if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS) || $planned !== maybe_serialize($value)) {
                return false;
            }
            // Our SiteOrigin guards recognize this exact migration delta. Other
            // metadata filters run normally and a nested save has a different depth.
            $outer = self::$panels_migration_update;
            self::$panels_migration_update = array(
                'post_id' => $post_id,
                'depth' => count($wp_current_filter) + 1,
                'value' => $planned,
                'previous' => maybe_serialize($previous),
            );
            try {
                $check = apply_filters('update_post_metadata', null, $post_id, $key, $value, $previous);
            } finally {
                self::$panels_migration_update = $outer;
            }
        } else {
            $check = apply_filters('update_post_metadata', null, $post_id, $key, $value, $previous);
        }
        if ($check !== null) {
            return (bool) $check;
        }

        $stored = maybe_serialize($value);
        if ($planned !== null && $planned !== $stored) {
            return false;
        }
        do_action('update_post_meta', $meta_id, $post_id, $key, $value);
        do_action('update_postmeta', $meta_id, $post_id, $key, $stored);
        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} pm
             LEFT JOIN {$wpdb->postmeta} earlier ON earlier.post_id = pm.post_id
                 AND earlier.meta_key = pm.meta_key AND earlier.meta_id < pm.meta_id
             SET pm.meta_value = %s
             WHERE pm.meta_id = %d AND pm.post_id = %d AND pm.meta_key = %s
                 AND BINARY pm.meta_value = BINARY %s AND earlier.meta_id IS NULL",
            $stored, $meta_id, $post_id, $key, $row->meta_value
        ));
        if ($updated !== 1) {
            return false;
        }

        wp_cache_delete($post_id, 'post_meta');
        do_action('updated_post_meta', $meta_id, $post_id, $key, $value);
        do_action('updated_postmeta', $meta_id, $post_id, $key, $stored);
        return true;
    }

    private static function save_builder_row($row, $key, $value, $count, &$results) {
        if (self::update_builder_meta($row, $key, $value)) {
            $results['widgets_updated'] += $count;
        } else {
            $results['errors'][] = sprintf(
                __('Could not save display logic for post #%d. Refresh the migration page and try again.', 'widget-options'),
                $row->post_id
            );
        }
    }

    /**
     * Update Elementor widgets with snippet IDs
     * 
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array to update
     */
    private static function update_elementor_widgets($code_to_snippet_map, &$results) {
        $elementor_data = self::migration_rows('_elementor_data');

        foreach ($elementor_data as $data) {
            $elements = json_decode($data->meta_value, true);

            if (!is_array($elements)) {
                continue;
            }

            $row_results = array('widgets_updated' => 0);
            $updated_elements = self::update_elementor_elements($elements, $code_to_snippet_map, $row_results);

            if ($updated_elements !== false) {
                self::save_builder_row($data, '_elementor_data', wp_json_encode($updated_elements), $row_results['widgets_updated'], $results);
            }
        }
    }

    /**
     * Update Elementor elements recursively
     * 
     * @param array $elements Array of elements
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array
     * @return array|false Updated elements or false if no changes
     */
    private static function update_elementor_elements($elements, $code_to_snippet_map, &$results) {
        $changed = false;

        foreach ($elements as &$element) {
            $code = self::inline_code($element['settings'] ?? null, 'widgetopts_logic', 'widgetopts_logic_snippet_id');
            if (isset($code_to_snippet_map[$code])) {
                // Backup
                $element['settings']['widgetopts_logic_backup'] = $code;
                // Remove old
                unset($element['settings']['widgetopts_logic']);
                // Set new
                $element['settings']['widgetopts_logic_snippet_id'] = $code_to_snippet_map[$code];
                // Stamp version
                $element['settings']['widgetopts_wopt_version'] = WIDGETOPTS_VERSION;
                $changed = true;
                $results['widgets_updated']++;
            }

            // Process child elements
            if (!empty($element['elements']) && is_array($element['elements'])) {
                $updated_children = self::update_elementor_elements($element['elements'], $code_to_snippet_map, $results);
                if ($updated_children !== false) {
                    $element['elements'] = $updated_children;
                    $changed = true;
                }
            }
        }

        return $changed ? $elements : false;
    }

    /**
     * Update Beaver Builder nodes with snippet IDs
     * 
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array to update
     */
    private static function update_beaver_builder_nodes($code_to_snippet_map, &$results) {
        // Update both published and draft BB data
        $meta_keys = array('_fl_builder_data', '_fl_builder_draft');

        foreach ($meta_keys as $meta_key) {
            $beaver_data = self::migration_rows($meta_key);

            foreach ($beaver_data as $data) {
                $nodes = self::decode_builder_data($data->meta_value);

                if (!is_array($nodes)) {
                    continue;
                }

                $count = 0;

                foreach ($nodes as &$node) {
                    $code = self::beaver_inline_code($node);
                    if (isset($code_to_snippet_map[$code])) {
                        // Backup
                        $node->settings->widgetopts_settings_logic_backup = $code;
                        // Remove old
                        unset($node->settings->widgetopts_settings_logic);
                        // Set new
                        $node->settings->widgetopts_logic_snippet_id = $code_to_snippet_map[$code];
                        // Stamp version
                        $node->settings->widgetopts_wopt_version = WIDGETOPTS_VERSION;
                        $count++;
                    }
                }

                if ($count) {
                    self::save_builder_row($data, $meta_key, $nodes, $meta_key === '_fl_builder_data' ? $count : 0, $results);
                }
            }
        }
    }

    /**
     * Update SiteOrigin widgets with snippet IDs
     * 
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array to update
     */
    private static function update_siteorigin_widgets($code_to_snippet_map, &$results) {
        $siteorigin_data = self::migration_rows('panels_data');

        foreach ($siteorigin_data as $data) {
            $panels = self::decode_builder_data($data->meta_value);

            if (!is_array($panels) || empty($panels['widgets']) || !is_array($panels['widgets'])) {
                continue;
            }

            $count = 0;

            foreach ($panels['widgets'] as &$widget) {
                $code = self::siteorigin_inline_code($widget);
                if (isset($code_to_snippet_map[$code])) {
                    // Backup
                    $widget['extended_widget_opts']['class']['logic_backup'] = $code;
                    // Remove old
                    unset($widget['extended_widget_opts']['class']['logic']);
                    // Set new
                    $widget['extended_widget_opts']['class']['logic_snippet_id'] = $code_to_snippet_map[$code];
                    // Stamp version
                    $widget['extended_widget_opts']['class']['wopt_version'] = WIDGETOPTS_VERSION;
                    $count++;
                }
            }

            if ($count) {
                self::save_builder_row($data, 'panels_data', $panels, $count, $results);
            }
        }
    }

    /**
     * Get migration status
     * 
     * @return array Migration status data
     */
    public static function get_migration_status() {
        return get_option(self::MIGRATION_OPTION, array());
    }

    /**
     * Reset migration status (for manual retry)
     */
    public static function reset_migration() {
        delete_option(self::MIGRATION_OPTION);
    }

    /**
     * Read candidate rows in batches. Both the migration table and its actions
     * use the same post eligibility rules. Keep drafts and scheduled content.
     */
    private static function migration_rows($meta_key = '', $post_id = 0) {
        global $wpdb;
        $eligible = "p.post_type NOT IN ('revision', 'widgetopts_snippet', 'customize_changeset')
                     AND p.post_status NOT IN ('trash', 'auto-draft')";
        if ($post_id) {
            $eligible .= $wpdb->prepare(' AND p.ID = %d', $post_id);
        }
        // Switching an ordinary post back to the WordPress editor retains its old
        // layout. Template and custom document types can be embedded independently.
        if ($meta_key === '_elementor_data' || $meta_key === '_fl_builder_data' || $meta_key === '_fl_builder_draft') {
            $enabled_key = $meta_key === '_elementor_data' ? '_elementor_edit_mode' : '_fl_builder_enabled';
            $eligible .= $wpdb->prepare(
                " AND (p.post_type NOT IN ('post', 'page') OR COALESCE((
                    SELECT enabled.meta_value FROM {$wpdb->postmeta} enabled
                    WHERE enabled.post_id = p.ID AND enabled.meta_key = %s
                    ORDER BY enabled.meta_id LIMIT 1
                ), '') NOT IN ('', '0'))",
                $enabled_key
            );
        }
        $cursor = 0;
        do {
            if ($meta_key === '') {
                $rows = $wpdb->get_results(
                    "SELECT p.ID, p.ID AS row_id, p.post_title, p.post_content FROM {$wpdb->posts} p
                     WHERE $eligible AND p.ID > $cursor
                     AND (p.post_content LIKE '%extended_widget_opts%' OR p.post_content LIKE '%<!--start_widgetopts%')
                     ORDER BY p.ID LIMIT 50"
                );
            } else {
                $markers = array(
                    '_elementor_data' => 'widgetopts_logic',
                    '_fl_builder_data' => 'widgetopts_settings_logic',
                    '_fl_builder_draft' => 'widgetopts_settings_logic',
                    'panels_data' => 'extended_widget_opts',
                );
                $filter = $wpdb->prepare('pm.meta_key = %s AND pm.meta_value LIKE %s',
                    $meta_key, '%' . $wpdb->esc_like($markers[$meta_key]) . '%');
                $rows = $wpdb->get_results(
                    "SELECT pm.meta_id AS row_id, pm.post_id, p.post_title, pm.meta_value
                     FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                     WHERE $eligible AND $filter AND pm.meta_id > $cursor
                     AND NOT EXISTS (SELECT 1 FROM {$wpdb->postmeta} earlier
                                     WHERE earlier.post_id = pm.post_id AND earlier.meta_key = pm.meta_key
                                     AND earlier.meta_id < pm.meta_id)
                     ORDER BY pm.meta_id LIMIT 50"
                );
            }
            foreach ($rows as $row) {
                $cursor = (int) $row->row_id;
                yield $row;
            }
        } while (count($rows) === 50);
    }

    /**
     * Collect all legacy logic codes grouped by unique code with location details.
     * Used by the migration page to display a table of snippets to migrate.
     *
     * @return array Array of items: [ 'code' => string, 'locations' => [ ['type','label','id'], ... ] ]
     */
    public static function collect_all_logic_codes_with_locations() {
        return self::find_logic_codes();
    }

    // When checking the notice, stop at the first actionable location.
    private static function find_logic_codes($first_only = false, $location = array()) {
        $source = isset($location['type']) ? $location['type'] : '';
        $id = isset($location['id']) ? $location['id'] : 0;
        $map = array(); // code => [ locations ]

        // Helper to add a location entry
        $add = function ($code, $type, $label, $id = '') use (&$map) {
            $code = trim($code);
            if (empty($code)) return;
            $key = md5($code);
            if (!isset($map[$key])) {
                $map[$key] = array('code' => $code, 'locations' => array());
            }
            $map[$key]['locations'][] = array(
                'type'  => $type,
                'label' => $label,
                'id'    => $id,
            );
        };

        // 1. Classic Widgets
        if (!$source || $source === 'classic_widget') {
            foreach (self::assigned_widgets($source ? $id : '') as $widget_id => $sidebar_id) {
                foreach (self::get_classic_widget_logic_codes($widget_id) as $logic) {
                    $add($logic, 'classic_widget', $sidebar_id . ' / ' . $widget_id, $widget_id);
                }
                if ($first_only && $map) return array_values($map);
            }
        }

        // 2. Gutenberg blocks
        $posts = !$source || $source === 'gutenberg' ? self::migration_rows('', $id) : array();
        foreach ($posts as $post) {
            $blocks = parse_blocks($post->post_content);
            self::extract_logic_from_blocks_with_locations($blocks, $post, $add);
            if ($first_only && $map) return array_values($map);
        }

        // 3. Elementor
        $elementor_rows = !$source || $source === 'elementor' ? self::migration_rows('_elementor_data', $id) : array();
        foreach ($elementor_rows as $row) {
            $elements = json_decode($row->meta_value, true);
            if (is_array($elements)) {
                self::extract_elementor_logic_with_locations($elements, $row, $add);
            }
            if ($first_only && $map) return array_values($map);
        }

        // 4. Beaver Builder
        $beaver_rows = !$source || $source === 'beaver' ? self::migration_rows('_fl_builder_data', $id) : array();
        foreach ($beaver_rows as $row) {
            $nodes = self::decode_builder_data($row->meta_value);
            if (is_array($nodes)) {
                foreach ($nodes as $node) {
                    $code = self::beaver_inline_code($node);
                    if ($code !== '') {
                        $label = 'Beaver Builder — ' . ($row->post_title ?: '#' . $row->post_id);
                        $add($code, 'beaver', $label, $row->post_id);
                    }
                }
            }
            if ($first_only && $map) return array_values($map);
        }

        // 5. SiteOrigin
        $so_rows = !$source || $source === 'siteorigin' ? self::migration_rows('panels_data', $id) : array();
        foreach ($so_rows as $row) {
            $panels = self::decode_builder_data($row->meta_value);
            if (is_array($panels) && isset($panels['widgets']) && is_array($panels['widgets'])) {
                foreach ($panels['widgets'] as $widget) {
                    $code = self::siteorigin_inline_code($widget);
                    if ($code !== '') {
                        $label = 'SiteOrigin — ' . ($row->post_title ?: '#' . $row->post_id);
                        $add($code, 'siteorigin', $label, $row->post_id);
                    }
                }
            }
            if ($first_only && $map) return array_values($map);
        }

        // Re-index
        return array_values($map);
    }

    /**
     * Extract logic from Gutenberg blocks with location info (recursive)
     */
    private static function extract_logic_from_blocks_with_locations($blocks, $post, $add) {
        foreach ($blocks as $block) {
            $rule = self::block_rule($block);
            if ($rule) {
                $block_name = !empty($block['blockName']) ? $block['blockName'] : 'block';
                $label = 'Block editor — ' . ($post->post_title ?: '#' . $post->ID) . ' (' . $block_name . ')';
                $add($rule['code'], 'gutenberg', $label, $post->ID);
            }
            if (!empty($block['innerBlocks'])) {
                self::extract_logic_from_blocks_with_locations($block['innerBlocks'], $post, $add);
            }
        }
    }

    /**
     * Extract logic from Elementor elements with location info (recursive)
     */
    private static function extract_elementor_logic_with_locations($elements, $row, $add) {
        foreach ($elements as $element) {
            if (self::inline_code($element['settings'] ?? null, 'widgetopts_logic', 'widgetopts_logic_snippet_id') !== '') {
                $widget_type = isset($element['widgetType']) ? $element['widgetType'] : (isset($element['elType']) ? $element['elType'] : 'element');
                $label = 'Elementor — ' . ($row->post_title ?: '#' . $row->post_id) . ' (' . $widget_type . ')';
                $add($element['settings']['widgetopts_logic'], 'elementor', $label, $row->post_id);
            }
            if (!empty($element['elements']) && is_array($element['elements'])) {
                self::extract_elementor_logic_with_locations($element['elements'], $row, $add);
            }
        }
    }

    /**
     * Migrate selected snippets by their code hashes.
     *
     * @param array $items Array of [ 'hash' => md5, 'title' => string ]
     * @return array Results with snippets_created, widgets_updated, errors
     */
    public static function migrate_selected($items) {
        $results = array(
            'snippets_created' => 0,
            'widgets_updated'  => 0,
            'errors'           => array(),
        );

        // Build full scan first to get codes for hashes
        $all = self::collect_all_logic_codes_with_locations();
        $hash_to_code = array();
        foreach ($all as $entry) {
            $hash_to_code[md5($entry['code'])] = $entry['code'];
        }

        $code_to_snippet_map = array();

        foreach ($items as $item) {
            $hash  = $item['hash'];
            $title = !empty($item['title']) ? $item['title'] : 'Migrated Snippet';

            if (!isset($hash_to_code[$hash])) {
                $results['errors'][] = sprintf(
                    __('Code not found for hash: %s', 'widget-options'),
                    $hash
                );
                continue;
            }

            $code = $hash_to_code[$hash];

            // Check if snippet with this code already exists
            $existing_id = WidgetOpts_Snippets_CPT::find_snippet_by_code($code);
            if ($existing_id) {
                $code_to_snippet_map[$code] = $existing_id;
                continue;
            }

            $snippet_id = WidgetOpts_Snippets_CPT::create_snippet(
                $title,
                $code,
                __('Automatically migrated from legacy display logic.', 'widget-options')
            );

            if (!is_wp_error($snippet_id)) {
                $code_to_snippet_map[$code] = $snippet_id;
                $results['snippets_created']++;
            } else {
                $results['errors'][] = sprintf(
                    __('Failed to create snippet "%s": %s', 'widget-options'),
                    $title,
                    $snippet_id->get_error_message()
                );
            }
        }

        if (!empty($code_to_snippet_map)) {
            self::update_classic_widgets($code_to_snippet_map, $results);
            self::update_gutenberg_blocks($code_to_snippet_map, $results);
            self::update_elementor_widgets($code_to_snippet_map, $results);
            self::update_beaver_builder_nodes($code_to_snippet_map, $results);
            self::update_siteorigin_widgets($code_to_snippet_map, $results);
        }

        // Re-scan and update migration flag
        self::rescan_and_update_flag();

        return $results;
    }

    /**
     * Delete legacy logic code by hash from ALL editors (remove the field entirely).
     * This means display logic will no longer execute for those widgets.
     *
     * @param string $hash md5 hash of the code
     * @return array Results
     */
    public static function delete_legacy_code($hash) {
        $results = array('widgets_updated' => 0, 'errors' => array());

        // Get code for this hash
        $all = self::collect_all_logic_codes_with_locations();
        $code = null;
        foreach ($all as $entry) {
            if (md5($entry['code']) === $hash) {
                $code = $entry['code'];
                break;
            }
        }

        if ($code === null) {
            return $results;
        }

        $normalized = trim($code);

        // 1. Classic Widgets
        global $wpdb;
        $assigned = self::assigned_widgets();
        $widget_opts = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'widget_%'"
        );
        foreach ($widget_opts as $option) {
            $widgets = maybe_unserialize($option->option_value);
            if (!is_array($widgets)) continue;
            $updated = false;
            foreach ($widgets as $widget_num => &$instance) {
                $widget_id = substr($option->option_name, strlen('widget_')) . '-' . $widget_num;
                if (!isset($assigned[$widget_id])) continue;
                if (!is_array($instance)) continue;

                if ($option->option_name === 'widget_block' && !empty($instance['content']) && is_string($instance['content'])) {
                    $blocks = parse_blocks($instance['content']);
                    $widget_fallback = empty($instance['extended_widget_opts-' . $widget_id]);
                    $changed = self::delete_logic_from_blocks($blocks, $normalized, $results, $widget_fallback);
                    if ($changed !== false) {
                        $instance['content'] = serialize_blocks($changed);
                        $updated = true;
                    }
                }

                if (self::classic_widget_code($instance, $widget_id) === $normalized) {
                    unset($instance['extended_widget_opts-' . $widget_id]['class']['logic']);
                    $updated = true;
                    $results['widgets_updated']++;
                }
            }
            if ($updated) {
                update_option($option->option_name, $widgets);
            }
        }

        // 2. Gutenberg
        self::update_gutenberg_blocks(array($normalized => null), $results);

        // 3. Elementor
        $el_rows = self::migration_rows('_elementor_data');
        foreach ($el_rows as $row) {
            $elements = json_decode($row->meta_value, true);
            if (!is_array($elements)) continue;
            $row_results = array('widgets_updated' => 0);
            $changed = self::delete_logic_from_elementor($elements, $normalized, $row_results);
            if ($changed !== false) {
                self::save_builder_row($row, '_elementor_data', wp_json_encode($changed), $row_results['widgets_updated'], $results);
            }
        }

        // 4. Beaver Builder (both published and draft)
        foreach (array('_fl_builder_data', '_fl_builder_draft') as $bb_meta_key) {
            $bb_rows = self::migration_rows($bb_meta_key);
            foreach ($bb_rows as $row) {
                $nodes = self::decode_builder_data($row->meta_value);
                if (!is_array($nodes)) continue;
                $count = 0;
                foreach ($nodes as &$node) {
                    if (self::beaver_inline_code($node) === $normalized) {
                        unset($node->settings->widgetopts_settings_logic);
                        $count++;
                    }
                }
                if ($count) {
                    self::save_builder_row($row, $bb_meta_key, $nodes, $bb_meta_key === '_fl_builder_data' ? $count : 0, $results);
                }
            }
        }

        // 5. SiteOrigin
        $so_rows = self::migration_rows('panels_data');
        foreach ($so_rows as $row) {
            $panels = self::decode_builder_data($row->meta_value);
            if (!is_array($panels) || empty($panels['widgets']) || !is_array($panels['widgets'])) continue;
            $count = 0;
            foreach ($panels['widgets'] as &$widget) {
                if (self::siteorigin_inline_code($widget) === $normalized) {
                    unset($widget['extended_widget_opts']['class']['logic']);
                    $count++;
                }
            }
            if ($count) {
                self::save_builder_row($row, 'panels_data', $panels, $count, $results);
            }
        }

        // Re-scan
        self::rescan_and_update_flag();

        return $results;
    }

    /**
     * Delete logic field from Gutenberg blocks recursively
     */
    private static function delete_logic_from_blocks($blocks, $code, &$results, $widget_fallback = false) {
        return self::update_blocks_with_snippets($blocks, array($code => null), $results, $widget_fallback);
    }

    /**
     * Delete logic field from Elementor elements recursively
     */
    private static function delete_logic_from_elementor(&$elements, $code, &$results) {
        $changed = false;
        foreach ($elements as &$element) {
            if (self::inline_code($element['settings'] ?? null, 'widgetopts_logic', 'widgetopts_logic_snippet_id') === $code) {
                unset($element['settings']['widgetopts_logic']);
                $changed = true;
                $results['widgets_updated']++;
            }
            if (!empty($element['elements']) && is_array($element['elements'])) {
                $inner = self::delete_logic_from_elementor($element['elements'], $code, $results);
                if ($inner !== false) {
                    $element['elements'] = $inner;
                    $changed = true;
                }
            }
        }
        return $changed ? $elements : false;
    }

    /**
     * Re-scan all editors and update the migration required flag.
     * Cache the first actionable location, or the time of an empty scan.
     *
     * @return bool Whether the migration page has a rule to migrate.
     */
    public static function rescan_and_update_flag() {
        $items = self::find_logic_codes(true);
        self::remember_migration_location($items);
        return !empty($items);
    }

    public static function remember_migration_location($items) {
        $location = array('checked_at' => time());
        if ($items) {
            $first = $items[0]['locations'][0];
            $location = array('type' => $first['type'], 'id' => $first['id']);
        }
        set_transient('widgetopts_legacy_migration_location', $location, DAY_IN_SECONDS);
        update_option('wopts_display_logic_migration_required', !empty($items));
    }

    public static function is_migration_required() {
        $location = get_transient('widgetopts_legacy_migration_location');
        if (isset($location['type'], $location['id'])) {
            // Recheck the saved location in case its last rule was removed or trashed.
            if (self::find_logic_codes(true, $location)) return true;
        } elseif (isset($location['checked_at'])) {
            // Preview renders can repeatedly set this hint without a saved rule.
            if ($location['checked_at'] + 5 * MINUTE_IN_SECONDS > time()
                || !get_option('wopts_display_logic_migration_required')) {
                return false;
            }
        }
        return self::rescan_and_update_flag();
    }
}
