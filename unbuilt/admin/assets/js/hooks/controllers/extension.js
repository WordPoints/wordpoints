/**
 * wp.wordpoints.hooks.controller.Extension
 *
 * @since 2.1.0
 *
 * @class
 * @augments Backbone.Model
 *
 *
 */
var hooks = wp.wordpoints.hooks,
	extensions = hooks.view.data.extensions,
	extend = hooks.util.extend,
	emptyFunction = hooks.util.emptyFunction,
	Extension;

Extension = Backbone.Model.extend({

	/**
	 * @since 2.1.0
	 */
	idAttribute: 'slug',

	/**
	 * @since 2.1.0
	 */
	initialize: function () {

		this.listenTo( hooks, 'reaction:view:init', this.initReaction );
		this.listenTo( hooks, 'reaction:model:validate', this.validateReaction );

		this.data = extensions[ this.id ];

		this.__child__.initialize.apply( this, arguments );
	},

	/**
	 * @since 2.1.0
	 * @abstract
	 */
	initReaction: emptyFunction( 'initReaction' ),

	/**
	 * @since 2.1.0
	 * @abstract
	 */
	validateReaction: emptyFunction( 'validateReaction' )

}, { extend: extend } );

module.exports = Extension;
