<?php
/**
 * Display Logic Snippets - Custom Post Type
 *
 * Registers and manages the widgetopts_snippet CPT for storing
 * reusable display logic code snippets.
 *
 * @copyright   Copyright (c) 2024, Widget Options Team
 * @since       5.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Class WidgetOpts_Snippets_CPT
 * 
 * Handles Custom Post Type registration and basic operations for snippets
 */
class WidgetOpts_Snippets_CPT {

    /**
     * Post type name
     */
    const POST_TYPE = 'widgetopts_snippet';

    /**
     * Option name for migration status
     */
    const MIGRATION_OPTION = 'widgetopts_snippets_migration';

    /**
     * Display logic version that triggers scanning
     */
    const DISPLAY_LOGIC_VERSION = '2.0';

    /**
     * Initialize the class
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'register_post_type'));
        add_action('init', array(__CLASS__, 'check_display_logic_version'), 20);
        add_filter('post_row_actions', array(__CLASS__, 'remove_quick_edit'), 10, 2);
        add_action('admin_head-post.php', array(__CLASS__, 'hide_publishing_actions'));
        add_action('admin_head-post-new.php', array(__CLASS__, 'hide_publishing_actions'));
    }

    /**
     * Register the Custom Post Type
     */
    public static function register_post_type() {
        $labels = array(
            'name'                  => _x('Logic Snippets', 'Post type general name', 'widget-options'),
            'singular_name'         => _x('Logic Snippet', 'Post type singular name', 'widget-options'),
            'menu_name'             => _x('Logic Snippets', 'Admin Menu text', 'widget-options'),
            'name_admin_bar'        => _x('Logic Snippet', 'Add New on Toolbar', 'widget-options'),
            'add_new'               => __('Add New', 'widget-options'),
            'add_new_item'          => __('Add New Snippet', 'widget-options'),
            'new_item'              => __('New Snippet', 'widget-options'),
            'edit_item'             => __('Edit Snippet', 'widget-options'),
            'view_item'             => __('View Snippet', 'widget-options'),
            'all_items'             => __('All Snippets', 'widget-options'),
            'search_items'          => __('Search Snippets', 'widget-options'),
            'not_found'             => __('No snippets found.', 'widget-options'),
            'not_found_in_trash'    => __('No snippets found in Trash.', 'widget-options'),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => false,
            'publicly_queryable'    => false,
            'show_ui'               => true,
            'show_in_menu'          => false, // We'll add it as submenu
            'query_var'             => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'capabilities'          => array(
                'edit_post'          => 'manage_options',
                'read_post'          => 'edit_posts',
                'delete_post'        => 'manage_options',
                'edit_posts'         => 'manage_options',
                'edit_others_posts'  => 'manage_options',
                'publish_posts'      => 'manage_options',
                'read_private_posts' => 'manage_options',
                'create_posts'       => 'manage_options',
            ),
            'has_archive'           => false,
            'hierarchical'          => false,
            'menu_position'         => null,
            'supports'              => array('title'),
            'show_in_rest'          => true,
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Check display logic version and scan widgets if needed
     * Runs once when version is below 2.0 or undefined
     */
    public static function check_display_logic_version() {
        if (!is_admin()) {
            return;
        }

        $version = get_option('wopts_display_logic_version', false);

        if ($version !== false && version_compare($version, self::DISPLAY_LOGIC_VERSION, '>=')) {
            return;
        }

        // Set version to prevent re-scanning
        update_option('wopts_display_logic_version', self::DISPLAY_LOGIC_VERSION);

        // Scan all widgets for legacy logic that needs migration
        self::scan_for_legacy_logic();
    }

    /**
     * Scan all widget sources for legacy display logic code
     * Sets wopts_display_logic_migration_required flag if found
     */
    public static function scan_for_legacy_logic() {
        // 1. Classic Widgets
        $sidebars_widgets = get_option('sidebars_widgets', array());
        foreach ($sidebars_widgets as $sidebar_id => $widgets) {
            if (!is_array($widgets) || $sidebar_id === 'wp_inactive_widgets') {
                continue;
            }
            foreach ($widgets as $widget_id) {
                preg_match('/^(.+)-(\d+)$/', $widget_id, $matches);
                if (count($matches) !== 3) {
                    continue;
                }
                $widget_base = $matches[1];
                $widget_num = (int) $matches[2];
                $widget_options_data = get_option('widget_' . $widget_base);
                if (!is_array($widget_options_data) || !isset($widget_options_data[$widget_num])) {
                    continue;
                }
                $instance = $widget_options_data[$widget_num];

                if (self::has_legacy_logic_in_widget_instance($instance)) {
                    update_option('wopts_display_logic_migration_required', true);
                    return;
                }

                if ($widget_base === 'block' && !empty($instance['content']) && is_string($instance['content'])) {
                    $blocks = parse_blocks($instance['content']);
                    if (self::has_legacy_logic_in_blocks($blocks)) {
                        update_option('wopts_display_logic_migration_required', true);
                        return;
                    }
                }
            }
        }

        // 2. Gutenberg blocks in posts
        global $wpdb;
        $has_legacy = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_content LIKE '%extended_widget_opts%' 
             AND post_content LIKE '%\"logic\"%'
             AND post_type != 'revision'
             AND post_status IN ('publish', 'draft', 'private')
             LIMIT 1"
        );
        if ($has_legacy > 0) {
            update_option('wopts_display_logic_migration_required', true);
            return;
        }

        // 3. Elementor
        $has_elementor_legacy = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = '_elementor_data' 
             AND pm.meta_value LIKE '%widgetopts_logic%'
             LIMIT 1"
        );
        if ($has_elementor_legacy > 0) {
            update_option('wopts_display_logic_migration_required', true);
            return;
        }

        // 4. Beaver Builder
        $has_beaver_legacy = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key LIKE '_fl_builder_%' 
             AND pm.meta_value LIKE '%widgetopts_logic%'
             LIMIT 1"
        );
        if ($has_beaver_legacy > 0) {
            update_option('wopts_display_logic_migration_required', true);
            return;
        }

        // 5. SiteOrigin
        $has_so_legacy = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_type != 'revision'
             WHERE pm.meta_key = 'panels_data' 
             AND pm.meta_value LIKE '%\"logic\"%'
             LIMIT 1"
        );
        if ($has_so_legacy > 0) {
            update_option('wopts_display_logic_migration_required', true);
            return;
        }
    }

    /**
     * Check recursively whether parsed blocks contain legacy logic.
     *
     * @param array $blocks Parsed blocks.
     * @return bool
     */
    private static function has_legacy_logic_in_blocks($blocks) {
        foreach ($blocks as $block) {
            if (!empty($block['attrs']['extended_widget_opts']['class']['logic'])) {
                return true;
            }

            if (!empty($block['innerBlocks']) && self::has_legacy_logic_in_blocks($block['innerBlocks'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether a widget instance contains legacy logic in instance meta.
     *
     * @param array $instance Widget instance data.
     * @return bool
     */
    private static function has_legacy_logic_in_widget_instance($instance) {
        if (!is_array($instance)) {
            return false;
        }

        foreach ($instance as $key => $value) {
            if (strpos($key, 'extended_widget_opts') === 0 && is_array($value) && !empty($value['class']['logic'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check migration status and run if needed
     */
    public static function check_and_run_migration() {
        $migration_status = get_option(self::MIGRATION_OPTION, array());
        
        // If migration completed or failed (manual intervention required), do nothing
        if (isset($migration_status['status']) && in_array($migration_status['status'], array('completed', 'failed'))) {
            return;
        }

        // If never migrated, run migration
        if (empty($migration_status)) {
            self::run_migration();
        }
    }

    /**
     * Run the migration process
     */
    public static function run_migration() {
        require_once WIDGETOPTS_PLUGIN_DIR . 'includes/snippets/class-snippets-migration.php';
        WidgetOpts_Snippets_Migration::migrate();
    }

    /**
     * Get all published snippets
     * 
     * @param string $search Optional search term to filter snippets by title
     * @return array Array of snippet objects with id, title, description
     */
    public static function get_all_snippets($search = '') {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $snippets = get_posts($args);

        $result = array();
        foreach ($snippets as $snippet) {
            $result[] = array(
                'id'          => $snippet->ID,
                'title'       => $snippet->post_title,
                'description' => get_post_meta($snippet->ID, '_widgetopts_snippet_description', true),
                'code'        => $snippet->post_content,
            );
        }

        return $result;
    }

    /**
     * Get snippet by ID
     * 
     * @param int $snippet_id Snippet post ID
     * @return array|false Snippet data or false if not found
     */
    public static function get_snippet($snippet_id) {
        $snippet = get_post($snippet_id);
        
        if (!$snippet || $snippet->post_type !== self::POST_TYPE || $snippet->post_status !== 'publish') {
            return false;
        }

        return array(
            'id'          => $snippet->ID,
            'title'       => $snippet->post_title,
            'description' => get_post_meta($snippet->ID, '_widgetopts_snippet_description', true),
            'code'        => $snippet->post_content,
        );
    }

    /**
     * Get snippet code by ID
     * 
     * @param int $snippet_id Snippet post ID
     * @return string|false Snippet code or false if not found
     */
    public static function get_snippet_code($snippet_id) {
        $snippet = self::get_snippet($snippet_id);
        return $snippet ? $snippet['code'] : false;
    }

    /**
     * Create a new snippet
     * 
     * @param string $title Snippet title
     * @param string $code PHP code
     * @param string $description Optional description
     * @return int|WP_Error Post ID on success, WP_Error on failure
     */
    public static function create_snippet($title, $code, $description = '') {
        $post_data = array(
            'post_title'   => sanitize_text_field($title),
            'post_content' => $code, // Will be validated on execution
            'post_type'    => self::POST_TYPE,
            'post_status'  => 'publish',
        );

        $post_id = wp_insert_post($post_data, true);

        if (!is_wp_error($post_id) && $description) {
            update_post_meta($post_id, '_widgetopts_snippet_description', sanitize_textarea_field($description));
        }

        return $post_id;
    }

    /**
     * Find snippet by code (for migration - to avoid duplicates)
     * 
     * @param string $code PHP code to search
     * @return int|false Snippet ID or false if not found
     */
    public static function find_snippet_by_code($code) {
        global $wpdb;
        
        $normalized_code = trim($code);
        
        $snippet_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = %s 
             AND post_status = 'publish' 
             AND TRIM(post_content) = %s 
             LIMIT 1",
            self::POST_TYPE,
            $normalized_code
        ));

        return $snippet_id ? (int) $snippet_id : false;
    }

    /**
     * Generate a unique title for migrated snippet
     * 
     * @param string $code PHP code
     * @param int $index Migration index
     * @return string Generated title
     */
    public static function generate_snippet_title($code, $index = 0) {
        // Default: Migrated Snippet with index
        return sprintf(__('Migrated Snippet #%d', 'widget-options'), $index + 1);
    }

    /**
     * Remove Quick Edit from row actions
     * 
     * @param array $actions Row actions
     * @param WP_Post $post Current post
     * @return array Modified actions
     */
    public static function remove_quick_edit($actions, $post) {
        if ($post->post_type === self::POST_TYPE) {
            unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }

    /**
     * Hide publishing actions (status, visibility) in post editor
     */
    public static function hide_publishing_actions() {
        global $post;
        
        if (isset($post) && $post->post_type === self::POST_TYPE) {
            echo '<style>
                #minor-publishing-actions,
                #visibility,
                .misc-pub-visibility,
                .misc-pub-curtime {
                    display: none !important;
                }
            </style>';
        }
    }
}

// Initialize
WidgetOpts_Snippets_CPT::init();
