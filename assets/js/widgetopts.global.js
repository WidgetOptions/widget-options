jQuery(function () {
  jQuery(document).on(
    "click",
    ".widgetopts-notice .notice-dismiss",
    function () {
      jQuery.ajax({
        url: widgetopts10n.ajax_url,
        type: "POST",
        data: {
          method: "delete_widgetopts_update_transient",
          action: "widgetopts_ajax_settings",
          nonce: jQuery('input[name="widgetopts-settings-nonce"]').val(),
        },
      });
    }
  );

  jQuery(document).on(
    "change keyup",
    '.widget-opts-logic textarea[name="extended_widget_opts[class][logic]"], .widget-opts-logic textarea',
    function () {
      checkForDangerousPatterns(this);
    }
  );

  jQuery(document).on(
    "click",
    ".widgetopts-tab-panel.tab-logic button",
    function () {
      checkForDangerousPatterns(
        jQuery(
          '.widget-opts-logic textarea[name="extended_widget_opts[class][logic]"]'
        )
      );
    }
  );

  function checkForDangerousPatterns(that) {
    const dangerousPatterns = [
      // Database-related keywords
      { pattern: /\binsert\b/i, keyword: "insert" },
      { pattern: /\bupdate\b/i, keyword: "update" },
      { pattern: /\bdelete\b/i, keyword: "delete" },
      { pattern: /\breplace\b/i, keyword: "replace" },
      { pattern: /\bselect\b/i, keyword: "select" },
      { pattern: /\bdrop\b/i, keyword: "drop" },
      { pattern: /\balter\b/i, keyword: "alter" },

      // WordPress-specific database functions
      { pattern: /\bwp_insert_post\b/i, keyword: "wp_insert_post" },
      { pattern: /\bwp_update_post\b/i, keyword: "wp_update_post" },
      { pattern: /\bwp_delete_post\b/i, keyword: "wp_delete_post" },
      { pattern: /\bwp_insert_user\b/i, keyword: "wp_insert_user" },
      { pattern: /\bwp_update_user\b/i, keyword: "wp_update_user" },
      { pattern: /\bwp_delete_user\b/i, keyword: "wp_delete_user" },
      { pattern: /\badd_option\b/i, keyword: "add_option" },
      { pattern: /\bupdate_option\b/i, keyword: "update_option" },
      { pattern: /\bdelete_option\b/i, keyword: "delete_option" },
      { pattern: /\bwpdb\b/i, keyword: "wpdb" },

      // JavaScript, CSS, and HTML
      { pattern: /<script\b[^>]*>(.*?)<\/script>/i, keyword: "<script>" },
      { pattern: /<style\b[^>]*>(.*?)<\/style>/i, keyword: "<style>" },

      // PHP file manipulation functions
      { pattern: /\bfile_put_contents\b/i, keyword: "file_put_contents" },
      { pattern: /\bfile_get_contents\b/i, keyword: "file_get_contents" },
      { pattern: /\bfopen\b/i, keyword: "fopen" },
      { pattern: /\bfwrite\b/i, keyword: "fwrite" },
      { pattern: /\bunlink\b/i, keyword: "unlink" },
      { pattern: /\brename\b/i, keyword: "rename" },
      { pattern: /\bchmod\b/i, keyword: "chmod" },
      { pattern: /\bchown\b/i, keyword: "chown" },
      { pattern: /\bcopy\b/i, keyword: "copy" },
      { pattern: /\bscandir\b/i, keyword: "scandir" },

      // External connections
      { pattern: /\bwp_remote_get\b/i, keyword: "wp_remote_get" },
      { pattern: /\bwp_remote_post\b/i, keyword: "wp_remote_post" },
      { pattern: /\bcurl_init\b/i, keyword: "curl_init" },
      {
        pattern: /\bstream_context_create\b/i,
        keyword: "stream_context_create",
      },

      // Reflection and dynamic variable/function manipulation
      { pattern: /\bReflectionClass\b/i, keyword: "ReflectionClass" },
      { pattern: /\bReflectionMethod\b/i, keyword: "ReflectionMethod" },
      { pattern: /\bReflectionProperty\b/i, keyword: "ReflectionProperty" },
      { pattern: /\bcall_user_func\b/i, keyword: "call_user_func" },
      {
        pattern: /\bcall_user_func_array\b/i,
        keyword: "call_user_func_array",
      },
      { pattern: /\bextract\b/i, keyword: "extract" },
      { pattern: /\bparse_str\b/i, keyword: "parse_str" },

      // System commands
      { pattern: /\beval\b/i, keyword: "eval" },
      { pattern: /\bsystem\b/i, keyword: "system" },
      { pattern: /\bshell_exec\b/i, keyword: "shell_exec" },
      { pattern: /\bexec\b/i, keyword: "exec" },
      { pattern: /\bpassthru\b/i, keyword: "passthru" },
      { pattern: /\bpopen\b/i, keyword: "popen" },
    ];

    let input = jQuery(that).val();
    let safe = true;

    for (const { pattern, keyword } of dangerousPatterns) {
      if (pattern.test(input)) {
        safe = false;
        if (jQuery(".wopts-warning-message").length === 0) {
          jQuery(that).after(
            `<p class="wopts-warning-message" style="font-size: 11px;">Warning: <span style="color: red;">Unallowed keyword "${keyword}" exists in your code.</span></p>`
          );
        }

        break;
      }
    }

    if (safe === true) {
      if (jQuery(".wopts-warning-message").length !== 0) {
        jQuery(".wopts-warning-message").remove();
      }
    }
  }
});
