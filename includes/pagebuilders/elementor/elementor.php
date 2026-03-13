<?php

/**
 * Extends funtionality to Elementor Pagebuilder
 *
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       4.3
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!function_exists('widgetopts_elementor_section')) {
    //Add "Widget Options" section to every Elementor Widgets
    add_action('elementor/element/after_section_end', 'widgetopts_elementor_section', 5, 3);
    function widgetopts_elementor_section($element, $section_id, $args)
    {
        if (widgetopts_is_elementor_edit_mode()) {
            global $widget_options;

            //filter the elements first to avoid conflicts that can cause pagebuilder not to load
            if (!in_array($element->get_name(), array('global-settings', 'section', 'page-settings', 'oew-blog-grid'))) {

                //create array of section_id to set Widget Options Section to single section to avoid issues
                $widgetopts_elementor_section_id = apply_filters(
                    'widgetopts_elementor_section_id',
                    array(
                        'section_image',
                        'section_advanced',
                        'section_title',
                        'section_editor',
                        'section_video',
                        'section_button',
                        'section_divider',
                        'section_spacer',
                        'section_map',
                        'section_icon',
                        'section_gallery',
                        'section_image_carousel',
                        'section_icon_list',
                        'section_counter',
                        'section_testimonial',
                        'section_tabs',
                        'section_toggle',
                        'section_social_icon',
                        'section_alert',
                        'section_audio',
                        'section_shortcode',
                        'section_anchor',
                        'section_sidebar',
                        'section_layout',
                        'section_slides',
                        'section_form_fields',
                        'section_list',
                        'section_header',
                        'section_pricing',
                        'section_countdown',
                        'section_buttons_content',
                        'section_blockquote_content',
                        'section_content',
                        'section_login_content',
                        'text_elements',
                        'section_side_a_content',
                        'section_side_b_content',
                        '_section_style'
                    )
                );

                //filter by the section_ids above
                if (in_array($section_id, $widgetopts_elementor_section_id)) {
                    // Prevent duplicate control registration for the same element
                    static $processed_elements = [];
                    $el_key = spl_object_id($element);
                    if (isset($processed_elements[$el_key])) {
                        return;
                    }
                    $processed_elements[$el_key] = true;

                    $element->start_controls_section(
                        'widgetopts_section',
                        [
                            'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
                            'label' => __('Widget Options', 'widget-options'),
                        ],
                        [
                            'overwrite'         => true
                        ]
                    );

                    $element->start_controls_tabs('widgetopts_content_tabs', [
                        'overwrite'         => true
                    ]);

                    if (isset($widget_options['visibility']) && 'activate' == $widget_options['visibility']) {
                        widgetopts_elementor_tab_visibility($element, $section_id, $args);
                    }

                    if (isset($widget_options['state']) && 'activate' == $widget_options['state']) {
                        widgetopts_elementor_tab_state($element, $section_id, $args);
                    }

                    if ('activate' == $widget_options['logic'] || (isset($widget_options['sliding']) && 'activate' == $widget_options['sliding'] && in_array($element->get_name(), array('button', 'button_plus', 'eael-creative-button', 'cta')))) {
                        widgetopts_elementor_tab_settings($element, $section_id, $args);
                    }

                    //upsell pro
                    if (!is_plugin_active('extended-widget-options/plugin.php')) {
                        $element->start_controls_tab(
                            'widgetopts_tab_upsell',
                            [
                                'label' => __('<i class="dashicons dashicons-plus"></i>', 'widget-options')
                            ],
                            [
                                'overwrite'         => true
                            ]
                        );
                        $upgrade_link = apply_filters('widget_options_site_url', trailingslashit(WIDGETOPTS_PLUGIN_WEBSITE) . '?utm_source=elementor&utm_medium=upgrade&utm_campaign=upgradebtn');
                        $element->add_control(
                            'widgetopts_pro',
                            [
                                'type' => Elementor\Controls_Manager::RAW_HTML,
                                'raw' => '<div class="elementor-panel-nerd-box">
                                    <i class="elementor-panel-nerd-box-icon dashicons dashicons-lock"></i>
                    						<div class="elementor-panel-nerd-box-title">' .
                                    __('Unlock All Widget Options', 'widget-options') .
                                    '</div>
                    						<div class="elementor-panel-nerd-box-message">' .
                                    __('Upgrade to Widget Options Extended to unlock all options to easily control and manage each Elementor widget.', 'widget-options') .
                                    '</div>
                    						<a href="' . $upgrade_link . '" class="elementor-panel-nerd-box-link elementor-button elementor-button-default elementor-go-pro" target="_blank">' .
                                    __('Upgrade Now!', 'widget-options') .
                                    '</a>
                    						</div>',
                            ],
                            [
                                'overwrite'         => true
                            ]
                        );
                        $element->end_controls_tab();
                    }
                    $element->end_controls_tabs();

                    $element->end_controls_section();
                }
            }
        }
    }
}

if (!function_exists('widgetopts_elementor_tab_visibility')) {
    function widgetopts_elementor_tab_visibility($element, $section_id, $args)
    {
        global $widget_options, $widgetopts_taxonomies, $widgetopts_pages, $widgetopts_types, $widgetopts_categories;

        $pages      = (!empty($widgetopts_pages))       ? $widgetopts_pages         : array();
        $taxonomies = (!empty($widgetopts_taxonomies))  ? $widgetopts_taxonomies    : array();
        $types      = (!empty($widgetopts_types))       ? $widgetopts_types         : array();
        $categories = (!empty($widgetopts_categories))  ? $widgetopts_categories    : array();

        // print_r( $get_terms['community-category'] );

        $element->start_controls_tab(
            'widgetopts_tab_visibility',
            [
                'label' => __('<span class="dashicons dashicons-visibility"></span>', 'widget-options')
            ],
            [
                'overwrite'         => true
            ]
        );

        $element->add_control(
            'widgetopts_visibility',
            [
                'label'         => __('Show/Hide', 'widget-options'),
                'type'          => Elementor\Controls_Manager::SELECT,
                'default'       => 'hide',
                'options'       => [
                    'show' => __('Show on Selected Pages'),
                    'hide' => __('Hide on Selected Pages')
                ],
                // 'separator'     => 'none'
            ],
            [
                'overwrite'         => true
            ]
        );

        if (isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['post_type']) && '1' == $widget_options['settings']['visibility']['post_type']) {
            $pages_array = array();
            if (!empty($pages)) {
                foreach ($pages as $page) {
                    $pages_array[$page->ID] = $page->post_title;
                }

                $element->add_control(
                    'widgetopts_pages',
                    [
                        'label'             => __('Pages', 'widget-options'),
                        'type'              => Elementor\Controls_Manager::SELECT2,
                        'multiple'          => true,
                        'label_block'       => true,
                        'separator'         => 'before',
                        'options'           => $pages_array,
                        'render_type'       => 'none',
                        'description'       => __('Click on the field to search and select pages', 'widget-options')
                    ],
                    [
                        'overwrite'         => true
                    ]
                );
            }

            if (!empty($types)) {
                $types_array = array();
                foreach ($types as $ptype => $type) {
                    $types_array[$ptype] = $type->labels->name;
                }

                $element->add_control(
                    'widgetopts_types',
                    [
                        'label'             => __('Post Types', 'widget-options'),
                        'type'              => Elementor\Controls_Manager::SELECT2,
                        'multiple'          => true,
                        'label_block'       => true,
                        'separator'         => 'before',
                        'options'           => $types_array,
                        'render_type'       => 'none',
                        'description'       => __('Click on the field to search and select custom post types', 'widget-options')
                    ],
                    [
                        'overwrite'         => true
                    ]
                );
            }
        }

        if (isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['taxonomies']) && '1' == $widget_options['settings']['visibility']['taxonomies']) {

            if (!empty($categories)) {
                $cat_array = array();
                foreach ($categories as $cat) {
                    $cat_array[$cat->cat_ID] = $cat->cat_name;
                }

                $element->add_control(
                    'widgetopts_tax_category',
                    [
                        'label'             => __('Categories', 'widget-options'),
                        'type'              => Elementor\Controls_Manager::SELECT2,
                        'multiple'          => true,
                        'label_block'       => true,
                        'separator'         => 'before',
                        'options'           => $cat_array,
                        'render_type'       => 'none',
                        'description'       => __('Click on the field to search and select categories', 'widget-options')
                    ],
                    [
                        'overwrite'         => true
                    ]
                );
            }

            if (!empty($taxonomies)) {
                $tax_array = array();
                foreach ($taxonomies as $taxonomy) {
                    $tax_array[$taxonomy->name] = $taxonomy->label;
                }

                $element->add_control(
                    'widgetopts_taxonomies',
                    [
                        'label'             => __('Taxonomies', 'widget-options'),
                        'type'              => Elementor\Controls_Manager::SELECT2,
                        'multiple'          => true,
                        'label_block'       => true,
                        'separator'         => 'before',
                        'options'           => $tax_array,
                        'render_type'       => 'none',
                        'description'       => __('Click on the field to search and select taxonomies', 'widget-options')
                    ],
                    [
                        'overwrite'         => true
                    ]
                );
            }
        }

        if (isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['misc']) && '1' == $widget_options['settings']['visibility']['misc']) {
            $element->add_control(
                'widgetopts_misc',
                [
                    'label'             => __('Miscellaneous', 'widget-options'),
                    'type'              => Elementor\Controls_Manager::SELECT2,
                    'multiple'          => true,
                    'label_block'       => true,
                    'separator'         => 'before',
                    'options'           => [
                        'home'      =>  __('Home/Front', 'widget-options'),
                        'blog'      =>  __('Blog', 'widget-options'),
                        'archives'  =>  __('Archives', 'widget-options'),
                        '404'       =>  __('404', 'widget-options'),
                        'search'    =>  __('Search', 'widget-options')
                    ],
                    'render_type'       => 'none',
                    'description'       => __('Click on the field to search and select miscellaneous pages', 'widget-options')
                ],
                [
                    'overwrite'         => true
                ]
            );
        }
        $element->end_controls_tab();
    }
}

if (!function_exists('widgetopts_elementor_tab_state')) {
    function widgetopts_elementor_tab_state($element, $section_id, $args)
    {
        global $widget_options;

        $element->start_controls_tab(
            'widgetopts_tab_state',
            [
                'label' => __('<span class="dashicons dashicons-admin-users"></span>', 'widget-options')
            ],
            [
                'overwrite'         => true
            ]
        );

        $element->add_control(
            'widgetopts_roles_state',
            [
                'label'         => __('User Login State', 'widget-options'),
                'type'          => Elementor\Controls_Manager::SELECT,
                'default'       => 'hide',
                'options'       => [
                    ''     => __('Select Visibility Option'),
                    'in' => __('Show only for Logged-in Users'),
                    'out' => __('Show only for Logged-out Users')
                ],
                'description'   => __('Restrict widget visibility for logged-in and logged-out users.', 'widget-options')
            ],
            [
                'overwrite'         => true
            ]
        );

        $element->end_controls_tab();
    }
}

if (!function_exists('widgetopts_elementor_tab_settings')) {
    function widgetopts_elementor_tab_settings($element, $section_id, $args)
    {
        global $widget_options;

        $element->start_controls_tab(
            'widgetopts_tab_settings',
            [
                'label' => __('<span class="dashicons dashicons-admin-generic"></span>', 'widget-options')
            ],
            [
                'overwrite'         => true
            ]
        );

        if (is_plugin_active('sliding-widget-options/plugin.php') && 'activate' == $widget_options['sliding'] && in_array($element->get_name(), array('button', 'button_plus', 'eael-creative-button', 'cta'))) {
            $element->add_control(
                'widgetopts_open_sliding',
                [
                    'label'          => __('Open Pop-up or Sliding Widgets', 'widget-options'),
                    'type'           => Elementor\Controls_Manager::SWITCHER,
                    'return_value'   => 'on',
                    'default'        => '',
                    'separator'      => 'none'
                ],
                [
                    'overwrite'         => true
                ]
            );
        }

        if ('activate' == $widget_options['logic']) {
            // Hidden control to store legacy logic value (avoids self-referencing condition)
            $element->add_control(
                'widgetopts_logic',
                [
                    'type'          => Elementor\Controls_Manager::HIDDEN,
                    'default'       => '',
                ],
                [
                    'overwrite'         => true
                ]
            );

            // Hidden flag: set to '1' when user clicks Clear button
            $element->add_control(
                'widgetopts_logic_cleared',
                [
                    'type'          => Elementor\Controls_Manager::HIDDEN,
                    'default'       => '',
                ],
                [
                    'overwrite'         => true
                ]
            );

            // Widget Options version stamp — used to determine if legacy logic should be used
            $element->add_control(
                'widgetopts_wopt_version',
                [
                    'type'          => Elementor\Controls_Manager::HIDDEN,
                    'default'       => '',
                ],
                [
                    'overwrite'         => true
                ]
            );

            // RAW_HTML warning + Clear button — shown only when legacy logic exists and no snippet assigned
            $migration_url = admin_url('options-general.php?page=widgetopts_migration');
            $migration_link = current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)
                ? '<br><a href="' . esc_url($migration_url) . '" target="_blank">' . __('Go to Migration Page', 'widget-options') . ' &rarr;</a>'
                : '';
            $element->add_control(
                'widgetopts_legacy_warning',
                [
                    'type'          => Elementor\Controls_Manager::RAW_HTML,
                    'raw'           => '<div style="margin-bottom:10px;">'
                        . '<p style="margin:0 0 5px;"><strong>' . __('Legacy Display Logic Code', 'widget-options') . '</strong></p>'
                        . '<p style="margin:0;padding:8px 12px;background:#fff3cd;border-left:4px solid #ffc107;color:#856404;font-size:12px;">'
                        . __('This element uses legacy inline display logic that needs migration.', 'widget-options')
                        . $migration_link
                        . '</p>'
                        . '<button type="button" class="elementor-button widgetopts-clear-legacy-logic" style="margin-top:8px;padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:12px;">' . __('Clear Legacy Logic', 'widget-options') . '</button>'
                        . '</div>',
                    'condition'     => [
                        'widgetopts_logic!' => '',
                        'widgetopts_logic_snippet_id' => '',
                    ],
                ],
                [
                    'overwrite'         => true
                ]
            );

            // Get available snippets for dropdown
            $snippet_options = array('' => __('— No Logic (Always Show) —', 'widget-options'));
            if (class_exists('WidgetOpts_Snippets_CPT')) {
                $snippets = WidgetOpts_Snippets_CPT::get_all_snippets();
                foreach ($snippets as $snippet) {
                    $snippet_options[$snippet['id']] = $snippet['title'];
                }
            }

            $snippet_description = __('Select a logic snippet to control when this widget is displayed.', 'widget-options');
            if (current_user_can('manage_options')) {
                $snippet_description .= '<br><a href="' . admin_url('edit.php?post_type=widgetopts_snippet') . '" target="_blank" style="display:inline-block;margin-top:5px;padding:3px 8px;background:#0073aa;color:#fff;text-decoration:none;border-radius:3px;font-size:11px;">' . __('Manage Snippets', 'widget-options') . ' →</a>';
            }

            $element->add_control(
                'widgetopts_logic_snippet_id',
                [
                    'type'          => Elementor\Controls_Manager::SELECT2,
                    'label'         => __('Display Logic Snippet', 'widget-options'),
                    'label_block'   => true,
                    'description'   => $snippet_description,
                    'options'       => $snippet_options,
                    'default'       => '',
                    'condition'     => [
                        'widgetopts_logic' => '',
                    ],
                ],
                [
                    'overwrite'         => true
                ]
            );
        }


        if (isset($widget_options['acf']) && 'activate' == $widget_options['acf']) {
            $fields = array();

            if (function_exists('acf_get_field_groups')) {
                $groups = acf_get_field_groups();
                if (is_array($groups)) {
                    foreach ($groups as $group) {
                        $fields_group = acf_get_fields($group);
                        if (!empty($fields_group)) {
                            foreach ($fields_group as $k => $fg) {
                                $fields[$fg['key']] = $fg['label'];
                            }
                        }
                    }
                }
            } else {
                $groups = apply_filters('acf/get_field_groups', array());
                if (is_array($groups)) {
                    foreach ($groups as $group) {
                        $fields_group = apply_filters('acf/field_group/get_fields', array(), $group['id']);
                        if (!empty($fields_group)) {
                            foreach ($fields_group as $k => $fg) {
                                $fields[$fg['key']] = $fg['label'];
                            }
                        }
                    }
                }
            }

            $element->add_control(
                'widgetopts_acf_title',
                [
                    'type' => Elementor\Controls_Manager::RAW_HTML,
                    'separator'         => 'before',
                    'raw' => '<h3>' . __('Advanced Custom Fields', 'widget-options') . '</h3>',
                ],
                [
                    'overwrite'         => true
                ]
            );

            $element->add_control(
                'widgetopts_acf_visibility',
                [
                    'label'         => __('Show/Hide', 'widget-options'),
                    'type'          => Elementor\Controls_Manager::SELECT,
                    'default'       => 'hide',
                    'options'       => [
                        'show' => __('Show when Condition\'s Met'),
                        'hide' => __('Hide when Condition\'s Met')
                    ],
                    'separator'         => 'before',
                ],
                [
                    'overwrite'         => true
                ]
            );

            $element->add_control(
                'widgetopts_acf_field',
                [
                    'label'             => __('Select ACF Field', 'widget-options'),
                    'type'              => Elementor\Controls_Manager::SELECT2,
                    'multiple'          => false,
                    'label_block'       => true,
                    'options'           => $fields,
                    'render_type'       => 'none',
                    'description'       => __('Select ACF field.', 'widget-options')
                ],
                [
                    'overwrite'         => true
                ]
            );

            $element->add_control(
                'widgetopts_acf_condition',
                [
                    'label'             => __('Condition', 'widget-options'),
                    'type'              => Elementor\Controls_Manager::SELECT2,
                    'multiple'          => false,
                    'label_block'       => true,
                    'options'           => [
                        'equal'      =>  __('Is Equal To', 'widget-options'),
                        'not_equal'  =>  __('Is Not Equal To', 'widget-options'),
                        'contains'   =>  __('Contains', 'widget-options'),
                        'not_contains'   =>  __('Does Not Contain', 'widget-options'),
                        'empty'      =>  __('Is Empty', 'widget-options'),
                        'not_empty'  =>  __('Is Not Empty', 'widget-options')
                    ],
                    'render_type'       => 'none',
                    'description'       => __('Select your condition for this widget visibility.', 'widget-options')
                ],
                [
                    'overwrite'         => true
                ]
            );
            $element->add_control(
                'widgetopts_acf',
                [
                    'type'          => Elementor\Controls_Manager::TEXTAREA,
                    'label'         => __('Conditional Value', 'widget-options'),
                    'description'   => __('Add your Conditional Value here if you selected Equal to, Not Equal To or Contains on the selection above.', 'widget-options'),
                    // 'separator'     => 'none',
                ],
                [
                    'overwrite'         => true
                ]
            );
        }

        $element->end_controls_tab();
    }
}

if (!function_exists('widgetopts_is_elementor_edit_mode')) {
    function widgetopts_is_elementor_edit_mode()
    {
        // Check Elementor's edit mode using the query parameters as fallback
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return true;
        }

        // Fallback check based on the request parameters
        if (isset($_GET['elementor-preview']) || isset($_GET['action']) && $_GET['action'] === 'elementor') {
            return true;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'elementor_ajax') {
            return true;
        }

        return false;
    }
}

/**
 * Protect legacy display logic from modification/injection on Elementor save.
 * Strategy: capture old DB value before save, restore it after save.
 * This prevents both code injection via console AND data loss before migration.
 * 
 * @since 5.1
 */
add_action('elementor/document/before_save', function($document) {
    $post_id = $document->get_main_id();
    $GLOBALS['_wopts_el_pre_save_' . $post_id] = get_post_meta($post_id, '_elementor_data', true);
}, 5, 1);

add_action('elementor/document/after_save', function($document) {
    $post_id = $document->get_main_id();
    $key = '_wopts_el_pre_save_' . $post_id;
    $old_raw = isset($GLOBALS[$key]) ? $GLOBALS[$key] : '';
    unset($GLOBALS[$key]);

    // Build map of element_id => old widgetopts_logic value
    $old_logic = array();
    if ($old_raw) {
        $old_els = is_string($old_raw) ? json_decode($old_raw, true) : $old_raw;
        if (is_array($old_els)) {
            $walk_old = function($els) use (&$walk_old, &$old_logic) {
                foreach ($els as $el) {
                    if (!empty($el['id']) && !empty($el['settings']['widgetopts_logic'])) {
                        $old_logic[$el['id']] = $el['settings']['widgetopts_logic'];
                    }
                    if (!empty($el['elements'])) $walk_old($el['elements']);
                }
            };
            $walk_old($old_els);
        }
    }

    // Read the just-saved data and fix it
    $saved_raw = get_post_meta($post_id, '_elementor_data', true);
    if (empty($saved_raw)) return;
    $saved_els = json_decode($saved_raw, true);
    if (!is_array($saved_els)) return;

    $changed = false;
    $protect = function(&$els) use (&$protect, &$old_logic, &$changed) {
        foreach ($els as &$el) {
            $id = isset($el['id']) ? $el['id'] : '';
            $wopt_ver = isset($el['settings']['widgetopts_wopt_version']) ? $el['settings']['widgetopts_wopt_version'] : '';
            $has_snippet = !empty($el['settings']['widgetopts_logic_snippet_id']);
            $has_legacy = !empty($el['settings']['widgetopts_logic']);

            // If wopt_version >= 4.2: legacy logic is obsolete — clear it
            if ($wopt_ver !== '' && version_compare($wopt_ver, '4.2', '>=')) {
                if ($has_legacy) {
                    $el['settings']['widgetopts_logic'] = '';
                    $changed = true;
                }
                if (!empty($el['elements'])) $protect($el['elements']);
                continue;
            }

            // Auto-clear legacy logic if snippet_id is already set (migration done)
            if ($has_snippet && $has_legacy) {
                $el['settings']['widgetopts_logic'] = '';
                $el['settings']['widgetopts_wopt_version'] = WIDGETOPTS_VERSION;
                $changed = true;
                if (!empty($el['elements'])) $protect($el['elements']);
                continue;
            }

            // User intentionally cleared legacy logic via Clear button
            $was_cleared = !empty($el['settings']['widgetopts_logic_cleared']);
            if ($was_cleared) {
                $el['settings']['widgetopts_logic'] = '';
                unset($el['settings']['widgetopts_logic_cleared']);
                $el['settings']['widgetopts_wopt_version'] = WIDGETOPTS_VERSION;
                $changed = true;
                if (!empty($el['elements'])) $protect($el['elements']);
                continue;
            }

            $old_val = ($id && isset($old_logic[$id])) ? $old_logic[$id] : '';
            $new_val = isset($el['settings']['widgetopts_logic']) ? $el['settings']['widgetopts_logic'] : '';
            if ($new_val !== $old_val) {
                if ($old_val !== '') {
                    $el['settings']['widgetopts_logic'] = $old_val;
                } else {
                    unset($el['settings']['widgetopts_logic']);
                }
                $changed = true;
            }

            if (!empty($el['elements'])) $protect($el['elements']);
        }
    };
    $protect($saved_els);

    if ($changed) {
        update_post_meta($post_id, '_elementor_data', wp_slash(json_encode($saved_els)));
    }
}, 10, 1);

/**
 * Add script to refresh snippets in Elementor editor
 * 
 * @since 5.1
 */
add_action('elementor/editor/after_enqueue_scripts', function() {
    ?>
    <style>
    /* Make legacy logic textarea readonly via CSS */
    .elementor-control-widgetopts_logic textarea[data-setting="widgetopts_logic"] {
        background: #f9f2f4 !important;
        color: #c7254e !important;
        font-family: monospace !important;
        font-size: 12px !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
    }
    </style>
    <script>
    (function() {
        // Convert Elementor's native Select2 to AJAX mode for snippet dropdown
        function initElementorSnippetSelect2() {
            if (typeof jQuery === 'undefined') return;
            var $select = jQuery('select[data-setting="widgetopts_logic_snippet_id"]');
            if (!$select.length) return;
            
            // Destroy Elementor's native Select2 if present
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            
            // Init Select2 with AJAX transport for dynamic search
            $select.select2({
                placeholder: '— No Logic (Always Show) —',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                dropdownParent: $select.closest('.elementor-control-content'),
                ajax: {
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'widgetopts_get_snippets_ajax',
                            search: params.term || ''
                        };
                    },
                    processResults: function(response) {
                        var results = [{id: '', text: '— No Logic (Always Show) —'}];
                        if (response.success && response.data && response.data.snippets) {
                            response.data.snippets.forEach(function(snippet) {
                                results.push({id: String(snippet.id), text: snippet.title, description: snippet.description || ''});
                            });
                        }
                        return { results: results };
                    },
                    cache: true
                }
            });
            
            // Add dynamic description element if not present
            var $control = $select.closest('.elementor-control-content');
            var $descEl = $control.find('.widgetopts-snippet-desc');
            if (!$descEl.length) {
                $descEl = jQuery('<p class="widgetopts-snippet-desc elementor-control-field-description" style="font-style: italic; color: #666; display:none;"></p>');
                $control.append($descEl);
            }
            
            // Sync Select2 change back to Elementor model + update description
            $select.off('select2:select.woDesc select2:unselect.woDesc');
            $select.on('select2:select.woDesc', function(e) {
                $select.trigger('change');
                var desc = e.params.data.description || '';
                if (desc) {
                    $descEl.text(desc).show();
                } else {
                    $descEl.text('').hide();
                }
            });
            $select.on('select2:unselect.woDesc', function() {
                $select.trigger('change');
                $descEl.text('').hide();
            });
        }
        
        // Clear Legacy Logic button handler
        function initClearLegacyLogicButton() {
            if (typeof jQuery === 'undefined') return;
            jQuery(document).off('click.woptsClearLegacy').on('click.woptsClearLegacy', '.widgetopts-clear-legacy-logic', function(e) {
                e.preventDefault();
                var btn = this;
                var elElement = jQuery(btn).closest('.elementor-editor-element-settings-list, .elementor-editor-element-edit').closest('.elementor-element');
                if (!elElement.length) {
                    // Fallback: find from panel
                    var panelEl = document.getElementById('elementor-panel');
                    if (panelEl && typeof elementor !== 'undefined' && elementor.selection) {
                        var selected = elementor.selection.getElements();
                        if (selected && selected[0]) {
                            var container = selected[0];
                            if (typeof $e !== 'undefined') {
                                $e.run('document/elements/settings', {
                                    container: container,
                                    settings: { widgetopts_logic: '', widgetopts_logic_cleared: '1' }
                                });
                            }
                            jQuery(btn).closest('.elementor-control-widgetopts_legacy_warning').slideUp();
                            return;
                        }
                    }
                }
                var dataId = elElement.attr('data-id');
                if (dataId && typeof elementor !== 'undefined' && typeof $e !== 'undefined') {
                    var container = elementor.getContainer(dataId);
                    if (container) {
                        $e.run('document/elements/settings', {
                            container: container,
                            settings: { widgetopts_logic: '', widgetopts_logic_cleared: '1' }
                        });
                    }
                }
                jQuery(btn).closest('.elementor-control-widgetopts_legacy_warning').slideUp();
            });
        }

        // Make legacy logic textarea readonly via MutationObserver
        var legacyObserver = null;
        function makeLegacyLogicReadonly() {
            if (typeof jQuery === 'undefined') return;
            // Apply to any currently visible
            jQuery('textarea[data-setting="widgetopts_logic"]').each(function() {
                if (!this.hasAttribute('readonly')) {
                    this.setAttribute('readonly', 'readonly');
                }
            });
            // Watch for new textareas appearing in the panel
            if (legacyObserver) legacyObserver.disconnect();
            var panelEl = document.getElementById('elementor-panel');
            if (!panelEl) return;
            legacyObserver = new MutationObserver(function(mutations) {
                var tas = panelEl.querySelectorAll('textarea[data-setting="widgetopts_logic"]:not([readonly])');
                tas.forEach(function(ta) {
                    ta.setAttribute('readonly', 'readonly');
                });
            });
            legacyObserver.observe(panelEl, { childList: true, subtree: true });
        }

        // Wait for elementor to load and register hooks
        function initElementorHooks() {
            if (typeof elementor !== 'undefined' && elementor.hooks) {
                elementor.hooks.addAction('panel/open_editor/widget', function() {
                    setTimeout(initElementorSnippetSelect2, 500);
                    setTimeout(makeLegacyLogicReadonly, 500);
                    initClearLegacyLogicButton();
                });
                elementor.hooks.addAction('panel/open_editor/section', function() {
                    setTimeout(initElementorSnippetSelect2, 500);
                    setTimeout(makeLegacyLogicReadonly, 500);
                    initClearLegacyLogicButton();
                });
                elementor.hooks.addAction('panel/open_editor/column', function() {
                    setTimeout(initElementorSnippetSelect2, 500);
                    setTimeout(makeLegacyLogicReadonly, 500);
                    initClearLegacyLogicButton();
                });
            } else {
                setTimeout(initElementorHooks, 500);
            }
        }
        
        initElementorHooks();
    })();
    </script>
    <?php
});
