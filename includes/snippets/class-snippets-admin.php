<?php
/**
 * Display Logic Snippets - Admin Interface
 *
 * Provides admin pages for managing snippets and handles admin notices
 * for migration status.
 *
 * @copyright   Copyright (c) 2024, Widget Options Team
 * @since       5.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Class WidgetOpts_Snippets_Admin
 * 
 * Handles admin interface for snippets management
 */
class WidgetOpts_Snippets_Admin {

    /**
     * Hook suffix for the migration page
     */
    private static $migration_hook = '';

    /**
     * Initialize the admin interface
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'), 11);
        add_action('admin_notices', array(__CLASS__, 'migration_notice'));
        add_action('admin_notices', array(__CLASS__, 'legacy_migration_required_notice'));
        add_action('admin_notices', array(__CLASS__, 'snippet_validation_notice'));
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post_' . WidgetOpts_Snippets_CPT::POST_TYPE, array(__CLASS__, 'save_snippet_meta'), 10, 2);
        add_filter('manage_' . WidgetOpts_Snippets_CPT::POST_TYPE . '_posts_columns', array(__CLASS__, 'add_columns'));
        add_action('manage_' . WidgetOpts_Snippets_CPT::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'column_content'), 10, 2);
        
        // AJAX for manual migration
        add_action('wp_ajax_widgetopts_run_migration', array(__CLASS__, 'ajax_run_migration'));
        add_action('wp_ajax_widgetopts_dismiss_migration_notice', array(__CLASS__, 'ajax_dismiss_migration_notice'));

        // AJAX for migration page
        add_action('wp_ajax_widgetopts_migration_scan', array(__CLASS__, 'ajax_migration_scan'));
        add_action('wp_ajax_widgetopts_migration_migrate', array(__CLASS__, 'ajax_migration_migrate'));
        add_action('wp_ajax_widgetopts_migration_delete', array(__CLASS__, 'ajax_migration_delete'));

        // Enqueue migration page assets
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_migration_assets'));

        // Redirect to migration page after plugin update if legacy code exists
        add_action('admin_init', array(__CLASS__, 'maybe_redirect_to_migration'));
    }

    /**
     * Add admin menu - only if Display Logic is enabled
     */
    public static function add_admin_menu() {
        global $widget_options;
        
        // Only show menu if Display Logic is enabled
        if (!isset($widget_options['logic']) || $widget_options['logic'] !== 'activate') {
            return;
        }
        
        // Add as submenu right after Widget Options (priority 11, Widget Options uses 10)
        add_submenu_page(
            'options-general.php',
            __('Widget Options Snippets', 'widget-options'),
            __('Widget Options Snippets', 'widget-options'),
            'manage_options',
            'edit.php?post_type=' . WidgetOpts_Snippets_CPT::POST_TYPE
        );

        // Migration page (hidden from menu, accessible via direct link)
        self::$migration_hook = add_submenu_page(
            null,
            __('Display Logic Migration', 'widget-options'),
            __('Display Logic Migration', 'widget-options'),
            WIDGETOPTS_MIGRATION_PERMISSIONS,
            'widgetopts_migration',
            array(__CLASS__, 'render_migration_page')
        );
    }

    /**
     * Render the migration page
     */
    public static function render_migration_page() {
        if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'widget-options'));
        }
        require_once WIDGETOPTS_PLUGIN_DIR . 'includes/admin/settings/migration-page.php';
    }

    /**
     * Enqueue migration page scripts/styles
     */
    public static function enqueue_migration_assets($hook) {
        if (!self::$migration_hook || $hook !== self::$migration_hook) {
            return;
        }
        wp_enqueue_script(
            'widgetopts-migration',
            WIDGETOPTS_PLUGIN_URL . 'assets/js/widgetopts.migration.js',
            array('jquery'),
            WIDGETOPTS_VERSION,
            true
        );
        wp_localize_script('widgetopts-migration', 'widgetoptsMigration', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('widgetopts_migration_page'),
            'i18n'    => array(
                'scanning'        => __('Scanning...', 'widget-options'),
                'migrating'       => __('Migrating...', 'widget-options'),
                'deleting'        => __('Deleting...', 'widget-options'),
                'noItems'         => __('No legacy display logic found. All widgets are using the new snippet-based system.', 'widget-options'),
                'confirmDelete'   => __('Are you sure you want to delete this legacy code? Display logic will no longer execute for the affected widgets.', 'widget-options'),
                'confirmMigrate'  => __('Migrate the selected snippets?', 'widget-options'),
                'selectAtLeast'   => __('Please select at least one snippet to migrate.', 'widget-options'),
                'migrationDone'   => __('Migration completed!', 'widget-options'),
                'error'           => __('An error occurred. Please try again.', 'widget-options'),
            ),
        ));
    }

    /**
     * AJAX: Scan all editors for legacy logic codes with locations
     */
    public static function ajax_migration_scan() {
        check_ajax_referer('widgetopts_migration_page', 'nonce');
        if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) {
            wp_send_json_error(array('message' => __('Permission denied.', 'widget-options')));
        }

        require_once WIDGETOPTS_PLUGIN_DIR . 'includes/snippets/class-snippets-migration.php';
        $items = WidgetOpts_Snippets_Migration::collect_all_logic_codes_with_locations();

        // Add hash and suggested title
        $index = 1;
        foreach ($items as &$item) {
            $item['hash']  = md5($item['code']);
            $item['title'] = sprintf(__('Migrated Snippet #%d', 'widget-options'), $index);
            $index++;
        }

        wp_send_json_success(array('items' => $items));
    }

    /**
     * AJAX: Migrate selected snippets
     */
    public static function ajax_migration_migrate() {
        check_ajax_referer('widgetopts_migration_page', 'nonce');
        if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) {
            wp_send_json_error(array('message' => __('Permission denied.', 'widget-options')));
        }

        $raw = isset($_POST['items']) ? $_POST['items'] : array();
        if (empty($raw) || !is_array($raw)) {
            wp_send_json_error(array('message' => __('No items provided.', 'widget-options')));
        }

        // Sanitize
        $items = array();
        foreach ($raw as $entry) {
            $items[] = array(
                'hash'  => sanitize_text_field($entry['hash']),
                'title' => sanitize_text_field($entry['title']),
            );
        }

        require_once WIDGETOPTS_PLUGIN_DIR . 'includes/snippets/class-snippets-migration.php';
        $results = WidgetOpts_Snippets_Migration::migrate_selected($items);

        wp_send_json_success(array('results' => $results));
    }

    /**
     * AJAX: Delete a legacy code entry by hash
     */
    public static function ajax_migration_delete() {
        check_ajax_referer('widgetopts_migration_page', 'nonce');
        if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) {
            wp_send_json_error(array('message' => __('Permission denied.', 'widget-options')));
        }

        $hash = isset($_POST['hash']) ? sanitize_text_field($_POST['hash']) : '';
        if (empty($hash)) {
            wp_send_json_error(array('message' => __('No hash provided.', 'widget-options')));
        }

        require_once WIDGETOPTS_PLUGIN_DIR . 'includes/snippets/class-snippets-migration.php';
        $results = WidgetOpts_Snippets_Migration::delete_legacy_code($hash);

        wp_send_json_success(array('results' => $results));
    }

    /**
     * Display notice when legacy display logic migration is required
     */
    public static function legacy_migration_required_notice() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'settings_page_widgetopts_migration') {
            return;
        }

        if (!get_option('wopts_display_logic_migration_required', false)) {
            return;
        }

        if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) {
            return;
        }

        $migration_url = admin_url('options-general.php?page=widgetopts_migration');
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('Widget Options - Display Logic Migration Required', 'widget-options'); ?></strong>
            </p>
            <p>
                <?php _e('Some widgets still use legacy inline display logic code that needs to be migrated to the new snippet-based system.', 'widget-options'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url($migration_url); ?>" class="button button-primary">
                    <?php _e('Go to Migration Page', 'widget-options'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Redirect to migration page on first admin load after plugin update,
     * if legacy display logic code exists and needs migration.
     */
    public static function maybe_redirect_to_migration() {
        if (!defined('WIDGETOPTS_VERSION')) return;

        $stored_version = get_option('widgetopts_plugin_version', '');
        if ($stored_version === WIDGETOPTS_VERSION) return;

        // Version changed — update stored version
        update_option('widgetopts_plugin_version', WIDGETOPTS_VERSION);

        // Only redirect on UPDATE (not first install)
        if ($stored_version === '') return;

        // Trigger a scan to set the migration flag if needed
        if (class_exists('WidgetOpts_Snippets_CPT')) {
            WidgetOpts_Snippets_CPT::scan_for_legacy_logic();
        }

        if (!get_option('wopts_display_logic_migration_required', false)) return;
        if (!current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) return;

        // Don't redirect during AJAX, CLI, or if already on migration page
        if (wp_doing_ajax() || (defined('WP_CLI') && WP_CLI)) return;
        if (isset($_GET['page']) && $_GET['page'] === 'widgetopts_migration') return;

        // Set transient and redirect
        set_transient('widgetopts_migration_redirect', true, 60);
        wp_safe_redirect(admin_url('options-general.php?page=widgetopts_migration'));
        exit;
    }

    /**
     * Display migration notice if needed
     */
    public static function migration_notice() {
        $migration_status = get_option(WidgetOpts_Snippets_Migration::MIGRATION_OPTION, array());

        // Show notice for failed migration
        if (isset($migration_status['status']) && $migration_status['status'] === 'failed') {
            $dismissed = get_option('widgetopts_migration_notice_dismissed', false);
            if ($dismissed) {
                return;
            }
            ?>
            <div class="notice notice-error is-dismissible" id="widgetopts-migration-failed-notice">
                <p>
                    <strong><?php _e('Widget Options - Migration Failed', 'widget-options'); ?></strong>
                </p>
                <p>
                    <?php _e('The automatic migration of display logic to snippets has failed. Please try running the migration manually.', 'widget-options'); ?>
                </p>
                <?php if (isset($migration_status['error'])): ?>
                    <p><code><?php echo esc_html($migration_status['error']); ?></code></p>
                <?php endif; ?>
                <p>
                    <button type="button" class="button button-primary" id="widgetopts-run-migration">
                        <?php _e('Run Migration Manually', 'widget-options'); ?>
                    </button>
                    <button type="button" class="button" id="widgetopts-dismiss-migration">
                        <?php _e('Dismiss', 'widget-options'); ?>
                    </button>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $('#widgetopts-run-migration').on('click', function() {
                    var $btn = $(this);
                    $btn.prop('disabled', true).text('<?php _e('Running...', 'widget-options'); ?>');
                    
                    $.post(ajaxurl, {
                        action: 'widgetopts_run_migration',
                        nonce: '<?php echo wp_create_nonce('widgetopts_migration'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || '<?php _e('Migration failed. Please try again.', 'widget-options'); ?>');
                            $btn.prop('disabled', false).text('<?php _e('Run Migration Manually', 'widget-options'); ?>');
                        }
                    });
                });
                
                $('#widgetopts-dismiss-migration').on('click', function() {
                    $.post(ajaxurl, {
                        action: 'widgetopts_dismiss_migration_notice',
                        nonce: '<?php echo wp_create_nonce('widgetopts_migration'); ?>'
                    });
                    $('#widgetopts-migration-failed-notice').fadeOut();
                });
            });
            </script>
            <?php
        }

        // Show success notice after migration
        if (isset($migration_status['status']) && $migration_status['status'] === 'completed' && isset($migration_status['results'])) {
            $show_success = get_transient('widgetopts_migration_success_notice');
            if ($show_success) {
                delete_transient('widgetopts_migration_success_notice');
                $results = $migration_status['results'];
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php _e('Widget Options - Migration Completed', 'widget-options'); ?></strong>
                    </p>
                    <p>
                        <?php 
                        printf(
                            __('Successfully migrated display logic to snippets. Created %d snippets and updated %d widgets.', 'widget-options'),
                            $results['snippets_created'],
                            $results['widgets_updated']
                        ); 
                        ?>
                    </p>
                    <?php if (!empty($results['errors'])): ?>
                        <p><?php _e('Some errors occurred:', 'widget-options'); ?></p>
                        <ul>
                            <?php foreach ($results['errors'] as $error): ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php
            }
        }
    }

    /**
     * AJAX handler for manual migration
     */
    public static function ajax_run_migration() {
        check_ajax_referer('widgetopts_migration', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'widget-options')));
        }

        // Reset migration status to allow re-run
        WidgetOpts_Snippets_Migration::reset_migration();

        // Run migration
        $result = WidgetOpts_Snippets_Migration::migrate();

        if ($result) {
            set_transient('widgetopts_migration_success_notice', true, 60);
            wp_send_json_success();
        } else {
            $status = WidgetOpts_Snippets_Migration::get_migration_status();
            wp_send_json_error(array(
                'message' => isset($status['error']) ? $status['error'] : __('Unknown error occurred.', 'widget-options')
            ));
        }
    }

    /**
     * AJAX handler to dismiss migration notice
     */
    public static function ajax_dismiss_migration_notice() {
        check_ajax_referer('widgetopts_migration', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }

        update_option('widgetopts_migration_notice_dismissed', true);
        wp_send_json_success();
    }

    /**
     * Add meta boxes for snippet editing
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'widgetopts_snippet_code',
            __('Snippet Code', 'widget-options'),
            array(__CLASS__, 'render_code_meta_box'),
            WidgetOpts_Snippets_CPT::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'widgetopts_snippet_description',
            __('Description', 'widget-options'),
            array(__CLASS__, 'render_description_meta_box'),
            WidgetOpts_Snippets_CPT::POST_TYPE,
            'normal',
            'default'
        );

        add_meta_box(
            'widgetopts_snippet_help',
            __('Help & Examples', 'widget-options'),
            array(__CLASS__, 'render_help_meta_box'),
            WidgetOpts_Snippets_CPT::POST_TYPE,
            'side',
            'default'
        );

        // Remove default editor
        remove_post_type_support(WidgetOpts_Snippets_CPT::POST_TYPE, 'editor');
    }

    /**
     * Render code meta box
     */
    public static function render_code_meta_box($post) {
        wp_nonce_field('widgetopts_snippet_save', 'widgetopts_snippet_nonce');
        
        $code = $post->post_content;
        ?>
        
        <div style="position: relative;">
            <textarea name="widgetopts_snippet_code" id="widgetopts_snippet_code" class="large-text code" rows="20" style="font-family: 'Courier New', Consolas, Monaco, monospace; font-size: 14px; line-height: 1.6; padding: 15px; background: #f9f9f9; border: 1px solid #dcdcde; border-radius: 4px; color: #1d2327; tab-size: 4;"><?php echo esc_textarea($code); ?></textarea>
        </div>
        <div style="background: #f0f0f1; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
            <p class="description" style="margin: 0; color: #1d2327;">
                <strong style="color: #1d2327;"><?php _e('Instructions:', 'widget-options'); ?></strong><br>
                <?php _e('Enter PHP code that returns true (show widget) or false (hide widget). You can use WordPress Conditional Tags.', 'widget-options'); ?><br> 
                <?php _e('Do NOT use PHP tags', 'widget-options'); ?> <code style="background: #fff; padding: 2px 6px; border-radius: 3px; color: #d63638;">&lt;?php ?&gt;</code> — <?php _e('just write the code directly.', 'widget-options'); ?><br>
                <?php _e('Example:', 'widget-options'); ?> <code style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px;">return is_user_logged_in();</code>
            </p>
            <p class="description" style="margin: 10px 0 0; padding: 10px; background: #fbfcf0; border-left: 4px solid #af991d; border-radius: 2px; color: #1d2327;">
                <strong style="color: #af991d;"><?php _e('Security Warning:', 'widget-options'); ?></strong><br>
                <?php _e('Code is executed via eval(). Only administrators can create/edit snippets. The code is validated against a whitelist of allowed functions.', 'widget-options'); ?>
            </p>
        </div>
        
        <style>
            #widgetopts_snippet_code:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
                outline: 2px solid transparent;
            }
            #widgetopts_snippet_code::selection {
                background: #b4d7ff;
            }
        </style>
        <?php
    }

    /**
     * Render description meta box
     */
    public static function render_description_meta_box($post) {
        $description = get_post_meta($post->ID, '_widgetopts_snippet_description', true);
        ?>
        <p class="description">
            <?php _e('Brief description of what this snippet does. This will be shown to users when selecting snippets.', 'widget-options'); ?>
        </p>
        <textarea name="widgetopts_snippet_description" id="widgetopts_snippet_description" class="large-text" rows="3"><?php echo esc_textarea($description); ?></textarea>
        <?php
    }

    /**
     * Render help meta box
     */
    public static function render_help_meta_box($post) {
        ?>
        <p><strong><?php _e('Common Conditional Tags:', 'widget-options'); ?></strong></p>
        <ul style="font-size: 12px;">
            <li><code>is_user_logged_in()</code> - <?php _e('User is logged in', 'widget-options'); ?></li>
            <li><code>is_front_page()</code> - <?php _e('Front page', 'widget-options'); ?></li>
            <li><code>is_home()</code> - <?php _e('Blog page', 'widget-options'); ?></li>
            <li><code>is_single()</code> - <?php _e('Single post', 'widget-options'); ?></li>
            <li><code>is_page()</code> - <?php _e('Static page', 'widget-options'); ?></li>
            <li><code>is_archive()</code> - <?php _e('Archive page', 'widget-options'); ?></li>
            <li><code>is_search()</code> - <?php _e('Search results', 'widget-options'); ?></li>
            <li><code>is_404()</code> - <?php _e('404 page', 'widget-options'); ?></li>
        </ul>
        
        <p><strong><?php _e('Examples:', 'widget-options'); ?></strong></p>
        <p><code>return is_user_logged_in();</code></p>
        <p><code>return is_page(array(5, 10));</code></p>
        <p><code>return is_single() && has_tag('featured');</code></p>
        
        <p>
            <a href="https://developer.wordpress.org/themes/basics/conditional-tags/" target="_blank">
                <?php _e('View all Conditional Tags →', 'widget-options'); ?>
            </a>
        </p>
        <?php
    }

    /**
     * Validate snippet code for syntax and security errors
     * 
     * @param string $code The PHP code to validate
     * @return array Array with 'valid' (bool) and 'errors' (array of error messages)
     */
    public static function validate_snippet_code($code) {
        $errors = array();
        
        // Skip validation for empty code
        if (empty(trim($code))) {
            return array('valid' => true, 'errors' => array());
        }
        
        // 1. Check for PHP syntax errors using php -l
        $syntax_error = self::check_php_syntax($code);
        if ($syntax_error) {
            $errors[] = array(
                'type' => 'syntax',
                'message' => $syntax_error
            );
        }
        
        // 2. Security validation using existing system
        if (function_exists('widgetopts_validate_expression')) {
            $validation = widgetopts_validate_expression($code);
            if ($validation['valid'] === false) {
                $errors[] = array(
                    'type' => 'security',
                    'message' => $validation['message']
                );
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
    
    /**
     * Check PHP syntax without executing the code
     * 
     * Uses token_get_all() to parse the code and catch syntax errors.
     * 
     * @param string $code The PHP code to check
     * @return string|false Error message or false if valid
     */
    public static function check_php_syntax($code) {
        // Mirror the same transformation that widgetopts_safe_eval() applies at runtime:
        // if code has no "return", it gets wrapped as "return (code);"
        if (stristr($code, "return") === false) {
            $code = "return (" . $code . ");";
        }

        // Wrap code in PHP tags for tokenization
        $wrapped_code = '<?php ' . $code;
        
        // Use token_get_all to check syntax - it throws ParseError on invalid syntax
        try {
            $previous_error_reporting = error_reporting(0);
            @token_get_all($wrapped_code, TOKEN_PARSE);
            error_reporting($previous_error_reporting);
            return false; // No syntax errors
        } catch (\ParseError $e) {
            error_reporting($previous_error_reporting);
            // Line numbers are correct because <?php is added on the same line as the first line of code
            $line = $e->getLine();
            $message = $e->getMessage();
            
            // Make error message more user-friendly
            return sprintf(
                __('Syntax error on line %d: %s', 'widget-options'),
                $line,
                $message
            );
        } catch (\Throwable $e) {
            error_reporting($previous_error_reporting);
            return sprintf(
                __('Code error: %s', 'widget-options'),
                $e->getMessage()
            );
        }
    }

    /**
     * Save snippet meta
     */
    public static function save_snippet_meta($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['widgetopts_snippet_nonce']) || !wp_verify_nonce($_POST['widgetopts_snippet_nonce'], 'widgetopts_snippet_save')) {
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Save code to post_content
        if (isset($_POST['widgetopts_snippet_code'])) {
            $code = wp_unslash($_POST['widgetopts_snippet_code']);
            
            // Validate code
            $validation = self::validate_snippet_code($code);
            
            $update_data = array(
                'ID'           => $post_id,
                'post_content' => $code,
            );
            
            // If validation failed, set status to draft
            if (!$validation['valid']) {
                $update_data['post_status'] = 'draft';
                
                // Store validation errors for display
                set_transient('widgetopts_snippet_errors_' . $post_id, $validation['errors'], 120);
            } else {
                // Clear any previous errors
                delete_transient('widgetopts_snippet_errors_' . $post_id);
            }

            // Update post content directly
            remove_action('save_post_' . WidgetOpts_Snippets_CPT::POST_TYPE, array(__CLASS__, 'save_snippet_meta'), 10);
            wp_update_post($update_data);
            add_action('save_post_' . WidgetOpts_Snippets_CPT::POST_TYPE, array(__CLASS__, 'save_snippet_meta'), 10, 2);
        }

        // Save description
        if (isset($_POST['widgetopts_snippet_description'])) {
            update_post_meta($post_id, '_widgetopts_snippet_description', sanitize_textarea_field($_POST['widgetopts_snippet_description']));
        }
    }
    
    /**
     * Display validation error notice on snippet edit page
     */
    public static function snippet_validation_notice() {
        global $post;
        
        if (!$post || $post->post_type !== WidgetOpts_Snippets_CPT::POST_TYPE) {
            return;
        }
        
        $errors = get_transient('widgetopts_snippet_errors_' . $post->ID);
        
        if (empty($errors)) {
            return;
        }
        
        // Don't delete transient here - keep showing until code is fixed
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Snippet Validation Failed', 'widget-options'); ?></strong></p>
            <p><?php _e('The snippet contains errors and has been saved as Draft. Please fix the following issues:', 'widget-options'); ?></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li>
                        <strong>
                            <?php 
                            if ($error['type'] === 'syntax') {
                                _e('Syntax Error:', 'widget-options');
                            } else {
                                _e('Security Error:', 'widget-options');
                            }
                            ?>
                        </strong>
                        <code style="display: block; margin-top: 5px; padding: 10px; background: #f6f6f6; white-space: pre-wrap; word-break: break-all;"><?php echo esc_html($error['message']); ?></code>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><?php _e('Fix the errors above and save again to publish the snippet.', 'widget-options'); ?></p>
        </div>
        <?php
    }

    /**
     * Add custom columns to snippets list
     */
    public static function add_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['description'] = __('Description', 'widget-options');
                $new_columns['code_preview'] = __('Code Preview', 'widget-options');
            }
        }
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public static function column_content($column, $post_id) {
        switch ($column) {
            case 'description':
                $description = get_post_meta($post_id, '_widgetopts_snippet_description', true);
                echo esc_html($description ?: '—');
                break;

            case 'code_preview':
                $post = get_post($post_id);
                $code = $post->post_content;
                $preview = strlen($code) > 80 ? substr($code, 0, 80) . '...' : $code;
                echo '<code style="font-size: 11px;">' . esc_html($preview) . '</code>';
                break;
        }
    }
}

// Initialize
WidgetOpts_Snippets_Admin::init();
