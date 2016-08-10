var $ = jQuery,
	hooks, emptyFunction;

window.wp = window.wp || {};

window.wp.wordpoints = window.wp.wordpoints || {};

hooks = wp.wordpoints.hooks = {
	model: {},
	view: {},
	controller: {},
	reactor: {},
	extension: {},
	util: {}
};

_.extend( hooks, Backbone.Events );

/**
 * ========================================================================
 * UTILITIES
 * ========================================================================
 */

// We override the basic `extend` function so that we can perform a
// little magic.
hooks.util.extend = function () {

	var obj = this;

	// Instead of duplicating Backbone's `extend()` logic here, we look up the tree
	// until we find the original extend function By doing it this way we are
	// compatible with other extend() methods being inserted somewhere up the tree
	// beside just Backbone's.
	while ( obj.extend === hooks.util.extend ) {
		obj = obj.__super__.constructor;
	}

	// We create the extension here, which we'll call `child`. From now on we can
	// think of ourselves as a parent.
	var child = obj.extend.apply( this, arguments );

	var parent = this.prototype;

	var childMethods = child.prototype;

	// If there are already descendants defined on the prototype, we need to add any
	// overridden methods as the children of the last generation.
	while ( childMethods.__child__ ) {
		childMethods = childMethods.__child__;
	}

	childMethods.__child__ = {};

	// This is where the magic happens. If the child defines any of the
	// parent's prototype methods in its own prototype, we override them with
	// the parent method, but save the child method for later in `__child__`.
	// This allows the parent method to call the child method.
	var iterator = function ( method, name ) {

		if ( 'constructor' === name ) {
			return;
		}

		if ( ! _.isFunction( method ) ) {
			return;
		}

		var childMethod = child.prototype[ name ];

		// If the child doesn't define this method, we still include a stub
		// so that the parent can call it without checking whether it exists.
		if ( childMethod === method ) {
			childMethod = emptyFunction( name );
		}

		// We need to override the child method in case there will be grandchildren.
		childMethods.__child__[ name ] = function () {

			var __child__;

			// If there are grandchildren, we need to update __child__ to refer to
			// them, instead of to us and our siblings (otherwise we'd loop
			// infinitely).
			if ( this.__child__ ) {
				__child__ = this.__child__;
				this.__child__ = __child__.__child__;
			}

			childMethod.apply( this, arguments );

			// Put everything back when we are done.
			if ( __child__ ) {
				this.__child__ = __child__;
			}
		};

		child.prototype[ name ] = method;
	};

	_.each( parent, iterator, this );

	return child;
};

/**
 * An empty function.
 *
 * To be used for "abstract" functions in objects that use hooks.util.extend().
 *
 * @returns {*}
 */
hooks.util.emptyFunction = emptyFunction = function ( methodName ) {

	return function self() {

		if ( this.__child__ ) {

			var descendant = this.__child__;

			// It is possible that the method name will not be set on the child, but
			// will be set on a lower descendant. This can happen when the method was
			// not on the original patriarch but was set by a later ancestor.
			while ( descendant && ! descendant[ methodName ] ) {
				descendant = descendant.__child__;
			}

			// If we found a descendant with the method, call it, and pass back any
			// returned value.
			if ( descendant ) {
				return descendant[ methodName ].apply( this, arguments );
			}
		}
	};
};

/**
 *
 * @param  {object}  object  The primary parameter to compare.
 * @param  {array}  hierarchy  The primary parameter to compare.
 *
 * @return {object}
 */
hooks.util.getDeep = function( object, hierarchy ) {

	for ( var i = 0; i < hierarchy.length; i++ ) {

		if ( typeof object === 'undefined' ) {
			break;
		}

		object = object[ hierarchy[ i ] ];
	}

	return object;
};

hooks.util.setDeep = function ( object, hierarchy, value ) {

	var field = hierarchy.pop();

	_.each( hierarchy, function ( field ) {
		if ( typeof object[ field ] === 'undefined' ) {
			if ( _.isNumber( field ) ) {
				object[ field ] = [];
			} else {
				object[ field ] = {};
			}
		}

		object = object[ field ];
	});

	object[ field ] = value;
};


wp.wordpoints.$cache = function ( $ ) {

	var cache = {};

	return function ( selector ) {

		if ( typeof cache[ selector ] === 'undefined' ) {
			cache[ selector ] = $.call( this, selector );
		}

		return cache[ selector ];
	};
};

wp.wordpoints.$ = wp.wordpoints.$cache( $ );

_.extend( hooks, {
	/**
	 * hooks.template( id )
	 *
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * See wp.template() in `wp-includes/js/wp-util.js`.
	 */
	template: function ( id ) {
		return wp.template( 'wordpoints-' + id );
	},

	/**
	 * hooks.textTemplate( text )
	 *
	 * Returns a WordPress-style templating function for a text string.
	 *
	 * See wp.template() in `wp-includes/js/wp-util.js`.
	 */
	textTemplate: function ( text ) {
		var options = {
			evaluate:    /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
			variable:    'data'
		};

		return _.template( text, null, options );
	},

	/**
	 * hooks.post( [action], [data] )
	 *
	 * Sends a POST request to WordPress.
	 * See wp.ajax.post() in `wp-includes/js/wp-util.js`.
	 *
	 * @borrows wp.ajax.post as post
	 */
	post: wp.ajax.post,

	/**
	 * hooks.ajax( [action], [options] )
	 *
	 * Sends an XHR request to WordPress.
	 * See wp.ajax.send() in `wp-includes/js/wp-util.js`.
	 *
	 * @borrows wp.ajax.send as ajax
	 */
	ajax: wp.ajax.send,

	/**
	 * Truncates a string by injecting an ellipsis into the middle.
	 * Useful for filenames.
	 *
	 * @param {String} string
	 * @param {Number} [length=30]
	 * @param {String} [replacement=&hellip;]
	 * @returns {String} The string, unless length is greater than string.length.
	 */
	truncate: function( string, length, replacement ) {
		length = length || 30;
		replacement = replacement || '&hellip;';

		if ( string.length <= length ) {
			return string;
		}

		return string.substr( 0, length / 2 ) + replacement + string.substr( -1 * length / 2 );
	}
});

/**
 * ========================================================================
 * MODELS
 * ========================================================================
 */

hooks.model.Base             = require( './models/base.js' );
hooks.model.Arg              = require( './models/arg.js' );
hooks.model.Args             = require( './models/args.js' );
hooks.model.Reaction         = require( './models/reaction.js' );
hooks.model.Reactions        = require( './models/reactions.js' );
hooks.model.Event            = require( './models/event.js' );

// Clean up. Prevents mobile browsers caching
$(window).on('unload', function(){
	window.wp = null;
});
