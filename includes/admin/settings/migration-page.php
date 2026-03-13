<?php
/**
 * Display Logic Migration Page
 *
 * Provides a UI for reviewing and batch-migrating legacy display logic
 * snippets to the new snippet-based system.
 *
 * @copyright   Copyright (c) 2024, Widget Options Team
 * @since       5.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;
?>
<div class="wrap" id="widgetopts-migration-wrap">
    <h1><?php esc_html_e('Display Logic Migration', 'widget-options'); ?></h1>

    <div id="widgetopts-migration-notices"></div>

    <div id="widgetopts-migration-loading" style="padding: 20px; text-align: center;">
        <span class="spinner is-active" style="float: none;"></span>
        <span><?php esc_html_e('Scanning website for legacy display logic...', 'widget-options'); ?></span>
    </div>

    <div id="widgetopts-migration-empty" style="display:none; padding: 20px; background: #f0f6fc; border-left: 4px solid #72aee6;">
        <p><strong><?php esc_html_e('No legacy display logic found.', 'widget-options'); ?></strong></p>
        <p><?php esc_html_e('All widgets are using the new snippet-based system.', 'widget-options'); ?></p>
    </div>

    <div id="widgetopts-migration-content" style="display:none;">
        <div style="margin-bottom: 15px; display: flex; gap: 8px; align-items: center;">
            <button type="button" class="button button-primary" id="widgetopts-migrate-all">
                <?php esc_html_e('Migrate All', 'widget-options'); ?>
            </button>
            <button type="button" class="button" id="widgetopts-migrate-selected">
                <?php esc_html_e('Migrate Selected', 'widget-options'); ?>
            </button>
            <label style="margin-left: 10px;">
                <input type="checkbox" id="widgetopts-select-all" />
                <?php esc_html_e('Select All', 'widget-options'); ?>
            </label>
        </div>

        <table class="wp-list-table widefat fixed striped" id="widgetopts-migration-table">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column" style="width: 40px;">
                        <span class="screen-reader-text"><?php esc_html_e('Select', 'widget-options'); ?></span>
                    </td>
                    <th class="manage-column" style="width: 35%;"><?php esc_html_e('Code Snippet', 'widget-options'); ?></th>
                    <th class="manage-column" style="width: 20%;"><?php esc_html_e('Name', 'widget-options'); ?></th>
                    <th class="manage-column" style="width: 35%;"><?php esc_html_e('Locations', 'widget-options'); ?></th>
                    <th class="manage-column" style="width: 10%;"><?php esc_html_e('Actions', 'widget-options'); ?></th>
                </tr>
            </thead>
            <tbody id="widgetopts-migration-tbody">
            </tbody>
        </table>
    </div>
</div>

<style>
    #widgetopts-migration-table .widgetopts-code-preview {
        font-family: 'Courier New', Consolas, Monaco, monospace;
        font-size: 12px;
        line-height: 1.4;
        background: #f9f2f4;
        color: #c7254e;
        padding: 8px 10px;
        border-radius: 3px;
        max-height: 120px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
        display: block;
    }
    #widgetopts-migration-table .widgetopts-name-input {
        width: 100%;
        padding: 4px 8px;
    }
    #widgetopts-migration-table .widgetopts-locations-list {
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 12px;
    }
    #widgetopts-migration-table .widgetopts-locations-list li {
        padding: 2px 0;
        color: #50575e;
    }
    #widgetopts-migration-table .widgetopts-locations-list .widgetopts-loc-type {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-right: 4px;
        color: #fff;
    }
    .widgetopts-loc-type-classic_widget { background: #0073aa; }
    .widgetopts-loc-type-gutenberg { background: #1e1e1e; }
    .widgetopts-loc-type-elementor { background: #92003B; }
    .widgetopts-loc-type-beaver { background: #6bc04b; }
    .widgetopts-loc-type-siteorigin { background: #2ea2cc; }
    .widgetopts-loc-count {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        color: #787c82;
    }
    #widgetopts-migration-table .widgetopts-locations-list {
        max-height: 150px;
        overflow-y: auto;
    }
    #widgetopts-migration-table .button-delete {
        color: #b32d2e;
        border-color: #b32d2e;
    }
    #widgetopts-migration-table .button-delete:hover {
        background: #b32d2e;
        color: #fff;
    }
    .widgetopts-migration-notice {
        padding: 10px 15px;
        margin: 10px 0;
        border-left: 4px solid;
        background: #fff;
    }
    .widgetopts-migration-notice.success {
        border-color: #00a32a;
        background: #f0f6e8;
    }
    .widgetopts-migration-notice.error {
        border-color: #d63638;
        background: #fcf0f1;
    }
    .widgetopts-row-disabled {
        opacity: 0.5;
        pointer-events: none;
    }
</style>
