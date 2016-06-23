/**
 * wp.wordpoints.hooks.view.Condition
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	template = wp.wordpoints.hooks.template,
	Extensions = wp.wordpoints.hooks.Extensions,
	Fields = wp.wordpoints.hooks.Fields,
	Condition;

Condition = Base.extend({

	namespace: 'condition',

	className: 'wordpoints-hook-condition',

	template: template( 'hook-reaction-condition' ),

	events: {
		'click .delete': 'destroy'
	},

	initialize: function () {

		this.listenTo( this.model, 'change', this.render );
		this.listenTo( this.model, 'destroy', this.remove );

		this.listenTo( this.model, 'invalid', this.model.reaction.showError );

		this.extension = Extensions.get( 'conditions' );
	},

	// Display the condition settings form.
	render: function () {

		this.$el.html( this.template() );

		this.$title = this.$( '.condition-title' );
		this.$settings = this.$( '.condition-settings' );

		this.renderTitle();
		this.renderSettings();

		this.trigger( 'render', this );

		return this;
	},

	renderTitle: function () {

		var conditionType = this.model.getType();

		if ( conditionType ) {
			this.$title.text( conditionType.title );
		}

		this.trigger( 'render:title', this );
	},

	renderSettings: function () {

		// Build the fields based on the condition type.
		var conditionType = this.model.getType(),
			fields = '';

		var fieldNamePrefix = _.clone( this.model.getFullHierarchy() );
		fieldNamePrefix.unshift( 'conditions' );
		fieldNamePrefix.push(
			'_conditions'
			, this.model.get( 'id' )
			, 'settings'
		);

		var fieldName = _.clone( fieldNamePrefix );

		fieldName.pop();
		fieldName.push( 'type' );

		fields += Fields.create(
			fieldName
			, this.model.get( 'type' )
			, { type: 'hidden' }
		);

		if ( conditionType ) {
			var controller = this.extension.getController(
				conditionType.data_type
				, conditionType.slug
			);

			if ( controller ) {
				fields += controller.renderSettings( this, fieldNamePrefix );
			}
		}

		this.$settings.append( fields );

		this.trigger( 'render:settings', this );
	},

	// Remove the item, destroy the model.
	destroy: function () {

		this.model.destroy();
	}
});

module.exports = Condition;
