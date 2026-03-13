/**
 * Widget Options - Migration Page JS
 *
 * Handles scanning, rendering, migrating and deleting legacy display logic snippets.
 */
(function ($) {
    'use strict';

    var config = window.widgetoptsMigration || {};
    var items = [];

    /**
     * Escape HTML to prevent XSS
     */
    function esc(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /**
     * Show a notice on the migration page
     */
    function showNotice(message, type) {
        var cls = 'widgetopts-migration-notice ' + (type || 'success');
        $('#widgetopts-migration-notices').append(
            '<div class="' + cls + '"><p>' + esc(message) + '</p></div>'
        );
    }

    /**
     * Type label mapping
     */
    function typeLabel(type) {
        var map = {
            classic_widget: 'Widget',
            gutenberg: 'Block Editor',
            elementor: 'Elementor',
            beaver: 'Beaver',
            siteorigin: 'SiteOrigin'
        };
        return map[type] || type;
    }

    /**
     * Build a single table row for a scanned item
     */
    function buildRow(item, idx) {
        var locations = '';
        if (item.locations && item.locations.length) {
            // Deduplicate locations: group by type+label, show count if > 1
            var seen = {};
            var unique = [];
            $.each(item.locations, function (_, loc) {
                var key = loc.type + '||' + loc.label;
                if (seen[key]) {
                    seen[key].count++;
                } else {
                    seen[key] = { type: loc.type, label: loc.label, count: 1 };
                    unique.push(seen[key]);
                }
            });

            locations = '<ul class="widgetopts-locations-list">';
            $.each(unique, function (_, loc) {
                locations += '<li>'
                    + '<span class="widgetopts-loc-type widgetopts-loc-type-' + esc(loc.type) + '">'
                    + esc(typeLabel(loc.type))
                    + '</span> '
                    + esc(loc.label)
                    + (loc.count > 1 ? ' <span class="widgetopts-loc-count" style="display: none;">&times;' + loc.count + '</span>' : '')
                    + '</li>';
            });
            locations += '</ul>';
        }

        return '<tr data-hash="' + esc(item.hash) + '" data-idx="' + idx + '">'
            + '<th scope="row" class="check-column"><input type="checkbox" class="widgetopts-row-cb" /></th>'
            + '<td><code class="widgetopts-code-preview">' + esc(item.code) + '</code></td>'
            + '<td><input type="text" class="widgetopts-name-input" value="' + esc(item.title) + '" /></td>'
            + '<td>' + locations + '</td>'
            + '<td><button type="button" class="button button-small button-delete widgetopts-delete-btn">'
            + 'Delete</button></td>'
            + '</tr>';
    }

    /**
     * Render the scanned items into the table
     */
    function renderTable() {
        var $tbody = $('#widgetopts-migration-tbody');
        $tbody.empty();

        if (!items.length) {
            $('#widgetopts-migration-content').hide();
            $('#widgetopts-migration-empty').show();
            return;
        }

        $.each(items, function (idx, item) {
            $tbody.append(buildRow(item, idx));
        });

        $('#widgetopts-migration-content').show();
        $('#widgetopts-migration-empty').hide();
    }

    /**
     * Perform the initial AJAX scan
     */
    function doScan() {
        $('#widgetopts-migration-loading').show();
        $('#widgetopts-migration-content').hide();
        $('#widgetopts-migration-empty').hide();

        $.post(config.ajaxurl, {
            action: 'widgetopts_migration_scan',
            nonce: config.nonce
        }, function (response) {
            $('#widgetopts-migration-loading').hide();
            if (response.success && response.data && response.data.items) {
                items = response.data.items;
                renderTable();
            } else {
                showNotice(config.i18n.error, 'error');
            }
        }).fail(function () {
            $('#widgetopts-migration-loading').hide();
            showNotice(config.i18n.error, 'error');
        });
    }

    /**
     * Collect selected items (or all if allMode)
     */
    function collectItems(allMode) {
        var collected = [];
        $('#widgetopts-migration-tbody tr').each(function () {
            var $row = $(this);
            if (allMode || $row.find('.widgetopts-row-cb').is(':checked')) {
                collected.push({
                    hash: $row.data('hash'),
                    title: $row.find('.widgetopts-name-input').val()
                });
            }
        });
        return collected;
    }

    /**
     * Run migration AJAX
     */
    function doMigrate(selectedItems) {
        if (!selectedItems.length) {
            alert(config.i18n.selectAtLeast);
            return;
        }
        if (!confirm(config.i18n.confirmMigrate)) {
            return;
        }

        // Disable buttons
        $('#widgetopts-migrate-all, #widgetopts-migrate-selected').prop('disabled', true).text(config.i18n.migrating);

        $.post(config.ajaxurl, {
            action: 'widgetopts_migration_migrate',
            nonce: config.nonce,
            items: selectedItems
        }, function (response) {
            $('#widgetopts-migrate-all, #widgetopts-migrate-selected').prop('disabled', false);
            $('#widgetopts-migrate-all').text('Migrate All');
            $('#widgetopts-migrate-selected').text('Migrate Selected');

            if (response.success && response.data && response.data.results) {
                var r = response.data.results;
                var msg = config.i18n.migrationDone
                    + ' Snippets created: ' + r.snippets_created
                    + ', Widgets updated: ' + r.widgets_updated;
                showNotice(msg, 'success');

                if (r.errors && r.errors.length) {
                    $.each(r.errors, function (_, err) {
                        showNotice(err, 'error');
                    });
                }

                // Re-scan to refresh
                doScan();
            } else {
                var errMsg = (response.data && response.data.message) ? response.data.message : config.i18n.error;
                showNotice(errMsg, 'error');
            }
        }).fail(function () {
            $('#widgetopts-migrate-all, #widgetopts-migrate-selected').prop('disabled', false);
            $('#widgetopts-migrate-all').text('Migrate All');
            $('#widgetopts-migrate-selected').text('Migrate Selected');
            showNotice(config.i18n.error, 'error');
        });
    }

    /**
     * Delete a single legacy code entry
     */
    function doDelete($row) {
        if (!confirm(config.i18n.confirmDelete)) {
            return;
        }

        var hash = $row.data('hash');
        $row.addClass('widgetopts-row-disabled');
        $row.find('.widgetopts-delete-btn').prop('disabled', true).text(config.i18n.deleting);

        $.post(config.ajaxurl, {
            action: 'widgetopts_migration_delete',
            nonce: config.nonce,
            hash: hash
        }, function (response) {
            if (response.success) {
                $row.fadeOut(300, function () {
                    $(this).remove();
                    // Remove from items array
                    items = items.filter(function (item) {
                        return item.hash !== hash;
                    });
                    if (!items.length) {
                        $('#widgetopts-migration-content').hide();
                        $('#widgetopts-migration-empty').show();
                    }
                });
            } else {
                $row.removeClass('widgetopts-row-disabled');
                $row.find('.widgetopts-delete-btn').prop('disabled', false).text('Delete');
                var errMsg = (response.data && response.data.message) ? response.data.message : config.i18n.error;
                showNotice(errMsg, 'error');
            }
        }).fail(function () {
            $row.removeClass('widgetopts-row-disabled');
            $row.find('.widgetopts-delete-btn').prop('disabled', false).text('Delete');
            showNotice(config.i18n.error, 'error');
        });
    }

    // DOM ready
    $(function () {
        // Initial scan
        doScan();

        // Select all toggle
        $('#widgetopts-select-all').on('change', function () {
            var checked = $(this).is(':checked');
            $('#widgetopts-migration-tbody .widgetopts-row-cb').prop('checked', checked);
        });

        // Migrate All
        $('#widgetopts-migrate-all').on('click', function () {
            doMigrate(collectItems(true));
        });

        // Migrate Selected
        $('#widgetopts-migrate-selected').on('click', function () {
            doMigrate(collectItems(false));
        });

        // Delete button
        $(document).on('click', '.widgetopts-delete-btn', function () {
            doDelete($(this).closest('tr'));
        });
    });

})(jQuery);
