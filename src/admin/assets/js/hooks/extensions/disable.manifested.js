(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var hooks = wp.wordpoints.hooks;

// Controllers.
hooks.extension.Disable = require( './disable/controllers/extension.js' );

var Disable = new hooks.extension.Disable();

// Register the extension.
hooks.Extensions.add( Disable );

// EOF

},{"./disable/controllers/extension.js":2}],2:[function(require,module,exports){
/**
 * @summary Disable hook extension controller object.
 *
 * @since 2.3.0
 *
 * @module
 */

var Extension = wp.wordpoints.hooks.controller.Extension,
	template = wp.wordpoints.hooks.template,
	Disable;

/**
 * wp.wordpoints.hooks.extension.Disable
 *
 * @since 2.3.0
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 */
Disable = Extension.extend({

	/**
	 * @since 2.3.0
	 */
	defaults: {
		slug: 'disable'
	},

	/**
	 * @summary The template for the extension settings.
	 *
	 * @since 2.3.0
	 */
	template: template( 'hook-disable' ),

	/**
	 * @summary The template for the "disabled" text shown in the reaction title.
	 *
	 * @since 2.3.0
	 */
	titleTemplate: template( 'hook-disabled-text' ),

	/**
	 * @since 2.3.0
	 */
	initReaction: function ( reaction ) {

		this.listenTo( reaction, 'render:title', function () {

			if ( ! reaction.$title.find( '.wordpoints-hook-disabled-text' ).length ) {
				reaction.$title.prepend( this.titleTemplate() );
			}

			this.setDisabled( reaction );
		});

		this.listenToOnce( reaction, 'render:settings', function () {

			// We hook this up late so the settings will be below other extensions.
			this.listenTo( reaction, 'render:fields', function () {

				if ( ! reaction.$fields.find( '.disable' ).length ) {
					reaction.$fields.append( this.template() );
				}

				this.setDisabled( reaction );
			});
		} );

		this.listenTo( reaction.model, 'change:disabled', function () {
			this.setDisabled( reaction );
		} );

		this.listenTo( reaction.model, 'sync', function () {
			this.setDisabled( reaction );
		} );
	},

	/**
	 * @summary Set the disabled class if the reaction is disabled.
	 *
	 * @since 2.3.0
	 */
	setDisabled: function ( reaction ) {

		var isDisabled = !! reaction.model.get( this.get( 'slug' ) );

		reaction.$el.toggleClass( 'disabled', isDisabled );

		reaction.$fields
			.find( 'input[name=disable]' )
			.prop( 'checked', isDisabled );
	},

	/**
	 * @since 2.3.0
	 */
	validateReaction: function () {}

} );

module.exports = Disable;

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2Rpc2FibGUubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2Rpc2FibGUvY29udHJvbGxlcnMvZXh0ZW5zaW9uLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCJ2YXIgaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzO1xuXG4vLyBDb250cm9sbGVycy5cbmhvb2tzLmV4dGVuc2lvbi5EaXNhYmxlID0gcmVxdWlyZSggJy4vZGlzYWJsZS9jb250cm9sbGVycy9leHRlbnNpb24uanMnICk7XG5cbnZhciBEaXNhYmxlID0gbmV3IGhvb2tzLmV4dGVuc2lvbi5EaXNhYmxlKCk7XG5cbi8vIFJlZ2lzdGVyIHRoZSBleHRlbnNpb24uXG5ob29rcy5FeHRlbnNpb25zLmFkZCggRGlzYWJsZSApO1xuXG4vLyBFT0ZcbiIsIi8qKlxuICogQHN1bW1hcnkgRGlzYWJsZSBob29rIGV4dGVuc2lvbiBjb250cm9sbGVyIG9iamVjdC5cbiAqXG4gKiBAc2luY2UgMi4zLjBcbiAqXG4gKiBAbW9kdWxlXG4gKi9cblxudmFyIEV4dGVuc2lvbiA9IHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb24sXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0RGlzYWJsZTtcblxuLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5EaXNhYmxlXG4gKlxuICogQHNpbmNlIDIuMy4wXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uXG4gKi9cbkRpc2FibGUgPSBFeHRlbnNpb24uZXh0ZW5kKHtcblxuXHQvKipcblx0ICogQHNpbmNlIDIuMy4wXG5cdCAqL1xuXHRkZWZhdWx0czoge1xuXHRcdHNsdWc6ICdkaXNhYmxlJ1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBUaGUgdGVtcGxhdGUgZm9yIHRoZSBleHRlbnNpb24gc2V0dGluZ3MuXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjMuMFxuXHQgKi9cblx0dGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1kaXNhYmxlJyApLFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBUaGUgdGVtcGxhdGUgZm9yIHRoZSBcImRpc2FibGVkXCIgdGV4dCBzaG93biBpbiB0aGUgcmVhY3Rpb24gdGl0bGUuXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjMuMFxuXHQgKi9cblx0dGl0bGVUZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWRpc2FibGVkLXRleHQnICksXG5cblx0LyoqXG5cdCAqIEBzaW5jZSAyLjMuMFxuXHQgKi9cblx0aW5pdFJlYWN0aW9uOiBmdW5jdGlvbiAoIHJlYWN0aW9uICkge1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggcmVhY3Rpb24sICdyZW5kZXI6dGl0bGUnLCBmdW5jdGlvbiAoKSB7XG5cblx0XHRcdGlmICggISByZWFjdGlvbi4kdGl0bGUuZmluZCggJy53b3JkcG9pbnRzLWhvb2stZGlzYWJsZWQtdGV4dCcgKS5sZW5ndGggKSB7XG5cdFx0XHRcdHJlYWN0aW9uLiR0aXRsZS5wcmVwZW5kKCB0aGlzLnRpdGxlVGVtcGxhdGUoKSApO1xuXHRcdFx0fVxuXG5cdFx0XHR0aGlzLnNldERpc2FibGVkKCByZWFjdGlvbiApO1xuXHRcdH0pO1xuXG5cdFx0dGhpcy5saXN0ZW5Ub09uY2UoIHJlYWN0aW9uLCAncmVuZGVyOnNldHRpbmdzJywgZnVuY3Rpb24gKCkge1xuXG5cdFx0XHQvLyBXZSBob29rIHRoaXMgdXAgbGF0ZSBzbyB0aGUgc2V0dGluZ3Mgd2lsbCBiZSBiZWxvdyBvdGhlciBleHRlbnNpb25zLlxuXHRcdFx0dGhpcy5saXN0ZW5UbyggcmVhY3Rpb24sICdyZW5kZXI6ZmllbGRzJywgZnVuY3Rpb24gKCkge1xuXG5cdFx0XHRcdGlmICggISByZWFjdGlvbi4kZmllbGRzLmZpbmQoICcuZGlzYWJsZScgKS5sZW5ndGggKSB7XG5cdFx0XHRcdFx0cmVhY3Rpb24uJGZpZWxkcy5hcHBlbmQoIHRoaXMudGVtcGxhdGUoKSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0dGhpcy5zZXREaXNhYmxlZCggcmVhY3Rpb24gKTtcblx0XHRcdH0pO1xuXHRcdH0gKTtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHJlYWN0aW9uLm1vZGVsLCAnY2hhbmdlOmRpc2FibGVkJywgZnVuY3Rpb24gKCkge1xuXHRcdFx0dGhpcy5zZXREaXNhYmxlZCggcmVhY3Rpb24gKTtcblx0XHR9ICk7XG5cblx0XHR0aGlzLmxpc3RlblRvKCByZWFjdGlvbi5tb2RlbCwgJ3N5bmMnLCBmdW5jdGlvbiAoKSB7XG5cdFx0XHR0aGlzLnNldERpc2FibGVkKCByZWFjdGlvbiApO1xuXHRcdH0gKTtcblx0fSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgU2V0IHRoZSBkaXNhYmxlZCBjbGFzcyBpZiB0aGUgcmVhY3Rpb24gaXMgZGlzYWJsZWQuXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjMuMFxuXHQgKi9cblx0c2V0RGlzYWJsZWQ6IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHR2YXIgaXNEaXNhYmxlZCA9ICEhIHJlYWN0aW9uLm1vZGVsLmdldCggdGhpcy5nZXQoICdzbHVnJyApICk7XG5cblx0XHRyZWFjdGlvbi4kZWwudG9nZ2xlQ2xhc3MoICdkaXNhYmxlZCcsIGlzRGlzYWJsZWQgKTtcblxuXHRcdHJlYWN0aW9uLiRmaWVsZHNcblx0XHRcdC5maW5kKCAnaW5wdXRbbmFtZT1kaXNhYmxlXScgKVxuXHRcdFx0LnByb3AoICdjaGVja2VkJywgaXNEaXNhYmxlZCApO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc2luY2UgMi4zLjBcblx0ICovXG5cdHZhbGlkYXRlUmVhY3Rpb246IGZ1bmN0aW9uICgpIHt9XG5cbn0gKTtcblxubW9kdWxlLmV4cG9ydHMgPSBEaXNhYmxlO1xuIl19
