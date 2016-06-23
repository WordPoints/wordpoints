/**
 * wp.wordpoints.hooks.view.ConditionGroup
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	Condition = wp.wordpoints.hooks.view.Condition,
	Args = wp.wordpoints.hooks.Args,
	$ = Backbone.$,
	template = wp.wordpoints.hooks.template,
	ConditionGroup;

ConditionGroup = Base.extend({

	className: 'condition-group',

	template: template( 'hook-reaction-condition-group' ),

	initialize: function () {

		this.listenTo( this.model, 'add', this.addOne );
		this.listenTo( this.model, 'reset', this.render );
		this.listenTo( this.model, 'remove', this.maybeHide );

		this.model.on( 'add', this.reaction.lockOpen, this.reaction );
		this.model.on( 'remove', this.reaction.lockOpen, this.reaction );
		this.model.on( 'reset', this.reaction.lockOpen, this.reaction );
	},

	render: function () {

		this.$el.html( this.template() );

		this.maybeHide();

		this.$( '.condition-group-title' ).text(
			Args.buildHierarchyHumanId(
				Args.getArgsFromHierarchy(
					this.model.get( 'hierarchy' )
					, this.reaction.model.get( 'event' )
				)
			)
		);

		this.addAll();

		return this;
	},

	addOne: function ( condition ) {

		condition.reaction = this.reaction.model;

		var view = new Condition( {
			el: $( '<div class="condition"></div>' ),
			model: condition,
			reaction: this.reaction
		} );

		this.$el.append( view.render().$el ).show();

		this.listenTo( condition, 'destroy', function () {
			this.model.get( 'conditions' ).remove( condition.id );
		} );
	},

	addAll: function () {
		this.model.get( 'conditions' ).each( this.addOne, this );
	},

	// Hide the group when it is empty.
	maybeHide: function () {

		if ( 0 === this.model.get( 'conditions' ).length ) {
			this.$el.hide();
		}
	}
});

module.exports = ConditionGroup;