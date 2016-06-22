/**
 * wp.wordpoints.hooks.model.Event
 *
 * @class
 * @augments Backbone.Model
 */
var HookEvent;

HookEvent = Backbone.Model.extend({

	// Default attributes for the event.
	defaults: function() {
		return {
			name: ''
		};
	},

	// We don't currently support syncing events, so we give an error instead.
	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving hook events is not supported.' }
		);
	}
});

module.exports = HookEvent;
