(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var hooks = wp.wordpoints.hooks;

// Models
hooks.model.Condition       = require( './conditions/models/condition.js' );
hooks.model.Conditions      = require( './conditions/models/conditions.js' );
hooks.model.ConditionGroup  = require( './conditions/models/condition-group.js' );
hooks.model.ConditionGroups = require( './conditions/models/condition-groups.js' );
hooks.model.ConditionType   = require( './conditions/models/condition-type.js' );
hooks.model.ConditionTypes  = require( './conditions/models/condition-types.js' );

// Views
hooks.view.Condition         = require( './conditions/views/condition.js' );
hooks.view.ConditionGroup    = require( './conditions/views/condition-group.js' );
hooks.view.ConditionSelector = require( './conditions/views/condition-selector.js' );
hooks.view.ConditionGroups   = require( './conditions/views/condition-groups.js' );

// Controllers.
hooks.extension.Conditions = require( './conditions/controllers/extension.js' );
hooks.extension.Conditions.Condition = require( './conditions/controllers/condition.js' );

var Conditions = new hooks.extension.Conditions();

// Conditions.
var Equals = require( './conditions/controllers/conditions/equals.js' );

Conditions.registerController( 'text', 'equals', Equals );
Conditions.registerController( 'entity', 'equals', Equals );
Conditions.registerController( 'entity_array', 'equals', Equals );
Conditions.registerController(
	'entity_array'
	, 'contains'
	, require( './conditions/controllers/conditions/entity-array-contains.js' )
);

// Register the extension.
hooks.Extensions.add( Conditions );

// EOF

},{"./conditions/controllers/condition.js":2,"./conditions/controllers/conditions/entity-array-contains.js":3,"./conditions/controllers/conditions/equals.js":4,"./conditions/controllers/extension.js":5,"./conditions/models/condition-group.js":6,"./conditions/models/condition-groups.js":7,"./conditions/models/condition-type.js":8,"./conditions/models/condition-types.js":9,"./conditions/models/condition.js":10,"./conditions/models/conditions.js":11,"./conditions/views/condition-group.js":12,"./conditions/views/condition-groups.js":13,"./conditions/views/condition-selector.js":14,"./conditions/views/condition.js":15}],2:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.extension.Conditions.condition.Condition
 *
 * @class
 * @augments Backbone.Model
 */

var Fields = wp.wordpoints.hooks.Fields,
	Condition;

Condition = Backbone.Model.extend({

	defaults: {
		slug: '',
		fields: []
	},

	idAttribute: 'slug',

	renderSettings: function ( condition, fieldNamePrefix ) {

		var fieldsHTML = '';

		_.each( this.get( 'fields' ), function ( setting, name ) {

			var fieldName = _.clone( fieldNamePrefix );

			fieldName.push( name );

			fieldsHTML += Fields.create(
				fieldName
				, condition.model.attributes.settings[ name ]
				, setting
			);

		}, this );

		return fieldsHTML;
	},

	validateSettings: function () {}
});

module.exports = Condition;

},{}],3:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.extension.Conditions.condition.EntityArrayContains
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.extension.Conditions.Condition
 */

var Condition = wp.wordpoints.hooks.extension.Conditions.Condition,
	ConditionGroups = wp.wordpoints.hooks.model.ConditionGroups,
	ConditionGroupsView = wp.wordpoints.hooks.view.ConditionGroups,
	Extensions = wp.wordpoints.hooks.Extensions,
	ArgsCollection = wp.wordpoints.hooks.model.Args,
	Args = wp.wordpoints.hooks.Args,
	EntityArrayContains;

EntityArrayContains = Condition.extend({

	defaults: {
		slug: 'entity_array_contains'
	},

	renderSettings: function ( condition, fieldNamePrefix ) {

		// Render the main fields.
		var fields = this.constructor.__super__.renderSettings.apply(
			this
			, [ condition, fieldNamePrefix ]
		);

		condition.$settings.append( fields );

		// Render view for sub-conditions.
		var arg = Args.getEntity(
			condition.model.getArg().get( 'entity_slug' )
		);

		condition.model.subGroups = new ConditionGroups( null, {
			args: new ArgsCollection( [ arg ] ),
			hierarchy: condition.model.getFullHierarchy().concat(
				[ '_conditions', condition.model.id, 'settings', 'conditions' ]
			),
			reaction: condition.reaction.model,
			_conditions: condition.model.get( 'settings' ).conditions
		} );

		var view = new ConditionGroupsView( {
			collection: condition.model.subGroups,
			reaction: condition.reaction
		});

		condition.$settings.append( view.render().$el );

		return '';
	},

	validateSettings: function ( condition, settings, errors ) {

		Extensions.get( 'conditions' ).validateConditions(
			[ condition.subGroups ]
			, settings.conditions
			, errors
		);
	}
});

module.exports = EntityArrayContains;

},{}],4:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.extension.Conditions.condition.Equals
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.extension.Conditions.Condition
 */

var Condition = wp.wordpoints.hooks.extension.Conditions.Condition,
	Equals;

Equals = Condition.extend({

	defaults: {
		slug: 'equals'
	},

	renderSettings: function ( condition, fieldNamePrefix ) {

		var fields = this.get( 'fields' ),
			arg = condition.model.getArg();

		// We render the `value` field differently based on the type of argument.
		if ( arg ) {

			var type = arg.get( '_type' );

			fields = _.extend( {}, fields );

			switch ( type ) {

				case 'attr':
					fields.value = _.extend(
						{}
						, fields.value
						, { type: arg.get( 'data_type' ) }
					);
					/* falls through */
				case 'entity':
					var values = arg.get( 'values' );

					if ( values ) {

						fields.value = _.extend(
							{}
							, fields.value
							, { type: 'select', options: values }
						);
					}
			}

			this.set( 'fields', fields );
		}

		return this.constructor.__super__.renderSettings.apply(
			this
			, [ condition, fieldNamePrefix ]
		);
	}
});

module.exports = Equals;

},{}],5:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.extension.Conditions
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 *
 *
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	ConditionGroups = wp.wordpoints.hooks.model.ConditionGroups,
	ConditionsGroupsView = wp.wordpoints.hooks.view.ConditionGroups,
	getDeep = wp.wordpoints.hooks.util.getDeep,
	Conditions;

Conditions = Extension.extend({

	defaults: {
		slug: 'conditions'
	},

	initialize: function () {

		this.dataType = Backbone.Model.extend( { idAttribute: 'slug' } );
		this.controllers = new Backbone.Collection(
			[]
			, { comparator: 'slug', model: this.dataType }
		);
	},

	initReaction: function ( reaction ) {

		reaction.conditions = {};
		reaction.model.conditions = {};

		var conditions = reaction.model.get( 'conditions' );

		if ( ! conditions ) {
			conditions = {};
		}

		_.each( reaction.Reactor.get( 'action_types' ), function ( actionType ) {

			var conditionGroups = conditions[ actionType ];

			if ( ! conditionGroups ) {
				conditionGroups = [];
			}

			reaction.model.conditions[ actionType ] = new ConditionGroups( null, {
				hierarchy: [ actionType ],
				reaction: reaction.model,
				_conditions: conditionGroups
			} );

			reaction.conditions[ actionType ] = new ConditionsGroupsView( {
				collection: reaction.model.conditions[ actionType ],
				reaction: reaction
			});

		}, this );

		this.listenTo( reaction, 'render:fields', function ( $el, currentActionType ) {

			var conditions = reaction.conditions[ currentActionType ];

			if ( ! conditions ) {
				return;
			}

			$el.append( conditions.render().$el );
		});
	},

	getDataTypeFromArg: function ( arg ) {

		var argType = arg.get( '_type' );

		switch ( argType ) {

			case 'attr':
				return arg.get( 'data_type' );

			case 'array':
				return 'entity_array';

			default:
				return argType;
		}
	},

	validateReaction: function ( model, attributes, errors ) {

		if ( ! attributes.conditions ) {
			return;
		}

		this.validateConditions( model.conditions, attributes.conditions, errors );
	},

	validateConditions: function ( conditions, settings, errors ) {

		_.each( conditions, function ( groups ) {
			groups.each( function ( group ) {
				group.get( 'conditions' ).each( function ( condition ) {

					var newErrors = [],
						hierarchy = condition.getHierarchy().concat(
							[ '_conditions', condition.id ]
						);

					if ( groups.hierarchy.length === 1 ) {
						hierarchy.unshift( groups.hierarchy[0] );
					}

					condition.validate(
						getDeep( settings, hierarchy )
						, {}
						, newErrors
					);

					if ( ! _.isEmpty( newErrors ) ) {

						hierarchy.unshift( 'conditions' );
						hierarchy.push( 'settings' );

						for ( var i = 0; i < newErrors.length; i++ ) {

							newErrors[ i ].field = hierarchy.concat(
								_.isArray( newErrors[ i ].field )
									? newErrors[ i ].field
									: [ newErrors[ i ].field ]
							);

							errors.push( newErrors[ i ] );
						}
					}
				});
			});
		});
	},

	getType: function ( dataType, slug ) {

		if ( typeof this.data.conditions[ dataType ] === 'undefined' ) {
			return false;
		}

		if ( typeof this.data.conditions[ dataType ][ slug ] === 'undefined' ) {
			return false;
		}

		return this.data.conditions[ dataType ][ slug ];
	},

	// Get all conditions for a certain data type.
	getByDataType: function ( dataType ) {

		return this.data.conditions[ dataType ];
	},

	getController: function ( dataTypeSlug, slug ) {

		var dataType = this.controllers.get( dataTypeSlug ),
			controller;

		if ( dataType ) {
			controller = dataType.get( 'controllers' )[ slug ];
		}

		if ( ! controller ) {
			controller = Conditions.Condition;
		}

		var type = this.getType( dataTypeSlug, slug );

		if ( ! type ) {
			type = { slug: slug };
		}

		return new controller( type );
	},

	registerController: function ( dataTypeSlug, slug, controller ) {

		var dataType = this.controllers.get( dataTypeSlug );

		if ( ! dataType ) {
			dataType = new this.dataType({
				slug: dataTypeSlug,
				controllers: {}
			});

			this.controllers.add( dataType );
		}

		dataType.get( 'controllers' )[ slug ] = controller;
	}

} );

module.exports = Conditions;

},{}],6:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.ConditionGroup
 *
 * @class
 * @augments Backbone.Collection
 */
var Conditions = wp.wordpoints.hooks.model.Conditions,
	ConditionGroup;

// This is a model although we originally thought it ought to be a collection,
// because Backbone doesn't support sub-collections. This is the closest thing
// to a sub-collection. See http://stackoverflow.com/q/10388199/1924128.
ConditionGroup = Backbone.Model.extend({

	defaults: function () {
		return {
			id: '',
			hierarchy: [],
			conditions: new Conditions(),
			groups: null,
			reaction: null
		};
	},

	initialize: function ( attributes ) {

		// Set up event proxying.
		this.listenTo( this.attributes.conditions, 'all', this.trigger );

		// Add the conditions to the collection.
		if ( attributes._conditions ) {
			this.reset( attributes._conditions );
		}
	},

	// Make sure that the model ids are properly set. Conditions are identified
	// by the index of the array in which they are stored. We copy the keys to
	// the id attributes of the models.
	reset: function ( models, options ) {

		options = options || {};
		options.group = this;

		var conditions = this.get( 'conditions' );

		this.setIds( models, 0 );

		return conditions.reset.call( conditions, models, options );
	},

	add: function ( models, options ) {

		options = options || {};
		options.group = this;

		var conditions = this.get( 'conditions' );

		this.setIds( models, this.getNextId() );

		return conditions.add.call( conditions, models, options );
	},

	getNextId: function() {

		var conditions = this.get( 'conditions' );

		if ( !conditions.length ) {
			return 0;
		}

		return parseInt( conditions.sort().last().get( 'id' ), 10 ) + 1;
	},

	setIds: function ( models, startId ) {

		if ( ! models ) {
			return;
		}

		_.each( _.isArray( models ) ? models : [ models ], function ( model, id ) {

			if ( startId !== 0 ) {
				model.id = startId++;
			} else {
				model.id = id;
			}

			// This will be set when an object is converted to a model, but if it is
			// a model already, we need to set it here.
			if ( model instanceof Backbone.Model ) {
				model.group = this;
			}

		}, this );
	},

	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving groups of hook conditions is not supported.' }
		);
	}
});

module.exports = ConditionGroup;

},{}],7:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.ConditionGroups
 *
 * @class
 * @augments Backbone.Collection
 */
var ConditionGroup = wp.wordpoints.hooks.model.ConditionGroup,
	Args = wp.wordpoints.hooks.Args,
	ConditionGroups;

ConditionGroups = Backbone.Collection.extend({

	model: ConditionGroup,

	hierarchy: [],

	initialize: function ( models, options ) {

		if ( options.args ) {
			this.args = options.args;
		}

		if ( options.hierarchy ) {
			this.hierarchy = options.hierarchy;
		}

		if ( options.reaction ) {
			this.reaction = options.reaction;
		}

		if ( options._conditions ) {
			this.mapConditions( options._conditions );
		}
	},

	mapConditions: function ( conditions, hierarchy ) {

		hierarchy = hierarchy || [];

		_.each( conditions, function ( arg, slug ) {

			if ( slug === '_conditions' ) {

				this.add( {
					id: this.getIdFromHierarchy( hierarchy ),
					hierarchy: _.clone( hierarchy ),
					groups: this,
					_conditions: arg
				} );

			} else {

				hierarchy.push( slug );

				this.mapConditions( arg, hierarchy );

				hierarchy.pop();
			}

		}, this );
	},

	getIdFromHierarchy: function ( hierarchy ) {
		return hierarchy.join( '.' );
	},

	getArgs: function () {

		var args = this.args;

		if ( ! args ) {
			args = Args.getEventArgs( this.reaction.get( 'event' ) );
		}

		return args;
	}
});

module.exports = ConditionGroups;

},{}],8:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.ConditionType
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	ConditionType;

ConditionType = Base.extend({
	idAttribute: 'slug'
});

module.exports = ConditionType;

},{}],9:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.ConditionTypes
 *
 * @class
 * @augments Backbone.Collection
 */
var ConditionType = wp.wordpoints.hooks.model.ConditionType,
	ConditionTypes;

ConditionTypes = Backbone.Collection.extend({

	model: ConditionType

});

module.exports = ConditionTypes;

},{}],10:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Condition
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	Args = wp.wordpoints.hooks.Args,
	Extensions = wp.wordpoints.hooks.Extensions,
	Fields = wp.wordpoints.hooks.Fields,
	Condition;

Condition = Base.extend({

	defaults: {
		type: '',
		settings: []
	},

	initialize: function ( attributes, options ) {
		if ( options.group ) {
			this.group = options.group;
		}
	},

	validate: function ( attributes, options, errors ) {

		errors = errors || [];

		var conditionType = this.getType();

		if ( ! conditionType ) {
			return;
		}

		var fields = conditionType.fields;

		Fields.validate(
			fields
			, attributes.settings
			, errors
		);

		var controller = this.getController();

		if ( controller ) {
			controller.validateSettings( this, attributes.settings, errors );
		}

		return errors;
	},

	getController: function () {

		var arg = this.getArg();

		if ( ! arg ) {
			return false;
		}

		var Conditions = Extensions.get( 'conditions' );

		return Conditions.getController(
			Conditions.getDataTypeFromArg( arg )
			, this.get( 'type' )
		);
	},

	getType: function () {

		var arg = this.getArg();

		if ( ! arg ) {
			return false;
		}

		var Conditions = Extensions.get( 'conditions' );

		return Conditions.getType(
			Conditions.getDataTypeFromArg( arg )
			, this.get( 'type' )
		);
	},

	getArg: function () {

		if ( ! this.arg ) {

			var args = Args.getArgsFromHierarchy(
				this.getHierarchy()
				, this.reaction.get( 'event' )
			);

			if ( args ) {
				this.arg = args[ args.length - 1 ];
			}
		}

		return this.arg;
	},

	getHierarchy: function () {
		return this.group.get( 'hierarchy' );
	},

	getFullHierarchy: function () {

		return this.group.get( 'groups' ).hierarchy.concat(
			this.getHierarchy()
		);
	},

	sync: function ( method, model, options ) {
		options.error(
			{ message: 'Fetching and saving hook conditions is not supported.' }
		);
	}
});

module.exports = Condition;

},{}],11:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.model.Conditions
 *
 * @class
 * @augments Backbone.Collection
 */
var Condition = wp.wordpoints.hooks.model.Condition,
	Conditions;

Conditions = Backbone.Collection.extend({

	// Reference to this collection's model.
	model: Condition,

	comparator: 'id',

	sync: function ( method, collection, options ) {
		options.error(
			{ message: 'Fetching and saving hook conditions is not supported.' }
		);
	}
});

module.exports = Conditions;

},{}],12:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.ConditionGroup
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	Condition = wp.wordpoints.hooks.view.Condition,
	Args = wp.wordpoints.hooks.Args,
	$ = Backbone.$,
	template = wp.wordpoints.hooks.template,
	ConditionGroup;

ConditionGroup = Base.extend({

	className: 'condition-group',

	template: template( 'hook-reaction-condition-group' ),

	initialize: function () {

		this.listenTo( this.model, 'add', this.addOne );
		this.listenTo( this.model, 'reset', this.render );
		this.listenTo( this.model, 'remove', this.maybeHide );

		this.model.on( 'add', this.reaction.lockOpen, this.reaction );
		this.model.on( 'remove', this.reaction.lockOpen, this.reaction );
		this.model.on( 'reset', this.reaction.lockOpen, this.reaction );
	},

	render: function () {

		this.$el.html( this.template() );

		this.maybeHide();

		this.$( '.condition-group-title' ).text(
			Args.buildHierarchyHumanId(
				Args.getArgsFromHierarchy(
					this.model.get( 'hierarchy' )
					, this.reaction.model.get( 'event' )
				)
			)
		);

		this.addAll();

		return this;
	},

	addOne: function ( condition ) {

		condition.reaction = this.reaction.model;

		var view = new Condition( {
			el: $( '<div class="condition"></div>' ),
			model: condition,
			reaction: this.reaction
		} );

		this.$el.append( view.render().$el ).show();

		this.listenTo( condition, 'destroy', function () {
			this.model.get( 'conditions' ).remove( condition.id );
		} );
	},

	addAll: function () {
		this.model.get( 'conditions' ).each( this.addOne, this );
	},

	// Hide the group when it is empty.
	maybeHide: function () {

		if ( 0 === this.model.get( 'conditions' ).length ) {
			this.$el.hide();
		}
	}
});

module.exports = ConditionGroup;
},{}],13:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.ConditionGroups
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	ConditionGroupView = wp.wordpoints.hooks.view.ConditionGroup,
	ArgSelector2 = wp.wordpoints.hooks.view.ArgSelector2,
	ConditionSelector = wp.wordpoints.hooks.view.ConditionSelector,
	Extensions = wp.wordpoints.hooks.Extensions,
	Args = wp.wordpoints.hooks.Args,
	template = wp.wordpoints.hooks.template,
	$cache = wp.wordpoints.$cache,
	ConditionGroups;

ConditionGroups = Base.extend({

	namespace: 'condition-groups',

	className: 'conditions',

	template: template( 'hook-condition-groups' ),

	events: {
		'click > .conditions-title .add-new':           'showArgSelector',
		'click > .add-condition-form .confirm-add-new': 'maybeAddNew',
		'click > .add-condition-form .cancel-add-new':  'cancelAddNew'
	},

	initialize: function () {

		this.Conditions = Extensions.get( 'conditions' );

		this.listenTo( this.collection, 'add', this.addOne );
		this.listenTo( this.collection, 'reset', this.render );

		this.listenTo( this.reaction, 'cancel', this.cancelAddNew );

		this.collection.on( 'update', this.reaction.lockOpen, this.reaction );
		this.collection.on( 'reset', this.reaction.lockOpen, this.reaction );
	},

	render: function () {

		this.$el.html( this.template() );

		this.$c = $cache.call( this, this.$ );

		this.addAll();

		this.trigger( 'render', this );

		return this;
	},

	addAll: function () {
		this.collection.each( this.addOne, this );
	},

	addOne: function ( ConditionGroup ) {

		var view = new ConditionGroupView({
			model: ConditionGroup,
			reaction: this.reaction
		});

		this.$c( '> .condition-groups' ).append( view.render().$el );
	},

	showArgSelector: function () {

		this.$c( '> .conditions-title .add-new' ).attr( 'disabled', true );

		if ( typeof this.ArgSelector === 'undefined' ) {

			var args = this.collection.getArgs();
			var Conditions = this.Conditions;
			var isEntityArray = ( this.collection.hierarchy.slice( -2 ).toString() === 'settings,conditions' );
			var hasConditions = function ( arg ) {

				var dataType = Conditions.getDataTypeFromArg( arg );

				// We don't allow identity conditions on top-level entities.
				if (
					! isEntityArray
					&& dataType === 'entity'
					&& _.isEmpty( arg.hierarchy )
				) {
					return false;
				}

				var conditions = Conditions.getByDataType( dataType );

				return ! _.isEmpty( conditions );
			};

			var hierarchies = Args.getHierarchiesMatching(
				{ top: args.models, end: hasConditions }
			);

			if ( _.isEmpty( hierarchies ) ) {

				this.$c( '> .add-condition-form .no-conditions' ).show();

			} else {

				this.ArgSelector = new ArgSelector2({
					hierarchies: hierarchies,
					el: this.$( '.arg-selectors' )
				});

				this.listenTo( this.ArgSelector, 'change', this.maybeShowConditionSelector );

				this.ArgSelector.render();

				this.ArgSelector.$select.change();
			}
		}

		this.$c( '> .add-condition-form' ).slideDown();
	},

	getArgType: function ( arg ) {

		var argType;

		if ( ! arg || ! arg.get ) {
			return;
		}

		argType = this.Conditions.getDataTypeFromArg( arg );

		// We compress relationships to avoid redundancy.
		if ( 'relationship' === argType ) {
			argType = this.getArgType( arg.getChild( arg.get( 'secondary' ) ) );
		}

		return argType;
	},

	maybeShowConditionSelector: function ( argSelectors, arg ) {

		var argType = this.getArgType( arg );

		if ( ! argType ) {
			if ( this.$conditionSelector ) {
				this.$conditionSelector.hide();
			}

			return;
		}

		var conditions = this.Conditions.getByDataType( argType );

		if ( ! this.ConditionSelector ) {

			this.ConditionSelector = new ConditionSelector({
				el: this.$( '.condition-selector' )
			});

			this.listenTo( this.ConditionSelector, 'change', this.conditionSelectionChange );

			this.$conditionSelector = this.ConditionSelector.$el;
		}

		this.ConditionSelector.collection.reset( _.toArray( conditions ) );

		this.$conditionSelector.show().find( 'select' ).change();
	},

	cancelAddNew: function () {

		this.$c( '> .add-condition-form' ).slideUp();
		this.$c( '> .conditions-title .add-new' ).attr( 'disabled', false );
	},

	conditionSelectionChange: function ( selector, value ) {

		this.$c( '> .add-condition-form .confirm-add-new' )
			.attr( 'disabled', ! value );
	},

	maybeAddNew: function () {

		var selected = this.ConditionSelector.getSelected();

		if ( ! selected ) {
			return;
		}

		var hierarchy = this.ArgSelector.getHierarchy(),
			id = this.collection.getIdFromHierarchy( hierarchy ),
			ConditionGroup = this.collection.get( id );

		if ( ! ConditionGroup ) {
			ConditionGroup = this.collection.add({
				id: id,
				hierarchy: hierarchy,
				groups: this.collection
			});
		}

		ConditionGroup.add( { type: selected } );

		this.$c( '> .add-condition-form' ).hide();
		this.$c( '> .conditions-title .add-new' ).attr( 'disabled', false );

		// TODO highlight new condition?
	}
});

module.exports = ConditionGroups;

},{}],14:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.ConditionSelector
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	ConditionTypes = wp.wordpoints.hooks.model.ConditionTypes,
	template = wp.wordpoints.hooks.template,
	ConditionSelector;

ConditionSelector = Base.extend({

	namespace: 'condition-selector',

	template: template( 'hook-condition-selector' ),

	optionTemplate: template( 'hook-arg-option' ),

	events: {
		'change select': 'triggerChange'
	},

	initialize: function ( options ) {

		this.label = options.label;

		if ( ! this.collection ) {
			this.collection = new ConditionTypes({ comparator: 'title' });
		}

		this.listenTo( this.collection, 'update', this.render );
		this.listenTo( this.collection, 'reset', this.render );
	},

	render: function () {

		this.$el.html(
			this.template(
				{ label: this.label, name: this.cid + '_condition_selector' }
			)
		);

		this.$select = this.$( 'select' );

		this.collection.each( function ( condition ) {

			this.$select.append( this.optionTemplate( condition.attributes ) );

		}, this );

		this.trigger( 'render', this );

		return this;
	},

	triggerChange: function ( event ) {

		this.trigger( 'change', this, this.getSelected(), event );
	},

	getSelected: function () {

		return this.$select.val();
	}
});

module.exports = ConditionSelector;

},{}],15:[function(require,module,exports){
/**
 * wp.wordpoints.hooks.view.Condition
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	template = wp.wordpoints.hooks.template,
	Extensions = wp.wordpoints.hooks.Extensions,
	Fields = wp.wordpoints.hooks.Fields,
	Condition;

Condition = Base.extend({

	namespace: 'condition',

	className: 'wordpoints-hook-condition',

	template: template( 'hook-reaction-condition' ),

	events: {
		'click .delete': 'destroy'
	},

	initialize: function () {

		this.listenTo( this.model, 'change', this.render );
		this.listenTo( this.model, 'destroy', this.remove );

		this.listenTo( this.model, 'invalid', this.model.reaction.showError );

		this.extension = Extensions.get( 'conditions' );
	},

	// Display the condition settings form.
	render: function () {

		this.$el.html( this.template() );

		this.$title = this.$( '.condition-title' );
		this.$settings = this.$( '.condition-settings' );

		this.renderTitle();
		this.renderSettings();

		this.trigger( 'render', this );

		return this;
	},

	renderTitle: function () {

		var conditionType = this.model.getType();

		if ( conditionType ) {
			this.$title.text( conditionType.title );
		}

		this.trigger( 'render:title', this );
	},

	renderSettings: function () {

		// Build the fields based on the condition type.
		var conditionType = this.model.getType(),
			fields = '';

		var fieldNamePrefix = _.clone( this.model.getFullHierarchy() );
		fieldNamePrefix.unshift( 'conditions' );
		fieldNamePrefix.push(
			'_conditions'
			, this.model.get( 'id' )
			, 'settings'
		);

		var fieldName = _.clone( fieldNamePrefix );

		fieldName.pop();
		fieldName.push( 'type' );

		fields += Fields.create(
			fieldName
			, this.model.get( 'type' )
			, { type: 'hidden' }
		);

		if ( conditionType ) {
			var controller = this.extension.getController(
				conditionType.data_type
				, conditionType.slug
			);

			if ( controller ) {
				fields += controller.renderSettings( this, fieldNamePrefix );
			}
		}

		this.$settings.append( fields );

		this.trigger( 'render:settings', this );
	},

	// Remove the item, destroy the model.
	destroy: function () {

		this.model.destroy();
	}
});

module.exports = Condition;

},{}]},{},[1]);
