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

				$existingPeriods.html( $periods );

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

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL3BlcmlvZHMubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL3BlcmlvZHMvY29udHJvbGxlcnMvZXh0ZW5zaW9uLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9wZXJpb2RzL3ZpZXdzL3NpbXBsZS1wZXJpb2QuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDZEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsInZhciBob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3M7XG5cbi8vIFZpZXdzLlxuaG9va3Mudmlldy5TaW1wbGVQZXJpb2QgPSByZXF1aXJlKCAnLi9wZXJpb2RzL3ZpZXdzL3NpbXBsZS1wZXJpb2QuanMnICk7XG5cbi8vIENvbnRyb2xsZXJzLlxuaG9va3MuZXh0ZW5zaW9uLlBlcmlvZHMgPSByZXF1aXJlKCAnLi9wZXJpb2RzL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcycgKTtcblxudmFyIFBlcmlvZHMgPSBuZXcgaG9va3MuZXh0ZW5zaW9uLlBlcmlvZHMoKTtcblxuLy8gUmVnaXN0ZXIgdGhlIGV4dGVuc2lvbi5cbmhvb2tzLkV4dGVuc2lvbnMuYWRkKCBQZXJpb2RzICk7XG5cbi8vIEVPRlxuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5QZXJpb2RzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uXG4gKi9cbnZhciBFeHRlbnNpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uLFxuXHRTaW1wbGVQZXJpb2QgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuU2ltcGxlUGVyaW9kLFxuXHRBcmdzID0gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLFxuXHR0ZW1wbGF0ZSA9IHdwLndvcmRwb2ludHMuaG9va3MudGVtcGxhdGUsXG5cdFBlcmlvZHM7XG5cblBlcmlvZHMgPSBFeHRlbnNpb24uZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdHNsdWc6ICdwZXJpb2RzJ1xuXHR9LFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stcGVyaW9kcycgKSxcblxuXHRpbml0UmVhY3Rpb246IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHRpZiAoICEgdGhpcy5zaG93Rm9yUmVhY3Rpb24oIHJlYWN0aW9uICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGhpcy5saXN0ZW5UbyggcmVhY3Rpb24sICdyZW5kZXI6ZmllbGRzJywgZnVuY3Rpb24gKCAkZWwsIGN1cnJlbnRBY3Rpb25UeXBlICkge1xuXG5cdFx0XHR2YXIgc2ltcGxlUGVyaW9kID0gbmV3IFNpbXBsZVBlcmlvZCgge1xuXHRcdFx0XHRleHRlbnNpb246IHRoaXMsXG5cdFx0XHRcdHJlYWN0aW9uOiByZWFjdGlvbixcblx0XHRcdFx0YWN0aW9uVHlwZTogY3VycmVudEFjdGlvblR5cGVcblx0XHRcdH0gKTtcblxuXHRcdFx0dmFyICRleGlzdGluZ1BlcmlvZHMgPSAkZWwuZmluZCggJy5wZXJpb2RzJyApLFxuXHRcdFx0XHQkcGVyaW9kcyA9IHNpbXBsZVBlcmlvZC5yZW5kZXIoKS4kZWw7XG5cblx0XHRcdGlmICggJGV4aXN0aW5nUGVyaW9kcy5sZW5ndGggKSB7XG5cblx0XHRcdFx0JGV4aXN0aW5nUGVyaW9kcy5odG1sKCAkcGVyaW9kcyApO1xuXG5cdFx0XHR9IGVsc2Uge1xuXG5cdFx0XHRcdCRlbC5hcHBlbmQoIHRoaXMudGVtcGxhdGUoKSApXG5cdFx0XHRcdFx0LmZpbmQoICcucGVyaW9kcycgKVxuXHRcdFx0XHRcdC5hcHBlbmQoICRwZXJpb2RzICk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH0sXG5cblx0c2hvd0ZvclJlYWN0aW9uOiBmdW5jdGlvbiAoIHJlYWN0aW9uICkge1xuXHRcdHJldHVybiBBcmdzLmlzRXZlbnRSZXBlYXRhYmxlKCByZWFjdGlvbi5tb2RlbC5nZXQoICdldmVudCcgKSApO1xuXHR9XG5cbn0gKTtcblxubW9kdWxlLmV4cG9ydHMgPSBQZXJpb2RzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuU2ltcGxlUGVyaW9kXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0RmllbGRzID0gd3Aud29yZHBvaW50cy5ob29rcy5GaWVsZHMsXG5cdFNpbXBsZVBlcmlvZDtcblxuU2ltcGxlUGVyaW9kID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ3BlcmlvZCcsXG5cblx0Y2xhc3NOYW1lOiAnd29yZHBvaW50cy1ob29rLXBlcmlvZCcsXG5cblx0dGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1yZWFjdGlvbi1zaW1wbGUtcGVyaW9kJyApLFxuXG5cdGV2ZW50czoge1xuXHRcdCdjaGFuZ2UgLndvcmRwb2ludHMtaG9vay1wZXJpb2Qtc3luYyc6ICdzeW5jJyxcblx0XHQnaW5wdXQgLndvcmRwb2ludHMtaG9vay1wZXJpb2Qtc3luYyc6ICdzeW5jJ1xuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggb3B0aW9ucyApIHtcblxuXHRcdHRoaXMuZXh0ZW5zaW9uID0gb3B0aW9ucy5leHRlbnNpb247XG5cdFx0dGhpcy5yZWFjdGlvbiA9IG9wdGlvbnMucmVhY3Rpb247XG5cdFx0dGhpcy5hY3Rpb25UeXBlID0gb3B0aW9ucy5hY3Rpb25UeXBlO1xuXHR9LFxuXG5cdC8vIERpc3BsYXkgdGhlIHBlcmlvZCBzZXR0aW5ncyBmb3JtLlxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBuYW1lID0gWyB0aGlzLmV4dGVuc2lvbi5nZXQoICdzbHVnJyApLCB0aGlzLmFjdGlvblR5cGUsIDAsICdsZW5ndGgnIF07XG5cdFx0dmFyIHBlcmlvZExlbmd0aCA9IHRoaXMucmVhY3Rpb24ubW9kZWwuZ2V0KCBuYW1lICk7XG5cblx0XHQvLyBXaGljaCB1bml0cyBzaG91bGQgdGhpcyBwZXJpb2QgYmUgZGlzcGxheWVkIGluIChzZWNvbmQsIG1pbnV0ZXMsICZjLik/XG5cdFx0dmFyIHVuaXQsIHVuaXRzO1xuXG5cdFx0dW5pdHMgPSBfLmtleXMoIHRoaXMuZXh0ZW5zaW9uLmRhdGEucGVyaW9kX3VuaXRzICkuc29ydCggZnVuY3Rpb24gKCBhLCBiICkge1xuXHRcdFx0cmV0dXJuIGIgLSBhOyAvLyBXZSBkb24ndCBoYXZlIHRvIHdvcnJ5IGFib3V0IE5hTiwgZXRjLlxuXHRcdH0pO1xuXG5cdFx0Zm9yICggdmFyIGkgPSAwOyBpIDwgdW5pdHMubGVuZ3RoOyBpKysgKSB7XG5cblx0XHRcdHVuaXQgPSB1bml0c1sgaSBdO1xuXG5cdFx0XHRpZiAoIDAgPT09IHBlcmlvZExlbmd0aCAlIHVuaXQgKSB7XG5cdFx0XHRcdGJyZWFrO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHZhciB0ZW1wbGF0ZSA9IHRoaXMudGVtcGxhdGUoIHtcblx0XHRcdG5hbWU6IEZpZWxkcy5nZXRGaWVsZE5hbWUoIG5hbWUgKSxcblx0XHRcdGxlbmd0aDogcGVyaW9kTGVuZ3RoLFxuXHRcdFx0bGVuZ3RoX2luX3VuaXRzOiBwZXJpb2RMZW5ndGggLyB1bml0LFxuXHRcdFx0bGVuZ3RoX2luX3VuaXRzX2xhYmVsOiB0aGlzLmdldFBlcmlvZExhYmVsKClcblx0XHR9ICk7XG5cblx0XHR0ZW1wbGF0ZSA9IEZpZWxkcy5jcmVhdGVTZWxlY3QoXG5cdFx0XHR7IHZhbHVlOiB1bml0LCBvcHRpb25zOiB0aGlzLmV4dGVuc2lvbi5kYXRhLnBlcmlvZF91bml0cyB9XG5cdFx0XHQsIHRlbXBsYXRlXG5cdFx0KTtcblxuXHRcdHRoaXMuJGVsLmh0bWwoIHRlbXBsYXRlICk7XG5cblx0XHR0aGlzLiRsZW5ndGggPSB0aGlzLiQoICcud29yZHBvaW50cy1ob29rLXBlcmlvZC1sZW5ndGgnICk7XG5cdFx0dGhpcy4kbGVuZ3RoSW5Vbml0cyA9IHRoaXMuJCggJy53b3JkcG9pbnRzLWhvb2stcGVyaW9kLWxlbmd0aC1pbi11bml0cycgKTtcblx0XHR0aGlzLiR1bml0cyA9IHRoaXMuJCggJy53b3JkcG9pbnRzLWhvb2stcGVyaW9kLXVuaXRzJyApO1xuXG5cdFx0dGhpcy50cmlnZ2VyKCAncmVuZGVyJywgdGhpcyApO1xuXG5cdFx0cmV0dXJuIHRoaXM7XG5cdH0sXG5cblx0Z2V0UGVyaW9kTGFiZWw6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBsYWJlbCA9IHRoaXMucmVhY3Rpb24uUmVhY3Rvci5nZXQoICdwZXJpb2RzX2xhYmVsJyApO1xuXG5cdFx0aWYgKCAhbGFiZWwgKSB7XG5cdFx0XHRsYWJlbCA9IHRoaXMuZXh0ZW5zaW9uLmRhdGEubDEwbi5sYWJlbDtcblx0XHR9XG5cblx0XHRyZXR1cm4gbGFiZWw7XG5cdH0sXG5cblx0c3luYzogZnVuY3Rpb24gKCkge1xuXHRcdHRoaXMuJGxlbmd0aC52YWwoIHRoaXMuJGxlbmd0aEluVW5pdHMudmFsKCkgKiB0aGlzLiR1bml0cy52YWwoKSApO1xuXHR9XG5cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IFNpbXBsZVBlcmlvZDtcbiJdfQ==
