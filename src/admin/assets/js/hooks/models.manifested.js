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

		return _.template( text, options );
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
			, options
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

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9tb2RlbHMubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9tb2RlbHMvYXJnLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvbW9kZWxzL2FyZ3MuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9tb2RlbHMvYmFzZS5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL21vZGVscy9ldmVudC5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL21vZGVscy9yZWFjdGlvbi5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL21vZGVscy9yZWFjdGlvbnMuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDelJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDaEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3hCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25FQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDMUJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCJ2YXIgJCA9IGpRdWVyeSxcblx0aG9va3MsIGVtcHR5RnVuY3Rpb247XG5cbndpbmRvdy53cCA9IHdpbmRvdy53cCB8fCB7fTtcblxud2luZG93LndwLndvcmRwb2ludHMgPSB3aW5kb3cud3Aud29yZHBvaW50cyB8fCB7fTtcblxuaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzID0ge1xuXHRtb2RlbDoge30sXG5cdHZpZXc6IHt9LFxuXHRjb250cm9sbGVyOiB7fSxcblx0cmVhY3Rvcjoge30sXG5cdGV4dGVuc2lvbjoge30sXG5cdHV0aWw6IHt9XG59O1xuXG5fLmV4dGVuZCggaG9va3MsIEJhY2tib25lLkV2ZW50cyApO1xuXG4vKipcbiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuICogVVRJTElUSUVTXG4gKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqL1xuXG4vLyBXZSBvdmVycmlkZSB0aGUgYmFzaWMgYGV4dGVuZGAgZnVuY3Rpb24gc28gdGhhdCB3ZSBjYW4gcGVyZm9ybSBhXG4vLyBsaXR0bGUgbWFnaWMuXG5ob29rcy51dGlsLmV4dGVuZCA9IGZ1bmN0aW9uICgpIHtcblxuXHR2YXIgb2JqID0gdGhpcztcblxuXHQvLyBJbnN0ZWFkIG9mIGR1cGxpY2F0aW5nIEJhY2tib25lJ3MgYGV4dGVuZCgpYCBsb2dpYyBoZXJlLCB3ZSBsb29rIHVwIHRoZSB0cmVlXG5cdC8vIHVudGlsIHdlIGZpbmQgdGhlIG9yaWdpbmFsIGV4dGVuZCBmdW5jdGlvbiBCeSBkb2luZyBpdCB0aGlzIHdheSB3ZSBhcmVcblx0Ly8gY29tcGF0aWJsZSB3aXRoIG90aGVyIGV4dGVuZCgpIG1ldGhvZHMgYmVpbmcgaW5zZXJ0ZWQgc29tZXdoZXJlIHVwIHRoZSB0cmVlXG5cdC8vIGJlc2lkZSBqdXN0IEJhY2tib25lJ3MuXG5cdHdoaWxlICggb2JqLmV4dGVuZCA9PT0gaG9va3MudXRpbC5leHRlbmQgKSB7XG5cdFx0b2JqID0gb2JqLl9fc3VwZXJfXy5jb25zdHJ1Y3Rvcjtcblx0fVxuXG5cdC8vIFdlIGNyZWF0ZSB0aGUgZXh0ZW5zaW9uIGhlcmUsIHdoaWNoIHdlJ2xsIGNhbGwgYGNoaWxkYC4gRnJvbSBub3cgb24gd2UgY2FuXG5cdC8vIHRoaW5rIG9mIG91cnNlbHZlcyBhcyBhIHBhcmVudC5cblx0dmFyIGNoaWxkID0gb2JqLmV4dGVuZC5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cblx0dmFyIHBhcmVudCA9IHRoaXMucHJvdG90eXBlO1xuXG5cdHZhciBjaGlsZE1ldGhvZHMgPSBjaGlsZC5wcm90b3R5cGU7XG5cblx0Ly8gSWYgdGhlcmUgYXJlIGFscmVhZHkgZGVzY2VuZGFudHMgZGVmaW5lZCBvbiB0aGUgcHJvdG90eXBlLCB3ZSBuZWVkIHRvIGFkZCBhbnlcblx0Ly8gb3ZlcnJpZGRlbiBtZXRob2RzIGFzIHRoZSBjaGlsZHJlbiBvZiB0aGUgbGFzdCBnZW5lcmF0aW9uLlxuXHR3aGlsZSAoIGNoaWxkTWV0aG9kcy5fX2NoaWxkX18gKSB7XG5cdFx0Y2hpbGRNZXRob2RzID0gY2hpbGRNZXRob2RzLl9fY2hpbGRfXztcblx0fVxuXG5cdGNoaWxkTWV0aG9kcy5fX2NoaWxkX18gPSB7fTtcblxuXHQvLyBUaGlzIGlzIHdoZXJlIHRoZSBtYWdpYyBoYXBwZW5zLiBJZiB0aGUgY2hpbGQgZGVmaW5lcyBhbnkgb2YgdGhlXG5cdC8vIHBhcmVudCdzIHByb3RvdHlwZSBtZXRob2RzIGluIGl0cyBvd24gcHJvdG90eXBlLCB3ZSBvdmVycmlkZSB0aGVtIHdpdGhcblx0Ly8gdGhlIHBhcmVudCBtZXRob2QsIGJ1dCBzYXZlIHRoZSBjaGlsZCBtZXRob2QgZm9yIGxhdGVyIGluIGBfX2NoaWxkX19gLlxuXHQvLyBUaGlzIGFsbG93cyB0aGUgcGFyZW50IG1ldGhvZCB0byBjYWxsIHRoZSBjaGlsZCBtZXRob2QuXG5cdHZhciBpdGVyYXRvciA9IGZ1bmN0aW9uICggbWV0aG9kLCBuYW1lICkge1xuXG5cdFx0aWYgKCAnY29uc3RydWN0b3InID09PSBuYW1lICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGlmICggISBfLmlzRnVuY3Rpb24oIG1ldGhvZCApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHZhciBjaGlsZE1ldGhvZCA9IGNoaWxkLnByb3RvdHlwZVsgbmFtZSBdO1xuXG5cdFx0Ly8gSWYgdGhlIGNoaWxkIGRvZXNuJ3QgZGVmaW5lIHRoaXMgbWV0aG9kLCB3ZSBzdGlsbCBpbmNsdWRlIGEgc3R1YlxuXHRcdC8vIHNvIHRoYXQgdGhlIHBhcmVudCBjYW4gY2FsbCBpdCB3aXRob3V0IGNoZWNraW5nIHdoZXRoZXIgaXQgZXhpc3RzLlxuXHRcdGlmICggY2hpbGRNZXRob2QgPT09IG1ldGhvZCApIHtcblx0XHRcdGNoaWxkTWV0aG9kID0gZW1wdHlGdW5jdGlvbiggbmFtZSApO1xuXHRcdH1cblxuXHRcdC8vIFdlIG5lZWQgdG8gb3ZlcnJpZGUgdGhlIGNoaWxkIG1ldGhvZCBpbiBjYXNlIHRoZXJlIHdpbGwgYmUgZ3JhbmRjaGlsZHJlbi5cblx0XHRjaGlsZE1ldGhvZHMuX19jaGlsZF9fWyBuYW1lIF0gPSBmdW5jdGlvbiAoKSB7XG5cblx0XHRcdHZhciBfX2NoaWxkX187XG5cblx0XHRcdC8vIElmIHRoZXJlIGFyZSBncmFuZGNoaWxkcmVuLCB3ZSBuZWVkIHRvIHVwZGF0ZSBfX2NoaWxkX18gdG8gcmVmZXIgdG9cblx0XHRcdC8vIHRoZW0sIGluc3RlYWQgb2YgdG8gdXMgYW5kIG91ciBzaWJsaW5ncyAob3RoZXJ3aXNlIHdlJ2QgbG9vcFxuXHRcdFx0Ly8gaW5maW5pdGVseSkuXG5cdFx0XHRpZiAoIHRoaXMuX19jaGlsZF9fICkge1xuXHRcdFx0XHRfX2NoaWxkX18gPSB0aGlzLl9fY2hpbGRfXztcblx0XHRcdFx0dGhpcy5fX2NoaWxkX18gPSBfX2NoaWxkX18uX19jaGlsZF9fO1xuXHRcdFx0fVxuXG5cdFx0XHRjaGlsZE1ldGhvZC5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cblx0XHRcdC8vIFB1dCBldmVyeXRoaW5nIGJhY2sgd2hlbiB3ZSBhcmUgZG9uZS5cblx0XHRcdGlmICggX19jaGlsZF9fICkge1xuXHRcdFx0XHR0aGlzLl9fY2hpbGRfXyA9IF9fY2hpbGRfXztcblx0XHRcdH1cblx0XHR9O1xuXG5cdFx0Y2hpbGQucHJvdG90eXBlWyBuYW1lIF0gPSBtZXRob2Q7XG5cdH07XG5cblx0Xy5lYWNoKCBwYXJlbnQsIGl0ZXJhdG9yLCB0aGlzICk7XG5cblx0cmV0dXJuIGNoaWxkO1xufTtcblxuLyoqXG4gKiBBbiBlbXB0eSBmdW5jdGlvbi5cbiAqXG4gKiBUbyBiZSB1c2VkIGZvciBcImFic3RyYWN0XCIgZnVuY3Rpb25zIGluIG9iamVjdHMgdGhhdCB1c2UgaG9va3MudXRpbC5leHRlbmQoKS5cbiAqXG4gKiBAcmV0dXJucyB7Kn1cbiAqL1xuaG9va3MudXRpbC5lbXB0eUZ1bmN0aW9uID0gZW1wdHlGdW5jdGlvbiA9IGZ1bmN0aW9uICggbWV0aG9kTmFtZSApIHtcblxuXHRyZXR1cm4gZnVuY3Rpb24gc2VsZigpIHtcblxuXHRcdGlmICggdGhpcy5fX2NoaWxkX18gKSB7XG5cblx0XHRcdHZhciBkZXNjZW5kYW50ID0gdGhpcy5fX2NoaWxkX187XG5cblx0XHRcdC8vIEl0IGlzIHBvc3NpYmxlIHRoYXQgdGhlIG1ldGhvZCBuYW1lIHdpbGwgbm90IGJlIHNldCBvbiB0aGUgY2hpbGQsIGJ1dFxuXHRcdFx0Ly8gd2lsbCBiZSBzZXQgb24gYSBsb3dlciBkZXNjZW5kYW50LiBUaGlzIGNhbiBoYXBwZW4gd2hlbiB0aGUgbWV0aG9kIHdhc1xuXHRcdFx0Ly8gbm90IG9uIHRoZSBvcmlnaW5hbCBwYXRyaWFyY2ggYnV0IHdhcyBzZXQgYnkgYSBsYXRlciBhbmNlc3Rvci5cblx0XHRcdHdoaWxlICggZGVzY2VuZGFudCAmJiAhIGRlc2NlbmRhbnRbIG1ldGhvZE5hbWUgXSApIHtcblx0XHRcdFx0ZGVzY2VuZGFudCA9IGRlc2NlbmRhbnQuX19jaGlsZF9fO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBJZiB3ZSBmb3VuZCBhIGRlc2NlbmRhbnQgd2l0aCB0aGUgbWV0aG9kLCBjYWxsIGl0LCBhbmQgcGFzcyBiYWNrIGFueVxuXHRcdFx0Ly8gcmV0dXJuZWQgdmFsdWUuXG5cdFx0XHRpZiAoIGRlc2NlbmRhbnQgKSB7XG5cdFx0XHRcdHJldHVybiBkZXNjZW5kYW50WyBtZXRob2ROYW1lIF0uYXBwbHkoIHRoaXMsIGFyZ3VtZW50cyApO1xuXHRcdFx0fVxuXHRcdH1cblx0fTtcbn07XG5cbi8qKlxuICpcbiAqIEBwYXJhbSAge29iamVjdH0gIG9iamVjdCAgVGhlIHByaW1hcnkgcGFyYW1ldGVyIHRvIGNvbXBhcmUuXG4gKiBAcGFyYW0gIHthcnJheX0gIGhpZXJhcmNoeSAgVGhlIHByaW1hcnkgcGFyYW1ldGVyIHRvIGNvbXBhcmUuXG4gKlxuICogQHJldHVybiB7b2JqZWN0fVxuICovXG5ob29rcy51dGlsLmdldERlZXAgPSBmdW5jdGlvbiggb2JqZWN0LCBoaWVyYXJjaHkgKSB7XG5cblx0Zm9yICggdmFyIGkgPSAwOyBpIDwgaGllcmFyY2h5Lmxlbmd0aDsgaSsrICkge1xuXG5cdFx0aWYgKCB0eXBlb2Ygb2JqZWN0ID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdGJyZWFrO1xuXHRcdH1cblxuXHRcdG9iamVjdCA9IG9iamVjdFsgaGllcmFyY2h5WyBpIF0gXTtcblx0fVxuXG5cdHJldHVybiBvYmplY3Q7XG59O1xuXG5ob29rcy51dGlsLnNldERlZXAgPSBmdW5jdGlvbiAoIG9iamVjdCwgaGllcmFyY2h5LCB2YWx1ZSApIHtcblxuXHR2YXIgZmllbGQgPSBoaWVyYXJjaHkucG9wKCk7XG5cblx0Xy5lYWNoKCBoaWVyYXJjaHksIGZ1bmN0aW9uICggZmllbGQgKSB7XG5cdFx0aWYgKCB0eXBlb2Ygb2JqZWN0WyBmaWVsZCBdID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdGlmICggXy5pc051bWJlciggZmllbGQgKSApIHtcblx0XHRcdFx0b2JqZWN0WyBmaWVsZCBdID0gW107XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRvYmplY3RbIGZpZWxkIF0gPSB7fTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHRvYmplY3QgPSBvYmplY3RbIGZpZWxkIF07XG5cdH0pO1xuXG5cdG9iamVjdFsgZmllbGQgXSA9IHZhbHVlO1xufTtcblxuXG53cC53b3JkcG9pbnRzLiRjYWNoZSA9IGZ1bmN0aW9uICggJCApIHtcblxuXHR2YXIgY2FjaGUgPSB7fTtcblxuXHRyZXR1cm4gZnVuY3Rpb24gKCBzZWxlY3RvciApIHtcblxuXHRcdGlmICggdHlwZW9mIGNhY2hlWyBzZWxlY3RvciBdID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdGNhY2hlWyBzZWxlY3RvciBdID0gJC5jYWxsKCB0aGlzLCBzZWxlY3RvciApO1xuXHRcdH1cblxuXHRcdHJldHVybiBjYWNoZVsgc2VsZWN0b3IgXTtcblx0fTtcbn07XG5cbndwLndvcmRwb2ludHMuJCA9IHdwLndvcmRwb2ludHMuJGNhY2hlKCAkICk7XG5cbl8uZXh0ZW5kKCBob29rcywge1xuXHQvKipcblx0ICogaG9va3MudGVtcGxhdGUoIGlkIClcblx0ICpcblx0ICogRmV0Y2ggYSBKYXZhU2NyaXB0IHRlbXBsYXRlIGZvciBhbiBpZCwgYW5kIHJldHVybiBhIHRlbXBsYXRpbmcgZnVuY3Rpb24gZm9yIGl0LlxuXHQgKlxuXHQgKiBTZWUgd3AudGVtcGxhdGUoKSBpbiBgd3AtaW5jbHVkZXMvanMvd3AtdXRpbC5qc2AuXG5cdCAqL1xuXHR0ZW1wbGF0ZTogZnVuY3Rpb24gKCBpZCApIHtcblx0XHRyZXR1cm4gd3AudGVtcGxhdGUoICd3b3JkcG9pbnRzLScgKyBpZCApO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBob29rcy50ZXh0VGVtcGxhdGUoIHRleHQgKVxuXHQgKlxuXHQgKiBSZXR1cm5zIGEgV29yZFByZXNzLXN0eWxlIHRlbXBsYXRpbmcgZnVuY3Rpb24gZm9yIGEgdGV4dCBzdHJpbmcuXG5cdCAqXG5cdCAqIFNlZSB3cC50ZW1wbGF0ZSgpIGluIGB3cC1pbmNsdWRlcy9qcy93cC11dGlsLmpzYC5cblx0ICovXG5cdHRleHRUZW1wbGF0ZTogZnVuY3Rpb24gKCB0ZXh0ICkge1xuXHRcdHZhciBvcHRpb25zID0ge1xuXHRcdFx0ZXZhbHVhdGU6ICAgIC88IyhbXFxzXFxTXSs/KSM+L2csXG5cdFx0XHRpbnRlcnBvbGF0ZTogL1xce1xce1xceyhbXFxzXFxTXSs/KVxcfVxcfVxcfS9nLFxuXHRcdFx0ZXNjYXBlOiAgICAgIC9cXHtcXHsoW15cXH1dKz8pXFx9XFx9KD8hXFx9KS9nLFxuXHRcdFx0dmFyaWFibGU6ICAgICdkYXRhJ1xuXHRcdH07XG5cblx0XHRyZXR1cm4gXy50ZW1wbGF0ZSggdGV4dCwgb3B0aW9ucyApO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBob29rcy5wb3N0KCBbYWN0aW9uXSwgW2RhdGFdIClcblx0ICpcblx0ICogU2VuZHMgYSBQT1NUIHJlcXVlc3QgdG8gV29yZFByZXNzLlxuXHQgKiBTZWUgd3AuYWpheC5wb3N0KCkgaW4gYHdwLWluY2x1ZGVzL2pzL3dwLXV0aWwuanNgLlxuXHQgKlxuXHQgKiBAYm9ycm93cyB3cC5hamF4LnBvc3QgYXMgcG9zdFxuXHQgKi9cblx0cG9zdDogd3AuYWpheC5wb3N0LFxuXG5cdC8qKlxuXHQgKiBob29rcy5hamF4KCBbYWN0aW9uXSwgW29wdGlvbnNdIClcblx0ICpcblx0ICogU2VuZHMgYW4gWEhSIHJlcXVlc3QgdG8gV29yZFByZXNzLlxuXHQgKiBTZWUgd3AuYWpheC5zZW5kKCkgaW4gYHdwLWluY2x1ZGVzL2pzL3dwLXV0aWwuanNgLlxuXHQgKlxuXHQgKiBAYm9ycm93cyB3cC5hamF4LnNlbmQgYXMgYWpheFxuXHQgKi9cblx0YWpheDogd3AuYWpheC5zZW5kLFxuXG5cdC8qKlxuXHQgKiBUcnVuY2F0ZXMgYSBzdHJpbmcgYnkgaW5qZWN0aW5nIGFuIGVsbGlwc2lzIGludG8gdGhlIG1pZGRsZS5cblx0ICogVXNlZnVsIGZvciBmaWxlbmFtZXMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7U3RyaW5nfSBzdHJpbmdcblx0ICogQHBhcmFtIHtOdW1iZXJ9IFtsZW5ndGg9MzBdXG5cdCAqIEBwYXJhbSB7U3RyaW5nfSBbcmVwbGFjZW1lbnQ9JmhlbGxpcDtdXG5cdCAqIEByZXR1cm5zIHtTdHJpbmd9IFRoZSBzdHJpbmcsIHVubGVzcyBsZW5ndGggaXMgZ3JlYXRlciB0aGFuIHN0cmluZy5sZW5ndGguXG5cdCAqL1xuXHR0cnVuY2F0ZTogZnVuY3Rpb24oIHN0cmluZywgbGVuZ3RoLCByZXBsYWNlbWVudCApIHtcblx0XHRsZW5ndGggPSBsZW5ndGggfHwgMzA7XG5cdFx0cmVwbGFjZW1lbnQgPSByZXBsYWNlbWVudCB8fCAnJmhlbGxpcDsnO1xuXG5cdFx0aWYgKCBzdHJpbmcubGVuZ3RoIDw9IGxlbmd0aCApIHtcblx0XHRcdHJldHVybiBzdHJpbmc7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHN0cmluZy5zdWJzdHIoIDAsIGxlbmd0aCAvIDIgKSArIHJlcGxhY2VtZW50ICsgc3RyaW5nLnN1YnN0ciggLTEgKiBsZW5ndGggLyAyICk7XG5cdH1cbn0pO1xuXG4vKipcbiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuICogTU9ERUxTXG4gKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqL1xuXG5ob29rcy5tb2RlbC5CYXNlICAgICAgICAgICAgID0gcmVxdWlyZSggJy4vbW9kZWxzL2Jhc2UuanMnICk7XG5ob29rcy5tb2RlbC5BcmcgICAgICAgICAgICAgID0gcmVxdWlyZSggJy4vbW9kZWxzL2FyZy5qcycgKTtcbmhvb2tzLm1vZGVsLkFyZ3MgICAgICAgICAgICAgPSByZXF1aXJlKCAnLi9tb2RlbHMvYXJncy5qcycgKTtcbmhvb2tzLm1vZGVsLlJlYWN0aW9uICAgICAgICAgPSByZXF1aXJlKCAnLi9tb2RlbHMvcmVhY3Rpb24uanMnICk7XG5ob29rcy5tb2RlbC5SZWFjdGlvbnMgICAgICAgID0gcmVxdWlyZSggJy4vbW9kZWxzL3JlYWN0aW9ucy5qcycgKTtcbmhvb2tzLm1vZGVsLkV2ZW50ICAgICAgICAgICAgPSByZXF1aXJlKCAnLi9tb2RlbHMvZXZlbnQuanMnICk7XG5cbi8vIENsZWFuIHVwLiBQcmV2ZW50cyBtb2JpbGUgYnJvd3NlcnMgY2FjaGluZ1xuJCh3aW5kb3cpLm9uKCd1bmxvYWQnLCBmdW5jdGlvbigpe1xuXHR3aW5kb3cud3AgPSBudWxsO1xufSk7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQXJnXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2UsXG5cdEFyZztcblxuQXJnID0gQmFzZS5leHRlbmQoe1xuXHRuYW1lc3BhY2U6ICdhcmcnLFxuXHRpZEF0dHJpYnV0ZTogJ3NsdWcnXG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBBcmc7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQXJnc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLkNvbGxlY3Rpb25cbiAqL1xudmFyIEFyZyA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQXJnLFxuXHRBcmdzO1xuXG5BcmdzID0gQmFja2JvbmUuQ29sbGVjdGlvbi5leHRlbmQoe1xuXG5cdG1vZGVsOiBBcmcsXG5cblx0Y29tcGFyYXRvcjogJ3NsdWcnLFxuXG5cdC8vIFdlIGRvbid0IGN1cnJlbnRseSBzdXBwb3J0IHN5bmNpbmcgZ3JvdXBzLCBzbyB3ZSBnaXZlIGFuIGVycm9yIGluc3RlYWQuXG5cdHN5bmM6IGZ1bmN0aW9uICggbWV0aG9kLCBjb2xsZWN0aW9uLCBvcHRpb25zICkge1xuXHRcdG9wdGlvbnMuZXJyb3IoXG5cdFx0XHR7IG1lc3NhZ2U6ICdGZXRjaGluZyBhbmQgc2F2aW5nIGdyb3VwcyBvZiBob29rIGFyZ3MgaXMgbm90IHN1cHBvcnRlZC4nIH1cblx0XHQpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBBcmdzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2VcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICovXG52YXIgaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLFxuXHRleHRlbmQgPSBob29rcy51dGlsLmV4dGVuZCxcblx0QmFzZTtcblxuLy8gQWRkIGEgYmFzZSB2aWV3IHNvIHdlIGNhbiBoYXZlIGEgc3RhbmRhcmRpemVkIHZpZXcgYm9vdHN0cmFwIGZvciB0aGlzIGFwcC5cbkJhc2UgPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoIHtcblxuXHQvLyBGaXJzdCwgd2UgbGV0IGVhY2ggdmlldyBzcGVjaWZ5IGl0cyBvd24gbmFtZXNwYWNlLCBzbyB3ZSBjYW4gdXNlIGl0IGFzXG5cdC8vIGEgcHJlZml4IGZvciBhbnkgc3RhbmRhcmQgZXZlbnRzIHdlIHdhbnQgdG8gZmlyZS5cblx0bmFtZXNwYWNlOiAnX2Jhc2UnLFxuXG5cdC8vIFdlIGhhdmUgYW4gaW5pdGlhbGl6YXRpb24gYm9vdHN0cmFwLiBCZWxvdyB3ZSdsbCBzZXQgdGhpbmdzIHVwIHNvIHRoYXRcblx0Ly8gdGhpcyBnZXRzIGNhbGxlZCBldmVuIHdoZW4gYW4gZXh0ZW5kaW5nIHZpZXcgc3BlY2lmaWVzIGFuIGBpbml0aWFsaXplYFxuXHQvLyBmdW5jdGlvbi5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBvcHRpb25zICkge1xuXG5cdFx0Ly8gVGhlIGZpcnN0IHRoaW5nIHdlIGRvIGlzIHRvIGFsbG93IGZvciBhIG5hbWVzcGFjZSB0byBiZSBwYXNzZWQgaW5cblx0XHQvLyBhcyBhbiBvcHRpb24gd2hlbiB0aGUgdmlldyBpcyBjb25zdHJ1Y3RlZCwgaW5zdGVhZCBvZiBmb3JjaW5nIGl0XG5cdFx0Ly8gdG8gYmUgcGFydCBvZiB0aGUgcHJvdG90eXBlIG9ubHkuXG5cdFx0aWYgKCB0eXBlb2Ygb3B0aW9ucy5uYW1lc3BhY2UgIT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0dGhpcy5uYW1lc3BhY2UgPSBvcHRpb25zLm5hbWVzcGFjZTtcblx0XHR9XG5cblx0XHRpZiAoIHR5cGVvZiBvcHRpb25zLnJlYWN0aW9uICE9PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdHRoaXMucmVhY3Rpb24gPSBvcHRpb25zLnJlYWN0aW9uO1xuXHRcdH1cblxuXHRcdC8vIE9uY2UgdGhpbmdzIGFyZSBzZXQgdXAsIHdlIGNhbGwgdGhlIGV4dGVuZGluZyB2aWV3J3MgYGluaXRpYWxpemVgIG1ldGhvZC5cblx0XHR0aGlzLl9fY2hpbGRfXy5pbml0aWFsaXplLmFwcGx5KCB0aGlzLCBhcmd1bWVudHMgKTtcblxuXHRcdC8vIEZpbmFsbHksIHdlIHRyaWdnZXIgYW4gYWN0aW9uIHRvIGxldCB0aGUgd2hvbGUgYXBwIGtub3cgd2UganVzdFxuXHRcdC8vIGNyZWF0ZWQgdGhpcyB2aWV3LlxuXHRcdGhvb2tzLnRyaWdnZXIoIHRoaXMubmFtZXNwYWNlICsgJzptb2RlbDppbml0JywgdGhpcyApO1xuXHR9LFxuXG5cdHZhbGlkYXRlOiBmdW5jdGlvbiAoIGF0dHJpYnV0ZXMsIG9wdGlvbnMsIGVycm9ycyApIHtcblxuXHRcdHZhciBuZXdFcnJvcnMgPSB0aGlzLl9fY2hpbGRfXy52YWxpZGF0ZS5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cblx0XHRlcnJvcnMgPSBlcnJvcnMgfHwgW107XG5cblx0XHRpZiAoIG5ld0Vycm9ycyApIHtcblx0XHRcdGVycm9ycy5jb25jYXQoIG5ld0Vycm9ycyApO1xuXHRcdH1cblxuXHRcdGhvb2tzLnRyaWdnZXIoXG5cdFx0XHR0aGlzLm5hbWVzcGFjZSArICc6bW9kZWw6dmFsaWRhdGUnXG5cdFx0XHQsIHRoaXNcblx0XHRcdCwgYXR0cmlidXRlc1xuXHRcdFx0LCBlcnJvcnNcblx0XHRcdCwgb3B0aW9uc1xuXHRcdCk7XG5cblx0XHRpZiAoIGVycm9ycy5sZW5ndGggPiAwICkge1xuXHRcdFx0cmV0dXJuIGVycm9ycztcblx0XHR9XG5cdH1cblxufSwgeyBleHRlbmQ6IGV4dGVuZCB9ICk7XG5cbm1vZHVsZS5leHBvcnRzID0gQmFzZTtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5FdmVudFxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKi9cbnZhciBIb29rRXZlbnQ7XG5cbkhvb2tFdmVudCA9IEJhY2tib25lLk1vZGVsLmV4dGVuZCh7XG5cblx0Ly8gRGVmYXVsdCBhdHRyaWJ1dGVzIGZvciB0aGUgZXZlbnQuXG5cdGRlZmF1bHRzOiBmdW5jdGlvbigpIHtcblx0XHRyZXR1cm4ge1xuXHRcdFx0bmFtZTogJydcblx0XHR9O1xuXHR9LFxuXG5cdC8vIFdlIGRvbid0IGN1cnJlbnRseSBzdXBwb3J0IHN5bmNpbmcgZXZlbnRzLCBzbyB3ZSBnaXZlIGFuIGVycm9yIGluc3RlYWQuXG5cdHN5bmM6IGZ1bmN0aW9uICggbWV0aG9kLCBjb2xsZWN0aW9uLCBvcHRpb25zICkge1xuXHRcdG9wdGlvbnMuZXJyb3IoXG5cdFx0XHR7IG1lc3NhZ2U6ICdGZXRjaGluZyBhbmQgc2F2aW5nIGhvb2sgZXZlbnRzIGlzIG5vdCBzdXBwb3J0ZWQuJyB9XG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gSG9va0V2ZW50O1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLlJlYWN0aW9uXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2UsXG5cdGdldERlZXAgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnV0aWwuZ2V0RGVlcCxcblx0UmVhY3Rpb247XG5cblJlYWN0aW9uID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ3JlYWN0aW9uJyxcblxuXHQvLyBEZWZhdWx0IGF0dHJpYnV0ZXMgZm9yIHRoZSByZWFjdGlvbi5cblx0ZGVmYXVsdHM6IGZ1bmN0aW9uKCkge1xuXHRcdHJldHVybiB7XG5cdFx0XHRkZXNjcmlwdGlvbjogJydcblx0XHR9O1xuXHR9LFxuXG5cdGdldDogZnVuY3Rpb24gKCBhdHRyICkge1xuXG5cdFx0dmFyIGF0dHMgPSB0aGlzLmF0dHJpYnV0ZXM7XG5cblx0XHRpZiAoIF8uaXNBcnJheSggYXR0ciApICkge1xuXHRcdFx0cmV0dXJuIGdldERlZXAoIGF0dHMsIGF0dHIgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gYXR0c1sgYXR0ciBdO1xuXHR9LFxuXG5cdC8vIE92ZXJyaWRlIHRoZSBkZWZhdWx0IHN5bmMgbWV0aG9kIHRvIHVzZSBXb3JkUHJlc3MncyBBamF4IEFQSS5cblx0c3luYzogZnVuY3Rpb24gKCBtZXRob2QsIG1vZGVsLCBvcHRpb25zICkge1xuXG5cdFx0b3B0aW9ucyA9IG9wdGlvbnMgfHwge307XG5cdFx0b3B0aW9ucy5kYXRhID0gXy5leHRlbmQoIG9wdGlvbnMuZGF0YSB8fCB7fSApO1xuXG5cdFx0c3dpdGNoICggbWV0aG9kICkge1xuXHRcdFx0Y2FzZSAncmVhZCc6XG5cdFx0XHRcdG9wdGlvbnMuZXJyb3IoIHsgbWVzc2FnZTogJ0ZldGNoaW5nIGhvb2sgcmVhY3Rpb25zIGlzIG5vdCBzdXBwb3J0ZWQuJyB9ICk7XG5cdFx0XHRcdHJldHVybjtcblxuXHRcdFx0Y2FzZSAnY3JlYXRlJzpcblx0XHRcdFx0b3B0aW9ucy5kYXRhLmFjdGlvbiA9ICd3b3JkcG9pbnRzX2FkbWluX2NyZWF0ZV9ob29rX3JlYWN0aW9uJztcblx0XHRcdFx0b3B0aW9ucy5kYXRhID0gXy5leHRlbmQoIG9wdGlvbnMuZGF0YSwgbW9kZWwuYXR0cmlidXRlcyApO1xuXHRcdFx0XHRicmVhaztcblxuXHRcdFx0Y2FzZSAndXBkYXRlJzpcblx0XHRcdFx0b3B0aW9ucy5kYXRhLmFjdGlvbiA9ICd3b3JkcG9pbnRzX2FkbWluX3VwZGF0ZV9ob29rX3JlYWN0aW9uJztcblx0XHRcdFx0b3B0aW9ucy5kYXRhID0gXy5leHRlbmQoIG9wdGlvbnMuZGF0YSwgbW9kZWwuYXR0cmlidXRlcyApO1xuXHRcdFx0XHRicmVhaztcblxuXHRcdFx0Y2FzZSAnZGVsZXRlJzpcblx0XHRcdFx0b3B0aW9ucy5kYXRhLmFjdGlvbiAgPSAnd29yZHBvaW50c19hZG1pbl9kZWxldGVfaG9va19yZWFjdGlvbic7XG5cdFx0XHRcdG9wdGlvbnMuZGF0YS5pZCAgICAgID0gbW9kZWwuZ2V0KCAnaWQnICk7XG5cdFx0XHRcdG9wdGlvbnMuZGF0YS5ub25jZSAgID0gbW9kZWwuZ2V0KCAnZGVsZXRlX25vbmNlJyApO1xuXHRcdFx0XHRvcHRpb25zLmRhdGEucmVhY3Rpb25fc3RvcmUgPSBtb2RlbC5nZXQoICdyZWFjdGlvbl9zdG9yZScgKTtcblx0XHRcdFx0YnJlYWs7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHdwLmFqYXguc2VuZCggb3B0aW9ucywgbnVsbCApO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBSZWFjdGlvbjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5SZWFjdGlvbnNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBSZWFjdGlvbiA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuUmVhY3Rpb24sXG5cdFJlYWN0aW9ucztcblxuUmVhY3Rpb25zID0gQmFja2JvbmUuQ29sbGVjdGlvbi5leHRlbmQoe1xuXG5cdC8vIFJlZmVyZW5jZSB0byB0aGlzIGNvbGxlY3Rpb24ncyBtb2RlbC5cblx0bW9kZWw6IFJlYWN0aW9uLFxuXG5cdC8vIFJlYWN0aW9ucyBhcmUgc29ydGVkIGJ5IHRoZWlyIG9yaWdpbmFsIGluc2VydGlvbiBvcmRlci5cblx0Y29tcGFyYXRvcjogJ2lkJyxcblxuXHQvLyBXZSBkb24ndCBjdXJyZW50bHkgc3VwcG9ydCBzeW5jaW5nIGdyb3Vwcywgc28gd2UgZ2l2ZSBhbiBlcnJvciBpbnN0ZWFkLlxuXHRzeW5jOiBmdW5jdGlvbiAoIG1ldGhvZCwgY29sbGVjdGlvbiwgb3B0aW9ucyApIHtcblx0XHRvcHRpb25zLmVycm9yKFxuXHRcdFx0eyBtZXNzYWdlOiAnRmV0Y2hpbmcgYW5kIHNhdmluZyBncm91cHMgb2YgaG9vayByZWFjdGlvbnMgaXMgbm90IHN1cHBvcnRlZC4nIH1cblx0XHQpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBSZWFjdGlvbnM7XG4iXX0=
