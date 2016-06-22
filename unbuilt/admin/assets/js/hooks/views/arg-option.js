/**
 * wp.wordpoints.hooks.view.ArgOption
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	template = wp.wordpoints.hooks.template,
	$ = Backbone.$,
	ArgOption;

ArgOption = Base.extend({

	namespace: 'arg-option',

	tagName: 'option',

	template: template( 'hook-arg-option' ),

	render: function () {

		this.$el = $( template( this.model ) );

		return this;
	}
});

module.exports = ArgOption;
