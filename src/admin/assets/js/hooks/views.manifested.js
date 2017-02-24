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

		// Set unchecked checkboxes' values to false, so that they will override the
		// current value when merged.
		$form.find( 'input[type=checkbox]' ).each( function ( i, el ) {

			if ( typeof formObj[ el.name ] === 'undefined' ) {
				formObj[ el.name ] = false;
			}
		});

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

var DataTypes = new Backbone.Collection( [], { model: DataType });

DataTypes.add( { slug: 'text' } );
DataTypes.add( { slug: 'integer', inputType: 'number' } );
DataTypes.add( { slug: 'decimal_number', inputType: 'number' } );

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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9jb250cm9sbGVycy9hcmdzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvY29udHJvbGxlcnMvZXh0ZW5zaW9uLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvY29udHJvbGxlcnMvZXh0ZW5zaW9ucy5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2NvbnRyb2xsZXJzL2ZpZWxkcy5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2NvbnRyb2xsZXJzL3JlYWN0b3IuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9jb250cm9sbGVycy9yZWFjdG9ycy5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL3ZpZXdzLm1hbmlmZXN0LmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3Mvdmlld3MvYXJnLWhpZXJhcmNoeS1zZWxlY3Rvci5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL3ZpZXdzL2FyZy1zZWxlY3Rvci5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL3ZpZXdzL2FyZy1zZWxlY3RvcnMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy92aWV3cy9iYXNlLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3Mvdmlld3MvcmVhY3Rpb24uanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy92aWV3cy9yZWFjdGlvbnMuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN2ZEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN0RUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNiQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcFRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25EQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNmQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDeERBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2hHQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2hFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25LQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM3Q0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcGRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQXJnc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQXJnc0NvbGxlY3Rpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkFyZ3MsXG5cdGwxMG4gPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcubDEwbixcblx0QXJncztcblxuQXJncyA9IEJhY2tib25lLk1vZGVsLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IHtcblx0XHRldmVudHM6IHt9LFxuXHRcdGVudGl0aWVzOiB7fVxuXHR9LFxuXG5cdGdldEV2ZW50QXJnOiBmdW5jdGlvbiAoIGV2ZW50U2x1Zywgc2x1ZyApIHtcblxuXHRcdHZhciBldmVudCA9IHRoaXMuZ2V0KCAnZXZlbnRzJyApWyBldmVudFNsdWcgXTtcblxuXHRcdGlmICggISBldmVudCB8fCAhIGV2ZW50LmFyZ3MgfHwgISBldmVudC5hcmdzWyBzbHVnIF0gKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0dmFyIGVudGl0eSA9IHRoaXMuZ2V0RW50aXR5KCBzbHVnICk7XG5cblx0XHRfLmV4dGVuZCggZW50aXR5LmF0dHJpYnV0ZXMsIGV2ZW50LmFyZ3NbIHNsdWcgXSApO1xuXG5cdFx0cmV0dXJuIGVudGl0eTtcblx0fSxcblxuXHRnZXRFdmVudEFyZ3M6IGZ1bmN0aW9uICggZXZlbnRTbHVnICkge1xuXG5cdFx0dmFyIGFyZ3NDb2xsZWN0aW9uID0gbmV3IEFyZ3NDb2xsZWN0aW9uKCksXG5cdFx0XHRldmVudCA9IHRoaXMuZ2V0KCAnZXZlbnRzJyApWyBldmVudFNsdWcgXTtcblxuXHRcdGlmICggdHlwZW9mIGV2ZW50ID09PSAndW5kZWZpbmVkJyB8fCB0eXBlb2YgZXZlbnQuYXJncyA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRyZXR1cm4gYXJnc0NvbGxlY3Rpb247XG5cdFx0fVxuXG5cdFx0Xy5lYWNoKCBldmVudC5hcmdzLCBmdW5jdGlvbiAoIGFyZyApIHtcblxuXHRcdFx0dmFyIGVudGl0eSA9IHRoaXMuZ2V0RW50aXR5KCBhcmcuc2x1ZyApO1xuXG5cdFx0XHRpZiAoICEgZW50aXR5ICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdF8uZXh0ZW5kKCBlbnRpdHkuYXR0cmlidXRlcywgYXJnICk7XG5cblx0XHRcdGFyZ3NDb2xsZWN0aW9uLmFkZCggZW50aXR5ICk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHRyZXR1cm4gYXJnc0NvbGxlY3Rpb247XG5cdH0sXG5cblx0aXNFdmVudFJlcGVhdGFibGU6IGZ1bmN0aW9uICggc2x1ZyApIHtcblxuXHRcdHZhciBhcmdzID0gdGhpcy5nZXRFdmVudEFyZ3MoIHNsdWcgKTtcblxuXHRcdHJldHVybiBfLmlzRW1wdHkoIGFyZ3Mud2hlcmUoIHsgaXNfc3RhdGVmdWw6IGZhbHNlIH0gKSApO1xuXHR9LFxuXG5cdHBhcnNlQXJnU2x1ZzogZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0dmFyIGlzQXJyYXkgPSBmYWxzZSxcblx0XHRcdGlzQWxpYXMgPSBmYWxzZTtcblxuXHRcdGlmICggJ3t9JyA9PT0gc2x1Zy5zbGljZSggLTIgKSApIHtcblx0XHRcdGlzQXJyYXkgPSB0cnVlO1xuXHRcdFx0c2x1ZyA9IHNsdWcuc2xpY2UoIDAsIC0yICk7XG5cdFx0fVxuXG5cdFx0dmFyIHBhcnRzID0gc2x1Zy5zcGxpdCggJzonLCAyICk7XG5cblx0XHRpZiAoIHBhcnRzWzFdICkge1xuXHRcdFx0aXNBbGlhcyA9IHBhcnRzWzBdO1xuXHRcdFx0c2x1ZyA9IHBhcnRzWzFdO1xuXHRcdH1cblxuXHRcdHJldHVybiB7IHNsdWc6IHNsdWcsIGlzQXJyYXk6IGlzQXJyYXksIGlzQWxpYXM6IGlzQWxpYXMgfTtcblx0fSxcblxuXHRfZ2V0RW50aXR5RGF0YTogZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0dmFyIHBhcnNlZCA9IHRoaXMucGFyc2VBcmdTbHVnKCBzbHVnICksXG5cdFx0XHRlbnRpdHkgPSB0aGlzLmdldCggJ2VudGl0aWVzJyApWyBwYXJzZWQuc2x1ZyBdO1xuXG5cdFx0aWYgKCAhIGVudGl0eSApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHRlbnRpdHkgPSBfLmV4dGVuZCgge30sIGVudGl0eSwgeyBzbHVnOiBzbHVnLCBfY2Fub25pY2FsOiBwYXJzZWQuc2x1ZyB9ICk7XG5cblx0XHRyZXR1cm4gZW50aXR5O1xuXHR9LFxuXG5cdGdldEVudGl0eTogZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0aWYgKCBzbHVnIGluc3RhbmNlb2YgRW50aXR5ICkge1xuXHRcdFx0cmV0dXJuIHNsdWc7XG5cdFx0fVxuXG5cdFx0dmFyIGVudGl0eSA9IHRoaXMuX2dldEVudGl0eURhdGEoIHNsdWcgKTtcblxuXHRcdGlmICggISBlbnRpdHkgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0ZW50aXR5ID0gbmV3IEVudGl0eSggZW50aXR5ICk7XG5cblx0XHRyZXR1cm4gZW50aXR5O1xuXHR9LFxuXG5cdGdldENoaWxkcmVuOiBmdW5jdGlvbiAoIHNsdWcgKSB7XG5cblx0XHR2YXIgZW50aXR5ID0gdGhpcy5fZ2V0RW50aXR5RGF0YSggc2x1ZyApO1xuXG5cdFx0aWYgKCAhIGVudGl0eSApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHR2YXIgY2hpbGRyZW4gPSBuZXcgQXJnc0NvbGxlY3Rpb24oKTtcblxuXHRcdF8uZWFjaCggZW50aXR5LmNoaWxkcmVuLCBmdW5jdGlvbiAoIGNoaWxkICkge1xuXG5cdFx0XHR2YXIgYXJnVHlwZSA9IEFyZ3MudHlwZVsgY2hpbGQuX3R5cGUgXTtcblxuXHRcdFx0aWYgKCAhIGFyZ1R5cGUgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y2hpbGRyZW4uYWRkKCBuZXcgYXJnVHlwZSggY2hpbGQgKSApO1xuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0cmV0dXJuIGNoaWxkcmVuO1xuXHR9LFxuXG5cdGdldENoaWxkOiBmdW5jdGlvbiAoIGVudGl0eVNsdWcsIGNoaWxkU2x1ZyApIHtcblxuXHRcdHZhciBlbnRpdHkgPSB0aGlzLl9nZXRFbnRpdHlEYXRhKCBlbnRpdHlTbHVnICk7XG5cblx0XHRpZiAoICEgZW50aXR5ICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHZhciBjaGlsZCA9IGVudGl0eS5jaGlsZHJlblsgY2hpbGRTbHVnIF07XG5cblx0XHRpZiAoICEgY2hpbGQgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0dmFyIGFyZ1R5cGUgPSBBcmdzLnR5cGVbIGNoaWxkLl90eXBlIF07XG5cblx0XHRpZiAoICEgYXJnVHlwZSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRyZXR1cm4gbmV3IGFyZ1R5cGUoIGNoaWxkICk7XG5cdH0sXG5cblx0LyoqXG5cdCAqXG5cdCAqIEBwYXJhbSBoaWVyYXJjaHlcblx0ICogQHBhcmFtIGV2ZW50U2x1ZyBPcHRpb25hbCBldmVudCBmb3IgY29udGV4dC5cblx0ICogQHJldHVybnMgeyp9XG5cdCAqL1xuXHRnZXRBcmdzRnJvbUhpZXJhcmNoeTogZnVuY3Rpb24gKCBoaWVyYXJjaHksIGV2ZW50U2x1ZyApIHtcblxuXHRcdHZhciBhcmdzID0gW10sIHBhcmVudCwgYXJnLCBzbHVnO1xuXG5cdFx0Zm9yICggdmFyIGkgPSAwOyBpIDwgaGllcmFyY2h5Lmxlbmd0aDsgaSsrICkge1xuXG5cdFx0XHRzbHVnID0gaGllcmFyY2h5WyBpIF07XG5cblx0XHRcdGlmICggcGFyZW50ICkge1xuXHRcdFx0XHRpZiAoICEgcGFyZW50LmdldENoaWxkICkge1xuXHRcdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGFyZyA9IHBhcmVudC5nZXRDaGlsZCggc2x1ZyApO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0aWYgKCBldmVudFNsdWcgJiYgdGhpcy5wYXJzZUFyZ1NsdWcoIHNsdWcgKS5pc0FsaWFzICkge1xuXHRcdFx0XHRcdGFyZyA9IHRoaXMuZ2V0RXZlbnRBcmcoIGV2ZW50U2x1Zywgc2x1ZyApO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdGFyZyA9IHRoaXMuZ2V0RW50aXR5KCBzbHVnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblxuXHRcdFx0aWYgKCAhIGFyZyApIHtcblx0XHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdFx0fVxuXG5cdFx0XHRwYXJlbnQgPSBhcmc7XG5cblx0XHRcdGFyZ3MucHVzaCggYXJnICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGFyZ3M7XG5cdH0sXG5cblx0Z2V0SGllcmFyY2hpZXNNYXRjaGluZzogZnVuY3Rpb24gKCBvcHRpb25zICkge1xuXG5cdFx0dmFyIGFyZ3MgPSBbXSwgaGllcmFyY2hpZXMgPSBbXSwgaGllcmFyY2h5ID0gW107XG5cblx0XHRvcHRpb25zID0gXy5leHRlbmQoIHt9LCBvcHRpb25zICk7XG5cblx0XHRpZiAoIG9wdGlvbnMuZXZlbnQgKSB7XG5cdFx0XHRvcHRpb25zLnRvcCA9IHRoaXMuZ2V0RXZlbnRBcmdzKCBvcHRpb25zLmV2ZW50ICkubW9kZWxzO1xuXHRcdH1cblxuXHRcdGlmICggb3B0aW9ucy50b3AgKSB7XG5cdFx0XHRhcmdzID0gXy5pc0FycmF5KCBvcHRpb25zLnRvcCApID8gb3B0aW9ucy50b3AgOiBbIG9wdGlvbnMudG9wIF07XG5cdFx0fSBlbHNlIHtcblx0XHRcdGFyZ3MgPSBfLmtleXMoIHRoaXMuZ2V0KCAnZW50aXRpZXMnICkgKTtcblx0XHR9XG5cblx0XHR2YXIgbWF0Y2hlciA9IHRoaXMuX2hpZXJhcmNoeU1hdGNoZXIoIG9wdGlvbnMsIGhpZXJhcmNoeSwgaGllcmFyY2hpZXMgKTtcblxuXHRcdGlmICggISBtYXRjaGVyICkge1xuXHRcdFx0cmV0dXJuIGhpZXJhcmNoaWVzO1xuXHRcdH1cblxuXHRcdF8uZWFjaCggYXJncywgZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0XHR2YXIgYXJnID0gdGhpcy5nZXRFbnRpdHkoIHNsdWcgKTtcblxuXHRcdFx0dGhpcy5fZ2V0SGllcmFyY2hpZXNNYXRjaGluZyhcblx0XHRcdFx0YXJnXG5cdFx0XHRcdCwgaGllcmFyY2h5XG5cdFx0XHRcdCwgaGllcmFyY2hpZXNcblx0XHRcdFx0LCBtYXRjaGVyXG5cdFx0XHQpO1xuXG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHRyZXR1cm4gaGllcmFyY2hpZXM7XG5cdH0sXG5cblx0X2hpZXJhcmNoeU1hdGNoZXI6IGZ1bmN0aW9uICggb3B0aW9ucywgaGllcmFyY2h5LCBoaWVyYXJjaGllcyApIHtcblxuXHRcdHZhciBmaWx0ZXJzID0gW10sIGk7XG5cblx0XHRpZiAoIG9wdGlvbnMuZW5kICkge1xuXHRcdFx0ZmlsdGVycy5wdXNoKCB7XG5cdFx0XHRcdG1ldGhvZDogXy5pc0Z1bmN0aW9uKCBvcHRpb25zLmVuZCApID8gJ2ZpbHRlcicgOiAnd2hlcmUnLFxuXHRcdFx0XHRhcmc6IG9wdGlvbnMuZW5kXG5cdFx0XHR9KTtcblx0XHR9XG5cblx0XHRpZiAoICEgZmlsdGVycyApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHRyZXR1cm4gZnVuY3Rpb24gKCBzdWJBcmdzLCBoaWVyYWNoeSApIHtcblxuXHRcdFx0dmFyIG1hdGNoaW5nID0gW10sIG1hdGNoZXM7XG5cblx0XHRcdGlmICggc3ViQXJncyBpbnN0YW5jZW9mIEJhY2tib25lLkNvbGxlY3Rpb24gKSB7XG5cdFx0XHRcdHN1YkFyZ3MgPSBzdWJBcmdzLm1vZGVscztcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHN1YkFyZ3MgPSBfLmNsb25lKCBzdWJBcmdzICk7XG5cdFx0XHR9XG5cblx0XHRcdF8uZWFjaCggc3ViQXJncywgZnVuY3Rpb24gKCBtYXRjaCApIHtcblx0XHRcdFx0bWF0Y2guaGllcmFjaHkgPSBoaWVyYWNoeTtcblx0XHRcdFx0bWF0Y2hpbmcucHVzaCggbWF0Y2ggKTtcblx0XHRcdH0pO1xuXG5cdFx0XHRtYXRjaGluZyA9IG5ldyBBcmdzQ29sbGVjdGlvbiggbWF0Y2hpbmcgKTtcblxuXHRcdFx0Zm9yICggaSA9IDA7IGkgPCBmaWx0ZXJzLmxlbmd0aDsgaSsrICkge1xuXG5cdFx0XHRcdG1hdGNoZXMgPSBtYXRjaGluZ1sgZmlsdGVyc1sgaSBdLm1ldGhvZCBdKCBmaWx0ZXJzWyBpIF0uYXJnICk7XG5cblx0XHRcdFx0aWYgKCBfLmlzRW1wdHkoIG1hdGNoZXMgKSApIHtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRtYXRjaGluZy5yZXNldCggbWF0Y2hlcyApO1xuXHRcdFx0fVxuXG5cdFx0XHRtYXRjaGluZy5lYWNoKCBmdW5jdGlvbiAoIG1hdGNoICkge1xuXHRcdFx0XHRoaWVyYXJjaHkucHVzaCggbWF0Y2ggKTtcblx0XHRcdFx0aGllcmFyY2hpZXMucHVzaCggXy5jbG9uZSggaGllcmFyY2h5ICkgKTtcblx0XHRcdFx0aGllcmFyY2h5LnBvcCgpO1xuXHRcdFx0fSk7XG5cdFx0fTtcblx0fSxcblxuXHRfZ2V0SGllcmFyY2hpZXNNYXRjaGluZzogZnVuY3Rpb24gKCBhcmcsIGhpZXJhcmNoeSwgaGllcmFyY2hpZXMsIGFkZE1hdGNoaW5nICkge1xuXG5cdFx0dmFyIHN1YkFyZ3M7XG5cblx0XHQvLyBDaGVjayB0aGUgdG9wLWxldmVsIGFyZ3MgYXMgd2VsbC5cblx0XHRpZiAoIGhpZXJhcmNoeS5sZW5ndGggPT09IDAgKSB7XG5cdFx0XHRhZGRNYXRjaGluZyggWyBhcmcgXSwgaGllcmFyY2h5ICk7XG5cdFx0fVxuXG5cdFx0aWYgKCBhcmcgaW5zdGFuY2VvZiBQYXJlbnQgKSB7XG5cdFx0XHRzdWJBcmdzID0gYXJnLmdldENoaWxkcmVuKCk7XG5cdFx0fVxuXG5cdFx0aWYgKCAhIHN1YkFyZ3MgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gSWYgdGhpcyBpcyBhbiBlbnRpdHksIGNoZWNrIGlmIHRoYXQgZW50aXR5IGlzIGFscmVhZHkgaW4gdGhlXG5cdFx0Ly8gaGllcmFyY2h5LCBhbmQgZG9uJ3QgYWRkIGl0IGFnYWluLCB0byBwcmV2ZW50IGluZmluaXRlIGxvb3BzLlxuXHRcdGlmICggaGllcmFyY2h5Lmxlbmd0aCAlIDIgPT09IDAgKSB7XG5cdFx0XHR2YXIgbG9vcHMgPSBfLmZpbHRlciggaGllcmFyY2h5LCBmdW5jdGlvbiAoIGl0ZW0gKSB7XG5cdFx0XHRcdHJldHVybiBpdGVtLmdldCggJ3NsdWcnICkgPT09IGFyZy5nZXQoICdzbHVnJyApO1xuXHRcdFx0fSk7XG5cblx0XHRcdC8vIFdlIGFsbG93IGl0IHRvIGxvb3AgdHdpY2UsIGJ1dCBub3QgdG8gYWRkIHRoZSBlbnRpdHkgYSB0aGlyZCB0aW1lLlxuXHRcdFx0aWYgKCBsb29wcy5sZW5ndGggPiAxICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0aGllcmFyY2h5LnB1c2goIGFyZyApO1xuXG5cdFx0YWRkTWF0Y2hpbmcoIHN1YkFyZ3MsIGhpZXJhcmNoeSApO1xuXG5cdFx0c3ViQXJncy5lYWNoKCBmdW5jdGlvbiAoIHN1YkFyZyApIHtcblxuXHRcdFx0dGhpcy5fZ2V0SGllcmFyY2hpZXNNYXRjaGluZyhcblx0XHRcdFx0c3ViQXJnXG5cdFx0XHRcdCwgaGllcmFyY2h5XG5cdFx0XHRcdCwgaGllcmFyY2hpZXNcblx0XHRcdFx0LCBhZGRNYXRjaGluZ1xuXHRcdFx0KTtcblxuXHRcdH0sIHRoaXMgKTtcblxuXHRcdGhpZXJhcmNoeS5wb3AoKTtcblx0fSxcblxuXHRidWlsZEhpZXJhcmNoeUh1bWFuSWQ6IGZ1bmN0aW9uICggaGllcmFyY2h5ICkge1xuXG5cdFx0dmFyIGh1bWFuSWQgPSAnJztcblxuXHRcdF8uZWFjaCggaGllcmFyY2h5LCBmdW5jdGlvbiAoIGFyZykge1xuXG5cdFx0XHRpZiAoICEgYXJnICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHZhciB0aXRsZSA9IGFyZy5nZXQoICd0aXRsZScgKTtcblxuXHRcdFx0aWYgKCAhIHRpdGxlICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGlmICggJycgIT09IGh1bWFuSWQgKSB7XG5cdFx0XHRcdC8vIFdlIGNvbXByZXNzIHJlbGF0aW9uc2hpcHMuXG5cdFx0XHRcdGlmICggYXJnIGluc3RhbmNlb2YgRW50aXR5ICkge1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGh1bWFuSWQgKz0gbDEwbi5zZXBhcmF0b3I7XG5cdFx0XHR9XG5cblx0XHRcdGh1bWFuSWQgKz0gdGl0bGU7XG5cdFx0fSk7XG5cblx0XHRyZXR1cm4gaHVtYW5JZDtcblx0fVxuXG59LCB7IHR5cGU6IHt9IH0pO1xuXG52YXIgQXJnID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKHtcblxuXHR0eXBlOiAnYXJnJyxcblxuXHRpZEF0dHJpYnV0ZTogJ3NsdWcnLFxuXG5cdGRlZmF1bHRzOiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIHsgX3R5cGU6IHRoaXMudHlwZSB9O1xuXHR9XG59KTtcblxudmFyIFBhcmVudCA9IEFyZy5leHRlbmQoe1xuXG5cdC8qKlxuXHQgKiBAYWJzdHJhY3Rcblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHNsdWcgVGhlIGNoaWxkIHNsdWcuXG5cdCAqL1xuXHRnZXRDaGlsZDogZnVuY3Rpb24gKCkge30sXG5cblx0LyoqXG5cdCAqIEBhYnN0cmFjdFxuXHQgKi9cblx0Z2V0Q2hpbGRyZW46IGZ1bmN0aW9uICgpIHt9XG59KTtcblxudmFyIEVudGl0eSA9IFBhcmVudC5leHRlbmQoe1xuXHR0eXBlOiAnZW50aXR5JyxcblxuXHRnZXRDaGlsZDogZnVuY3Rpb24gKCBzbHVnICkge1xuXHRcdHJldHVybiB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MuZ2V0Q2hpbGQoIHRoaXMuZ2V0KCAnc2x1ZycgKSwgc2x1ZyApO1xuXHR9LFxuXG5cdGdldENoaWxkcmVuOiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIHdwLndvcmRwb2ludHMuaG9va3MuQXJncy5nZXRDaGlsZHJlbiggdGhpcy5nZXQoICdzbHVnJyApICk7XG5cdH1cbn0pO1xuXG52YXIgUmVsYXRpb25zaGlwID0gUGFyZW50LmV4dGVuZCh7XG5cdHR5cGU6ICdyZWxhdGlvbnNoaXAnLFxuXG5cdHBhcnNlQXJnU2x1ZzogZnVuY3Rpb24gKCBzbHVnICkge1xuXG5cdFx0dmFyIGlzQXJyYXkgPSBmYWxzZTtcblxuXHRcdGlmICggJ3t9JyA9PT0gc2x1Zy5zbGljZSggLTIgKSApIHtcblx0XHRcdGlzQXJyYXkgPSB0cnVlO1xuXHRcdFx0c2x1ZyA9IHNsdWcuc2xpY2UoIDAsIC0yICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHsgaXNBcnJheTogaXNBcnJheSwgc2x1Zzogc2x1ZyB9O1xuXHR9LFxuXG5cdGdldENoaWxkOiBmdW5jdGlvbiAoIHNsdWcgKSB7XG5cblx0XHR2YXIgY2hpbGQ7XG5cblx0XHRpZiAoIHNsdWcgIT09IHRoaXMuZ2V0KCAnc2Vjb25kYXJ5JyApICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHZhciBwYXJzZWQgPSB0aGlzLnBhcnNlQXJnU2x1Zyggc2x1ZyApO1xuXG5cdFx0aWYgKCBwYXJzZWQuaXNBcnJheSApIHtcblx0XHRcdGNoaWxkID0gbmV3IEVudGl0eUFycmF5KHsgZW50aXR5X3NsdWc6IHBhcnNlZC5zbHVnIH0pO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRjaGlsZCA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncy5nZXRFbnRpdHkoIHBhcnNlZC5zbHVnICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGNoaWxkO1xuXHR9LFxuXG5cdGdldENoaWxkcmVuOiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIG5ldyBBcmdzQ29sbGVjdGlvbiggWyB0aGlzLmdldENoaWxkKCB0aGlzLmdldCggJ3NlY29uZGFyeScgKSApIF0gKTtcblx0fVxufSk7XG5cbnZhciBFbnRpdHlBcnJheSA9IEFyZy5leHRlbmQoIHtcblx0dHlwZTogJ2FycmF5JyxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cdFx0dGhpcy5zZXQoICdzbHVnJywgdGhpcy5nZXQoICdlbnRpdHlfc2x1ZycgKSArICd7fScgKTtcblx0fVxufSk7XG5cbnZhciBBdHRyID0gQXJnLmV4dGVuZCgge1xuXHR0eXBlOiAnYXR0cidcbn0pO1xuXG5BcmdzLnR5cGUuZW50aXR5ID0gRW50aXR5O1xuQXJncy50eXBlLnJlbGF0aW9uc2hpcCA9IFJlbGF0aW9uc2hpcDtcbkFyZ3MudHlwZS5hcnJheSA9IEVudGl0eUFycmF5O1xuQXJncy50eXBlLmF0dHIgPSBBdHRyO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEFyZ3M7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb25cbiAqXG4gKiBAc2luY2UgMi4xLjBcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICpcbiAqXG4gKi9cbnZhciBob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3MsXG5cdGV4dGVuc2lvbnMgPSBob29rcy52aWV3LmRhdGEuZXh0ZW5zaW9ucyxcblx0ZXh0ZW5kID0gaG9va3MudXRpbC5leHRlbmQsXG5cdGVtcHR5RnVuY3Rpb24gPSBob29rcy51dGlsLmVtcHR5RnVuY3Rpb24sXG5cdEV4dGVuc2lvbjtcblxuRXh0ZW5zaW9uID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKHtcblxuXHQvKipcblx0ICogQHNpbmNlIDIuMS4wXG5cdCAqL1xuXHRpZEF0dHJpYnV0ZTogJ3NsdWcnLFxuXG5cdC8qKlxuXHQgKiBAc2luY2UgMi4xLjBcblx0ICovXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oIGhvb2tzLCAncmVhY3Rpb246dmlldzppbml0JywgdGhpcy5pbml0UmVhY3Rpb24gKTtcblx0XHR0aGlzLmxpc3RlblRvKCBob29rcywgJ3JlYWN0aW9uOm1vZGVsOnZhbGlkYXRlJywgdGhpcy52YWxpZGF0ZVJlYWN0aW9uICk7XG5cblx0XHR0aGlzLmRhdGEgPSBleHRlbnNpb25zWyB0aGlzLmlkIF07XG5cblx0XHR0aGlzLl9fY2hpbGRfXy5pbml0aWFsaXplLmFwcGx5KCB0aGlzLCBhcmd1bWVudHMgKTtcblx0fSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgSW5pdGlhbGl6ZXMgYSByZWFjdGlvbi5cblx0ICogXG5cdCAqIFRoaXMgaXMgY2FsbGVkIHdoZW4gYSByZWFjdGlvbiB2aWV3IGlzIGluaXRpYWxpemVkLlxuXHQgKiBcblx0ICogQHNpbmNlIDIuMS4wXG5cdCAqIFxuXHQgKiBAYWJzdHJhY3Rcblx0ICogXG5cdCAqIEBwYXJhbSB7d3Aud29yZHBvaW50cy5ob29rcy52aWV3LlJlYWN0aW9ufSByZWFjdGlvbiBUaGUgcmVhY3Rpb24gYmVpbmdcblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGluaXRpYWxpemVkLlxuXHQgKi9cblx0aW5pdFJlYWN0aW9uOiBlbXB0eUZ1bmN0aW9uKCAnaW5pdFJlYWN0aW9uJyApLFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBWYWxpZGF0ZXMgYSByZWFjdGlvbidzIHNldHRpbmdzLlxuXHQgKiBcblx0ICogVGhpcyBpcyBjYWxsZWQgYmVmb3JlIGEgcmVhY3Rpb24gbW9kZWwgaXMgc2F2ZWQuXG5cdCAqIFxuXHQgKiBAc2luY2UgMi4xLjBcblx0ICogXG5cdCAqIEBhYnN0cmFjdFxuXHQgKiBcblx0ICogQHBhcmFtIHtSZWFjdGlvbn0gbW9kZWwgICAgICBUaGUgcmVhY3Rpb24gbW9kZWwuXG5cdCAqIEBwYXJhbSB7YXJyYXl9ICAgIGF0dHJpYnV0ZXMgVGhlIG1vZGVsJ3MgYXR0cmlidXRlcyAodGhlIHNldHRpbmdzIGJlaW5nXG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFsaWRhdGVkKS5cblx0ICogQHBhcmFtIHthcnJheX0gICAgZXJyb3JzICAgICBBbnkgZXJyb3JzIHRoYXQgd2VyZSBlbmNvdW50ZXJlZC5cblx0ICogQHBhcmFtIHthcnJheX0gICAgb3B0aW9ucyAgICBPcHRpb25zLlxuXHQgKi9cblx0dmFsaWRhdGVSZWFjdGlvbjogZW1wdHlGdW5jdGlvbiggJ3ZhbGlkYXRlUmVhY3Rpb24nIClcblxufSwgeyBleHRlbmQ6IGV4dGVuZCB9ICk7XG5cbm1vZHVsZS5leHBvcnRzID0gRXh0ZW5zaW9uO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLkNvbGxlY3Rpb25cbiAqL1xudmFyIEV4dGVuc2lvbiA9IHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb24sXG5cdEV4dGVuc2lvbnM7XG5cbkV4dGVuc2lvbnMgPSBCYWNrYm9uZS5Db2xsZWN0aW9uLmV4dGVuZCh7XG5cdG1vZGVsOiBFeHRlbnNpb25cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEV4dGVuc2lvbnM7IiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRmllbGRzXG4gKlxuICogQHNpbmNlIDIuMS4wXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqL1xudmFyICQgPSBCYWNrYm9uZS4kLFxuXHRob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3MsXG5cdGwxMG4gPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcubDEwbixcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHR0ZXh0VGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRleHRUZW1wbGF0ZSxcblx0RmllbGRzO1xuXG5GaWVsZHMgPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0ZmllbGRzOiB7fVxuXHR9LFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stcmVhY3Rpb24tZmllbGQnICksXG5cdHRlbXBsYXRlSGlkZGVuOiB0ZW1wbGF0ZSggJ2hvb2stcmVhY3Rpb24taGlkZGVuLWZpZWxkJyApLFxuXHR0ZW1wbGF0ZVNlbGVjdDogdGVtcGxhdGUoICdob29rLXJlYWN0aW9uLXNlbGVjdC1maWVsZCcgKSxcblxuXHRlbXB0eU1lc3NhZ2U6IHRleHRUZW1wbGF0ZSggbDEwbi5lbXB0eUZpZWxkICksXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggaG9va3MsICdyZWFjdGlvbjptb2RlbDp2YWxpZGF0ZScsIHRoaXMudmFsaWRhdGVSZWFjdGlvbiApO1xuXHRcdHRoaXMubGlzdGVuVG8oIGhvb2tzLCAncmVhY3Rpb246dmlldzppbml0JywgdGhpcy5pbml0UmVhY3Rpb24gKTtcblxuXHRcdHRoaXMuYXR0cmlidXRlcy5maWVsZHMuZXZlbnQgPSB7XG5cdFx0XHR0eXBlOiAnaGlkZGVuJyxcblx0XHRcdHJlcXVpcmVkOiB0cnVlXG5cdFx0fTtcblx0fSxcblxuXHRjcmVhdGU6IGZ1bmN0aW9uICggbmFtZSwgdmFsdWUsIGRhdGEgKSB7XG5cblx0XHRpZiAoIHR5cGVvZiB2YWx1ZSA9PT0gJ3VuZGVmaW5lZCcgJiYgZGF0YVsnZGVmYXVsdCddICkge1xuXHRcdFx0dmFsdWUgPSBkYXRhWydkZWZhdWx0J107XG5cdFx0fVxuXG5cdFx0ZGF0YSA9IF8uZXh0ZW5kKFxuXHRcdFx0eyBuYW1lOiB0aGlzLmdldEZpZWxkTmFtZSggbmFtZSApLCB2YWx1ZTogdmFsdWUgfVxuXHRcdFx0LCBkYXRhXG5cdFx0KTtcblxuXHRcdHN3aXRjaCAoIGRhdGEudHlwZSApIHtcblx0XHRcdGNhc2UgJ3NlbGVjdCc6XG5cdFx0XHRcdHJldHVybiB0aGlzLmNyZWF0ZVNlbGVjdCggZGF0YSApO1xuXG5cdFx0XHRjYXNlICdoaWRkZW4nOlxuXHRcdFx0XHRyZXR1cm4gdGhpcy50ZW1wbGF0ZUhpZGRlbiggZGF0YSApO1xuXHRcdH1cblxuXHRcdHZhciBEYXRhVHlwZSA9IERhdGFUeXBlcy5nZXQoIGRhdGEudHlwZSApO1xuXG5cdFx0aWYgKCBEYXRhVHlwZSApIHtcblx0XHRcdHJldHVybiBEYXRhVHlwZS5jcmVhdGVGaWVsZCggZGF0YSApO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRyZXR1cm4gdGhpcy50ZW1wbGF0ZSggZGF0YSApO1xuXHRcdH1cblx0fSxcblxuXHRjcmVhdGVTZWxlY3Q6IGZ1bmN0aW9uICggZGF0YSwgdGVtcGxhdGUgKSB7XG5cblx0XHR2YXIgJHRlbXBsYXRlID0gJCggJzxkaXY+PC9kaXY+JyApLmh0bWwoIHRlbXBsYXRlIHx8IHRoaXMudGVtcGxhdGVTZWxlY3QoIGRhdGEgKSApLFxuXHRcdFx0b3B0aW9ucyA9ICcnLFxuXHRcdFx0Zm91bmRWYWx1ZSA9IHR5cGVvZiBkYXRhLnZhbHVlID09PSAndW5kZWZpbmVkJ1xuXHRcdFx0XHR8fCB0eXBlb2YgZGF0YS5vcHRpb25zWyBkYXRhLnZhbHVlIF0gIT09ICd1bmRlZmluZWQnO1xuXG5cdFx0aWYgKCAhICR0ZW1wbGF0ZSApIHtcblx0XHRcdCR0ZW1wbGF0ZSA9ICQoICc8ZGl2PjwvZGl2PicgKS5odG1sKCB0aGlzLnRlbXBsYXRlU2VsZWN0KCBkYXRhICkgKTtcblx0XHR9XG5cblx0XHRfLmVhY2goIGRhdGEub3B0aW9ucywgZnVuY3Rpb24gKCBvcHRpb24sIGluZGV4ICkge1xuXG5cdFx0XHR2YXIgdmFsdWUsIGxhYmVsO1xuXG5cdFx0XHRpZiAoIG9wdGlvbi52YWx1ZSApIHtcblx0XHRcdFx0dmFsdWUgPSBvcHRpb24udmFsdWU7XG5cdFx0XHRcdGxhYmVsID0gb3B0aW9uLmxhYmVsO1xuXG5cdFx0XHRcdGlmICggISBmb3VuZFZhbHVlICYmIGRhdGEudmFsdWUgPT09IHZhbHVlICkge1xuXHRcdFx0XHRcdGZvdW5kVmFsdWUgPSB0cnVlO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHR2YWx1ZSA9IGluZGV4O1xuXHRcdFx0XHRsYWJlbCA9IG9wdGlvbjtcblx0XHRcdH1cblxuXHRcdFx0b3B0aW9ucyArPSAkKCAnPG9wdGlvbj48L29wdGlvbj4nIClcblx0XHRcdFx0LmF0dHIoICd2YWx1ZScsIHZhbHVlIClcblx0XHRcdFx0LnRleHQoIGxhYmVsID8gbGFiZWwgOiB2YWx1ZSApXG5cdFx0XHRcdC5wcm9wKCAnb3V0ZXJIVE1MJyApO1xuXHRcdH0pO1xuXG5cdFx0Ly8gSWYgdGhlIGN1cnJlbnQgdmFsdWUgaXNuJ3QgaW4gdGhlIGxpc3QsIGFkZCBpdCBpbi5cblx0XHRpZiAoICEgZm91bmRWYWx1ZSApIHtcblx0XHRcdG9wdGlvbnMgKz0gJCggJzxvcHRpb24+PC9vcHRpb24+JyApXG5cdFx0XHRcdC5hdHRyKCAndmFsdWUnLCBkYXRhLnZhbHVlIClcblx0XHRcdFx0LnRleHQoIGRhdGEudmFsdWUgKVxuXHRcdFx0XHQucHJvcCggJ291dGVySFRNTCcgKTtcblx0XHR9XG5cblx0XHQkdGVtcGxhdGUuZmluZCggJ3NlbGVjdCcgKVxuXHRcdFx0LmFwcGVuZCggb3B0aW9ucyApXG5cdFx0XHQudmFsKCBkYXRhLnZhbHVlIClcblx0XHRcdC5maW5kKCAnOnNlbGVjdGVkJyApXG5cdFx0XHRcdC5hdHRyKCAnc2VsZWN0ZWQnLCB0cnVlICk7XG5cblx0XHRyZXR1cm4gJHRlbXBsYXRlLmh0bWwoKTtcblx0fSxcblxuXHRnZXRGaWVsZE5hbWU6IGZ1bmN0aW9uICggZmllbGQgKSB7XG5cblx0XHRpZiAoIF8uaXNBcnJheSggZmllbGQgKSApIHtcblxuXHRcdFx0ZmllbGQgPSBfLmNsb25lKCBmaWVsZCApO1xuXG5cdFx0XHRpZiAoIDEgPT09IGZpZWxkLmxlbmd0aCApIHtcblx0XHRcdFx0ZmllbGQgPSBmaWVsZC5zaGlmdCgpO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0ZmllbGQgPSBmaWVsZC5zaGlmdCgpICsgJ1snICsgZmllbGQuam9pbiggJ11bJyApICsgJ10nO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHJldHVybiBmaWVsZDtcblx0fSxcblxuXHRnZXRBdHRyU2x1ZzogZnVuY3Rpb24gKCByZWFjdGlvbiwgZmllbGROYW1lICkge1xuXG5cdFx0dmFyIG5hbWUgPSBmaWVsZE5hbWU7XG5cblx0XHR2YXIgbmFtZVBhcnRzID0gW10sXG5cdFx0XHRmaXJzdEJyYWNrZXQgPSBuYW1lLmluZGV4T2YoICdbJyApO1xuXG5cdFx0Ly8gSWYgdGhpcyBpc24ndCBhbiBhcnJheS1zeW50YXggbmFtZSwgd2UgZG9uJ3QgbmVlZCB0byBwcm9jZXNzIGl0LlxuXHRcdGlmICggLTEgPT09IGZpcnN0QnJhY2tldCApIHtcblx0XHRcdHJldHVybiBuYW1lO1xuXHRcdH1cblxuXHRcdC8vIFVzdWFsbHkgdGhlIGJyYWNrZXQgd2lsbCBiZSBwcm9jZWVkZWQgYnkgc29tZXRoaW5nOiBgYXJyYXlbLi4uXWAuXG5cdFx0aWYgKCAwICE9PSBmaXJzdEJyYWNrZXQgKSB7XG5cdFx0XHRuYW1lUGFydHMucHVzaCggbmFtZS5zdWJzdHJpbmcoIDAsIGZpcnN0QnJhY2tldCApICk7XG5cdFx0XHRuYW1lID0gbmFtZS5zdWJzdHJpbmcoIGZpcnN0QnJhY2tldCApO1xuXHRcdH1cblxuXHRcdG5hbWVQYXJ0cyA9IG5hbWVQYXJ0cy5jb25jYXQoIG5hbWUuc2xpY2UoIDEsIC0xICkuc3BsaXQoICddWycgKSApO1xuXG5cdFx0Ly8gSWYgdGhlIGxhc3QgZWxlbWVudCBpcyBlbXB0eSwgaXQgaXMgYSBub24tYXNzb2NpYXRpdmUgYXJyYXk6IGBhW11gXG5cdFx0aWYgKCBuYW1lUGFydHNbIG5hbWVQYXJ0cy5sZW5ndGggLSAxIF0gPT09ICcnICkge1xuXHRcdFx0bmFtZVBhcnRzLnBvcCgpO1xuXHRcdH1cblxuXHRcdHJldHVybiBuYW1lUGFydHM7XG5cdH0sXG5cblx0Ly8gR2V0IHRoZSBkYXRhIGZyb20gYSBmb3JtIGFzIGtleSA9PiB2YWx1ZSBwYWlycy5cblx0Z2V0Rm9ybURhdGE6IGZ1bmN0aW9uICggcmVhY3Rpb24sICRmb3JtICkge1xuXG5cdFx0dmFyIGZvcm1PYmogPSB7fSxcblx0XHRcdGlucHV0cyA9ICRmb3JtLmZpbmQoICc6aW5wdXQnICkuc2VyaWFsaXplQXJyYXkoKTtcblxuXHRcdF8uZWFjaCggaW5wdXRzLCBmdW5jdGlvbiAoIGlucHV0ICkge1xuXHRcdFx0Zm9ybU9ialsgaW5wdXQubmFtZSBdID0gaW5wdXQudmFsdWU7XG5cdFx0fSApO1xuXG5cdFx0Ly8gU2V0IHVuY2hlY2tlZCBjaGVja2JveGVzJyB2YWx1ZXMgdG8gZmFsc2UsIHNvIHRoYXQgdGhleSB3aWxsIG92ZXJyaWRlIHRoZVxuXHRcdC8vIGN1cnJlbnQgdmFsdWUgd2hlbiBtZXJnZWQuXG5cdFx0JGZvcm0uZmluZCggJ2lucHV0W3R5cGU9Y2hlY2tib3hdJyApLmVhY2goIGZ1bmN0aW9uICggaSwgZWwgKSB7XG5cblx0XHRcdGlmICggdHlwZW9mIGZvcm1PYmpbIGVsLm5hbWUgXSA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRcdGZvcm1PYmpbIGVsLm5hbWUgXSA9IGZhbHNlO1xuXHRcdFx0fVxuXHRcdH0pO1xuXG5cdFx0cmV0dXJuIHRoaXMuYXJyYXlpZnkoIGZvcm1PYmogKTtcblx0fSxcblxuXHRhcnJheWlmeTogZnVuY3Rpb24gKCBmb3JtRGF0YSApIHtcblxuXHRcdHZhciBhcnJheURhdGEgPSB7fTtcblxuXHRcdF8uZWFjaCggZm9ybURhdGEsIGZ1bmN0aW9uICggdmFsdWUsIG5hbWUgKSB7XG5cblx0XHRcdHZhciBuYW1lUGFydHMgPSBbXSxcblx0XHRcdFx0ZGF0YSA9IGFycmF5RGF0YSxcblx0XHRcdFx0aXNBcnJheSA9IGZhbHNlLFxuXHRcdFx0XHRmaXJzdEJyYWNrZXQgPSBuYW1lLmluZGV4T2YoICdbJyApO1xuXG5cdFx0XHQvLyBJZiB0aGlzIGlzbid0IGFuIGFycmF5LXN5bnRheCBuYW1lLCB3ZSBkb24ndCBuZWVkIHRvIHByb2Nlc3MgaXQuXG5cdFx0XHRpZiAoIC0xID09PSBmaXJzdEJyYWNrZXQgKSB7XG5cdFx0XHRcdGRhdGFbIG5hbWUgXSA9IHZhbHVlO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFVzdWFsbHkgdGhlIGJyYWNrZXQgd2lsbCBiZSBwcm9jZWVkZWQgYnkgc29tZXRoaW5nOiBgYXJyYXlbLi4uXWAuXG5cdFx0XHRpZiAoIDAgIT09IGZpcnN0QnJhY2tldCApIHtcblx0XHRcdFx0bmFtZVBhcnRzLnB1c2goIG5hbWUuc3Vic3RyaW5nKCAwLCBmaXJzdEJyYWNrZXQgKSApO1xuXHRcdFx0XHRuYW1lID0gbmFtZS5zdWJzdHJpbmcoIGZpcnN0QnJhY2tldCApO1xuXHRcdFx0fVxuXG5cdFx0XHRuYW1lUGFydHMgPSBuYW1lUGFydHMuY29uY2F0KCBuYW1lLnNsaWNlKCAxLCAtMSApLnNwbGl0KCAnXVsnICkgKTtcblxuXHRcdFx0Ly8gSWYgdGhlIGxhc3QgZWxlbWVudCBpcyBlbXB0eSwgaXQgaXMgYSBub24tYXNzb2NpYXRpdmUgYXJyYXk6IGBhW11gXG5cdFx0XHRpZiAoIG5hbWVQYXJ0c1sgbmFtZVBhcnRzLmxlbmd0aCAtIDEgXSA9PT0gJycgKSB7XG5cdFx0XHRcdGlzQXJyYXkgPSB0cnVlO1xuXHRcdFx0XHRuYW1lUGFydHMucG9wKCk7XG5cdFx0XHR9XG5cblx0XHRcdHZhciBrZXkgPSBuYW1lUGFydHMucG9wKCk7XG5cblx0XHRcdC8vIENvbnN0cnVjdCB0aGUgaGllcmFyY2hpY2FsIG9iamVjdC5cblx0XHRcdF8uZWFjaCggbmFtZVBhcnRzLCBmdW5jdGlvbiAoIHBhcnQgKSB7XG5cdFx0XHRcdGRhdGEgPSBkYXRhWyBwYXJ0IF0gPSAoIGRhdGFbIHBhcnQgXSB8fCB7fSApO1xuXHRcdFx0fSk7XG5cblx0XHRcdC8vIFNldCB0aGUgdmFsdWUuXG5cdFx0XHRpZiAoIGlzQXJyYXkgKSB7XG5cblx0XHRcdFx0aWYgKCB0eXBlb2YgZGF0YVsga2V5IF0gPT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0XHRcdGRhdGFbIGtleSBdID0gW107XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRkYXRhWyBrZXkgXS5wdXNoKCB2YWx1ZSApO1xuXG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRkYXRhWyBrZXkgXSA9IHZhbHVlO1xuXHRcdFx0fVxuXHRcdH0pO1xuXG5cdFx0cmV0dXJuIGFycmF5RGF0YTtcblx0fSxcblxuXHR2YWxpZGF0ZTogZnVuY3Rpb24gKCBmaWVsZHMsIGF0dHJpYnV0ZXMsIGVycm9ycyApIHtcblxuXHRcdF8uZWFjaCggZmllbGRzLCBmdW5jdGlvbiAoIGZpZWxkLCBzbHVnICkge1xuXHRcdFx0aWYgKFxuXHRcdFx0XHRmaWVsZC5yZXF1aXJlZFxuXHRcdFx0XHQmJiAoXG5cdFx0XHRcdFx0dHlwZW9mIGF0dHJpYnV0ZXNbIHNsdWcgXSA9PT0gJ3VuZGVmaW5lZCdcblx0XHRcdFx0XHR8fCAnJyA9PT0gJC50cmltKCBhdHRyaWJ1dGVzWyBzbHVnIF0gKVxuXHRcdFx0XHQpXG5cdFx0XHQpIHtcblx0XHRcdFx0ZXJyb3JzLnB1c2goIHtcblx0XHRcdFx0XHRmaWVsZDogc2x1Zyxcblx0XHRcdFx0XHRtZXNzYWdlOiB0aGlzLmVtcHR5TWVzc2FnZSggZmllbGQgKVxuXHRcdFx0XHR9ICk7XG5cdFx0XHR9XG5cdFx0fSwgdGhpcyApO1xuXHR9LFxuXG5cdGluaXRSZWFjdGlvbjogZnVuY3Rpb24gKCByZWFjdGlvbiApIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHJlYWN0aW9uLCAncmVuZGVyOnNldHRpbmdzJywgdGhpcy5yZW5kZXJSZWFjdGlvbiApO1xuXHR9LFxuXG5cdHJlbmRlclJlYWN0aW9uOiBmdW5jdGlvbiAoICRlbCwgY3VycmVudEFjdGlvblR5cGUsIHJlYWN0aW9uICkge1xuXG5cdFx0dmFyIGZpZWxkc0hUTUwgPSAnJztcblxuXHRcdF8uZWFjaCggdGhpcy5nZXQoICdmaWVsZHMnICksIGZ1bmN0aW9uICggZmllbGQsIG5hbWUgKSB7XG5cblx0XHRcdGZpZWxkc0hUTUwgKz0gdGhpcy5jcmVhdGUoXG5cdFx0XHRcdG5hbWUsXG5cdFx0XHRcdHJlYWN0aW9uLm1vZGVsLmdldCggbmFtZSApLFxuXHRcdFx0XHRmaWVsZFxuXHRcdFx0KTtcblxuXHRcdH0sIHRoaXMgKTtcblxuXHRcdCRlbC5odG1sKCBmaWVsZHNIVE1MICk7XG5cdH0sXG5cblx0dmFsaWRhdGVSZWFjdGlvbjogZnVuY3Rpb24gKCByZWFjdGlvbiwgYXR0cmlidXRlcywgZXJyb3JzICkge1xuXG5cdFx0dGhpcy52YWxpZGF0ZSggdGhpcy5nZXQoICdmaWVsZHMnICksIGF0dHJpYnV0ZXMsIGVycm9ycyApO1xuXHR9XG59KTtcblxudmFyIERhdGFUeXBlID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKHtcblxuXHRpZEF0dHJpYnV0ZTogJ3NsdWcnLFxuXG5cdGRlZmF1bHRzOiB7XG5cdFx0aW5wdXRUeXBlOiAndGV4dCdcblx0fSxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLXJlYWN0aW9uLWZpZWxkJyApLFxuXG5cdGNyZWF0ZUZpZWxkOiBmdW5jdGlvbiAoIGRhdGEgKSB7XG5cblx0XHRyZXR1cm4gdGhpcy50ZW1wbGF0ZShcblx0XHRcdF8uZXh0ZW5kKCB7fSwgZGF0YSwgeyB0eXBlOiB0aGlzLmdldCggJ2lucHV0VHlwZScgKSB9IClcblx0XHQpO1xuXHR9XG59KTtcblxudmFyIERhdGFUeXBlcyA9IG5ldyBCYWNrYm9uZS5Db2xsZWN0aW9uKCBbXSwgeyBtb2RlbDogRGF0YVR5cGUgfSk7XG5cbkRhdGFUeXBlcy5hZGQoIHsgc2x1ZzogJ3RleHQnIH0gKTtcbkRhdGFUeXBlcy5hZGQoIHsgc2x1ZzogJ2ludGVnZXInLCBpbnB1dFR5cGU6ICdudW1iZXInIH0gKTtcbkRhdGFUeXBlcy5hZGQoIHsgc2x1ZzogJ2RlY2ltYWxfbnVtYmVyJywgaW5wdXRUeXBlOiAnbnVtYmVyJyB9ICk7XG5cbm1vZHVsZS5leHBvcnRzID0gRmllbGRzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuUmVhY3RvclxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLkV4dGVuc2lvblxuICpcbiAqXG4gKi9cbnZhciBFeHRlbnNpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uLFxuXHRob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3MsXG5cdGVtcHR5RnVuY3Rpb24gPSBob29rcy51dGlsLmVtcHR5RnVuY3Rpb24sXG5cdFJlYWN0b3I7XG5cblJlYWN0b3IgPSBFeHRlbnNpb24uZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdCdhcmdfdHlwZXMnOiBbXSxcblx0XHQnYWN0aW9uX3R5cGVzJzogW11cblx0fSxcblxuXHQvKipcblx0ICogQHNpbmNlIDIuMS4wXG5cdCAqL1xuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLmxpc3RlblRvKCBob29rcywgJ3JlYWN0aW9uczp2aWV3OmluaXQnLCB0aGlzLmxpc3RlblRvRGVmYXVsdHMgKTtcblxuXHRcdHRoaXMuX19jaGlsZF9fLmluaXRpYWxpemUuYXBwbHkoIHRoaXMsIGFyZ3VtZW50cyApO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc2luY2UgMi4xLjBcblx0ICovXG5cdGxpc3RlblRvRGVmYXVsdHM6IGZ1bmN0aW9uICggcmVhY3Rpb25zVmlldyApIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oXG5cdFx0XHRyZWFjdGlvbnNWaWV3XG5cdFx0XHQsICdob29rLXJlYWN0aW9uLWRlZmF1bHRzJ1xuXHRcdFx0LCB0aGlzLmZpbHRlclJlYWN0aW9uRGVmYXVsdHNcblx0XHQpO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc2luY2UgMi4xLjBcblx0ICogQGFic3RyYWN0XG5cdCAqL1xuXHRmaWx0ZXJSZWFjdGlvbkRlZmF1bHRzOiBlbXB0eUZ1bmN0aW9uKCAnZmlsdGVyUmVhY3Rpb25EZWZhdWx0cycgKVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gUmVhY3RvcjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLlJlYWN0b3JzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuQ29sbGVjdGlvblxuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb25zXG4gKi9cbnZhciBFeHRlbnNpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLkV4dGVuc2lvbnMsXG5cdFJlYWN0b3IgPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuUmVhY3Rvcixcblx0UmVhY3RvcnM7XG5cblJlYWN0b3JzID0gRXh0ZW5zaW9ucy5leHRlbmQoe1xuXHRtb2RlbDogUmVhY3RvclxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gUmVhY3RvcnM7IiwidmFyIGhvb2tzID0gd3Aud29yZHBvaW50cy5ob29rcyxcblx0JCA9IGpRdWVyeSxcblx0ZGF0YTtcblxuLy8gTG9hZCB0aGUgYXBwbGljYXRpb24gb25jZSB0aGUgRE9NIGlzIHJlYWR5LlxuJCggZnVuY3Rpb24gKCkge1xuXG5cdC8vIExldCBhbGwgcGFydHMgb2YgdGhlIGFwcCBrbm93IHRoYXQgd2UncmUgYWJvdXQgdG8gc3RhcnQuXG5cdGhvb2tzLnRyaWdnZXIoICdpbml0JyApO1xuXG5cdC8vIFdlIGtpY2sgdGhpbmdzIG9mZiBieSBjcmVhdGluZyB0aGUgKipHcm91cHMqKi5cblx0Ly8gSW5zdGVhZCBvZiBnZW5lcmF0aW5nIG5ldyBlbGVtZW50cywgYmluZCB0byB0aGUgZXhpc3Rpbmcgc2tlbGV0b25zIG9mXG5cdC8vIHRoZSBncm91cHMgYWxyZWFkeSBwcmVzZW50IGluIHRoZSBIVE1MLlxuXHQkKCAnLndvcmRwb2ludHMtaG9vay1yZWFjdGlvbi1ncm91cC1jb250YWluZXInICkuZWFjaCggZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyICR0aGlzID0gJCggdGhpcyApLFxuXHRcdFx0ZXZlbnQ7XG5cblx0XHRldmVudCA9ICR0aGlzXG5cdFx0XHQuZmluZCggJy53b3JkcG9pbnRzLWhvb2stcmVhY3Rpb24tZ3JvdXAnIClcblx0XHRcdFx0LmRhdGEoICd3b3JkcG9pbnRzLWhvb2tzLWhvb2stZXZlbnQnICk7XG5cblx0XHRuZXcgaG9va3Mudmlldy5SZWFjdGlvbnMoIHtcblx0XHRcdGVsOiAkdGhpcyxcblx0XHRcdG1vZGVsOiBuZXcgaG9va3MubW9kZWwuUmVhY3Rpb25zKCBkYXRhLnJlYWN0aW9uc1sgZXZlbnQgXSApXG5cdFx0fSApO1xuXHR9ICk7XG59KTtcblxuLy8gTGluayBhbnkgbG9jYWxpemVkIHN0cmluZ3MuXG5ob29rcy52aWV3LmwxMG4gPSB3aW5kb3cuV29yZFBvaW50c0hvb2tzQWRtaW5MMTBuIHx8IHt9O1xuXG4vLyBMaW5rIGFueSBzZXR0aW5ncy5cbmRhdGEgPSBob29rcy52aWV3LmRhdGEgPSB3aW5kb3cuV29yZFBvaW50c0hvb2tzQWRtaW5EYXRhIHx8IHt9O1xuXG4vLyBMb2FkIHRoZSBjb250cm9sbGVycy5cbmhvb2tzLmNvbnRyb2xsZXIuRmllbGRzICAgICA9IHJlcXVpcmUoICcuL2NvbnRyb2xsZXJzL2ZpZWxkcy5qcycgKTtcbmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uICA9IHJlcXVpcmUoICcuL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcycgKTtcbmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9ucyA9IHJlcXVpcmUoICcuL2NvbnRyb2xsZXJzL2V4dGVuc2lvbnMuanMnICk7XG5ob29rcy5jb250cm9sbGVyLlJlYWN0b3IgICAgPSByZXF1aXJlKCAnLi9jb250cm9sbGVycy9yZWFjdG9yLmpzJyApO1xuaG9va3MuY29udHJvbGxlci5SZWFjdG9ycyAgID0gcmVxdWlyZSggJy4vY29udHJvbGxlcnMvcmVhY3RvcnMuanMnICk7XG5ob29rcy5jb250cm9sbGVyLkFyZ3MgICAgICAgPSByZXF1aXJlKCAnLi9jb250cm9sbGVycy9hcmdzLmpzJyApO1xuXG4vLyBTdGFydCB0aGVtIHVwIGhlcmUgc28gdGhhdCB3ZSBjYW4gYmVnaW4gdXNpbmcgdGhlbS5cbmhvb2tzLkZpZWxkcyAgICAgPSBuZXcgaG9va3MuY29udHJvbGxlci5GaWVsZHMoIHsgZmllbGRzOiBkYXRhLmZpZWxkcyB9ICk7XG5ob29rcy5SZWFjdG9ycyAgID0gbmV3IGhvb2tzLmNvbnRyb2xsZXIuUmVhY3RvcnMoKTtcbmhvb2tzLkV4dGVuc2lvbnMgPSBuZXcgaG9va3MuY29udHJvbGxlci5FeHRlbnNpb25zKCk7XG5ob29rcy5BcmdzICAgICAgID0gbmV3IGhvb2tzLmNvbnRyb2xsZXIuQXJncyh7IGV2ZW50czogZGF0YS5ldmVudHMsIGVudGl0aWVzOiBkYXRhLmVudGl0aWVzIH0pO1xuXG4vLyBMb2FkIHRoZSB2aWV3cy5cbmhvb2tzLnZpZXcuQmFzZSAgICAgICAgICAgICAgPSByZXF1aXJlKCAnLi92aWV3cy9iYXNlLmpzJyApO1xuaG9va3Mudmlldy5SZWFjdGlvbiAgICAgICAgICA9IHJlcXVpcmUoICcuL3ZpZXdzL3JlYWN0aW9uLmpzJyApO1xuaG9va3Mudmlldy5SZWFjdGlvbnMgICAgICAgICA9IHJlcXVpcmUoICcuL3ZpZXdzL3JlYWN0aW9ucy5qcycgKTtcbmhvb2tzLnZpZXcuQXJnU2VsZWN0b3IgICAgICAgPSByZXF1aXJlKCAnLi92aWV3cy9hcmctc2VsZWN0b3IuanMnICk7XG5ob29rcy52aWV3LkFyZ1NlbGVjdG9ycyAgICAgID0gcmVxdWlyZSggJy4vdmlld3MvYXJnLXNlbGVjdG9ycy5qcycgKTtcbmhvb2tzLnZpZXcuQXJnSGllcmFyY2h5U2VsZWN0b3IgPSByZXF1aXJlKCAnLi92aWV3cy9hcmctaGllcmFyY2h5LXNlbGVjdG9yLmpzJyApO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQXJnU2VsZWN0b3JzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdEFyZ3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MsXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0JCA9IEJhY2tib25lLiQsXG5cdEFyZ0hpZXJhcmNoeVNlbGVjdG9yO1xuXG5BcmdIaWVyYXJjaHlTZWxlY3RvciA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdhcmctaGllcmFyY2h5LXNlbGVjdG9yJyxcblxuXHR0YWdOYW1lOiAnZGl2JyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWFyZy1zZWxlY3RvcicgKSxcblxuXHRldmVudHM6IHtcblx0XHQnY2hhbmdlIHNlbGVjdCc6ICd0cmlnZ2VyQ2hhbmdlJ1xuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggb3B0aW9ucyApIHtcblx0XHRpZiAoIG9wdGlvbnMuaGllcmFyY2hpZXMgKSB7XG5cdFx0XHR0aGlzLmhpZXJhcmNoaWVzID0gb3B0aW9ucy5oaWVyYXJjaGllcztcblx0XHR9XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRlbC5hcHBlbmQoXG5cdFx0XHR0aGlzLnRlbXBsYXRlKCB7IGxhYmVsOiB0aGlzLmxhYmVsLCBuYW1lOiB0aGlzLmNpZCB9IClcblx0XHQpO1xuXG5cdFx0dGhpcy4kc2VsZWN0ID0gdGhpcy4kKCAnc2VsZWN0JyApO1xuXG5cdFx0Xy5lYWNoKCB0aGlzLmhpZXJhcmNoaWVzLCBmdW5jdGlvbiAoIGhpZXJhcmNoeSwgaW5kZXggKSB7XG5cblx0XHRcdHZhciAkb3B0aW9uID0gJCggJzxvcHRpb24+PC9vcHRpb24+JyApXG5cdFx0XHRcdC52YWwoIGluZGV4IClcblx0XHRcdFx0LnRleHQoIEFyZ3MuYnVpbGRIaWVyYXJjaHlIdW1hbklkKCBoaWVyYXJjaHkgKSApO1xuXG5cdFx0XHR0aGlzLiRzZWxlY3QuYXBwZW5kKCAkb3B0aW9uICk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXInLCB0aGlzICk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHR0cmlnZ2VyQ2hhbmdlOiBmdW5jdGlvbiAoIGV2ZW50ICkge1xuXG5cdFx0dmFyIGluZGV4ID0gdGhpcy4kc2VsZWN0LnZhbCgpLFxuXHRcdFx0aGllcmFyY2h5LCBhcmc7XG5cblx0XHQvLyBEb24ndCBkbyBhbnl0aGluZyBpZiB0aGUgdmFsdWUgaGFzbid0IHJlYWxseSBjaGFuZ2VkLlxuXHRcdGlmICggaW5kZXggPT09IHRoaXMuY3VycmVudEluZGV4ICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRoaXMuY3VycmVudEluZGV4ID0gaW5kZXg7XG5cblx0XHRpZiAoIGluZGV4ICE9PSBmYWxzZSApIHtcblx0XHRcdGhpZXJhcmNoeSA9IHRoaXMuaGllcmFyY2hpZXNbIGluZGV4IF07XG5cblx0XHRcdGlmICggISBoaWVyYXJjaHkgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YXJnID0gaGllcmFyY2h5WyBoaWVyYXJjaHkubGVuZ3RoIC0gMSBdO1xuXHRcdH1cblxuXHRcdHRoaXMudHJpZ2dlciggJ2NoYW5nZScsIHRoaXMsIGFyZywgaW5kZXgsIGV2ZW50ICk7XG5cdH0sXG5cblx0Z2V0SGllcmFyY2h5OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgaGllcmFyY2h5ID0gW107XG5cblx0XHRfLmVhY2goIHRoaXMuZ2V0SGllcmFyY2h5QXJncygpLCBmdW5jdGlvbiAoIGFyZyApIHtcblx0XHRcdGhpZXJhcmNoeS5wdXNoKCBhcmcuZ2V0KCAnc2x1ZycgKSApO1xuXHRcdH0pO1xuXG5cdFx0cmV0dXJuIGhpZXJhcmNoeTtcblx0fSxcblxuXHRnZXRIaWVyYXJjaHlBcmdzOiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIHRoaXMuaGllcmFyY2hpZXNbIHRoaXMuY3VycmVudEluZGV4IF07XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEFyZ0hpZXJhcmNoeVNlbGVjdG9yO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQXJnU2VsZWN0b3JcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHRBcmdTZWxlY3RvcjtcblxuQXJnU2VsZWN0b3IgPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAnYXJnLXNlbGVjdG9yJyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWFyZy1zZWxlY3RvcicgKSxcblxuXHRvcHRpb25UZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWFyZy1vcHRpb24nICksXG5cblx0ZXZlbnRzOiB7XG5cdFx0J2NoYW5nZSBzZWxlY3QnOiAndHJpZ2dlckNoYW5nZSdcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIG9wdGlvbnMgKSB7XG5cblx0XHR0aGlzLmxhYmVsID0gb3B0aW9ucy5sYWJlbDtcblx0XHR0aGlzLm51bWJlciA9IG9wdGlvbnMubnVtYmVyO1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5jb2xsZWN0aW9uLCAndXBkYXRlJywgdGhpcy5yZW5kZXIgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLmNvbGxlY3Rpb24sICdyZXNldCcsIHRoaXMucmVuZGVyICk7XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRlbC5odG1sKFxuXHRcdFx0dGhpcy50ZW1wbGF0ZSggeyBsYWJlbDogdGhpcy5sYWJlbCwgbmFtZTogdGhpcy5jaWQgKyAnXycgKyB0aGlzLm51bWJlciB9IClcblx0XHQpO1xuXG5cdFx0dGhpcy4kc2VsZWN0ID0gdGhpcy4kKCAnc2VsZWN0JyApO1xuXG5cdFx0dGhpcy5jb2xsZWN0aW9uLmVhY2goIGZ1bmN0aW9uICggYXJnICkge1xuXG5cdFx0XHR0aGlzLiRzZWxlY3QuYXBwZW5kKCB0aGlzLm9wdGlvblRlbXBsYXRlKCBhcmcuYXR0cmlidXRlcyApICk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXInLCB0aGlzICk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHR0cmlnZ2VyQ2hhbmdlOiBmdW5jdGlvbiAoIGV2ZW50ICkge1xuXG5cdFx0dmFyIHZhbHVlID0gdGhpcy4kc2VsZWN0LnZhbCgpO1xuXG5cdFx0aWYgKCAnMCcgPT09IHZhbHVlICkge1xuXHRcdFx0dmFsdWUgPSBmYWxzZTtcblx0XHR9XG5cblx0XHR0aGlzLnRyaWdnZXIoICdjaGFuZ2UnLCB0aGlzLCB2YWx1ZSwgZXZlbnQgKTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQXJnU2VsZWN0b3I7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5BcmdTZWxlY3RvcnNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0QXJnU2VsZWN0b3IgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQXJnU2VsZWN0b3IsXG5cdEFyZ1NlbGVjdG9ycztcblxuQXJnU2VsZWN0b3JzID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ2FyZy1zZWxlY3RvcnMnLFxuXG5cdHRhZ05hbWU6ICdkaXYnLFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggb3B0aW9ucyApIHtcblx0XHRpZiAoIG9wdGlvbnMuYXJncyApIHtcblx0XHRcdHRoaXMuYXJncyA9IG9wdGlvbnMuYXJncztcblx0XHR9XG5cblx0XHR0aGlzLmhpZXJhcmNoeSA9IFtdO1xuXHR9LFxuXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGFyZ3MgPSB0aGlzLmFyZ3MsIGFyZztcblxuXHRcdGlmICggYXJncy5sZW5ndGggPT09IDEgKSB7XG5cdFx0XHRhcmcgPSBhcmdzLmF0KCAwICk7XG5cdFx0XHR0aGlzLmhpZXJhcmNoeS5wdXNoKCB7IGFyZzogYXJnIH0gKTtcblx0XHRcdGFyZ3MgPSBhcmcuZ2V0Q2hpbGRyZW4oKTtcblx0XHR9XG5cblx0XHR0aGlzLmFkZFNlbGVjdG9yKCBhcmdzICk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHRhZGRTZWxlY3RvcjogZnVuY3Rpb24gKCBhcmdzICkge1xuXG5cdFx0dmFyIHNlbGVjdG9yID0gbmV3IEFyZ1NlbGVjdG9yKHtcblx0XHRcdGNvbGxlY3Rpb246IGFyZ3MsXG5cdFx0XHRudW1iZXI6IHRoaXMuaGllcmFyY2h5Lmxlbmd0aFxuXHRcdH0pO1xuXG5cdFx0c2VsZWN0b3IucmVuZGVyKCk7XG5cblx0XHR0aGlzLiRlbC5hcHBlbmQoIHNlbGVjdG9yLiRlbCApO1xuXG5cdFx0c2VsZWN0b3IuJCggJ3NlbGVjdCcgKS5mb2N1cygpO1xuXG5cdFx0dGhpcy5oaWVyYXJjaHkucHVzaCggeyBzZWxlY3Rvcjogc2VsZWN0b3IgfSApO1xuXG5cdFx0dGhpcy5saXN0ZW5Ubyggc2VsZWN0b3IsICdjaGFuZ2UnLCB0aGlzLnVwZGF0ZSApO1xuXHR9LFxuXG5cdHVwZGF0ZTogZnVuY3Rpb24gKCBzZWxlY3RvciwgdmFsdWUgKSB7XG5cblx0XHR2YXIgaWQgPSBzZWxlY3Rvci5udW1iZXIsXG5cdFx0XHRhcmc7XG5cblx0XHQvLyBEb24ndCBkbyBhbnl0aGluZyBpZiB0aGUgdmFsdWUgaGFzbid0IHJlYWxseSBjaGFuZ2VkLlxuXHRcdGlmICggdGhpcy5oaWVyYXJjaHlbIGlkIF0uYXJnICYmIHZhbHVlID09PSB0aGlzLmhpZXJhcmNoeVsgaWQgXS5hcmcuZ2V0KCAnc2x1ZycgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRpZiAoIHZhbHVlICkge1xuXHRcdFx0YXJnID0gc2VsZWN0b3IuY29sbGVjdGlvbi5nZXQoIHZhbHVlICk7XG5cblx0XHRcdGlmICggISBhcmcgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHR9XG5cblx0XHR0aGlzLnRyaWdnZXIoICdjaGFuZ2luZycsIHRoaXMsIGFyZywgdmFsdWUgKTtcblxuXHRcdGlmICggdmFsdWUgKSB7XG5cblx0XHRcdHRoaXMuaGllcmFyY2h5WyBpZCBdLmFyZyA9IGFyZztcblxuXHRcdFx0dGhpcy51cGRhdGVDaGlsZHJlbiggaWQgKTtcblxuXHRcdH0gZWxzZSB7XG5cblx0XHRcdC8vIE5vdGhpbmcgaXMgc2VsZWN0ZWQsIGhpZGUgYWxsIGNoaWxkIHNlbGVjdG9ycy5cblx0XHRcdHRoaXMuaGlkZUNoaWxkcmVuKCBpZCApO1xuXG5cdFx0XHRkZWxldGUgdGhpcy5oaWVyYXJjaHlbIGlkIF0uYXJnO1xuXHRcdH1cblxuXHRcdHRoaXMudHJpZ2dlciggJ2NoYW5nZScsIHRoaXMsIGFyZywgdmFsdWUgKTtcblx0fSxcblxuXHR1cGRhdGVDaGlsZHJlbjogZnVuY3Rpb24gKCBpZCApIHtcblxuXHRcdHZhciBhcmcgPSB0aGlzLmhpZXJhcmNoeVsgaWQgXS5hcmcsIGNoaWxkcmVuO1xuXG5cdFx0aWYgKCBhcmcuZ2V0Q2hpbGRyZW4gKSB7XG5cblx0XHRcdGNoaWxkcmVuID0gYXJnLmdldENoaWxkcmVuKCk7XG5cblx0XHRcdC8vIFdlIGNvbXByZXNzIHJlbGF0aW9uc2hpcHMgc28gd2UgaGF2ZSBqdXN0IFBvc3QgwrsgQXV0aG9yIGluc3RlYWQgb2Zcblx0XHRcdC8vIFBvc3QgwrsgQXV0aG9yIMK7IFVzZXIuXG5cdFx0XHRpZiAoIGNoaWxkcmVuLmxlbmd0aCAmJiBhcmcuZ2V0KCAnX3R5cGUnICkgPT09ICdyZWxhdGlvbnNoaXAnICkge1xuXHRcdFx0XHR2YXIgY2hpbGQgPSBjaGlsZHJlbi5hdCggMCApO1xuXG5cdFx0XHRcdGlmICggISBjaGlsZC5nZXRDaGlsZHJlbiApIHtcblx0XHRcdFx0XHR0aGlzLmhpZGVDaGlsZHJlbiggaWQgKTtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRjaGlsZHJlbiA9IGNoaWxkLmdldENoaWxkcmVuKCk7XG5cdFx0XHR9XG5cblx0XHRcdC8vIEhpZGUgYW55IGdyYW5kY2hpbGQgc2VsZWN0b3JzLlxuXHRcdFx0dGhpcy5oaWRlQ2hpbGRyZW4oIGlkICsgMSApO1xuXG5cdFx0XHQvLyBDcmVhdGUgdGhlIGNoaWxkIHNlbGVjdG9yIGlmIGl0IGRvZXMgbm90IGV4aXN0LlxuXHRcdFx0aWYgKCAhIHRoaXMuaGllcmFyY2h5WyBpZCArIDEgXSApIHtcblx0XHRcdFx0dGhpcy5hZGRTZWxlY3RvciggY2hpbGRyZW4gKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHRoaXMuaGllcmFyY2h5WyBpZCArIDEgXS5zZWxlY3Rvci5jb2xsZWN0aW9uLnJlc2V0KCBjaGlsZHJlbi5tb2RlbHMgKTtcblx0XHRcdFx0dGhpcy5oaWVyYXJjaHlbIGlkICsgMSBdLnNlbGVjdG9yLiRlbC5zaG93KCkuZmluZCggJ3NlbGVjdCcgKS5mb2N1cygpO1xuXHRcdFx0fVxuXG5cdFx0fSBlbHNlIHtcblxuXHRcdFx0dGhpcy5oaWRlQ2hpbGRyZW4oIGlkICk7XG5cdFx0fVxuXHR9LFxuXG5cdGhpZGVDaGlsZHJlbjogZnVuY3Rpb24gKCBpZCApIHtcblx0XHRfLmVhY2goIHRoaXMuaGllcmFyY2h5LnNsaWNlKCBpZCArIDEgKSwgZnVuY3Rpb24gKCBsZXZlbCApIHtcblx0XHRcdGxldmVsLnNlbGVjdG9yLiRlbC5oaWRlKCk7XG5cdFx0XHRkZWxldGUgbGV2ZWwuYXJnO1xuXHRcdH0pO1xuXHR9LFxuXG5cdGdldEhpZXJhcmNoeTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGhpZXJhcmNoeSA9IFtdO1xuXG5cdFx0Xy5lYWNoKCB0aGlzLmhpZXJhcmNoeSwgZnVuY3Rpb24gKCBsZXZlbCApIHtcblxuXHRcdFx0aWYgKCAhIGxldmVsLmFyZyApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRoaWVyYXJjaHkucHVzaCggbGV2ZWwuYXJnLmdldCggJ3NsdWcnICkgKTtcblxuXHRcdFx0Ly8gUmVsYXRpb25zaGlwcyBhcmUgY29tcHJlc3NlZCwgc28gd2UgaGF2ZSB0byBleHBhbmQgdGhlbSBoZXJlLlxuXHRcdFx0aWYgKCBsZXZlbC5hcmcuZ2V0KCAnX3R5cGUnICkgPT09ICdyZWxhdGlvbnNoaXAnICkge1xuXHRcdFx0XHRoaWVyYXJjaHkucHVzaCggbGV2ZWwuYXJnLmdldCggJ3NlY29uZGFyeScgKSApO1xuXHRcdFx0fVxuXHRcdH0pO1xuXG5cdFx0cmV0dXJuIGhpZXJhcmNoeTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQXJnU2VsZWN0b3JzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqL1xudmFyIGhvb2tzID0gd3Aud29yZHBvaW50cy5ob29rcyxcblx0ZXh0ZW5kID0gaG9va3MudXRpbC5leHRlbmQsXG5cdEJhc2U7XG5cbi8vIEFkZCBhIGJhc2UgdmlldyBzbyB3ZSBjYW4gaGF2ZSBhIHN0YW5kYXJkaXplZCB2aWV3IGJvb3RzdHJhcCBmb3IgdGhpcyBhcHAuXG5CYXNlID0gQmFja2JvbmUuVmlldy5leHRlbmQoIHtcblxuXHQvLyBGaXJzdCwgd2UgbGV0IGVhY2ggdmlldyBzcGVjaWZ5IGl0cyBvd24gbmFtZXNwYWNlLCBzbyB3ZSBjYW4gdXNlIGl0IGFzXG5cdC8vIGEgcHJlZml4IGZvciBhbnkgc3RhbmRhcmQgZXZlbnRzIHdlIHdhbnQgdG8gZmlyZS5cblx0bmFtZXNwYWNlOiAnX2Jhc2UnLFxuXG5cdC8vIFdlIGhhdmUgYW4gaW5pdGlhbGl6YXRpb24gYm9vdHN0cmFwLiBCZWxvdyB3ZSdsbCBzZXQgdGhpbmdzIHVwIHNvIHRoYXRcblx0Ly8gdGhpcyBnZXRzIGNhbGxlZCBldmVuIHdoZW4gYW4gZXh0ZW5kaW5nIHZpZXcgc3BlY2lmaWVzIGFuIGBpbml0aWFsaXplYFxuXHQvLyBmdW5jdGlvbi5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBvcHRpb25zICkge1xuXG5cdFx0Ly8gVGhlIGZpcnN0IHRoaW5nIHdlIGRvIGlzIHRvIGFsbG93IGZvciBhIG5hbWVzcGFjZSB0byBiZSBwYXNzZWQgaW5cblx0XHQvLyBhcyBhbiBvcHRpb24gd2hlbiB0aGUgdmlldyBpcyBjb25zdHJ1Y3RlZCwgaW5zdGVhZCBvZiBmb3JjaW5nIGl0XG5cdFx0Ly8gdG8gYmUgcGFydCBvZiB0aGUgcHJvdG90eXBlIG9ubHkuXG5cdFx0aWYgKCB0eXBlb2Ygb3B0aW9ucy5uYW1lc3BhY2UgIT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0dGhpcy5uYW1lc3BhY2UgPSBvcHRpb25zLm5hbWVzcGFjZTtcblx0XHR9XG5cblx0XHRpZiAoIHR5cGVvZiBvcHRpb25zLnJlYWN0aW9uICE9PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdHRoaXMucmVhY3Rpb24gPSBvcHRpb25zLnJlYWN0aW9uO1xuXHRcdH1cblxuXHRcdC8vIE9uY2UgdGhpbmdzIGFyZSBzZXQgdXAsIHdlIGNhbGwgdGhlIGV4dGVuZGluZyB2aWV3J3MgYGluaXRpYWxpemVgXG5cdFx0Ly8gZnVuY3Rpb24uIEl0IGlzIG1hcHBlZCB0byBgX2luaXRpYWxpemVgIG9uIHRoZSBjdXJyZW50IG9iamVjdC5cblx0XHR0aGlzLl9fY2hpbGRfXy5pbml0aWFsaXplLmFwcGx5KCB0aGlzLCBhcmd1bWVudHMgKTtcblxuXHRcdC8vIEZpbmFsbHksIHdlIHRyaWdnZXIgYW4gYWN0aW9uIHRvIGxldCB0aGUgd2hvbGUgYXBwIGtub3cgd2UganVzdFxuXHRcdC8vIGNyZWF0ZWQgdGhpcyB2aWV3LlxuXHRcdGhvb2tzLnRyaWdnZXIoIHRoaXMubmFtZXNwYWNlICsgJzp2aWV3OmluaXQnLCB0aGlzICk7XG5cdH1cblxufSwgeyBleHRlbmQ6IGV4dGVuZCB9ICk7XG5cbm1vZHVsZS5leHBvcnRzID0gQmFzZTtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0RmllbGRzID0gd3Aud29yZHBvaW50cy5ob29rcy5GaWVsZHMsXG5cdFJlYWN0b3JzID0gd3Aud29yZHBvaW50cy5ob29rcy5SZWFjdG9ycyxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0JCA9IEJhY2tib25lLiQsXG5cdGwxMG4gPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcubDEwbixcblx0ZGF0YSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5kYXRhLFxuXHRSZWFjdGlvbjtcblxuLy8gVGhlIERPTSBlbGVtZW50IGZvciBhIHJlYWN0aW9uLi4uXG5SZWFjdGlvbiA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdyZWFjdGlvbicsXG5cblx0Y2xhc3NOYW1lOiAnd29yZHBvaW50cy1ob29rLXJlYWN0aW9uJyxcblxuXHR0ZW1wbGF0ZTogd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSggJ2hvb2stcmVhY3Rpb24nICksXG5cblx0Ly8gVGhlIERPTSBldmVudHMgc3BlY2lmaWMgdG8gYW4gaXRlbS5cblx0ZXZlbnRzOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgZXZlbnRzID0ge1xuXHRcdFx0J2NsaWNrIC5hY3Rpb25zIC5kZWxldGUnOiAnY29uZmlybURlbGV0ZScsXG5cdFx0XHQnY2xpY2sgLnNhdmUnOiAgICAgICAgICAgICdzYXZlJyxcblx0XHRcdCdjbGljayAuY2FuY2VsJzogICAgICAgICAgJ2NhbmNlbCcsXG5cdFx0XHQnY2xpY2sgLmNsb3NlJzogICAgICAgICAgICdjbG9zZScsXG5cdFx0XHQnY2xpY2sgLmVkaXQnOiAgICAgICAgICAgICdlZGl0Jyxcblx0XHRcdCdjaGFuZ2UgLmZpZWxkcyAqJzogICAgICAgJ2xvY2tPcGVuJ1xuXHRcdH07XG5cblx0XHQvKlxuXHRcdCAqIFVzZSBmZWF0dXJlIGRldGVjdGlvbiB0byBkZXRlcm1pbmUgd2hldGhlciB3ZSBzaG91bGQgdXNlIHRoZSBgaW5wdXRgXG5cdFx0ICogZXZlbnQuIElucHV0IGlzIHByZWZlcnJlZCBidXQgbGFja3Mgc3VwcG9ydCBpbiBsZWdhY3kgYnJvd3NlcnMuXG5cdFx0ICovXG5cdFx0aWYgKCAnb25pbnB1dCcgaW4gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2lucHV0JyApICkge1xuXHRcdFx0ZXZlbnRzWydpbnB1dCBpbnB1dCddID0gJ2xvY2tPcGVuJztcblx0XHR9IGVsc2Uge1xuXHRcdFx0ZXZlbnRzWydrZXl1cCBpbnB1dCddID0gJ21heWJlTG9ja09wZW4nO1xuXHRcdH1cblxuXHRcdHJldHVybiBldmVudHM7XG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2NoYW5nZTpkZXNjcmlwdGlvbicsIHRoaXMucmVuZGVyRGVzY3JpcHRpb24gKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnY2hhbmdlOnJlYWN0b3InLCB0aGlzLnNldFJlYWN0b3IgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnY2hhbmdlOnJlYWN0b3InLCB0aGlzLnJlbmRlclRhcmdldCApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdkZXN0cm95JywgdGhpcy5yZW1vdmUgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnc3luYycsIHRoaXMuc2hvd1N1Y2Nlc3MgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnZXJyb3InLCB0aGlzLnNob3dBamF4RXJyb3JzICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2ludmFsaWQnLCB0aGlzLnNob3dWYWxpZGF0aW9uRXJyb3JzICk7XG5cblx0XHR0aGlzLm9uKCAncmVuZGVyOnNldHRpbmdzJywgdGhpcy5yZW5kZXJUYXJnZXQgKTtcblxuXHRcdHRoaXMuc2V0UmVhY3RvcigpO1xuXHR9LFxuXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbCggdGhpcy50ZW1wbGF0ZSgpICk7XG5cblx0XHR0aGlzLiR0aXRsZSAgICA9IHRoaXMuJCggJy50aXRsZScgKTtcblx0XHR0aGlzLiRmaWVsZHMgICA9IHRoaXMuJCggJy5maWVsZHMnICk7XG5cdFx0dGhpcy4kc2V0dGluZ3MgPSB0aGlzLiRmaWVsZHMuZmluZCggJy5zZXR0aW5ncycgKTtcblx0XHR0aGlzLiR0YXJnZXQgICA9IHRoaXMuJGZpZWxkcy5maW5kKCAnLnRhcmdldCcgKTtcblxuXHRcdHRoaXMucmVuZGVyRGVzY3JpcHRpb24oKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcicsIHRoaXMgKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdC8vIFJlLXJlbmRlciB0aGUgdGl0bGUgb2YgdGhlIGhvb2suXG5cdHJlbmRlckRlc2NyaXB0aW9uOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiR0aXRsZS50ZXh0KCB0aGlzLm1vZGVsLmdldCggJ2Rlc2NyaXB0aW9uJyApICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXI6dGl0bGUnLCB0aGlzICk7XG5cdH0sXG5cblx0cmVuZGVyRmllbGRzOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgY3VycmVudEFjdGlvblR5cGUgPSB0aGlzLmdldEN1cnJlbnRBY3Rpb25UeXBlKCk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXI6c2V0dGluZ3MnLCB0aGlzLiRzZXR0aW5ncywgY3VycmVudEFjdGlvblR5cGUsIHRoaXMgKTtcblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXI6ZmllbGRzJywgdGhpcy4kZmllbGRzLCBjdXJyZW50QWN0aW9uVHlwZSwgdGhpcyApO1xuXG5cdFx0dGhpcy5yZW5kZXJlZEZpZWxkcyA9IHRydWU7XG5cdH0sXG5cblx0cmVuZGVyVGFyZ2V0OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgYXJnVHlwZXMgPSB0aGlzLlJlYWN0b3IuZ2V0KCAnYXJnX3R5cGVzJyApLFxuXHRcdFx0ZW5kO1xuXG5cdFx0Ly8gSWYgdGhlcmUgaXMganVzdCBvbmUgYXJnIHR5cGUsIHdlIGNhbiB1c2UgdGhlIGBfLndoZXJlKClgLWxpa2Ugc3ludGF4LlxuXHRcdGlmICggYXJnVHlwZXMubGVuZ3RoID09PSAxICkge1xuXG5cdFx0XHRlbmQgPSB7IF9jYW5vbmljYWw6IGFyZ1R5cGVzWzBdLCBfdHlwZTogJ2VudGl0eScgfTtcblxuXHRcdH0gZWxzZSB7XG5cblx0XHRcdC8vIE90aGVyd2lzZSwgd2UnbGwgYmUgbmVlZCBvdXIgb3duIGZ1bmN0aW9uLCBmb3IgYF8uZmlsdGVyKClgLlxuXHRcdFx0ZW5kID0gZnVuY3Rpb24gKCBhcmcgKSB7XG5cdFx0XHRcdHJldHVybiAoXG5cdFx0XHRcdFx0YXJnLmdldCggJ190eXBlJyApID09PSAnZW50aXR5J1xuXHRcdFx0XHRcdCYmIF8uY29udGFpbnMoIGFyZ1R5cGVzLCBhcmcuZ2V0KCAnX2Nhbm9uaWNhbCcgKSApXG5cdFx0XHRcdCk7XG5cdFx0XHR9O1xuXHRcdH1cblxuXHRcdHZhciBoaWVyYXJjaGllcyA9IEFyZ3MuZ2V0SGllcmFyY2hpZXNNYXRjaGluZygge1xuXHRcdFx0ZXZlbnQ6IHRoaXMubW9kZWwuZ2V0KCAnZXZlbnQnICksXG5cdFx0XHRlbmQ6IGVuZFxuXHRcdH0gKTtcblxuXHRcdHZhciBvcHRpb25zID0gW107XG5cblx0XHRfLmVhY2goIGhpZXJhcmNoaWVzLCBmdW5jdGlvbiAoIGhpZXJhcmNoeSApIHtcblx0XHRcdG9wdGlvbnMucHVzaCgge1xuXHRcdFx0XHRsYWJlbDogQXJncy5idWlsZEhpZXJhcmNoeUh1bWFuSWQoIGhpZXJhcmNoeSApLFxuXHRcdFx0XHR2YWx1ZTogXy5wbHVjayggXy5wbHVjayggaGllcmFyY2h5LCAnYXR0cmlidXRlcycgKSwgJ3NsdWcnICkuam9pbiggJywnIClcblx0XHRcdH0gKTtcblx0XHR9KTtcblxuXHRcdHZhciB2YWx1ZSA9IHRoaXMubW9kZWwuZ2V0KCAndGFyZ2V0JyApO1xuXG5cdFx0aWYgKCBfLmlzQXJyYXkoIHZhbHVlICkgKSB7XG5cdFx0XHR2YWx1ZSA9IHZhbHVlLmpvaW4oICcsJyApO1xuXHRcdH1cblxuXHRcdHZhciBsYWJlbCA9IHRoaXMuUmVhY3Rvci5nZXQoICd0YXJnZXRfbGFiZWwnICk7XG5cblx0XHRpZiAoICEgbGFiZWwgKSB7XG5cdFx0XHRsYWJlbCA9IGwxMG4udGFyZ2V0X2xhYmVsO1xuXHRcdH1cblxuXHRcdGlmICggISB0aGlzLm1vZGVsLmlzTmV3KCkgKSB7XG5cdFx0XHRsYWJlbCArPSAnICcgKyBsMTBuLmNhbm5vdEJlQ2hhbmdlZDtcblx0XHR9XG5cblx0XHR2YXIgZmllbGQgPSBGaWVsZHMuY3JlYXRlKFxuXHRcdFx0J3RhcmdldCdcblx0XHRcdCwgdmFsdWVcblx0XHRcdCwge1xuXHRcdFx0XHR0eXBlOiAnc2VsZWN0Jyxcblx0XHRcdFx0b3B0aW9uczogb3B0aW9ucyxcblx0XHRcdFx0bGFiZWw6IGxhYmVsXG5cdFx0XHR9XG5cdFx0KTtcblxuXHRcdHRoaXMuJHRhcmdldC5odG1sKCBmaWVsZCApO1xuXG5cdFx0aWYgKCAhIHRoaXMubW9kZWwuaXNOZXcoKSApIHtcblx0XHRcdHRoaXMuJHRhcmdldC5maW5kKCAnc2VsZWN0JyApLnByb3AoICdkaXNhYmxlZCcsIHRydWUgKTtcblx0XHR9XG5cdH0sXG5cblx0c2V0UmVhY3RvcjogZnVuY3Rpb24gKCkge1xuXHRcdHRoaXMuUmVhY3RvciA9IFJlYWN0b3JzLmdldCggdGhpcy5tb2RlbC5nZXQoICdyZWFjdG9yJyApICk7XG5cdH0sXG5cblx0Ly8gR2V0IHRoZSBjdXJyZW50IGFjdGlvbiB0eXBlIHRoYXQgc2V0dGluZ3MgYXJlIGJlaW5nIGRpc3BsYXllZCBmb3IuXG5cdC8vIFJpZ2h0IG5vdyB3ZSBqdXN0IGRlZmF1bHQgdGhpcyB0byB0aGUgZmlyc3QgYWN0aW9uIHR5cGUgdGhhdCB0aGUgcmVhY3RvclxuXHQvLyBzdXBwb3J0cyB3aGljaCBpcyByZWdpc3RlcmVkIGZvciB0aGlzIGV2ZW50LlxuXHRnZXRDdXJyZW50QWN0aW9uVHlwZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGV2ZW50QWN0aW9uVHlwZXMgPSBkYXRhLmV2ZW50X2FjdGlvbl90eXBlc1sgdGhpcy5tb2RlbC5nZXQoICdldmVudCcgKSBdO1xuXG5cdFx0aWYgKCAhIGV2ZW50QWN0aW9uVHlwZXMgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dmFyIHJlYWN0b3JBY3Rpb25UeXBlcyA9IHRoaXMuUmVhY3Rvci5nZXQoICdhY3Rpb25fdHlwZXMnICk7XG5cblx0XHQvLyBXZSBsb29wIHRocm91Z2ggdGhlIHJlYWN0b3IgYWN0aW9uIHR5cGVzIGFzIHRoZSBwcmltYXJ5IGxpc3QsIGJlY2F1c2UgaXRcblx0XHQvLyBpcyBpbiBvcmRlciwgd2hpbGUgdGhlIGV2ZW50IGFjdGlvbiB0eXBlcyBpc24ndCBpbiBhbnkgcGFydGljdWxhciBvcmRlci5cblx0XHQvLyBPdGhlcndpc2Ugd2UnZCBlbmQgdXAgc2VsZWN0aW5nIHRoZSBhY3Rpb24gdHlwZXMgaW5jb25zaXN0ZW50bHkuXG5cdFx0Zm9yICggdmFyIGkgPSAwOyBpIDwgcmVhY3RvckFjdGlvblR5cGVzLmxlbmd0aDsgaSsrICkge1xuXHRcdFx0aWYgKCBldmVudEFjdGlvblR5cGVzWyByZWFjdG9yQWN0aW9uVHlwZXNbIGkgXSBdICkge1xuXHRcdFx0XHRyZXR1cm4gcmVhY3RvckFjdGlvblR5cGVzWyBpIF07XG5cdFx0XHR9XG5cdFx0fVxuXHR9LFxuXG5cdC8vIFRvZ2dsZSB0aGUgdmlzaWJpbGl0eSBvZiB0aGUgZm9ybS5cblx0ZWRpdDogZnVuY3Rpb24gKCkge1xuXG5cdFx0aWYgKCAhIHRoaXMucmVuZGVyZWRGaWVsZHMgKSB7XG5cdFx0XHR0aGlzLnJlbmRlckZpZWxkcygpO1xuXHRcdH1cblxuXHRcdC8vIFRoZW4gZGlzcGxheSB0aGUgZm9ybS5cblx0XHR0aGlzLiRmaWVsZHMuc2xpZGVEb3duKCAnZmFzdCcgKTtcblx0XHR0aGlzLiRlbC5hZGRDbGFzcyggJ2VkaXRpbmcnICk7XG5cdH0sXG5cblx0Ly8gQ2xvc2UgdGhlIGZvcm0uXG5cdGNsb3NlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRmaWVsZHMuc2xpZGVVcCggJ2Zhc3QnICk7XG5cdFx0dGhpcy4kZWwucmVtb3ZlQ2xhc3MoICdlZGl0aW5nJyApO1xuXHRcdHRoaXMuJCggJy5zdWNjZXNzJyApLmhpZGUoKTtcblx0fSxcblxuXHQvLyBNYXliZSBsb2NrIHRoZSBmb3JtIG9wZW4gd2hlbiBhbiBpbnB1dCBpcyBhbHRlcmVkLlxuXHRtYXliZUxvY2tPcGVuOiBmdW5jdGlvbiAoIGV2ZW50ICkge1xuXG5cdFx0dmFyICR0YXJnZXQgPSAkKCBldmVudC50YXJnZXQgKTtcblxuXHRcdHZhciBhdHRyU2x1ZyA9IEZpZWxkcy5nZXRBdHRyU2x1ZyggdGhpcy5tb2RlbCwgJHRhcmdldC5hdHRyKCAnbmFtZScgKSApO1xuXG5cdFx0aWYgKCAkdGFyZ2V0LnZhbCgpICE9PSB0aGlzLm1vZGVsLmdldCggYXR0clNsdWcgKSArICcnICkge1xuXHRcdFx0dGhpcy5sb2NrT3BlbigpO1xuXHRcdH1cblx0fSxcblxuXHQvLyBMb2NrIHRoZSBmb3JtIG9wZW4gd2hlbiB0aGUgZm9ybSB2YWx1ZXMgaGF2ZSBiZWVuIGNoYW5nZWQuXG5cdGxvY2tPcGVuOiBmdW5jdGlvbiAoKSB7XG5cblx0XHRpZiAoIHRoaXMuY2FuY2VsbGluZyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aGlzLiRlbC5hZGRDbGFzcyggJ2NoYW5nZWQnICk7XG5cdFx0dGhpcy4kKCAnLnNhdmUnICkucHJvcCggJ2Rpc2FibGVkJywgZmFsc2UgKTtcblx0XHR0aGlzLiQoICcuc3VjY2VzcycgKS5mYWRlT3V0KCk7XG5cdH0sXG5cblx0Ly8gQ2FuY2VsIGVkaXRpbmcgb3IgYWRkaW5nIGEgbmV3IHJlYWN0aW9uLlxuXHRjYW5jZWw6IGZ1bmN0aW9uICgpIHtcblxuXHRcdGlmICggdGhpcy4kZWwuaGFzQ2xhc3MoICduZXcnICkgKSB7XG5cblx0XHRcdHRoaXMubW9kZWwuY29sbGVjdGlvbi50cmlnZ2VyKCAnY2FuY2VsLWFkZC1uZXcnICk7XG5cdFx0XHR0aGlzLnJlbW92ZSgpO1xuXG5cdFx0XHR3cC5hMTF5LnNwZWFrKCBsMTBuLmRpc2NhcmRlZFJlYWN0aW9uICk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aGlzLiRlbC5yZW1vdmVDbGFzcyggJ2NoYW5nZWQnICk7XG5cdFx0dGhpcy4kKCAnLnNhdmUnICkucHJvcCggJ2Rpc2FibGVkJywgdHJ1ZSApO1xuXG5cdFx0dGhpcy5jYW5jZWxsaW5nID0gdHJ1ZTtcblxuXHRcdHRoaXMucmVuZGVyRmllbGRzKCk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdjYW5jZWwnICk7XG5cblx0XHR3cC5hMTF5LnNwZWFrKCBsMTBuLmRpc2NhcmRlZENoYW5nZXMgKTtcblxuXHRcdHRoaXMuY2FuY2VsbGluZyA9IGZhbHNlO1xuXHR9LFxuXG5cdC8vIFNhdmUgY2hhbmdlcyB0byB0aGUgcmVhY3Rpb24uXG5cdHNhdmU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMud2FpdCgpO1xuXHRcdHRoaXMuJCggJy5zYXZlJyApLnByb3AoICdkaXNhYmxlZCcsIHRydWUgKTtcblxuXHRcdHdwLmExMXkuc3BlYWsoIGwxMG4uc2F2aW5nICk7XG5cblx0XHR2YXIgZm9ybURhdGEgPSBGaWVsZHMuZ2V0Rm9ybURhdGEoIHRoaXMubW9kZWwsIHRoaXMuJGZpZWxkcyApO1xuXG5cdFx0aWYgKCBmb3JtRGF0YS50YXJnZXQgKSB7XG5cdFx0XHRmb3JtRGF0YS50YXJnZXQgPSBmb3JtRGF0YS50YXJnZXQuc3BsaXQoICcsJyApO1xuXHRcdH1cblxuXHRcdHRoaXMubW9kZWwuc2F2ZSggZm9ybURhdGEsIHsgd2FpdDogdHJ1ZSwgcmF3QXR0czogZm9ybURhdGEgfSApO1xuXHR9LFxuXG5cdC8vIERpc3BsYXkgYSBzcGlubmVyIHdoaWxlIGNoYW5nZXMgYXJlIGJlaW5nIHNhdmVkLlxuXHR3YWl0OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiQoICcuc3Bpbm5lci1vdmVybGF5JyApLnNob3coKTtcblx0XHR0aGlzLiQoICcuZXJyJyApLnNsaWRlVXAoKTtcblx0fSxcblxuXHQvLyBDb25maXJtIHRoYXQgYSByZWFjdGlvbiBpcyBpbnRlbmRlZCB0byBiZSBkZWxldGVkIGJlZm9yZSBkZWxldGluZyBpdC5cblx0Y29uZmlybURlbGV0ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyICRkaWFsb2cgPSAkKCAnPGRpdj48cD48L3A+PC9kaXY+JyApLFxuXHRcdFx0dmlldyA9IHRoaXM7XG5cblx0XHR0aGlzLiQoICcubWVzc2FnZXMgZGl2JyApLnNsaWRlVXAoKTtcblxuXHRcdCRkaWFsb2dcblx0XHRcdC5hdHRyKCAndGl0bGUnLCBsMTBuLmNvbmZpcm1UaXRsZSApXG5cdFx0XHQuZmluZCggJ3AnIClcblx0XHRcdFx0LnRleHQoIGwxMG4uY29uZmlybURlbGV0ZSApXG5cdFx0XHQuZW5kKClcblx0XHRcdC5kaWFsb2coe1xuXHRcdFx0XHRkaWFsb2dDbGFzczogJ3dwLWRpYWxvZyB3b3JkcG9pbnRzLWRlbGV0ZS1ob29rLXJlYWN0aW9uLWRpYWxvZycsXG5cdFx0XHRcdHJlc2l6YWJsZTogZmFsc2UsXG5cdFx0XHRcdGRyYWdnYWJsZTogZmFsc2UsXG5cdFx0XHRcdGhlaWdodDogMjUwLFxuXHRcdFx0XHRtb2RhbDogdHJ1ZSxcblx0XHRcdFx0YnV0dG9uczogW1xuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdHRleHQ6IGwxMG4uY2FuY2VsVGV4dCxcblx0XHRcdFx0XHRcdCdjbGFzcyc6ICdidXR0b24tc2Vjb25kYXJ5Jyxcblx0XHRcdFx0XHRcdGNsaWNrOiBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHRcdFx0JCggdGhpcyApLmRpYWxvZyggJ2Rlc3Ryb3knICk7XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHR0ZXh0OiBsMTBuLmRlbGV0ZVRleHQsXG5cdFx0XHRcdFx0XHQnY2xhc3MnOiAnYnV0dG9uLXByaW1hcnknLFxuXHRcdFx0XHRcdFx0Y2xpY2s6IGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRcdFx0XHQkKCB0aGlzICkuZGlhbG9nKCAnZGVzdHJveScgKTtcblx0XHRcdFx0XHRcdFx0dmlldy5kZXN0cm95KCk7XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRdXG5cdFx0XHR9KTtcblx0fSxcblxuXHQvLyBSZW1vdmUgdGhlIGl0ZW0sIGRlc3Ryb3kgdGhlIG1vZGVsLlxuXHRkZXN0cm95OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR3cC5hMTF5LnNwZWFrKCBsMTBuLmRlbGV0aW5nICk7XG5cblx0XHR0aGlzLndhaXQoKTtcblxuXHRcdHRoaXMubW9kZWwuZGVzdHJveShcblx0XHRcdHtcblx0XHRcdFx0d2FpdDogdHJ1ZSxcblx0XHRcdFx0c3VjY2VzczogZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRcdHdwLmExMXkuc3BlYWsoIGwxMG4ucmVhY3Rpb25EZWxldGVkICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHQpO1xuXHR9LFxuXG5cdC8vIERpc3BsYXkgZXJyb3JzIHdoZW4gdGhlIG1vZGVsIGhhcyBpbnZhbGlkIGZpZWxkcy5cblx0c2hvd1ZhbGlkYXRpb25FcnJvcnM6IGZ1bmN0aW9uICggbW9kZWwsIGVycm9ycyApIHtcblx0XHR0aGlzLnNob3dFcnJvciggZXJyb3JzICk7XG5cdH0sXG5cblx0Ly8gRGlzcGxheSBhbiBlcnJvciB3aGVuIHRoZXJlIGlzIGFuIEFqYXggZmFpbHVyZS5cblx0c2hvd0FqYXhFcnJvcnM6IGZ1bmN0aW9uICggZXZlbnQsIHJlc3BvbnNlICkge1xuXG5cdFx0dmFyIGVycm9ycztcblxuXHRcdGlmICggISBfLmlzRW1wdHkoIHJlc3BvbnNlLmVycm9ycyApICkge1xuXHRcdFx0ZXJyb3JzID0gcmVzcG9uc2UuZXJyb3JzO1xuXHRcdH0gZWxzZSBpZiAoIHJlc3BvbnNlLm1lc3NhZ2UgKSB7XG5cdFx0XHRlcnJvcnMgPSByZXNwb25zZS5tZXNzYWdlO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRlcnJvcnMgPSBsMTBuLnVuZXhwZWN0ZWRFcnJvcjtcblx0XHR9XG5cblx0XHR0aGlzLnNob3dFcnJvciggZXJyb3JzICk7XG5cdH0sXG5cblx0c2hvd0Vycm9yOiBmdW5jdGlvbiAoIGVycm9ycyApIHtcblxuXHRcdHZhciBnZW5lcmFsRXJyb3JzID0gW107XG5cdFx0dmFyIGExMXlFcnJvcnMgPSBbXTtcblx0XHR2YXIgJGVycm9ycyA9IHRoaXMuJCggJy5tZXNzYWdlcyAuZXJyJyApO1xuXG5cdFx0dGhpcy4kKCAnLnNwaW5uZXItb3ZlcmxheScgKS5oaWRlKCk7XG5cblx0XHQvLyBTb21ldGltZXMgd2UgZ2V0IGEgbGlzdCBvZiBlcnJvcnMuXG5cdFx0aWYgKCBfLmlzQXJyYXkoIGVycm9ycyApICkge1xuXG5cdFx0XHQvLyBXaGVuIHRoYXQgaGFwcGVucywgd2UgbG9vcCBvdmVyIHRoZW0gYW5kIHRyeSB0byBkaXNwbGF5IGVhY2ggb2Zcblx0XHRcdC8vIHRoZW0gbmV4dCB0byB0aGVpciBhc3NvY2lhdGVkIGZpZWxkLlxuXHRcdFx0Xy5lYWNoKCBlcnJvcnMsIGZ1bmN0aW9uICggZXJyb3IgKSB7XG5cblx0XHRcdFx0dmFyICRmaWVsZCwgZXNjYXBlZEZpZWxkTmFtZTtcblxuXHRcdFx0XHQvLyBTb21ldGltZXMgc29tZSBvZiB0aGUgZXJyb3JzIGFyZW4ndCBmb3IgYW55IHBhcnRpY3VsYXIgZmllbGRcblx0XHRcdFx0Ly8gdGhvdWdoLCBzbyB3ZSBjb2xsZWN0IHRoZW0gaW4gYW4gYXJyYXkgYW4gZGlzcGxheSB0aGVtIGFsbFxuXHRcdFx0XHQvLyB0b2dldGhlciBhIGJpdCBsYXRlci5cblx0XHRcdFx0aWYgKCAhIGVycm9yLmZpZWxkICkge1xuXHRcdFx0XHRcdGdlbmVyYWxFcnJvcnMucHVzaCggZXJyb3IubWVzc2FnZSApO1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGVzY2FwZWRGaWVsZE5hbWUgPSBGaWVsZHMuZ2V0RmllbGROYW1lKCBlcnJvci5maWVsZCApXG5cdFx0XHRcdFx0XHQucmVwbGFjZSggL1teYS16MC05LV9cXFtcXF1cXHt9XFxcXF0vZ2ksICcnIClcblx0XHRcdFx0XHRcdC5yZXBsYWNlKCAvXFxcXC9nLCAnXFxcXFxcXFwnICk7XG5cblx0XHRcdFx0Ly8gV2hlbiBhIGZpZWxkIGlzIHNwZWNpZmllZCwgd2UgdHJ5IHRvIGxvY2F0ZSBpdC5cblx0XHRcdFx0JGZpZWxkID0gdGhpcy4kKCAnW25hbWU9XCInICsgZXNjYXBlZEZpZWxkTmFtZSArICdcIl0nICk7XG5cblx0XHRcdFx0aWYgKCAwID09PSAkZmllbGQubGVuZ3RoICkge1xuXG5cdFx0XHRcdFx0Ly8gSG93ZXZlciwgdGhlcmUgYXJlIHRpbWVzIHdoZW4gdGhlIGVycm9yIGlzIGZvciBhIGZpZWxkIHNldFxuXHRcdFx0XHRcdC8vIGFuZCBub3QgYSBzaW5nbGUgZmllbGQuIEluIHRoYXQgY2FzZSwgd2UgdHJ5IHRvIGZpbmQgdGhlXG5cdFx0XHRcdFx0Ly8gZmllbGRzIGluIHRoYXQgc2V0LlxuXHRcdFx0XHRcdCRmaWVsZCA9IHRoaXMuJCggJ1tuYW1lXj1cIicgKyBlc2NhcGVkRmllbGROYW1lICsgJ1wiXScgKTtcblxuXHRcdFx0XHRcdC8vIElmIHRoYXQgZmFpbHMsIHdlIGp1c3QgYWRkIHRoaXMgdG8gdGhlIGdlbmVyYWwgZXJyb3JzLlxuXHRcdFx0XHRcdGlmICggMCA9PT0gJGZpZWxkLmxlbmd0aCApIHtcblx0XHRcdFx0XHRcdGdlbmVyYWxFcnJvcnMucHVzaCggZXJyb3IubWVzc2FnZSApO1xuXHRcdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdCRmaWVsZCA9ICRmaWVsZC5maXJzdCgpO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0JGZpZWxkLmJlZm9yZShcblx0XHRcdFx0XHQkKCAnPGRpdiBjbGFzcz1cIm1lc3NhZ2UgZXJyXCI+PC9kaXY+JyApLnRleHQoIGVycm9yLm1lc3NhZ2UgKVxuXHRcdFx0XHQpO1xuXG5cdFx0XHRcdGExMXlFcnJvcnMucHVzaCggZXJyb3IubWVzc2FnZSApO1xuXG5cdFx0XHR9LCB0aGlzICk7XG5cblx0XHRcdCRlcnJvcnMuaHRtbCggJycgKTtcblxuXHRcdFx0Ly8gVGhlcmUgbWF5IGJlIHNvbWUgZ2VuZXJhbCBlcnJvcnMgdGhhdCB3ZSBuZWVkIHRvIGRpc3BsYXkgdG8gdGhlIHVzZXIuXG5cdFx0XHQvLyBXZSBhbHNvIGFkZCBhbiBleHBsYW5hdGlvbiB0aGF0IHRoZXJlIGFyZSBzb21lIGZpZWxkcyB0aGF0IG5lZWQgdG8gYmVcblx0XHRcdC8vIGNvcnJlY3RlZCwgaWYgdGhlcmUgd2VyZSBzb21lIHBlci1maWVsZCBlcnJvcnMsIHRvIG1ha2Ugc3VyZSB0aGF0IHRoZXlcblx0XHRcdC8vIHNlZSB0aG9zZSBlcnJvcnMgYXMgd2VsbCAoc2luY2UgdGhleSBtYXkgbm90IGJlIGluIHZpZXcpLlxuXHRcdFx0aWYgKCBnZW5lcmFsRXJyb3JzLmxlbmd0aCA8IGVycm9ycy5sZW5ndGggKSB7XG5cdFx0XHRcdGdlbmVyYWxFcnJvcnMudW5zaGlmdCggbDEwbi5maWVsZHNJbnZhbGlkICk7XG5cdFx0XHR9XG5cblx0XHRcdF8uZWFjaCggZ2VuZXJhbEVycm9ycywgZnVuY3Rpb24gKCBlcnJvciApIHtcblx0XHRcdFx0JGVycm9ycy5hcHBlbmQoICQoICc8cD48L3A+JyApLnRleHQoIGVycm9yICkgKTtcblx0XHRcdH0pO1xuXG5cdFx0XHQvLyBOb3RpZnkgdW5zaWdodGVkIHVzZXJzIGFzIHdlbGwuXG5cdFx0XHRhMTF5RXJyb3JzLnVuc2hpZnQoIGwxMG4uZmllbGRzSW52YWxpZCApO1xuXG5cdFx0XHR3cC5hMTF5LnNwZWFrKCBhMTF5RXJyb3JzLmpvaW4oICcgJyApICk7XG5cblx0XHR9IGVsc2Uge1xuXG5cdFx0XHQkZXJyb3JzLnRleHQoIGVycm9ycyApO1xuXHRcdFx0d3AuYTExeS5zcGVhayggZXJyb3JzICk7XG5cdFx0fVxuXG5cdFx0JGVycm9ycy5mYWRlSW4oKTtcblx0fSxcblxuXHQvLyBEaXNwbGF5IGEgc3VjY2VzcyBtZXNzYWdlLlxuXHRzaG93U3VjY2VzczogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kKCAnLnNwaW5uZXItb3ZlcmxheScgKS5oaWRlKCk7XG5cblx0XHR0aGlzLiQoICcuc3VjY2VzcycgKVxuXHRcdFx0LnRleHQoIGwxMG4uY2hhbmdlc1NhdmVkIClcblx0XHRcdC5zbGlkZURvd24oKTtcblxuXHRcdHdwLmExMXkuc3BlYWsoIGwxMG4ucmVhY3Rpb25TYXZlZCApO1xuXG5cdFx0dGhpcy4kdGFyZ2V0LmZpbmQoICdzZWxlY3QnICkucHJvcCggJ2Rpc2FibGVkJywgdHJ1ZSApO1xuXG5cdFx0dGhpcy4kZWwucmVtb3ZlQ2xhc3MoICduZXcgY2hhbmdlZCcgKTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gUmVhY3Rpb247XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Ib29rc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHRSZWFjdGlvblZpZXcgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuUmVhY3Rpb24sXG5cdFJlYWN0aW9uID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5SZWFjdGlvbixcblx0UmVhY3Rpb25zO1xuXG5SZWFjdGlvbnMgPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAncmVhY3Rpb25zJyxcblxuXHQvLyBEZWxlZ2F0ZWQgZXZlbnRzIGZvciBjcmVhdGluZyBuZXcgcmVhY3Rpb25zLlxuXHRldmVudHM6IHtcblx0XHQnY2xpY2sgLmFkZC1yZWFjdGlvbic6ICdpbml0QWRkUmVhY3Rpb24nXG5cdH0sXG5cblx0Ly8gQXQgaW5pdGlhbGl6YXRpb24gd2UgYmluZCB0byB0aGUgcmVsZXZhbnQgZXZlbnRzIG9uIHRoZSBgUmVhY3Rpb25zYFxuXHQvLyBjb2xsZWN0aW9uLCB3aGVuIGl0ZW1zIGFyZSBhZGRlZCBvciBjaGFuZ2VkLiBLaWNrIHRoaW5ncyBvZmYgYnlcblx0Ly8gbG9hZGluZyBhbnkgcHJlZXhpc3RpbmcgaG9va3MgZnJvbSAqdGhlIGRhdGFiYXNlKi5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24oKSB7XG5cblx0XHR0aGlzLiRyZWFjdGlvbkdyb3VwID0gdGhpcy4kKCAnLndvcmRwb2ludHMtaG9vay1yZWFjdGlvbi1ncm91cCcgKTtcblx0XHR0aGlzLiRhZGRSZWFjdGlvbiAgID0gdGhpcy4kKCAnLmFkZC1yZWFjdGlvbicgKTtcblx0XHR0aGlzLiRldmVudHMgPSB0aGlzLiQoICcud29yZHBvaW50cy1ob29rLWV2ZW50cycgKTtcblxuXHRcdGlmICggdGhpcy4kZXZlbnRzLmxlbmd0aCAhPT0gMCApIHtcblx0XHRcdC8vIENoZWNrIGhvdyBtYW55IGRpZmZlcmVudCBldmVudHMgdGhpcyBncm91cCBzdXBwb3J0cy4gSWYgaXQgaXMgb25seVxuXHRcdFx0Ly8gb25lLCB3ZSBjYW4gaGlkZSB0aGUgZXZlbnQgc2VsZWN0b3IuXG5cdFx0XHRpZiAoIDIgPT09IHRoaXMuJGV2ZW50cy5jaGlsZHJlbiggJ29wdGlvbicgKS5sZW5ndGggKSB7XG5cdFx0XHRcdHRoaXMuJGV2ZW50cy5wcm9wKCAnc2VsZWN0ZWRJbmRleCcsIDEgKS5oaWRlKCk7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0Ly8gTWFrZSBzdXJlIHRoYXQgdGhlIGFkZCByZWFjdGlvbiBidXR0b24gaXNuJ3QgZGlzYWJsZWQsIGJlY2F1c2Ugc29tZXRpbWVzXG5cdFx0Ly8gdGhlIGJyb3dzZXIgd2lsbCBhdXRvbWF0aWNhbGx5IGRpc2FibGUgaXQsIGUuZy4sIGlmIGl0IHdhcyBkaXNhYmxlZFxuXHRcdC8vIGFuZCB0aGUgcGFnZSB3YXMgcmVmcmVzaGVkLlxuXHRcdHRoaXMuJGFkZFJlYWN0aW9uLnByb3AoICdkaXNhYmxlZCcsIGZhbHNlICk7XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnYWRkJywgdGhpcy5hZGRPbmUgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAncmVzZXQnLCB0aGlzLmFkZEFsbCApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdjYW5jZWwtYWRkLW5ldycsIHRoaXMuY2FuY2VsQWRkUmVhY3Rpb24gKTtcblxuXHRcdHRoaXMuYWRkQWxsKCk7XG5cdH0sXG5cblx0Ly8gQWRkIGEgc2luZ2xlIHJlYWN0aW9uIHRvIHRoZSBncm91cCBieSBjcmVhdGluZyBhIHZpZXcgZm9yIGl0LCBhbmQgYXBwZW5kaW5nXG5cdC8vIGl0cyBlbGVtZW50IHRvIHRoZSBncm91cC4gSWYgdGhpcyBpcyBhIG5ldyByZWFjdGlvbiB3ZSBlbnRlciBlZGl0IG1vZGUgZnJvbVxuXHQvLyBhbmQgbG9jayB0aGUgdmlldyBvcGVuIHVudGlsIGl0IGlzIHNhdmVkLlxuXHRhZGRPbmU6IGZ1bmN0aW9uKCByZWFjdGlvbiApIHtcblxuXHRcdHZhciB2aWV3ID0gbmV3IFJlYWN0aW9uVmlldyggeyBtb2RlbDogcmVhY3Rpb24gfSApLFxuXHRcdFx0ZWxlbWVudCA9IHZpZXcucmVuZGVyKCkuZWw7XG5cblx0XHR2YXIgaXNOZXcgPSAnJyA9PT0gcmVhY3Rpb24uZ2V0KCAnZGVzY3JpcHRpb24nICk7XG5cblx0XHRpZiAoIGlzTmV3ICkge1xuXHRcdFx0dmlldy5lZGl0KCk7XG5cdFx0XHR2aWV3LmxvY2tPcGVuKCk7XG5cdFx0XHR2aWV3LiRlbC5hZGRDbGFzcyggJ25ldycgKTtcblx0XHR9XG5cblx0XHQvLyBBcHBlbmQgdGhlIGVsZW1lbnQgdG8gdGhlIGdyb3VwLlxuXHRcdHRoaXMuJHJlYWN0aW9uR3JvdXAuYXBwZW5kKCBlbGVtZW50ICk7XG5cblx0XHRpZiAoIGlzTmV3ICkge1xuXHRcdFx0dmlldy4kZmllbGRzLmZpbmQoICc6aW5wdXQ6dmlzaWJsZScgKS5maXJzdCgpLmZvY3VzKCk7XG5cdFx0fVxuXHR9LFxuXG5cdC8vIEFkZCBhbGwgaXRlbXMgaW4gdGhlICoqUmVhY3Rpb25zKiogY29sbGVjdGlvbiBhdCBvbmNlLlxuXHRhZGRBbGw6IGZ1bmN0aW9uKCkge1xuXHRcdHRoaXMubW9kZWwuZWFjaCggdGhpcy5hZGRPbmUsIHRoaXMgKTtcblxuXHRcdHRoaXMuJCggJy5zcGlubmVyLW92ZXJsYXknICkuZmFkZU91dCgpO1xuXHR9LFxuXG5cdGdldFJlYWN0aW9uRGVmYXVsdHM6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBkZWZhdWx0cyA9IHt9O1xuXG5cdFx0aWYgKCB0aGlzLiRldmVudHMubGVuZ3RoICE9PSAwICkge1xuXG5cdFx0XHQvLyBGaXJzdCwgYmUgc3VyZSB0aGF0IGFuIGV2ZW50IHdhcyBzZWxlY3RlZC5cblx0XHRcdHZhciBldmVudCA9IHRoaXMuJGV2ZW50cy52YWwoKTtcblxuXHRcdFx0aWYgKCAnMCcgPT09IGV2ZW50ICkge1xuXHRcdFx0XHQvLyBTaG93IGFuIGVycm9yLlxuXHRcdFx0fVxuXG5cdFx0XHRkZWZhdWx0cy5ldmVudCA9IGV2ZW50O1xuXHRcdFx0ZGVmYXVsdHMubm9uY2UgPSB0aGlzLiRldmVudHNcblx0XHRcdFx0LmZpbmQoXG5cdFx0XHRcdFx0J29wdGlvblt2YWx1ZT1cIicgKyBldmVudC5yZXBsYWNlKCAvW15hLXowLTktX10vZ2ksICcnICkgKyAnXCJdJ1xuXHRcdFx0XHQpXG5cdFx0XHRcdC5kYXRhKCAnbm9uY2UnICk7XG5cblx0XHR9IGVsc2Uge1xuXG5cdFx0XHRkZWZhdWx0cy5ldmVudCA9IHRoaXMuJHJlYWN0aW9uR3JvdXAuZGF0YSggJ3dvcmRwb2ludHMtaG9va3MtaG9vay1ldmVudCcgKTtcblx0XHRcdGRlZmF1bHRzLm5vbmNlID0gdGhpcy4kcmVhY3Rpb25Hcm91cC5kYXRhKCAnd29yZHBvaW50cy1ob29rcy1jcmVhdGUtbm9uY2UnICk7XG5cdFx0fVxuXG5cdFx0ZGVmYXVsdHMucmVhY3RvciA9IHRoaXMuJHJlYWN0aW9uR3JvdXAuZGF0YSggJ3dvcmRwb2ludHMtaG9va3MtcmVhY3RvcicgKTtcblx0XHRkZWZhdWx0cy5yZWFjdGlvbl9zdG9yZSA9IHRoaXMuJHJlYWN0aW9uR3JvdXAuZGF0YSggJ3dvcmRwb2ludHMtaG9va3MtcmVhY3Rpb24tc3RvcmUnICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdob29rLXJlYWN0aW9uLWRlZmF1bHRzJywgZGVmYXVsdHMsIHRoaXMgKTtcblxuXHRcdHJldHVybiBkZWZhdWx0cztcblx0fSxcblxuXHQvLyBTaG93IHRoZSBmb3JtIGZvciBhIG5ldyByZWFjdGlvbi5cblx0aW5pdEFkZFJlYWN0aW9uOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgZGF0YSA9IHRoaXMuZ2V0UmVhY3Rpb25EZWZhdWx0cygpO1xuXG5cdFx0dGhpcy4kYWRkUmVhY3Rpb24ucHJvcCggJ2Rpc2FibGVkJywgdHJ1ZSApO1xuXG5cdFx0dmFyIHJlYWN0aW9uID0gbmV3IFJlYWN0aW9uKCBkYXRhICk7XG5cblx0XHR0aGlzLm1vZGVsLmFkZCggWyByZWFjdGlvbiBdICk7XG5cblx0XHQvLyBSZS1lbmFibGUgdGhlIHN1Ym1pdCBidXR0b24gd2hlbiBhIG5ldyByZWFjdGlvbiBpcyBzYXZlZC5cblx0XHR0aGlzLmxpc3RlblRvT25jZSggcmVhY3Rpb24sICdzeW5jJywgZnVuY3Rpb24gKCkge1xuXHRcdFx0dGhpcy4kYWRkUmVhY3Rpb24ucHJvcCggJ2Rpc2FibGVkJywgZmFsc2UgKTtcblx0XHR9KTtcblx0fSxcblxuXHQvLyBXaGVuIGEgbmV3IHJlYWN0aW9uIGlzIHJlbW92ZWQsIHJlLWVuYWJsZSB0aGUgYWRkIHJlYWN0aW9uIGJ1dHRvbi5cblx0Y2FuY2VsQWRkUmVhY3Rpb246IGZ1bmN0aW9uICgpIHtcblx0XHR0aGlzLiRhZGRSZWFjdGlvbi5wcm9wKCAnZGlzYWJsZWQnLCBmYWxzZSApO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBSZWFjdGlvbnM7XG4iXX0=
