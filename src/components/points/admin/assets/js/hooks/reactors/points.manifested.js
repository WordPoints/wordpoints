(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{}],2:[function(require,module,exports){
var hooks = wp.wordpoints.hooks,
	data = wp.wordpoints.hooks.view.data.reactors;

hooks.on( 'init', function () {

	hooks.Reactors.add( new hooks.reactor.Points( data.points ) );

	if ( data.points_legacy ) {
		hooks.Reactors.add( new hooks.reactor.Points( data.points_legacy ) );
	}
});

hooks.reactor.Points = require( './points.js' );

},{"./points.js":1}]},{},[2]);
