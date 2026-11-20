<?php

// Builder save hooks do not run for REST meta updates or direct metadata writes.
add_filter('add_post_metadata', 'widgetopts_guard_builder_meta', 5, 4);
add_filter('update_post_metadata', 'widgetopts_guard_builder_meta', 5, 4);
add_filter('update_post_metadata_by_mid', 'widgetopts_guard_builder_meta_by_id', 5, 4);

// Reject a REST write before the posts controller creates or updates the post.
add_filter('rest_dispatch_request', function ($response, $request, $route, $handler) {
    $callback = $handler['callback'] ?? null;
    $meta = $request->get_param('meta');
    if ($response !== null || !is_array($meta) || !is_array($callback)
        || !(($callback[0] ?? null) instanceof WP_REST_Posts_Controller)
        || !in_array($callback[1] ?? null, array('create_item', 'update_item'), true)
        || current_user_can('manage_options')) {
        return $response;
    }

    $post_id = $callback[1] === 'update_item' ? (int) $request['id'] : 0;
    $fields = $callback[0]->get_item_schema()['properties']['meta']['properties'] ?? array();
    foreach ($meta as $key => $layout) {
        if (isset($fields[$key]) && widgetopts_is_builder_layout_meta($key)
            && (!$post_id || current_user_can('edit_post_meta', $post_id, $key))
            && widgetopts_guard_builder_meta(null, $post_id, $key, $layout) === false) {
            return new WP_Error('widgetopts_inline_logic_forbidden',
                __('Only administrators can change inline Display Logic.', 'widget-options'), array('status' => 403));
        }
    }
    return $response;
}, 10, 4);

function widgetopts_is_builder_layout_meta($key) {
    return is_string($key) && in_array(strtolower(rtrim($key)), array('_elementor_data', '_fl_builder_data', '_fl_builder_draft', 'panels_data'), true);
}

function widgetopts_guard_builder_meta_by_id($check, $meta_id, $value, $key) {
    if ($check !== null || current_user_can('manage_options') || ($key !== false && !widgetopts_is_builder_layout_meta($key))) {
        return $check;
    }
    $row = get_metadata_by_mid('post', $meta_id);
    if (!$row) {
        return $check;
    }
    return widgetopts_guard_builder_meta($check, $row->post_id, $key === false ? $row->meta_key : $key, $value);
}

function widgetopts_guard_builder_meta($check, $post_id, $key, $value) {
    if ($check !== null || !widgetopts_is_builder_layout_meta($key) || current_user_can('manage_options')) {
        return $check;
    }

    $key = strtolower(rtrim($key));
    $incoming = widgetopts_builder_rules_by_node($key, $value);
    if ($incoming === null) {
        return false;
    }
    if (!$incoming) {
        return $check;
    }
    $old_layout = $post_id ? get_post_meta($post_id, $key, true) : '';
    if ($post_id && $old_layout === '' && wp_is_post_revision($post_id)) {
        $old_layout = get_post_meta(wp_is_post_revision($post_id), $key, true);
    }
    if ($post_id && ($old_layout === '' || $old_layout === array()) && in_array($key, array('_fl_builder_data', '_fl_builder_draft'), true)) {
        // Beaver clears the destination to an empty array before copying the other layout.
        $other = $key === '_fl_builder_data' ? '_fl_builder_draft' : '_fl_builder_data';
        $old_layout = get_post_meta($post_id, $other, true);
    }
    if ($key === 'panels_data') {
        $value = is_string($value) ? json_decode($value, true) : $value;
        $old_layout = is_string($old_layout) ? json_decode($old_layout, true) : $old_layout;
        if (widgetopts_builder_rules_by_node($key, $old_layout) === null) {
            $old_layout = array();
        }
        widgetopts_match_siteorigin_widgets($value, $old_layout);
        $incoming = widgetopts_builder_rules_by_node($key, $value);
        if ($incoming === null) {
            return false;
        }
    }
    $stored = widgetopts_builder_rules_by_node($key, $old_layout);
    foreach ($incoming as $node => $rules) {
        if ($rules !== ($stored[$node] ?? array())) {
            return false;
        }
    }
    return $check;
}

// Older SiteOrigin layouts have no widget_id. Match unchanged widgets first,
// then use their previous position for ordinary text edits. Each stored widget
// may authorize at most one incoming widget, including in nested layouts.
function widgetopts_match_siteorigin_widgets(&$layout, &$stored) {
    if (empty($layout['widgets']) || empty($stored['widgets'])) {
        return;
    }
    $by_id = array();
    $by_content = array();
    $fingerprint = static function ($widget) {
        $class = $widget['panels_info']['class'] ?? '';
        unset($widget['panels_info'], $widget['extended_widget_opts']['class']['logic']);
        if (empty($widget['extended_widget_opts']['class'])) {
            unset($widget['extended_widget_opts']['class']);
        }
        if (empty($widget['extended_widget_opts'])) {
            unset($widget['extended_widget_opts']);
        }
        return hash('sha256', serialize(array($class, $widget)));
    };
    // Pop identical anonymous widgets in their original order.
    foreach (array_reverse($stored['widgets'], true) as $index => $widget) {
        $id = $widget['panels_info']['widget_id'] ?? '';
        if ($id === '') {
            $by_content[$fingerprint($widget)][] = $index;
        } else {
            $by_id[serialize($id)] = $index;
        }
    }

    $matches = array();
    $used = array();
    foreach ($layout['widgets'] as $index => $widget) {
        $id = $widget['panels_info']['widget_id'] ?? '';
        $old_index = $by_id[serialize($id)] ?? null;
        if ($old_index === null) {
            $hash = $fingerprint($widget);
            if (!empty($by_content[$hash])) {
                $old_index = array_pop($by_content[$hash]);
            }
        }
        if ($old_index !== null) {
            $matches[$index] = $old_index;
            $used[$old_index] = true;
        }
    }
    foreach ($layout['widgets'] as $index => &$widget) {
        $old_index = $matches[$index] ?? null;
        if ($old_index === null && !isset($used[$index]) && isset($stored['widgets'][$index])
            && ($stored['widgets'][$index]['panels_info']['widget_id'] ?? '') === ''
            && ($stored['widgets'][$index]['panels_info']['class'] ?? '') === ($widget['panels_info']['class'] ?? '')) {
            $old_index = $index;
        }
        // Comparison-only identifiers keep unmatched widgets distinct from stored ones.
        $widget['panels_info']['widget_id'] = $old_index === null ? 'new-' . $index : 'old-' . $old_index;
        if ($old_index !== null && !empty($widget['panels_data'])) {
            $nested = $widget['panels_data'];
            $old = $stored['widgets'][$old_index]['panels_data'] ?? array();
            $nested = is_string($nested) ? json_decode($nested, true) : $nested;
            $old = is_string($old) ? json_decode($old, true) : $old;
            widgetopts_match_siteorigin_widgets($nested, $old);
            $widget['panels_data'] = $nested;
            $stored['widgets'][$old_index]['panels_data'] = $old;
        }
    }
    foreach ($stored['widgets'] as $index => &$old_widget) {
        $old_widget['panels_info']['widget_id'] = 'old-' . $index;
    }
}

// An empty entry represents a retained widget with no rule. An absent widget
// may be deleted. Node identity allows unrelated edits and widget reordering.
function widgetopts_builder_rules_by_node($key, $layout) {
    if ($layout === '' || $layout === null || $layout === false) {
        return array();
    }
    if (in_array($key, array('_elementor_data', 'panels_data'), true) && is_string($layout)) {
        $layout = json_decode($layout, true);
    }
    if (!is_array($layout)) {
        return null;
    }

    $result = array();
    if ($key === '_elementor_data') {
        $groups = array($layout);
        while ($groups) {
            foreach (array_pop($groups) as $element) {
                if (!is_array($element)) {
                    return null;
                }
                $id = $element['id'] ?? null;
                $logic = $element['settings']['widgetopts_logic'] ?? '';
                if (!is_scalar($id) || (string) $id === '' || isset($result[$id])) {
                    // Ambiguous node identities cannot carry executable conditions.
                    if ($logic !== '' || (is_scalar($id) && !empty($result[$id]))) {
                        return null;
                    }
                } else {
                    $result[$id] = $logic === '' ? array() : array($logic);
                }
                if (!empty($element['elements']) && is_array($element['elements'])) {
                    $groups[] = $element['elements'];
                }
            }
        }
    } elseif ($key === 'panels_data') {
        $widgets = $layout['widgets'] ?? array();
        if (!is_array($widgets)) {
            return null;
        }
        foreach ($widgets as $index => $widget) {
            if (!is_array($widget)) {
                return null;
            }
            $id = $widget['panels_info']['widget_id'] ?? '';
            $path = $id === '' ? serialize(array('index', $index)) : serialize(array('id', $id));
            if (!is_scalar($id) || isset($result[$path])) {
                return null;
            }
            $logic = $widget['extended_widget_opts']['class']['logic'] ?? '';
            $result[$path] = $logic === '' ? array() : array($logic);
            if (!empty($widget['panels_data'])) {
                $nested = widgetopts_builder_rules_by_node($key, $widget['panels_data']);
                if ($nested === null) {
                    return null;
                }
                foreach ($nested as $child => $rules) {
                    $result[serialize(array($path, $child))] = $rules;
                }
            }
        }
    } else {
        foreach ($layout as $id => $node) {
            $node = (array) $node;
            $settings = (array) ($node['settings'] ?? array());
            $rules = array();
            foreach (array('widgetopts_settings_logic', 'widgetopts_settings_logic_backup') as $field) {
                if (isset($settings[$field]) && $settings[$field] !== '') {
                    $rules[$field] = $settings[$field];
                }
            }
            $result[$id] = $rules;
        }
    }
    return $result;
}
