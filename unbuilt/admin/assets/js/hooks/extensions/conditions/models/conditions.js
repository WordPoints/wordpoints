/**
 * wp.wordpoints.hooks.model.Conditions
 *
 * @class
 * @augments Backbone.Collection
 */
var Condition = wp.wordpoints.hooks.model.Condition,
	Conditions;

Conditions = Backbone.Collection.extend({

	// Reference to this collection's model.
	model: Condition,

	comparator: 'id',

	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving hook conditions is not supported.' }
		);
	}
});

module.exports = Conditions;
