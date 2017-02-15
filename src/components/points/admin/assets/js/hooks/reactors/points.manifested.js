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

	var periods = hooks.Extensions.get( 'periods' ),
		nativeShowForReaction = periods.showForReaction;

	// Legacy periods extension.
	var LegacyPeriods = new hooks.extension.Periods(
		{ slug: 'points_legacy_periods' }
	);

	LegacyPeriods.showForReaction = function ( reaction ) {

		if ( ! reaction.model.get( 'points_legacy_periods' ) ) {
			return false;
		}

		return nativeShowForReaction( reaction );
	};

	// Register the legacy periods extension.
	hooks.Extensions.add( LegacyPeriods );

	// Don't show regular periods extension when legacy periods are in use.
	periods.showForReaction = function ( reaction ) {

		if ( reaction.model.get( 'points_legacy_periods' ) ) {
			return false;
		}

		return nativeShowForReaction( reaction );
	};
});

hooks.reactor.Points = require( './points.js' );

},{"./points.js":1}]},{},[2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2NvbXBvbmVudHMvcG9pbnRzL2FkbWluL2Fzc2V0cy9qcy9ob29rcy9yZWFjdG9ycy9wb2ludHMuanMiLCJ1bmJ1aWx0L2NvbXBvbmVudHMvcG9pbnRzL2FkbWluL2Fzc2V0cy9qcy9ob29rcy9yZWFjdG9ycy9wb2ludHMubWFuaWZlc3QuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2xGQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5yZWFjdG9yLlBvaW50c1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLlJlYWN0b3JcbiAqL1xudmFyIFJlYWN0b3IgPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuUmVhY3Rvcixcblx0RmllbGRzID0gd3Aud29yZHBvaW50cy5ob29rcy5GaWVsZHMsXG5cdGRhdGEgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuZGF0YSxcblx0UG9pbnRzO1xuXG5Qb2ludHMgPSBSZWFjdG9yLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IHtcblx0XHRzbHVnOiAncG9pbnRzJyxcblx0XHRmaWVsZHM6IHt9LFxuXHRcdHJldmVyc2Fsc19leHRlbnNpb25fc2x1ZzogJ3JldmVyc2Fscydcblx0fSxcblxuXHRpbml0UmVhY3Rpb246IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHRpZiAoIHJlYWN0aW9uLm1vZGVsLmdldCggJ3JlYWN0b3InICkgIT09IHRoaXMuZ2V0KCAnc2x1ZycgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aGlzLmxpc3RlblRvKCByZWFjdGlvbiwgJ3JlbmRlcjpzZXR0aW5ncycsIHRoaXMucmVuZGVyICk7XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoICRlbCwgY3VycmVudEFjdGlvblR5cGUsIHJlYWN0aW9uICkge1xuXG5cdFx0dmFyIGZpZWxkcyA9ICcnO1xuXG5cdFx0Xy5mb3JFYWNoKCB0aGlzLmdldCggJ2ZpZWxkcycgKSwgZnVuY3Rpb24gKCBmaWVsZCwgbmFtZSApIHtcblxuXHRcdFx0ZmllbGRzICs9IEZpZWxkcy5jcmVhdGUoXG5cdFx0XHRcdG5hbWUsXG5cdFx0XHRcdHJlYWN0aW9uLm1vZGVsLmdldCggbmFtZSApLFxuXHRcdFx0XHRmaWVsZFxuXHRcdFx0KTtcblx0XHR9KTtcblxuXHRcdHJlYWN0aW9uLiRzZXR0aW5ncy5hcHBlbmQoIGZpZWxkcyApO1xuXHR9LFxuXG5cdHZhbGlkYXRlUmVhY3Rpb246IGZ1bmN0aW9uICggbW9kZWwsIGF0dHJpYnV0ZXMsIGVycm9ycyApIHtcblxuXHRcdGlmICggYXR0cmlidXRlcy5yZWFjdG9yICE9PSB0aGlzLmdldCggJ3NsdWcnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0RmllbGRzLnZhbGlkYXRlKFxuXHRcdFx0dGhpcy5nZXQoICdmaWVsZHMnIClcblx0XHRcdCwgYXR0cmlidXRlc1xuXHRcdFx0LCBlcnJvcnNcblx0XHQpO1xuXHR9LFxuXG5cdGZpbHRlclJlYWN0aW9uRGVmYXVsdHM6IGZ1bmN0aW9uICggZGVmYXVsdHMsIHZpZXcgKSB7XG5cblx0XHRpZiAoIGRlZmF1bHRzLnJlYWN0b3IgIT09IHRoaXMuZ2V0KCAnc2x1ZycgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkZWZhdWx0cy5wb2ludHNfdHlwZSA9IHZpZXcuJHJlYWN0aW9uR3JvdXAuZGF0YShcblx0XHRcdCd3b3JkcG9pbnRzLWhvb2tzLXBvaW50cy10eXBlJ1xuXHRcdCk7XG5cblx0XHQvLyBIYXZlIHRvZ2dsZSBldmVudHMgYmVoYXZlIGxpa2UgcmV2ZXJzYWxzLlxuXHRcdGlmIChcblx0XHRcdGRlZmF1bHRzLmV2ZW50XG5cdFx0XHQmJiBkYXRhLmV2ZW50X2FjdGlvbl90eXBlc1sgZGVmYXVsdHMuZXZlbnQgXVxuXHRcdFx0JiYgZGF0YS5ldmVudF9hY3Rpb25fdHlwZXNbIGRlZmF1bHRzLmV2ZW50IF0udG9nZ2xlX29uXG5cdFx0KSB7XG5cdFx0XHRkZWZhdWx0c1sgdGhpcy5nZXQoICdyZXZlcnNhbHNfZXh0ZW5zaW9uX3NsdWcnICkgXSA9IHtcblx0XHRcdFx0dG9nZ2xlX29mZjogJ3RvZ2dsZV9vbidcblx0XHRcdH07XG5cdFx0fVxuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBQb2ludHM7XG4iLCJ2YXIgaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLFxuXHRkYXRhID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LmRhdGEucmVhY3RvcnM7XG5cbmhvb2tzLm9uKCAnaW5pdCcsIGZ1bmN0aW9uICgpIHtcblxuXHRob29rcy5SZWFjdG9ycy5hZGQoIG5ldyBob29rcy5yZWFjdG9yLlBvaW50cyggZGF0YS5wb2ludHMgKSApO1xuXG5cdGlmICggZGF0YS5wb2ludHNfbGVnYWN5ICkge1xuXHRcdGhvb2tzLlJlYWN0b3JzLmFkZCggbmV3IGhvb2tzLnJlYWN0b3IuUG9pbnRzKCBkYXRhLnBvaW50c19sZWdhY3kgKSApO1xuXHR9XG5cblx0dmFyIHBlcmlvZHMgPSBob29rcy5FeHRlbnNpb25zLmdldCggJ3BlcmlvZHMnICksXG5cdFx0bmF0aXZlU2hvd0ZvclJlYWN0aW9uID0gcGVyaW9kcy5zaG93Rm9yUmVhY3Rpb247XG5cblx0Ly8gTGVnYWN5IHBlcmlvZHMgZXh0ZW5zaW9uLlxuXHR2YXIgTGVnYWN5UGVyaW9kcyA9IG5ldyBob29rcy5leHRlbnNpb24uUGVyaW9kcyhcblx0XHR7IHNsdWc6ICdwb2ludHNfbGVnYWN5X3BlcmlvZHMnIH1cblx0KTtcblxuXHRMZWdhY3lQZXJpb2RzLnNob3dGb3JSZWFjdGlvbiA9IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHRpZiAoICEgcmVhY3Rpb24ubW9kZWwuZ2V0KCAncG9pbnRzX2xlZ2FjeV9wZXJpb2RzJyApICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHJldHVybiBuYXRpdmVTaG93Rm9yUmVhY3Rpb24oIHJlYWN0aW9uICk7XG5cdH07XG5cblx0Ly8gUmVnaXN0ZXIgdGhlIGxlZ2FjeSBwZXJpb2RzIGV4dGVuc2lvbi5cblx0aG9va3MuRXh0ZW5zaW9ucy5hZGQoIExlZ2FjeVBlcmlvZHMgKTtcblxuXHQvLyBEb24ndCBzaG93IHJlZ3VsYXIgcGVyaW9kcyBleHRlbnNpb24gd2hlbiBsZWdhY3kgcGVyaW9kcyBhcmUgaW4gdXNlLlxuXHRwZXJpb2RzLnNob3dGb3JSZWFjdGlvbiA9IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHRpZiAoIHJlYWN0aW9uLm1vZGVsLmdldCggJ3BvaW50c19sZWdhY3lfcGVyaW9kcycgKSApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHRyZXR1cm4gbmF0aXZlU2hvd0ZvclJlYWN0aW9uKCByZWFjdGlvbiApO1xuXHR9O1xufSk7XG5cbmhvb2tzLnJlYWN0b3IuUG9pbnRzID0gcmVxdWlyZSggJy4vcG9pbnRzLmpzJyApO1xuIl19
