jQuery(function () {
  jQuery(document).on("click", ".widgetopts-notice .notice-dismiss", function () {
    jQuery.ajax({
      url: widgetopts10n.ajax_url,
      type: "POST",
      data: {
        method: "delete_widgetopts_update_transient",
        action: "widgetopts_ajax_settings",
        nonce: jQuery('input[name="widgetopts-settings-nonce"]').val(),
      },
    });
  });

  jQuery(document).on("change", '.widget-opts-logic textarea[name="extended_widget_opts[class][logic]"], .widget-opts-logic textarea', function () {
    checkForDangerousPatterns(this);
  });

  jQuery(document).on("keyup", '.widget-opts-logic textarea[name="extended_widget_opts[class][logic]"], .widget-opts-logic textarea', function () {
    partialCheckForDangerousPatterns(this);
  });

  jQuery(document).on("click", ".widgetopts-tab-panel.tab-logic button", function () {
    checkForDangerousPatterns(jQuery('.widget-opts-logic textarea[name="extended_widget_opts[class][logic]"]'));
  });

  function checkForDangerousPatterns(that) {
    let expression = jQuery(that).val();
    jQuery.ajax({
      type: "POST",
      url: widgetopts10n.ajax_url,
      data: {
        action: "widgetopts_ajax_validate_expression",
        nonce: widgetopts10n.validate_expression_nonce,
        expression: expression,
      },
      dataType: "json",
      success: function (response) {
        if (response.valid) {
          if (jQuery(".wopts-warning-message").length !== 0) {
            jQuery(".wopts-warning-message").remove();
          }
        } else {
          if (jQuery(".wopts-warning-message").length === 0) {
            if (response.message != "") {
              jQuery(that).after(`<p class="wopts-warning-message" style="font-size: 11px;">Warning: <span style="color: red;">${response.message}</span></p>`);
            }
          }
        }
      },
      error: function () {},
    });
  }

  function partialCheckForDangerousPatterns(that) {
    const dangerousPatterns = [
      // Database-related keywords
      { pattern: /\b(insert|update|delete|replace|select|drop|alter|truncate|grant|revoke)\b/i, message: "Potential SQL injection detected." },

      // WordPress-specific database functions
      { pattern: /\b(wp_insert_post|wp_update_post|wp_delete_post|wp_insert_user|wp_update_user|wp_delete_user|add_option|update_option|delete_option|wpdb)\b/i, message: "Unsafe WordPress database functions found." },

      // PHP file manipulation functions
      { pattern: /\b(file_put_contents|file_get_contents|fopen|fwrite|unlink|rename|chmod|chown|chgrp|copy|scandir)\b/i, message: "File system manipulation functions are not allowed." },

      // External connections
      { pattern: /\b(wp_remote_get|wp_remote_post|curl_init|curl_exec|curl_setopt|open_basedir|fsockopen|proc_nice|stream_socket_server|stream_socket_client)\b/i, message: "Potential remote execution functions detected." },

      // Execution function
      { pattern: /\b(eval|assert|system|exec|shell_exec|passthru|proc_open|popen|pcntl_exec|dl|include|require|include_once|require_once)\b/i, message: "Execution functions are not allowed." },

      // Encoding/decoding functions
      { pattern: /\b(base64_decode|hex2bin|mb_decode_mimeheader|str_rot13)\b/i, message: "Encoding/decoding functions that may be used for obfuscation are not allowed." },

      // Dynamic function execution
      { pattern: /\b(call_user_func|call_user_func_array|create_function|compact|extract|parse_str|ReflectionClass|ReflectionMethod|ReflectionProperty)\b/i, message: "Dynamic function execution is not allowed." },

      // Remote execution functions
      { pattern: /\b(str_replace|str_ireplace|preg_replace|preg_replace_callback|preg_replace_callback_array)\b/i, message: "String replacement functions are restricted due to potential obfuscation." },

      //Dynamic PHP variable call
      { pattern: /\[\s*[\'"]?[a-zA-Z0-9_]+\s*\.\s*[\'"]?[a-zA-Z0-9_]+\s*\]/i, message: "Concatenated function execution is not allowed." },
      { pattern: /(?:\(\$[a-zA-Z_]\w*\)|\$[a-zA-Z_]\w*)(?=\s*\()/g, message: "Potential function name obfuscation detected." },
      { pattern: /\b(str_replace|preg_replace|preg_replace_callback|preg_replace_callback_array)\s*\(\s*[\'"]\s*\.\s*[\'"]/, message: "Potential function name obfuscation detected." },

      //Backtick
      { pattern: /`[^`]*`/, message: "Backticks execution is not allowed." },

      { pattern: /\\x(?:[0-9A-F]{2})+/i, message: "Hexadecimal escape sequences detected." },
      { pattern: /\\u(?:[0-9A-F]{4})+/i, message: "Unicode escape sequences detected." },
      { pattern: /\$\w+\s*\[\s*[\'"]?\d+[\'"]?\s*\]\s*\(/, message: "Dynamic function execution using arrays is not allowed." },
    ];

    let input = jQuery(that).val();
    let safe = true;

    for (const { pattern, message } of dangerousPatterns) {
      if (pattern.test(input)) {
        safe = false;
        if (jQuery(".wopts-warning-message").length === 0) {
          jQuery(that).after(`<p class="wopts-warning-message" style="font-size: 11px;">Warning: <span style="color: red;">${message}</span></p>`);
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
