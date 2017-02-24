/**
 * @summary Data Type model object.
 *
 * @since 2.3.0
 *
 * @module
 */

var template = wp.wordpoints.hooks.template;

/**
 * wp.wordpoints.hooks.controller.DataType
 *
 * @since 2.1.0
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.DataType
 */
var DataType = Backbone.Model.extend({

	/**
	 * @since 2.1.0
	 */
	idAttribute: 'slug',

	/**
	 * @since 2.1.0
	 */
	defaults: {
		inputType: 'text'
	},

	/**
	 * @summary The template for the field.
	 *
	 * @since 2.1.0
	 */
	template: template( 'hook-reaction-field' ),

	/**
	 * @summary Creates the HTML for a field for data of this type.
	 *
	 * @since 2.1.0
	 *
	 * @param {object} data       - Field data.
	 * @param {string} data.name  - Field name attribute.
	 * @param {string} data.value - Field value attribute.
	 *
	 * @return {string} HTML for a form field.
	 */
	createField: function ( data ) {

		return this.template(
			_.extend( {}, data, { type: this.get( 'inputType' ) } )
		);
	}
});

module.exports = DataType;
