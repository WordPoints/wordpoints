/**
 * wp.wordpoints.hooks.model.Reactions
 *
 * @class
 * @augments Backbone.Collection
 */
var Reaction = wp.wordpoints.hooks.model.Reaction,
	Reactions;

Reactions = Backbone.Collection.extend({

	// Reference to this collection's model.
	model: Reaction,

	// Reactions are sorted by their original insertion order.
	comparator: 'id',

	// We don't currently support syncing groups, so we give an error instead.
	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving groups of hook reactions is not supported.' }
		);
	}
});

module.exports = Reactions;
