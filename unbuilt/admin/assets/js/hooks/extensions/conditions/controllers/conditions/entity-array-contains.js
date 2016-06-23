/**
 * wp.wordpoints.hooks.extension.Conditions.condition.EntityArrayContains
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.extension.Conditions.Condition
 */

var Condition = wp.wordpoints.hooks.extension.Conditions.Condition,
	ConditionGroups = wp.wordpoints.hooks.model.ConditionGroups,
	ConditionGroupsView = wp.wordpoints.hooks.view.ConditionGroups,
	Extensions = wp.wordpoints.hooks.Extensions,
	ArgsCollection = wp.wordpoints.hooks.model.Args,
	Args = wp.wordpoints.hooks.Args,
	EntityArrayContains;

EntityArrayContains = Condition.extend({

	defaults: {
		slug: 'entity_array_contains'
	},

	renderSettings: function ( condition, fieldNamePrefix ) {

		// Render the main fields.
		var fields = this.constructor.__super__.renderSettings.apply(
			this
			, [ condition, fieldNamePrefix ]
		);

		condition.$settings.append( fields );

		// Render view for sub-conditions.
		var arg = Args.getEntity(
			condition.model.getArg().get( 'entity_slug' )
		);

		condition.model.subGroups = new ConditionGroups( null, {
			args: new ArgsCollection( [ arg ] ),
			hierarchy: condition.model.getFullHierarchy().concat(
				[ '_conditions', condition.model.id, 'settings', 'conditions' ]
			),
			reaction: condition.reaction.model,
			_conditions: condition.model.get( 'settings' ).conditions
		} );

		var view = new ConditionGroupsView( {
			collection: condition.model.subGroups,
			reaction: condition.reaction
		});

		condition.$settings.append( view.render().$el );

		return '';
	},

	validateSettings: function ( condition, settings, errors ) {

		Extensions.get( 'conditions' ).validateConditions(
			[ condition.subGroups ]
			, settings.conditions
			, errors
		);
	}
});

module.exports = EntityArrayContains;
