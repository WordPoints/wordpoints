/**
 * wp.wordpoints.hooks.model.Condition
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	Args = wp.wordpoints.hooks.Args,
	Extensions = wp.wordpoints.hooks.Extensions,
	Fields = wp.wordpoints.hooks.Fields,
	Condition;

Condition = Base.extend({

	defaults: {
		type: '',
		settings: []
	},

	initialize: function ( attributes, options ) {
		if ( options.group ) {
			this.group = options.group;
		}
	},

	validate: function ( attributes, options, errors ) {

		errors = errors || [];

		var conditionType = this.getType();

		if ( ! conditionType ) {
			return;
		}

		var fields = conditionType.fields;

		Fields.validate(
			fields
			, attributes.settings
			, errors
		);

		var controller = this.getController();

		if ( controller ) {
			controller.validateSettings( this, attributes.settings, errors );
		}

		return errors;
	},

	getController: function () {

		var arg = this.getArg();

		if ( ! arg ) {
			return false;
		}

		var Conditions = Extensions.get( 'conditions' );

		return Conditions.getController(
			Conditions.getDataTypeFromArg( arg )
			, this.get( 'type' )
		);
	},

	getType: function () {

		var arg = this.getArg();

		if ( ! arg ) {
			return false;
		}

		var Conditions = Extensions.get( 'conditions' );

		return Conditions.getType(
			Conditions.getDataTypeFromArg( arg )
			, this.get( 'type' )
		);
	},

	getArg: function () {

		if ( ! this.arg ) {

			var args = Args.getArgsFromHierarchy(
				this.getHierarchy()
				, this.reaction.get( 'event' )
			);

			if ( args ) {
				this.arg = args[ args.length - 1 ];
			}
		}

		return this.arg;
	},

	getHierarchy: function () {
		return this.group.get( 'hierarchy' );
	},

	getFullHierarchy: function () {

		return this.group.get( 'groups' ).hierarchy.concat(
			this.getHierarchy()
		);
	},

	sync: function ( method, model, options ) {
		options.error(
			{ message: 'Fetching and saving hook conditions is not supported.' }
		);
	}
});

module.exports = Condition;
