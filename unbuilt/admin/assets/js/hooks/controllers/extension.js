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
	 * @summary Initializes a reaction.
	 * 
	 * This is called when a reaction view is initialized.
	 * 
	 * @since 2.1.0
	 * 
	 * @abstract
	 * 
	 * @param {wp.wordpoints.hooks.view.Reaction} reaction The reaction being
	 *                                                     initialized.
	 */
	initReaction: emptyFunction( 'initReaction' ),

	/**
	 * @summary Validates a reaction's settings.
	 * 
	 * This is called before a reaction model is saved.
	 * 
	 * @since 2.1.0
	 * 
	 * @abstract
	 * 
	 * @param {Reaction} model      The reaction model.
	 * @param {array}    attributes The model's attributes (the settings being
	 *                              validated).
	 * @param {array}    errors     Any errors that were encountered.
	 * @param {array}    options    Options.
	 */
	validateReaction: emptyFunction( 'validateReaction' )

}, { extend: extend } );

module.exports = Extension;
