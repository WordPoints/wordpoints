/**
 * @summary Disable hook extension controller object.
 *
 * @since 2.3.0
 *
 * @module
 */

var Extension = wp.wordpoints.hooks.controller.Extension,
	template = wp.wordpoints.hooks.template,
	Disable;

/**
 * wp.wordpoints.hooks.extension.Disable
 *
 * @since 2.3.0
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 */
Disable = Extension.extend({

	/**
	 * @since 2.3.0
	 */
	defaults: {
		slug: 'disable'
	},

	/**
	 * @summary The template for the extension settings.
	 *
	 * @since 2.3.0
	 */
	template: template( 'hook-disable' ),

	/**
	 * @summary The template for the "disabled" text shown in the reaction title.
	 *
	 * @since 2.3.0
	 */
	titleTemplate: template( 'hook-disabled-text' ),

	/**
	 * @since 2.3.0
	 */
	initReaction: function ( reaction ) {

		this.listenTo( reaction, 'render:title', function () {

			if ( ! reaction.$title.find( '.wordpoints-hook-disabled-text' ).length ) {
				reaction.$title.prepend( this.titleTemplate() );
			}

			this.setDisabled( reaction );
		});

		this.listenToOnce( reaction, 'render:settings', function () {

			// We hook this up late so the settings will be below other extensions.
			this.listenTo( reaction, 'render:fields', function () {

				if ( ! reaction.$fields.find( '.disable' ).length ) {
					reaction.$fields.append( this.template() );
				}

				this.setDisabled( reaction );
			});
		} );

		this.listenTo( reaction.model, 'change:disabled', function () {
			this.setDisabled( reaction );
		} );

		this.listenTo( reaction.model, 'sync', function () {
			this.setDisabled( reaction );
		} );
	},

	/**
	 * @summary Set the disabled class if the reaction is disabled.
	 *
	 * @since 2.3.0
	 */
	setDisabled: function ( reaction ) {

		var isDisabled = !! reaction.model.get( this.get( 'slug' ) );

		reaction.$el.toggleClass( 'disabled', isDisabled );

		reaction.$fields
			.find( 'input[name=disable]' )
			.prop( 'checked', isDisabled );
	},

	/**
	 * @since 2.3.0
	 */
	validateReaction: function () {}

} );

module.exports = Disable;
