/**
 * Update user points via Ajax.
 *
 * @package WordPoints\Points
 * @since 2.5.0
 */

/* global ajaxurl, WordPointsUserPointsTableL10n, WordPointsUserPointsTableData, wp */

(function ( $ ) {

	var self;

	self = {

		//
		// Properties.
		//

		/**
		 * The current state of the operation.
		 *
		 * @since 2.5.0
		 *
		 * @type object state {
		 *       int    points The number of points.
		 *       string action The action being performed: 'add' or 'subtract'.
		 *       string reason The reason for the change supplied by the user.
		 * }
		 */
		state : {},

		/**
		 * Localization strings for the object.
		 *
		 * @since 2.5.0
		 */
		l10n : WordPointsUserPointsTableL10n,

		/**
		 * Data for the object.
		 *
		 * @since 2.5.0
		 */
		data : WordPointsUserPointsTableData,

		/**
		 * Template for the confirmation dialog.
		 *
		 * @since 2.5.0
		 */
		dialogTemplate : wp.wordpoints.utils.template( 'user-points-dialog' ),

		//
		// Methods.
		//

		/**
		 * Initialize the script.
		 *
		 * @since 2.5.0
		 */
		init : function() {

			$( '.wrap' ).on(
				'click'
				, 'input.wordpoints-add-points, input.wordpoints-subtract-points'
				, self.onClick
			);
		},

		/**
		 * Handles a click on the Add/Subtract buttons.
		 *
		 * @since 2.5.0
		 *
		 * @param event
		 */
		onClick : function( event ) {

			event.preventDefault();

			var $target = $( event.target ),
				$input = $target.siblings( 'input[type="number"]' ),
				points = parseInt( $input.val(), 10 );

			if ( isNaN( points ) || points < 1 ) {
				self.invalidInput();
				return;
			}

			// Reset.
			self.state = {};

			self.state.points  = points;
			self.state.row     = $input.parents( 'tr' );
			self.state.current = parseInt( self.state.row.find( 'td.points' ).html(), 10 );

			if ( $target.hasClass( 'wordpoints-add-points' ) ) {
				self.state.action = 'add';
			} else {
				self.state.action = 'subtract';
			}

			if (
				self.state.action === 'subtract'
				&& self.state.current - self.state.points < self.data.pointsMinimum
			) {

				self.invalidInput(
					wp.wordpoints.utils.textTemplate( self.l10n.lessThanMinimum )(
						{ minimum: self.data.pointsMinimum }
					)
				);

				return;
			}

			self.confirm();
		},

		/**
		 * Show the user an error if they offer an invalid points value.
		 *
		 * @since 2.5.0
		 *
		 * @param message
		 */
		invalidInput : function ( message ) {

			message = message || self.l10n.invalidInputText;

			$( '<div></div>' )
				.attr( 'title', self.l10n.invalidInputTitle )
				.append( $( '<p></p>' ).text( message ) )
				.dialog( {
					dialogClass: 'wp-dialog',
					resizable: false,
					draggable: false,
					height: 'auto',
					modal: true,
					buttons: [
						{
							text: self.l10n.closeButtonText,
							'class': 'button',
							click: function() {
								$( this ).dialog( 'close' );
							}
						}
					]
				} );
		},

		/**
		 * Display a confirmation popup to the user.
		 *
		 * It also has a field letting them specify a reason for the change.
		 *
		 * @since 2.5.0
		 */
		confirm : function() {

			var button, total;

			var current = self.state.current;

			if ( self.state.action === 'add' ) {
				button  = self.l10n.addButtonText;
				total   = current + ' + ' + self.state.points + ' = ' + ( current + self.state.points );
			} else {
				button  = self.l10n.subtractButtonText;
				total   = current + ' - ' + self.state.points + ' = ' + ( current - self.state.points );
			}

			$( self.dialogTemplate( { total: total } ) )
				.find( '.wordpoints-points-user' )
					.append( self.state.row.find( 'td.username' ).html() )
				.end()
				.dialog( {
					dialogClass: 'wp-dialog',
					resizable: false,
					draggable: false,
					height: 'auto',
					modal: true,
					buttons: [
						{
							text: self.l10n.cancelButtonText,
							'class': 'button',
							click: function() {
								$( this ).dialog( 'close' );
							}
						},
						{
							text: button,
							'class': 'button button-primary',
							click: function() {
								var $this = $( this );

								self.state.reason = $this.find( 'input' ).val();

								$this.html(
									$( '<p></p>' )
										.text( self.l10n.waitMessage )
										.prepend(
											$( '<span></span>' )
												.addClass( 'spinner is-active' )
										)
								);
								$this.dialog( 'option', 'buttons', {} );

								self.state.dialog = $this;
								self.submit();
							}
						}
					]
				} );
		},

		/**
		 * Submit that Ajax request.
		 *
		 * @since 2.5.0
		 */
		submit : function() {

			$.post(
				ajaxurl,
				{
					'action': 'wordpoints_points_alter_user_points',
					'points': self.state.points,
					'alter': self.state.action,
					'reason': self.state.reason,
					'user_id': self.state.row.data( 'wordpoints-points-user-id' ),
					'type': self.data.pointsType,
					'_ajax_nonce': self.data.nonce
				}
			).done(
				function( response ) {

					if ( ! response.success ) {
						self.error();
						return;
					}

					self.state.response = response;
					self.success();
				}
			).fail(
				function () {
					self.error();
				}
			);
		},

		/**
		 * Show an error on failure.
		 *
		 * @since 2.5.0
		 */
		error : function( message ) {

			if ( ! message ) {
				message = self.l10n.errorMessage;
			}

			var $dialog = self.state.dialog;

			$dialog.html( $( '<p></p>' ).text( message ) );

			$dialog.dialog(
				'option',
				'buttons',
				[
					{
						text: self.l10n.closeButtonText,
						'class': 'button button-primary',
						click: function() {
							$( this ).dialog( 'close' );
						}
					}
				]
			);
		},

		/**
		 * Called on success to update the displayed values.
		 *
		 * @since 2.5.0
		 */
		success : function() {

			// Close the dialog.
			self.state.dialog.dialog( 'close' );

			// Update the number of points for the user.
			self.state.row
				.find( 'td.points' )
				.html( self.state.response.data.points )
				.effect( 'highlight', {}, 3000 );

			wp.a11y.speak( self.l10n.successMessage );
		}
	};

	// Initialize the object when the DOM is ready.
	$( document ).ready( function() {
		self.init();
	});

})(jQuery);

// EOF.
