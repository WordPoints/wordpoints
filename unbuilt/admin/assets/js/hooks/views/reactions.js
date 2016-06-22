/**
 * wp.wordpoints.hooks.view.Hooks
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	ReactionView = wp.wordpoints.hooks.view.Reaction,
	Reaction = wp.wordpoints.hooks.model.Reaction,
	Reactions;

Reactions = Base.extend({

	namespace: 'reactions',

	// Delegated events for creating new reactions.
	events: {
		'click .add-reaction': 'initAddReaction'
	},

	// At initialization we bind to the relevant events on the `Reactions`
	// collection, when items are added or changed. Kick things off by
	// loading any preexisting hooks from *the database*.
	initialize: function() {

		this.$reactionGroup = this.$( '.wordpoints-hook-reaction-group' );
		this.$addReaction   = this.$( '.add-reaction' );
		this.$events = this.$( '.wordpoints-hook-events' );

		if ( this.$events.length !== 0 ) {
			// Check how many different events this group supports. If it is only
			// one, we can hide the event selector.
			if ( 2 === this.$events.children( 'option' ).length ) {
				this.$events.prop( 'selectedIndex', 1 ).hide();
			}
		}

		// Make sure that the add reaction button isn't disabled, because sometimes
		// the browser will automatically disable it, e.g., if it was disabled
		// and the page was refreshed.
		this.$addReaction.prop( 'disabled', false );

		this.listenTo( this.model, 'add', this.addOne );
		this.listenTo( this.model, 'reset', this.addAll );
		this.listenTo( this.model, 'cancel-add-new', this.cancelAddReaction );

		this.addAll();
	},

	// Add a single reaction to the group by creating a view for it, and appending
	// its element to the group. If this is a new reaction we enter edit mode from
	// and lock the view open until it is saved.
	addOne: function( reaction ) {

		var view = new ReactionView( { model: reaction } ),
			element = view.render().el;

		if ( '' === reaction.get( 'description' ) ) {
			view.edit();
			view.lockOpen();
			view.$el.addClass( 'new' );
		}

		// Append the element to the group.
		this.$reactionGroup.append( element );
	},

	// Add all items in the **Reactions** collection at once.
	addAll: function() {
		this.model.each( this.addOne, this );

		this.$( '.spinner-overlay' ).fadeOut();
	},

	getReactionDefaults: function () {

		var defaults = {};

		if ( this.$events.length !== 0 ) {

			// First, be sure that an event was selected.
			var event = this.$events.val();

			if ( '0' === event ) {
				// Show an error.
			}

			defaults.event = event;
			defaults.nonce = this.$events
				.find(
					'option[value="' + event.replace( /[^a-z0-9-_]/gi, '' ) + '"]'
				)
				.data( 'nonce' );

		} else {

			defaults.event = this.$reactionGroup.data( 'wordpoints-hooks-hook-event' );
			defaults.nonce = this.$reactionGroup.data( 'wordpoints-hooks-create-nonce' );
		}

		defaults.reactor = this.$reactionGroup.data( 'wordpoints-hooks-reactor' );
		defaults.reaction_store = this.$reactionGroup.data( 'wordpoints-hooks-reaction-store' );

		this.trigger( 'hook-reaction-defaults', defaults, this );

		return defaults;
	},

	// Show the form for a new reaction.
	initAddReaction: function () {

		var data = this.getReactionDefaults();

		this.$addReaction.prop( 'disabled', true );

		var reaction = new Reaction( data );

		this.model.add( [ reaction ] );

		// Re-enable the submit button when a new reaction is saved.
		this.listenToOnce( reaction, 'sync', function () {
			this.$addReaction.prop( 'disabled', false );
		});
	},

	// When a new reaction is removed, re-enable the add reaction button.
	cancelAddReaction: function () {
		this.$addReaction.prop( 'disabled', false );
	}
});

module.exports = Reactions;
