(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{"./models/arg.js":2,"./models/args.js":3,"./models/base.js":4,"./models/event.js":5,"./models/reaction.js":6,"./models/reactions.js":7}],2:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Arg
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	Arg;

Arg = Base.extend({
	namespace: 'arg',
	idAttribute: 'slug'
});

module.exports = Arg;

},{}],3:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Args
 *
 * @class
 * @augments Backbone.Collection
 */
var Arg = wp.wordpoints.hooks.model.Arg,
	Args;

Args = Backbone.Collection.extend({

	model: Arg,

	comparator: 'slug',

	// We don't currently support syncing groups, so we give an error instead.
	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving groups of hook args is not supported.' }
		);
	}
});

module.exports = Args;

},{}],4:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Base
 *
 * @class
 * @augments Backbone.Model
 */
var hooks = wp.wordpoints.hooks,
	extend = hooks.util.extend,
	Base;

// Add a base view so we can have a standardized view bootstrap for this app.
Base = Backbone.Model.extend( {

	// First, we let each view specify its own namespace, so we can use it as
	// a prefix for any standard events we want to fire.
	namespace: '_base',

	// We have an initialization bootstrap. Below we'll set things up so that
	// this gets called even when an extending view specifies an `initialize`
	// function.
	initialize: function ( options ) {

		// The first thing we do is to allow for a namespace to be passed in
		// as an option when the view is constructed, instead of forcing it
		// to be part of the prototype only.
		if ( typeof options.namespace !== 'undefined' ) {
			this.namespace = options.namespace;
		}

		if ( typeof options.reaction !== 'undefined' ) {
			this.reaction = options.reaction;
		}

		// Once things are set up, we call the extending view's `initialize` method.
		this.__child__.initialize.apply( this, arguments );

		// Finally, we trigger an action to let the whole app know we just
		// created this view.
		hooks.trigger( this.namespace + ':model:init', this );
	},

	validate: function ( attributes, options, errors ) {

		var newErrors = this.__child__.validate.apply( this, arguments );

		errors = errors || [];

		if ( newErrors ) {
			errors.concat( newErrors );
		}

		hooks.trigger(
			this.namespace + ':model:validate'
			, this
			, attributes
			, errors
		);

		if ( errors.length > 0 ) {
			return errors;
		}
	}

}, { extend: extend } );

module.exports = Base;

},{}],5:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Event
 *
 * @class
 * @augments Backbone.Model
 */
var HookEvent;

HookEvent = Backbone.Model.extend({

	// Default attributes for the event.
	defaults: function() {
		return {
			name: ''
		};
	},

	// We don't currently support syncing events, so we give an error instead.
	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving hook events is not supported.' }
		);
	}
});

module.exports = HookEvent;

},{}],6:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Reaction
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	getDeep = wp.wordpoints.hooks.util.getDeep,
	Reaction;

Reaction = Base.extend({

	namespace: 'reaction',

	// Default attributes for the reaction.
	defaults: function() {
		return {
			description: ''
		};
	},

	get: function ( attr ) {

		var atts = this.attributes;

		if ( _.isArray( attr ) ) {
			return getDeep( atts, attr );
		}

		return atts[ attr ];
	},

	// Override the default sync method to use WordPress's Ajax API.
	sync: function ( method, model, options ) {

		options = options || {};
		options.data = _.extend( options.data || {} );

		switch ( method ) {
			case 'read':
				options.error( { message: 'Fetching hook reactions is not supported.' } );
				return;

			case 'create':
				options.data.action = 'wordpoints_admin_create_hook_reaction';
				options.data = _.extend( options.data, model.attributes );
				break;

			case 'update':
				options.data.action = 'wordpoints_admin_update_hook_reaction';
				options.data = _.extend( options.data, model.attributes );
				break;

			case 'delete':
				options.data.action  = 'wordpoints_admin_delete_hook_reaction';
				options.data.id      = model.get( 'id' );
				options.data.nonce   = model.get( 'delete_nonce' );
				options.data.reaction_store = model.get( 'reaction_store' );
				break;
		}

		return wp.ajax.send( options, null );
	}
});

module.exports = Reaction;

},{}],7:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Reactions
 *
 * @class
 * @augments Backbone.Collection
 */
var Reaction = wp.wordpoints.hooks.model.Reaction,
	Reactions;

Reactions = Backbone.Collection.extend({

	// Reference to this collection's model.
	model: Reaction,

	// Reactions are sorted by their original insertion order.
	comparator: 'id',

	// We don't currently support syncing groups, so we give an error instead.
	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving groups of hook reactions is not supported.' }
		);
	}
});

module.exports = Reactions;

},{}]},{},[1]);
