(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.Args
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var ArgsCollection = wp.wordpoints.hooks.model.Args,
	l10n = wp.wordpoints.hooks.view.l10n,
	Args;

Args = Backbone.Model.extend({

	defaults: {
		events: {},
		entities: {}
	},

	getEventArg: function ( eventSlug, slug ) {

		var event = this.get( 'events' )[ eventSlug ];

		if ( ! event || ! event.args || ! event.args[ slug ] ) {
			return false;
		}

		var entity = this.getEntity( slug );

		_.extend( entity.attributes, event.args[ slug ] );

		return entity;
	},

	getEventArgs: function ( eventSlug ) {

		var argsCollection = new ArgsCollection(),
			event = this.get( 'events' )[ eventSlug ];

		if ( typeof event === 'undefined' || typeof event.args === 'undefined' ) {
			return argsCollection;
		}

		_.each( event.args, function ( arg ) {

			var entity = this.getEntity( arg.slug );

			if ( ! entity ) {
				return;
			}

			_.extend( entity.attributes, arg );

			argsCollection.add( entity );

		}, this );

		return argsCollection;
	},

	isEventRepeatable: function ( slug ) {

		var args = this.getEventArgs( slug );

		return _.isEmpty( args.where( { is_stateful: false } ) );
	},

	parseArgSlug: function ( slug ) {

		var isArray = false,
			isAlias = false;

		if ( '{}' === slug.slice( -2 ) ) {
			isArray = true;
			slug = slug.slice( 0, -2 );
		}

		var parts = slug.split( ':', 2 );

		if ( parts[1] ) {
			isAlias = parts[0];
			slug = parts[1];
		}

		return { slug: slug, isArray: isArray, isAlias: isAlias };
	},

	_getEntityData: function ( slug ) {

		var parsed = this.parseArgSlug( slug ),
			entity = this.get( 'entities' )[ parsed.slug ];

		if ( ! entity ) {
			return false;
		}

		entity = _.extend( {}, entity, { slug: slug, _canonical: parsed.slug } );

		return entity;
	},

	getEntity: function ( slug ) {

		if ( slug instanceof Entity ) {
			return slug;
		}

		var entity = this._getEntityData( slug );

		if ( ! entity ) {
			return false;
		}

		entity = new Entity( entity );

		return entity;
	},

	getChildren: function ( slug ) {

		var entity = this._getEntityData( slug );

		if ( ! entity ) {
			return false;
		}

		var children = new ArgsCollection();

		_.each( entity.children, function ( child ) {

			var argType = Args.type[ child._type ];

			if ( ! argType ) {
				return;
			}

			children.add( new argType( child ) );

		}, this );

		return children;
	},

	getChild: function ( entitySlug, childSlug ) {

		var entity = this._getEntityData( entitySlug );

		if ( ! entity ) {
			return false;
		}

		var child = entity.children[ childSlug ];

		if ( ! child ) {
			return false;
		}

		var argType = Args.type[ child._type ];

		if ( ! argType ) {
			return;
		}

		return new argType( child );
	},

	/**
	 *
	 * @param hierarchy
	 * @param eventSlug Optional event for context.
	 * @returns {*}
	 */
	getArgsFromHierarchy: function ( hierarchy, eventSlug ) {

		var args = [], parent, arg, slug;

		for ( var i = 0; i < hierarchy.length; i++ ) {

			slug = hierarchy[ i ];

			if ( parent ) {
				if ( ! parent.getChild ) {
					return false;
				}

				arg = parent.getChild( slug );
			} else {
				if ( eventSlug && this.parseArgSlug( slug ).isAlias ) {
					arg = this.getEventArg( eventSlug, slug );
				} else {
					arg = this.getEntity( slug );
				}
			}

			if ( ! arg ) {
				return false;
			}

			parent = arg;

			args.push( arg );
		}

		return args;
	},

	getHierarchiesMatching: function ( options ) {

		var args = [], hierarchies = [], hierarchy = [];

		options = _.extend( {}, options );

		if ( options.event ) {
			options.top = this.getEventArgs( options.event ).models;
		}

		if ( options.top ) {
			args = _.isArray( options.top ) ? options.top : [ options.top ];
		} else {
			args = _.keys( this.get( 'entities' ) );
		}

		var matcher = this._hierarchyMatcher( options, hierarchy, hierarchies );

		if ( ! matcher ) {
			return hierarchies;
		}

		_.each( args, function ( slug ) {

			var arg = this.getEntity( slug );

			this._getHierarchiesMatching(
				arg
				, hierarchy
				, hierarchies
				, matcher
			);


		}, this );

		return hierarchies;
	},

	_hierarchyMatcher: function ( options, hierarchy, hierarchies ) {

		var filters = [], i;

		if ( options.end ) {
			filters.push( {
				method: _.isFunction( options.end ) ? 'filter' : 'where',
				arg: options.end
			});
		}

		if ( ! filters ) {
			return false;
		}

		return function ( subArgs, hierachy ) {

			var matching = [], matches;

			if ( subArgs instanceof Backbone.Collection ) {
				subArgs = subArgs.models;
			} else {
				subArgs = _.clone( subArgs );
			}

			_.each( subArgs, function ( match ) {
				match.hierachy = hierachy;
				matching.push( match );
			});

			matching = new ArgsCollection( matching );

			for ( i = 0; i < filters.length; i++ ) {

				matches = matching[ filters[ i ].method ]( filters[ i ].arg );

				if ( _.isEmpty( matches ) ) {
					return;
				}

				matching.reset( matches );
			}

			matching.each( function ( match ) {
				hierarchy.push( match );
				hierarchies.push( _.clone( hierarchy ) );
				hierarchy.pop();
			});
		};
	},

	_getHierarchiesMatching: function ( arg, hierarchy, hierarchies, addMatching ) {

		var subArgs;

		// Check the top-level args as well.
		if ( hierarchy.length === 0 ) {
			addMatching( [ arg ], hierarchy );
		}

		if ( arg instanceof Parent ) {
			subArgs = arg.getChildren();
		}

		if ( ! subArgs ) {
			return;
		}

		// If this is an entity, check if that entity is already in the
		// hierarchy, and don't add it again, to prevent infinite loops.
		if ( hierarchy.length % 2 === 0 ) {
			var loops = _.filter( hierarchy, function ( item ) {
				return item.get( 'slug' ) === arg.get( 'slug' );
			});

			// We allow it to loop twice, but not to add the entity a third time.
			if ( loops.length > 1 ) {
				return;
			}
		}

		hierarchy.push( arg );

		addMatching( subArgs, hierarchy );

		subArgs.each( function ( subArg ) {

			this._getHierarchiesMatching(
				subArg
				, hierarchy
				, hierarchies
				, addMatching
			);

		}, this );

		hierarchy.pop();
	},

	buildHierarchyHumanId: function ( hierarchy ) {

		var humanId = '';

		_.each( hierarchy, function ( arg) {

			if ( ! arg ) {
				return;
			}

			var title = arg.get( 'title' );

			if ( ! title ) {
				return;
			}

			if ( '' !== humanId ) {
				// We compress relationships.
				if ( arg instanceof Entity ) {
					return;
				}

				humanId += l10n.separator;
			}

			humanId += title;
		});

		return humanId;
	}

}, { type: {} });

var Arg = Backbone.Model.extend({

	type: 'arg',

	idAttribute: 'slug',

	defaults: function () {
		return { _type: this.type };
	}
});

var Parent = Arg.extend({

	/**
	 * @abstract
	 *
	 * @param {string} slug The child slug.
	 */
	getChild: function () {},

	/**
	 * @abstract
	 */
	getChildren: function () {}
});

var Entity = Parent.extend({
	type: 'entity',

	getChild: function ( slug ) {
		return wp.wordpoints.hooks.Args.getChild( this.get( 'slug' ), slug );
	},

	getChildren: function () {
		return wp.wordpoints.hooks.Args.getChildren( this.get( 'slug' ) );
	}
});

var Relationship = Parent.extend({
	type: 'relationship',

	parseArgSlug: function ( slug ) {

		var isArray = false;

		if ( '{}' === slug.slice( -2 ) ) {
			isArray = true;
			slug = slug.slice( 0, -2 );
		}

		return { isArray: isArray, slug: slug };
	},

	getChild: function ( slug ) {

		var child;

		if ( slug !== this.get( 'secondary' ) ) {
			return false;
		}

		var parsed = this.parseArgSlug( slug );

		if ( parsed.isArray ) {
			child = new EntityArray({ entity_slug: parsed.slug });
		} else {
			child = wp.wordpoints.hooks.Args.getEntity( parsed.slug );
		}

		return child;
	},

	getChildren: function () {
		return new ArgsCollection( [ this.getChild( this.get( 'secondary' ) ) ] );
	}
});

var EntityArray = Arg.extend( {
	type: 'array',

	initialize: function () {
		this.set( 'slug', this.get( 'entity_slug' ) + '{}' );
	}
});

var Attr = Arg.extend( {
	type: 'attr'
});

Args.type.entity = Entity;
Args.type.relationship = Relationship;
Args.type.array = EntityArray;
Args.type.attr = Attr;

module.exports = Args;

},{}],2:[function(require,module,exports){
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

},{}],3:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.controller.Extensions
 *
 * @class
 * @augments Backbone.Collection
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	Extensions;

Extensions = Backbone.Collection.extend({
	model: Extension
});

module.exports = Extensions;
},{}],4:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.controller.Fields
 *
 * @since 2.1.0
 *
 * @class
 * @augments Backbone.Model
 */
var $ = Backbone.$,
	hooks = wp.wordpoints.hooks,
	l10n = wp.wordpoints.hooks.view.l10n,
	template = wp.wordpoints.hooks.template,
	textTemplate = wp.wordpoints.hooks.textTemplate,
	Fields;

Fields = Backbone.Model.extend({

	defaults: {
		fields: {}
	},

	template: template( 'hook-reaction-field' ),
	templateHidden: template( 'hook-reaction-hidden-field' ),
	templateSelect: template( 'hook-reaction-select-field' ),

	emptyMessage: textTemplate( l10n.emptyField ),

	initialize: function () {

		this.listenTo( hooks, 'reaction:model:validate', this.validateReaction );
		this.listenTo( hooks, 'reaction:view:init', this.initReaction );

		this.attributes.fields.event = {
			type: 'hidden',
			required: true
		};
	},

	create: function ( name, value, data ) {

		if ( typeof value === 'undefined' && data['default'] ) {
			value = data['default'];
		}

		data = _.extend(
			{ name: this.getFieldName( name ), value: value }
			, data
		);

		switch ( data.type ) {
			case 'select':
				return this.createSelect( data );

			case 'hidden':
				return this.templateHidden( data );
		}

		var DataType = DataTypes.get( data.type );

		if ( DataType ) {
			return DataType.createField( data );
		} else {
			return this.template( data );
		}
	},

	createSelect: function ( data, template ) {

		var $template = $( '<div></div>' ).html( template || this.templateSelect( data ) ),
			options = '',
			foundValue = typeof data.value === 'undefined'
				|| typeof data.options[ data.value ] !== 'undefined';

		if ( ! $template ) {
			$template = $( '<div></div>' ).html( this.templateSelect( data ) );
		}

		_.each( data.options, function ( option, index ) {

			var value, label;

			if ( option.value ) {
				value = option.value;
				label = option.label;

				if ( ! foundValue && data.value === value ) {
					foundValue = true;
				}
			} else {
				value = index;
				label = option;
			}

			options += $( '<option></option>' )
				.attr( 'value', value )
				.text( label ? label : value )
				.prop( 'outerHTML' );
		});

		// If the current value isn't in the list, add it in.
		if ( ! foundValue ) {
			options += $( '<option></option>' )
				.attr( 'value', data.value )
				.text( data.value )
				.prop( 'outerHTML' );
		}

		$template.find( 'select' )
			.append( options )
			.val( data.value )
			.find( ':selected' )
				.attr( 'selected', true );

		return $template.html();
	},

	getFieldName: function ( field ) {

		if ( _.isArray( field ) ) {

			field = _.clone( field );

			if ( 1 === field.length ) {
				field = field.shift();
			} else {
				field = field.shift() + '[' + field.join( '][' ) + ']';
			}
		}

		return field;
	},

	getAttrSlug: function ( reaction, fieldName ) {

		var name = fieldName;

		var nameParts = [],
			firstBracket = name.indexOf( '[' );

		// If this isn't an array-syntax name, we don't need to process it.
		if ( -1 === firstBracket ) {
			return name;
		}

		// Usually the bracket will be proceeded by something: `array[...]`.
		if ( 0 !== firstBracket ) {
			nameParts.push( name.substring( 0, firstBracket ) );
			name = name.substring( firstBracket );
		}

		nameParts = nameParts.concat( name.slice( 1, -1 ).split( '][' ) );

		// If the last element is empty, it is a non-associative array: `a[]`
		if ( nameParts[ nameParts.length - 1 ] === '' ) {
			nameParts.pop();
		}

		return nameParts;
	},

	// Get the data from a form as key => value pairs.
	getFormData: function ( reaction, $form ) {

		var formObj = {},
			inputs = $form.find( ':input' ).serializeArray();

		_.each( inputs, function ( input ) {
			formObj[ input.name ] = input.value;
		} );

		return this.arrayify( formObj );
	},

	arrayify: function ( formData ) {

		var arrayData = {};

		_.each( formData, function ( value, name ) {

			var nameParts = [],
				data = arrayData,
				isArray = false,
				firstBracket = name.indexOf( '[' );

			// If this isn't an array-syntax name, we don't need to process it.
			if ( -1 === firstBracket ) {
				data[ name ] = value;
				return;
			}

			// Usually the bracket will be proceeded by something: `array[...]`.
			if ( 0 !== firstBracket ) {
				nameParts.push( name.substring( 0, firstBracket ) );
				name = name.substring( firstBracket );
			}

			nameParts = nameParts.concat( name.slice( 1, -1 ).split( '][' ) );

			// If the last element is empty, it is a non-associative array: `a[]`
			if ( nameParts[ nameParts.length - 1 ] === '' ) {
				isArray = true;
				nameParts.pop();
			}

			var key = nameParts.pop();

			// Construct the hierarchical object.
			_.each( nameParts, function ( part ) {
				data = data[ part ] = ( data[ part ] || {} );
			});

			// Set the value.
			if ( isArray ) {

				if ( typeof data[ key ] === 'undefined' ) {
					data[ key ] = [];
				}

				data[ key ].push( value );

			} else {
				data[ key ] = value;
			}
		});

		return arrayData;
	},

	validate: function ( fields, attributes, errors ) {

		_.each( fields, function ( field, slug ) {
			if (
				field.required
				&& (
					typeof attributes[ slug ] === 'undefined'
					|| '' === $.trim( attributes[ slug ] )
				)
			) {
				errors.push( {
					field: slug,
					message: this.emptyMessage( field )
				} );
			}
		}, this );
	},

	initReaction: function ( reaction ) {

		this.listenTo( reaction, 'render:settings', this.renderReaction );
	},

	renderReaction: function ( $el, currentActionType, reaction ) {

		var fieldsHTML = '';

		_.each( this.get( 'fields' ), function ( field, name ) {

			fieldsHTML += this.create(
				name,
				reaction.model.get( name ),
				field
			);

		}, this );

		$el.html( fieldsHTML );
	},

	validateReaction: function ( reaction, attributes, errors ) {

		this.validate( this.get( 'fields' ), attributes, errors );
	}
});

var DataType = Backbone.Model.extend({

	idAttribute: 'slug',

	defaults: {
		inputType: 'text'
	},

	template: template( 'hook-reaction-field' ),

	createField: function ( data ) {

		return this.template(
			_.extend( {}, data, { type: this.get( 'inputType' ) } )
		);
	}
});

var DataTypes = new Backbone.Collection();

DataTypes.add( new DataType( { slug: 'text' } ) );
DataTypes.add( new DataType( { slug: 'integer', inputType: 'number' } ) );

module.exports = Fields;

},{}],5:[function(require,module,exports){
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

},{}],6:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.controller.Reactors
 *
 * @class
 * @augments Backbone.Collection
 * @augments wp.wordpoints.hooks.controller.Extensions
 */
var Extensions = wp.wordpoints.hooks.controller.Extensions,
	Reactor = wp.wordpoints.hooks.controller.Reactor,
	Reactors;

Reactors = Extensions.extend({
	model: Reactor
});

module.exports = Reactors;
},{}],7:[function(require,module,exports){
var hooks = wp.wordpoints.hooks,
	$ = jQuery,
	data;

// Load the application once the DOM is ready.
$( function () {

	// Let all parts of the app know that we're about to start.
	hooks.trigger( 'init' );

	// We kick things off by creating the **Groups**.
	// Instead of generating new elements, bind to the existing skeletons of
	// the groups already present in the HTML.
	$( '.wordpoints-hook-reaction-group-container' ).each( function () {

		var $this = $( this ),
			event;

		event = $this
			.find( '.wordpoints-hook-reaction-group' )
				.data( 'wordpoints-hooks-hook-event' );

		new hooks.view.Reactions( {
			el: $this,
			model: new hooks.model.Reactions( data.reactions[ event ] )
		} );
	} );
});

// Link any localized strings.
hooks.view.l10n = window.WordPointsHooksAdminL10n || {};

// Link any settings.
data = hooks.view.data = window.WordPointsHooksAdminData || {};

// Load the controllers.
hooks.controller.Fields     = require( './controllers/fields.js' );
hooks.controller.Extension  = require( './controllers/extension.js' );
hooks.controller.Extensions = require( './controllers/extensions.js' );
hooks.controller.Reactor    = require( './controllers/reactor.js' );
hooks.controller.Reactors   = require( './controllers/reactors.js' );
hooks.controller.Args       = require( './controllers/args.js' );

// Start them up here so that we can begin using them.
hooks.Fields     = new hooks.controller.Fields( { fields: data.fields } );
hooks.Reactors   = new hooks.controller.Reactors();
hooks.Extensions = new hooks.controller.Extensions();
hooks.Args       = new hooks.controller.Args({ events: data.events, entities: data.entities });

// Load the views.
hooks.view.Base              = require( './views/base.js' );
hooks.view.Reaction          = require( './views/reaction.js' );
hooks.view.Reactions         = require( './views/reactions.js' );
hooks.view.ArgSelector       = require( './views/arg-selector.js' );
hooks.view.ArgSelectors      = require( './views/arg-selectors.js' );
hooks.view.ArgHierarchySelector = require( './views/arg-hierarchy-selector.js' );

},{"./controllers/args.js":1,"./controllers/extension.js":2,"./controllers/extensions.js":3,"./controllers/fields.js":4,"./controllers/reactor.js":5,"./controllers/reactors.js":6,"./views/arg-hierarchy-selector.js":8,"./views/arg-selector.js":9,"./views/arg-selectors.js":10,"./views/base.js":11,"./views/reaction.js":12,"./views/reactions.js":13}],8:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.ArgSelectors
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	Args = wp.wordpoints.hooks.Args,
	template = wp.wordpoints.hooks.template,
	$ = Backbone.$,
	ArgHierarchySelector;

ArgHierarchySelector = Base.extend({

	namespace: 'arg-hierarchy-selector',

	tagName: 'div',

	template: template( 'hook-arg-selector' ),

	events: {
		'change select': 'triggerChange'
	},

	initialize: function ( options ) {
		if ( options.hierarchies ) {
			this.hierarchies = options.hierarchies;
		}
	},

	render: function () {

		this.$el.append(
			this.template( { label: this.label, name: this.cid } )
		);

		this.$select = this.$( 'select' );

		_.each( this.hierarchies, function ( hierarchy, index ) {

			var $option = $( '<option></option>' )
				.val( index )
				.text( Args.buildHierarchyHumanId( hierarchy ) );

			this.$select.append( $option );

		}, this );

		this.trigger( 'render', this );

		return this;
	},

	triggerChange: function ( event ) {

		var index = this.$select.val(),
			hierarchy, arg;

		// Don't do anything if the value hasn't really changed.
		if ( index === this.currentIndex ) {
			return;
		}

		this.currentIndex = index;

		if ( index !== false ) {
			hierarchy = this.hierarchies[ index ];

			if ( ! hierarchy ) {
				return;
			}

			arg = hierarchy[ hierarchy.length - 1 ];
		}

		this.trigger( 'change', this, arg, index, event );
	},

	getHierarchy: function () {

		var hierarchy = [];

		_.each( this.getHierarchyArgs(), function ( arg ) {
			hierarchy.push( arg.get( 'slug' ) );
		});

		return hierarchy;
	},

	getHierarchyArgs: function () {
		return this.hierarchies[ this.currentIndex ];
	}
});

module.exports = ArgHierarchySelector;

},{}],9:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.ArgSelector
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	template = wp.wordpoints.hooks.template,
	ArgSelector;

ArgSelector = Base.extend({

	namespace: 'arg-selector',

	template: template( 'hook-arg-selector' ),

	optionTemplate: template( 'hook-arg-option' ),

	events: {
		'change select': 'triggerChange'
	},

	initialize: function ( options ) {

		this.label = options.label;
		this.number = options.number;

		this.listenTo( this.collection, 'update', this.render );
		this.listenTo( this.collection, 'reset', this.render );
	},

	render: function () {

		this.$el.html(
			this.template( { label: this.label, name: this.cid + '_' + this.number } )
		);

		this.$select = this.$( 'select' );

		this.collection.each( function ( arg ) {

			this.$select.append( this.optionTemplate( arg.attributes ) );

		}, this );

		this.trigger( 'render', this );

		return this;
	},

	triggerChange: function ( event ) {

		var value = this.$select.val();

		if ( '0' === value ) {
			value = false;
		}

		this.trigger( 'change', this, value, event );
	}
});

module.exports = ArgSelector;

},{}],10:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.ArgSelectors
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	ArgSelector = wp.wordpoints.hooks.view.ArgSelector,
	ArgSelectors;

ArgSelectors = Base.extend({

	namespace: 'arg-selectors',

	tagName: 'div',

	initialize: function ( options ) {
		if ( options.args ) {
			this.args = options.args;
		}

		this.hierarchy = [];
	},

	render: function () {

		var args = this.args, arg;

		if ( args.length === 1 ) {
			arg = args.at( 0 );
			this.hierarchy.push( { arg: arg } );
			args = arg.getChildren();
		}

		this.addSelector( args );

		return this;
	},

	addSelector: function ( args ) {

		var selector = new ArgSelector({
			collection: args,
			number: this.hierarchy.length
		});

		selector.render();

		this.$el.append( selector.$el );

		selector.$( 'select' ).focus();

		this.hierarchy.push( { selector: selector } );

		this.listenTo( selector, 'change', this.update );
	},

	update: function ( selector, value ) {

		var id = selector.number,
			arg;

		// Don't do anything if the value hasn't really changed.
		if ( this.hierarchy[ id ].arg && value === this.hierarchy[ id ].arg.get( 'slug' ) ) {
			return;
		}

		if ( value ) {
			arg = selector.collection.get( value );

			if ( ! arg ) {
				return;
			}
		}

		this.trigger( 'changing', this, arg, value );

		if ( value ) {

			this.hierarchy[ id ].arg = arg;

			this.updateChildren( id );

		} else {

			// Nothing is selected, hide all child selectors.
			this.hideChildren( id );

			delete this.hierarchy[ id ].arg;
		}

		this.trigger( 'change', this, arg, value );
	},

	updateChildren: function ( id ) {

		var arg = this.hierarchy[ id ].arg, children;

		if ( arg.getChildren ) {

			children = arg.getChildren();

			// We compress relationships so we have just Post » Author instead of
			// Post » Author » User.
			if ( children.length && arg.get( '_type' ) === 'relationship' ) {
				var child = children.at( 0 );

				if ( ! child.getChildren ) {
					this.hideChildren( id );
					return;
				}

				children = child.getChildren();
			}

			// Hide any grandchild selectors.
			this.hideChildren( id + 1 );

			// Create the child selector if it does not exist.
			if ( ! this.hierarchy[ id + 1 ] ) {
				this.addSelector( children );
			} else {
				this.hierarchy[ id + 1 ].selector.collection.reset( children.models );
				this.hierarchy[ id + 1 ].selector.$el.show().find( 'select' ).focus();
			}

		} else {

			this.hideChildren( id );
		}
	},

	hideChildren: function ( id ) {
		_.each( this.hierarchy.slice( id + 1 ), function ( level ) {
			level.selector.$el.hide();
			delete level.arg;
		});
	},

	getHierarchy: function () {

		var hierarchy = [];

		_.each( this.hierarchy, function ( level ) {

			if ( ! level.arg ) {
				return;
			}

			hierarchy.push( level.arg.get( 'slug' ) );

			// Relationships are compressed, so we have to expand them here.
			if ( level.arg.get( '_type' ) === 'relationship' ) {
				hierarchy.push( level.arg.get( 'secondary' ) );
			}
		});

		return hierarchy;
	}
});

module.exports = ArgSelectors;

},{}],11:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.Base
 *
 * @class
 * @augments Backbone.View
 */
var hooks = wp.wordpoints.hooks,
	extend = hooks.util.extend,
	Base;

// Add a base view so we can have a standardized view bootstrap for this app.
Base = Backbone.View.extend( {

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

		// Once things are set up, we call the extending view's `initialize`
		// function. It is mapped to `_initialize` on the current object.
		this.__child__.initialize.apply( this, arguments );

		// Finally, we trigger an action to let the whole app know we just
		// created this view.
		hooks.trigger( this.namespace + ':view:init', this );
	}

}, { extend: extend } );

module.exports = Base;

},{}],12:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.Base
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	Fields = wp.wordpoints.hooks.Fields,
	Reactors = wp.wordpoints.hooks.Reactors,
	Args = wp.wordpoints.hooks.Args,
	$ = Backbone.$,
	l10n = wp.wordpoints.hooks.view.l10n,
	data = wp.wordpoints.hooks.view.data,
	Reaction;

// The DOM element for a reaction...
Reaction = Base.extend({

	namespace: 'reaction',

	className: 'wordpoints-hook-reaction',

	template: wp.wordpoints.hooks.template( 'hook-reaction' ),

	// The DOM events specific to an item.
	events: function () {

		var events = {
			'click .actions .delete': 'confirmDelete',
			'click .save':            'save',
			'click .cancel':          'cancel',
			'click .close':           'close',
			'click .edit':            'edit',
			'change .fields *':       'lockOpen'
		};

		/*
		 * Use feature detection to determine whether we should use the `input`
		 * event. Input is preferred but lacks support in legacy browsers.
		 */
		if ( 'oninput' in document.createElement( 'input' ) ) {
			events['input input'] = 'lockOpen';
		} else {
			events['keyup input'] = 'maybeLockOpen';
		}

		return events;
	},

	initialize: function () {

		this.listenTo( this.model, 'change:description', this.renderDescription );
		this.listenTo( this.model, 'change:reactor', this.setReactor );
		this.listenTo( this.model, 'change:reactor', this.renderTarget );
		this.listenTo( this.model, 'destroy', this.remove );
		this.listenTo( this.model, 'sync', this.showSuccess );
		this.listenTo( this.model, 'error', this.showAjaxErrors );
		this.listenTo( this.model, 'invalid', this.showValidationErrors );

		this.on( 'render:settings', this.renderTarget );

		this.setReactor();
	},

	render: function () {

		this.$el.html( this.template() );

		this.$title    = this.$( '.title' );
		this.$fields   = this.$( '.fields' );
		this.$settings = this.$fields.find( '.settings' );
		this.$target   = this.$fields.find( '.target' );

		this.renderDescription();

		this.trigger( 'render', this );

		return this;
	},

	// Re-render the title of the hook.
	renderDescription: function () {

		this.$title.text( this.model.get( 'description' ) );

		this.trigger( 'render:title', this );
	},

	renderFields: function () {

		var currentActionType = this.getCurrentActionType();

		this.trigger( 'render:settings', this.$settings, currentActionType, this );
		this.trigger( 'render:fields', this.$fields, currentActionType, this );

		this.renderedFields = true;
	},

	renderTarget: function () {

		var argTypes = this.Reactor.get( 'arg_types' ),
			end;

		// If there is just one arg type, we can use the `_.where()`-like syntax.
		if ( argTypes.length === 1 ) {

			end = { _canonical: argTypes[0], _type: 'entity' };

		} else {

			// Otherwise, we'll be need our own function, for `_.filter()`.
			end = function ( arg ) {
				return (
					arg.get( '_type' ) === 'entity'
					&& _.contains( argTypes, arg.get( '_canonical' ) )
				);
			};
		}

		var hierarchies = Args.getHierarchiesMatching( {
			event: this.model.get( 'event' ),
			end: end
		} );

		var options = [];

		_.each( hierarchies, function ( hierarchy ) {
			options.push( {
				label: Args.buildHierarchyHumanId( hierarchy ),
				value: _.pluck( _.pluck( hierarchy, 'attributes' ), 'slug' ).join( ',' )
			} );
		});

		var value = this.model.get( 'target' );

		if ( _.isArray( value ) ) {
			value = value.join( ',' );
		}

		var label = this.Reactor.get( 'target_label' );

		if ( ! label ) {
			label = l10n.target_label;
		}

		if ( ! this.model.isNew() ) {
			label += ' ' + l10n.cannotBeChanged;
		}

		var field = Fields.create(
			'target'
			, value
			, {
				type: 'select',
				options: options,
				label: label
			}
		);

		this.$target.html( field );

		if ( ! this.model.isNew() ) {
			this.$target.find( 'select' ).prop( 'disabled', true );
		}
	},

	setReactor: function () {
		this.Reactor = Reactors.get( this.model.get( 'reactor' ) );
	},

	// Get the current action type that settings are being displayed for.
	// Right now we just default this to the first action type that the reactor
	// supports which is registered for this event.
	getCurrentActionType: function () {

		var eventActionTypes = data.event_action_types[ this.model.get( 'event' ) ];

		if ( ! eventActionTypes ) {
			return;
		}

		var reactorActionTypes = this.Reactor.get( 'action_types' );

		// We loop through the reactor action types as the primary list, because it
		// is in order, while the event action types isn't in any particular order.
		// Otherwise we'd end up selecting the action types inconsistently.
		for ( var i = 0; i < reactorActionTypes.length; i++ ) {
			if ( eventActionTypes[ reactorActionTypes[ i ] ] ) {
				return reactorActionTypes[ i ];
			}
		}
	},

	// Toggle the visibility of the form.
	edit: function () {

		if ( ! this.renderedFields ) {
			this.renderFields();
		}

		// Then display the form.
		this.$fields.slideDown( 'fast' );
		this.$el.addClass( 'editing' );
	},

	// Close the form.
	close: function () {

		this.$fields.slideUp( 'fast' );
		this.$el.removeClass( 'editing' );
		this.$( '.success' ).hide();
	},

	// Maybe lock the form open when an input is altered.
	maybeLockOpen: function ( event ) {

		var $target = $( event.target );

		var attrSlug = Fields.getAttrSlug( this.model, $target.attr( 'name' ) );

		if ( $target.val() !== this.model.get( attrSlug ) + '' ) {
			this.lockOpen();
		}
	},

	// Lock the form open when the form values have been changed.
	lockOpen: function () {

		if ( this.cancelling ) {
			return;
		}

		this.$el.addClass( 'changed' );
		this.$( '.save' ).prop( 'disabled', false );
		this.$( '.success' ).fadeOut();
	},

	// Cancel editing or adding a new reaction.
	cancel: function () {

		if ( this.$el.hasClass( 'new' ) ) {

			this.model.collection.trigger( 'cancel-add-new' );
			this.remove();

			wp.a11y.speak( l10n.discardedReaction );

			return;
		}

		this.$el.removeClass( 'changed' );
		this.$( '.save' ).prop( 'disabled', true );

		this.cancelling = true;

		this.renderFields();

		this.trigger( 'cancel' );

		wp.a11y.speak( l10n.discardedChanges );

		this.cancelling = false;
	},

	// Save changes to the reaction.
	save: function () {

		this.wait();
		this.$( '.save' ).prop( 'disabled', true );

		wp.a11y.speak( l10n.saving );

		var formData = Fields.getFormData( this.model, this.$fields );

		if ( formData.target ) {
			formData.target = formData.target.split( ',' );
		}

		this.model.save( formData, { wait: true, rawAtts: formData } );
	},

	// Display a spinner while changes are being saved.
	wait: function () {

		this.$( '.spinner-overlay' ).show();
		this.$( '.err' ).slideUp();
	},

	// Confirm that a reaction is intended to be deleted before deleting it.
	confirmDelete: function () {

		var $dialog = $( '<div><p></p></div>' ),
			view = this;

		this.$( '.messages div' ).slideUp();

		$dialog
			.attr( 'title', l10n.confirmTitle )
			.find( 'p' )
				.text( l10n.confirmDelete )
			.end()
			.dialog({
				dialogClass: 'wp-dialog wordpoints-delete-hook-reaction-dialog',
				resizable: false,
				draggable: false,
				height: 250,
				modal: true,
				buttons: [
					{
						text: l10n.cancelText,
						'class': 'button-secondary',
						click: function() {
							$( this ).dialog( 'destroy' );
						}
					},
					{
						text: l10n.deleteText,
						'class': 'button-primary',
						click: function() {
							$( this ).dialog( 'destroy' );
							view.destroy();
						}
					}
				]
			});
	},

	// Remove the item, destroy the model.
	destroy: function () {

		wp.a11y.speak( l10n.deleting );

		this.wait();

		this.model.destroy(
			{
				wait: true,
				success: function () {
					wp.a11y.speak( l10n.reactionDeleted );
				}
			}
		);
	},

	// Display errors when the model has invalid fields.
	showValidationErrors: function ( model, errors ) {
		this.showError( errors );
	},

	// Display an error when there is an Ajax failure.
	showAjaxErrors: function ( event, response ) {

		var errors;

		if ( ! _.isEmpty( response.errors ) ) {
			errors = response.errors;
		} else if ( response.message ) {
			errors = response.message;
		} else {
			errors = l10n.unexpectedError;
		}

		this.showError( errors );
	},

	showError: function ( errors ) {

		var generalErrors = [];
		var a11yErrors = [];
		var $errors = this.$( '.messages .err' );

		this.$( '.spinner-overlay' ).hide();

		// Sometimes we get a list of errors.
		if ( _.isArray( errors ) ) {

			// When that happens, we loop over them and try to display each of
			// them next to their associated field.
			_.each( errors, function ( error ) {

				var $field, escapedFieldName;

				// Sometimes some of the errors aren't for any particular field
				// though, so we collect them in an array an display them all
				// together a bit later.
				if ( ! error.field ) {
					generalErrors.push( error.message );
					return;
				}

				escapedFieldName = Fields.getFieldName( error.field )
						.replace( /[^a-z0-9-_\[\]\{}\\]/gi, '' )
						.replace( /\\/g, '\\\\' );

				// When a field is specified, we try to locate it.
				$field = this.$( '[name="' + escapedFieldName + '"]' );

				if ( 0 === $field.length ) {

					// However, there are times when the error is for a field set
					// and not a single field. In that case, we try to find the
					// fields in that set.
					$field = this.$( '[name^="' + escapedFieldName + '"]' );

					// If that fails, we just add this to the general errors.
					if ( 0 === $field.length ) {
						generalErrors.push( error.message );
						return;
					}

					$field = $field.first();
				}

				$field.before(
					$( '<div class="message err"></div>' ).text( error.message )
				);

				a11yErrors.push( error.message );

			}, this );

			$errors.html( '' );

			// There may be some general errors that we need to display to the user.
			// We also add an explanation that there are some fields that need to be
			// corrected, if there were some per-field errors, to make sure that they
			// see those errors as well (since they may not be in view).
			if ( generalErrors.length < errors.length ) {
				generalErrors.unshift( l10n.fieldsInvalid );
			}

			_.each( generalErrors, function ( error ) {
				$errors.append( $( '<p></p>' ).text( error ) );
			});

			// Notify unsighted users as well.
			a11yErrors.unshift( l10n.fieldsInvalid );

			wp.a11y.speak( a11yErrors.join( ' ' ) );

		} else {

			$errors.text( errors );
			wp.a11y.speak( errors );
		}

		$errors.fadeIn();
	},

	// Display a success message.
	showSuccess: function () {

		this.$( '.spinner-overlay' ).hide();

		this.$( '.success' )
			.text( l10n.changesSaved )
			.slideDown();

		wp.a11y.speak( l10n.reactionSaved );

		this.$target.find( 'select' ).prop( 'disabled', true );

		this.$el.removeClass( 'new changed' );
	}
});

module.exports = Reaction;

},{}],13:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.Hooks
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	ReactionView = wp.wordpoints.hooks.view.Reaction,
	Reaction = wp.wordpoints.hooks.model.Reaction,
	Reactions;

Reactions = Base.extend({

	namespace: 'reactions',

	// Delegated events for creating new reactions.
	events: {
		'click .add-reaction': 'initAddReaction'
	},

	// At initialization we bind to the relevant events on the `Reactions`
	// collection, when items are added or changed. Kick things off by
	// loading any preexisting hooks from *the database*.
	initialize: function() {

		this.$reactionGroup = this.$( '.wordpoints-hook-reaction-group' );
		this.$addReaction   = this.$( '.add-reaction' );
		this.$events = this.$( '.wordpoints-hook-events' );

		if ( this.$events.length !== 0 ) {
			// Check how many different events this group supports. If it is only
			// one, we can hide the event selector.
			if ( 2 === this.$events.children( 'option' ).length ) {
				this.$events.prop( 'selectedIndex', 1 ).hide();
			}
		}

		// Make sure that the add reaction button isn't disabled, because sometimes
		// the browser will automatically disable it, e.g., if it was disabled
		// and the page was refreshed.
		this.$addReaction.prop( 'disabled', false );

		this.listenTo( this.model, 'add', this.addOne );
		this.listenTo( this.model, 'reset', this.addAll );
		this.listenTo( this.model, 'cancel-add-new', this.cancelAddReaction );

		this.addAll();
	},

	// Add a single reaction to the group by creating a view for it, and appending
	// its element to the group. If this is a new reaction we enter edit mode from
	// and lock the view open until it is saved.
	addOne: function( reaction ) {

		var view = new ReactionView( { model: reaction } ),
			element = view.render().el;

		var isNew = '' === reaction.get( 'description' );

		if ( isNew ) {
			view.edit();
			view.lockOpen();
			view.$el.addClass( 'new' );
		}

		// Append the element to the group.
		this.$reactionGroup.append( element );

		if ( isNew ) {
			view.$fields.find( ':input:visible' ).first().focus();
		}
	},

	// Add all items in the **Reactions** collection at once.
	addAll: function() {
		this.model.each( this.addOne, this );

		this.$( '.spinner-overlay' ).fadeOut();
	},

	getReactionDefaults: function () {

		var defaults = {};

		if ( this.$events.length !== 0 ) {

			// First, be sure that an event was selected.
			var event = this.$events.val();

			if ( '0' === event ) {
				// Show an error.
			}

			defaults.event = event;
			defaults.nonce = this.$events
				.find(
					'option[value="' + event.replace( /[^a-z0-9-_]/gi, '' ) + '"]'
				)
				.data( 'nonce' );

		} else {

			defaults.event = this.$reactionGroup.data( 'wordpoints-hooks-hook-event' );
			defaults.nonce = this.$reactionGroup.data( 'wordpoints-hooks-create-nonce' );
		}

		defaults.reactor = this.$reactionGroup.data( 'wordpoints-hooks-reactor' );
		defaults.reaction_store = this.$reactionGroup.data( 'wordpoints-hooks-reaction-store' );

		this.trigger( 'hook-reaction-defaults', defaults, this );

		return defaults;
	},

	// Show the form for a new reaction.
	initAddReaction: function () {

		var data = this.getReactionDefaults();

		this.$addReaction.prop( 'disabled', true );

		var reaction = new Reaction( data );

		this.model.add( [ reaction ] );

		// Re-enable the submit button when a new reaction is saved.
		this.listenToOnce( reaction, 'sync', function () {
			this.$addReaction.prop( 'disabled', false );
		});
	},

	// When a new reaction is removed, re-enable the add reaction button.
	cancelAddReaction: function () {
		this.$addReaction.prop( 'disabled', false );
	}
});

module.exports = Reactions;

},{}]},{},[7])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9jb250cm9sbGVycy9hcmdzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvY29udHJvbGxlcnMvZXh0ZW5zaW9uLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvY29udHJvbGxlcnMvZXh0ZW5zaW9ucy5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2NvbnRyb2xsZXJzL2ZpZWxkcy5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2NvbnRyb2xsZXJzL3JlYWN0b3IuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9jb250cm9sbGVycy9yZWFjdG9ycy5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL3ZpZXdzLm1hbmlmZXN0LmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3Mvdmlld3MvYXJnLWhpZXJhcmNoeS1zZWxlY3Rvci5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL3ZpZXdzL2FyZy1zZWxlY3Rvci5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL3ZpZXdzL2FyZy1zZWxlY3RvcnMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy92aWV3cy9iYXNlLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3Mvdmlld3MvcmVhY3Rpb24uanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy92aWV3cy9yZWFjdGlvbnMuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN2ZEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN0RUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNiQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzFTQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDZkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3hEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuS0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDN0NBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BkQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkFyZ3NcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEFyZ3NDb2xsZWN0aW9uID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5BcmdzLFxuXHRsMTBuID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LmwxMG4sXG5cdEFyZ3M7XG5cbkFyZ3MgPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0ZXZlbnRzOiB7fSxcblx0XHRlbnRpdGllczoge31cblx0fSxcblxuXHRnZXRFdmVudEFyZzogZnVuY3Rpb24gKCBldmVudFNsdWcsIHNsdWcgKSB7XG5cblx0XHR2YXIgZXZlbnQgPSB0aGlzLmdldCggJ2V2ZW50cycgKVsgZXZlbnRTbHVnIF07XG5cblx0XHRpZiAoICEgZXZlbnQgfHwgISBldmVudC5hcmdzIHx8ICEgZXZlbnQuYXJnc1sgc2x1ZyBdICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHZhciBlbnRpdHkgPSB0aGlzLmdldEVudGl0eSggc2x1ZyApO1xuXG5cdFx0Xy5leHRlbmQoIGVudGl0eS5hdHRyaWJ1dGVzLCBldmVudC5hcmdzWyBzbHVnIF0gKTtcblxuXHRcdHJldHVybiBlbnRpdHk7XG5cdH0sXG5cblx0Z2V0RXZlbnRBcmdzOiBmdW5jdGlvbiAoIGV2ZW50U2x1ZyApIHtcblxuXHRcdHZhciBhcmdzQ29sbGVjdGlvbiA9IG5ldyBBcmdzQ29sbGVjdGlvbigpLFxuXHRcdFx0ZXZlbnQgPSB0aGlzLmdldCggJ2V2ZW50cycgKVsgZXZlbnRTbHVnIF07XG5cblx0XHRpZiAoIHR5cGVvZiBldmVudCA9PT0gJ3VuZGVmaW5lZCcgfHwgdHlwZW9mIGV2ZW50LmFyZ3MgPT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0cmV0dXJuIGFyZ3NDb2xsZWN0aW9uO1xuXHRcdH1cblxuXHRcdF8uZWFjaCggZXZlbnQuYXJncywgZnVuY3Rpb24gKCBhcmcgKSB7XG5cblx0XHRcdHZhciBlbnRpdHkgPSB0aGlzLmdldEVudGl0eSggYXJnLnNsdWcgKTtcblxuXHRcdFx0aWYgKCAhIGVudGl0eSApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRfLmV4dGVuZCggZW50aXR5LmF0dHJpYnV0ZXMsIGFyZyApO1xuXG5cdFx0XHRhcmdzQ29sbGVjdGlvbi5hZGQoIGVudGl0eSApO1xuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0cmV0dXJuIGFyZ3NDb2xsZWN0aW9uO1xuXHR9LFxuXG5cdGlzRXZlbnRSZXBlYXRhYmxlOiBmdW5jdGlvbiAoIHNsdWcgKSB7XG5cblx0XHR2YXIgYXJncyA9IHRoaXMuZ2V0RXZlbnRBcmdzKCBzbHVnICk7XG5cblx0XHRyZXR1cm4gXy5pc0VtcHR5KCBhcmdzLndoZXJlKCB7IGlzX3N0YXRlZnVsOiBmYWxzZSB9ICkgKTtcblx0fSxcblxuXHRwYXJzZUFyZ1NsdWc6IGZ1bmN0aW9uICggc2x1ZyApIHtcblxuXHRcdHZhciBpc0FycmF5ID0gZmFsc2UsXG5cdFx0XHRpc0FsaWFzID0gZmFsc2U7XG5cblx0XHRpZiAoICd7fScgPT09IHNsdWcuc2xpY2UoIC0yICkgKSB7XG5cdFx0XHRpc0FycmF5ID0gdHJ1ZTtcblx0XHRcdHNsdWcgPSBzbHVnLnNsaWNlKCAwLCAtMiApO1xuXHRcdH1cblxuXHRcdHZhciBwYXJ0cyA9IHNsdWcuc3BsaXQoICc6JywgMiApO1xuXG5cdFx0aWYgKCBwYXJ0c1sxXSApIHtcblx0XHRcdGlzQWxpYXMgPSBwYXJ0c1swXTtcblx0XHRcdHNsdWcgPSBwYXJ0c1sxXTtcblx0XHR9XG5cblx0XHRyZXR1cm4geyBzbHVnOiBzbHVnLCBpc0FycmF5OiBpc0FycmF5LCBpc0FsaWFzOiBpc0FsaWFzIH07XG5cdH0sXG5cblx0X2dldEVudGl0eURhdGE6IGZ1bmN0aW9uICggc2x1ZyApIHtcblxuXHRcdHZhciBwYXJzZWQgPSB0aGlzLnBhcnNlQXJnU2x1Zyggc2x1ZyApLFxuXHRcdFx0ZW50aXR5ID0gdGhpcy5nZXQoICdlbnRpdGllcycgKVsgcGFyc2VkLnNsdWcgXTtcblxuXHRcdGlmICggISBlbnRpdHkgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0ZW50aXR5ID0gXy5leHRlbmQoIHt9LCBlbnRpdHksIHsgc2x1Zzogc2x1ZywgX2Nhbm9uaWNhbDogcGFyc2VkLnNsdWcgfSApO1xuXG5cdFx0cmV0dXJuIGVudGl0eTtcblx0fSxcblxuXHRnZXRFbnRpdHk6IGZ1bmN0aW9uICggc2x1ZyApIHtcblxuXHRcdGlmICggc2x1ZyBpbnN0YW5jZW9mIEVudGl0eSApIHtcblx0XHRcdHJldHVybiBzbHVnO1xuXHRcdH1cblxuXHRcdHZhciBlbnRpdHkgPSB0aGlzLl9nZXRFbnRpdHlEYXRhKCBzbHVnICk7XG5cblx0XHRpZiAoICEgZW50aXR5ICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdGVudGl0eSA9IG5ldyBFbnRpdHkoIGVudGl0eSApO1xuXG5cdFx0cmV0dXJuIGVudGl0eTtcblx0fSxcblxuXHRnZXRDaGlsZHJlbjogZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0dmFyIGVudGl0eSA9IHRoaXMuX2dldEVudGl0eURhdGEoIHNsdWcgKTtcblxuXHRcdGlmICggISBlbnRpdHkgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0dmFyIGNoaWxkcmVuID0gbmV3IEFyZ3NDb2xsZWN0aW9uKCk7XG5cblx0XHRfLmVhY2goIGVudGl0eS5jaGlsZHJlbiwgZnVuY3Rpb24gKCBjaGlsZCApIHtcblxuXHRcdFx0dmFyIGFyZ1R5cGUgPSBBcmdzLnR5cGVbIGNoaWxkLl90eXBlIF07XG5cblx0XHRcdGlmICggISBhcmdUeXBlICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNoaWxkcmVuLmFkZCggbmV3IGFyZ1R5cGUoIGNoaWxkICkgKTtcblxuXHRcdH0sIHRoaXMgKTtcblxuXHRcdHJldHVybiBjaGlsZHJlbjtcblx0fSxcblxuXHRnZXRDaGlsZDogZnVuY3Rpb24gKCBlbnRpdHlTbHVnLCBjaGlsZFNsdWcgKSB7XG5cblx0XHR2YXIgZW50aXR5ID0gdGhpcy5fZ2V0RW50aXR5RGF0YSggZW50aXR5U2x1ZyApO1xuXG5cdFx0aWYgKCAhIGVudGl0eSApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHR2YXIgY2hpbGQgPSBlbnRpdHkuY2hpbGRyZW5bIGNoaWxkU2x1ZyBdO1xuXG5cdFx0aWYgKCAhIGNoaWxkICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHZhciBhcmdUeXBlID0gQXJncy50eXBlWyBjaGlsZC5fdHlwZSBdO1xuXG5cdFx0aWYgKCAhIGFyZ1R5cGUgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0cmV0dXJuIG5ldyBhcmdUeXBlKCBjaGlsZCApO1xuXHR9LFxuXG5cdC8qKlxuXHQgKlxuXHQgKiBAcGFyYW0gaGllcmFyY2h5XG5cdCAqIEBwYXJhbSBldmVudFNsdWcgT3B0aW9uYWwgZXZlbnQgZm9yIGNvbnRleHQuXG5cdCAqIEByZXR1cm5zIHsqfVxuXHQgKi9cblx0Z2V0QXJnc0Zyb21IaWVyYXJjaHk6IGZ1bmN0aW9uICggaGllcmFyY2h5LCBldmVudFNsdWcgKSB7XG5cblx0XHR2YXIgYXJncyA9IFtdLCBwYXJlbnQsIGFyZywgc2x1ZztcblxuXHRcdGZvciAoIHZhciBpID0gMDsgaSA8IGhpZXJhcmNoeS5sZW5ndGg7IGkrKyApIHtcblxuXHRcdFx0c2x1ZyA9IGhpZXJhcmNoeVsgaSBdO1xuXG5cdFx0XHRpZiAoIHBhcmVudCApIHtcblx0XHRcdFx0aWYgKCAhIHBhcmVudC5nZXRDaGlsZCApIHtcblx0XHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRhcmcgPSBwYXJlbnQuZ2V0Q2hpbGQoIHNsdWcgKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGlmICggZXZlbnRTbHVnICYmIHRoaXMucGFyc2VBcmdTbHVnKCBzbHVnICkuaXNBbGlhcyApIHtcblx0XHRcdFx0XHRhcmcgPSB0aGlzLmdldEV2ZW50QXJnKCBldmVudFNsdWcsIHNsdWcgKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRhcmcgPSB0aGlzLmdldEVudGl0eSggc2x1ZyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cblx0XHRcdGlmICggISBhcmcgKSB7XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0cGFyZW50ID0gYXJnO1xuXG5cdFx0XHRhcmdzLnB1c2goIGFyZyApO1xuXHRcdH1cblxuXHRcdHJldHVybiBhcmdzO1xuXHR9LFxuXG5cdGdldEhpZXJhcmNoaWVzTWF0Y2hpbmc6IGZ1bmN0aW9uICggb3B0aW9ucyApIHtcblxuXHRcdHZhciBhcmdzID0gW10sIGhpZXJhcmNoaWVzID0gW10sIGhpZXJhcmNoeSA9IFtdO1xuXG5cdFx0b3B0aW9ucyA9IF8uZXh0ZW5kKCB7fSwgb3B0aW9ucyApO1xuXG5cdFx0aWYgKCBvcHRpb25zLmV2ZW50ICkge1xuXHRcdFx0b3B0aW9ucy50b3AgPSB0aGlzLmdldEV2ZW50QXJncyggb3B0aW9ucy5ldmVudCApLm1vZGVscztcblx0XHR9XG5cblx0XHRpZiAoIG9wdGlvbnMudG9wICkge1xuXHRcdFx0YXJncyA9IF8uaXNBcnJheSggb3B0aW9ucy50b3AgKSA/IG9wdGlvbnMudG9wIDogWyBvcHRpb25zLnRvcCBdO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRhcmdzID0gXy5rZXlzKCB0aGlzLmdldCggJ2VudGl0aWVzJyApICk7XG5cdFx0fVxuXG5cdFx0dmFyIG1hdGNoZXIgPSB0aGlzLl9oaWVyYXJjaHlNYXRjaGVyKCBvcHRpb25zLCBoaWVyYXJjaHksIGhpZXJhcmNoaWVzICk7XG5cblx0XHRpZiAoICEgbWF0Y2hlciApIHtcblx0XHRcdHJldHVybiBoaWVyYXJjaGllcztcblx0XHR9XG5cblx0XHRfLmVhY2goIGFyZ3MsIGZ1bmN0aW9uICggc2x1ZyApIHtcblxuXHRcdFx0dmFyIGFyZyA9IHRoaXMuZ2V0RW50aXR5KCBzbHVnICk7XG5cblx0XHRcdHRoaXMuX2dldEhpZXJhcmNoaWVzTWF0Y2hpbmcoXG5cdFx0XHRcdGFyZ1xuXHRcdFx0XHQsIGhpZXJhcmNoeVxuXHRcdFx0XHQsIGhpZXJhcmNoaWVzXG5cdFx0XHRcdCwgbWF0Y2hlclxuXHRcdFx0KTtcblxuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0cmV0dXJuIGhpZXJhcmNoaWVzO1xuXHR9LFxuXG5cdF9oaWVyYXJjaHlNYXRjaGVyOiBmdW5jdGlvbiAoIG9wdGlvbnMsIGhpZXJhcmNoeSwgaGllcmFyY2hpZXMgKSB7XG5cblx0XHR2YXIgZmlsdGVycyA9IFtdLCBpO1xuXG5cdFx0aWYgKCBvcHRpb25zLmVuZCApIHtcblx0XHRcdGZpbHRlcnMucHVzaCgge1xuXHRcdFx0XHRtZXRob2Q6IF8uaXNGdW5jdGlvbiggb3B0aW9ucy5lbmQgKSA/ICdmaWx0ZXInIDogJ3doZXJlJyxcblx0XHRcdFx0YXJnOiBvcHRpb25zLmVuZFxuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0aWYgKCAhIGZpbHRlcnMgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGZ1bmN0aW9uICggc3ViQXJncywgaGllcmFjaHkgKSB7XG5cblx0XHRcdHZhciBtYXRjaGluZyA9IFtdLCBtYXRjaGVzO1xuXG5cdFx0XHRpZiAoIHN1YkFyZ3MgaW5zdGFuY2VvZiBCYWNrYm9uZS5Db2xsZWN0aW9uICkge1xuXHRcdFx0XHRzdWJBcmdzID0gc3ViQXJncy5tb2RlbHM7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRzdWJBcmdzID0gXy5jbG9uZSggc3ViQXJncyApO1xuXHRcdFx0fVxuXG5cdFx0XHRfLmVhY2goIHN1YkFyZ3MsIGZ1bmN0aW9uICggbWF0Y2ggKSB7XG5cdFx0XHRcdG1hdGNoLmhpZXJhY2h5ID0gaGllcmFjaHk7XG5cdFx0XHRcdG1hdGNoaW5nLnB1c2goIG1hdGNoICk7XG5cdFx0XHR9KTtcblxuXHRcdFx0bWF0Y2hpbmcgPSBuZXcgQXJnc0NvbGxlY3Rpb24oIG1hdGNoaW5nICk7XG5cblx0XHRcdGZvciAoIGkgPSAwOyBpIDwgZmlsdGVycy5sZW5ndGg7IGkrKyApIHtcblxuXHRcdFx0XHRtYXRjaGVzID0gbWF0Y2hpbmdbIGZpbHRlcnNbIGkgXS5tZXRob2QgXSggZmlsdGVyc1sgaSBdLmFyZyApO1xuXG5cdFx0XHRcdGlmICggXy5pc0VtcHR5KCBtYXRjaGVzICkgKSB7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0bWF0Y2hpbmcucmVzZXQoIG1hdGNoZXMgKTtcblx0XHRcdH1cblxuXHRcdFx0bWF0Y2hpbmcuZWFjaCggZnVuY3Rpb24gKCBtYXRjaCApIHtcblx0XHRcdFx0aGllcmFyY2h5LnB1c2goIG1hdGNoICk7XG5cdFx0XHRcdGhpZXJhcmNoaWVzLnB1c2goIF8uY2xvbmUoIGhpZXJhcmNoeSApICk7XG5cdFx0XHRcdGhpZXJhcmNoeS5wb3AoKTtcblx0XHRcdH0pO1xuXHRcdH07XG5cdH0sXG5cblx0X2dldEhpZXJhcmNoaWVzTWF0Y2hpbmc6IGZ1bmN0aW9uICggYXJnLCBoaWVyYXJjaHksIGhpZXJhcmNoaWVzLCBhZGRNYXRjaGluZyApIHtcblxuXHRcdHZhciBzdWJBcmdzO1xuXG5cdFx0Ly8gQ2hlY2sgdGhlIHRvcC1sZXZlbCBhcmdzIGFzIHdlbGwuXG5cdFx0aWYgKCBoaWVyYXJjaHkubGVuZ3RoID09PSAwICkge1xuXHRcdFx0YWRkTWF0Y2hpbmcoIFsgYXJnIF0sIGhpZXJhcmNoeSApO1xuXHRcdH1cblxuXHRcdGlmICggYXJnIGluc3RhbmNlb2YgUGFyZW50ICkge1xuXHRcdFx0c3ViQXJncyA9IGFyZy5nZXRDaGlsZHJlbigpO1xuXHRcdH1cblxuXHRcdGlmICggISBzdWJBcmdzICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIElmIHRoaXMgaXMgYW4gZW50aXR5LCBjaGVjayBpZiB0aGF0IGVudGl0eSBpcyBhbHJlYWR5IGluIHRoZVxuXHRcdC8vIGhpZXJhcmNoeSwgYW5kIGRvbid0IGFkZCBpdCBhZ2FpbiwgdG8gcHJldmVudCBpbmZpbml0ZSBsb29wcy5cblx0XHRpZiAoIGhpZXJhcmNoeS5sZW5ndGggJSAyID09PSAwICkge1xuXHRcdFx0dmFyIGxvb3BzID0gXy5maWx0ZXIoIGhpZXJhcmNoeSwgZnVuY3Rpb24gKCBpdGVtICkge1xuXHRcdFx0XHRyZXR1cm4gaXRlbS5nZXQoICdzbHVnJyApID09PSBhcmcuZ2V0KCAnc2x1ZycgKTtcblx0XHRcdH0pO1xuXG5cdFx0XHQvLyBXZSBhbGxvdyBpdCB0byBsb29wIHR3aWNlLCBidXQgbm90IHRvIGFkZCB0aGUgZW50aXR5IGEgdGhpcmQgdGltZS5cblx0XHRcdGlmICggbG9vcHMubGVuZ3RoID4gMSApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdGhpZXJhcmNoeS5wdXNoKCBhcmcgKTtcblxuXHRcdGFkZE1hdGNoaW5nKCBzdWJBcmdzLCBoaWVyYXJjaHkgKTtcblxuXHRcdHN1YkFyZ3MuZWFjaCggZnVuY3Rpb24gKCBzdWJBcmcgKSB7XG5cblx0XHRcdHRoaXMuX2dldEhpZXJhcmNoaWVzTWF0Y2hpbmcoXG5cdFx0XHRcdHN1YkFyZ1xuXHRcdFx0XHQsIGhpZXJhcmNoeVxuXHRcdFx0XHQsIGhpZXJhcmNoaWVzXG5cdFx0XHRcdCwgYWRkTWF0Y2hpbmdcblx0XHRcdCk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHRoaWVyYXJjaHkucG9wKCk7XG5cdH0sXG5cblx0YnVpbGRIaWVyYXJjaHlIdW1hbklkOiBmdW5jdGlvbiAoIGhpZXJhcmNoeSApIHtcblxuXHRcdHZhciBodW1hbklkID0gJyc7XG5cblx0XHRfLmVhY2goIGhpZXJhcmNoeSwgZnVuY3Rpb24gKCBhcmcpIHtcblxuXHRcdFx0aWYgKCAhIGFyZyApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHR2YXIgdGl0bGUgPSBhcmcuZ2V0KCAndGl0bGUnICk7XG5cblx0XHRcdGlmICggISB0aXRsZSApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoICcnICE9PSBodW1hbklkICkge1xuXHRcdFx0XHQvLyBXZSBjb21wcmVzcyByZWxhdGlvbnNoaXBzLlxuXHRcdFx0XHRpZiAoIGFyZyBpbnN0YW5jZW9mIEVudGl0eSApIHtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRodW1hbklkICs9IGwxMG4uc2VwYXJhdG9yO1xuXHRcdFx0fVxuXG5cdFx0XHRodW1hbklkICs9IHRpdGxlO1xuXHRcdH0pO1xuXG5cdFx0cmV0dXJuIGh1bWFuSWQ7XG5cdH1cblxufSwgeyB0eXBlOiB7fSB9KTtcblxudmFyIEFyZyA9IEJhY2tib25lLk1vZGVsLmV4dGVuZCh7XG5cblx0dHlwZTogJ2FyZycsXG5cblx0aWRBdHRyaWJ1dGU6ICdzbHVnJyxcblxuXHRkZWZhdWx0czogZnVuY3Rpb24gKCkge1xuXHRcdHJldHVybiB7IF90eXBlOiB0aGlzLnR5cGUgfTtcblx0fVxufSk7XG5cbnZhciBQYXJlbnQgPSBBcmcuZXh0ZW5kKHtcblxuXHQvKipcblx0ICogQGFic3RyYWN0XG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBzbHVnIFRoZSBjaGlsZCBzbHVnLlxuXHQgKi9cblx0Z2V0Q2hpbGQ6IGZ1bmN0aW9uICgpIHt9LFxuXG5cdC8qKlxuXHQgKiBAYWJzdHJhY3Rcblx0ICovXG5cdGdldENoaWxkcmVuOiBmdW5jdGlvbiAoKSB7fVxufSk7XG5cbnZhciBFbnRpdHkgPSBQYXJlbnQuZXh0ZW5kKHtcblx0dHlwZTogJ2VudGl0eScsXG5cblx0Z2V0Q2hpbGQ6IGZ1bmN0aW9uICggc2x1ZyApIHtcblx0XHRyZXR1cm4gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLmdldENoaWxkKCB0aGlzLmdldCggJ3NsdWcnICksIHNsdWcgKTtcblx0fSxcblxuXHRnZXRDaGlsZHJlbjogZnVuY3Rpb24gKCkge1xuXHRcdHJldHVybiB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MuZ2V0Q2hpbGRyZW4oIHRoaXMuZ2V0KCAnc2x1ZycgKSApO1xuXHR9XG59KTtcblxudmFyIFJlbGF0aW9uc2hpcCA9IFBhcmVudC5leHRlbmQoe1xuXHR0eXBlOiAncmVsYXRpb25zaGlwJyxcblxuXHRwYXJzZUFyZ1NsdWc6IGZ1bmN0aW9uICggc2x1ZyApIHtcblxuXHRcdHZhciBpc0FycmF5ID0gZmFsc2U7XG5cblx0XHRpZiAoICd7fScgPT09IHNsdWcuc2xpY2UoIC0yICkgKSB7XG5cdFx0XHRpc0FycmF5ID0gdHJ1ZTtcblx0XHRcdHNsdWcgPSBzbHVnLnNsaWNlKCAwLCAtMiApO1xuXHRcdH1cblxuXHRcdHJldHVybiB7IGlzQXJyYXk6IGlzQXJyYXksIHNsdWc6IHNsdWcgfTtcblx0fSxcblxuXHRnZXRDaGlsZDogZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0dmFyIGNoaWxkO1xuXG5cdFx0aWYgKCBzbHVnICE9PSB0aGlzLmdldCggJ3NlY29uZGFyeScgKSApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHR2YXIgcGFyc2VkID0gdGhpcy5wYXJzZUFyZ1NsdWcoIHNsdWcgKTtcblxuXHRcdGlmICggcGFyc2VkLmlzQXJyYXkgKSB7XG5cdFx0XHRjaGlsZCA9IG5ldyBFbnRpdHlBcnJheSh7IGVudGl0eV9zbHVnOiBwYXJzZWQuc2x1ZyB9KTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0Y2hpbGQgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MuZ2V0RW50aXR5KCBwYXJzZWQuc2x1ZyApO1xuXHRcdH1cblxuXHRcdHJldHVybiBjaGlsZDtcblx0fSxcblxuXHRnZXRDaGlsZHJlbjogZnVuY3Rpb24gKCkge1xuXHRcdHJldHVybiBuZXcgQXJnc0NvbGxlY3Rpb24oIFsgdGhpcy5nZXRDaGlsZCggdGhpcy5nZXQoICdzZWNvbmRhcnknICkgKSBdICk7XG5cdH1cbn0pO1xuXG52YXIgRW50aXR5QXJyYXkgPSBBcmcuZXh0ZW5kKCB7XG5cdHR5cGU6ICdhcnJheScsXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXHRcdHRoaXMuc2V0KCAnc2x1ZycsIHRoaXMuZ2V0KCAnZW50aXR5X3NsdWcnICkgKyAne30nICk7XG5cdH1cbn0pO1xuXG52YXIgQXR0ciA9IEFyZy5leHRlbmQoIHtcblx0dHlwZTogJ2F0dHInXG59KTtcblxuQXJncy50eXBlLmVudGl0eSA9IEVudGl0eTtcbkFyZ3MudHlwZS5yZWxhdGlvbnNoaXAgPSBSZWxhdGlvbnNoaXA7XG5BcmdzLnR5cGUuYXJyYXkgPSBFbnRpdHlBcnJheTtcbkFyZ3MudHlwZS5hdHRyID0gQXR0cjtcblxubW9kdWxlLmV4cG9ydHMgPSBBcmdzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uXG4gKlxuICogQHNpbmNlIDIuMS4wXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqXG4gKlxuICovXG52YXIgaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLFxuXHRleHRlbnNpb25zID0gaG9va3Mudmlldy5kYXRhLmV4dGVuc2lvbnMsXG5cdGV4dGVuZCA9IGhvb2tzLnV0aWwuZXh0ZW5kLFxuXHRlbXB0eUZ1bmN0aW9uID0gaG9va3MudXRpbC5lbXB0eUZ1bmN0aW9uLFxuXHRFeHRlbnNpb247XG5cbkV4dGVuc2lvbiA9IEJhY2tib25lLk1vZGVsLmV4dGVuZCh7XG5cblx0LyoqXG5cdCAqIEBzaW5jZSAyLjEuMFxuXHQgKi9cblx0aWRBdHRyaWJ1dGU6ICdzbHVnJyxcblxuXHQvKipcblx0ICogQHNpbmNlIDIuMS4wXG5cdCAqL1xuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLmxpc3RlblRvKCBob29rcywgJ3JlYWN0aW9uOnZpZXc6aW5pdCcsIHRoaXMuaW5pdFJlYWN0aW9uICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggaG9va3MsICdyZWFjdGlvbjptb2RlbDp2YWxpZGF0ZScsIHRoaXMudmFsaWRhdGVSZWFjdGlvbiApO1xuXG5cdFx0dGhpcy5kYXRhID0gZXh0ZW5zaW9uc1sgdGhpcy5pZCBdO1xuXG5cdFx0dGhpcy5fX2NoaWxkX18uaW5pdGlhbGl6ZS5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzdW1tYXJ5IEluaXRpYWxpemVzIGEgcmVhY3Rpb24uXG5cdCAqIFxuXHQgKiBUaGlzIGlzIGNhbGxlZCB3aGVuIGEgcmVhY3Rpb24gdmlldyBpcyBpbml0aWFsaXplZC5cblx0ICogXG5cdCAqIEBzaW5jZSAyLjEuMFxuXHQgKiBcblx0ICogQGFic3RyYWN0XG5cdCAqIFxuXHQgKiBAcGFyYW0ge3dwLndvcmRwb2ludHMuaG9va3Mudmlldy5SZWFjdGlvbn0gcmVhY3Rpb24gVGhlIHJlYWN0aW9uIGJlaW5nXG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpbml0aWFsaXplZC5cblx0ICovXG5cdGluaXRSZWFjdGlvbjogZW1wdHlGdW5jdGlvbiggJ2luaXRSZWFjdGlvbicgKSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgVmFsaWRhdGVzIGEgcmVhY3Rpb24ncyBzZXR0aW5ncy5cblx0ICogXG5cdCAqIFRoaXMgaXMgY2FsbGVkIGJlZm9yZSBhIHJlYWN0aW9uIG1vZGVsIGlzIHNhdmVkLlxuXHQgKiBcblx0ICogQHNpbmNlIDIuMS4wXG5cdCAqIFxuXHQgKiBAYWJzdHJhY3Rcblx0ICogXG5cdCAqIEBwYXJhbSB7UmVhY3Rpb259IG1vZGVsICAgICAgVGhlIHJlYWN0aW9uIG1vZGVsLlxuXHQgKiBAcGFyYW0ge2FycmF5fSAgICBhdHRyaWJ1dGVzIFRoZSBtb2RlbCdzIGF0dHJpYnV0ZXMgKHRoZSBzZXR0aW5ncyBiZWluZ1xuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHZhbGlkYXRlZCkuXG5cdCAqIEBwYXJhbSB7YXJyYXl9ICAgIGVycm9ycyAgICAgQW55IGVycm9ycyB0aGF0IHdlcmUgZW5jb3VudGVyZWQuXG5cdCAqIEBwYXJhbSB7YXJyYXl9ICAgIG9wdGlvbnMgICAgT3B0aW9ucy5cblx0ICovXG5cdHZhbGlkYXRlUmVhY3Rpb246IGVtcHR5RnVuY3Rpb24oICd2YWxpZGF0ZVJlYWN0aW9uJyApXG5cbn0sIHsgZXh0ZW5kOiBleHRlbmQgfSApO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEV4dGVuc2lvbjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLkV4dGVuc2lvbnNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBFeHRlbnNpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uLFxuXHRFeHRlbnNpb25zO1xuXG5FeHRlbnNpb25zID0gQmFja2JvbmUuQ29sbGVjdGlvbi5leHRlbmQoe1xuXHRtb2RlbDogRXh0ZW5zaW9uXG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBFeHRlbnNpb25zOyIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLkZpZWxkc1xuICpcbiAqIEBzaW5jZSAyLjEuMFxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKi9cbnZhciAkID0gQmFja2JvbmUuJCxcblx0aG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLFxuXHRsMTBuID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LmwxMG4sXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0dGV4dFRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZXh0VGVtcGxhdGUsXG5cdEZpZWxkcztcblxuRmllbGRzID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdGZpZWxkczoge31cblx0fSxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLXJlYWN0aW9uLWZpZWxkJyApLFxuXHR0ZW1wbGF0ZUhpZGRlbjogdGVtcGxhdGUoICdob29rLXJlYWN0aW9uLWhpZGRlbi1maWVsZCcgKSxcblx0dGVtcGxhdGVTZWxlY3Q6IHRlbXBsYXRlKCAnaG9vay1yZWFjdGlvbi1zZWxlY3QtZmllbGQnICksXG5cblx0ZW1wdHlNZXNzYWdlOiB0ZXh0VGVtcGxhdGUoIGwxMG4uZW1wdHlGaWVsZCApLFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oIGhvb2tzLCAncmVhY3Rpb246bW9kZWw6dmFsaWRhdGUnLCB0aGlzLnZhbGlkYXRlUmVhY3Rpb24gKTtcblx0XHR0aGlzLmxpc3RlblRvKCBob29rcywgJ3JlYWN0aW9uOnZpZXc6aW5pdCcsIHRoaXMuaW5pdFJlYWN0aW9uICk7XG5cblx0XHR0aGlzLmF0dHJpYnV0ZXMuZmllbGRzLmV2ZW50ID0ge1xuXHRcdFx0dHlwZTogJ2hpZGRlbicsXG5cdFx0XHRyZXF1aXJlZDogdHJ1ZVxuXHRcdH07XG5cdH0sXG5cblx0Y3JlYXRlOiBmdW5jdGlvbiAoIG5hbWUsIHZhbHVlLCBkYXRhICkge1xuXG5cdFx0aWYgKCB0eXBlb2YgdmFsdWUgPT09ICd1bmRlZmluZWQnICYmIGRhdGFbJ2RlZmF1bHQnXSApIHtcblx0XHRcdHZhbHVlID0gZGF0YVsnZGVmYXVsdCddO1xuXHRcdH1cblxuXHRcdGRhdGEgPSBfLmV4dGVuZChcblx0XHRcdHsgbmFtZTogdGhpcy5nZXRGaWVsZE5hbWUoIG5hbWUgKSwgdmFsdWU6IHZhbHVlIH1cblx0XHRcdCwgZGF0YVxuXHRcdCk7XG5cblx0XHRzd2l0Y2ggKCBkYXRhLnR5cGUgKSB7XG5cdFx0XHRjYXNlICdzZWxlY3QnOlxuXHRcdFx0XHRyZXR1cm4gdGhpcy5jcmVhdGVTZWxlY3QoIGRhdGEgKTtcblxuXHRcdFx0Y2FzZSAnaGlkZGVuJzpcblx0XHRcdFx0cmV0dXJuIHRoaXMudGVtcGxhdGVIaWRkZW4oIGRhdGEgKTtcblx0XHR9XG5cblx0XHR2YXIgRGF0YVR5cGUgPSBEYXRhVHlwZXMuZ2V0KCBkYXRhLnR5cGUgKTtcblxuXHRcdGlmICggRGF0YVR5cGUgKSB7XG5cdFx0XHRyZXR1cm4gRGF0YVR5cGUuY3JlYXRlRmllbGQoIGRhdGEgKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0cmV0dXJuIHRoaXMudGVtcGxhdGUoIGRhdGEgKTtcblx0XHR9XG5cdH0sXG5cblx0Y3JlYXRlU2VsZWN0OiBmdW5jdGlvbiAoIGRhdGEsIHRlbXBsYXRlICkge1xuXG5cdFx0dmFyICR0ZW1wbGF0ZSA9ICQoICc8ZGl2PjwvZGl2PicgKS5odG1sKCB0ZW1wbGF0ZSB8fCB0aGlzLnRlbXBsYXRlU2VsZWN0KCBkYXRhICkgKSxcblx0XHRcdG9wdGlvbnMgPSAnJyxcblx0XHRcdGZvdW5kVmFsdWUgPSB0eXBlb2YgZGF0YS52YWx1ZSA9PT0gJ3VuZGVmaW5lZCdcblx0XHRcdFx0fHwgdHlwZW9mIGRhdGEub3B0aW9uc1sgZGF0YS52YWx1ZSBdICE9PSAndW5kZWZpbmVkJztcblxuXHRcdGlmICggISAkdGVtcGxhdGUgKSB7XG5cdFx0XHQkdGVtcGxhdGUgPSAkKCAnPGRpdj48L2Rpdj4nICkuaHRtbCggdGhpcy50ZW1wbGF0ZVNlbGVjdCggZGF0YSApICk7XG5cdFx0fVxuXG5cdFx0Xy5lYWNoKCBkYXRhLm9wdGlvbnMsIGZ1bmN0aW9uICggb3B0aW9uLCBpbmRleCApIHtcblxuXHRcdFx0dmFyIHZhbHVlLCBsYWJlbDtcblxuXHRcdFx0aWYgKCBvcHRpb24udmFsdWUgKSB7XG5cdFx0XHRcdHZhbHVlID0gb3B0aW9uLnZhbHVlO1xuXHRcdFx0XHRsYWJlbCA9IG9wdGlvbi5sYWJlbDtcblxuXHRcdFx0XHRpZiAoICEgZm91bmRWYWx1ZSAmJiBkYXRhLnZhbHVlID09PSB2YWx1ZSApIHtcblx0XHRcdFx0XHRmb3VuZFZhbHVlID0gdHJ1ZTtcblx0XHRcdFx0fVxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0dmFsdWUgPSBpbmRleDtcblx0XHRcdFx0bGFiZWwgPSBvcHRpb247XG5cdFx0XHR9XG5cblx0XHRcdG9wdGlvbnMgKz0gJCggJzxvcHRpb24+PC9vcHRpb24+JyApXG5cdFx0XHRcdC5hdHRyKCAndmFsdWUnLCB2YWx1ZSApXG5cdFx0XHRcdC50ZXh0KCBsYWJlbCA/IGxhYmVsIDogdmFsdWUgKVxuXHRcdFx0XHQucHJvcCggJ291dGVySFRNTCcgKTtcblx0XHR9KTtcblxuXHRcdC8vIElmIHRoZSBjdXJyZW50IHZhbHVlIGlzbid0IGluIHRoZSBsaXN0LCBhZGQgaXQgaW4uXG5cdFx0aWYgKCAhIGZvdW5kVmFsdWUgKSB7XG5cdFx0XHRvcHRpb25zICs9ICQoICc8b3B0aW9uPjwvb3B0aW9uPicgKVxuXHRcdFx0XHQuYXR0ciggJ3ZhbHVlJywgZGF0YS52YWx1ZSApXG5cdFx0XHRcdC50ZXh0KCBkYXRhLnZhbHVlIClcblx0XHRcdFx0LnByb3AoICdvdXRlckhUTUwnICk7XG5cdFx0fVxuXG5cdFx0JHRlbXBsYXRlLmZpbmQoICdzZWxlY3QnIClcblx0XHRcdC5hcHBlbmQoIG9wdGlvbnMgKVxuXHRcdFx0LnZhbCggZGF0YS52YWx1ZSApXG5cdFx0XHQuZmluZCggJzpzZWxlY3RlZCcgKVxuXHRcdFx0XHQuYXR0ciggJ3NlbGVjdGVkJywgdHJ1ZSApO1xuXG5cdFx0cmV0dXJuICR0ZW1wbGF0ZS5odG1sKCk7XG5cdH0sXG5cblx0Z2V0RmllbGROYW1lOiBmdW5jdGlvbiAoIGZpZWxkICkge1xuXG5cdFx0aWYgKCBfLmlzQXJyYXkoIGZpZWxkICkgKSB7XG5cblx0XHRcdGZpZWxkID0gXy5jbG9uZSggZmllbGQgKTtcblxuXHRcdFx0aWYgKCAxID09PSBmaWVsZC5sZW5ndGggKSB7XG5cdFx0XHRcdGZpZWxkID0gZmllbGQuc2hpZnQoKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGZpZWxkID0gZmllbGQuc2hpZnQoKSArICdbJyArIGZpZWxkLmpvaW4oICddWycgKSArICddJztcblx0XHRcdH1cblx0XHR9XG5cblx0XHRyZXR1cm4gZmllbGQ7XG5cdH0sXG5cblx0Z2V0QXR0clNsdWc6IGZ1bmN0aW9uICggcmVhY3Rpb24sIGZpZWxkTmFtZSApIHtcblxuXHRcdHZhciBuYW1lID0gZmllbGROYW1lO1xuXG5cdFx0dmFyIG5hbWVQYXJ0cyA9IFtdLFxuXHRcdFx0Zmlyc3RCcmFja2V0ID0gbmFtZS5pbmRleE9mKCAnWycgKTtcblxuXHRcdC8vIElmIHRoaXMgaXNuJ3QgYW4gYXJyYXktc3ludGF4IG5hbWUsIHdlIGRvbid0IG5lZWQgdG8gcHJvY2VzcyBpdC5cblx0XHRpZiAoIC0xID09PSBmaXJzdEJyYWNrZXQgKSB7XG5cdFx0XHRyZXR1cm4gbmFtZTtcblx0XHR9XG5cblx0XHQvLyBVc3VhbGx5IHRoZSBicmFja2V0IHdpbGwgYmUgcHJvY2VlZGVkIGJ5IHNvbWV0aGluZzogYGFycmF5Wy4uLl1gLlxuXHRcdGlmICggMCAhPT0gZmlyc3RCcmFja2V0ICkge1xuXHRcdFx0bmFtZVBhcnRzLnB1c2goIG5hbWUuc3Vic3RyaW5nKCAwLCBmaXJzdEJyYWNrZXQgKSApO1xuXHRcdFx0bmFtZSA9IG5hbWUuc3Vic3RyaW5nKCBmaXJzdEJyYWNrZXQgKTtcblx0XHR9XG5cblx0XHRuYW1lUGFydHMgPSBuYW1lUGFydHMuY29uY2F0KCBuYW1lLnNsaWNlKCAxLCAtMSApLnNwbGl0KCAnXVsnICkgKTtcblxuXHRcdC8vIElmIHRoZSBsYXN0IGVsZW1lbnQgaXMgZW1wdHksIGl0IGlzIGEgbm9uLWFzc29jaWF0aXZlIGFycmF5OiBgYVtdYFxuXHRcdGlmICggbmFtZVBhcnRzWyBuYW1lUGFydHMubGVuZ3RoIC0gMSBdID09PSAnJyApIHtcblx0XHRcdG5hbWVQYXJ0cy5wb3AoKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gbmFtZVBhcnRzO1xuXHR9LFxuXG5cdC8vIEdldCB0aGUgZGF0YSBmcm9tIGEgZm9ybSBhcyBrZXkgPT4gdmFsdWUgcGFpcnMuXG5cdGdldEZvcm1EYXRhOiBmdW5jdGlvbiAoIHJlYWN0aW9uLCAkZm9ybSApIHtcblxuXHRcdHZhciBmb3JtT2JqID0ge30sXG5cdFx0XHRpbnB1dHMgPSAkZm9ybS5maW5kKCAnOmlucHV0JyApLnNlcmlhbGl6ZUFycmF5KCk7XG5cblx0XHRfLmVhY2goIGlucHV0cywgZnVuY3Rpb24gKCBpbnB1dCApIHtcblx0XHRcdGZvcm1PYmpbIGlucHV0Lm5hbWUgXSA9IGlucHV0LnZhbHVlO1xuXHRcdH0gKTtcblxuXHRcdHJldHVybiB0aGlzLmFycmF5aWZ5KCBmb3JtT2JqICk7XG5cdH0sXG5cblx0YXJyYXlpZnk6IGZ1bmN0aW9uICggZm9ybURhdGEgKSB7XG5cblx0XHR2YXIgYXJyYXlEYXRhID0ge307XG5cblx0XHRfLmVhY2goIGZvcm1EYXRhLCBmdW5jdGlvbiAoIHZhbHVlLCBuYW1lICkge1xuXG5cdFx0XHR2YXIgbmFtZVBhcnRzID0gW10sXG5cdFx0XHRcdGRhdGEgPSBhcnJheURhdGEsXG5cdFx0XHRcdGlzQXJyYXkgPSBmYWxzZSxcblx0XHRcdFx0Zmlyc3RCcmFja2V0ID0gbmFtZS5pbmRleE9mKCAnWycgKTtcblxuXHRcdFx0Ly8gSWYgdGhpcyBpc24ndCBhbiBhcnJheS1zeW50YXggbmFtZSwgd2UgZG9uJ3QgbmVlZCB0byBwcm9jZXNzIGl0LlxuXHRcdFx0aWYgKCAtMSA9PT0gZmlyc3RCcmFja2V0ICkge1xuXHRcdFx0XHRkYXRhWyBuYW1lIF0gPSB2YWx1ZTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBVc3VhbGx5IHRoZSBicmFja2V0IHdpbGwgYmUgcHJvY2VlZGVkIGJ5IHNvbWV0aGluZzogYGFycmF5Wy4uLl1gLlxuXHRcdFx0aWYgKCAwICE9PSBmaXJzdEJyYWNrZXQgKSB7XG5cdFx0XHRcdG5hbWVQYXJ0cy5wdXNoKCBuYW1lLnN1YnN0cmluZyggMCwgZmlyc3RCcmFja2V0ICkgKTtcblx0XHRcdFx0bmFtZSA9IG5hbWUuc3Vic3RyaW5nKCBmaXJzdEJyYWNrZXQgKTtcblx0XHRcdH1cblxuXHRcdFx0bmFtZVBhcnRzID0gbmFtZVBhcnRzLmNvbmNhdCggbmFtZS5zbGljZSggMSwgLTEgKS5zcGxpdCggJ11bJyApICk7XG5cblx0XHRcdC8vIElmIHRoZSBsYXN0IGVsZW1lbnQgaXMgZW1wdHksIGl0IGlzIGEgbm9uLWFzc29jaWF0aXZlIGFycmF5OiBgYVtdYFxuXHRcdFx0aWYgKCBuYW1lUGFydHNbIG5hbWVQYXJ0cy5sZW5ndGggLSAxIF0gPT09ICcnICkge1xuXHRcdFx0XHRpc0FycmF5ID0gdHJ1ZTtcblx0XHRcdFx0bmFtZVBhcnRzLnBvcCgpO1xuXHRcdFx0fVxuXG5cdFx0XHR2YXIga2V5ID0gbmFtZVBhcnRzLnBvcCgpO1xuXG5cdFx0XHQvLyBDb25zdHJ1Y3QgdGhlIGhpZXJhcmNoaWNhbCBvYmplY3QuXG5cdFx0XHRfLmVhY2goIG5hbWVQYXJ0cywgZnVuY3Rpb24gKCBwYXJ0ICkge1xuXHRcdFx0XHRkYXRhID0gZGF0YVsgcGFydCBdID0gKCBkYXRhWyBwYXJ0IF0gfHwge30gKTtcblx0XHRcdH0pO1xuXG5cdFx0XHQvLyBTZXQgdGhlIHZhbHVlLlxuXHRcdFx0aWYgKCBpc0FycmF5ICkge1xuXG5cdFx0XHRcdGlmICggdHlwZW9mIGRhdGFbIGtleSBdID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdFx0XHRkYXRhWyBrZXkgXSA9IFtdO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0ZGF0YVsga2V5IF0ucHVzaCggdmFsdWUgKTtcblxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0ZGF0YVsga2V5IF0gPSB2YWx1ZTtcblx0XHRcdH1cblx0XHR9KTtcblxuXHRcdHJldHVybiBhcnJheURhdGE7XG5cdH0sXG5cblx0dmFsaWRhdGU6IGZ1bmN0aW9uICggZmllbGRzLCBhdHRyaWJ1dGVzLCBlcnJvcnMgKSB7XG5cblx0XHRfLmVhY2goIGZpZWxkcywgZnVuY3Rpb24gKCBmaWVsZCwgc2x1ZyApIHtcblx0XHRcdGlmIChcblx0XHRcdFx0ZmllbGQucmVxdWlyZWRcblx0XHRcdFx0JiYgKFxuXHRcdFx0XHRcdHR5cGVvZiBhdHRyaWJ1dGVzWyBzbHVnIF0gPT09ICd1bmRlZmluZWQnXG5cdFx0XHRcdFx0fHwgJycgPT09ICQudHJpbSggYXR0cmlidXRlc1sgc2x1ZyBdIClcblx0XHRcdFx0KVxuXHRcdFx0KSB7XG5cdFx0XHRcdGVycm9ycy5wdXNoKCB7XG5cdFx0XHRcdFx0ZmllbGQ6IHNsdWcsXG5cdFx0XHRcdFx0bWVzc2FnZTogdGhpcy5lbXB0eU1lc3NhZ2UoIGZpZWxkIClcblx0XHRcdFx0fSApO1xuXHRcdFx0fVxuXHRcdH0sIHRoaXMgKTtcblx0fSxcblxuXHRpbml0UmVhY3Rpb246IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHR0aGlzLmxpc3RlblRvKCByZWFjdGlvbiwgJ3JlbmRlcjpzZXR0aW5ncycsIHRoaXMucmVuZGVyUmVhY3Rpb24gKTtcblx0fSxcblxuXHRyZW5kZXJSZWFjdGlvbjogZnVuY3Rpb24gKCAkZWwsIGN1cnJlbnRBY3Rpb25UeXBlLCByZWFjdGlvbiApIHtcblxuXHRcdHZhciBmaWVsZHNIVE1MID0gJyc7XG5cblx0XHRfLmVhY2goIHRoaXMuZ2V0KCAnZmllbGRzJyApLCBmdW5jdGlvbiAoIGZpZWxkLCBuYW1lICkge1xuXG5cdFx0XHRmaWVsZHNIVE1MICs9IHRoaXMuY3JlYXRlKFxuXHRcdFx0XHRuYW1lLFxuXHRcdFx0XHRyZWFjdGlvbi5tb2RlbC5nZXQoIG5hbWUgKSxcblx0XHRcdFx0ZmllbGRcblx0XHRcdCk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHQkZWwuaHRtbCggZmllbGRzSFRNTCApO1xuXHR9LFxuXG5cdHZhbGlkYXRlUmVhY3Rpb246IGZ1bmN0aW9uICggcmVhY3Rpb24sIGF0dHJpYnV0ZXMsIGVycm9ycyApIHtcblxuXHRcdHRoaXMudmFsaWRhdGUoIHRoaXMuZ2V0KCAnZmllbGRzJyApLCBhdHRyaWJ1dGVzLCBlcnJvcnMgKTtcblx0fVxufSk7XG5cbnZhciBEYXRhVHlwZSA9IEJhY2tib25lLk1vZGVsLmV4dGVuZCh7XG5cblx0aWRBdHRyaWJ1dGU6ICdzbHVnJyxcblxuXHRkZWZhdWx0czoge1xuXHRcdGlucHV0VHlwZTogJ3RleHQnXG5cdH0sXG5cblx0dGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1yZWFjdGlvbi1maWVsZCcgKSxcblxuXHRjcmVhdGVGaWVsZDogZnVuY3Rpb24gKCBkYXRhICkge1xuXG5cdFx0cmV0dXJuIHRoaXMudGVtcGxhdGUoXG5cdFx0XHRfLmV4dGVuZCgge30sIGRhdGEsIHsgdHlwZTogdGhpcy5nZXQoICdpbnB1dFR5cGUnICkgfSApXG5cdFx0KTtcblx0fVxufSk7XG5cbnZhciBEYXRhVHlwZXMgPSBuZXcgQmFja2JvbmUuQ29sbGVjdGlvbigpO1xuXG5EYXRhVHlwZXMuYWRkKCBuZXcgRGF0YVR5cGUoIHsgc2x1ZzogJ3RleHQnIH0gKSApO1xuRGF0YVR5cGVzLmFkZCggbmV3IERhdGFUeXBlKCB7IHNsdWc6ICdpbnRlZ2VyJywgaW5wdXRUeXBlOiAnbnVtYmVyJyB9ICkgKTtcblxubW9kdWxlLmV4cG9ydHMgPSBGaWVsZHM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5SZWFjdG9yXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uXG4gKlxuICpcbiAqL1xudmFyIEV4dGVuc2lvbiA9IHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb24sXG5cdGhvb2tzID0gd3Aud29yZHBvaW50cy5ob29rcyxcblx0ZW1wdHlGdW5jdGlvbiA9IGhvb2tzLnV0aWwuZW1wdHlGdW5jdGlvbixcblx0UmVhY3RvcjtcblxuUmVhY3RvciA9IEV4dGVuc2lvbi5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0J2FyZ190eXBlcyc6IFtdLFxuXHRcdCdhY3Rpb25fdHlwZXMnOiBbXVxuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc2luY2UgMi4xLjBcblx0ICovXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oIGhvb2tzLCAncmVhY3Rpb25zOnZpZXc6aW5pdCcsIHRoaXMubGlzdGVuVG9EZWZhdWx0cyApO1xuXG5cdFx0dGhpcy5fX2NoaWxkX18uaW5pdGlhbGl6ZS5hcHBseSggdGhpcywgYXJndW1lbnRzICk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzaW5jZSAyLjEuMFxuXHQgKi9cblx0bGlzdGVuVG9EZWZhdWx0czogZnVuY3Rpb24gKCByZWFjdGlvbnNWaWV3ICkge1xuXG5cdFx0dGhpcy5saXN0ZW5Ubyhcblx0XHRcdHJlYWN0aW9uc1ZpZXdcblx0XHRcdCwgJ2hvb2stcmVhY3Rpb24tZGVmYXVsdHMnXG5cdFx0XHQsIHRoaXMuZmlsdGVyUmVhY3Rpb25EZWZhdWx0c1xuXHRcdCk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzaW5jZSAyLjEuMFxuXHQgKiBAYWJzdHJhY3Rcblx0ICovXG5cdGZpbHRlclJlYWN0aW9uRGVmYXVsdHM6IGVtcHR5RnVuY3Rpb24oICdmaWx0ZXJSZWFjdGlvbkRlZmF1bHRzJyApXG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBSZWFjdG9yO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuUmVhY3RvcnNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLkV4dGVuc2lvbnNcbiAqL1xudmFyIEV4dGVuc2lvbnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9ucyxcblx0UmVhY3RvciA9IHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5SZWFjdG9yLFxuXHRSZWFjdG9ycztcblxuUmVhY3RvcnMgPSBFeHRlbnNpb25zLmV4dGVuZCh7XG5cdG1vZGVsOiBSZWFjdG9yXG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBSZWFjdG9yczsiLCJ2YXIgaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLFxuXHQkID0galF1ZXJ5LFxuXHRkYXRhO1xuXG4vLyBMb2FkIHRoZSBhcHBsaWNhdGlvbiBvbmNlIHRoZSBET00gaXMgcmVhZHkuXG4kKCBmdW5jdGlvbiAoKSB7XG5cblx0Ly8gTGV0IGFsbCBwYXJ0cyBvZiB0aGUgYXBwIGtub3cgdGhhdCB3ZSdyZSBhYm91dCB0byBzdGFydC5cblx0aG9va3MudHJpZ2dlciggJ2luaXQnICk7XG5cblx0Ly8gV2Uga2ljayB0aGluZ3Mgb2ZmIGJ5IGNyZWF0aW5nIHRoZSAqKkdyb3VwcyoqLlxuXHQvLyBJbnN0ZWFkIG9mIGdlbmVyYXRpbmcgbmV3IGVsZW1lbnRzLCBiaW5kIHRvIHRoZSBleGlzdGluZyBza2VsZXRvbnMgb2Zcblx0Ly8gdGhlIGdyb3VwcyBhbHJlYWR5IHByZXNlbnQgaW4gdGhlIEhUTUwuXG5cdCQoICcud29yZHBvaW50cy1ob29rLXJlYWN0aW9uLWdyb3VwLWNvbnRhaW5lcicgKS5lYWNoKCBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgJHRoaXMgPSAkKCB0aGlzICksXG5cdFx0XHRldmVudDtcblxuXHRcdGV2ZW50ID0gJHRoaXNcblx0XHRcdC5maW5kKCAnLndvcmRwb2ludHMtaG9vay1yZWFjdGlvbi1ncm91cCcgKVxuXHRcdFx0XHQuZGF0YSggJ3dvcmRwb2ludHMtaG9va3MtaG9vay1ldmVudCcgKTtcblxuXHRcdG5ldyBob29rcy52aWV3LlJlYWN0aW9ucygge1xuXHRcdFx0ZWw6ICR0aGlzLFxuXHRcdFx0bW9kZWw6IG5ldyBob29rcy5tb2RlbC5SZWFjdGlvbnMoIGRhdGEucmVhY3Rpb25zWyBldmVudCBdIClcblx0XHR9ICk7XG5cdH0gKTtcbn0pO1xuXG4vLyBMaW5rIGFueSBsb2NhbGl6ZWQgc3RyaW5ncy5cbmhvb2tzLnZpZXcubDEwbiA9IHdpbmRvdy5Xb3JkUG9pbnRzSG9va3NBZG1pbkwxMG4gfHwge307XG5cbi8vIExpbmsgYW55IHNldHRpbmdzLlxuZGF0YSA9IGhvb2tzLnZpZXcuZGF0YSA9IHdpbmRvdy5Xb3JkUG9pbnRzSG9va3NBZG1pbkRhdGEgfHwge307XG5cbi8vIExvYWQgdGhlIGNvbnRyb2xsZXJzLlxuaG9va3MuY29udHJvbGxlci5GaWVsZHMgICAgID0gcmVxdWlyZSggJy4vY29udHJvbGxlcnMvZmllbGRzLmpzJyApO1xuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb24gID0gcmVxdWlyZSggJy4vY29udHJvbGxlcnMvZXh0ZW5zaW9uLmpzJyApO1xuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb25zID0gcmVxdWlyZSggJy4vY29udHJvbGxlcnMvZXh0ZW5zaW9ucy5qcycgKTtcbmhvb2tzLmNvbnRyb2xsZXIuUmVhY3RvciAgICA9IHJlcXVpcmUoICcuL2NvbnRyb2xsZXJzL3JlYWN0b3IuanMnICk7XG5ob29rcy5jb250cm9sbGVyLlJlYWN0b3JzICAgPSByZXF1aXJlKCAnLi9jb250cm9sbGVycy9yZWFjdG9ycy5qcycgKTtcbmhvb2tzLmNvbnRyb2xsZXIuQXJncyAgICAgICA9IHJlcXVpcmUoICcuL2NvbnRyb2xsZXJzL2FyZ3MuanMnICk7XG5cbi8vIFN0YXJ0IHRoZW0gdXAgaGVyZSBzbyB0aGF0IHdlIGNhbiBiZWdpbiB1c2luZyB0aGVtLlxuaG9va3MuRmllbGRzICAgICA9IG5ldyBob29rcy5jb250cm9sbGVyLkZpZWxkcyggeyBmaWVsZHM6IGRhdGEuZmllbGRzIH0gKTtcbmhvb2tzLlJlYWN0b3JzICAgPSBuZXcgaG9va3MuY29udHJvbGxlci5SZWFjdG9ycygpO1xuaG9va3MuRXh0ZW5zaW9ucyA9IG5ldyBob29rcy5jb250cm9sbGVyLkV4dGVuc2lvbnMoKTtcbmhvb2tzLkFyZ3MgICAgICAgPSBuZXcgaG9va3MuY29udHJvbGxlci5BcmdzKHsgZXZlbnRzOiBkYXRhLmV2ZW50cywgZW50aXRpZXM6IGRhdGEuZW50aXRpZXMgfSk7XG5cbi8vIExvYWQgdGhlIHZpZXdzLlxuaG9va3Mudmlldy5CYXNlICAgICAgICAgICAgICA9IHJlcXVpcmUoICcuL3ZpZXdzL2Jhc2UuanMnICk7XG5ob29rcy52aWV3LlJlYWN0aW9uICAgICAgICAgID0gcmVxdWlyZSggJy4vdmlld3MvcmVhY3Rpb24uanMnICk7XG5ob29rcy52aWV3LlJlYWN0aW9ucyAgICAgICAgID0gcmVxdWlyZSggJy4vdmlld3MvcmVhY3Rpb25zLmpzJyApO1xuaG9va3Mudmlldy5BcmdTZWxlY3RvciAgICAgICA9IHJlcXVpcmUoICcuL3ZpZXdzL2FyZy1zZWxlY3Rvci5qcycgKTtcbmhvb2tzLnZpZXcuQXJnU2VsZWN0b3JzICAgICAgPSByZXF1aXJlKCAnLi92aWV3cy9hcmctc2VsZWN0b3JzLmpzJyApO1xuaG9va3Mudmlldy5BcmdIaWVyYXJjaHlTZWxlY3RvciA9IHJlcXVpcmUoICcuL3ZpZXdzL2FyZy1oaWVyYXJjaHktc2VsZWN0b3IuanMnICk7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5BcmdTZWxlY3RvcnNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHQkID0gQmFja2JvbmUuJCxcblx0QXJnSGllcmFyY2h5U2VsZWN0b3I7XG5cbkFyZ0hpZXJhcmNoeVNlbGVjdG9yID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ2FyZy1oaWVyYXJjaHktc2VsZWN0b3InLFxuXG5cdHRhZ05hbWU6ICdkaXYnLFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stYXJnLXNlbGVjdG9yJyApLFxuXG5cdGV2ZW50czoge1xuXHRcdCdjaGFuZ2Ugc2VsZWN0JzogJ3RyaWdnZXJDaGFuZ2UnXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBvcHRpb25zICkge1xuXHRcdGlmICggb3B0aW9ucy5oaWVyYXJjaGllcyApIHtcblx0XHRcdHRoaXMuaGllcmFyY2hpZXMgPSBvcHRpb25zLmhpZXJhcmNoaWVzO1xuXHRcdH1cblx0fSxcblxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGVsLmFwcGVuZChcblx0XHRcdHRoaXMudGVtcGxhdGUoIHsgbGFiZWw6IHRoaXMubGFiZWwsIG5hbWU6IHRoaXMuY2lkIH0gKVxuXHRcdCk7XG5cblx0XHR0aGlzLiRzZWxlY3QgPSB0aGlzLiQoICdzZWxlY3QnICk7XG5cblx0XHRfLmVhY2goIHRoaXMuaGllcmFyY2hpZXMsIGZ1bmN0aW9uICggaGllcmFyY2h5LCBpbmRleCApIHtcblxuXHRcdFx0dmFyICRvcHRpb24gPSAkKCAnPG9wdGlvbj48L29wdGlvbj4nIClcblx0XHRcdFx0LnZhbCggaW5kZXggKVxuXHRcdFx0XHQudGV4dCggQXJncy5idWlsZEhpZXJhcmNoeUh1bWFuSWQoIGhpZXJhcmNoeSApICk7XG5cblx0XHRcdHRoaXMuJHNlbGVjdC5hcHBlbmQoICRvcHRpb24gKTtcblxuXHRcdH0sIHRoaXMgKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcicsIHRoaXMgKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdHRyaWdnZXJDaGFuZ2U6IGZ1bmN0aW9uICggZXZlbnQgKSB7XG5cblx0XHR2YXIgaW5kZXggPSB0aGlzLiRzZWxlY3QudmFsKCksXG5cdFx0XHRoaWVyYXJjaHksIGFyZztcblxuXHRcdC8vIERvbid0IGRvIGFueXRoaW5nIGlmIHRoZSB2YWx1ZSBoYXNuJ3QgcmVhbGx5IGNoYW5nZWQuXG5cdFx0aWYgKCBpbmRleCA9PT0gdGhpcy5jdXJyZW50SW5kZXggKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGhpcy5jdXJyZW50SW5kZXggPSBpbmRleDtcblxuXHRcdGlmICggaW5kZXggIT09IGZhbHNlICkge1xuXHRcdFx0aGllcmFyY2h5ID0gdGhpcy5oaWVyYXJjaGllc1sgaW5kZXggXTtcblxuXHRcdFx0aWYgKCAhIGhpZXJhcmNoeSApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRhcmcgPSBoaWVyYXJjaHlbIGhpZXJhcmNoeS5sZW5ndGggLSAxIF07XG5cdFx0fVxuXG5cdFx0dGhpcy50cmlnZ2VyKCAnY2hhbmdlJywgdGhpcywgYXJnLCBpbmRleCwgZXZlbnQgKTtcblx0fSxcblxuXHRnZXRIaWVyYXJjaHk6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBoaWVyYXJjaHkgPSBbXTtcblxuXHRcdF8uZWFjaCggdGhpcy5nZXRIaWVyYXJjaHlBcmdzKCksIGZ1bmN0aW9uICggYXJnICkge1xuXHRcdFx0aGllcmFyY2h5LnB1c2goIGFyZy5nZXQoICdzbHVnJyApICk7XG5cdFx0fSk7XG5cblx0XHRyZXR1cm4gaGllcmFyY2h5O1xuXHR9LFxuXG5cdGdldEhpZXJhcmNoeUFyZ3M6IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4gdGhpcy5oaWVyYXJjaGllc1sgdGhpcy5jdXJyZW50SW5kZXggXTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQXJnSGllcmFyY2h5U2VsZWN0b3I7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5BcmdTZWxlY3RvclxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHR0ZW1wbGF0ZSA9IHdwLndvcmRwb2ludHMuaG9va3MudGVtcGxhdGUsXG5cdEFyZ1NlbGVjdG9yO1xuXG5BcmdTZWxlY3RvciA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdhcmctc2VsZWN0b3InLFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stYXJnLXNlbGVjdG9yJyApLFxuXG5cdG9wdGlvblRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stYXJnLW9wdGlvbicgKSxcblxuXHRldmVudHM6IHtcblx0XHQnY2hhbmdlIHNlbGVjdCc6ICd0cmlnZ2VyQ2hhbmdlJ1xuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggb3B0aW9ucyApIHtcblxuXHRcdHRoaXMubGFiZWwgPSBvcHRpb25zLmxhYmVsO1xuXHRcdHRoaXMubnVtYmVyID0gb3B0aW9ucy5udW1iZXI7XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLmNvbGxlY3Rpb24sICd1cGRhdGUnLCB0aGlzLnJlbmRlciApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuY29sbGVjdGlvbiwgJ3Jlc2V0JywgdGhpcy5yZW5kZXIgKTtcblx0fSxcblxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGVsLmh0bWwoXG5cdFx0XHR0aGlzLnRlbXBsYXRlKCB7IGxhYmVsOiB0aGlzLmxhYmVsLCBuYW1lOiB0aGlzLmNpZCArICdfJyArIHRoaXMubnVtYmVyIH0gKVxuXHRcdCk7XG5cblx0XHR0aGlzLiRzZWxlY3QgPSB0aGlzLiQoICdzZWxlY3QnICk7XG5cblx0XHR0aGlzLmNvbGxlY3Rpb24uZWFjaCggZnVuY3Rpb24gKCBhcmcgKSB7XG5cblx0XHRcdHRoaXMuJHNlbGVjdC5hcHBlbmQoIHRoaXMub3B0aW9uVGVtcGxhdGUoIGFyZy5hdHRyaWJ1dGVzICkgKTtcblxuXHRcdH0sIHRoaXMgKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcicsIHRoaXMgKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdHRyaWdnZXJDaGFuZ2U6IGZ1bmN0aW9uICggZXZlbnQgKSB7XG5cblx0XHR2YXIgdmFsdWUgPSB0aGlzLiRzZWxlY3QudmFsKCk7XG5cblx0XHRpZiAoICcwJyA9PT0gdmFsdWUgKSB7XG5cdFx0XHR2YWx1ZSA9IGZhbHNlO1xuXHRcdH1cblxuXHRcdHRoaXMudHJpZ2dlciggJ2NoYW5nZScsIHRoaXMsIHZhbHVlLCBldmVudCApO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBBcmdTZWxlY3RvcjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkFyZ1NlbGVjdG9yc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHRBcmdTZWxlY3RvciA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5BcmdTZWxlY3Rvcixcblx0QXJnU2VsZWN0b3JzO1xuXG5BcmdTZWxlY3RvcnMgPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAnYXJnLXNlbGVjdG9ycycsXG5cblx0dGFnTmFtZTogJ2RpdicsXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBvcHRpb25zICkge1xuXHRcdGlmICggb3B0aW9ucy5hcmdzICkge1xuXHRcdFx0dGhpcy5hcmdzID0gb3B0aW9ucy5hcmdzO1xuXHRcdH1cblxuXHRcdHRoaXMuaGllcmFyY2h5ID0gW107XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgYXJncyA9IHRoaXMuYXJncywgYXJnO1xuXG5cdFx0aWYgKCBhcmdzLmxlbmd0aCA9PT0gMSApIHtcblx0XHRcdGFyZyA9IGFyZ3MuYXQoIDAgKTtcblx0XHRcdHRoaXMuaGllcmFyY2h5LnB1c2goIHsgYXJnOiBhcmcgfSApO1xuXHRcdFx0YXJncyA9IGFyZy5nZXRDaGlsZHJlbigpO1xuXHRcdH1cblxuXHRcdHRoaXMuYWRkU2VsZWN0b3IoIGFyZ3MgKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdGFkZFNlbGVjdG9yOiBmdW5jdGlvbiAoIGFyZ3MgKSB7XG5cblx0XHR2YXIgc2VsZWN0b3IgPSBuZXcgQXJnU2VsZWN0b3Ioe1xuXHRcdFx0Y29sbGVjdGlvbjogYXJncyxcblx0XHRcdG51bWJlcjogdGhpcy5oaWVyYXJjaHkubGVuZ3RoXG5cdFx0fSk7XG5cblx0XHRzZWxlY3Rvci5yZW5kZXIoKTtcblxuXHRcdHRoaXMuJGVsLmFwcGVuZCggc2VsZWN0b3IuJGVsICk7XG5cblx0XHRzZWxlY3Rvci4kKCAnc2VsZWN0JyApLmZvY3VzKCk7XG5cblx0XHR0aGlzLmhpZXJhcmNoeS5wdXNoKCB7IHNlbGVjdG9yOiBzZWxlY3RvciB9ICk7XG5cblx0XHR0aGlzLmxpc3RlblRvKCBzZWxlY3RvciwgJ2NoYW5nZScsIHRoaXMudXBkYXRlICk7XG5cdH0sXG5cblx0dXBkYXRlOiBmdW5jdGlvbiAoIHNlbGVjdG9yLCB2YWx1ZSApIHtcblxuXHRcdHZhciBpZCA9IHNlbGVjdG9yLm51bWJlcixcblx0XHRcdGFyZztcblxuXHRcdC8vIERvbid0IGRvIGFueXRoaW5nIGlmIHRoZSB2YWx1ZSBoYXNuJ3QgcmVhbGx5IGNoYW5nZWQuXG5cdFx0aWYgKCB0aGlzLmhpZXJhcmNoeVsgaWQgXS5hcmcgJiYgdmFsdWUgPT09IHRoaXMuaGllcmFyY2h5WyBpZCBdLmFyZy5nZXQoICdzbHVnJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGlmICggdmFsdWUgKSB7XG5cdFx0XHRhcmcgPSBzZWxlY3Rvci5jb2xsZWN0aW9uLmdldCggdmFsdWUgKTtcblxuXHRcdFx0aWYgKCAhIGFyZyApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHRoaXMudHJpZ2dlciggJ2NoYW5naW5nJywgdGhpcywgYXJnLCB2YWx1ZSApO1xuXG5cdFx0aWYgKCB2YWx1ZSApIHtcblxuXHRcdFx0dGhpcy5oaWVyYXJjaHlbIGlkIF0uYXJnID0gYXJnO1xuXG5cdFx0XHR0aGlzLnVwZGF0ZUNoaWxkcmVuKCBpZCApO1xuXG5cdFx0fSBlbHNlIHtcblxuXHRcdFx0Ly8gTm90aGluZyBpcyBzZWxlY3RlZCwgaGlkZSBhbGwgY2hpbGQgc2VsZWN0b3JzLlxuXHRcdFx0dGhpcy5oaWRlQ2hpbGRyZW4oIGlkICk7XG5cblx0XHRcdGRlbGV0ZSB0aGlzLmhpZXJhcmNoeVsgaWQgXS5hcmc7XG5cdFx0fVxuXG5cdFx0dGhpcy50cmlnZ2VyKCAnY2hhbmdlJywgdGhpcywgYXJnLCB2YWx1ZSApO1xuXHR9LFxuXG5cdHVwZGF0ZUNoaWxkcmVuOiBmdW5jdGlvbiAoIGlkICkge1xuXG5cdFx0dmFyIGFyZyA9IHRoaXMuaGllcmFyY2h5WyBpZCBdLmFyZywgY2hpbGRyZW47XG5cblx0XHRpZiAoIGFyZy5nZXRDaGlsZHJlbiApIHtcblxuXHRcdFx0Y2hpbGRyZW4gPSBhcmcuZ2V0Q2hpbGRyZW4oKTtcblxuXHRcdFx0Ly8gV2UgY29tcHJlc3MgcmVsYXRpb25zaGlwcyBzbyB3ZSBoYXZlIGp1c3QgUG9zdCDCuyBBdXRob3IgaW5zdGVhZCBvZlxuXHRcdFx0Ly8gUG9zdCDCuyBBdXRob3IgwrsgVXNlci5cblx0XHRcdGlmICggY2hpbGRyZW4ubGVuZ3RoICYmIGFyZy5nZXQoICdfdHlwZScgKSA9PT0gJ3JlbGF0aW9uc2hpcCcgKSB7XG5cdFx0XHRcdHZhciBjaGlsZCA9IGNoaWxkcmVuLmF0KCAwICk7XG5cblx0XHRcdFx0aWYgKCAhIGNoaWxkLmdldENoaWxkcmVuICkge1xuXHRcdFx0XHRcdHRoaXMuaGlkZUNoaWxkcmVuKCBpZCApO1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGNoaWxkcmVuID0gY2hpbGQuZ2V0Q2hpbGRyZW4oKTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gSGlkZSBhbnkgZ3JhbmRjaGlsZCBzZWxlY3RvcnMuXG5cdFx0XHR0aGlzLmhpZGVDaGlsZHJlbiggaWQgKyAxICk7XG5cblx0XHRcdC8vIENyZWF0ZSB0aGUgY2hpbGQgc2VsZWN0b3IgaWYgaXQgZG9lcyBub3QgZXhpc3QuXG5cdFx0XHRpZiAoICEgdGhpcy5oaWVyYXJjaHlbIGlkICsgMSBdICkge1xuXHRcdFx0XHR0aGlzLmFkZFNlbGVjdG9yKCBjaGlsZHJlbiApO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0dGhpcy5oaWVyYXJjaHlbIGlkICsgMSBdLnNlbGVjdG9yLmNvbGxlY3Rpb24ucmVzZXQoIGNoaWxkcmVuLm1vZGVscyApO1xuXHRcdFx0XHR0aGlzLmhpZXJhcmNoeVsgaWQgKyAxIF0uc2VsZWN0b3IuJGVsLnNob3coKS5maW5kKCAnc2VsZWN0JyApLmZvY3VzKCk7XG5cdFx0XHR9XG5cblx0XHR9IGVsc2Uge1xuXG5cdFx0XHR0aGlzLmhpZGVDaGlsZHJlbiggaWQgKTtcblx0XHR9XG5cdH0sXG5cblx0aGlkZUNoaWxkcmVuOiBmdW5jdGlvbiAoIGlkICkge1xuXHRcdF8uZWFjaCggdGhpcy5oaWVyYXJjaHkuc2xpY2UoIGlkICsgMSApLCBmdW5jdGlvbiAoIGxldmVsICkge1xuXHRcdFx0bGV2ZWwuc2VsZWN0b3IuJGVsLmhpZGUoKTtcblx0XHRcdGRlbGV0ZSBsZXZlbC5hcmc7XG5cdFx0fSk7XG5cdH0sXG5cblx0Z2V0SGllcmFyY2h5OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgaGllcmFyY2h5ID0gW107XG5cblx0XHRfLmVhY2goIHRoaXMuaGllcmFyY2h5LCBmdW5jdGlvbiAoIGxldmVsICkge1xuXG5cdFx0XHRpZiAoICEgbGV2ZWwuYXJnICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGhpZXJhcmNoeS5wdXNoKCBsZXZlbC5hcmcuZ2V0KCAnc2x1ZycgKSApO1xuXG5cdFx0XHQvLyBSZWxhdGlvbnNoaXBzIGFyZSBjb21wcmVzc2VkLCBzbyB3ZSBoYXZlIHRvIGV4cGFuZCB0aGVtIGhlcmUuXG5cdFx0XHRpZiAoIGxldmVsLmFyZy5nZXQoICdfdHlwZScgKSA9PT0gJ3JlbGF0aW9uc2hpcCcgKSB7XG5cdFx0XHRcdGhpZXJhcmNoeS5wdXNoKCBsZXZlbC5hcmcuZ2V0KCAnc2Vjb25kYXJ5JyApICk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cblx0XHRyZXR1cm4gaGllcmFyY2h5O1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBBcmdTZWxlY3RvcnM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICovXG52YXIgaG9va3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLFxuXHRleHRlbmQgPSBob29rcy51dGlsLmV4dGVuZCxcblx0QmFzZTtcblxuLy8gQWRkIGEgYmFzZSB2aWV3IHNvIHdlIGNhbiBoYXZlIGEgc3RhbmRhcmRpemVkIHZpZXcgYm9vdHN0cmFwIGZvciB0aGlzIGFwcC5cbkJhc2UgPSBCYWNrYm9uZS5WaWV3LmV4dGVuZCgge1xuXG5cdC8vIEZpcnN0LCB3ZSBsZXQgZWFjaCB2aWV3IHNwZWNpZnkgaXRzIG93biBuYW1lc3BhY2UsIHNvIHdlIGNhbiB1c2UgaXQgYXNcblx0Ly8gYSBwcmVmaXggZm9yIGFueSBzdGFuZGFyZCBldmVudHMgd2Ugd2FudCB0byBmaXJlLlxuXHRuYW1lc3BhY2U6ICdfYmFzZScsXG5cblx0Ly8gV2UgaGF2ZSBhbiBpbml0aWFsaXphdGlvbiBib290c3RyYXAuIEJlbG93IHdlJ2xsIHNldCB0aGluZ3MgdXAgc28gdGhhdFxuXHQvLyB0aGlzIGdldHMgY2FsbGVkIGV2ZW4gd2hlbiBhbiBleHRlbmRpbmcgdmlldyBzcGVjaWZpZXMgYW4gYGluaXRpYWxpemVgXG5cdC8vIGZ1bmN0aW9uLlxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIG9wdGlvbnMgKSB7XG5cblx0XHQvLyBUaGUgZmlyc3QgdGhpbmcgd2UgZG8gaXMgdG8gYWxsb3cgZm9yIGEgbmFtZXNwYWNlIHRvIGJlIHBhc3NlZCBpblxuXHRcdC8vIGFzIGFuIG9wdGlvbiB3aGVuIHRoZSB2aWV3IGlzIGNvbnN0cnVjdGVkLCBpbnN0ZWFkIG9mIGZvcmNpbmcgaXRcblx0XHQvLyB0byBiZSBwYXJ0IG9mIHRoZSBwcm90b3R5cGUgb25seS5cblx0XHRpZiAoIHR5cGVvZiBvcHRpb25zLm5hbWVzcGFjZSAhPT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHR0aGlzLm5hbWVzcGFjZSA9IG9wdGlvbnMubmFtZXNwYWNlO1xuXHRcdH1cblxuXHRcdGlmICggdHlwZW9mIG9wdGlvbnMucmVhY3Rpb24gIT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0dGhpcy5yZWFjdGlvbiA9IG9wdGlvbnMucmVhY3Rpb247XG5cdFx0fVxuXG5cdFx0Ly8gT25jZSB0aGluZ3MgYXJlIHNldCB1cCwgd2UgY2FsbCB0aGUgZXh0ZW5kaW5nIHZpZXcncyBgaW5pdGlhbGl6ZWBcblx0XHQvLyBmdW5jdGlvbi4gSXQgaXMgbWFwcGVkIHRvIGBfaW5pdGlhbGl6ZWAgb24gdGhlIGN1cnJlbnQgb2JqZWN0LlxuXHRcdHRoaXMuX19jaGlsZF9fLmluaXRpYWxpemUuYXBwbHkoIHRoaXMsIGFyZ3VtZW50cyApO1xuXG5cdFx0Ly8gRmluYWxseSwgd2UgdHJpZ2dlciBhbiBhY3Rpb24gdG8gbGV0IHRoZSB3aG9sZSBhcHAga25vdyB3ZSBqdXN0XG5cdFx0Ly8gY3JlYXRlZCB0aGlzIHZpZXcuXG5cdFx0aG9va3MudHJpZ2dlciggdGhpcy5uYW1lc3BhY2UgKyAnOnZpZXc6aW5pdCcsIHRoaXMgKTtcblx0fVxuXG59LCB7IGV4dGVuZDogZXh0ZW5kIH0gKTtcblxubW9kdWxlLmV4cG9ydHMgPSBCYXNlO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHRGaWVsZHMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkZpZWxkcyxcblx0UmVhY3RvcnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLlJlYWN0b3JzLFxuXHRBcmdzID0gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLFxuXHQkID0gQmFja2JvbmUuJCxcblx0bDEwbiA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5sMTBuLFxuXHRkYXRhID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LmRhdGEsXG5cdFJlYWN0aW9uO1xuXG4vLyBUaGUgRE9NIGVsZW1lbnQgZm9yIGEgcmVhY3Rpb24uLi5cblJlYWN0aW9uID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ3JlYWN0aW9uJyxcblxuXHRjbGFzc05hbWU6ICd3b3JkcG9pbnRzLWhvb2stcmVhY3Rpb24nLFxuXG5cdHRlbXBsYXRlOiB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlKCAnaG9vay1yZWFjdGlvbicgKSxcblxuXHQvLyBUaGUgRE9NIGV2ZW50cyBzcGVjaWZpYyB0byBhbiBpdGVtLlxuXHRldmVudHM6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBldmVudHMgPSB7XG5cdFx0XHQnY2xpY2sgLmFjdGlvbnMgLmRlbGV0ZSc6ICdjb25maXJtRGVsZXRlJyxcblx0XHRcdCdjbGljayAuc2F2ZSc6ICAgICAgICAgICAgJ3NhdmUnLFxuXHRcdFx0J2NsaWNrIC5jYW5jZWwnOiAgICAgICAgICAnY2FuY2VsJyxcblx0XHRcdCdjbGljayAuY2xvc2UnOiAgICAgICAgICAgJ2Nsb3NlJyxcblx0XHRcdCdjbGljayAuZWRpdCc6ICAgICAgICAgICAgJ2VkaXQnLFxuXHRcdFx0J2NoYW5nZSAuZmllbGRzIConOiAgICAgICAnbG9ja09wZW4nXG5cdFx0fTtcblxuXHRcdC8qXG5cdFx0ICogVXNlIGZlYXR1cmUgZGV0ZWN0aW9uIHRvIGRldGVybWluZSB3aGV0aGVyIHdlIHNob3VsZCB1c2UgdGhlIGBpbnB1dGBcblx0XHQgKiBldmVudC4gSW5wdXQgaXMgcHJlZmVycmVkIGJ1dCBsYWNrcyBzdXBwb3J0IGluIGxlZ2FjeSBicm93c2Vycy5cblx0XHQgKi9cblx0XHRpZiAoICdvbmlucHV0JyBpbiBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCAnaW5wdXQnICkgKSB7XG5cdFx0XHRldmVudHNbJ2lucHV0IGlucHV0J10gPSAnbG9ja09wZW4nO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRldmVudHNbJ2tleXVwIGlucHV0J10gPSAnbWF5YmVMb2NrT3Blbic7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGV2ZW50cztcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnY2hhbmdlOmRlc2NyaXB0aW9uJywgdGhpcy5yZW5kZXJEZXNjcmlwdGlvbiApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdjaGFuZ2U6cmVhY3RvcicsIHRoaXMuc2V0UmVhY3RvciApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdjaGFuZ2U6cmVhY3RvcicsIHRoaXMucmVuZGVyVGFyZ2V0ICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2Rlc3Ryb3knLCB0aGlzLnJlbW92ZSApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdzeW5jJywgdGhpcy5zaG93U3VjY2VzcyApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdlcnJvcicsIHRoaXMuc2hvd0FqYXhFcnJvcnMgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnaW52YWxpZCcsIHRoaXMuc2hvd1ZhbGlkYXRpb25FcnJvcnMgKTtcblxuXHRcdHRoaXMub24oICdyZW5kZXI6c2V0dGluZ3MnLCB0aGlzLnJlbmRlclRhcmdldCApO1xuXG5cdFx0dGhpcy5zZXRSZWFjdG9yKCk7XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRlbC5odG1sKCB0aGlzLnRlbXBsYXRlKCkgKTtcblxuXHRcdHRoaXMuJHRpdGxlICAgID0gdGhpcy4kKCAnLnRpdGxlJyApO1xuXHRcdHRoaXMuJGZpZWxkcyAgID0gdGhpcy4kKCAnLmZpZWxkcycgKTtcblx0XHR0aGlzLiRzZXR0aW5ncyA9IHRoaXMuJGZpZWxkcy5maW5kKCAnLnNldHRpbmdzJyApO1xuXHRcdHRoaXMuJHRhcmdldCAgID0gdGhpcy4kZmllbGRzLmZpbmQoICcudGFyZ2V0JyApO1xuXG5cdFx0dGhpcy5yZW5kZXJEZXNjcmlwdGlvbigpO1xuXG5cdFx0dGhpcy50cmlnZ2VyKCAncmVuZGVyJywgdGhpcyApO1xuXG5cdFx0cmV0dXJuIHRoaXM7XG5cdH0sXG5cblx0Ly8gUmUtcmVuZGVyIHRoZSB0aXRsZSBvZiB0aGUgaG9vay5cblx0cmVuZGVyRGVzY3JpcHRpb246IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJHRpdGxlLnRleHQoIHRoaXMubW9kZWwuZ2V0KCAnZGVzY3JpcHRpb24nICkgKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjp0aXRsZScsIHRoaXMgKTtcblx0fSxcblxuXHRyZW5kZXJGaWVsZHM6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBjdXJyZW50QWN0aW9uVHlwZSA9IHRoaXMuZ2V0Q3VycmVudEFjdGlvblR5cGUoKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjpzZXR0aW5ncycsIHRoaXMuJHNldHRpbmdzLCBjdXJyZW50QWN0aW9uVHlwZSwgdGhpcyApO1xuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjpmaWVsZHMnLCB0aGlzLiRmaWVsZHMsIGN1cnJlbnRBY3Rpb25UeXBlLCB0aGlzICk7XG5cblx0XHR0aGlzLnJlbmRlcmVkRmllbGRzID0gdHJ1ZTtcblx0fSxcblxuXHRyZW5kZXJUYXJnZXQ6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBhcmdUeXBlcyA9IHRoaXMuUmVhY3Rvci5nZXQoICdhcmdfdHlwZXMnICksXG5cdFx0XHRlbmQ7XG5cblx0XHQvLyBJZiB0aGVyZSBpcyBqdXN0IG9uZSBhcmcgdHlwZSwgd2UgY2FuIHVzZSB0aGUgYF8ud2hlcmUoKWAtbGlrZSBzeW50YXguXG5cdFx0aWYgKCBhcmdUeXBlcy5sZW5ndGggPT09IDEgKSB7XG5cblx0XHRcdGVuZCA9IHsgX2Nhbm9uaWNhbDogYXJnVHlwZXNbMF0sIF90eXBlOiAnZW50aXR5JyB9O1xuXG5cdFx0fSBlbHNlIHtcblxuXHRcdFx0Ly8gT3RoZXJ3aXNlLCB3ZSdsbCBiZSBuZWVkIG91ciBvd24gZnVuY3Rpb24sIGZvciBgXy5maWx0ZXIoKWAuXG5cdFx0XHRlbmQgPSBmdW5jdGlvbiAoIGFyZyApIHtcblx0XHRcdFx0cmV0dXJuIChcblx0XHRcdFx0XHRhcmcuZ2V0KCAnX3R5cGUnICkgPT09ICdlbnRpdHknXG5cdFx0XHRcdFx0JiYgXy5jb250YWlucyggYXJnVHlwZXMsIGFyZy5nZXQoICdfY2Fub25pY2FsJyApIClcblx0XHRcdFx0KTtcblx0XHRcdH07XG5cdFx0fVxuXG5cdFx0dmFyIGhpZXJhcmNoaWVzID0gQXJncy5nZXRIaWVyYXJjaGllc01hdGNoaW5nKCB7XG5cdFx0XHRldmVudDogdGhpcy5tb2RlbC5nZXQoICdldmVudCcgKSxcblx0XHRcdGVuZDogZW5kXG5cdFx0fSApO1xuXG5cdFx0dmFyIG9wdGlvbnMgPSBbXTtcblxuXHRcdF8uZWFjaCggaGllcmFyY2hpZXMsIGZ1bmN0aW9uICggaGllcmFyY2h5ICkge1xuXHRcdFx0b3B0aW9ucy5wdXNoKCB7XG5cdFx0XHRcdGxhYmVsOiBBcmdzLmJ1aWxkSGllcmFyY2h5SHVtYW5JZCggaGllcmFyY2h5ICksXG5cdFx0XHRcdHZhbHVlOiBfLnBsdWNrKCBfLnBsdWNrKCBoaWVyYXJjaHksICdhdHRyaWJ1dGVzJyApLCAnc2x1ZycgKS5qb2luKCAnLCcgKVxuXHRcdFx0fSApO1xuXHRcdH0pO1xuXG5cdFx0dmFyIHZhbHVlID0gdGhpcy5tb2RlbC5nZXQoICd0YXJnZXQnICk7XG5cblx0XHRpZiAoIF8uaXNBcnJheSggdmFsdWUgKSApIHtcblx0XHRcdHZhbHVlID0gdmFsdWUuam9pbiggJywnICk7XG5cdFx0fVxuXG5cdFx0dmFyIGxhYmVsID0gdGhpcy5SZWFjdG9yLmdldCggJ3RhcmdldF9sYWJlbCcgKTtcblxuXHRcdGlmICggISBsYWJlbCApIHtcblx0XHRcdGxhYmVsID0gbDEwbi50YXJnZXRfbGFiZWw7XG5cdFx0fVxuXG5cdFx0aWYgKCAhIHRoaXMubW9kZWwuaXNOZXcoKSApIHtcblx0XHRcdGxhYmVsICs9ICcgJyArIGwxMG4uY2Fubm90QmVDaGFuZ2VkO1xuXHRcdH1cblxuXHRcdHZhciBmaWVsZCA9IEZpZWxkcy5jcmVhdGUoXG5cdFx0XHQndGFyZ2V0J1xuXHRcdFx0LCB2YWx1ZVxuXHRcdFx0LCB7XG5cdFx0XHRcdHR5cGU6ICdzZWxlY3QnLFxuXHRcdFx0XHRvcHRpb25zOiBvcHRpb25zLFxuXHRcdFx0XHRsYWJlbDogbGFiZWxcblx0XHRcdH1cblx0XHQpO1xuXG5cdFx0dGhpcy4kdGFyZ2V0Lmh0bWwoIGZpZWxkICk7XG5cblx0XHRpZiAoICEgdGhpcy5tb2RlbC5pc05ldygpICkge1xuXHRcdFx0dGhpcy4kdGFyZ2V0LmZpbmQoICdzZWxlY3QnICkucHJvcCggJ2Rpc2FibGVkJywgdHJ1ZSApO1xuXHRcdH1cblx0fSxcblxuXHRzZXRSZWFjdG9yOiBmdW5jdGlvbiAoKSB7XG5cdFx0dGhpcy5SZWFjdG9yID0gUmVhY3RvcnMuZ2V0KCB0aGlzLm1vZGVsLmdldCggJ3JlYWN0b3InICkgKTtcblx0fSxcblxuXHQvLyBHZXQgdGhlIGN1cnJlbnQgYWN0aW9uIHR5cGUgdGhhdCBzZXR0aW5ncyBhcmUgYmVpbmcgZGlzcGxheWVkIGZvci5cblx0Ly8gUmlnaHQgbm93IHdlIGp1c3QgZGVmYXVsdCB0aGlzIHRvIHRoZSBmaXJzdCBhY3Rpb24gdHlwZSB0aGF0IHRoZSByZWFjdG9yXG5cdC8vIHN1cHBvcnRzIHdoaWNoIGlzIHJlZ2lzdGVyZWQgZm9yIHRoaXMgZXZlbnQuXG5cdGdldEN1cnJlbnRBY3Rpb25UeXBlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgZXZlbnRBY3Rpb25UeXBlcyA9IGRhdGEuZXZlbnRfYWN0aW9uX3R5cGVzWyB0aGlzLm1vZGVsLmdldCggJ2V2ZW50JyApIF07XG5cblx0XHRpZiAoICEgZXZlbnRBY3Rpb25UeXBlcyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR2YXIgcmVhY3RvckFjdGlvblR5cGVzID0gdGhpcy5SZWFjdG9yLmdldCggJ2FjdGlvbl90eXBlcycgKTtcblxuXHRcdC8vIFdlIGxvb3AgdGhyb3VnaCB0aGUgcmVhY3RvciBhY3Rpb24gdHlwZXMgYXMgdGhlIHByaW1hcnkgbGlzdCwgYmVjYXVzZSBpdFxuXHRcdC8vIGlzIGluIG9yZGVyLCB3aGlsZSB0aGUgZXZlbnQgYWN0aW9uIHR5cGVzIGlzbid0IGluIGFueSBwYXJ0aWN1bGFyIG9yZGVyLlxuXHRcdC8vIE90aGVyd2lzZSB3ZSdkIGVuZCB1cCBzZWxlY3RpbmcgdGhlIGFjdGlvbiB0eXBlcyBpbmNvbnNpc3RlbnRseS5cblx0XHRmb3IgKCB2YXIgaSA9IDA7IGkgPCByZWFjdG9yQWN0aW9uVHlwZXMubGVuZ3RoOyBpKysgKSB7XG5cdFx0XHRpZiAoIGV2ZW50QWN0aW9uVHlwZXNbIHJlYWN0b3JBY3Rpb25UeXBlc1sgaSBdIF0gKSB7XG5cdFx0XHRcdHJldHVybiByZWFjdG9yQWN0aW9uVHlwZXNbIGkgXTtcblx0XHRcdH1cblx0XHR9XG5cdH0sXG5cblx0Ly8gVG9nZ2xlIHRoZSB2aXNpYmlsaXR5IG9mIHRoZSBmb3JtLlxuXHRlZGl0OiBmdW5jdGlvbiAoKSB7XG5cblx0XHRpZiAoICEgdGhpcy5yZW5kZXJlZEZpZWxkcyApIHtcblx0XHRcdHRoaXMucmVuZGVyRmllbGRzKCk7XG5cdFx0fVxuXG5cdFx0Ly8gVGhlbiBkaXNwbGF5IHRoZSBmb3JtLlxuXHRcdHRoaXMuJGZpZWxkcy5zbGlkZURvd24oICdmYXN0JyApO1xuXHRcdHRoaXMuJGVsLmFkZENsYXNzKCAnZWRpdGluZycgKTtcblx0fSxcblxuXHQvLyBDbG9zZSB0aGUgZm9ybS5cblx0Y2xvc2U6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGZpZWxkcy5zbGlkZVVwKCAnZmFzdCcgKTtcblx0XHR0aGlzLiRlbC5yZW1vdmVDbGFzcyggJ2VkaXRpbmcnICk7XG5cdFx0dGhpcy4kKCAnLnN1Y2Nlc3MnICkuaGlkZSgpO1xuXHR9LFxuXG5cdC8vIE1heWJlIGxvY2sgdGhlIGZvcm0gb3BlbiB3aGVuIGFuIGlucHV0IGlzIGFsdGVyZWQuXG5cdG1heWJlTG9ja09wZW46IGZ1bmN0aW9uICggZXZlbnQgKSB7XG5cblx0XHR2YXIgJHRhcmdldCA9ICQoIGV2ZW50LnRhcmdldCApO1xuXG5cdFx0dmFyIGF0dHJTbHVnID0gRmllbGRzLmdldEF0dHJTbHVnKCB0aGlzLm1vZGVsLCAkdGFyZ2V0LmF0dHIoICduYW1lJyApICk7XG5cblx0XHRpZiAoICR0YXJnZXQudmFsKCkgIT09IHRoaXMubW9kZWwuZ2V0KCBhdHRyU2x1ZyApICsgJycgKSB7XG5cdFx0XHR0aGlzLmxvY2tPcGVuKCk7XG5cdFx0fVxuXHR9LFxuXG5cdC8vIExvY2sgdGhlIGZvcm0gb3BlbiB3aGVuIHRoZSBmb3JtIHZhbHVlcyBoYXZlIGJlZW4gY2hhbmdlZC5cblx0bG9ja09wZW46IGZ1bmN0aW9uICgpIHtcblxuXHRcdGlmICggdGhpcy5jYW5jZWxsaW5nICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRoaXMuJGVsLmFkZENsYXNzKCAnY2hhbmdlZCcgKTtcblx0XHR0aGlzLiQoICcuc2F2ZScgKS5wcm9wKCAnZGlzYWJsZWQnLCBmYWxzZSApO1xuXHRcdHRoaXMuJCggJy5zdWNjZXNzJyApLmZhZGVPdXQoKTtcblx0fSxcblxuXHQvLyBDYW5jZWwgZWRpdGluZyBvciBhZGRpbmcgYSBuZXcgcmVhY3Rpb24uXG5cdGNhbmNlbDogZnVuY3Rpb24gKCkge1xuXG5cdFx0aWYgKCB0aGlzLiRlbC5oYXNDbGFzcyggJ25ldycgKSApIHtcblxuXHRcdFx0dGhpcy5tb2RlbC5jb2xsZWN0aW9uLnRyaWdnZXIoICdjYW5jZWwtYWRkLW5ldycgKTtcblx0XHRcdHRoaXMucmVtb3ZlKCk7XG5cblx0XHRcdHdwLmExMXkuc3BlYWsoIGwxMG4uZGlzY2FyZGVkUmVhY3Rpb24gKTtcblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRoaXMuJGVsLnJlbW92ZUNsYXNzKCAnY2hhbmdlZCcgKTtcblx0XHR0aGlzLiQoICcuc2F2ZScgKS5wcm9wKCAnZGlzYWJsZWQnLCB0cnVlICk7XG5cblx0XHR0aGlzLmNhbmNlbGxpbmcgPSB0cnVlO1xuXG5cdFx0dGhpcy5yZW5kZXJGaWVsZHMoKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ2NhbmNlbCcgKTtcblxuXHRcdHdwLmExMXkuc3BlYWsoIGwxMG4uZGlzY2FyZGVkQ2hhbmdlcyApO1xuXG5cdFx0dGhpcy5jYW5jZWxsaW5nID0gZmFsc2U7XG5cdH0sXG5cblx0Ly8gU2F2ZSBjaGFuZ2VzIHRvIHRoZSByZWFjdGlvbi5cblx0c2F2ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy53YWl0KCk7XG5cdFx0dGhpcy4kKCAnLnNhdmUnICkucHJvcCggJ2Rpc2FibGVkJywgdHJ1ZSApO1xuXG5cdFx0d3AuYTExeS5zcGVhayggbDEwbi5zYXZpbmcgKTtcblxuXHRcdHZhciBmb3JtRGF0YSA9IEZpZWxkcy5nZXRGb3JtRGF0YSggdGhpcy5tb2RlbCwgdGhpcy4kZmllbGRzICk7XG5cblx0XHRpZiAoIGZvcm1EYXRhLnRhcmdldCApIHtcblx0XHRcdGZvcm1EYXRhLnRhcmdldCA9IGZvcm1EYXRhLnRhcmdldC5zcGxpdCggJywnICk7XG5cdFx0fVxuXG5cdFx0dGhpcy5tb2RlbC5zYXZlKCBmb3JtRGF0YSwgeyB3YWl0OiB0cnVlLCByYXdBdHRzOiBmb3JtRGF0YSB9ICk7XG5cdH0sXG5cblx0Ly8gRGlzcGxheSBhIHNwaW5uZXIgd2hpbGUgY2hhbmdlcyBhcmUgYmVpbmcgc2F2ZWQuXG5cdHdhaXQ6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJCggJy5zcGlubmVyLW92ZXJsYXknICkuc2hvdygpO1xuXHRcdHRoaXMuJCggJy5lcnInICkuc2xpZGVVcCgpO1xuXHR9LFxuXG5cdC8vIENvbmZpcm0gdGhhdCBhIHJlYWN0aW9uIGlzIGludGVuZGVkIHRvIGJlIGRlbGV0ZWQgYmVmb3JlIGRlbGV0aW5nIGl0LlxuXHRjb25maXJtRGVsZXRlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgJGRpYWxvZyA9ICQoICc8ZGl2PjxwPjwvcD48L2Rpdj4nICksXG5cdFx0XHR2aWV3ID0gdGhpcztcblxuXHRcdHRoaXMuJCggJy5tZXNzYWdlcyBkaXYnICkuc2xpZGVVcCgpO1xuXG5cdFx0JGRpYWxvZ1xuXHRcdFx0LmF0dHIoICd0aXRsZScsIGwxMG4uY29uZmlybVRpdGxlIClcblx0XHRcdC5maW5kKCAncCcgKVxuXHRcdFx0XHQudGV4dCggbDEwbi5jb25maXJtRGVsZXRlIClcblx0XHRcdC5lbmQoKVxuXHRcdFx0LmRpYWxvZyh7XG5cdFx0XHRcdGRpYWxvZ0NsYXNzOiAnd3AtZGlhbG9nIHdvcmRwb2ludHMtZGVsZXRlLWhvb2stcmVhY3Rpb24tZGlhbG9nJyxcblx0XHRcdFx0cmVzaXphYmxlOiBmYWxzZSxcblx0XHRcdFx0ZHJhZ2dhYmxlOiBmYWxzZSxcblx0XHRcdFx0aGVpZ2h0OiAyNTAsXG5cdFx0XHRcdG1vZGFsOiB0cnVlLFxuXHRcdFx0XHRidXR0b25zOiBbXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0dGV4dDogbDEwbi5jYW5jZWxUZXh0LFxuXHRcdFx0XHRcdFx0J2NsYXNzJzogJ2J1dHRvbi1zZWNvbmRhcnknLFxuXHRcdFx0XHRcdFx0Y2xpY2s6IGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRcdFx0XHQkKCB0aGlzICkuZGlhbG9nKCAnZGVzdHJveScgKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdHRleHQ6IGwxMG4uZGVsZXRlVGV4dCxcblx0XHRcdFx0XHRcdCdjbGFzcyc6ICdidXR0b24tcHJpbWFyeScsXG5cdFx0XHRcdFx0XHRjbGljazogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdFx0XHRcdCQoIHRoaXMgKS5kaWFsb2coICdkZXN0cm95JyApO1xuXHRcdFx0XHRcdFx0XHR2aWV3LmRlc3Ryb3koKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdF1cblx0XHRcdH0pO1xuXHR9LFxuXG5cdC8vIFJlbW92ZSB0aGUgaXRlbSwgZGVzdHJveSB0aGUgbW9kZWwuXG5cdGRlc3Ryb3k6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHdwLmExMXkuc3BlYWsoIGwxMG4uZGVsZXRpbmcgKTtcblxuXHRcdHRoaXMud2FpdCgpO1xuXG5cdFx0dGhpcy5tb2RlbC5kZXN0cm95KFxuXHRcdFx0e1xuXHRcdFx0XHR3YWl0OiB0cnVlLFxuXHRcdFx0XHRzdWNjZXNzOiBmdW5jdGlvbiAoKSB7XG5cdFx0XHRcdFx0d3AuYTExeS5zcGVhayggbDEwbi5yZWFjdGlvbkRlbGV0ZWQgKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdCk7XG5cdH0sXG5cblx0Ly8gRGlzcGxheSBlcnJvcnMgd2hlbiB0aGUgbW9kZWwgaGFzIGludmFsaWQgZmllbGRzLlxuXHRzaG93VmFsaWRhdGlvbkVycm9yczogZnVuY3Rpb24gKCBtb2RlbCwgZXJyb3JzICkge1xuXHRcdHRoaXMuc2hvd0Vycm9yKCBlcnJvcnMgKTtcblx0fSxcblxuXHQvLyBEaXNwbGF5IGFuIGVycm9yIHdoZW4gdGhlcmUgaXMgYW4gQWpheCBmYWlsdXJlLlxuXHRzaG93QWpheEVycm9yczogZnVuY3Rpb24gKCBldmVudCwgcmVzcG9uc2UgKSB7XG5cblx0XHR2YXIgZXJyb3JzO1xuXG5cdFx0aWYgKCAhIF8uaXNFbXB0eSggcmVzcG9uc2UuZXJyb3JzICkgKSB7XG5cdFx0XHRlcnJvcnMgPSByZXNwb25zZS5lcnJvcnM7XG5cdFx0fSBlbHNlIGlmICggcmVzcG9uc2UubWVzc2FnZSApIHtcblx0XHRcdGVycm9ycyA9IHJlc3BvbnNlLm1lc3NhZ2U7XG5cdFx0fSBlbHNlIHtcblx0XHRcdGVycm9ycyA9IGwxMG4udW5leHBlY3RlZEVycm9yO1xuXHRcdH1cblxuXHRcdHRoaXMuc2hvd0Vycm9yKCBlcnJvcnMgKTtcblx0fSxcblxuXHRzaG93RXJyb3I6IGZ1bmN0aW9uICggZXJyb3JzICkge1xuXG5cdFx0dmFyIGdlbmVyYWxFcnJvcnMgPSBbXTtcblx0XHR2YXIgYTExeUVycm9ycyA9IFtdO1xuXHRcdHZhciAkZXJyb3JzID0gdGhpcy4kKCAnLm1lc3NhZ2VzIC5lcnInICk7XG5cblx0XHR0aGlzLiQoICcuc3Bpbm5lci1vdmVybGF5JyApLmhpZGUoKTtcblxuXHRcdC8vIFNvbWV0aW1lcyB3ZSBnZXQgYSBsaXN0IG9mIGVycm9ycy5cblx0XHRpZiAoIF8uaXNBcnJheSggZXJyb3JzICkgKSB7XG5cblx0XHRcdC8vIFdoZW4gdGhhdCBoYXBwZW5zLCB3ZSBsb29wIG92ZXIgdGhlbSBhbmQgdHJ5IHRvIGRpc3BsYXkgZWFjaCBvZlxuXHRcdFx0Ly8gdGhlbSBuZXh0IHRvIHRoZWlyIGFzc29jaWF0ZWQgZmllbGQuXG5cdFx0XHRfLmVhY2goIGVycm9ycywgZnVuY3Rpb24gKCBlcnJvciApIHtcblxuXHRcdFx0XHR2YXIgJGZpZWxkLCBlc2NhcGVkRmllbGROYW1lO1xuXG5cdFx0XHRcdC8vIFNvbWV0aW1lcyBzb21lIG9mIHRoZSBlcnJvcnMgYXJlbid0IGZvciBhbnkgcGFydGljdWxhciBmaWVsZFxuXHRcdFx0XHQvLyB0aG91Z2gsIHNvIHdlIGNvbGxlY3QgdGhlbSBpbiBhbiBhcnJheSBhbiBkaXNwbGF5IHRoZW0gYWxsXG5cdFx0XHRcdC8vIHRvZ2V0aGVyIGEgYml0IGxhdGVyLlxuXHRcdFx0XHRpZiAoICEgZXJyb3IuZmllbGQgKSB7XG5cdFx0XHRcdFx0Z2VuZXJhbEVycm9ycy5wdXNoKCBlcnJvci5tZXNzYWdlICk7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0ZXNjYXBlZEZpZWxkTmFtZSA9IEZpZWxkcy5nZXRGaWVsZE5hbWUoIGVycm9yLmZpZWxkIClcblx0XHRcdFx0XHRcdC5yZXBsYWNlKCAvW15hLXowLTktX1xcW1xcXVxce31cXFxcXS9naSwgJycgKVxuXHRcdFx0XHRcdFx0LnJlcGxhY2UoIC9cXFxcL2csICdcXFxcXFxcXCcgKTtcblxuXHRcdFx0XHQvLyBXaGVuIGEgZmllbGQgaXMgc3BlY2lmaWVkLCB3ZSB0cnkgdG8gbG9jYXRlIGl0LlxuXHRcdFx0XHQkZmllbGQgPSB0aGlzLiQoICdbbmFtZT1cIicgKyBlc2NhcGVkRmllbGROYW1lICsgJ1wiXScgKTtcblxuXHRcdFx0XHRpZiAoIDAgPT09ICRmaWVsZC5sZW5ndGggKSB7XG5cblx0XHRcdFx0XHQvLyBIb3dldmVyLCB0aGVyZSBhcmUgdGltZXMgd2hlbiB0aGUgZXJyb3IgaXMgZm9yIGEgZmllbGQgc2V0XG5cdFx0XHRcdFx0Ly8gYW5kIG5vdCBhIHNpbmdsZSBmaWVsZC4gSW4gdGhhdCBjYXNlLCB3ZSB0cnkgdG8gZmluZCB0aGVcblx0XHRcdFx0XHQvLyBmaWVsZHMgaW4gdGhhdCBzZXQuXG5cdFx0XHRcdFx0JGZpZWxkID0gdGhpcy4kKCAnW25hbWVePVwiJyArIGVzY2FwZWRGaWVsZE5hbWUgKyAnXCJdJyApO1xuXG5cdFx0XHRcdFx0Ly8gSWYgdGhhdCBmYWlscywgd2UganVzdCBhZGQgdGhpcyB0byB0aGUgZ2VuZXJhbCBlcnJvcnMuXG5cdFx0XHRcdFx0aWYgKCAwID09PSAkZmllbGQubGVuZ3RoICkge1xuXHRcdFx0XHRcdFx0Z2VuZXJhbEVycm9ycy5wdXNoKCBlcnJvci5tZXNzYWdlICk7XG5cdFx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0JGZpZWxkID0gJGZpZWxkLmZpcnN0KCk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQkZmllbGQuYmVmb3JlKFxuXHRcdFx0XHRcdCQoICc8ZGl2IGNsYXNzPVwibWVzc2FnZSBlcnJcIj48L2Rpdj4nICkudGV4dCggZXJyb3IubWVzc2FnZSApXG5cdFx0XHRcdCk7XG5cblx0XHRcdFx0YTExeUVycm9ycy5wdXNoKCBlcnJvci5tZXNzYWdlICk7XG5cblx0XHRcdH0sIHRoaXMgKTtcblxuXHRcdFx0JGVycm9ycy5odG1sKCAnJyApO1xuXG5cdFx0XHQvLyBUaGVyZSBtYXkgYmUgc29tZSBnZW5lcmFsIGVycm9ycyB0aGF0IHdlIG5lZWQgdG8gZGlzcGxheSB0byB0aGUgdXNlci5cblx0XHRcdC8vIFdlIGFsc28gYWRkIGFuIGV4cGxhbmF0aW9uIHRoYXQgdGhlcmUgYXJlIHNvbWUgZmllbGRzIHRoYXQgbmVlZCB0byBiZVxuXHRcdFx0Ly8gY29ycmVjdGVkLCBpZiB0aGVyZSB3ZXJlIHNvbWUgcGVyLWZpZWxkIGVycm9ycywgdG8gbWFrZSBzdXJlIHRoYXQgdGhleVxuXHRcdFx0Ly8gc2VlIHRob3NlIGVycm9ycyBhcyB3ZWxsIChzaW5jZSB0aGV5IG1heSBub3QgYmUgaW4gdmlldykuXG5cdFx0XHRpZiAoIGdlbmVyYWxFcnJvcnMubGVuZ3RoIDwgZXJyb3JzLmxlbmd0aCApIHtcblx0XHRcdFx0Z2VuZXJhbEVycm9ycy51bnNoaWZ0KCBsMTBuLmZpZWxkc0ludmFsaWQgKTtcblx0XHRcdH1cblxuXHRcdFx0Xy5lYWNoKCBnZW5lcmFsRXJyb3JzLCBmdW5jdGlvbiAoIGVycm9yICkge1xuXHRcdFx0XHQkZXJyb3JzLmFwcGVuZCggJCggJzxwPjwvcD4nICkudGV4dCggZXJyb3IgKSApO1xuXHRcdFx0fSk7XG5cblx0XHRcdC8vIE5vdGlmeSB1bnNpZ2h0ZWQgdXNlcnMgYXMgd2VsbC5cblx0XHRcdGExMXlFcnJvcnMudW5zaGlmdCggbDEwbi5maWVsZHNJbnZhbGlkICk7XG5cblx0XHRcdHdwLmExMXkuc3BlYWsoIGExMXlFcnJvcnMuam9pbiggJyAnICkgKTtcblxuXHRcdH0gZWxzZSB7XG5cblx0XHRcdCRlcnJvcnMudGV4dCggZXJyb3JzICk7XG5cdFx0XHR3cC5hMTF5LnNwZWFrKCBlcnJvcnMgKTtcblx0XHR9XG5cblx0XHQkZXJyb3JzLmZhZGVJbigpO1xuXHR9LFxuXG5cdC8vIERpc3BsYXkgYSBzdWNjZXNzIG1lc3NhZ2UuXG5cdHNob3dTdWNjZXNzOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiQoICcuc3Bpbm5lci1vdmVybGF5JyApLmhpZGUoKTtcblxuXHRcdHRoaXMuJCggJy5zdWNjZXNzJyApXG5cdFx0XHQudGV4dCggbDEwbi5jaGFuZ2VzU2F2ZWQgKVxuXHRcdFx0LnNsaWRlRG93bigpO1xuXG5cdFx0d3AuYTExeS5zcGVhayggbDEwbi5yZWFjdGlvblNhdmVkICk7XG5cblx0XHR0aGlzLiR0YXJnZXQuZmluZCggJ3NlbGVjdCcgKS5wcm9wKCAnZGlzYWJsZWQnLCB0cnVlICk7XG5cblx0XHR0aGlzLiRlbC5yZW1vdmVDbGFzcyggJ25ldyBjaGFuZ2VkJyApO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBSZWFjdGlvbjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3Lkhvb2tzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdFJlYWN0aW9uVmlldyA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5SZWFjdGlvbixcblx0UmVhY3Rpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLlJlYWN0aW9uLFxuXHRSZWFjdGlvbnM7XG5cblJlYWN0aW9ucyA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdyZWFjdGlvbnMnLFxuXG5cdC8vIERlbGVnYXRlZCBldmVudHMgZm9yIGNyZWF0aW5nIG5ldyByZWFjdGlvbnMuXG5cdGV2ZW50czoge1xuXHRcdCdjbGljayAuYWRkLXJlYWN0aW9uJzogJ2luaXRBZGRSZWFjdGlvbidcblx0fSxcblxuXHQvLyBBdCBpbml0aWFsaXphdGlvbiB3ZSBiaW5kIHRvIHRoZSByZWxldmFudCBldmVudHMgb24gdGhlIGBSZWFjdGlvbnNgXG5cdC8vIGNvbGxlY3Rpb24sIHdoZW4gaXRlbXMgYXJlIGFkZGVkIG9yIGNoYW5nZWQuIEtpY2sgdGhpbmdzIG9mZiBieVxuXHQvLyBsb2FkaW5nIGFueSBwcmVleGlzdGluZyBob29rcyBmcm9tICp0aGUgZGF0YWJhc2UqLlxuXHRpbml0aWFsaXplOiBmdW5jdGlvbigpIHtcblxuXHRcdHRoaXMuJHJlYWN0aW9uR3JvdXAgPSB0aGlzLiQoICcud29yZHBvaW50cy1ob29rLXJlYWN0aW9uLWdyb3VwJyApO1xuXHRcdHRoaXMuJGFkZFJlYWN0aW9uICAgPSB0aGlzLiQoICcuYWRkLXJlYWN0aW9uJyApO1xuXHRcdHRoaXMuJGV2ZW50cyA9IHRoaXMuJCggJy53b3JkcG9pbnRzLWhvb2stZXZlbnRzJyApO1xuXG5cdFx0aWYgKCB0aGlzLiRldmVudHMubGVuZ3RoICE9PSAwICkge1xuXHRcdFx0Ly8gQ2hlY2sgaG93IG1hbnkgZGlmZmVyZW50IGV2ZW50cyB0aGlzIGdyb3VwIHN1cHBvcnRzLiBJZiBpdCBpcyBvbmx5XG5cdFx0XHQvLyBvbmUsIHdlIGNhbiBoaWRlIHRoZSBldmVudCBzZWxlY3Rvci5cblx0XHRcdGlmICggMiA9PT0gdGhpcy4kZXZlbnRzLmNoaWxkcmVuKCAnb3B0aW9uJyApLmxlbmd0aCApIHtcblx0XHRcdFx0dGhpcy4kZXZlbnRzLnByb3AoICdzZWxlY3RlZEluZGV4JywgMSApLmhpZGUoKTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHQvLyBNYWtlIHN1cmUgdGhhdCB0aGUgYWRkIHJlYWN0aW9uIGJ1dHRvbiBpc24ndCBkaXNhYmxlZCwgYmVjYXVzZSBzb21ldGltZXNcblx0XHQvLyB0aGUgYnJvd3NlciB3aWxsIGF1dG9tYXRpY2FsbHkgZGlzYWJsZSBpdCwgZS5nLiwgaWYgaXQgd2FzIGRpc2FibGVkXG5cdFx0Ly8gYW5kIHRoZSBwYWdlIHdhcyByZWZyZXNoZWQuXG5cdFx0dGhpcy4kYWRkUmVhY3Rpb24ucHJvcCggJ2Rpc2FibGVkJywgZmFsc2UgKTtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdhZGQnLCB0aGlzLmFkZE9uZSApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdyZXNldCcsIHRoaXMuYWRkQWxsICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2NhbmNlbC1hZGQtbmV3JywgdGhpcy5jYW5jZWxBZGRSZWFjdGlvbiApO1xuXG5cdFx0dGhpcy5hZGRBbGwoKTtcblx0fSxcblxuXHQvLyBBZGQgYSBzaW5nbGUgcmVhY3Rpb24gdG8gdGhlIGdyb3VwIGJ5IGNyZWF0aW5nIGEgdmlldyBmb3IgaXQsIGFuZCBhcHBlbmRpbmdcblx0Ly8gaXRzIGVsZW1lbnQgdG8gdGhlIGdyb3VwLiBJZiB0aGlzIGlzIGEgbmV3IHJlYWN0aW9uIHdlIGVudGVyIGVkaXQgbW9kZSBmcm9tXG5cdC8vIGFuZCBsb2NrIHRoZSB2aWV3IG9wZW4gdW50aWwgaXQgaXMgc2F2ZWQuXG5cdGFkZE9uZTogZnVuY3Rpb24oIHJlYWN0aW9uICkge1xuXG5cdFx0dmFyIHZpZXcgPSBuZXcgUmVhY3Rpb25WaWV3KCB7IG1vZGVsOiByZWFjdGlvbiB9ICksXG5cdFx0XHRlbGVtZW50ID0gdmlldy5yZW5kZXIoKS5lbDtcblxuXHRcdHZhciBpc05ldyA9ICcnID09PSByZWFjdGlvbi5nZXQoICdkZXNjcmlwdGlvbicgKTtcblxuXHRcdGlmICggaXNOZXcgKSB7XG5cdFx0XHR2aWV3LmVkaXQoKTtcblx0XHRcdHZpZXcubG9ja09wZW4oKTtcblx0XHRcdHZpZXcuJGVsLmFkZENsYXNzKCAnbmV3JyApO1xuXHRcdH1cblxuXHRcdC8vIEFwcGVuZCB0aGUgZWxlbWVudCB0byB0aGUgZ3JvdXAuXG5cdFx0dGhpcy4kcmVhY3Rpb25Hcm91cC5hcHBlbmQoIGVsZW1lbnQgKTtcblxuXHRcdGlmICggaXNOZXcgKSB7XG5cdFx0XHR2aWV3LiRmaWVsZHMuZmluZCggJzppbnB1dDp2aXNpYmxlJyApLmZpcnN0KCkuZm9jdXMoKTtcblx0XHR9XG5cdH0sXG5cblx0Ly8gQWRkIGFsbCBpdGVtcyBpbiB0aGUgKipSZWFjdGlvbnMqKiBjb2xsZWN0aW9uIGF0IG9uY2UuXG5cdGFkZEFsbDogZnVuY3Rpb24oKSB7XG5cdFx0dGhpcy5tb2RlbC5lYWNoKCB0aGlzLmFkZE9uZSwgdGhpcyApO1xuXG5cdFx0dGhpcy4kKCAnLnNwaW5uZXItb3ZlcmxheScgKS5mYWRlT3V0KCk7XG5cdH0sXG5cblx0Z2V0UmVhY3Rpb25EZWZhdWx0czogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGRlZmF1bHRzID0ge307XG5cblx0XHRpZiAoIHRoaXMuJGV2ZW50cy5sZW5ndGggIT09IDAgKSB7XG5cblx0XHRcdC8vIEZpcnN0LCBiZSBzdXJlIHRoYXQgYW4gZXZlbnQgd2FzIHNlbGVjdGVkLlxuXHRcdFx0dmFyIGV2ZW50ID0gdGhpcy4kZXZlbnRzLnZhbCgpO1xuXG5cdFx0XHRpZiAoICcwJyA9PT0gZXZlbnQgKSB7XG5cdFx0XHRcdC8vIFNob3cgYW4gZXJyb3IuXG5cdFx0XHR9XG5cblx0XHRcdGRlZmF1bHRzLmV2ZW50ID0gZXZlbnQ7XG5cdFx0XHRkZWZhdWx0cy5ub25jZSA9IHRoaXMuJGV2ZW50c1xuXHRcdFx0XHQuZmluZChcblx0XHRcdFx0XHQnb3B0aW9uW3ZhbHVlPVwiJyArIGV2ZW50LnJlcGxhY2UoIC9bXmEtejAtOS1fXS9naSwgJycgKSArICdcIl0nXG5cdFx0XHRcdClcblx0XHRcdFx0LmRhdGEoICdub25jZScgKTtcblxuXHRcdH0gZWxzZSB7XG5cblx0XHRcdGRlZmF1bHRzLmV2ZW50ID0gdGhpcy4kcmVhY3Rpb25Hcm91cC5kYXRhKCAnd29yZHBvaW50cy1ob29rcy1ob29rLWV2ZW50JyApO1xuXHRcdFx0ZGVmYXVsdHMubm9uY2UgPSB0aGlzLiRyZWFjdGlvbkdyb3VwLmRhdGEoICd3b3JkcG9pbnRzLWhvb2tzLWNyZWF0ZS1ub25jZScgKTtcblx0XHR9XG5cblx0XHRkZWZhdWx0cy5yZWFjdG9yID0gdGhpcy4kcmVhY3Rpb25Hcm91cC5kYXRhKCAnd29yZHBvaW50cy1ob29rcy1yZWFjdG9yJyApO1xuXHRcdGRlZmF1bHRzLnJlYWN0aW9uX3N0b3JlID0gdGhpcy4kcmVhY3Rpb25Hcm91cC5kYXRhKCAnd29yZHBvaW50cy1ob29rcy1yZWFjdGlvbi1zdG9yZScgKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ2hvb2stcmVhY3Rpb24tZGVmYXVsdHMnLCBkZWZhdWx0cywgdGhpcyApO1xuXG5cdFx0cmV0dXJuIGRlZmF1bHRzO1xuXHR9LFxuXG5cdC8vIFNob3cgdGhlIGZvcm0gZm9yIGEgbmV3IHJlYWN0aW9uLlxuXHRpbml0QWRkUmVhY3Rpb246IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBkYXRhID0gdGhpcy5nZXRSZWFjdGlvbkRlZmF1bHRzKCk7XG5cblx0XHR0aGlzLiRhZGRSZWFjdGlvbi5wcm9wKCAnZGlzYWJsZWQnLCB0cnVlICk7XG5cblx0XHR2YXIgcmVhY3Rpb24gPSBuZXcgUmVhY3Rpb24oIGRhdGEgKTtcblxuXHRcdHRoaXMubW9kZWwuYWRkKCBbIHJlYWN0aW9uIF0gKTtcblxuXHRcdC8vIFJlLWVuYWJsZSB0aGUgc3VibWl0IGJ1dHRvbiB3aGVuIGEgbmV3IHJlYWN0aW9uIGlzIHNhdmVkLlxuXHRcdHRoaXMubGlzdGVuVG9PbmNlKCByZWFjdGlvbiwgJ3N5bmMnLCBmdW5jdGlvbiAoKSB7XG5cdFx0XHR0aGlzLiRhZGRSZWFjdGlvbi5wcm9wKCAnZGlzYWJsZWQnLCBmYWxzZSApO1xuXHRcdH0pO1xuXHR9LFxuXG5cdC8vIFdoZW4gYSBuZXcgcmVhY3Rpb24gaXMgcmVtb3ZlZCwgcmUtZW5hYmxlIHRoZSBhZGQgcmVhY3Rpb24gYnV0dG9uLlxuXHRjYW5jZWxBZGRSZWFjdGlvbjogZnVuY3Rpb24gKCkge1xuXHRcdHRoaXMuJGFkZFJlYWN0aW9uLnByb3AoICdkaXNhYmxlZCcsIGZhbHNlICk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IFJlYWN0aW9ucztcbiJdfQ==
