/**
 * wp.wordpoints.hooks.model.Args
 *
 * @class
 * @augments Backbone.Collection
 */
var Arg = wp.wordpoints.hooks.model.Arg,
	Args;

Args = Backbone.Collection.extend({

	model: Arg,

	comparator: 'slug',

	// We don't currently support syncing groups, so we give an error instead.
	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving groups of hook args is not supported.' }
		);
	}
});

module.exports = Args;
