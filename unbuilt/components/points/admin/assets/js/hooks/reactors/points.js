/**
 * wp.wordpoints.hooks.reactor.Points
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Reactor
 */
var Reactor = wp.wordpoints.hooks.controller.Reactor,
	Fields = wp.wordpoints.hooks.Fields,
	data = wp.wordpoints.hooks.view.data,
	Points;

Points = Reactor.extend({

	defaults: {
		slug: 'points',
		fields: {},
		reversals_extension_slug: 'reversals'
	},

	initReaction: function ( reaction ) {

		if ( reaction.model.get( 'reactor' ) !== this.get( 'slug' ) ) {
			return;
		}

		this.listenTo( reaction, 'render:settings', this.render );
	},

	render: function ( $el, currentActionType, reaction ) {

		var fields = '';

		_.forEach( this.get( 'fields' ), function ( field, name ) {

			fields += Fields.create(
				name,
				reaction.model.get( name ),
				field
			);
		});

		reaction.$settings.append( fields );
	},

	validateReaction: function ( model, attributes, errors ) {

		if ( attributes.reactor !== this.get( 'slug' ) ) {
			return;
		}

		Fields.validate(
			this.get( 'fields' )
			, attributes
			, errors
		);
	},

	filterReactionDefaults: function ( defaults, view ) {

		if ( defaults.reactor !== this.get( 'slug' ) ) {
			return;
		}

		defaults.points_type = view.$reactionGroup.data(
			'wordpoints-hooks-points-type'
		);

		// Have toggle events behave like reversals.
		if (
			defaults.event
			&& data.event_action_types[ defaults.event ]
			&& data.event_action_types[ defaults.event ].toggle_on
		) {
			defaults[ this.get( 'reversals_extension_slug' ) ] = {
				toggle_off: 'toggle_on'
			};
		}
	}
});

module.exports = Points;
