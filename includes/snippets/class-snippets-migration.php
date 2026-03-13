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
            $logic_codes = self::collect_all_logic_codes();

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
     * Collect all unique logic codes from all widget sources
     * 
     * @return array Array of unique logic codes
     */
    private static function collect_all_logic_codes() {
        $codes = array();

        // 1. Classic Widgets
        $sidebars_widgets = get_option('sidebars_widgets', array());
        foreach ($sidebars_widgets as $sidebar_id => $widgets) {
            if (!is_array($widgets) || $sidebar_id === 'wp_inactive_widgets') {
                continue;
            }
            foreach ($widgets as $widget_id) {
                $codes = array_merge($codes, self::get_classic_widget_logic_codes($widget_id));
            }
        }

        // 2. Gutenberg Widget Blocks (in widget areas)
        $codes = array_merge($codes, self::collect_gutenberg_logic_codes());

        // 3. Elementor
        $codes = array_merge($codes, self::collect_elementor_logic_codes());

        // 4. Beaver Builder
        $codes = array_merge($codes, self::collect_beaver_logic_codes());

        // 5. SiteOrigin
        $codes = array_merge($codes, self::collect_siteorigin_logic_codes());

        // Return unique codes only
        return array_unique(array_filter($codes));
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

        $codes = self::extract_logic_from_widget_instance($instance);

        if ($widget_base === 'block' && !empty($instance['content']) && is_string($instance['content'])) {
            $codes = array_merge($codes, self::extract_logic_from_blocks(parse_blocks($instance['content'])));
        }

        return array_unique(array_filter($codes));
    }

    /**
     * Collect logic codes from Gutenberg blocks
     * 
     * @return array Array of logic codes
     */
    private static function collect_gutenberg_logic_codes() {
        $codes = array();

        // Get all posts that might contain blocks
        global $wpdb;
        $posts = $wpdb->get_results(
            "SELECT ID, post_content FROM {$wpdb->posts} 
             WHERE post_content LIKE '%extended_widget_opts%' 
             AND post_type != 'revision'
             AND post_status IN ('publish', 'draft', 'private')"
        );

        foreach ($posts as $post) {
            $blocks = parse_blocks($post->post_content);
            $codes = array_merge($codes, self::extract_logic_from_blocks($blocks));
        }

        return $codes;
    }

    /**
     * Extract logic codes from blocks recursively
     * 
     * @param array $blocks Array of blocks
     * @return array Array of logic codes
     */
    private static function extract_logic_from_blocks($blocks) {
        $codes = array();

        foreach ($blocks as $block) {
            if (isset($block['attrs']['extended_widget_opts']['class']['logic'])) {
                $logic = $block['attrs']['extended_widget_opts']['class']['logic'];
                if (!empty($logic)) {
                    $codes[] = trim($logic);
                }
            }

            // Check inner blocks
            if (!empty($block['innerBlocks'])) {
                $codes = array_merge($codes, self::extract_logic_from_blocks($block['innerBlocks']));
            }
        }

        return $codes;
    }

    private static function extract_logic_from_widget_instance($instance) {
        $codes = array();

        if (!is_array($instance)) {
            return $codes;
        }

        foreach ($instance as $key => $value) {
            if (strpos($key, 'extended_widget_opts') === 0 && is_array($value)) {
                if (isset($value['class']['logic']) && !empty($value['class']['logic'])) {
                    $codes[] = trim($value['class']['logic']);
                }
            }
        }

        return $codes;
    }

    /**
     * Collect logic codes from Elementor
     * 
     * @return array Array of logic codes
     */
    private static function collect_elementor_logic_codes() {
        $codes = array();

        global $wpdb;
        $elementor_data = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_elementor_data' 
             AND pm.meta_value LIKE '%widgetopts_logic%'"
        );

        foreach ($elementor_data as $data) {
            $elements = json_decode($data->meta_value, true);
            if (is_array($elements)) {
                $codes = array_merge($codes, self::extract_elementor_logic($elements));
            }
        }

        return $codes;
    }

    /**
     * Extract logic codes from Elementor elements recursively
     * 
     * @param array $elements Array of elements
     * @return array Array of logic codes
     */
    private static function extract_elementor_logic($elements) {
        $codes = array();

        foreach ($elements as $element) {
            if (isset($element['settings']['widgetopts_logic']) && !empty($element['settings']['widgetopts_logic'])) {
                $codes[] = trim($element['settings']['widgetopts_logic']);
            }

            if (!empty($element['elements'])) {
                $codes = array_merge($codes, self::extract_elementor_logic($element['elements']));
            }
        }

        return $codes;
    }

    /**
     * Collect logic codes from Beaver Builder
     * 
     * @return array Array of logic codes
     */
    private static function collect_beaver_logic_codes() {
        $codes = array();

        global $wpdb;
        $beaver_data = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_fl_builder_data' 
             AND pm.meta_value LIKE '%widgetopts_settings_logic%'"
        );

        foreach ($beaver_data as $data) {
            $nodes = maybe_unserialize($data->meta_value);
            if (is_array($nodes)) {
                foreach ($nodes as $node) {
                    if (isset($node->settings->widgetopts_settings_logic) && !empty($node->settings->widgetopts_settings_logic)) {
                        $codes[] = trim($node->settings->widgetopts_settings_logic);
                    }
                }
            }
        }

        return $codes;
    }

    /**
     * Collect logic codes from SiteOrigin Page Builder
     * 
     * @return array Array of logic codes
     */
    private static function collect_siteorigin_logic_codes() {
        $codes = array();

        global $wpdb;
        $siteorigin_data = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = 'panels_data' 
             AND pm.meta_value LIKE '%extended_widget_opts%'"
        );

        foreach ($siteorigin_data as $data) {
            $panels = maybe_unserialize($data->meta_value);
            if (is_array($panels) && isset($panels['widgets'])) {
                foreach ($panels['widgets'] as $widget) {
                    if (isset($widget['extended_widget_opts']['class']['logic']) && !empty($widget['extended_widget_opts']['class']['logic'])) {
                        $codes[] = trim($widget['extended_widget_opts']['class']['logic']);
                    }
                }
            }
        }

        return $codes;
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
                if (!is_array($instance)) {
                    continue;
                }

                if ($option->option_name === 'widget_block' && !empty($instance['content']) && is_string($instance['content'])) {
                    $blocks = parse_blocks($instance['content']);
                    $updated_blocks = self::update_blocks_with_snippets($blocks, $code_to_snippet_map, $results);

                    if ($updated_blocks !== false) {
                        $instance['content'] = serialize_blocks($updated_blocks);
                        $updated = true;
                    }
                }

                foreach ($instance as $key => &$value) {
                    if (strpos($key, 'extended_widget_opts') === 0 && is_array($value)) {
                        if (isset($value['class']['logic']) && !empty($value['class']['logic'])) {
                            $code = trim($value['class']['logic']);

                            if (isset($code_to_snippet_map[$code])) {
                                // Backup old logic code
                                $value['class']['logic_backup'] = $code;
                                // Remove old logic field
                                unset($value['class']['logic']);
                                // Set new snippet ID
                                $value['class']['logic_snippet_id'] = $code_to_snippet_map[$code];
                                // Stamp version
                                $value['class']['wopt_version'] = WIDGETOPTS_VERSION;
                                $updated = true;
                                $results['widgets_updated']++;
                            }
                        }
                    }
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
        global $wpdb;

        $posts = $wpdb->get_results(
            "SELECT ID, post_content FROM {$wpdb->posts} 
             WHERE post_content LIKE '%extended_widget_opts%' 
             AND post_type != 'revision'
             AND post_status IN ('publish', 'draft', 'private')"
        );

        foreach ($posts as $post) {
            $blocks = parse_blocks($post->post_content);
            $updated_blocks = self::update_blocks_with_snippets($blocks, $code_to_snippet_map, $results);

            if ($updated_blocks !== false) {
                $new_content = serialize_blocks($updated_blocks);
                $wpdb->update(
                    $wpdb->posts,
                    array('post_content' => $new_content),
                    array('ID' => $post->ID),
                    array('%s'),
                    array('%d')
                );
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
    private static function update_blocks_with_snippets($blocks, $code_to_snippet_map, &$results) {
        $changed = false;

        foreach ($blocks as &$block) {
            if (isset($block['attrs']['extended_widget_opts']['class']['logic'])) {
                $code = trim($block['attrs']['extended_widget_opts']['class']['logic']);

                if (!empty($code) && isset($code_to_snippet_map[$code])) {
                    // Backup
                    $block['attrs']['extended_widget_opts']['class']['logic_backup'] = $code;
                    // Remove old
                    unset($block['attrs']['extended_widget_opts']['class']['logic']);
                    // Set new
                    $block['attrs']['extended_widget_opts']['class']['logic_snippet_id'] = $code_to_snippet_map[$code];
                    // Stamp version
                    $block['attrs']['extended_widget_opts']['class']['wopt_version'] = WIDGETOPTS_VERSION;
                    $changed = true;
                    $results['widgets_updated']++;
                }
            }

            // Process inner blocks
            if (!empty($block['innerBlocks'])) {
                $updated_inner = self::update_blocks_with_snippets($block['innerBlocks'], $code_to_snippet_map, $results);
                if ($updated_inner !== false) {
                    $block['innerBlocks'] = $updated_inner;
                    $changed = true;
                }
            }
        }

        return $changed ? $blocks : false;
    }

    /**
     * Update Elementor widgets with snippet IDs
     * 
     * @param array $code_to_snippet_map Map of code => snippet_id
     * @param array &$results Results array to update
     */
    private static function update_elementor_widgets($code_to_snippet_map, &$results) {
        global $wpdb;

        $elementor_data = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_elementor_data' 
             AND pm.meta_value LIKE '%widgetopts_logic%'"
        );

        foreach ($elementor_data as $data) {
            $elements = json_decode($data->meta_value, true);

            if (!is_array($elements)) {
                continue;
            }

            $updated_elements = self::update_elementor_elements($elements, $code_to_snippet_map, $results);

            if ($updated_elements !== false) {
                update_post_meta($data->post_id, '_elementor_data', wp_slash(json_encode($updated_elements)));
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
            if (isset($element['settings']['widgetopts_logic']) && !empty($element['settings']['widgetopts_logic'])) {
                $code = trim($element['settings']['widgetopts_logic']);

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
            }

            // Process child elements
            if (!empty($element['elements'])) {
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
        global $wpdb;

        // Update both published and draft BB data
        $meta_keys = array('_fl_builder_data', '_fl_builder_draft');

        foreach ($meta_keys as $meta_key) {
            $beaver_data = $wpdb->get_results($wpdb->prepare(
                "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
                 JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
                 WHERE pm.meta_key = %s 
                 AND pm.meta_value LIKE %s",
                $meta_key,
                '%widgetopts_settings_logic%'
            ));

            foreach ($beaver_data as $data) {
                $nodes = maybe_unserialize($data->meta_value);

                if (!is_array($nodes)) {
                    continue;
                }

                $changed = false;

                foreach ($nodes as &$node) {
                    if (isset($node->settings->widgetopts_settings_logic) && !empty($node->settings->widgetopts_settings_logic)) {
                        $code = trim($node->settings->widgetopts_settings_logic);

                        if (isset($code_to_snippet_map[$code])) {
                            // Backup
                            $node->settings->widgetopts_settings_logic_backup = $code;
                            // Remove old
                            unset($node->settings->widgetopts_settings_logic);
                            // Set new
                            $node->settings->widgetopts_logic_snippet_id = $code_to_snippet_map[$code];
                            // Stamp version
                            $node->settings->widgetopts_wopt_version = WIDGETOPTS_VERSION;
                            $changed = true;
                            if ($meta_key === '_fl_builder_data') {
                                $results['widgets_updated']++;
                            }
                        }
                    }
                }

                if ($changed) {
                    update_post_meta($data->post_id, $meta_key, $nodes);
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
        global $wpdb;

        $siteorigin_data = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = 'panels_data' 
             AND pm.meta_value LIKE '%extended_widget_opts%'"
        );

        foreach ($siteorigin_data as $data) {
            $panels = maybe_unserialize($data->meta_value);

            if (!is_array($panels) || !isset($panels['widgets'])) {
                continue;
            }

            $changed = false;

            foreach ($panels['widgets'] as &$widget) {
                if (isset($widget['extended_widget_opts']['class']['logic']) && !empty($widget['extended_widget_opts']['class']['logic'])) {
                    $code = trim($widget['extended_widget_opts']['class']['logic']);

                    if (isset($code_to_snippet_map[$code])) {
                        // Backup
                        $widget['extended_widget_opts']['class']['logic_backup'] = $code;
                        // Remove old
                        unset($widget['extended_widget_opts']['class']['logic']);
                        // Set new
                        $widget['extended_widget_opts']['class']['logic_snippet_id'] = $code_to_snippet_map[$code];
                        // Stamp version
                        $widget['extended_widget_opts']['class']['wopt_version'] = WIDGETOPTS_VERSION;
                        $changed = true;
                        $results['widgets_updated']++;
                    }
                }
            }

            if ($changed) {
                update_post_meta($data->post_id, 'panels_data', $panels);
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
     * Collect all legacy logic codes grouped by unique code with location details.
     * Used by the migration page to display a table of snippets to migrate.
     *
     * @return array Array of items: [ 'code' => string, 'locations' => [ ['type','label','id'], ... ] ]
     */
    public static function collect_all_logic_codes_with_locations() {
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
        $sidebars_widgets = get_option('sidebars_widgets', array());
        foreach ($sidebars_widgets as $sidebar_id => $widgets) {
            if (!is_array($widgets) || $sidebar_id === 'wp_inactive_widgets') {
                continue;
            }
            foreach ($widgets as $widget_id) {
                $codes = self::get_classic_widget_logic_codes($widget_id);
                foreach ($codes as $logic) {
                    $add($logic, 'classic_widget', $sidebar_id . ' / ' . $widget_id, $widget_id);
                }
            }
        }

        // 2. Gutenberg blocks
        global $wpdb;
        $posts = $wpdb->get_results(
            "SELECT ID, post_title, post_content FROM {$wpdb->posts}
             WHERE post_content LIKE '%extended_widget_opts%'
             AND post_type != 'revision'
             AND post_status IN ('publish', 'draft', 'private')"
        );
        foreach ($posts as $post) {
            $blocks = parse_blocks($post->post_content);
            self::extract_logic_from_blocks_with_locations($blocks, $post, $add);
        }

        // 3. Elementor
        $elementor_rows = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value, p.post_title
             FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_elementor_data'
             AND pm.meta_value LIKE '%widgetopts_logic%'"
        );
        foreach ($elementor_rows as $row) {
            $elements = json_decode($row->meta_value, true);
            if (is_array($elements)) {
                self::extract_elementor_logic_with_locations($elements, $row, $add);
            }
        }

        // 4. Beaver Builder
        $beaver_rows = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value, p.post_title
             FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_fl_builder_data'
             AND pm.meta_value LIKE '%widgetopts_settings_logic%'"
        );
        foreach ($beaver_rows as $row) {
            $nodes = maybe_unserialize($row->meta_value);
            if (is_array($nodes)) {
                foreach ($nodes as $node) {
                    if (isset($node->settings->widgetopts_settings_logic) && !empty($node->settings->widgetopts_settings_logic)) {
                        $label = 'Beaver Builder — ' . ($row->post_title ?: '#' . $row->post_id);
                        $add($node->settings->widgetopts_settings_logic, 'beaver', $label, $row->post_id);
                    }
                }
            }
        }

        // 5. SiteOrigin
        $so_rows = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value, p.post_title
             FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = 'panels_data'
             AND pm.meta_value LIKE '%extended_widget_opts%'"
        );
        foreach ($so_rows as $row) {
            $panels = maybe_unserialize($row->meta_value);
            if (is_array($panels) && isset($panels['widgets'])) {
                foreach ($panels['widgets'] as $widget) {
                    if (isset($widget['extended_widget_opts']['class']['logic']) && !empty($widget['extended_widget_opts']['class']['logic'])) {
                        $label = 'SiteOrigin — ' . ($row->post_title ?: '#' . $row->post_id);
                        $add($widget['extended_widget_opts']['class']['logic'], 'siteorigin', $label, $row->post_id);
                    }
                }
            }
        }

        // Re-index
        return array_values($map);
    }

    /**
     * Extract logic from Gutenberg blocks with location info (recursive)
     */
    private static function extract_logic_from_blocks_with_locations($blocks, $post, $add) {
        foreach ($blocks as $block) {
            if (isset($block['attrs']['extended_widget_opts']['class']['logic'])) {
                $logic = $block['attrs']['extended_widget_opts']['class']['logic'];
                if (!empty($logic)) {
                    $block_name = !empty($block['blockName']) ? $block['blockName'] : 'block';
                    $label = 'Block editor — ' . ($post->post_title ?: '#' . $post->ID) . ' (' . $block_name . ')';
                    $add($logic, 'gutenberg', $label, $post->ID);
                }
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
            if (isset($element['settings']['widgetopts_logic']) && !empty($element['settings']['widgetopts_logic'])) {
                $widget_type = isset($element['widgetType']) ? $element['widgetType'] : (isset($element['elType']) ? $element['elType'] : 'element');
                $label = 'Elementor — ' . ($row->post_title ?: '#' . $row->post_id) . ' (' . $widget_type . ')';
                $add($element['settings']['widgetopts_logic'], 'elementor', $label, $row->post_id);
            }
            if (!empty($element['elements'])) {
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
        $results = array('widgets_updated' => 0);

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
        $widget_opts = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'widget_%'"
        );
        foreach ($widget_opts as $option) {
            $widgets = maybe_unserialize($option->option_value);
            if (!is_array($widgets)) continue;
            $updated = false;
            foreach ($widgets as &$instance) {
                if (!is_array($instance)) continue;

                if ($option->option_name === 'widget_block' && !empty($instance['content']) && is_string($instance['content'])) {
                    $blocks = parse_blocks($instance['content']);
                    $changed = self::delete_logic_from_blocks($blocks, $normalized, $results);
                    if ($changed !== false) {
                        $instance['content'] = serialize_blocks($changed);
                        $updated = true;
                    }
                }

                foreach ($instance as $key => &$value) {
                    if (strpos($key, 'extended_widget_opts') === 0 && is_array($value)) {
                        if (isset($value['class']['logic']) && trim($value['class']['logic']) === $normalized) {
                            unset($value['class']['logic']);
                            $updated = true;
                            $results['widgets_updated']++;
                        }
                    }
                }
            }
            if ($updated) {
                update_option($option->option_name, $widgets);
            }
        }

        // 2. Gutenberg
        $posts = $wpdb->get_results(
            "SELECT ID, post_content FROM {$wpdb->posts}
             WHERE post_content LIKE '%extended_widget_opts%'
             AND post_type != 'revision'
             AND post_status IN ('publish', 'draft', 'private')"
        );
        foreach ($posts as $post) {
            $blocks = parse_blocks($post->post_content);
            $changed = self::delete_logic_from_blocks($blocks, $normalized, $results);
            if ($changed !== false) {
                $wpdb->update($wpdb->posts, array('post_content' => serialize_blocks($changed)), array('ID' => $post->ID), array('%s'), array('%d'));
            }
        }

        // 3. Elementor
        $el_rows = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_elementor_data' AND pm.meta_value LIKE '%widgetopts_logic%'"
        );
        foreach ($el_rows as $row) {
            $elements = json_decode($row->meta_value, true);
            if (!is_array($elements)) continue;
            $changed = self::delete_logic_from_elementor($elements, $normalized, $results);
            if ($changed !== false) {
                update_post_meta($row->post_id, '_elementor_data', wp_slash(json_encode($changed)));
            }
        }

        // 4. Beaver Builder (both published and draft)
        foreach (array('_fl_builder_data', '_fl_builder_draft') as $bb_meta_key) {
            $bb_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
                 JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
                 WHERE pm.meta_key = %s AND pm.meta_value LIKE %s",
                $bb_meta_key,
                '%widgetopts_settings_logic%'
            ));
            foreach ($bb_rows as $row) {
                $nodes = maybe_unserialize($row->meta_value);
                if (!is_array($nodes)) continue;
                $changed = false;
                foreach ($nodes as &$node) {
                    if (isset($node->settings->widgetopts_settings_logic) && trim($node->settings->widgetopts_settings_logic) === $normalized) {
                        unset($node->settings->widgetopts_settings_logic);
                        $changed = true;
                        if ($bb_meta_key === '_fl_builder_data') {
                            $results['widgets_updated']++;
                        }
                    }
                }
                if ($changed) {
                    update_post_meta($row->post_id, $bb_meta_key, $nodes);
                }
            }
        }

        // 5. SiteOrigin
        $so_rows = $wpdb->get_results(
            "SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = 'panels_data' AND pm.meta_value LIKE '%extended_widget_opts%'"
        );
        foreach ($so_rows as $row) {
            $panels = maybe_unserialize($row->meta_value);
            if (!is_array($panels) || !isset($panels['widgets'])) continue;
            $changed = false;
            foreach ($panels['widgets'] as &$widget) {
                if (isset($widget['extended_widget_opts']['class']['logic']) && trim($widget['extended_widget_opts']['class']['logic']) === $normalized) {
                    unset($widget['extended_widget_opts']['class']['logic']);
                    $changed = true;
                    $results['widgets_updated']++;
                }
            }
            if ($changed) {
                update_post_meta($row->post_id, 'panels_data', $panels);
            }
        }

        // Re-scan
        self::rescan_and_update_flag();

        return $results;
    }

    /**
     * Delete logic field from Gutenberg blocks recursively
     */
    private static function delete_logic_from_blocks(&$blocks, $code, &$results) {
        $changed = false;
        foreach ($blocks as &$block) {
            if (isset($block['attrs']['extended_widget_opts']['class']['logic'])) {
                if (trim($block['attrs']['extended_widget_opts']['class']['logic']) === $code) {
                    unset($block['attrs']['extended_widget_opts']['class']['logic']);
                    $changed = true;
                    $results['widgets_updated']++;
                }
            }
            if (!empty($block['innerBlocks'])) {
                $inner = self::delete_logic_from_blocks($block['innerBlocks'], $code, $results);
                if ($inner !== false) {
                    $block['innerBlocks'] = $inner;
                    $changed = true;
                }
            }
        }
        return $changed ? $blocks : false;
    }

    /**
     * Delete logic field from Elementor elements recursively
     */
    private static function delete_logic_from_elementor(&$elements, $code, &$results) {
        $changed = false;
        foreach ($elements as &$element) {
            if (isset($element['settings']['widgetopts_logic']) && trim($element['settings']['widgetopts_logic']) === $code) {
                unset($element['settings']['widgetopts_logic']);
                $changed = true;
                $results['widgets_updated']++;
            }
            if (!empty($element['elements'])) {
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
     * If no legacy logic found, flag is removed.
     */
    public static function rescan_and_update_flag() {
        // Delete current flag first
        delete_option('wopts_display_logic_migration_required');
        // Re-scan (will set the flag if legacy logic still exists)
        WidgetOpts_Snippets_CPT::scan_for_legacy_logic();
    }
}
