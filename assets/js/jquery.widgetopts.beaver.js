"use strict";

var widgetoptsBeaverModule = {
	init: function() {
		var self 	= this;
        jQuery( '.widgetopts-module-settings-container' ).hide();

		self.bindEvents();
    },

	bindEvents: function() {
		var self 	= this;
		var $body = jQuery( 'body' );

		$body.on( 'click', '.fl-builder-widgetopts-tab a', self.navClick );
		
		// Refresh snippets on Select2 open for snippet dropdown
		$body.on( 'select2:open', 'select[name="widgetopts_logic_snippet_id"]', self.refreshSnippets );

	},

	navClick: function( e ) {
		e.preventDefault();
		var $link = jQuery(e.target).closest('a');
		jQuery( '.widgetopts-s-active' ).removeClass( 'widgetopts-s-active' );
		jQuery( '#fl-builder-settings-tab-widgetopts .fl-builder-settings-section' ).hide();
		jQuery( $link.attr('href') ).show();
		$link.addClass( 'widgetopts-s-active' );
	},
	
	refreshSnippets: function( e ) {
		var $select = jQuery(this);
		var currentValue = $select.val();
		
		// Get ajax URL - use FLBuilder config or fallback
		var ajax_url = (typeof FLBuilder !== 'undefined' && FLBuilder._ajaxUrl) 
			? FLBuilder._ajaxUrl() 
			: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
		
		jQuery.ajax({
			url: ajax_url,
			type: 'POST',
			data: { action: 'widgetopts_get_snippets_ajax' },
			success: function(response) {
				if (response.success && response.data && response.data.snippets) {
					var snippets = response.data.snippets;
					var $options = '<option value="">— No Logic (Always Show) —</option>';
					snippets.forEach(function(snippet) {
						var selected = (currentValue == snippet.id) ? ' selected' : '';
						$options += '<option value="' + snippet.id + '"' + selected + '>' + jQuery('<div>').text(snippet.title).html() + '</option>';
					});
					$select.html($options).trigger('change.select2');
				}
			}
		});
	}
}

jQuery(document).ready(function() {
	widgetoptsBeaverModule.init();
});