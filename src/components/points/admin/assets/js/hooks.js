/**
 * Points Types hooks UI.
 *
 * Based on the widgets UI, obviously.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

/* global ajaxurl, isRtl, WordPointsHooksL10n, jQuery */

/**
 * @var object WordPointsHooks
 */
var WordPointsHooks;

(function ( $ ) {

WordPointsHooks = {

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	init : function() {

		var rem,
			the_id,
			self = this,
			chooser = $( '.hooks-chooser' ),
			selectPointsType = chooser.find( '.hooks-chooser-points-types' ),
			points_types = $( 'div.hooks-sortables' ),
			isRTL = !! ( 'undefined' !== typeof isRtl && isRtl ),
			margin = ( isRTL ? 'marginRight' : 'marginLeft' ),
			$currentDelete = false;

		// Require confirmation for points type delete.
		$( '.points-settings .delete' ).click( function( event ) {

			if ( ! $currentDelete ) {

				$currentDelete = $( this );

				event.preventDefault();

				$( '<div title="' + WordPointsHooksL10n.confirmTitle + '"><p>' + WordPointsHooksL10n.confirmDelete + '</p></div>' ).dialog({
					dialogClass: 'wp-dialog wordpoints-delete-type-dialog',
					resizable: false,
					draggable: false,
					height: 250,
					modal: true,
					buttons: [
						{
							text: WordPointsHooksL10n.deleteText,
							'class': 'button-primary',
							click: function() {
								$( this ).dialog( 'close' );
								$currentDelete.click();
								$currentDelete = false;
							}
						},
						{
							text: WordPointsHooksL10n.cancelText,
							'class': 'button-secondary',
							click: function() {
								$( this ).dialog( 'close' );
								$currentDelete = false;
							}
						}
					]
				});
			}
		});

		$( '#hooks-right' ).children( '.hooks-holder-wrap' ).children( '.points-type-name' ).click( function () {

			var $this = $( this ),
				parent = $this.parent();

			if ( parent.hasClass( 'closed' ) ) {

				parent.removeClass( 'closed' );
				$this.siblings( '.hooks-sortables' ).sortable( 'refresh' );

			} else {

				parent.addClass( 'closed' );
			}
		});

		// Open/close points type on click.
		$( '#hooks-left' ).children( '.hooks-holder-wrap' ).children( '.points-type-name' ).click( function () {

			$( this ).parent().toggleClass( 'closed' );
		});

		// Set the height of the points types.
		points_types.each( function () {

			if ( $( this ).parent().hasClass( 'inactive' ) ) {
				return true;
			}

			var h = 50,
				H = $( this ).children( '.hook' ).length;

			h = h + parseInt( H * 48, 10 );
			$( this ).css( 'minHeight', h + 'px' );
		});

		// Let hooks toggle.
		$( document.body ).bind( 'click.hooks-toggle', function ( e ) {

			var target = $( e.target ),
				css = {},
				hook,
				inside,
				w;

			if ( target.parents( '.hook-top' ).length && ! target.parents( '#available-hooks' ).length ) {

				hook = target.closest( 'div.hook' );
				inside = hook.children( '.hook-inside' );
				w = parseInt( hook.find( 'input.hook-width' ).val(), 10 );

				if ( inside.is( ':hidden' ) ) {

					if ( w > 250 && inside.closest( 'div.hooks-sortables' ).length ) {

						css.width = w + 30 + 'px';

						if ( inside.closest( 'div.hook-liquid-right' ).length ) {
							css[ margin ] = 235 - w + 'px';
						}

						hook.css( css );
					}

					WordPointsHooks.fixLabels( hook );
					inside.slideDown( 'fast' );

				} else {

					inside.slideUp( 'fast', function () {

						hook.css( { 'width':'', margin:'' } );
					});
				}

				e.preventDefault();

			} else if ( target.hasClass( 'hook-control-save' ) ) {

				if ( ! target.parent().parent().parent().parent().hasClass( 'wordpoints-points-add-new' ) ) {

					WordPointsHooks.save( target.closest( 'div.hook' ), 0, 1, 0 );
					e.preventDefault();
				}

			} else if ( target.hasClass( 'hook-control-remove' ) ) {

				WordPointsHooks.save( target.closest( 'div.hook' ), 1, 1, 0 );
				e.preventDefault();

			} else if ( target.hasClass( 'hook-control-close' ) ) {

				WordPointsHooks.close( target.closest( 'div.hook' ) );
				e.preventDefault();
			}
		});

		// Append titles to hook names when provided.
		points_types.children( '.hook' ).each( function () {

			WordPointsHooks.appendTitle( this );

			if ( $( 'p.hook-error', this ).length ) {
				$( 'a.hook-action', this ).click();
			}
		});

		// Make hooks draggable.
		$( '#hook-list' ).children( '.hook' ).draggable({
			connectToSortable: 'div.hooks-sortables',
			handle: '> .hook-top > .hook-title',
			distance: 2,
			helper: 'clone',
			zIndex: 100,
			containment: 'document',
			start: function ( event, ui ) {

				var chooser = $( this ).find( '.hooks-chooser' );

				ui.helper.find( 'div.hook-description' ).hide();
				the_id = this.id;

				if ( chooser.length ) {
					// Hide the chooser and move it out of the hook.
					$( '#wpbody-content' ).append( chooser.hide() );
					// Delete the cloned chooser from the drag helper.
					ui.helper.find( '.hooks-chooser' ).remove();
					self.clearHookSelection();
				}
			},
			stop: function () {

				if ( rem ) {
					$( rem ).hide();
				}

				rem = '';
			}
		});

		// Make hooks sortable.
		points_types.sortable({
			placeholder: 'hook-placeholder',
			items: '> .hook:not( .points-settings )',
			handle: '> .hook-top > .hook-title',
			cursor: 'move',
			distance: 2,
			containment: 'document',
			start: function ( event, ui ) {

				var height, $this = $(this),
					$wrap = $this.parent(),
					inside = ui.item.children( '.hook-inside' );

				if ( inside.css( 'display' ) === 'block' ) {
					inside.hide();
					$( this ).sortable( 'refreshPositions' );
				}

				if ( ! $wrap.hasClass('closed') ) {

					// Lock all open points types' min-height when starting to drag.
					// Prevents jumping when dragging a hook from an open points type to a closed points type below.
					height = ( ui.item.hasClass('ui-draggable') ) ? $this.height() : 1 + $this.height();
					$this.css( 'min-height', height + 'px' );
				}
			},
			stop: function ( event, ui ) {

				var addNew, hookNumber, $pointsType, $children, child, item,
				$hook = ui.item,
				id = the_id;

				if ( $hook.hasClass('deleting') ) {
					self.save( $hook, 1, 0, 1 ); // delete hook
					$hook.remove();
					return;
				}

				addNew = $hook.find('input.add_new').val();
				hookNumber = $hook.find('input.multi_number').val();

				$hook.attr( 'style', '' ).removeClass( 'ui-draggable' );
				the_id = '';

				if ( addNew ) {
					if ( 'multi' === addNew ) {

						$hook.html(
							$hook.html().replace( /<[^<>]+>/g, function( tag ) {
								return tag.replace( /__i__|%i%/g, hookNumber );
							})
						);

						$hook.attr( 'id', id.replace( '__i__', hookNumber ) );
						hookNumber++;

						$( 'div#' + id ).find( 'input.multi_number' ).val( hookNumber );

					} else if ( 'single' === addNew ) {

						$hook.attr( 'id', 'new-' + id );
						rem = 'div#' + id;
					}

					self.save( $hook, 0, 0, 1 );
					$hook.find('input.add_new').val('');
				}

				$pointsType = $hook.parent();

				if ( $pointsType.parent().hasClass('closed') ) {

					$pointsType.parent().removeClass( 'closed' );
					$children = $pointsType.children( '.hook' );

					// Make sure the dropped hook is at the top.
					if ( $children.length > 1 ) {

						child = $children.get(0);
						item = $hook.get(0);

						if ( child.id && item.id && child.id !== item.id ) {
							$( child ).before( $hook );
						}
					}
				}

				if ( addNew ) {
					$hook.find( 'a.hook-action' ).trigger('click');
				} else {
					self.saveOrder( $pointsType.attr('id') );
				}
			},

			activate: function() {
				$(this).parent().addClass( 'hook-hover' );
			},

			deactivate: function() {
				// Remove all min-height added on "start"
				$(this).css( 'min-height', '' ).parent().removeClass( 'hook-hover' );
			}

		}).sortable( 'option', 'connectWith', 'div.hooks-sortables' );

		// Make available hooks droppable.
		$( '#available-hooks' ).droppable({
			tolerance: 'pointer',
			accept: function ( o ) {

				return $( o ).parent().attr( 'id' ) !== 'hook-list';
			},
			drop: function ( e, ui ) {

				ui.draggable.addClass( 'deleting' );
				$( '#removing-hook' ).hide().children( 'span' ).html( '' );
			},
			over: function ( e, ui ) {

				ui.draggable.addClass( 'deleting' );
				$( 'div.hook-placeholder' ).hide();

				if ( ui.draggable.hasClass( 'ui-sortable-helper' ) ) {
					$( '#removing-hook' ).show().children( 'span' )
						.html( ui.draggable.find( 'div.hook-title' ).children( 'h4' ).html() );
				}
			},
			out: function ( e, ui ) {

				ui.draggable.removeClass( 'deleting' );
				$( 'div.hook-placeholder' ).show();
				$( '#removing-hook' ).hide().children( 'span' ).html( '' );
			}
		});

		// Points type chooser.
		$( '#hooks-right .hooks-holder-wrap' ).each( function( index, element ) {

			var $element = $( element ),
				name = $element.find( '.points-type-name h3' ).text(),
				id = $element.find( '.hooks-sortables' ).attr( 'id' ),
				li = $('<li tabindex="0">').text( $.trim( name ) );

			if ( $element.hasClass( 'new-points-type' ) ) {
				return;
			}

			if ( index === 0 ) {
				li.addClass( 'hooks-chooser-selected' );
			}

			selectPointsType.append( li );
			li.data( 'pointsTypeId', id );
		});

		$( '#available-hooks .hook .hook-title' ).on( 'click.hooks-chooser', function() {

			var hook = $(this).closest( '.hook' );

			if ( hook.hasClass( 'hook-in-question' ) || ( $( '#hooks-left' ).hasClass( 'chooser' ) ) ) {

				self.closeChooser();

			} else {

				// Open the chooser.
				self.clearHookSelection();
				$( '#hooks-left' ).addClass( 'chooser' );

				hook.addClass( 'hook-in-question' ).children( '.hook-description' ).after( chooser );

				chooser.slideDown( 300, function() {
					selectPointsType.find( '.hooks-chooser-selected' ).focus();
				});

				selectPointsType.find( 'li' ).on( 'focusin.hooks-chooser', function() {
					selectPointsType.find( '.hooks-chooser-selected' ).removeClass( 'hooks-chooser-selected wp-ui-highlight' );
					$( this ).addClass( 'hooks-chooser-selected wp-ui-highlight' );
				});
			}
		});

		// Add event handlers.
		chooser.on( 'click.hooks-chooser', function( event ) {

			var $target = $( event.target );

			if ( $target.hasClass('button-primary') ) {

				self.addHook( chooser );
				self.closeChooser();

			} else if ( $target.hasClass('button-secondary') ) {

				self.closeChooser();
			}

		}).on( 'keyup.hooks-chooser', function( event ) {

			if ( event.which === $.ui.keyCode.ENTER ) {

				if ( $( event.target ).hasClass('button-secondary') ) {
					// Close instead of adding when pressing Enter on the Cancel button
					self.closeChooser();
				} else {
					self.addHook( chooser );
					self.closeChooser();
				}

			} else if ( event.which === $.ui.keyCode.ESCAPE ) {

				self.closeChooser();
			}
		});
	},

	/**
	 * Save hook display order.
	 *
	 * @since 1.0.0
	 */
	saveOrder : function ( sb ) {
		if ( sb ) {
			$( '#' + sb ).closest( 'div.hooks-holder-wrap' ).find( '.spinner:first' ).css( 'display', 'inline-block' );
		}

		var a = {
			action: 'wordpoints-points-hooks-order',
			savehooks: $( '#_wpnonce_hooks' ).val(),
			points_types: []
		};

		$( 'div.hooks-sortables' ).each( function () {

			if ( $( this ).sortable ) {
				a['points_types[' + $( this ).attr( 'id' ) + ']'] = $( this ).sortable( 'toArray' ).join( ',' );
			}
		});

		$.post( ajaxurl, a, function() {

			$( '.spinner ').hide();
		});

		this.resize();
	},

	/**
	 * Save hook settings.
	 *
	 * @since 1.0.0
	 */
	save : function ( hook, del, animate, order ) {
		var sb = hook.closest( 'div.hooks-sortables' ).attr( 'id' ),
			data = hook.find( 'form' ).serialize(),
			a;

		hook = $( hook );
		$( '.spinner', hook ).show();

		a = {
			action: 'save-wordpoints-points-hook',
			savehooks: $( '#_wpnonce_hooks' ).val(),
			points_type: sb
		};

		if ( del ) {
			a.delete_hook = 1;
		}

		data += '&' + $.param( a );

		$.post( ajaxurl, data, function ( r ) {

			var id;

			if ( del ) {

				if ( ! $( 'input.hook_number', hook ).val() ) {

					id = $( 'input.hook-id', hook ).val();
					$( '#available-hooks' ).find( 'input.hook-id' ).each( function () {

						if ( $( this ).val() === id ) {
							$( this ).closest( 'div.hook' ).show();
						}
					});
				}

				if ( animate ) {

					order = 0;
					hook.slideUp( 'fast', function () {

						$( this ).remove();
						WordPointsHooks.saveOrder();
					});

				} else {

					hook.remove();
					WordPointsHooks.resize();
				}

			} else {

				$( '.spinner' ).hide();

				if ( r && r.length > 2 ) {

					$( 'div.hook-content', hook ).html( r );
					WordPointsHooks.appendTitle( hook );
					WordPointsHooks.fixLabels( hook );
				}
			}

			if ( order ) {
				WordPointsHooks.saveOrder();
			}
		});
	},

	/**
	 * Append the main setting value to the hook title bar.
	 *
	 * @since 1.0.0
	 *
	 * @param {object} hook - The DOM object for the hook whose title to append.
	 */
	appendTitle : function ( hook ) {

		var title, $title_append;

		$title_append = $( '.wordpoints-append-to-hook-title', hook );

		if ( $title_append.length === 0 ) {

			// Back-compat.
			title = $( 'input[id*="-title"]', hook ).val();

			if ( ! title ) {
				return;
			}

		} else {

			if ( $title_append.is( 'select' ) ) {
				title = $title_append.find( ':selected' ).text();
			} else {
				title = $title_append.val();
			}
		}

		$( '.in-hook-title', hook ).text( ': ' + title );
	},

	/**
	 * Resize the hook box.
	 *
	 * @since 1.0.0
	 */
	resize : function () {

		$( 'div.hooks-sortables' ).each( function () {

			if ( $( this ).parent().hasClass( 'inactive' ) ) {
				return true;
			}

			var h = 50, H = $( this ).children( '.hook' ).length;
			h = h + parseInt( H * 48, 10 );
			$( this ).css( 'minHeight', h + 'px' );
		});
	},

	/**
	 * Fix label element 'for' attributes.
	 *
	 * @since 1.0.0
	 */
	fixLabels : function ( hook ) {

		hook.children( '.hook-inside' ).find( 'label' ).each( function () {

			var f = $( this ).attr( 'for' );

			if ( f && f === $( 'input', this ).attr( 'id' ) ) {
				$( this ).removeAttr( 'for' );
			}
		});
	},

	/**
	 * Close the hook box.
	 *
	 * @since 1.0.0
	 */
	close : function ( hook ) {

		hook.children( '.hook-inside' ).slideUp( 'fast', function () {

			hook.css( { 'width':'', margin:'' } );
		});
	},

	/**
	 * Add a hook via the chooser.
	 *
	 * @since 1.1.0
	 */
	addHook: function( chooser ) {

		var hook,
			hookId,
			add,
			n,
			pointsTypeId = chooser.find( '.hooks-chooser-selected' ).data( 'pointsTypeId' ),
			pointsType = $( '#' + pointsTypeId );

		hook = $( '#available-hooks' ).find( '.hook-in-question' ).clone();
		hookId = hook.attr('id');
		add = hook.find( 'input.add_new' ).val();
		n = hook.find( 'input.multi_number' ).val();

		// Remove the cloned chooser from the hook.
		hook.find('.hooks-chooser').remove();

		if ( 'multi' === add ) {

			hook.html(
				hook.html().replace( /<[^<>]+>/g, function( m ) {
					return m.replace( /__i__|%i%/g, n );
				})
			);

			hook.attr( 'id', hookId.replace( '__i__', n ) );
			n++;
			$( '#' + hookId ).find( 'input.multi_number' ).val(n);

		} else if ( 'single' === add ) {

			hook.attr( 'id', 'new-' + hookId );
			$( '#' + hookId ).hide();
		}

		// Open the hooks container
		pointsType.closest( '.hooks-holder-wrap' ).removeClass( 'closed' );
		pointsType.sortable( 'refresh' );
		pointsType.find( '.points-hooks-settings-separator' ).after( hook );

		WordPointsHooks.save( hook, 0, 0, 1 );

		// No longer "new" hook
		hook.find( 'input.add_new' ).val( '' );

		/*
		 * Check if any part of the sidebar is visible in the viewport. If it is, don't scroll.
		 * Otherwise, scroll up to so the sidebar is in view.
		 *
		 * We do this by comparing the top and bottom, of the sidebar so see if they are within
		 * the bounds of the viewport.
		 */
		var viewport_top = $(window).scrollTop(),
			viewport_bottom = viewport_top + $(window).height(),
			pointsTypeBounds = pointsType.offset();

		pointsTypeBounds.bottom = pointsTypeBounds.top + pointsType.outerHeight();

		if ( viewport_top > pointsTypeBounds.bottom || viewport_bottom < pointsTypeBounds.top ) {
			$( 'html, body' ).animate({
				scrollTop: pointsType.offset().top - 130
			}, 200 );
		}

		window.setTimeout( function() {
			// Cannot use a callback in the animation above as it fires twice,
			// have to queue this "by hand".
			hook.find( '.hook-title' ).trigger( 'click' );
		}, 250 );
	},

	/**
	 * Close the points type chooser.
	 *
	 * @since 1.1.0
	 */
	closeChooser: function() {

		var self = this;

		$( '.hooks-chooser' ).slideUp( 200, function() {
			$( '#wpbody-content' ).append( this );
			self.clearHookSelection();
		});
	},

	/**
	 * Clear the hook selection.
	 *
	 * @since 1.1.0
	 */
	clearHookSelection: function() {

		$( '#hooks-left' ).removeClass( 'chooser' );
		$( '.hook-in-question' ).removeClass( 'hook-in-question' );
	}
};

$( document ).ready( function() { WordPointsHooks.init(); } );

})(jQuery);
