/**
 * wp.wordpoints.hooks.controller.Reactor
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 *
 *
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	hooks = wp.wordpoints.hooks,
	emptyFunction = hooks.util.emptyFunction,
	Reactor;

Reactor = Extension.extend({

	defaults: {
		'arg_types': [],
		'action_types': []
	},

	/**
	 * @since 2.1.0
	 */
	initialize: function () {

		this.listenTo( hooks, 'reactions:view:init', this.listenToDefaults );

		this.__child__.initialize.apply( this, arguments );
	},

	/**
	 * @since 2.1.0
	 */
	listenToDefaults: function ( reactionsView ) {

		this.listenTo(
			reactionsView
			, 'hook-reaction-defaults'
			, this.filterReactionDefaults
		);
	},

	/**
	 * @since 2.1.0
	 * @abstract
	 */
	filterReactionDefaults: emptyFunction( 'filterReactionDefaults' )
});

module.exports = Reactor;
