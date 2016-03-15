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
		$(document).on( 'click', '#extended-widget-opts-page-lists h4',function(){
			getid = $(this).attr('id');
			$('#extended-widget-opts-page-lists .'+ getid).slideToggle(250);
		} );

		//admin settings
		$(document).on( 'click', '.opts-add-class-btn',function(e){
			getVal = $('.opts-add-class-txtfld').val();
			if( getVal.length > 0 ){
				$('#opts-predefined-classes ul').append('<li><input type="hidden" name="extwopts_class_settings[classlists][]" value="'+ getVal +'" /><span class"opts-li-value">'+ getVal +'</span> <a href="#" class="opts-remove-class-btn"><span class="dashicons dashicons-dismiss"></span></a></li>');	
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
	});

	function extended_widget_opts_init( widget, action ){
		selected = 0;
		if( ''	!=	widget ){
			if( $( '#' + widget.attr('id') ).find('#extended-widget-opts-selectedtab').length > 0 ){
				selected = $( '#' + widget.attr('id') ).find('#extended-widget-opts-selectedtab').val();
				selected = parseInt( selected );
			}
		}
		if( action == 'added' ){
			selected = 0;
		}
	    
	    if( '' != widget ){
	    	if( $( '#' + widget.attr('id') ).find('.extended-widget-opts-tabs').length > 0 ){
	    		$( '#' + widget.attr('id') ).find('.extended-widget-opts-tabs').tabs({ active: selected });
	    	}
	    }else{
	    	$('.extended-widget-opts-tabs').tabs({ active: selected });
	    }

	    $('.extended-widget-opts-tabs').click('tabsselect', function (event, ui) {
			if( $(this).find('#extended-widget-opts-selectedtab').length > 0 ){
				$(this).find('#extended-widget-opts-selectedtab').val( $(this).tabs('option', 'active') );
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

