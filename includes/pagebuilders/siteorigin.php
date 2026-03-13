<?php

/**
 * Handle compatibility with Pagebuilder by SiteOrigin Plugin
 *
 * Process AJAX actions.
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       3.0
 */
// Exit if accessed directly
if (! defined('ABSPATH')) exit;

if (!function_exists('widgetopts_siteorigin_panels_data')) {
    add_filter('siteorigin_panels_data', 'widgetopts_siteorigin_panels_data', 10, 4);
    function widgetopts_siteorigin_panels_data($panels_data, $post_id)
    {
        global $widget_options;
        if (!is_admin()) {
            if (isset($panels_data['widgets']) && !empty($panels_data['widgets']) && is_array($panels_data['widgets'])) {

                global $current_user;

                foreach ($panels_data['widgets'] as $key => $widgets) {
                    if (isset($widgets['extended_widget_opts']) && !empty($widgets['extended_widget_opts'])) {

                        if (isset($panels_data['widgets'][$key]) && 'activate' == $widget_options['logic']) {
                            // New snippet-based system
                            if (isset($widgets['extended_widget_opts']['class']['logic_snippet_id']) && !empty($widgets['extended_widget_opts']['class']['logic_snippet_id'])) {
                                $snippet_id = $widgets['extended_widget_opts']['class']['logic_snippet_id'];
                                if (class_exists('WidgetOpts_Snippets_API')) {
                                    $result = WidgetOpts_Snippets_API::execute_snippet($snippet_id);
                                    if ($result === false) {
                                        unset($panels_data['widgets'][$key]);
                                    }
                                }
                            }
                            // Legacy support for old inline logic
                            elseif (isset($widgets['extended_widget_opts']['class']['logic']) && !empty($widgets['extended_widget_opts']['class']['logic'])) {
                                // Flag that legacy migration is needed
                                if (!get_option('wopts_display_logic_migration_required', false)) {
                                    update_option('wopts_display_logic_migration_required', true);
                                }

                                $display_logic = stripslashes(trim($widgets['extended_widget_opts']['class']['logic']));
                                $display_logic = apply_filters('widget_options_logic_override', $display_logic);
                                if ($display_logic === false) {
                                    unset($panels_data['widgets'][$key]);
                                }
                                if ($display_logic === true) {
                                    // return true;
                                }
                                $display_logic = htmlspecialchars_decode($display_logic, ENT_QUOTES);
                                try {
                                    if (!widgetopts_safe_eval($display_logic)) {
                                        unset($panels_data['widgets'][$key]);
                                    }
                                } catch (ParseError $e) {
                                    unset($panels_data['widgets'][$key]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $panels_data;
    }
}

/**
 * Protect legacy display logic from modification/injection on SiteOrigin save.
 * Intercepts panels_data meta save, restores old logic values from DB.
 * 
 * @since 5.1
 */
if (!function_exists('widgetopts_siteorigin_protect_logic_on_save')) {
    add_filter('update_post_metadata', 'widgetopts_siteorigin_protect_logic_on_save', 10, 5);
    function widgetopts_siteorigin_protect_logic_on_save($check, $object_id, $meta_key, $meta_value, $prev_value) {
        if ($meta_key !== 'panels_data') return $check;

        static $processing = false;
        if ($processing) return $check;

        // Get old panels data from DB
        $old_data = get_post_meta($object_id, 'panels_data', true);
        if (!is_array($old_data) || empty($old_data['widgets'])) return $check;

        // Collect known old logic values
        $old_logic_set = array();
        foreach ($old_data['widgets'] as $widget) {
            if (isset($widget['extended_widget_opts']['class']['logic']) && $widget['extended_widget_opts']['class']['logic'] !== '') {
                $old_logic_set[] = $widget['extended_widget_opts']['class']['logic'];
            }
        }
        if (empty($old_logic_set)) return $check;

        // Check new data
        $new_data = is_array($meta_value) ? $meta_value : maybe_unserialize($meta_value);
        if (!is_array($new_data) || empty($new_data['widgets'])) return $check;

        $modified = false;
        foreach ($new_data['widgets'] as &$widget) {
            if (!isset($widget['extended_widget_opts']['class'])) continue;

            $cls = &$widget['extended_widget_opts']['class'];
            $wopt_ver = isset($cls['wopt_version']) ? $cls['wopt_version'] : '';
            $has_snippet = !empty($cls['logic_snippet_id']);
            $has_legacy = !empty($cls['logic']);

            // If wopt_version >= 4.2: legacy logic is obsolete — clear it
            if ($wopt_ver !== '' && version_compare($wopt_ver, '4.2', '>=')) {
                if ($has_legacy) {
                    $cls['logic'] = '';
                    $modified = true;
                }
                unset($cls);
                continue;
            }

            // Auto-clear legacy logic if snippet_id is already set (migration done)
            if ($has_snippet && $has_legacy) {
                $cls['logic'] = '';
                $cls['wopt_version'] = WIDGETOPTS_VERSION;
                $modified = true;
                unset($cls);
                continue;
            }

            // User intentionally cleared legacy logic via Clear button
            if (!empty($cls['logic_cleared'])) {
                $cls['logic'] = '';
                unset($cls['logic_cleared']);
                $cls['wopt_version'] = WIDGETOPTS_VERSION;
                $modified = true;
                unset($cls);
                continue;
            }

            if (!isset($cls['logic'])) {
                unset($cls);
                continue;
            }
            $val = $cls['logic'];
            if ($val !== '' && !in_array($val, $old_logic_set, true)) {
                // Injected value not in old data — strip
                $cls['logic'] = '';
                $modified = true;
            }

            unset($cls);
        }

        if ($modified) {
            $processing = true;
            update_post_meta($object_id, 'panels_data', $new_data);
            $processing = false;
            return true; // Short-circuit original update
        }

        return $check;
    }
}

if (!function_exists('widgetopts_siteorigin_panels_widget_classes')) {
    add_filter('siteorigin_panels_widget_classes', 'widgetopts_siteorigin_panels_widget_classes', 10, 4);
    function widgetopts_siteorigin_panels_widget_classes($classes, $widget, $instance, $widget_info)
    {
        if (isset($instance['extended_widget_opts'])) {
            global $widget_options;

            $get_classes    = widgetopts_classes_generator($instance['extended_widget_opts'], $widget_options, $widget_options['settings'], true);
            $get_classes[]  = 'widgetopts-SO';

            $classes = apply_filters('widgetopts_siteorigin_panels_widget_classes', array_merge($classes, $get_classes), $widget_info);
        }

        return $classes;
    }
}
