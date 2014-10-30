/**
 * Backbone code for the ranks screen.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/* global jQuery, Backbone, WordPointsRanksAdminL10n, WordPointsRanksAdminData, _ */

window.wp = window.wp || {};

// Load the application once the DOM is ready, using `jQuery.ready`:
jQuery( function ( $ ) {

	var ranks, Ranks;

	// Add a `wordpoints` namespace to the global `wp` object.
	wp.wordpoints = wp.wordpoints || {};

	// Add a `ranks` namespace to the WordPoints object, and create a local
	// shortcut to it.
	ranks = wp.wordpoints.ranks = { model: {}, view: {}, controller: {} };

	// Make the translated strings accessible as well.
	ranks.l10n = WordPointsRanksAdminL10n;

	// Make other data accessible as well.
	ranks.data = WordPointsRanksAdminData;

	// Rank Model
	// ----------

	// Our basic **Rank** model has `name` and `order` attributes.
	wp.wordpoints.ranks.model.Rank = Backbone.Model.extend({

		// Default attributes for the rank.
		defaults: function() {
			return {
				name:  '',
				order: Ranks.nextOrder()
			};
		},

		// Validates the rank attributes before saving it.
		validate: function ( attributes ) {

			if ( '' === $.trim( attributes.name ) ) {
				return { field: 'name', message: ranks.l10n.emptyName };
			}

			attributes.order = parseInt( attributes.order, 10 );

			if ( isNaN( attributes.order ) || attributes.order < 0 ) {
				attributes.order = Ranks.nextOrder();
			}
		},

		// Override the default sync method to use WordPress's Ajax API.
		sync: function ( method, model, options ) {
			return sync( method, model, options, this );
		}
	});

	// Rank Collection
	// ---------------

	ranks.model.RankGroup = Backbone.Collection.extend({

		// Reference to this collection's model.
		model: ranks.model.Rank,

		// We keep the Ranks in sequential order, since they are stored by order
		// in the database. This generates the next order number for new items.
		nextOrder: function() {

			if ( ! this.length ) {
				return 0;
			}

			return parseInt( this.sort().last().get( 'order' ), 10 ) + 1;
		},

		// Ranks are sorted by their original insertion order.
		comparator: 'order',

		// We do_it_right() and use WordPress's Ajax API, so we need to override the
		// synchronizer with our own function.
		sync: function ( method, collection, options ) {
			return sync( method, collection, options, this );
		}
	});

	// Create our global collection of **Ranks**.
	Ranks = new ranks.model.RankGroup();

	// Rank Type Model
	// ---------------

	ranks.model.RankType = Backbone.Model.extend({

		// Default attributes for the rank type.
		defaults: function() {
			return {
				name: '',
			};
		},
	});

	// Rank View
	// ---------

	// The DOM element for a rank...
	ranks.view.Rank = Backbone.View.extend({

		//... is a list tag.
		tagName: 'li',

		// The DOM events specific to an item.
		events: {
			'click .delete': 'confirmDelete',
			'click .save':   'save',
			'click .cancel': 'cancel',
			'click .close':  'close',
			'click .edit':   'edit',
			'change form *': 'lockOpen',
			'keyup input':   'maybeLockOpen'
		},

		// The Rank view listens for changes to its model, re-rendering. Since there's
		// a one-to-one correspondence between a **Rank** and a **Rank view** in this
		// app, we set a direct reference on the model for convenience.
		initialize: function () {

			this.listenTo( this.model, 'change', this.render );
			this.listenTo( this.model, 'destroy', this.remove );
			this.listenTo( this.model, 'sync', this.showSuccess );
			this.listenTo( this.model, 'error', this.showError );
			this.listenTo( this.model, 'invalid', this.showError );

			this.template = _.template(
				$( '.rank-template_' + this.model.get( 'type' ).replace( /[^a-z0-9-_]/gi, '' ) )
					.html()
			);
		},

		// Re-render the titles of the rank.
		render: function () {

			this.$el
				.html( this.template( this.model.toJSON() ) )
				.addClass( 'wordpoints-rank' )
				.removeClass( 'changed' );

			return this;
		},

		// Toggle the visibility of the form.
		edit: function () {

			this.$( 'form' ).slideDown( 'fast' );
			this.$el.addClass( 'editing' );
		},

		// Close the form.
		close: function ( event ) {

			event.preventDefault();

			this.$( 'form' ).slideUp( 'fast' );
			this.$el.removeClass( 'editing' );
			this.$( '.success' ).hide();
		},

		// Maybe lock the form open when an input is altered.
		maybeLockOpen: function ( event ) {

			var $target = $( event.target );

			if ( $target.val() !== this.model.get( $target.attr( 'name' ) ) ) {
				this.lockOpen();
			}
		},

		// Lock the form open when the form values have been changed.
		lockOpen: function () {

			this.$el.addClass( 'changed' );
			this.$( '.save' ).prop( 'disabled', false );
			this.$( '.success' ).fadeOut();
		},

		cancel: function ( event ) {

			event.preventDefault();

			if ( this.$el.hasClass( 'new' ) ) {
				Ranks.trigger( 'cancel-add-new' );
				this.remove();
				return;
			}

			this.render();
		},

		// Save changes to the rank.
		save: function ( event ) {

			event.preventDefault();

			this.wait();
			this.$( '.save' ).prop( 'disabled', true );

			this.model.save( getFormData( this.$( 'form' ) ), { wait: true } );
		},

		// Display a spinner while changes are being saved.
		wait: function () {

			this.$( '.spinner-overlay' ).show();
			this.$( '.err' ).slideUp();
		},

		// Confirm that a rank is intended to be deleted before deleting it.
		confirmDelete: function ( event ) {

			var $dialog = $( '<div><p></p></div>' ),
				view = this;

			event.preventDefault();

			$dialog
				.attr( 'title', ranks.l10n.confirmTitle )
				.find( 'p' )
					.text( ranks.l10n.confirmDelete )
				.end()
				.dialog({
					dialogClass: 'wp-dialog wordpoints-delete-rank-dialog',
					resizable: false,
					draggable: false,
					height: 250,
					modal: true,
					buttons: [
						{
							text: ranks.l10n.deleteText,
							class: 'button-primary',
							click: function() {
								$( this ).dialog( 'close' );
								view.delete();
							}
						},
						{
							text: ranks.l10n.cancelText,
							class: 'button-secondary',
							click: function() {
								$( this ).dialog( 'close' );
							}
						}
					]
				});
		},

		// Remove the item, destroy the model.
		delete: function () {

			this.wait();

			this.model.destroy( { wait: true } );
		},

		// Display an error when there is an Ajax failure.
		showError: function ( event, response, options ) {

			var message, $error, $field;

			this.$( '.spinner-overlay' ).hide();

			// We check for the `collection` property here to be sure that this
			// request was in the context of a specific model. Errors for requests
			// general to the whole collection are handled by the `Group` view. But
			// for new model, the collection isn't set, so we also have to make
			// sure that this isn't a new model.
			if ( options.context && ! options.context.collection && ( ! options.context.isNew || ! options.context.isNew() ) ) {
				return;
			}

			$error  = this.$( '.messages .err' );
			message = ranks.l10n.unexpectedError;

			if ( response.message ) {

				message = response.message;

				// Check if the error is for a specific field.
				if ( response.data && response.data.field ) {

					$field = this.$(
						'[name="' + response.data.field.replace( /[^a-z0-9-_]/gi, '' ) + '"]'
					);

					// If this field actually exists, insert an error before it.
					if ( 0 !== $field.length ) {

						$field.before(
							$( '<div class="message err"></div>' )
								.text( message )
								.show()
						);

						return;
					}
				}
			}

			$error.text( message ).fadeIn();
		},

		// Display a success message.
		showSuccess: function () {

			this.$( '.success' )
				.text( ranks.l10n.changesSaved )
				.slideDown();

			this.$el.removeClass( 'new' );
		}
	});

	// Group View
	// ----------

	// Our overall **Group** is the top-level piece of UI.
	ranks.view.Group = Backbone.View.extend({

		// Instead of generating a new element, bind to the existing skeleton of
		// the group already present in the HTML.
		el: $( '.wordpoints-rank-group-container' ),

		// Delegated events for creating new items, and clearing completed ones.
		events: {
			'click .add-rank': 'initAddRank'
		},

		// At initialization we bind to the relevant events on the `Ranks`
		// collection, when items are added or changed. Kick things off by
		// loading any preexisting ranks from *the database*.
		initialize: function() {

			this.$addRank = this.$( '.add-rank' );
			this.$rankTypes = this.$( '.wordpoints-rank-types' );

			// Check how many different rank types this group supports. If it is only
			// one, we can hide the rank type selector.
			if ( 2 === this.$rankTypes.children( 'option' ).length ) {
				this.$rankTypes.prop( 'selectedIndex', 1 ).hide();
			}

			// Make sure that the add rank button isn't disabled, because sometimes
			// the browser will automatically disable it, e.g., if it was disabled
			// and the page was refreshed.
			this.$addRank.prop( 'disabled', false );

			this.listenTo( Ranks, 'add', this.addOne );
			this.listenTo( Ranks, 'reset', this.addAll );
			this.listenTo( Ranks, 'error', this.showError );
			this.listenTo( Ranks, 'cancel-add-new', this.cancelAddRank );
			this.listenTo( Ranks, 'sync', this.cancelAddRank );

			Ranks.reset(
				ranks.data.ranks[ this.$( '.wordpoints-rank-group' ).data( 'slug' ) ]
			);
		},

		// Add a single rank to the group by creating a view for it, and appending
		// its element to the `<ul>`. If this is a new rank we enter edit mode from
		// and lock the view open until it is saved.
		addOne: function( rank ) {

			var view = new ranks.view.Rank( { model: rank } ),
				element = view.render().el;

			if ( '' === rank.get( 'name' ) ) {
				view.edit();
				view.lockOpen();
				view.$el.addClass( 'new' );
			}

			// Append the element to the group.
			this.$( '.wordpoints-rank-group' ).append( element );
		},

		// Add all items in the **Ranks** collection at once.
		addAll: function() {
			Ranks.each( this.addOne, this );

			this.$( '.spinner-overlay' ).fadeOut();
		},

		// Show the form for a new rank.
		initAddRank: function () {

			var rankType, nonce;

			// First, be sure that a rank type was selected.
			rankType = this.$rankTypes.val();

			if ( '0' === rankType ) {
				// Show an error.
			}

			this.$addRank.prop( 'disabled', true );

			nonce = this.$rankTypes
				.find(
					'option[value="' + rankType.replace( /[^a-z0-9-_]/gi, '' ) + '"]'
				)
				.data( 'nonce' );

			Ranks.add(
				[
					new ranks.model.Rank(
						{
							type: rankType,
							nonce: nonce
						}
					)
				]
			);
		},

		// When a new rank is removed, re-enable the add rank button.
		cancelAddRank: function () {
			this.$addRank.prop( 'disabled', false );
		},

		// Display an error when an Ajax request fails.
		showError: function ( event, response, options ) {

			// We only show an error if this request wasn't specific to a model.
			// Models have the `collection` attribute, so we check for that. If it's
			// missing, we know that request was in context of the collection
			// generally, not a specific model.
			if ( options.context.collection ) {
				return;
			}

			$( '#message.error p' )
				.text(
					response.message || ranks.l10n.unexpectedError
				)
				.parent()
					.fadeIn();
		}
	});

	// Finally, we kick things off by creating the **Group**.
	new ranks.view.Group();

	// Utility Functions
	// -----------------

	// Get the data from a from as key => value pairs.
	function getFormData( $form ) {

		var formObj = {},
			inputs = $form.serializeArray();

		$.each( inputs, function ( i, input ) {
			formObj[ input.name ] = input.value;
		});

		return formObj;
	}

	// Synchronize the data with the database.
	function sync( method, model, options, context ) {

		var deferred;

		options = options || {};
		options.context = context;
		options.data = _.extend( options.data || {}, {
			group: $( '.wordpoints-rank-group' ).data( 'slug' )
		});

		switch ( method ) {
			case 'read':
				options.data.action = 'wordpoints_admin_get_ranks';
				options.data.nonce  = $( '.wordpoints-rank-group' ).data( 'nonce' );
			break;

			case 'create':
				options.data.action = 'wordpoints_admin_create_rank';
				options.data = _.extend( options.data, context.attributes );
			break;

			case 'update':
				options.data.action = 'wordpoints_admin_update_rank';
				options.data = _.extend( options.data, context.attributes );
			break;

			case 'delete':
				options.data.action = 'wordpoints_admin_delete_rank';
				options.data.id     = context.get( 'id' );
				options.data.nonce  = context.get( 'delete_nonce' );
			break;
		}

		deferred = wp.ajax.send( options );

		return deferred;
	}
});

// EOF
