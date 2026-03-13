(function($){

	WidgetOptsSelect2 = {
		_init: function()
		{
			$('select.widgetopts-select2').each(function() {
				var $el = $(this);

				// Use AJAX mode for snippet dropdown
				if ($el.attr('name') === 'widgetopts_logic_snippet_id') {
					$el.select2({
						width: '100%',
						allowClear: true,
						placeholder: '— No Logic (Always Show) —',
						minimumInputLength: 0,
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
					}).on('change', $.proxy(WidgetOptsSelect2._maybePreview, this.context));

					// Add dynamic description element if not present
					var $field = $el.closest('.fl-field');
					var $descEl = $field.find('.widgetopts-snippet-desc');
					if (!$descEl.length) {
						$descEl = $('<p class="widgetopts-snippet-desc description" style="font-style: italic; color: #666; display:none; margin-top:5px;"></p>');
						$field.find('.fl-field-control').append($descEl);
					}
					$el.on('select2:select', function(e) {
						var desc = e.params.data.description || '';
						if (desc) {
							$descEl.text(desc).show();
						} else {
							$descEl.text('').hide();
						}
					});
					$el.on('select2:unselect', function() {
						$descEl.text('').hide();
					});
					return;
				}

				$el.select2({
					width: '100%',
					allowClear: true,
					placeholder: ' '
				}).on('change', $.proxy(WidgetOptsSelect2._maybePreview, this.context))
				.on('select2:unselecting', function(e) {
		          $(this).data('unselecting', true);
		        }).on('select2:open', function(e) { // note the open event is important
		          if ($(this).data('unselecting')) {
		            $(this).removeData('unselecting'); // you need to unset this before close
		            $(this).select2('close');
		          }
		        }).on('select2:unselect', function(e) { // note the open event is important
		          if( !$(this).val() ){
		          	$(this).val("").trigger('change');
		          }
		        });

				if($(this).attr('multiple')) {
					var ul = $(this).siblings('.select2-container').first('ul.select2-selection__rendered');
					ul.sortable({
						placeholder : 'ui-state-highlight',
						forcePlaceholderSize: true,
						items       : 'li:not(.select2-search__field)',
						tolerance   : 'pointer',
						stop: function() {
							$($(ul).find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $(this).find('option[value="' + id + '"]')[0];
								$(this).prepend(option);
							});
						}
					});
				}
			});
		},

		_maybePreview: function() {
			var e = {
				target: this
			};
			
			var field       = $(this).closest('.fl-field');
			var previewType = field.data('preview');

			if ('refresh' == previewType.type) {
				FLBuilder.preview.delayPreview(e);
			}
		}
	}

	FLBuilder.addHook('settings-form-init', function() {
		WidgetOptsSelect2._init();
	});

})(jQuery);