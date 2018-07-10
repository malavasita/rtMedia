// check for terms and condition

/**
 * Remove Terms Warning Popup,
 * Add Warning Message Before Submit Button
 * By: Malav Vasita <malav.vasita@rtcamp.com>
 */
if ( 'object' === typeof rtMediaHook ) {
	rtMediaHook.register( 'rtmedia_js_upload_file', function ( args ) {
		if ( false === args ) {
			return args;
		}

		var rt_alert_msg = ( ( 'string' === typeof rtmedia_upload_terms_check_terms_message ) ) ? rtmedia_upload_terms_check_terms_message : rtmedia_upload_terms_check_terms_default_message;

		if ( jQuery( '#rtmedia_upload_terms_conditions' ).length > 0 ) {
			if ( ! jQuery( '#rtmedia_upload_terms_conditions' ).is( ':checked' ) ) {
				var selector = jQuery( '.rtmedia-upload-terms' );
				rtp_display_terms_warning ( selector, rt_alert_msg );
				return false;
			}
		}

		return true;
	} );

	rtMediaHook.register( 'rtmedia_js_before_activity_added', function ( args ) {
		if ( jQuery( '#rtmedia_upload_terms_conditions' ).length > 0 ) {
			if ( false == args ) {
				return args;
			}

			var rt_alert_msg = ( ( 'string' === typeof rtmedia_upload_terms_check_terms_message ) ) ? rtmedia_upload_terms_check_terms_message : rtmedia_upload_terms_check_terms_default_message;
			if ( ! jQuery( '#rtmedia_upload_terms_conditions' ).is( ':checked' )) {
				var selector = jQuery( '.rtmedia-upload-terms' );
				rtp_display_terms_warning ( selector, rt_alert_msg );
				return false;
			}
		}
		return true;
	});

	/**
	 * When Select Attribute for media [ rtmedia-custom-attributes: Add-Ons ] Issue:8,
	 * This Hook returns false that's why the attributes were not saved,
	 * Added: 'return true;'
	 * By: Yahil
	 */
	rtMediaHook.register( 'rtmedia_js_after_file_upload', function () {

		if ( jQuery( '#rtmedia-upload-container #rtmedia_upload_terms_conditions' ).length > 0 ) {
			jQuery( '#rtmedia_upload_terms_conditions' ).removeAttr( 'checked' );
		}
		return true;
	});
}

jQuery(document).ready(function () {
	if( ( '#aw-whats-new-submit' ).length > 0 ) {
		jQuery( '#aw-whats-new-submit' ).attr( 'disabled', 'disabled' );
	}

	jQuery( '#rtmedia_upload_terms_conditions' ).on( 'click', function () {
		if ( jQuery( '#rtmedia_upload_terms_conditions' ).length > 0 ){
			jQuery( '#rtmedia_upload_terms_conditions' ).change( function() {
				if ( jQuery( '#rtmedia_upload_terms_conditions' ).is( ':checked' ) ) {
					jQuery( '#aw-whats-new-submit' ).attr( 'disabled', false );
				} else {
					jQuery( '#aw-whats-new-submit' ).attr( 'disabled', true );
				}
			} );
		}

		if ( jQuery( '#rtmedia_upload_terms_conditions' ).is( ':checked' ) ) {
			jQuery( '.rt_alert_msg' ).remove();
		} else {
			var selector = jQuery( '.rtmedia-upload-terms' );
			var rt_alert_msg = ( ( typeof rtmedia_upload_terms_check_terms_message ) == "string" ) ? rtmedia_upload_terms_check_terms_message : rtmedia_upload_terms_check_terms_default_message;
			rtp_display_terms_warning ( selector, rt_alert_msg );
		}

		if ( typeof rtmedia_direct_upload_enabled != 'undefined' && rtmedia_direct_upload_enabled == '1' ) {
			if ( jQuery( '#aw-whats-new-submit' ).length > 0 ) {
				if ( jQuery( '#whats-new' ).val() != '' || jQuery( '#rtmedia_uploader_filelist' ).children( 'li' ).length > 0) {
					jQuery( '#aw-whats-new-submit' ).trigger( 'click' );
				}
			} else {
				jQuery( '.start-media-upload' ).trigger( 'click' );
			}
		}
	});

});

/**
 * Show Error Message On Admin Side
 * Handle error on rtMedia settings
 * By: Malav Vasita
 */
function rtp_display_terms_warning ( selector, rt_alert_msg ) {
	if ( ! jQuery( '.rt_alert_msg' ).length ) {
		var invalid_error_msg = jQuery( "<span />" ).attr( 'style', 'color:red; display:block; clear:both;' ).addClass( 'rt_alert_msg' ).empty().append( rt_alert_msg );
		selector.after( invalid_error_msg );
	}
}

/**
 * Show/Hide Privacy Message On Front End
 * Handle privacy message on website
 * By: Malav Vasita
 */
function handle_privacy_message() {
	jQuery( '#close_rtm_privacy_message' ).on( 'click', function(c) {
		jQuery( '.privacy_message_wrapper' ).fadeOut( 'slow', function(c) {
			jQuery( '.privacy_message_wrapper' ).remove();
			jQuery.cookie( "rtm_show_privacy_message", "view", { expires : 1, path: "/" } );
		});
	});
}

jQuery( document ).ready( function() {
	handle_privacy_message();
} );
