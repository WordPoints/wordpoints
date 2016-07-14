/**
 * Perform actions when admin notices are dismissed.
 *
 * @package WordPoints\Admin
 * @since 2.1.0
 */

// globals: jQuery, wp

(function( $ ) {

	'use strict';

	$( document ).ready(function() {

		$( '.wordpoints-notice-dismiss-form' ).hide();

		$( '.notice' ).on( 'click', '.notice-dismiss', function() {

			var $this = $( this ).closest( '.notice' ),
				option = $this.data( 'option' ),
				nonce = $this.data( 'nonce' );

			if ( ! option || ! nonce ) {
				return;
			}

			wp.ajax.post(
				'wordpoints-delete-admin-notice-option',
				{ wordpoints_notice: option, _wpnonce: nonce }
			);
		});
	});

})( jQuery );

// EOF
