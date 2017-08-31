/**
 * Generic code for the points types administration screen.
 *
 * @package WordPoints\Points\Administration
 * @since 2.1.0
 */

/* global WordPointsPointsTypesL10n, jQuery */

jQuery( document ).ready( function ( $ ) {

	var $currentDelete;
	var $pointsTypeName = $( '#settings [name=points-name]' );

	// Require confirmation for points type delete.
	$( '#settings .delete' ).click( function( event ) {

		if ( ! $currentDelete ) {

			$currentDelete = $( this );

			event.preventDefault();

			$( '<div></div>' )
				.attr( 'title', WordPointsPointsTypesL10n.confirmTitle )
				.append( $( '<p></p>' ).text( WordPointsPointsTypesL10n.confirmAboutTo ) )
				.append( $( '<p></p>' ).append( $( '<b></b>' ).text( $pointsTypeName.val() ) ) )
				.append( $( '<p></p>' ).text( WordPointsPointsTypesL10n.confirmDelete ) )
				.append( $( '<p></p>' ).text( WordPointsPointsTypesL10n.confirmType ) )
				.append(
					$( '<label></label>' )
						.text( WordPointsPointsTypesL10n.confirmLabel + ' ' )
						.append(
							$( '<input />' )
								.addClass( 'wordpoints-points-delete-confirm-input' )
								.attr( 'type', 'text' )
						)
				)
				.dialog({
					dialogClass: 'wp-dialog wordpoints-delete-type-dialog',
					resizable: false,
					draggable: false,
					height: 'auto',
					modal: true,
					buttons: [
						{
							text: WordPointsPointsTypesL10n.cancelText,
							'class': 'button-secondary',
							click: function() {
								$( this ).dialog( 'destroy' );
								$currentDelete = false;
							}
						},
						{
							text: WordPointsPointsTypesL10n.deleteText,
							'class': 'button-primary',
							click: function() {

								var $this = $( this );
								var typedName = $this
									.find( '.wordpoints-points-delete-confirm-input' )
									.val();

								$this.dialog( 'destroy' );

								if ( typedName === $pointsTypeName.val() ) {
									$currentDelete.click();
								}

								$currentDelete = false;
							}
						}
					]
				});
		}
	});

});

// EOF
