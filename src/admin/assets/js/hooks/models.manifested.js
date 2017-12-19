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
	 * @borrows wp.wordpoints.utils.template as template
	 */
	template: wp.wordpoints.utils.template,

	/**
	 * hooks.textTemplate( text )
	 *
	 * Returns a WordPress-style templating function for a text string.
	 *
	 * @borrows wp.wordpoints.utils.textTemplate as textTemplate
	 */
	textTemplate: wp.wordpoints.utils.textTemplate,

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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9tb2RlbHMubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9tb2RlbHMvYXJnLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvbW9kZWxzL2FyZ3MuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9tb2RlbHMvYmFzZS5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL21vZGVscy9ldmVudC5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL21vZGVscy9yZWFjdGlvbi5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL21vZGVscy9yZWFjdGlvbnMuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM5UUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDeEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsInZhciAkID0galF1ZXJ5LFxuXHRob29rcywgZW1wdHlGdW5jdGlvbjtcblxud2luZG93LndwID0gd2luZG93LndwIHx8IHt9O1xuXG53aW5kb3cud3Aud29yZHBvaW50cyA9IHdpbmRvdy53cC53b3JkcG9pbnRzIHx8IHt9O1xuXG5ob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3MgPSB7XG5cdG1vZGVsOiB7fSxcblx0dmlldzoge30sXG5cdGNvbnRyb2xsZXI6IHt9LFxuXHRyZWFjdG9yOiB7fSxcblx0ZXh0ZW5zaW9uOiB7fSxcblx0dXRpbDoge31cbn07XG5cbl8uZXh0ZW5kKCBob29rcywgQmFja2JvbmUuRXZlbnRzICk7XG5cbi8qKlxuICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4gKiBVVElMSVRJRVNcbiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuICovXG5cbi8vIFdlIG92ZXJyaWRlIHRoZSBiYXNpYyBgZXh0ZW5kYCBmdW5jdGlvbiBzbyB0aGF0IHdlIGNhbiBwZXJmb3JtIGFcbi8vIGxpdHRsZSBtYWdpYy5cbmhvb2tzLnV0aWwuZXh0ZW5kID0gZnVuY3Rpb24gKCkge1xuXG5cdHZhciBvYmogPSB0aGlzO1xuXG5cdC8vIEluc3RlYWQgb2YgZHVwbGljYXRpbmcgQmFja2JvbmUncyBgZXh0ZW5kKClgIGxvZ2ljIGhlcmUsIHdlIGxvb2sgdXAgdGhlIHRyZWVcblx0Ly8gdW50aWwgd2UgZmluZCB0aGUgb3JpZ2luYWwgZXh0ZW5kIGZ1bmN0aW9uIEJ5IGRvaW5nIGl0IHRoaXMgd2F5IHdlIGFyZVxuXHQvLyBjb21wYXRpYmxlIHdpdGggb3RoZXIgZXh0ZW5kKCkgbWV0aG9kcyBiZWluZyBpbnNlcnRlZCBzb21ld2hlcmUgdXAgdGhlIHRyZWVcblx0Ly8gYmVzaWRlIGp1c3QgQmFja2JvbmUncy5cblx0d2hpbGUgKCBvYmouZXh0ZW5kID09PSBob29rcy51dGlsLmV4dGVuZCApIHtcblx0XHRvYmogPSBvYmouX19zdXBlcl9fLmNvbnN0cnVjdG9yO1xuXHR9XG5cblx0Ly8gV2UgY3JlYXRlIHRoZSBleHRlbnNpb24gaGVyZSwgd2hpY2ggd2UnbGwgY2FsbCBgY2hpbGRgLiBGcm9tIG5vdyBvbiB3ZSBjYW5cblx0Ly8gdGhpbmsgb2Ygb3Vyc2VsdmVzIGFzIGEgcGFyZW50LlxuXHR2YXIgY2hpbGQgPSBvYmouZXh0ZW5kLmFwcGx5KCB0aGlzLCBhcmd1bWVudHMgKTtcblxuXHR2YXIgcGFyZW50ID0gdGhpcy5wcm90b3R5cGU7XG5cblx0dmFyIGNoaWxkTWV0aG9kcyA9IGNoaWxkLnByb3RvdHlwZTtcblxuXHQvLyBJZiB0aGVyZSBhcmUgYWxyZWFkeSBkZXNjZW5kYW50cyBkZWZpbmVkIG9uIHRoZSBwcm90b3R5cGUsIHdlIG5lZWQgdG8gYWRkIGFueVxuXHQvLyBvdmVycmlkZGVuIG1ldGhvZHMgYXMgdGhlIGNoaWxkcmVuIG9mIHRoZSBsYXN0IGdlbmVyYXRpb24uXG5cdHdoaWxlICggY2hpbGRNZXRob2RzLl9fY2hpbGRfXyApIHtcblx0XHRjaGlsZE1ldGhvZHMgPSBjaGlsZE1ldGhvZHMuX19jaGlsZF9fO1xuXHR9XG5cblx0Y2hpbGRNZXRob2RzLl9fY2hpbGRfXyA9IHt9O1xuXG5cdC8vIFRoaXMgaXMgd2hlcmUgdGhlIG1hZ2ljIGhhcHBlbnMuIElmIHRoZSBjaGlsZCBkZWZpbmVzIGFueSBvZiB0aGVcblx0Ly8gcGFyZW50J3MgcHJvdG90eXBlIG1ldGhvZHMgaW4gaXRzIG93biBwcm90b3R5cGUsIHdlIG92ZXJyaWRlIHRoZW0gd2l0aFxuXHQvLyB0aGUgcGFyZW50IG1ldGhvZCwgYnV0IHNhdmUgdGhlIGNoaWxkIG1ldGhvZCBmb3IgbGF0ZXIgaW4gYF9fY2hpbGRfX2AuXG5cdC8vIFRoaXMgYWxsb3dzIHRoZSBwYXJlbnQgbWV0aG9kIHRvIGNhbGwgdGhlIGNoaWxkIG1ldGhvZC5cblx0dmFyIGl0ZXJhdG9yID0gZnVuY3Rpb24gKCBtZXRob2QsIG5hbWUgKSB7XG5cblx0XHRpZiAoICdjb25zdHJ1Y3RvcicgPT09IG5hbWUgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0aWYgKCAhIF8uaXNGdW5jdGlvbiggbWV0aG9kICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dmFyIGNoaWxkTWV0aG9kID0gY2hpbGQucHJvdG90eXBlWyBuYW1lIF07XG5cblx0XHQvLyBJZiB0aGUgY2hpbGQgZG9lc24ndCBkZWZpbmUgdGhpcyBtZXRob2QsIHdlIHN0aWxsIGluY2x1ZGUgYSBzdHViXG5cdFx0Ly8gc28gdGhhdCB0aGUgcGFyZW50IGNhbiBjYWxsIGl0IHdpdGhvdXQgY2hlY2tpbmcgd2hldGhlciBpdCBleGlzdHMuXG5cdFx0aWYgKCBjaGlsZE1ldGhvZCA9PT0gbWV0aG9kICkge1xuXHRcdFx0Y2hpbGRNZXRob2QgPSBlbXB0eUZ1bmN0aW9uKCBuYW1lICk7XG5cdFx0fVxuXG5cdFx0Ly8gV2UgbmVlZCB0byBvdmVycmlkZSB0aGUgY2hpbGQgbWV0aG9kIGluIGNhc2UgdGhlcmUgd2lsbCBiZSBncmFuZGNoaWxkcmVuLlxuXHRcdGNoaWxkTWV0aG9kcy5fX2NoaWxkX19bIG5hbWUgXSA9IGZ1bmN0aW9uICgpIHtcblxuXHRcdFx0dmFyIF9fY2hpbGRfXztcblxuXHRcdFx0Ly8gSWYgdGhlcmUgYXJlIGdyYW5kY2hpbGRyZW4sIHdlIG5lZWQgdG8gdXBkYXRlIF9fY2hpbGRfXyB0byByZWZlciB0b1xuXHRcdFx0Ly8gdGhlbSwgaW5zdGVhZCBvZiB0byB1cyBhbmQgb3VyIHNpYmxpbmdzIChvdGhlcndpc2Ugd2UnZCBsb29wXG5cdFx0XHQvLyBpbmZpbml0ZWx5KS5cblx0XHRcdGlmICggdGhpcy5fX2NoaWxkX18gKSB7XG5cdFx0XHRcdF9fY2hpbGRfXyA9IHRoaXMuX19jaGlsZF9fO1xuXHRcdFx0XHR0aGlzLl9fY2hpbGRfXyA9IF9fY2hpbGRfXy5fX2NoaWxkX187XG5cdFx0XHR9XG5cblx0XHRcdGNoaWxkTWV0aG9kLmFwcGx5KCB0aGlzLCBhcmd1bWVudHMgKTtcblxuXHRcdFx0Ly8gUHV0IGV2ZXJ5dGhpbmcgYmFjayB3aGVuIHdlIGFyZSBkb25lLlxuXHRcdFx0aWYgKCBfX2NoaWxkX18gKSB7XG5cdFx0XHRcdHRoaXMuX19jaGlsZF9fID0gX19jaGlsZF9fO1xuXHRcdFx0fVxuXHRcdH07XG5cblx0XHRjaGlsZC5wcm90b3R5cGVbIG5hbWUgXSA9IG1ldGhvZDtcblx0fTtcblxuXHRfLmVhY2goIHBhcmVudCwgaXRlcmF0b3IsIHRoaXMgKTtcblxuXHRyZXR1cm4gY2hpbGQ7XG59O1xuXG4vKipcbiAqIEFuIGVtcHR5IGZ1bmN0aW9uLlxuICpcbiAqIFRvIGJlIHVzZWQgZm9yIFwiYWJzdHJhY3RcIiBmdW5jdGlvbnMgaW4gb2JqZWN0cyB0aGF0IHVzZSBob29rcy51dGlsLmV4dGVuZCgpLlxuICpcbiAqIEByZXR1cm5zIHsqfVxuICovXG5ob29rcy51dGlsLmVtcHR5RnVuY3Rpb24gPSBlbXB0eUZ1bmN0aW9uID0gZnVuY3Rpb24gKCBtZXRob2ROYW1lICkge1xuXG5cdHJldHVybiBmdW5jdGlvbiBzZWxmKCkge1xuXG5cdFx0aWYgKCB0aGlzLl9fY2hpbGRfXyApIHtcblxuXHRcdFx0dmFyIGRlc2NlbmRhbnQgPSB0aGlzLl9fY2hpbGRfXztcblxuXHRcdFx0Ly8gSXQgaXMgcG9zc2libGUgdGhhdCB0aGUgbWV0aG9kIG5hbWUgd2lsbCBub3QgYmUgc2V0IG9uIHRoZSBjaGlsZCwgYnV0XG5cdFx0XHQvLyB3aWxsIGJlIHNldCBvbiBhIGxvd2VyIGRlc2NlbmRhbnQuIFRoaXMgY2FuIGhhcHBlbiB3aGVuIHRoZSBtZXRob2Qgd2FzXG5cdFx0XHQvLyBub3Qgb24gdGhlIG9yaWdpbmFsIHBhdHJpYXJjaCBidXQgd2FzIHNldCBieSBhIGxhdGVyIGFuY2VzdG9yLlxuXHRcdFx0d2hpbGUgKCBkZXNjZW5kYW50ICYmICEgZGVzY2VuZGFudFsgbWV0aG9kTmFtZSBdICkge1xuXHRcdFx0XHRkZXNjZW5kYW50ID0gZGVzY2VuZGFudC5fX2NoaWxkX187XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIHdlIGZvdW5kIGEgZGVzY2VuZGFudCB3aXRoIHRoZSBtZXRob2QsIGNhbGwgaXQsIGFuZCBwYXNzIGJhY2sgYW55XG5cdFx0XHQvLyByZXR1cm5lZCB2YWx1ZS5cblx0XHRcdGlmICggZGVzY2VuZGFudCApIHtcblx0XHRcdFx0cmV0dXJuIGRlc2NlbmRhbnRbIG1ldGhvZE5hbWUgXS5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9O1xufTtcblxuLyoqXG4gKlxuICogQHBhcmFtICB7b2JqZWN0fSAgb2JqZWN0ICBUaGUgcHJpbWFyeSBwYXJhbWV0ZXIgdG8gY29tcGFyZS5cbiAqIEBwYXJhbSAge2FycmF5fSAgaGllcmFyY2h5ICBUaGUgcHJpbWFyeSBwYXJhbWV0ZXIgdG8gY29tcGFyZS5cbiAqXG4gKiBAcmV0dXJuIHtvYmplY3R9XG4gKi9cbmhvb2tzLnV0aWwuZ2V0RGVlcCA9IGZ1bmN0aW9uKCBvYmplY3QsIGhpZXJhcmNoeSApIHtcblxuXHRmb3IgKCB2YXIgaSA9IDA7IGkgPCBoaWVyYXJjaHkubGVuZ3RoOyBpKysgKSB7XG5cblx0XHRpZiAoIHR5cGVvZiBvYmplY3QgPT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0YnJlYWs7XG5cdFx0fVxuXG5cdFx0b2JqZWN0ID0gb2JqZWN0WyBoaWVyYXJjaHlbIGkgXSBdO1xuXHR9XG5cblx0cmV0dXJuIG9iamVjdDtcbn07XG5cbmhvb2tzLnV0aWwuc2V0RGVlcCA9IGZ1bmN0aW9uICggb2JqZWN0LCBoaWVyYXJjaHksIHZhbHVlICkge1xuXG5cdHZhciBmaWVsZCA9IGhpZXJhcmNoeS5wb3AoKTtcblxuXHRfLmVhY2goIGhpZXJhcmNoeSwgZnVuY3Rpb24gKCBmaWVsZCApIHtcblx0XHRpZiAoIHR5cGVvZiBvYmplY3RbIGZpZWxkIF0gPT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0aWYgKCBfLmlzTnVtYmVyKCBmaWVsZCApICkge1xuXHRcdFx0XHRvYmplY3RbIGZpZWxkIF0gPSBbXTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdG9iamVjdFsgZmllbGQgXSA9IHt9O1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdG9iamVjdCA9IG9iamVjdFsgZmllbGQgXTtcblx0fSk7XG5cblx0b2JqZWN0WyBmaWVsZCBdID0gdmFsdWU7XG59O1xuXG5cbndwLndvcmRwb2ludHMuJGNhY2hlID0gZnVuY3Rpb24gKCAkICkge1xuXG5cdHZhciBjYWNoZSA9IHt9O1xuXG5cdHJldHVybiBmdW5jdGlvbiAoIHNlbGVjdG9yICkge1xuXG5cdFx0aWYgKCB0eXBlb2YgY2FjaGVbIHNlbGVjdG9yIF0gPT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0Y2FjaGVbIHNlbGVjdG9yIF0gPSAkLmNhbGwoIHRoaXMsIHNlbGVjdG9yICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGNhY2hlWyBzZWxlY3RvciBdO1xuXHR9O1xufTtcblxud3Aud29yZHBvaW50cy4kID0gd3Aud29yZHBvaW50cy4kY2FjaGUoICQgKTtcblxuXy5leHRlbmQoIGhvb2tzLCB7XG5cdC8qKlxuXHQgKiBob29rcy50ZW1wbGF0ZSggaWQgKVxuXHQgKlxuXHQgKiBGZXRjaCBhIEphdmFTY3JpcHQgdGVtcGxhdGUgZm9yIGFuIGlkLCBhbmQgcmV0dXJuIGEgdGVtcGxhdGluZyBmdW5jdGlvbiBmb3IgaXQuXG5cdCAqXG5cdCAqIEBib3Jyb3dzIHdwLndvcmRwb2ludHMudXRpbHMudGVtcGxhdGUgYXMgdGVtcGxhdGVcblx0ICovXG5cdHRlbXBsYXRlOiB3cC53b3JkcG9pbnRzLnV0aWxzLnRlbXBsYXRlLFxuXG5cdC8qKlxuXHQgKiBob29rcy50ZXh0VGVtcGxhdGUoIHRleHQgKVxuXHQgKlxuXHQgKiBSZXR1cm5zIGEgV29yZFByZXNzLXN0eWxlIHRlbXBsYXRpbmcgZnVuY3Rpb24gZm9yIGEgdGV4dCBzdHJpbmcuXG5cdCAqXG5cdCAqIEBib3Jyb3dzIHdwLndvcmRwb2ludHMudXRpbHMudGV4dFRlbXBsYXRlIGFzIHRleHRUZW1wbGF0ZVxuXHQgKi9cblx0dGV4dFRlbXBsYXRlOiB3cC53b3JkcG9pbnRzLnV0aWxzLnRleHRUZW1wbGF0ZSxcblxuXHQvKipcblx0ICogaG9va3MucG9zdCggW2FjdGlvbl0sIFtkYXRhXSApXG5cdCAqXG5cdCAqIFNlbmRzIGEgUE9TVCByZXF1ZXN0IHRvIFdvcmRQcmVzcy5cblx0ICogU2VlIHdwLmFqYXgucG9zdCgpIGluIGB3cC1pbmNsdWRlcy9qcy93cC11dGlsLmpzYC5cblx0ICpcblx0ICogQGJvcnJvd3Mgd3AuYWpheC5wb3N0IGFzIHBvc3Rcblx0ICovXG5cdHBvc3Q6IHdwLmFqYXgucG9zdCxcblxuXHQvKipcblx0ICogaG9va3MuYWpheCggW2FjdGlvbl0sIFtvcHRpb25zXSApXG5cdCAqXG5cdCAqIFNlbmRzIGFuIFhIUiByZXF1ZXN0IHRvIFdvcmRQcmVzcy5cblx0ICogU2VlIHdwLmFqYXguc2VuZCgpIGluIGB3cC1pbmNsdWRlcy9qcy93cC11dGlsLmpzYC5cblx0ICpcblx0ICogQGJvcnJvd3Mgd3AuYWpheC5zZW5kIGFzIGFqYXhcblx0ICovXG5cdGFqYXg6IHdwLmFqYXguc2VuZCxcblxuXHQvKipcblx0ICogVHJ1bmNhdGVzIGEgc3RyaW5nIGJ5IGluamVjdGluZyBhbiBlbGxpcHNpcyBpbnRvIHRoZSBtaWRkbGUuXG5cdCAqIFVzZWZ1bCBmb3IgZmlsZW5hbWVzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge1N0cmluZ30gc3RyaW5nXG5cdCAqIEBwYXJhbSB7TnVtYmVyfSBbbGVuZ3RoPTMwXVxuXHQgKiBAcGFyYW0ge1N0cmluZ30gW3JlcGxhY2VtZW50PSZoZWxsaXA7XVxuXHQgKiBAcmV0dXJucyB7U3RyaW5nfSBUaGUgc3RyaW5nLCB1bmxlc3MgbGVuZ3RoIGlzIGdyZWF0ZXIgdGhhbiBzdHJpbmcubGVuZ3RoLlxuXHQgKi9cblx0dHJ1bmNhdGU6IGZ1bmN0aW9uKCBzdHJpbmcsIGxlbmd0aCwgcmVwbGFjZW1lbnQgKSB7XG5cdFx0bGVuZ3RoID0gbGVuZ3RoIHx8IDMwO1xuXHRcdHJlcGxhY2VtZW50ID0gcmVwbGFjZW1lbnQgfHwgJyZoZWxsaXA7JztcblxuXHRcdGlmICggc3RyaW5nLmxlbmd0aCA8PSBsZW5ndGggKSB7XG5cdFx0XHRyZXR1cm4gc3RyaW5nO1xuXHRcdH1cblxuXHRcdHJldHVybiBzdHJpbmcuc3Vic3RyKCAwLCBsZW5ndGggLyAyICkgKyByZXBsYWNlbWVudCArIHN0cmluZy5zdWJzdHIoIC0xICogbGVuZ3RoIC8gMiApO1xuXHR9XG59KTtcblxuLyoqXG4gKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqIE1PREVMU1xuICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4gKi9cblxuaG9va3MubW9kZWwuQmFzZSAgICAgICAgICAgICA9IHJlcXVpcmUoICcuL21vZGVscy9iYXNlLmpzJyApO1xuaG9va3MubW9kZWwuQXJnICAgICAgICAgICAgICA9IHJlcXVpcmUoICcuL21vZGVscy9hcmcuanMnICk7XG5ob29rcy5tb2RlbC5BcmdzICAgICAgICAgICAgID0gcmVxdWlyZSggJy4vbW9kZWxzL2FyZ3MuanMnICk7XG5ob29rcy5tb2RlbC5SZWFjdGlvbiAgICAgICAgID0gcmVxdWlyZSggJy4vbW9kZWxzL3JlYWN0aW9uLmpzJyApO1xuaG9va3MubW9kZWwuUmVhY3Rpb25zICAgICAgICA9IHJlcXVpcmUoICcuL21vZGVscy9yZWFjdGlvbnMuanMnICk7XG5ob29rcy5tb2RlbC5FdmVudCAgICAgICAgICAgID0gcmVxdWlyZSggJy4vbW9kZWxzL2V2ZW50LmpzJyApO1xuXG4vLyBDbGVhbiB1cC4gUHJldmVudHMgbW9iaWxlIGJyb3dzZXJzIGNhY2hpbmdcbiQod2luZG93KS5vbigndW5sb2FkJywgZnVuY3Rpb24oKXtcblx0d2luZG93LndwID0gbnVsbDtcbn0pO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkFyZ1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlLFxuXHRBcmc7XG5cbkFyZyA9IEJhc2UuZXh0ZW5kKHtcblx0bmFtZXNwYWNlOiAnYXJnJyxcblx0aWRBdHRyaWJ1dGU6ICdzbHVnJ1xufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQXJnO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkFyZ3NcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBBcmcgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkFyZyxcblx0QXJncztcblxuQXJncyA9IEJhY2tib25lLkNvbGxlY3Rpb24uZXh0ZW5kKHtcblxuXHRtb2RlbDogQXJnLFxuXG5cdGNvbXBhcmF0b3I6ICdzbHVnJyxcblxuXHQvLyBXZSBkb24ndCBjdXJyZW50bHkgc3VwcG9ydCBzeW5jaW5nIGdyb3Vwcywgc28gd2UgZ2l2ZSBhbiBlcnJvciBpbnN0ZWFkLlxuXHRzeW5jOiBmdW5jdGlvbiAoIG1ldGhvZCwgY29sbGVjdGlvbiwgb3B0aW9ucyApIHtcblx0XHRvcHRpb25zLmVycm9yKFxuXHRcdFx0eyBtZXNzYWdlOiAnRmV0Y2hpbmcgYW5kIHNhdmluZyBncm91cHMgb2YgaG9vayBhcmdzIGlzIG5vdCBzdXBwb3J0ZWQuJyB9XG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQXJncztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqL1xudmFyIGhvb2tzID0gd3Aud29yZHBvaW50cy5ob29rcyxcblx0ZXh0ZW5kID0gaG9va3MudXRpbC5leHRlbmQsXG5cdEJhc2U7XG5cbi8vIEFkZCBhIGJhc2UgdmlldyBzbyB3ZSBjYW4gaGF2ZSBhIHN0YW5kYXJkaXplZCB2aWV3IGJvb3RzdHJhcCBmb3IgdGhpcyBhcHAuXG5CYXNlID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKCB7XG5cblx0Ly8gRmlyc3QsIHdlIGxldCBlYWNoIHZpZXcgc3BlY2lmeSBpdHMgb3duIG5hbWVzcGFjZSwgc28gd2UgY2FuIHVzZSBpdCBhc1xuXHQvLyBhIHByZWZpeCBmb3IgYW55IHN0YW5kYXJkIGV2ZW50cyB3ZSB3YW50IHRvIGZpcmUuXG5cdG5hbWVzcGFjZTogJ19iYXNlJyxcblxuXHQvLyBXZSBoYXZlIGFuIGluaXRpYWxpemF0aW9uIGJvb3RzdHJhcC4gQmVsb3cgd2UnbGwgc2V0IHRoaW5ncyB1cCBzbyB0aGF0XG5cdC8vIHRoaXMgZ2V0cyBjYWxsZWQgZXZlbiB3aGVuIGFuIGV4dGVuZGluZyB2aWV3IHNwZWNpZmllcyBhbiBgaW5pdGlhbGl6ZWBcblx0Ly8gZnVuY3Rpb24uXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggb3B0aW9ucyApIHtcblxuXHRcdC8vIFRoZSBmaXJzdCB0aGluZyB3ZSBkbyBpcyB0byBhbGxvdyBmb3IgYSBuYW1lc3BhY2UgdG8gYmUgcGFzc2VkIGluXG5cdFx0Ly8gYXMgYW4gb3B0aW9uIHdoZW4gdGhlIHZpZXcgaXMgY29uc3RydWN0ZWQsIGluc3RlYWQgb2YgZm9yY2luZyBpdFxuXHRcdC8vIHRvIGJlIHBhcnQgb2YgdGhlIHByb3RvdHlwZSBvbmx5LlxuXHRcdGlmICggdHlwZW9mIG9wdGlvbnMubmFtZXNwYWNlICE9PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdHRoaXMubmFtZXNwYWNlID0gb3B0aW9ucy5uYW1lc3BhY2U7XG5cdFx0fVxuXG5cdFx0aWYgKCB0eXBlb2Ygb3B0aW9ucy5yZWFjdGlvbiAhPT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHR0aGlzLnJlYWN0aW9uID0gb3B0aW9ucy5yZWFjdGlvbjtcblx0XHR9XG5cblx0XHQvLyBPbmNlIHRoaW5ncyBhcmUgc2V0IHVwLCB3ZSBjYWxsIHRoZSBleHRlbmRpbmcgdmlldydzIGBpbml0aWFsaXplYCBtZXRob2QuXG5cdFx0dGhpcy5fX2NoaWxkX18uaW5pdGlhbGl6ZS5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cblx0XHQvLyBGaW5hbGx5LCB3ZSB0cmlnZ2VyIGFuIGFjdGlvbiB0byBsZXQgdGhlIHdob2xlIGFwcCBrbm93IHdlIGp1c3Rcblx0XHQvLyBjcmVhdGVkIHRoaXMgdmlldy5cblx0XHRob29rcy50cmlnZ2VyKCB0aGlzLm5hbWVzcGFjZSArICc6bW9kZWw6aW5pdCcsIHRoaXMgKTtcblx0fSxcblxuXHR2YWxpZGF0ZTogZnVuY3Rpb24gKCBhdHRyaWJ1dGVzLCBvcHRpb25zLCBlcnJvcnMgKSB7XG5cblx0XHR2YXIgbmV3RXJyb3JzID0gdGhpcy5fX2NoaWxkX18udmFsaWRhdGUuYXBwbHkoIHRoaXMsIGFyZ3VtZW50cyApO1xuXG5cdFx0ZXJyb3JzID0gZXJyb3JzIHx8IFtdO1xuXG5cdFx0aWYgKCBuZXdFcnJvcnMgKSB7XG5cdFx0XHRlcnJvcnMuY29uY2F0KCBuZXdFcnJvcnMgKTtcblx0XHR9XG5cblx0XHRob29rcy50cmlnZ2VyKFxuXHRcdFx0dGhpcy5uYW1lc3BhY2UgKyAnOm1vZGVsOnZhbGlkYXRlJ1xuXHRcdFx0LCB0aGlzXG5cdFx0XHQsIGF0dHJpYnV0ZXNcblx0XHRcdCwgZXJyb3JzXG5cdFx0XHQsIG9wdGlvbnNcblx0XHQpO1xuXG5cdFx0aWYgKCBlcnJvcnMubGVuZ3RoID4gMCApIHtcblx0XHRcdHJldHVybiBlcnJvcnM7XG5cdFx0fVxuXHR9XG5cbn0sIHsgZXh0ZW5kOiBleHRlbmQgfSApO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEJhc2U7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuRXZlbnRcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICovXG52YXIgSG9va0V2ZW50O1xuXG5Ib29rRXZlbnQgPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoe1xuXG5cdC8vIERlZmF1bHQgYXR0cmlidXRlcyBmb3IgdGhlIGV2ZW50LlxuXHRkZWZhdWx0czogZnVuY3Rpb24oKSB7XG5cdFx0cmV0dXJuIHtcblx0XHRcdG5hbWU6ICcnXG5cdFx0fTtcblx0fSxcblxuXHQvLyBXZSBkb24ndCBjdXJyZW50bHkgc3VwcG9ydCBzeW5jaW5nIGV2ZW50cywgc28gd2UgZ2l2ZSBhbiBlcnJvciBpbnN0ZWFkLlxuXHRzeW5jOiBmdW5jdGlvbiAoIG1ldGhvZCwgY29sbGVjdGlvbiwgb3B0aW9ucyApIHtcblx0XHRvcHRpb25zLmVycm9yKFxuXHRcdFx0eyBtZXNzYWdlOiAnRmV0Y2hpbmcgYW5kIHNhdmluZyBob29rIGV2ZW50cyBpcyBub3Qgc3VwcG9ydGVkLicgfVxuXHRcdCk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEhvb2tFdmVudDtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5SZWFjdGlvblxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlLFxuXHRnZXREZWVwID0gd3Aud29yZHBvaW50cy5ob29rcy51dGlsLmdldERlZXAsXG5cdFJlYWN0aW9uO1xuXG5SZWFjdGlvbiA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdyZWFjdGlvbicsXG5cblx0Ly8gRGVmYXVsdCBhdHRyaWJ1dGVzIGZvciB0aGUgcmVhY3Rpb24uXG5cdGRlZmF1bHRzOiBmdW5jdGlvbigpIHtcblx0XHRyZXR1cm4ge1xuXHRcdFx0ZGVzY3JpcHRpb246ICcnXG5cdFx0fTtcblx0fSxcblxuXHRnZXQ6IGZ1bmN0aW9uICggYXR0ciApIHtcblxuXHRcdHZhciBhdHRzID0gdGhpcy5hdHRyaWJ1dGVzO1xuXG5cdFx0aWYgKCBfLmlzQXJyYXkoIGF0dHIgKSApIHtcblx0XHRcdHJldHVybiBnZXREZWVwKCBhdHRzLCBhdHRyICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGF0dHNbIGF0dHIgXTtcblx0fSxcblxuXHQvLyBPdmVycmlkZSB0aGUgZGVmYXVsdCBzeW5jIG1ldGhvZCB0byB1c2UgV29yZFByZXNzJ3MgQWpheCBBUEkuXG5cdHN5bmM6IGZ1bmN0aW9uICggbWV0aG9kLCBtb2RlbCwgb3B0aW9ucyApIHtcblxuXHRcdG9wdGlvbnMgPSBvcHRpb25zIHx8IHt9O1xuXHRcdG9wdGlvbnMuZGF0YSA9IF8uZXh0ZW5kKCBvcHRpb25zLmRhdGEgfHwge30gKTtcblxuXHRcdHN3aXRjaCAoIG1ldGhvZCApIHtcblx0XHRcdGNhc2UgJ3JlYWQnOlxuXHRcdFx0XHRvcHRpb25zLmVycm9yKCB7IG1lc3NhZ2U6ICdGZXRjaGluZyBob29rIHJlYWN0aW9ucyBpcyBub3Qgc3VwcG9ydGVkLicgfSApO1xuXHRcdFx0XHRyZXR1cm47XG5cblx0XHRcdGNhc2UgJ2NyZWF0ZSc6XG5cdFx0XHRcdG9wdGlvbnMuZGF0YS5hY3Rpb24gPSAnd29yZHBvaW50c19hZG1pbl9jcmVhdGVfaG9va19yZWFjdGlvbic7XG5cdFx0XHRcdG9wdGlvbnMuZGF0YSA9IF8uZXh0ZW5kKCBvcHRpb25zLmRhdGEsIG1vZGVsLmF0dHJpYnV0ZXMgKTtcblx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdGNhc2UgJ3VwZGF0ZSc6XG5cdFx0XHRcdG9wdGlvbnMuZGF0YS5hY3Rpb24gPSAnd29yZHBvaW50c19hZG1pbl91cGRhdGVfaG9va19yZWFjdGlvbic7XG5cdFx0XHRcdG9wdGlvbnMuZGF0YSA9IF8uZXh0ZW5kKCBvcHRpb25zLmRhdGEsIG1vZGVsLmF0dHJpYnV0ZXMgKTtcblx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdGNhc2UgJ2RlbGV0ZSc6XG5cdFx0XHRcdG9wdGlvbnMuZGF0YS5hY3Rpb24gID0gJ3dvcmRwb2ludHNfYWRtaW5fZGVsZXRlX2hvb2tfcmVhY3Rpb24nO1xuXHRcdFx0XHRvcHRpb25zLmRhdGEuaWQgICAgICA9IG1vZGVsLmdldCggJ2lkJyApO1xuXHRcdFx0XHRvcHRpb25zLmRhdGEubm9uY2UgICA9IG1vZGVsLmdldCggJ2RlbGV0ZV9ub25jZScgKTtcblx0XHRcdFx0b3B0aW9ucy5kYXRhLnJlYWN0aW9uX3N0b3JlID0gbW9kZWwuZ2V0KCAncmVhY3Rpb25fc3RvcmUnICk7XG5cdFx0XHRcdGJyZWFrO1xuXHRcdH1cblxuXHRcdHJldHVybiB3cC5hamF4LnNlbmQoIG9wdGlvbnMsIG51bGwgKTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gUmVhY3Rpb247XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuUmVhY3Rpb25zXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuQ29sbGVjdGlvblxuICovXG52YXIgUmVhY3Rpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLlJlYWN0aW9uLFxuXHRSZWFjdGlvbnM7XG5cblJlYWN0aW9ucyA9IEJhY2tib25lLkNvbGxlY3Rpb24uZXh0ZW5kKHtcblxuXHQvLyBSZWZlcmVuY2UgdG8gdGhpcyBjb2xsZWN0aW9uJ3MgbW9kZWwuXG5cdG1vZGVsOiBSZWFjdGlvbixcblxuXHQvLyBSZWFjdGlvbnMgYXJlIHNvcnRlZCBieSB0aGVpciBvcmlnaW5hbCBpbnNlcnRpb24gb3JkZXIuXG5cdGNvbXBhcmF0b3I6ICdpZCcsXG5cblx0Ly8gV2UgZG9uJ3QgY3VycmVudGx5IHN1cHBvcnQgc3luY2luZyBncm91cHMsIHNvIHdlIGdpdmUgYW4gZXJyb3IgaW5zdGVhZC5cblx0c3luYzogZnVuY3Rpb24gKCBtZXRob2QsIGNvbGxlY3Rpb24sIG9wdGlvbnMgKSB7XG5cdFx0b3B0aW9ucy5lcnJvcihcblx0XHRcdHsgbWVzc2FnZTogJ0ZldGNoaW5nIGFuZCBzYXZpbmcgZ3JvdXBzIG9mIGhvb2sgcmVhY3Rpb25zIGlzIG5vdCBzdXBwb3J0ZWQuJyB9XG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gUmVhY3Rpb25zO1xuIl19
