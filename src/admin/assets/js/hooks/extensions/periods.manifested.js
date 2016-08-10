(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var hooks = wp.wordpoints.hooks;

// Controllers.
hooks.extension.Periods = require( './periods/controllers/extension.js' );

var Periods = new hooks.extension.Periods();

// Register the extension.
hooks.Extensions.add( Periods );

// EOF

},{"./periods/controllers/extension.js":2}],2:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.extension.Periods
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	Fields = wp.wordpoints.hooks.Fields,
	Args = wp.wordpoints.hooks.Args,
	template = wp.wordpoints.hooks.template,
	$ = Backbone.$,
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

			var $periods = $( '<div></div>' ).html( this.template() );

			var name = [ 'periods', currentActionType, 0, 'length' ];

			var label = reaction.Reactor.get( 'periods_label' );

			if ( ! label ) {
				label = this.data.l10n.label;
			}

			$periods.find( '.periods' ).html(
				Fields.create(
					name
					, reaction.model.get( name )
					, {
						type: 'select',
						options: this.data.periods,
						label: label
					}
				)
			);

			$el.append( $periods.html() );
		});
	},

	showForReaction: function ( reaction ) {
		return Args.isEventRepeatable( reaction.model.get( 'event' ) );
	}

} );

module.exports = Periods;

},{}]},{},[1]);
