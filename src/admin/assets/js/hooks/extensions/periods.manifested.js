(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var hooks = wp.wordpoints.hooks;

// Views.
hooks.view.SimplePeriod = require( './periods/views/simple-period.js' );

// Controllers.
hooks.extension.Periods = require( './periods/controllers/extension.js' );

var Periods = new hooks.extension.Periods();

// Register the extension.
hooks.Extensions.add( Periods );

// EOF

},{"./periods/controllers/extension.js":2,"./periods/views/simple-period.js":3}],2:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.extension.Periods
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	SimplePeriod = wp.wordpoints.hooks.view.SimplePeriod,
	Args = wp.wordpoints.hooks.Args,
	template = wp.wordpoints.hooks.template,
	Periods;

Periods = Extension.extend({

	defaults: {
		slug: 'periods'
	},

	template: template( 'hook-periods' ),

	initReaction: function ( reaction ) {

		if ( ! this.showForReaction( reaction ) ) {
			return;
		}

		this.listenTo( reaction, 'render:fields', function ( $el, currentActionType ) {

			var simplePeriod = new SimplePeriod( {
				extension: this,
				reaction: reaction,
				actionType: currentActionType
			} );

			var $existingPeriods = $el.find( '.periods' ),
				$periods = simplePeriod.render().$el;

			if ( $existingPeriods.length ) {

				$existingPeriods.replaceWith( $periods.find( '.periods' ) );

			} else {

				$el.append( this.template() )
					.find( '.periods' )
					.append( $periods );
			}
		});
	},

	showForReaction: function ( reaction ) {
		return Args.isEventRepeatable( reaction.model.get( 'event' ) );
	}

} );

module.exports = Periods;

},{}],3:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.SimplePeriod
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	template = wp.wordpoints.hooks.template,
	Fields = wp.wordpoints.hooks.Fields,
	SimplePeriod;

SimplePeriod = Base.extend({

	namespace: 'period',

	className: 'wordpoints-hook-period',

	template: template( 'hook-reaction-simple-period' ),

	events: {
		'change .wordpoints-hook-period-sync': 'sync',
		'input .wordpoints-hook-period-sync': 'sync'
	},

	initialize: function ( options ) {

		this.extension = options.extension;
		this.reaction = options.reaction;
		this.actionType = options.actionType;
	},

	// Display the period settings form.
	render: function () {

		var name = [ this.extension.get( 'slug' ), this.actionType, 0, 'length' ];
		var periodLength = this.reaction.model.get( name );

		// Which units should this period be displayed in (second, minutes, &c.)?
		var unit, units;

		units = _.keys( this.extension.data.period_units ).sort( function ( a, b ) {
			return b - a; // We don't have to worry about NaN, etc.
		});

		for ( var i = 0; i < units.length; i++ ) {

			unit = units[ i ];

			if ( 0 === periodLength % unit ) {
				break;
			}
		}

		var template = this.template( {
			name: Fields.getFieldName( name ),
			length: periodLength,
			length_in_units: periodLength / unit,
			length_in_units_label: this.getPeriodLabel()
		} );

		template = Fields.createSelect(
			{ value: unit, options: this.extension.data.period_units }
			, template
		);

		this.$el.html( template );

		this.$length = this.$( '.wordpoints-hook-period-length' );
		this.$lengthInUnits = this.$( '.wordpoints-hook-period-length-in-units' );
		this.$units = this.$( '.wordpoints-hook-period-units' );

		this.trigger( 'render', this );

		return this;
	},

	getPeriodLabel: function () {

		var label = this.reaction.Reactor.get( 'periods_label' );

		if ( !label ) {
			label = this.extension.data.l10n.label;
		}

		return label;
	},

	sync: function () {
		this.$length.val( this.$lengthInUnits.val() * this.$units.val() );
	}

});

module.exports = SimplePeriod;

},{}]},{},[1]);
