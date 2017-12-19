/**
 * General JS utility functions.
 *
 * @package WordPoints
 * @since 2.5.0
 */

window.wp = window.wp || {};
window.wp.wordpoints = window.wp.wordpoints || {};
window.wp.wordpoints.utils = window.wp.wordpoints.utils || {};

(function () {

	/**
	 * wp.wordpoints.utils.template( id )
	 *
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * See wp.template() in `wp-includes/js/wp-util.js`.
	 */
	wp.wordpoints.utils.template = function ( id ) {
		return wp.template( 'wordpoints-' + id );
	};

	/**
	 * wp.wordpoints.utils.textTemplate( text )
	 *
	 * Returns a WordPress-style templating function for a text string.
	 *
	 * See wp.template() in `wp-includes/js/wp-util.js`.
	 */
	wp.wordpoints.utils.textTemplate = function ( text ) {
		var options = {
			evaluate:    /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
			variable:    'data'
		};

		return _.template( text, options );
	};

}());

// EOF
