(function( $, window, document, undefined ) {
	if( $('.extended-widget-opts-tabs').length > 0 ){
		extended_widget_opts_init( '', 'loaded' );
	}

	$(document).ready(function(){
		$(".widget-liquid-right .widget, .inactive-sidebar .widget, #accordion-panel-widgets .customize-control-widget_form").each(function (i, widget) {
	    	extended_widget_opts_init( '', 'loaded' );
	  	});
	  	$(document).on('widget-added', function(event, widget) {
		    extended_widget_opts_init( widget, 'added' );

		});
		$(document).on('widget-updated', function(event, widget) {
			extended_widget_opts_init( widget, 'updated' );
		});
		$(document).on( 'click', '.extended-widget-opts-inner-lists h4',function(){
			getid = $(this).attr('id');
			$('.extended-widget-opts-inner-lists .'+ getid).slideToggle(250);
		} );

		$(document).on( 'click', '.widget-opts-toggler-note',function(e){
            $(this).parent('p').parent('.widget-opts-logic').find('.widget-opts-toggle-note').slideToggle(250);

            e.preventDefault();
            e.stopPropagation();
        } );

		//admin settings
		$(document).on( 'click', '.opts-add-class-btn',function(e){
			getVal = $('.opts-add-class-txtfld').val();
			var fname = 'extwopts_class_settings[classlists][]';
			if( $(this).hasClass('widgetopts-add-class-btn') ){
				fname = 'classes[classlists][]';
			}
			if( getVal.length > 0 ){
				$('#opts-predefined-classes ul').append('<li><input type="hidden" name="'+ fname +'" value="'+ getVal +'" /><span class"opts-li-value">'+ getVal +'</span> <a href="#" class="opts-remove-class-btn"><span class="dashicons dashicons-dismiss"></span></a></li>');
				$('.opts-add-class-txtfld').val('');
			}

			e.preventDefault();
			e.stopPropagation();
		} );
		
		$(document).on( 'click', '.opts-remove-class-btn',function(e){
			$(this).parent('li').fadeOut('fast',function(){
				$(this).remove();
			});
			e.preventDefault();
			e.stopPropagation();
		} );

		if( $('.wp-admin.widgets-php .wrap a.page-title-action').length > 0 ){
			$('.wp-admin.widgets-php .wrap a.page-title-action').after('<a href="'+ widgetopts10n.opts_page +'" class="page-title-action hide-if-no-customize">'+ widgetopts10n.translation.manage_settings +'</a>');
		}
	});

	function extended_widget_opts_init( widget, action ){
		selected 			= 0;
		selected_visibility = 0;
		selected_settings   = 0;
		if( ''	!=	widget ){
			if( $( '#' + widget.attr('id') ).find('#extended-widget-opts-selectedtab').length > 0 ){
				selected = $( '#' + widget.attr('id') ).find('#extended-widget-opts-selectedtab').val();
				selected = parseInt( selected );
			}

			if( $( '#' + widget.attr('id') ).find('#extended-widget-opts-visibility-selectedtab').length > 0 ){
                selected_visibility = $( '#' + widget.attr('id') ).find('#extended-widget-opts-visibility-selectedtab').val();
                selected_visibility = parseInt( selected_visibility );
            }

          	if( $( '#' + widget.attr('id') ).find('#extended-widget-opts-settings-selectedtab').length > 0 ){
                selected_settings = $( '#' + widget.attr('id') ).find('#extended-widget-opts-settings-selectedtab').val();
                selected_settings = parseInt( selected_settings );
            }
		}
		if( action == 'added' ){
			selected 			= 0;
			selected_visibility = 0;
			selected_settings   = 0;
		}

	    if( '' != widget ){
	    	if( $( '#' + widget.attr('id') ).find('.extended-widget-opts-tabs').length > 0 ){
	    		$( '#' + widget.attr('id') ).find('.extended-widget-opts-tabs').tabs({ active: selected });
	    	}
	    	if( $( '#' + widget.attr('id') ).find('.extended-widget-opts-visibility-tabs').length > 0 ){
                $( '#' + widget.attr('id') ).find('.extended-widget-opts-visibility-tabs').tabs({ active: selected_visibility });
            }
            if( $( '#' + widget.attr('id') ).find('.extended-widget-opts-settings-tabs').length > 0 ){
                $( '#' + widget.attr('id') ).find('.extended-widget-opts-settings-tabs').tabs({ active: selected_settings });
            }

	    }else{
	    	$('.extended-widget-opts-tabs').tabs({ active: selected });
	    	$('.extended-widget-opts-visibility-tabs').tabs({ active: selected_visibility });
	    	$('.extended-widget-opts-settings-tabs').tabs({ active: selected_settings });
	    }

	    $('.extended-widget-opts-tabs').click('tabsselect', function (event, ui) {
			if( $(this).find('#extended-widget-opts-selectedtab').length > 0 ){
				$(this).find('#extended-widget-opts-selectedtab').val( $(this).tabs('option', 'active') );
			}
		});

	    $('.extended-widget-opts-visibility-tabs').click('tabsselect', function (event, ui) {
            if( $(this).find('#extended-widget-opts-visibility-selectedtab').length > 0 ){
                $(this).find('#extended-widget-opts-visibility-selectedtab').val( $(this).tabs('option', 'active') );
            }
        });

        $('.extended-widget-opts-settings-tabs').click('tabsselect', function (event, ui) {
            if( $(this).find('#extended-widget-opts-settings-selectedtab').length > 0 ){
                $(this).find('#extended-widget-opts-settings-selectedtab').val( $(this).tabs('option', 'active') );
            }
        });

    	$('.extended-widget-opts-date').datepicker({
		    //comment the beforeShow handler if you want to see the ugly overlay
		    beforeShow: function() {
		        setTimeout(function(){
		            $('.ui-datepicker').css('z-index', 99999999999999);
		        }, 0);
		    }
		});
	}
})( jQuery, window, document );
