<?php
/**
 * Display Logic Snippets - API
 *
 * Provides AJAX endpoints and helper functions for accessing snippets
 * from various editors (widgets, Gutenberg, Elementor, etc.)
 *
 * @copyright   Copyright (c) 2024, Widget Options Team
 * @since       5.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Class WidgetOpts_Snippets_API
 * 
 * Handles API endpoints and data retrieval for snippets
 */
class WidgetOpts_Snippets_API {

    /**
     * Initialize the API
     */
    public static function init() {
        // AJAX endpoints
        add_action('wp_ajax_widgetopts_get_snippets', array(__CLASS__, 'ajax_get_snippets'));
        
        // Localize script data
        add_action('admin_enqueue_scripts', array(__CLASS__, 'localize_snippets_data'));
    }

    /**
     * AJAX handler to get all snippets
     */
    public static function ajax_get_snippets() {
        // Check if user can edit widgets
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'widget-options')));
        }

        $snippets = WidgetOpts_Snippets_CPT::get_all_snippets();
        
        // Format for Select2 / dropdown
        $formatted = array(
            array(
                'id'    => '',
                'text'  => __('— No Logic —', 'widget-options'),
            )
        );

        foreach ($snippets as $snippet) {
            $formatted[] = array(
                'id'          => $snippet['id'],
                'text'        => $snippet['title'],
                'description' => $snippet['description'],
            );
        }

        wp_send_json_success($formatted);
    }

    /**
     * Localize snippets data for JavaScript
     */
    public static function localize_snippets_data($hook) {
        // Only on widgets page and customizer
        if (!in_array($hook, array('widgets.php', 'customize.php', 'post.php', 'post-new.php'))) {
            return;
        }

        $snippets = WidgetOpts_Snippets_CPT::get_all_snippets();
        
        $formatted = array(
            array(
                'id'          => '',
                'title'       => __('— No Logic —', 'widget-options'),
                'description' => '',
            )
        );

        foreach ($snippets as $snippet) {
            $formatted[] = array(
                'id'          => $snippet['id'],
                'title'       => $snippet['title'],
                'description' => $snippet['description'],
            );
        }

        wp_localize_script('jquery', 'widgetoptsSnippets', array(
            'snippets' => $formatted,
            'nonce'    => wp_create_nonce('widgetopts_snippets'),
            'strings'  => array(
                'selectSnippet' => __('Select Logic Snippet', 'widget-options'),
                'noLogic'       => __('— No Logic —', 'widget-options'),
                'search'        => __('Search snippets...', 'widget-options'),
            ),
        ));
    }

    /**
     * Get snippets for dropdown (non-AJAX)
     * 
     * @return array Array of snippets formatted for dropdown
     */
    public static function get_snippets_for_dropdown() {
        $snippets = WidgetOpts_Snippets_CPT::get_all_snippets();
        
        $options = array(
            '' => __('— No Logic —', 'widget-options'),
        );

        foreach ($snippets as $snippet) {
            $options[$snippet['id']] = $snippet['title'];
        }

        return $options;
    }

    /**
     * Execute snippet logic by ID
     * 
     * @param int $snippet_id Snippet post ID
     * @return bool|null True to show, false to hide, null if snippet not found
     */
    public static function execute_snippet($snippet_id) {
        if (empty($snippet_id)) {
            return null;
        }

        $code = WidgetOpts_Snippets_CPT::get_snippet_code($snippet_id);
        
        if ($code === false) {
            return null;
        }

        // Apply legacy filters for backward compatibility
        $code = apply_filters('widget_options_logic_override', $code);
        $code = apply_filters('extended_widget_options_logic_override', $code);

        // Handle filter returns
        if ($code === false) {
            return false;
        }
        if ($code === true) {
            return true;
        }

        // Decode and execute
        $code = htmlspecialchars_decode($code, ENT_QUOTES);

        // Use the existing safe_eval function
        if (function_exists('widgetopts_safe_eval')) {
            return widgetopts_safe_eval($code);
        }

        return null;
    }

    /**
     * Get snippet ID from widget options (with backward compatibility)
     * 
     * Checks for new snippet_id field, falls back to legacy logic field
     * 
     * @param array $opts Widget options array
     * @param string $editor Editor type (classic, elementor, beaver, siteorigin)
     * @return int|string|null Snippet ID, legacy code, or null
     */
    public static function get_logic_from_opts($opts, $editor = 'classic') {
        switch ($editor) {
            case 'elementor':
                // New format
                if (isset($opts['widgetopts_logic_snippet_id']) && !empty($opts['widgetopts_logic_snippet_id'])) {
                    return array('type' => 'snippet', 'value' => $opts['widgetopts_logic_snippet_id']);
                }
                // Legacy format (should not exist after migration, but keep for safety)
                if (isset($opts['widgetopts_logic']) && !empty($opts['widgetopts_logic'])) {
                    return array('type' => 'legacy', 'value' => $opts['widgetopts_logic']);
                }
                break;

            case 'beaver':
                // New format
                if (isset($opts->widgetopts_logic_snippet_id) && !empty($opts->widgetopts_logic_snippet_id)) {
                    return array('type' => 'snippet', 'value' => $opts->widgetopts_logic_snippet_id);
                }
                // Legacy format
                if (isset($opts->widgetopts_settings_logic) && !empty($opts->widgetopts_settings_logic)) {
                    return array('type' => 'legacy', 'value' => $opts->widgetopts_settings_logic);
                }
                break;

            case 'siteorigin':
            case 'classic':
            default:
                // New format
                if (isset($opts['class']['logic_snippet_id']) && !empty($opts['class']['logic_snippet_id'])) {
                    return array('type' => 'snippet', 'value' => $opts['class']['logic_snippet_id']);
                }
                // Legacy format
                if (isset($opts['class']['logic']) && !empty($opts['class']['logic'])) {
                    return array('type' => 'legacy', 'value' => $opts['class']['logic']);
                }
                break;
        }

        return null;
    }

    /**
     * Process display logic and return visibility
     * 
     * @param array|object $opts Widget options
     * @param string $editor Editor type
     * @return bool|null True to show, false to hide, null if no logic
     */
    public static function process_display_logic($opts, $editor = 'classic') {
        $logic = self::get_logic_from_opts($opts, $editor);

        if ($logic === null) {
            return null; // No logic defined
        }

        if ($logic['type'] === 'snippet') {
            return self::execute_snippet($logic['value']);
        }

        // Legacy code execution (for backward compatibility during transition)
        if ($logic['type'] === 'legacy') {
            $code = stripslashes(trim($logic['value']));
            $code = apply_filters('widget_options_logic_override', $code);
            $code = apply_filters('extended_widget_options_logic_override', $code);

            if ($code === false) {
                return false;
            }
            if ($code === true) {
                return true;
            }

            $code = htmlspecialchars_decode($code, ENT_QUOTES);
            
            if (function_exists('widgetopts_safe_eval')) {
                return widgetopts_safe_eval($code);
            }
        }

        return null;
    }
}

// Initialize
WidgetOpts_Snippets_API::init();
