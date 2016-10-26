"use strict";

var widgetoptsSettingsModule = {
	init: function() {
        jQuery( '.widgetopts-module-settings-container' ).hide();

		this.bindEvents();
    },

	bindEvents: function() {
		var $container = jQuery( '#wpcontent' );

		$container.on( 'click', '.widgetopts-settings-view-toggle a', this.toggleView );
		$container.on( 'click', '.list .widgetopts-module-card:not(.itsec-module-pro-upsell) .widgetopts-module-card-content, .widgetopts-toggle-settings, .widgetopts-module-settings-cancel', this.toggleSettings );
		$container.on( 'click', '.widgetopts-close-modal, .widgetopts-modal-background', this.closeGridSettingsModal );
		$container.on( 'keyup', this.closeGridSettingsModal );
		$container.on( 'click', '.widgetopts-toggle-activation', this.toggleModuleActivation );
		$container.on( 'click', '.widgetopts-module-settings-save', this.saveSettings );

	},

	toggleSettings: function( e ) {
		e.stopPropagation();

		var $listClassElement = jQuery(e.currentTarget).parents( '.widgetopts-module-cards-container' );

		if ( $listClassElement.hasClass( 'list') ) {
			widgetoptsSettingsModule.toggleListSettingsCard.call( this, e );
		} else {
			widgetoptsSettingsModule.showGridSettingsModal.call( this, e );
		}

		// We use this to avoid pushing a new state when we're trying to handle a popstate
		if ( 'widgetopts-popstate' !== e.type ) {
			var module_id = jQuery(this).closest('.widgetopts-module-card').data( 'module-id' );
			window.history.pushState( {'module':module_id}, module_id, '?page=widgetopts_plugin_settings&module=' + module_id );
		}
	},

	showGridSettingsModal: function( e ) {
		e.preventDefault();

		var $settingsContainer = jQuery(this).parents( '.widgetopts-module-card' ).find( '.widgetopts-module-settings-container' ),
			$modalBackground = jQuery( '.widgetopts-modal-background' );

		$modalBackground.show();
		$settingsContainer
			.show();

		jQuery( 'body' ).addClass( 'widgetopts-modal-open' );
	},

	closeGridSettingsModal: function( e ) {
		if ( 'undefined' !== typeof e ) {
			e.preventDefault();

			// For keyup events, only process esc
			if ( 'keyup' === e.type && 27 !== e.which ) {
				return;
			}
		}

		jQuery( '.widgetopts-modal-background' ).hide();
		jQuery( '.widgetopts-module-settings-container' ).hide();
		jQuery( 'body' ).removeClass( 'widgetopts-modal-open' );
	},

	toggleModuleActivation: function( e ) {
		e.preventDefault();
		e.stopPropagation();

		var $button = jQuery(this),
			$card = $button.parents( '.widgetopts-module-card' ),
			$buttons = $card.find( '.widgetopts-toggle-activation' ),
			module = $card.attr( 'data-module-id' );

		$buttons.prop( 'disabled', true );

		if ( $button.html() == widgetopts.translation.activate ) {
			var method = 'activate';
		} else {
			var method = 'deactivate';
		}

		widgetoptsSettingsModule.sendAJAXRequest( module, method, {}, widgetoptsSettingsModule.toggleModuleActivationCallback );
	},

	toggleModuleActivationCallback: function( results ) {
		var module = results.module;
		var method = results.method;

		var $card = jQuery( '#widgetopts-module-card-' + module ),
			$buttons = $card.find( '.widgetopts-toggle-activation' );

		if ( results.errors.length > 0 ) {
			$buttons
				.html( widgetopts.translations.error )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' );

			setTimeout( function() {
				widgetoptsSettingsModule.isModuleActive( module );
			}, 1000 );

			return;
		}

		// console.log( method );

		if ( 'activate' === method ) {
			$buttons
				.html( widgetopts.translation.deactivate )
				.addClass( 'button-secondary' )
				.removeClass( 'button-primary' )
				.prop( 'disabled', false );

			$card
				.addClass( 'widgetopts-module-type-enabled' )
				.removeClass( 'widgetopts-module-type-disabled' );

			var newToggleSettingsLabel = widgetopts.translation.show_settings;
		} else {
			$buttons
				.html( widgetopts.translation.activate )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' )
				.prop( 'disabled', false );

			$card
				.addClass( 'widgetopts-module-type-disabled' )
				.removeClass( 'widgetopts-module-type-enabled' );

			var newToggleSettingsLabel = widgetopts.translation.show_description;
		}

		if( !$card.hasClass('widgetopts-module-card-no-settings') ){
			$card.find( '.widgetopts-toggle-settings' ).html( newToggleSettingsLabel );
		}
	},

	saveSettings: function( e ) {
		e.preventDefault();

		var $button = jQuery(this);

		if ( $button.hasClass( 'widgetopts-module-settings-save' ) ) {
			var module = $button.parents( '.widgetopts-module-card' ).attr( 'data-module-id' );
		} else {
			var module = '';
		}

		$button.prop( 'disabled', true );

		var data = {
			'--widgetopts-form-serialized-data': jQuery( '#widgetopts-module-settings-form' ).serialize()
		};

		widgetoptsSettingsModule.sendAJAXRequest( module, 'save', data, widgetoptsSettingsModule.saveSettingsCallback );
	},

	saveSettingsCallback: function( results ) {
		if ( '' === results.module ) {
			jQuery( '#widgetopts-save' ).prop( 'disabled', false );
		} else {
			jQuery( '#widgetopts-module-card-' + results.module + ' button.widgetopts-module-settings-save' ).prop( 'disabled', false );
		}

		var $container = jQuery( '.widgetopts-module-cards-container' );

		if ( !$container.hasClass( 'list' ) ) {
			var view = 'grid';
		} else {
			var view = 'list';
		}
		// console.log( results );
		widgetoptsSettingsModule.clearMessages();
		if ( results.errors.length > 0 || ! results.closeModal ) {
			// widgetoptsSettingsModule.showErrors( results.errors, results.module, 'open' );
			widgetoptsSettingsModule.showMessages( results.messages, results.module, 'open' );

			if ( 'grid' === view ) {
				$container.find( '.widgetopts-module-settings-content-container:visible' ).animate( {'scrollTop': 0}, 'fast' );
			}

			if ( 'list' === view ) {
				jQuery(document).scrollTop( 0 );
			}
		} else {
			widgetoptsSettingsModule.showMessages( results.messages, results.module, 'closed' );

			if ( 'grid' === view ) {
				$container.find( '.widgetopts-module-settings-content-container:visible' ).scrollTop( 0 );
				widgetoptsSettingsModule.closeGridSettingsModal();
			}
		}
	},

	clearMessages: function() {
		jQuery( '#widgetopts-settings-messages-container, .widgetopts-module-messages-container' ).empty();
	},

	showMessages: function( messages, module, containerStatus ) {
		jQuery.each( messages, function( index, message ) {
			widgetoptsSettingsModule.showMessage( message, module, containerStatus );
		} );
	},

	showMessage: function( message, module, containerStatus ) {
		if ( !jQuery( '.widgetopts-module-cards-container' ).hasClass( 'list' ) ) {
			var view = 'grid';
		} else {
			var view = 'list';
		}

		if ( 'closed' !== containerStatus && 'open' !== containerStatus ) {
			containerStatus = 'closed';
		}

		if ( 'string' !== typeof module ) {
			module = '';
		}

		if ( 'closed' === containerStatus || '' === module ) {
			var container = jQuery( '#widgetopts-settings-messages-container' );

			setTimeout( function() {
				container.removeClass( 'visible' );
				setTimeout( function() {
					container.find( 'div' ).remove();
				}, 500 );
			}, 4000 );
		} else {
			var container = jQuery( '#widgetopts-module-card-' + module + ' .widgetopts-module-messages-container' );
		}

		container.append( '<div class="updated fade"><p><strong>' + message + '</strong></p></div>' ).addClass( 'visible' );
	},


	sendAJAXRequest: function( module, method, data, callback ) {
		var postData = {
			'action': widgetopts.ajax_action,
			'nonce':  widgetopts.ajax_nonce,
			'module': module,
			'method': method,
			'data':   data,
		};

		jQuery.post( ajaxurl, postData )
			.always(function( a, status, b ) {
				widgetoptsSettingsModule.processAjaxResponse( a, status, b, module, method, data, callback );
			});
	},

	processAjaxResponse: function( a, status, b, module, method, data, callback ) {
		var results = {
			'module':        module,
			'method':        method,
			'data':          data,
			'status':        status,
			'license_status': null,
			'jqxhr':         null,
			'success':       false,
			'response':      null,
			'errors':        [],
			'messages':      [],
			'functionCalls': [],
			'redirect':      false,
			'closeModal':    true
		};
		// console.log( a );

		a = jQuery.parseJSON( a  );

		if ( 'WIDGETOPTS_Response' === a.source && 'undefined' !== a.response ) {
			// Successful response with a valid format.
			results.jqxhr = b;
			results.success = a.success;
			results.response = a.response;
			results.errors = a.errors;
			results.messages = a.messages;
			results.functionCalls = a.functionCalls;
			results.redirect = a.redirect;
			results.closeModal = a.closeModal;

			if( typeof results.license_status != 'undefined' ){
				results.license_status = a.license_status;
			}
		}

		if ( 'function' === typeof callback ) {
			callback( results );
		} else if ( 'function' === typeof console.log ) {
			console.log( 'ERROR: Unable to handle settings AJAX request due to an invalid callback:', callback, {'data': postData, 'results': results} );
		}

	},

	getUrlParameter: function( name ) {
		var pageURL = decodeURIComponent( window.location.search.substring( 1 ) ),
			URLParameters = pageURL.split( '&' ),
			parameterName,
			i;

		// Loop through all parameters
		for ( i = 0; i < URLParameters.length; i++ ) {
			parameterName = URLParameters[i].split( '=' );

			// If this is the parameter we're looking for
			if ( parameterName[0] === name ) {
				// Return the value or true if there is no value
				return parameterName[1] === undefined ? true : parameterName[1];
			}
		}
		// If the requested parameter doesn't exist, return false
		return false;
	}
}

jQuery(document).ready(function() {
	widgetoptsSettingsModule.init();
});
