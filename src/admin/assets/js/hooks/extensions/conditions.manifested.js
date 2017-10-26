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

Conditions.registerController( 'decimal_number', 'equals', Equals );
Conditions.registerController( 'entity', 'equals', Equals );
Conditions.registerController( 'entity_array', 'equals', Equals );
Conditions.registerController( 'entity_array', 'contains', require( './conditions/controllers/conditions/entity-array-contains.js' ) );
Conditions.registerController( 'integer', 'equals', Equals );
Conditions.registerController( 'text', 'equals', Equals );

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
	data = wp.wordpoints.hooks.view.data,
	Conditions;

Conditions = Extension.extend({

	defaults: {
		slug: 'conditions'
	},

	initialize: function () {

		this.argFilters = [ this.onlyEnumerableEntities ];
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

		var actionTypes = _.keys(
			data.event_action_types[ reaction.model.get( 'event' ) ]
		);

		if ( ! actionTypes ) {
			return;
		}

		actionTypes = _.intersection(
			reaction.Reactor.get( 'action_types' )
			, actionTypes
		);

		_.each( actionTypes, function ( actionType ) {

			var conditionGroups = conditions[ actionType ];

			if ( ! conditionGroups ) {
				conditionGroups = [];
			}

			reaction.model.conditions[ actionType ] = new ConditionGroups( null, {
				hierarchy: [ actionType ],
				reaction: reaction.model,
				_conditions: conditionGroups
			} );

		}, this );

		var appended = false;

		this.listenTo( reaction, 'render:fields', function ( $el, currentActionType ) {

			var conditionsView = reaction.conditions[ currentActionType ];

			if ( ! conditionsView ) {
				conditionsView = reaction.conditions[ currentActionType ] = new ConditionsGroupsView( {
					collection: reaction.model.conditions[ currentActionType ],
					reaction: reaction
				});
			}

			// If we've already appended the container view to the reaction view,
			// then we don't need to do that again.
			if ( appended ) {

				var conditionsCollection = reaction.model.conditions[ currentActionType ];
				var conditions = reaction.model.get( 'conditions' );

				if ( ! conditions ) {
					conditions = {};
				}

				// However, we do need to update the condition collection, in case
				// some of the condition models have been removed or new ones added.
				conditionsCollection.set(
					conditionsCollection.mapConditionGroups(
						conditions[ currentActionType ] || []
					)
					, { parse: true }
				);

				// And then re-render everything.
				conditionsView.render();

			} else {

				$el.append( conditionsView.render().$el );

				appended = true;
			}
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

	validateReaction: function ( model, attributes, errors, options ) {

		// https://github.com/WordPoints/wordpoints/issues/519.
		if ( ! options.rawAtts.conditions ) {
			delete attributes.conditions;
			delete model.attributes.conditions;
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

		return _.clone( this.data.conditions[ dataType ][ slug ] );
	},

	// Get all conditions for a certain data type.
	getByDataType: function ( dataType ) {

		return _.clone( this.data.conditions[ dataType ] );
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
	},

	/**
	 * Arg filter to disallow identity conditions on entities that aren't enumerable.
	 */
	onlyEnumerableEntities: function ( arg, dataType, isEntityArray, conditions ) {

		if ( dataType === 'entity' && _.isEmpty( arg.get( 'values' ) ) ) {
			delete conditions.equals;
		}

		return true;
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
// to a sub-collection. See https://stackoverflow.com/q/10388199/1924128.
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

	/**
	 * @summary Update the conditions collection.
	 *
	 * This is a wrapper for the `set()` method of the collection stored in the
	 * `conditions` attribute of this Model. It ensures that the passed model
	 * objects have been given proper IDs, and sets options.group to this object.
	 *
	 * Note that the `_conditions` attribute itself is not modified, only the
	 * collection that is stored in the `conditions` attribute.
	 *
	 * @since 2.1.3
	 *
	 * @param {Object[]} models                    - The conditions.
	 * @param {Object}   [options={ group: this }] - Options to pass to
	 *                                               `Conditions.set()`. The `group`
	 *                                               will always be set to `this`.
	 *
	 * @returns {Object[]} The added models.
	 */
	setConditions: function ( models, options ) {

		options = options || {};
		options.group = this;

		var conditions = this.get( 'conditions' );

		this.setIds( models, 0 );

		return conditions.set.call( conditions, models, options );
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

/**
 * Object format for models expected by this collection.
 *
 * @typedef {Object} RawConditionGroup
 *
 * @property {string}          id          - The ID of the group.
 * @property {Array}           hierarchy   - The hierarchy for the group.
 * @property {ConditionGroups} groups      - The collection for the group.
 * @property {Array}           _conditions - The conditions in the group.
 */

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

	/**
	 * @summary Converts a conditions hierarchy into an array of condition groups.
	 *
	 * The conditions, as saved in the database, are in a nested hierarchy based on
	 * which (sub)args they are for. Therefore it is necessary to parse the hierarchy
	 * into a simple array containing the condition information and the arg hierarchy
	 * for it.
	 *
	 * @since 2.1.3
	 *
	 * @param {Object}              conditions     - The conditions hierarchy.
	 * @param {RawConditionGroup[]} [groups=[]]    - The array of condition groups.
	 * @param {Array}               [hierarchy=[]] - The current location within the
	 *                                               conditions hierarchy.
	 *
	 * @returns {RawConditionGroup[]} The parsed groups in the format for models
	 *                                expected by this collection.
	 */
	mapConditionGroups: function ( conditions, groups, hierarchy ) {

		hierarchy = hierarchy || [];
		groups = groups || [];

		_.each( conditions, function ( arg, slug ) {

			if ( slug === '_conditions' ) {

				groups.push( {
					id:          this.getIdFromHierarchy( hierarchy ),
					hierarchy:   _.clone( hierarchy ),
					groups:      this,
					_conditions: _.toArray( arg )
				} );

			} else {

				hierarchy.push( slug );

				this.mapConditionGroups( arg, groups, hierarchy );

				hierarchy.pop();
			}

		}, this );

		return groups;
	},

	/**
	 * @summary Parses a conditions hierarchy and adds each group to the collection.
	 *
	 * @since 2.1.0
	 * @since 2.1.3 The hierarchy arg was deprecated.
	 *
	 * @param {Array} conditions  - The raw conditions hierarchy to parse.
	 * @param {Array} [hierarchy] - Deprecated. Previously used to track the current
	 *                              location within the conditions hierarchy.
	 */
	mapConditions: function ( conditions, hierarchy ) {

		var groups = this.mapConditionGroups( conditions, [], hierarchy );

		this.reset( groups );
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
	},

	/**
	 * @summary Parses a raw value into a list of models.
	 *
	 * Implemented here so if the models are going to be merged with corresponding
	 * ones in the existing collection, we can go ahead and update the `conditions`
	 * collection of the existing models based on their passed in `_conditions`
	 * attribute. Otherwise the conditions collection would not be updated. See [the
	 * discussion on GitHub]{@link https://github.com/WordPoints/wordpoints/issues/
     * 517#issuecomment-250307147} for more information on why we do it this way.
	 *
	 * @since 2.1.3
	 *
	 * @param {Object|Object[]} resp    - The raw model(s).
	 * @param {Object}          options - Options passed from `set()`.
	 *
	 * @returns {Object|Object[]} The condition models, with `conditions` property
	 *                            set as needed.
	 */
	parse: function ( resp, options ) {

		if ( ! options.merge ) {
			return resp;
		}

		var models = _.isArray( resp ) ? resp : [resp],
			model;

		for ( var i = 0; i < models.length; i++ ) {

			model = this.get( models[ i ].id );

			if ( ! model ) {
				continue;
			}

			model.setConditions( models[ i ]._conditions, options );

			models[ i ].conditions = model.get( 'conditions' );
		}

		return resp;
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

	isNew: function () {
		return 'undefined' === typeof this.reaction.get(
			[ 'conditions' ]
				.concat( this.getFullHierarchy() )
				.concat( [ '_conditions', this.id ] )
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

		var $view = view.render().$el;

		this.$el.append( $view ).show();

		if ( condition.isNew() ) {
			$view.find( ':input:visible:eq( 1 )' ).focus();
		}

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
	ArgHierarchySelector = wp.wordpoints.hooks.view.ArgHierarchySelector,
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

		// See https://github.com/WordPoints/wordpoints/issues/520.
		if ( this.ArgSelector ) {

			this.$( '> .add-condition-form .arg-selectors' ).replaceWith(
				this.ArgSelector.$el
			);

			this.$( '> .add-condition-form .condition-selector' ).replaceWith(
				this.ConditionSelector.$el
			);

			this.ArgSelector.delegateEvents();
			this.ConditionSelector.delegateEvents();
			this.ConditionSelector.triggerChange();
		}

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
			var argFilters = Conditions.argFilters;
			var isEntityArray = ( this.collection.hierarchy.slice( -2 ).toString() === 'settings,conditions' );
			var hasConditions = function ( arg ) {

				var dataType = Conditions.getDataTypeFromArg( arg );
				var conditions = Conditions.getByDataType( dataType );

				for ( var i = 0; i < argFilters.length; i++ ) {
					if ( ! argFilters[ i ]( arg, dataType, isEntityArray, conditions ) ) {
						return false;
					}
				}

				return ! _.isEmpty( conditions );
			};

			var hierarchies = Args.getHierarchiesMatching(
				{ top: args.models, end: hasConditions }
			);

			if ( _.isEmpty( hierarchies ) ) {

				this.$c( '> .add-condition-form .no-conditions' ).show();

			} else {

				this.ArgSelector = new ArgHierarchySelector({
					hierarchies: hierarchies,
					el: this.$( '> .add-condition-form .arg-selectors' )
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
				el: this.$( '> .add-condition-form .condition-selector' )
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

		wp.a11y.speak( this.Conditions.data.l10n.added_condition );

		this.$c( '> .add-condition-form' ).hide();
		this.$c( '> .conditions-title .add-new' ).attr( 'disabled', false );
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

		wp.a11y.speak( this.extension.data.l10n.deleted_condition );

		this.model.destroy();
	}
});

module.exports = Condition;

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9uLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZW50aXR5LWFycmF5LWNvbnRhaW5zLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZXF1YWxzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2V4dGVuc2lvbnMvY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLWdyb3VwLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tZ3JvdXBzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tdHlwZS5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2V4dGVuc2lvbnMvY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLXR5cGVzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24uanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbnMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLWdyb3VwLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cHMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLXNlbGVjdG9yLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDNUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM5REE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BRQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN2SUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDM0tBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2ZBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDaEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2pJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN4QkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN4RkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25PQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNyRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsInZhciBob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3M7XG5cbi8vIE1vZGVsc1xuaG9va3MubW9kZWwuQ29uZGl0aW9uICAgICAgID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLmpzJyApO1xuaG9va3MubW9kZWwuQ29uZGl0aW9ucyAgICAgID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9ucy5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3VwICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi1ncm91cC5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3VwcyA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi1ncm91cHMuanMnICk7XG5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tdHlwZS5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvblR5cGVzICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi10eXBlcy5qcycgKTtcblxuLy8gVmlld3Ncbmhvb2tzLnZpZXcuQ29uZGl0aW9uICAgICAgICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi5qcycgKTtcbmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXAgICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cC5qcycgKTtcbmhvb2tzLnZpZXcuQ29uZGl0aW9uU2VsZWN0b3IgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1zZWxlY3Rvci5qcycgKTtcbmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBzICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cHMuanMnICk7XG5cbi8vIENvbnRyb2xsZXJzLlxuaG9va3MuZXh0ZW5zaW9uLkNvbmRpdGlvbnMgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcycgKTtcbmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvbiA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9uLmpzJyApO1xuXG52YXIgQ29uZGl0aW9ucyA9IG5ldyBob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucygpO1xuXG4vLyBDb25kaXRpb25zLlxudmFyIEVxdWFscyA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9ucy9lcXVhbHMuanMnICk7XG5cbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAnZGVjaW1hbF9udW1iZXInLCAnZXF1YWxzJywgRXF1YWxzICk7XG5Db25kaXRpb25zLnJlZ2lzdGVyQ29udHJvbGxlciggJ2VudGl0eScsICdlcXVhbHMnLCBFcXVhbHMgKTtcbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAnZW50aXR5X2FycmF5JywgJ2VxdWFscycsIEVxdWFscyApO1xuQ29uZGl0aW9ucy5yZWdpc3RlckNvbnRyb2xsZXIoICdlbnRpdHlfYXJyYXknLCAnY29udGFpbnMnLCByZXF1aXJlKCAnLi9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZW50aXR5LWFycmF5LWNvbnRhaW5zLmpzJyApICk7XG5Db25kaXRpb25zLnJlZ2lzdGVyQ29udHJvbGxlciggJ2ludGVnZXInLCAnZXF1YWxzJywgRXF1YWxzICk7XG5Db25kaXRpb25zLnJlZ2lzdGVyQ29udHJvbGxlciggJ3RleHQnLCAnZXF1YWxzJywgRXF1YWxzICk7XG5cbi8vIFJlZ2lzdGVyIHRoZSBleHRlbnNpb24uXG5ob29rcy5FeHRlbnNpb25zLmFkZCggQ29uZGl0aW9ucyApO1xuXG4vLyBFT0ZcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5jb25kaXRpb24uQ29uZGl0aW9uXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqL1xuXG52YXIgRmllbGRzID0gd3Aud29yZHBvaW50cy5ob29rcy5GaWVsZHMsXG5cdENvbmRpdGlvbjtcblxuQ29uZGl0aW9uID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdHNsdWc6ICcnLFxuXHRcdGZpZWxkczogW11cblx0fSxcblxuXHRpZEF0dHJpYnV0ZTogJ3NsdWcnLFxuXG5cdHJlbmRlclNldHRpbmdzOiBmdW5jdGlvbiAoIGNvbmRpdGlvbiwgZmllbGROYW1lUHJlZml4ICkge1xuXG5cdFx0dmFyIGZpZWxkc0hUTUwgPSAnJztcblxuXHRcdF8uZWFjaCggdGhpcy5nZXQoICdmaWVsZHMnICksIGZ1bmN0aW9uICggc2V0dGluZywgbmFtZSApIHtcblxuXHRcdFx0dmFyIGZpZWxkTmFtZSA9IF8uY2xvbmUoIGZpZWxkTmFtZVByZWZpeCApO1xuXG5cdFx0XHRmaWVsZE5hbWUucHVzaCggbmFtZSApO1xuXG5cdFx0XHRmaWVsZHNIVE1MICs9IEZpZWxkcy5jcmVhdGUoXG5cdFx0XHRcdGZpZWxkTmFtZVxuXHRcdFx0XHQsIGNvbmRpdGlvbi5tb2RlbC5hdHRyaWJ1dGVzLnNldHRpbmdzWyBuYW1lIF1cblx0XHRcdFx0LCBzZXR0aW5nXG5cdFx0XHQpO1xuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0cmV0dXJuIGZpZWxkc0hUTUw7XG5cdH0sXG5cblx0dmFsaWRhdGVTZXR0aW5nczogZnVuY3Rpb24gKCkge31cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5jb25kaXRpb24uRW50aXR5QXJyYXlDb250YWluc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5Db25kaXRpb25cbiAqL1xuXG52YXIgQ29uZGl0aW9uID0gd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5Db25kaXRpb24sXG5cdENvbmRpdGlvbkdyb3VwcyA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXBzLFxuXHRDb25kaXRpb25Hcm91cHNWaWV3ID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvbkdyb3Vwcyxcblx0RXh0ZW5zaW9ucyA9IHdwLndvcmRwb2ludHMuaG9va3MuRXh0ZW5zaW9ucyxcblx0QXJnc0NvbGxlY3Rpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkFyZ3MsXG5cdEFyZ3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MsXG5cdEVudGl0eUFycmF5Q29udGFpbnM7XG5cbkVudGl0eUFycmF5Q29udGFpbnMgPSBDb25kaXRpb24uZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdHNsdWc6ICdlbnRpdHlfYXJyYXlfY29udGFpbnMnXG5cdH0sXG5cblx0cmVuZGVyU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBmaWVsZE5hbWVQcmVmaXggKSB7XG5cblx0XHQvLyBSZW5kZXIgdGhlIG1haW4gZmllbGRzLlxuXHRcdHZhciBmaWVsZHMgPSB0aGlzLmNvbnN0cnVjdG9yLl9fc3VwZXJfXy5yZW5kZXJTZXR0aW5ncy5hcHBseShcblx0XHRcdHRoaXNcblx0XHRcdCwgWyBjb25kaXRpb24sIGZpZWxkTmFtZVByZWZpeCBdXG5cdFx0KTtcblxuXHRcdGNvbmRpdGlvbi4kc2V0dGluZ3MuYXBwZW5kKCBmaWVsZHMgKTtcblxuXHRcdC8vIFJlbmRlciB2aWV3IGZvciBzdWItY29uZGl0aW9ucy5cblx0XHR2YXIgYXJnID0gQXJncy5nZXRFbnRpdHkoXG5cdFx0XHRjb25kaXRpb24ubW9kZWwuZ2V0QXJnKCkuZ2V0KCAnZW50aXR5X3NsdWcnIClcblx0XHQpO1xuXG5cdFx0Y29uZGl0aW9uLm1vZGVsLnN1Ykdyb3VwcyA9IG5ldyBDb25kaXRpb25Hcm91cHMoIG51bGwsIHtcblx0XHRcdGFyZ3M6IG5ldyBBcmdzQ29sbGVjdGlvbiggWyBhcmcgXSApLFxuXHRcdFx0aGllcmFyY2h5OiBjb25kaXRpb24ubW9kZWwuZ2V0RnVsbEhpZXJhcmNoeSgpLmNvbmNhdChcblx0XHRcdFx0WyAnX2NvbmRpdGlvbnMnLCBjb25kaXRpb24ubW9kZWwuaWQsICdzZXR0aW5ncycsICdjb25kaXRpb25zJyBdXG5cdFx0XHQpLFxuXHRcdFx0cmVhY3Rpb246IGNvbmRpdGlvbi5yZWFjdGlvbi5tb2RlbCxcblx0XHRcdF9jb25kaXRpb25zOiBjb25kaXRpb24ubW9kZWwuZ2V0KCAnc2V0dGluZ3MnICkuY29uZGl0aW9uc1xuXHRcdH0gKTtcblxuXHRcdHZhciB2aWV3ID0gbmV3IENvbmRpdGlvbkdyb3Vwc1ZpZXcoIHtcblx0XHRcdGNvbGxlY3Rpb246IGNvbmRpdGlvbi5tb2RlbC5zdWJHcm91cHMsXG5cdFx0XHRyZWFjdGlvbjogY29uZGl0aW9uLnJlYWN0aW9uXG5cdFx0fSk7XG5cblx0XHRjb25kaXRpb24uJHNldHRpbmdzLmFwcGVuZCggdmlldy5yZW5kZXIoKS4kZWwgKTtcblxuXHRcdHJldHVybiAnJztcblx0fSxcblxuXHR2YWxpZGF0ZVNldHRpbmdzOiBmdW5jdGlvbiAoIGNvbmRpdGlvbiwgc2V0dGluZ3MsIGVycm9ycyApIHtcblxuXHRcdEV4dGVuc2lvbnMuZ2V0KCAnY29uZGl0aW9ucycgKS52YWxpZGF0ZUNvbmRpdGlvbnMoXG5cdFx0XHRbIGNvbmRpdGlvbi5zdWJHcm91cHMgXVxuXHRcdFx0LCBzZXR0aW5ncy5jb25kaXRpb25zXG5cdFx0XHQsIGVycm9yc1xuXHRcdCk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEVudGl0eUFycmF5Q29udGFpbnM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MuZXh0ZW5zaW9uLkNvbmRpdGlvbnMuY29uZGl0aW9uLkVxdWFsc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5Db25kaXRpb25cbiAqL1xuXG52YXIgQ29uZGl0aW9uID0gd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5Db25kaXRpb24sXG5cdEVxdWFscztcblxuRXF1YWxzID0gQ29uZGl0aW9uLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IHtcblx0XHRzbHVnOiAnZXF1YWxzJ1xuXHR9LFxuXG5cdHJlbmRlclNldHRpbmdzOiBmdW5jdGlvbiAoIGNvbmRpdGlvbiwgZmllbGROYW1lUHJlZml4ICkge1xuXG5cdFx0dmFyIGZpZWxkcyA9IHRoaXMuZ2V0KCAnZmllbGRzJyApLFxuXHRcdFx0YXJnID0gY29uZGl0aW9uLm1vZGVsLmdldEFyZygpO1xuXG5cdFx0Ly8gV2UgcmVuZGVyIHRoZSBgdmFsdWVgIGZpZWxkIGRpZmZlcmVudGx5IGJhc2VkIG9uIHRoZSB0eXBlIG9mIGFyZ3VtZW50LlxuXHRcdGlmICggYXJnICkge1xuXG5cdFx0XHR2YXIgdHlwZSA9IGFyZy5nZXQoICdfdHlwZScgKTtcblxuXHRcdFx0ZmllbGRzID0gXy5leHRlbmQoIHt9LCBmaWVsZHMgKTtcblxuXHRcdFx0c3dpdGNoICggdHlwZSApIHtcblxuXHRcdFx0XHRjYXNlICdhdHRyJzpcblx0XHRcdFx0XHRmaWVsZHMudmFsdWUgPSBfLmV4dGVuZChcblx0XHRcdFx0XHRcdHt9XG5cdFx0XHRcdFx0XHQsIGZpZWxkcy52YWx1ZVxuXHRcdFx0XHRcdFx0LCB7IHR5cGU6IGFyZy5nZXQoICdkYXRhX3R5cGUnICkgfVxuXHRcdFx0XHRcdCk7XG5cdFx0XHRcdFx0LyogZmFsbHMgdGhyb3VnaCAqL1xuXHRcdFx0XHRjYXNlICdlbnRpdHknOlxuXHRcdFx0XHRcdHZhciB2YWx1ZXMgPSBhcmcuZ2V0KCAndmFsdWVzJyApO1xuXG5cdFx0XHRcdFx0aWYgKCB2YWx1ZXMgKSB7XG5cblx0XHRcdFx0XHRcdGZpZWxkcy52YWx1ZSA9IF8uZXh0ZW5kKFxuXHRcdFx0XHRcdFx0XHR7fVxuXHRcdFx0XHRcdFx0XHQsIGZpZWxkcy52YWx1ZVxuXHRcdFx0XHRcdFx0XHQsIHsgdHlwZTogJ3NlbGVjdCcsIG9wdGlvbnM6IHZhbHVlcyB9XG5cdFx0XHRcdFx0XHQpO1xuXHRcdFx0XHRcdH1cblx0XHRcdH1cblxuXHRcdFx0dGhpcy5zZXQoICdmaWVsZHMnLCBmaWVsZHMgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gdGhpcy5jb25zdHJ1Y3Rvci5fX3N1cGVyX18ucmVuZGVyU2V0dGluZ3MuYXBwbHkoXG5cdFx0XHR0aGlzXG5cdFx0XHQsIFsgY29uZGl0aW9uLCBmaWVsZE5hbWVQcmVmaXggXVxuXHRcdCk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IEVxdWFscztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9uc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5jb250cm9sbGVyLkV4dGVuc2lvblxuICpcbiAqXG4gKi9cbnZhciBFeHRlbnNpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uLFxuXHRDb25kaXRpb25Hcm91cHMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3Vwcyxcblx0Q29uZGl0aW9uc0dyb3Vwc1ZpZXcgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBzLFxuXHRnZXREZWVwID0gd3Aud29yZHBvaW50cy5ob29rcy51dGlsLmdldERlZXAsXG5cdGRhdGEgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuZGF0YSxcblx0Q29uZGl0aW9ucztcblxuQ29uZGl0aW9ucyA9IEV4dGVuc2lvbi5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0c2x1ZzogJ2NvbmRpdGlvbnMnXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5hcmdGaWx0ZXJzID0gWyB0aGlzLm9ubHlFbnVtZXJhYmxlRW50aXRpZXMgXTtcblx0XHR0aGlzLmRhdGFUeXBlID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKCB7IGlkQXR0cmlidXRlOiAnc2x1ZycgfSApO1xuXHRcdHRoaXMuY29udHJvbGxlcnMgPSBuZXcgQmFja2JvbmUuQ29sbGVjdGlvbihcblx0XHRcdFtdXG5cdFx0XHQsIHsgY29tcGFyYXRvcjogJ3NsdWcnLCBtb2RlbDogdGhpcy5kYXRhVHlwZSB9XG5cdFx0KTtcblx0fSxcblxuXHRpbml0UmVhY3Rpb246IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHRyZWFjdGlvbi5jb25kaXRpb25zID0ge307XG5cdFx0cmVhY3Rpb24ubW9kZWwuY29uZGl0aW9ucyA9IHt9O1xuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSByZWFjdGlvbi5tb2RlbC5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0aWYgKCAhIGNvbmRpdGlvbnMgKSB7XG5cdFx0XHRjb25kaXRpb25zID0ge307XG5cdFx0fVxuXG5cdFx0dmFyIGFjdGlvblR5cGVzID0gXy5rZXlzKFxuXHRcdFx0ZGF0YS5ldmVudF9hY3Rpb25fdHlwZXNbIHJlYWN0aW9uLm1vZGVsLmdldCggJ2V2ZW50JyApIF1cblx0XHQpO1xuXG5cdFx0aWYgKCAhIGFjdGlvblR5cGVzICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGFjdGlvblR5cGVzID0gXy5pbnRlcnNlY3Rpb24oXG5cdFx0XHRyZWFjdGlvbi5SZWFjdG9yLmdldCggJ2FjdGlvbl90eXBlcycgKVxuXHRcdFx0LCBhY3Rpb25UeXBlc1xuXHRcdCk7XG5cblx0XHRfLmVhY2goIGFjdGlvblR5cGVzLCBmdW5jdGlvbiAoIGFjdGlvblR5cGUgKSB7XG5cblx0XHRcdHZhciBjb25kaXRpb25Hcm91cHMgPSBjb25kaXRpb25zWyBhY3Rpb25UeXBlIF07XG5cblx0XHRcdGlmICggISBjb25kaXRpb25Hcm91cHMgKSB7XG5cdFx0XHRcdGNvbmRpdGlvbkdyb3VwcyA9IFtdO1xuXHRcdFx0fVxuXG5cdFx0XHRyZWFjdGlvbi5tb2RlbC5jb25kaXRpb25zWyBhY3Rpb25UeXBlIF0gPSBuZXcgQ29uZGl0aW9uR3JvdXBzKCBudWxsLCB7XG5cdFx0XHRcdGhpZXJhcmNoeTogWyBhY3Rpb25UeXBlIF0sXG5cdFx0XHRcdHJlYWN0aW9uOiByZWFjdGlvbi5tb2RlbCxcblx0XHRcdFx0X2NvbmRpdGlvbnM6IGNvbmRpdGlvbkdyb3Vwc1xuXHRcdFx0fSApO1xuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0dmFyIGFwcGVuZGVkID0gZmFsc2U7XG5cblx0XHR0aGlzLmxpc3RlblRvKCByZWFjdGlvbiwgJ3JlbmRlcjpmaWVsZHMnLCBmdW5jdGlvbiAoICRlbCwgY3VycmVudEFjdGlvblR5cGUgKSB7XG5cblx0XHRcdHZhciBjb25kaXRpb25zVmlldyA9IHJlYWN0aW9uLmNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF07XG5cblx0XHRcdGlmICggISBjb25kaXRpb25zVmlldyApIHtcblx0XHRcdFx0Y29uZGl0aW9uc1ZpZXcgPSByZWFjdGlvbi5jb25kaXRpb25zWyBjdXJyZW50QWN0aW9uVHlwZSBdID0gbmV3IENvbmRpdGlvbnNHcm91cHNWaWV3KCB7XG5cdFx0XHRcdFx0Y29sbGVjdGlvbjogcmVhY3Rpb24ubW9kZWwuY29uZGl0aW9uc1sgY3VycmVudEFjdGlvblR5cGUgXSxcblx0XHRcdFx0XHRyZWFjdGlvbjogcmVhY3Rpb25cblx0XHRcdFx0fSk7XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIHdlJ3ZlIGFscmVhZHkgYXBwZW5kZWQgdGhlIGNvbnRhaW5lciB2aWV3IHRvIHRoZSByZWFjdGlvbiB2aWV3LFxuXHRcdFx0Ly8gdGhlbiB3ZSBkb24ndCBuZWVkIHRvIGRvIHRoYXQgYWdhaW4uXG5cdFx0XHRpZiAoIGFwcGVuZGVkICkge1xuXG5cdFx0XHRcdHZhciBjb25kaXRpb25zQ29sbGVjdGlvbiA9IHJlYWN0aW9uLm1vZGVsLmNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF07XG5cdFx0XHRcdHZhciBjb25kaXRpb25zID0gcmVhY3Rpb24ubW9kZWwuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdFx0XHRpZiAoICEgY29uZGl0aW9ucyApIHtcblx0XHRcdFx0XHRjb25kaXRpb25zID0ge307XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBIb3dldmVyLCB3ZSBkbyBuZWVkIHRvIHVwZGF0ZSB0aGUgY29uZGl0aW9uIGNvbGxlY3Rpb24sIGluIGNhc2Vcblx0XHRcdFx0Ly8gc29tZSBvZiB0aGUgY29uZGl0aW9uIG1vZGVscyBoYXZlIGJlZW4gcmVtb3ZlZCBvciBuZXcgb25lcyBhZGRlZC5cblx0XHRcdFx0Y29uZGl0aW9uc0NvbGxlY3Rpb24uc2V0KFxuXHRcdFx0XHRcdGNvbmRpdGlvbnNDb2xsZWN0aW9uLm1hcENvbmRpdGlvbkdyb3Vwcyhcblx0XHRcdFx0XHRcdGNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF0gfHwgW11cblx0XHRcdFx0XHQpXG5cdFx0XHRcdFx0LCB7IHBhcnNlOiB0cnVlIH1cblx0XHRcdFx0KTtcblxuXHRcdFx0XHQvLyBBbmQgdGhlbiByZS1yZW5kZXIgZXZlcnl0aGluZy5cblx0XHRcdFx0Y29uZGl0aW9uc1ZpZXcucmVuZGVyKCk7XG5cblx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0JGVsLmFwcGVuZCggY29uZGl0aW9uc1ZpZXcucmVuZGVyKCkuJGVsICk7XG5cblx0XHRcdFx0YXBwZW5kZWQgPSB0cnVlO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9LFxuXG5cdGdldERhdGFUeXBlRnJvbUFyZzogZnVuY3Rpb24gKCBhcmcgKSB7XG5cblx0XHR2YXIgYXJnVHlwZSA9IGFyZy5nZXQoICdfdHlwZScgKTtcblxuXHRcdHN3aXRjaCAoIGFyZ1R5cGUgKSB7XG5cblx0XHRcdGNhc2UgJ2F0dHInOlxuXHRcdFx0XHRyZXR1cm4gYXJnLmdldCggJ2RhdGFfdHlwZScgKTtcblxuXHRcdFx0Y2FzZSAnYXJyYXknOlxuXHRcdFx0XHRyZXR1cm4gJ2VudGl0eV9hcnJheSc7XG5cblx0XHRcdGRlZmF1bHQ6XG5cdFx0XHRcdHJldHVybiBhcmdUeXBlO1xuXHRcdH1cblx0fSxcblxuXHR2YWxpZGF0ZVJlYWN0aW9uOiBmdW5jdGlvbiAoIG1vZGVsLCBhdHRyaWJ1dGVzLCBlcnJvcnMsIG9wdGlvbnMgKSB7XG5cblx0XHQvLyBodHRwczovL2dpdGh1Yi5jb20vV29yZFBvaW50cy93b3JkcG9pbnRzL2lzc3Vlcy81MTkuXG5cdFx0aWYgKCAhIG9wdGlvbnMucmF3QXR0cy5jb25kaXRpb25zICkge1xuXHRcdFx0ZGVsZXRlIGF0dHJpYnV0ZXMuY29uZGl0aW9ucztcblx0XHRcdGRlbGV0ZSBtb2RlbC5hdHRyaWJ1dGVzLmNvbmRpdGlvbnM7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGhpcy52YWxpZGF0ZUNvbmRpdGlvbnMoIG1vZGVsLmNvbmRpdGlvbnMsIGF0dHJpYnV0ZXMuY29uZGl0aW9ucywgZXJyb3JzICk7XG5cdH0sXG5cblx0dmFsaWRhdGVDb25kaXRpb25zOiBmdW5jdGlvbiAoIGNvbmRpdGlvbnMsIHNldHRpbmdzLCBlcnJvcnMgKSB7XG5cblx0XHRfLmVhY2goIGNvbmRpdGlvbnMsIGZ1bmN0aW9uICggZ3JvdXBzICkge1xuXHRcdFx0Z3JvdXBzLmVhY2goIGZ1bmN0aW9uICggZ3JvdXAgKSB7XG5cdFx0XHRcdGdyb3VwLmdldCggJ2NvbmRpdGlvbnMnICkuZWFjaCggZnVuY3Rpb24gKCBjb25kaXRpb24gKSB7XG5cblx0XHRcdFx0XHR2YXIgbmV3RXJyb3JzID0gW10sXG5cdFx0XHRcdFx0XHRoaWVyYXJjaHkgPSBjb25kaXRpb24uZ2V0SGllcmFyY2h5KCkuY29uY2F0KFxuXHRcdFx0XHRcdFx0XHRbICdfY29uZGl0aW9ucycsIGNvbmRpdGlvbi5pZCBdXG5cdFx0XHRcdFx0XHQpO1xuXG5cdFx0XHRcdFx0aWYgKCBncm91cHMuaGllcmFyY2h5Lmxlbmd0aCA9PT0gMSApIHtcblx0XHRcdFx0XHRcdGhpZXJhcmNoeS51bnNoaWZ0KCBncm91cHMuaGllcmFyY2h5WzBdICk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y29uZGl0aW9uLnZhbGlkYXRlKFxuXHRcdFx0XHRcdFx0Z2V0RGVlcCggc2V0dGluZ3MsIGhpZXJhcmNoeSApXG5cdFx0XHRcdFx0XHQsIHt9XG5cdFx0XHRcdFx0XHQsIG5ld0Vycm9yc1xuXHRcdFx0XHRcdCk7XG5cblx0XHRcdFx0XHRpZiAoICEgXy5pc0VtcHR5KCBuZXdFcnJvcnMgKSApIHtcblxuXHRcdFx0XHRcdFx0aGllcmFyY2h5LnVuc2hpZnQoICdjb25kaXRpb25zJyApO1xuXHRcdFx0XHRcdFx0aGllcmFyY2h5LnB1c2goICdzZXR0aW5ncycgKTtcblxuXHRcdFx0XHRcdFx0Zm9yICggdmFyIGkgPSAwOyBpIDwgbmV3RXJyb3JzLmxlbmd0aDsgaSsrICkge1xuXG5cdFx0XHRcdFx0XHRcdG5ld0Vycm9yc1sgaSBdLmZpZWxkID0gaGllcmFyY2h5LmNvbmNhdChcblx0XHRcdFx0XHRcdFx0XHRfLmlzQXJyYXkoIG5ld0Vycm9yc1sgaSBdLmZpZWxkIClcblx0XHRcdFx0XHRcdFx0XHRcdD8gbmV3RXJyb3JzWyBpIF0uZmllbGRcblx0XHRcdFx0XHRcdFx0XHRcdDogWyBuZXdFcnJvcnNbIGkgXS5maWVsZCBdXG5cdFx0XHRcdFx0XHRcdCk7XG5cblx0XHRcdFx0XHRcdFx0ZXJyb3JzLnB1c2goIG5ld0Vycm9yc1sgaSBdICk7XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9KTtcblx0XHRcdH0pO1xuXHRcdH0pO1xuXHR9LFxuXG5cdGdldFR5cGU6IGZ1bmN0aW9uICggZGF0YVR5cGUsIHNsdWcgKSB7XG5cblx0XHRpZiAoIHR5cGVvZiB0aGlzLmRhdGEuY29uZGl0aW9uc1sgZGF0YVR5cGUgXSA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0aWYgKCB0eXBlb2YgdGhpcy5kYXRhLmNvbmRpdGlvbnNbIGRhdGFUeXBlIF1bIHNsdWcgXSA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIF8uY2xvbmUoIHRoaXMuZGF0YS5jb25kaXRpb25zWyBkYXRhVHlwZSBdWyBzbHVnIF0gKTtcblx0fSxcblxuXHQvLyBHZXQgYWxsIGNvbmRpdGlvbnMgZm9yIGEgY2VydGFpbiBkYXRhIHR5cGUuXG5cdGdldEJ5RGF0YVR5cGU6IGZ1bmN0aW9uICggZGF0YVR5cGUgKSB7XG5cblx0XHRyZXR1cm4gXy5jbG9uZSggdGhpcy5kYXRhLmNvbmRpdGlvbnNbIGRhdGFUeXBlIF0gKTtcblx0fSxcblxuXHRnZXRDb250cm9sbGVyOiBmdW5jdGlvbiAoIGRhdGFUeXBlU2x1Zywgc2x1ZyApIHtcblxuXHRcdHZhciBkYXRhVHlwZSA9IHRoaXMuY29udHJvbGxlcnMuZ2V0KCBkYXRhVHlwZVNsdWcgKSxcblx0XHRcdGNvbnRyb2xsZXI7XG5cblx0XHRpZiAoIGRhdGFUeXBlICkge1xuXHRcdFx0Y29udHJvbGxlciA9IGRhdGFUeXBlLmdldCggJ2NvbnRyb2xsZXJzJyApWyBzbHVnIF07XG5cdFx0fVxuXG5cdFx0aWYgKCAhIGNvbnRyb2xsZXIgKSB7XG5cdFx0XHRjb250cm9sbGVyID0gQ29uZGl0aW9ucy5Db25kaXRpb247XG5cdFx0fVxuXG5cdFx0dmFyIHR5cGUgPSB0aGlzLmdldFR5cGUoIGRhdGFUeXBlU2x1Zywgc2x1ZyApO1xuXG5cdFx0aWYgKCAhIHR5cGUgKSB7XG5cdFx0XHR0eXBlID0geyBzbHVnOiBzbHVnIH07XG5cdFx0fVxuXG5cdFx0cmV0dXJuIG5ldyBjb250cm9sbGVyKCB0eXBlICk7XG5cdH0sXG5cblx0cmVnaXN0ZXJDb250cm9sbGVyOiBmdW5jdGlvbiAoIGRhdGFUeXBlU2x1Zywgc2x1ZywgY29udHJvbGxlciApIHtcblxuXHRcdHZhciBkYXRhVHlwZSA9IHRoaXMuY29udHJvbGxlcnMuZ2V0KCBkYXRhVHlwZVNsdWcgKTtcblxuXHRcdGlmICggISBkYXRhVHlwZSApIHtcblx0XHRcdGRhdGFUeXBlID0gbmV3IHRoaXMuZGF0YVR5cGUoe1xuXHRcdFx0XHRzbHVnOiBkYXRhVHlwZVNsdWcsXG5cdFx0XHRcdGNvbnRyb2xsZXJzOiB7fVxuXHRcdFx0fSk7XG5cblx0XHRcdHRoaXMuY29udHJvbGxlcnMuYWRkKCBkYXRhVHlwZSApO1xuXHRcdH1cblxuXHRcdGRhdGFUeXBlLmdldCggJ2NvbnRyb2xsZXJzJyApWyBzbHVnIF0gPSBjb250cm9sbGVyO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBBcmcgZmlsdGVyIHRvIGRpc2FsbG93IGlkZW50aXR5IGNvbmRpdGlvbnMgb24gZW50aXRpZXMgdGhhdCBhcmVuJ3QgZW51bWVyYWJsZS5cblx0ICovXG5cdG9ubHlFbnVtZXJhYmxlRW50aXRpZXM6IGZ1bmN0aW9uICggYXJnLCBkYXRhVHlwZSwgaXNFbnRpdHlBcnJheSwgY29uZGl0aW9ucyApIHtcblxuXHRcdGlmICggZGF0YVR5cGUgPT09ICdlbnRpdHknICYmIF8uaXNFbXB0eSggYXJnLmdldCggJ3ZhbHVlcycgKSApICkge1xuXHRcdFx0ZGVsZXRlIGNvbmRpdGlvbnMuZXF1YWxzO1xuXHRcdH1cblxuXHRcdHJldHVybiB0cnVlO1xuXHR9XG5cbn0gKTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25zO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3VwXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuQ29sbGVjdGlvblxuICovXG52YXIgQ29uZGl0aW9ucyA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9ucyxcblx0Q29uZGl0aW9uR3JvdXA7XG5cbi8vIFRoaXMgaXMgYSBtb2RlbCBhbHRob3VnaCB3ZSBvcmlnaW5hbGx5IHRob3VnaHQgaXQgb3VnaHQgdG8gYmUgYSBjb2xsZWN0aW9uLFxuLy8gYmVjYXVzZSBCYWNrYm9uZSBkb2Vzbid0IHN1cHBvcnQgc3ViLWNvbGxlY3Rpb25zLiBUaGlzIGlzIHRoZSBjbG9zZXN0IHRoaW5nXG4vLyB0byBhIHN1Yi1jb2xsZWN0aW9uLiBTZWUgaHR0cHM6Ly9zdGFja292ZXJmbG93LmNvbS9xLzEwMzg4MTk5LzE5MjQxMjguXG5Db25kaXRpb25Hcm91cCA9IEJhY2tib25lLk1vZGVsLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4ge1xuXHRcdFx0aWQ6ICcnLFxuXHRcdFx0aGllcmFyY2h5OiBbXSxcblx0XHRcdGNvbmRpdGlvbnM6IG5ldyBDb25kaXRpb25zKCksXG5cdFx0XHRncm91cHM6IG51bGwsXG5cdFx0XHRyZWFjdGlvbjogbnVsbFxuXHRcdH07XG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBhdHRyaWJ1dGVzICkge1xuXG5cdFx0Ly8gU2V0IHVwIGV2ZW50IHByb3h5aW5nLlxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuYXR0cmlidXRlcy5jb25kaXRpb25zLCAnYWxsJywgdGhpcy50cmlnZ2VyICk7XG5cblx0XHQvLyBBZGQgdGhlIGNvbmRpdGlvbnMgdG8gdGhlIGNvbGxlY3Rpb24uXG5cdFx0aWYgKCBhdHRyaWJ1dGVzLl9jb25kaXRpb25zICkge1xuXHRcdFx0dGhpcy5yZXNldCggYXR0cmlidXRlcy5fY29uZGl0aW9ucyApO1xuXHRcdH1cblx0fSxcblxuXHQvLyBNYWtlIHN1cmUgdGhhdCB0aGUgbW9kZWwgaWRzIGFyZSBwcm9wZXJseSBzZXQuIENvbmRpdGlvbnMgYXJlIGlkZW50aWZpZWRcblx0Ly8gYnkgdGhlIGluZGV4IG9mIHRoZSBhcnJheSBpbiB3aGljaCB0aGV5IGFyZSBzdG9yZWQuIFdlIGNvcHkgdGhlIGtleXMgdG9cblx0Ly8gdGhlIGlkIGF0dHJpYnV0ZXMgb2YgdGhlIG1vZGVscy5cblx0cmVzZXQ6IGZ1bmN0aW9uICggbW9kZWxzLCBvcHRpb25zICkge1xuXG5cdFx0b3B0aW9ucyA9IG9wdGlvbnMgfHwge307XG5cdFx0b3B0aW9ucy5ncm91cCA9IHRoaXM7XG5cblx0XHR2YXIgY29uZGl0aW9ucyA9IHRoaXMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdHRoaXMuc2V0SWRzKCBtb2RlbHMsIDAgKTtcblxuXHRcdHJldHVybiBjb25kaXRpb25zLnJlc2V0LmNhbGwoIGNvbmRpdGlvbnMsIG1vZGVscywgb3B0aW9ucyApO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBVcGRhdGUgdGhlIGNvbmRpdGlvbnMgY29sbGVjdGlvbi5cblx0ICpcblx0ICogVGhpcyBpcyBhIHdyYXBwZXIgZm9yIHRoZSBgc2V0KClgIG1ldGhvZCBvZiB0aGUgY29sbGVjdGlvbiBzdG9yZWQgaW4gdGhlXG5cdCAqIGBjb25kaXRpb25zYCBhdHRyaWJ1dGUgb2YgdGhpcyBNb2RlbC4gSXQgZW5zdXJlcyB0aGF0IHRoZSBwYXNzZWQgbW9kZWxcblx0ICogb2JqZWN0cyBoYXZlIGJlZW4gZ2l2ZW4gcHJvcGVyIElEcywgYW5kIHNldHMgb3B0aW9ucy5ncm91cCB0byB0aGlzIG9iamVjdC5cblx0ICpcblx0ICogTm90ZSB0aGF0IHRoZSBgX2NvbmRpdGlvbnNgIGF0dHJpYnV0ZSBpdHNlbGYgaXMgbm90IG1vZGlmaWVkLCBvbmx5IHRoZVxuXHQgKiBjb2xsZWN0aW9uIHRoYXQgaXMgc3RvcmVkIGluIHRoZSBgY29uZGl0aW9uc2AgYXR0cmlidXRlLlxuXHQgKlxuXHQgKiBAc2luY2UgMi4xLjNcblx0ICpcblx0ICogQHBhcmFtIHtPYmplY3RbXX0gbW9kZWxzICAgICAgICAgICAgICAgICAgICAtIFRoZSBjb25kaXRpb25zLlxuXHQgKiBAcGFyYW0ge09iamVjdH0gICBbb3B0aW9ucz17IGdyb3VwOiB0aGlzIH1dIC0gT3B0aW9ucyB0byBwYXNzIHRvXG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgQ29uZGl0aW9ucy5zZXQoKWAuIFRoZSBgZ3JvdXBgXG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB3aWxsIGFsd2F5cyBiZSBzZXQgdG8gYHRoaXNgLlxuXHQgKlxuXHQgKiBAcmV0dXJucyB7T2JqZWN0W119IFRoZSBhZGRlZCBtb2RlbHMuXG5cdCAqL1xuXHRzZXRDb25kaXRpb25zOiBmdW5jdGlvbiAoIG1vZGVscywgb3B0aW9ucyApIHtcblxuXHRcdG9wdGlvbnMgPSBvcHRpb25zIHx8IHt9O1xuXHRcdG9wdGlvbnMuZ3JvdXAgPSB0aGlzO1xuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSB0aGlzLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHR0aGlzLnNldElkcyggbW9kZWxzLCAwICk7XG5cblx0XHRyZXR1cm4gY29uZGl0aW9ucy5zZXQuY2FsbCggY29uZGl0aW9ucywgbW9kZWxzLCBvcHRpb25zICk7XG5cdH0sXG5cblx0YWRkOiBmdW5jdGlvbiAoIG1vZGVscywgb3B0aW9ucyApIHtcblxuXHRcdG9wdGlvbnMgPSBvcHRpb25zIHx8IHt9O1xuXHRcdG9wdGlvbnMuZ3JvdXAgPSB0aGlzO1xuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSB0aGlzLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHR0aGlzLnNldElkcyggbW9kZWxzLCB0aGlzLmdldE5leHRJZCgpICk7XG5cblx0XHRyZXR1cm4gY29uZGl0aW9ucy5hZGQuY2FsbCggY29uZGl0aW9ucywgbW9kZWxzLCBvcHRpb25zICk7XG5cdH0sXG5cblx0Z2V0TmV4dElkOiBmdW5jdGlvbigpIHtcblxuXHRcdHZhciBjb25kaXRpb25zID0gdGhpcy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0aWYgKCAhY29uZGl0aW9ucy5sZW5ndGggKSB7XG5cdFx0XHRyZXR1cm4gMDtcblx0XHR9XG5cblx0XHRyZXR1cm4gcGFyc2VJbnQoIGNvbmRpdGlvbnMuc29ydCgpLmxhc3QoKS5nZXQoICdpZCcgKSwgMTAgKSArIDE7XG5cdH0sXG5cblx0c2V0SWRzOiBmdW5jdGlvbiAoIG1vZGVscywgc3RhcnRJZCApIHtcblxuXHRcdGlmICggISBtb2RlbHMgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Xy5lYWNoKCBfLmlzQXJyYXkoIG1vZGVscyApID8gbW9kZWxzIDogWyBtb2RlbHMgXSwgZnVuY3Rpb24gKCBtb2RlbCwgaWQgKSB7XG5cblx0XHRcdGlmICggc3RhcnRJZCAhPT0gMCApIHtcblx0XHRcdFx0bW9kZWwuaWQgPSBzdGFydElkKys7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRtb2RlbC5pZCA9IGlkO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBUaGlzIHdpbGwgYmUgc2V0IHdoZW4gYW4gb2JqZWN0IGlzIGNvbnZlcnRlZCB0byBhIG1vZGVsLCBidXQgaWYgaXQgaXNcblx0XHRcdC8vIGEgbW9kZWwgYWxyZWFkeSwgd2UgbmVlZCB0byBzZXQgaXQgaGVyZS5cblx0XHRcdGlmICggbW9kZWwgaW5zdGFuY2VvZiBCYWNrYm9uZS5Nb2RlbCApIHtcblx0XHRcdFx0bW9kZWwuZ3JvdXAgPSB0aGlzO1xuXHRcdFx0fVxuXG5cdFx0fSwgdGhpcyApO1xuXHR9LFxuXG5cdHN5bmM6IGZ1bmN0aW9uICggbWV0aG9kLCBjb2xsZWN0aW9uLCBvcHRpb25zICkge1xuXHRcdG9wdGlvbnMuZXJyb3IoXG5cdFx0XHR7IG1lc3NhZ2U6ICdGZXRjaGluZyBhbmQgc2F2aW5nIGdyb3VwcyBvZiBob29rIGNvbmRpdGlvbnMgaXMgbm90IHN1cHBvcnRlZC4nIH1cblx0XHQpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25Hcm91cDtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25Hcm91cHNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBDb25kaXRpb25Hcm91cCA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXAsXG5cdEFyZ3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MsXG5cdENvbmRpdGlvbkdyb3VwcztcblxuLyoqXG4gKiBPYmplY3QgZm9ybWF0IGZvciBtb2RlbHMgZXhwZWN0ZWQgYnkgdGhpcyBjb2xsZWN0aW9uLlxuICpcbiAqIEB0eXBlZGVmIHtPYmplY3R9IFJhd0NvbmRpdGlvbkdyb3VwXG4gKlxuICogQHByb3BlcnR5IHtzdHJpbmd9ICAgICAgICAgIGlkICAgICAgICAgIC0gVGhlIElEIG9mIHRoZSBncm91cC5cbiAqIEBwcm9wZXJ0eSB7QXJyYXl9ICAgICAgICAgICBoaWVyYXJjaHkgICAtIFRoZSBoaWVyYXJjaHkgZm9yIHRoZSBncm91cC5cbiAqIEBwcm9wZXJ0eSB7Q29uZGl0aW9uR3JvdXBzfSBncm91cHMgICAgICAtIFRoZSBjb2xsZWN0aW9uIGZvciB0aGUgZ3JvdXAuXG4gKiBAcHJvcGVydHkge0FycmF5fSAgICAgICAgICAgX2NvbmRpdGlvbnMgLSBUaGUgY29uZGl0aW9ucyBpbiB0aGUgZ3JvdXAuXG4gKi9cblxuQ29uZGl0aW9uR3JvdXBzID0gQmFja2JvbmUuQ29sbGVjdGlvbi5leHRlbmQoe1xuXG5cdG1vZGVsOiBDb25kaXRpb25Hcm91cCxcblxuXHRoaWVyYXJjaHk6IFtdLFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggbW9kZWxzLCBvcHRpb25zICkge1xuXG5cdFx0aWYgKCBvcHRpb25zLmFyZ3MgKSB7XG5cdFx0XHR0aGlzLmFyZ3MgPSBvcHRpb25zLmFyZ3M7XG5cdFx0fVxuXG5cdFx0aWYgKCBvcHRpb25zLmhpZXJhcmNoeSApIHtcblx0XHRcdHRoaXMuaGllcmFyY2h5ID0gb3B0aW9ucy5oaWVyYXJjaHk7XG5cdFx0fVxuXG5cdFx0aWYgKCBvcHRpb25zLnJlYWN0aW9uICkge1xuXHRcdFx0dGhpcy5yZWFjdGlvbiA9IG9wdGlvbnMucmVhY3Rpb247XG5cdFx0fVxuXG5cdFx0aWYgKCBvcHRpb25zLl9jb25kaXRpb25zICkge1xuXHRcdFx0dGhpcy5tYXBDb25kaXRpb25zKCBvcHRpb25zLl9jb25kaXRpb25zICk7XG5cdFx0fVxuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBDb252ZXJ0cyBhIGNvbmRpdGlvbnMgaGllcmFyY2h5IGludG8gYW4gYXJyYXkgb2YgY29uZGl0aW9uIGdyb3Vwcy5cblx0ICpcblx0ICogVGhlIGNvbmRpdGlvbnMsIGFzIHNhdmVkIGluIHRoZSBkYXRhYmFzZSwgYXJlIGluIGEgbmVzdGVkIGhpZXJhcmNoeSBiYXNlZCBvblxuXHQgKiB3aGljaCAoc3ViKWFyZ3MgdGhleSBhcmUgZm9yLiBUaGVyZWZvcmUgaXQgaXMgbmVjZXNzYXJ5IHRvIHBhcnNlIHRoZSBoaWVyYXJjaHlcblx0ICogaW50byBhIHNpbXBsZSBhcnJheSBjb250YWluaW5nIHRoZSBjb25kaXRpb24gaW5mb3JtYXRpb24gYW5kIHRoZSBhcmcgaGllcmFyY2h5XG5cdCAqIGZvciBpdC5cblx0ICpcblx0ICogQHNpbmNlIDIuMS4zXG5cdCAqXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSAgICAgICAgICAgICAgY29uZGl0aW9ucyAgICAgLSBUaGUgY29uZGl0aW9ucyBoaWVyYXJjaHkuXG5cdCAqIEBwYXJhbSB7UmF3Q29uZGl0aW9uR3JvdXBbXX0gW2dyb3Vwcz1bXV0gICAgLSBUaGUgYXJyYXkgb2YgY29uZGl0aW9uIGdyb3Vwcy5cblx0ICogQHBhcmFtIHtBcnJheX0gICAgICAgICAgICAgICBbaGllcmFyY2h5PVtdXSAtIFRoZSBjdXJyZW50IGxvY2F0aW9uIHdpdGhpbiB0aGVcblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmRpdGlvbnMgaGllcmFyY2h5LlxuXHQgKlxuXHQgKiBAcmV0dXJucyB7UmF3Q29uZGl0aW9uR3JvdXBbXX0gVGhlIHBhcnNlZCBncm91cHMgaW4gdGhlIGZvcm1hdCBmb3IgbW9kZWxzXG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBleHBlY3RlZCBieSB0aGlzIGNvbGxlY3Rpb24uXG5cdCAqL1xuXHRtYXBDb25kaXRpb25Hcm91cHM6IGZ1bmN0aW9uICggY29uZGl0aW9ucywgZ3JvdXBzLCBoaWVyYXJjaHkgKSB7XG5cblx0XHRoaWVyYXJjaHkgPSBoaWVyYXJjaHkgfHwgW107XG5cdFx0Z3JvdXBzID0gZ3JvdXBzIHx8IFtdO1xuXG5cdFx0Xy5lYWNoKCBjb25kaXRpb25zLCBmdW5jdGlvbiAoIGFyZywgc2x1ZyApIHtcblxuXHRcdFx0aWYgKCBzbHVnID09PSAnX2NvbmRpdGlvbnMnICkge1xuXG5cdFx0XHRcdGdyb3Vwcy5wdXNoKCB7XG5cdFx0XHRcdFx0aWQ6ICAgICAgICAgIHRoaXMuZ2V0SWRGcm9tSGllcmFyY2h5KCBoaWVyYXJjaHkgKSxcblx0XHRcdFx0XHRoaWVyYXJjaHk6ICAgXy5jbG9uZSggaGllcmFyY2h5ICksXG5cdFx0XHRcdFx0Z3JvdXBzOiAgICAgIHRoaXMsXG5cdFx0XHRcdFx0X2NvbmRpdGlvbnM6IF8udG9BcnJheSggYXJnIClcblx0XHRcdFx0fSApO1xuXG5cdFx0XHR9IGVsc2Uge1xuXG5cdFx0XHRcdGhpZXJhcmNoeS5wdXNoKCBzbHVnICk7XG5cblx0XHRcdFx0dGhpcy5tYXBDb25kaXRpb25Hcm91cHMoIGFyZywgZ3JvdXBzLCBoaWVyYXJjaHkgKTtcblxuXHRcdFx0XHRoaWVyYXJjaHkucG9wKCk7XG5cdFx0XHR9XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHRyZXR1cm4gZ3JvdXBzO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBQYXJzZXMgYSBjb25kaXRpb25zIGhpZXJhcmNoeSBhbmQgYWRkcyBlYWNoIGdyb3VwIHRvIHRoZSBjb2xsZWN0aW9uLlxuXHQgKlxuXHQgKiBAc2luY2UgMi4xLjBcblx0ICogQHNpbmNlIDIuMS4zIFRoZSBoaWVyYXJjaHkgYXJnIHdhcyBkZXByZWNhdGVkLlxuXHQgKlxuXHQgKiBAcGFyYW0ge0FycmF5fSBjb25kaXRpb25zICAtIFRoZSByYXcgY29uZGl0aW9ucyBoaWVyYXJjaHkgdG8gcGFyc2UuXG5cdCAqIEBwYXJhbSB7QXJyYXl9IFtoaWVyYXJjaHldIC0gRGVwcmVjYXRlZC4gUHJldmlvdXNseSB1c2VkIHRvIHRyYWNrIHRoZSBjdXJyZW50XG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24gd2l0aGluIHRoZSBjb25kaXRpb25zIGhpZXJhcmNoeS5cblx0ICovXG5cdG1hcENvbmRpdGlvbnM6IGZ1bmN0aW9uICggY29uZGl0aW9ucywgaGllcmFyY2h5ICkge1xuXG5cdFx0dmFyIGdyb3VwcyA9IHRoaXMubWFwQ29uZGl0aW9uR3JvdXBzKCBjb25kaXRpb25zLCBbXSwgaGllcmFyY2h5ICk7XG5cblx0XHR0aGlzLnJlc2V0KCBncm91cHMgKTtcblx0fSxcblxuXHRnZXRJZEZyb21IaWVyYXJjaHk6IGZ1bmN0aW9uICggaGllcmFyY2h5ICkge1xuXHRcdHJldHVybiBoaWVyYXJjaHkuam9pbiggJy4nICk7XG5cdH0sXG5cblx0Z2V0QXJnczogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGFyZ3MgPSB0aGlzLmFyZ3M7XG5cblx0XHRpZiAoICEgYXJncyApIHtcblx0XHRcdGFyZ3MgPSBBcmdzLmdldEV2ZW50QXJncyggdGhpcy5yZWFjdGlvbi5nZXQoICdldmVudCcgKSApO1xuXHRcdH1cblxuXHRcdHJldHVybiBhcmdzO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBAc3VtbWFyeSBQYXJzZXMgYSByYXcgdmFsdWUgaW50byBhIGxpc3Qgb2YgbW9kZWxzLlxuXHQgKlxuXHQgKiBJbXBsZW1lbnRlZCBoZXJlIHNvIGlmIHRoZSBtb2RlbHMgYXJlIGdvaW5nIHRvIGJlIG1lcmdlZCB3aXRoIGNvcnJlc3BvbmRpbmdcblx0ICogb25lcyBpbiB0aGUgZXhpc3RpbmcgY29sbGVjdGlvbiwgd2UgY2FuIGdvIGFoZWFkIGFuZCB1cGRhdGUgdGhlIGBjb25kaXRpb25zYFxuXHQgKiBjb2xsZWN0aW9uIG9mIHRoZSBleGlzdGluZyBtb2RlbHMgYmFzZWQgb24gdGhlaXIgcGFzc2VkIGluIGBfY29uZGl0aW9uc2Bcblx0ICogYXR0cmlidXRlLiBPdGhlcndpc2UgdGhlIGNvbmRpdGlvbnMgY29sbGVjdGlvbiB3b3VsZCBub3QgYmUgdXBkYXRlZC4gU2VlIFt0aGVcblx0ICogZGlzY3Vzc2lvbiBvbiBHaXRIdWJde0BsaW5rIGh0dHBzOi8vZ2l0aHViLmNvbS9Xb3JkUG9pbnRzL3dvcmRwb2ludHMvaXNzdWVzL1xuICAgICAqIDUxNyNpc3N1ZWNvbW1lbnQtMjUwMzA3MTQ3fSBmb3IgbW9yZSBpbmZvcm1hdGlvbiBvbiB3aHkgd2UgZG8gaXQgdGhpcyB3YXkuXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjEuM1xuXHQgKlxuXHQgKiBAcGFyYW0ge09iamVjdHxPYmplY3RbXX0gcmVzcCAgICAtIFRoZSByYXcgbW9kZWwocykuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSAgICAgICAgICBvcHRpb25zIC0gT3B0aW9ucyBwYXNzZWQgZnJvbSBgc2V0KClgLlxuXHQgKlxuXHQgKiBAcmV0dXJucyB7T2JqZWN0fE9iamVjdFtdfSBUaGUgY29uZGl0aW9uIG1vZGVscywgd2l0aCBgY29uZGl0aW9uc2AgcHJvcGVydHlcblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgc2V0IGFzIG5lZWRlZC5cblx0ICovXG5cdHBhcnNlOiBmdW5jdGlvbiAoIHJlc3AsIG9wdGlvbnMgKSB7XG5cblx0XHRpZiAoICEgb3B0aW9ucy5tZXJnZSApIHtcblx0XHRcdHJldHVybiByZXNwO1xuXHRcdH1cblxuXHRcdHZhciBtb2RlbHMgPSBfLmlzQXJyYXkoIHJlc3AgKSA/IHJlc3AgOiBbcmVzcF0sXG5cdFx0XHRtb2RlbDtcblxuXHRcdGZvciAoIHZhciBpID0gMDsgaSA8IG1vZGVscy5sZW5ndGg7IGkrKyApIHtcblxuXHRcdFx0bW9kZWwgPSB0aGlzLmdldCggbW9kZWxzWyBpIF0uaWQgKTtcblxuXHRcdFx0aWYgKCAhIG1vZGVsICkge1xuXHRcdFx0XHRjb250aW51ZTtcblx0XHRcdH1cblxuXHRcdFx0bW9kZWwuc2V0Q29uZGl0aW9ucyggbW9kZWxzWyBpIF0uX2NvbmRpdGlvbnMsIG9wdGlvbnMgKTtcblxuXHRcdFx0bW9kZWxzWyBpIF0uY29uZGl0aW9ucyA9IG1vZGVsLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHJlc3A7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbkdyb3VwcztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2UsXG5cdENvbmRpdGlvblR5cGU7XG5cbkNvbmRpdGlvblR5cGUgPSBCYXNlLmV4dGVuZCh7XG5cdGlkQXR0cmlidXRlOiAnc2x1Zydcbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvblR5cGU7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uVHlwZXNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBDb25kaXRpb25UeXBlID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlLFxuXHRDb25kaXRpb25UeXBlcztcblxuQ29uZGl0aW9uVHlwZXMgPSBCYWNrYm9uZS5Db2xsZWN0aW9uLmV4dGVuZCh7XG5cblx0bW9kZWw6IENvbmRpdGlvblR5cGVcblxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uVHlwZXM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkJhc2UsXG5cdEFyZ3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MsXG5cdEV4dGVuc2lvbnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkV4dGVuc2lvbnMsXG5cdEZpZWxkcyA9IHdwLndvcmRwb2ludHMuaG9va3MuRmllbGRzLFxuXHRDb25kaXRpb247XG5cbkNvbmRpdGlvbiA9IEJhc2UuZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdHR5cGU6ICcnLFxuXHRcdHNldHRpbmdzOiBbXVxuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggYXR0cmlidXRlcywgb3B0aW9ucyApIHtcblx0XHRpZiAoIG9wdGlvbnMuZ3JvdXAgKSB7XG5cdFx0XHR0aGlzLmdyb3VwID0gb3B0aW9ucy5ncm91cDtcblx0XHR9XG5cdH0sXG5cblx0dmFsaWRhdGU6IGZ1bmN0aW9uICggYXR0cmlidXRlcywgb3B0aW9ucywgZXJyb3JzICkge1xuXG5cdFx0ZXJyb3JzID0gZXJyb3JzIHx8IFtdO1xuXG5cdFx0dmFyIGNvbmRpdGlvblR5cGUgPSB0aGlzLmdldFR5cGUoKTtcblxuXHRcdGlmICggISBjb25kaXRpb25UeXBlICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHZhciBmaWVsZHMgPSBjb25kaXRpb25UeXBlLmZpZWxkcztcblxuXHRcdEZpZWxkcy52YWxpZGF0ZShcblx0XHRcdGZpZWxkc1xuXHRcdFx0LCBhdHRyaWJ1dGVzLnNldHRpbmdzXG5cdFx0XHQsIGVycm9yc1xuXHRcdCk7XG5cblx0XHR2YXIgY29udHJvbGxlciA9IHRoaXMuZ2V0Q29udHJvbGxlcigpO1xuXG5cdFx0aWYgKCBjb250cm9sbGVyICkge1xuXHRcdFx0Y29udHJvbGxlci52YWxpZGF0ZVNldHRpbmdzKCB0aGlzLCBhdHRyaWJ1dGVzLnNldHRpbmdzLCBlcnJvcnMgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gZXJyb3JzO1xuXHR9LFxuXG5cdGdldENvbnRyb2xsZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBhcmcgPSB0aGlzLmdldEFyZygpO1xuXG5cdFx0aWYgKCAhIGFyZyApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHR2YXIgQ29uZGl0aW9ucyA9IEV4dGVuc2lvbnMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdHJldHVybiBDb25kaXRpb25zLmdldENvbnRyb2xsZXIoXG5cdFx0XHRDb25kaXRpb25zLmdldERhdGFUeXBlRnJvbUFyZyggYXJnIClcblx0XHRcdCwgdGhpcy5nZXQoICd0eXBlJyApXG5cdFx0KTtcblx0fSxcblxuXHRnZXRUeXBlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgYXJnID0gdGhpcy5nZXRBcmcoKTtcblxuXHRcdGlmICggISBhcmcgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0dmFyIENvbmRpdGlvbnMgPSBFeHRlbnNpb25zLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHRyZXR1cm4gQ29uZGl0aW9ucy5nZXRUeXBlKFxuXHRcdFx0Q29uZGl0aW9ucy5nZXREYXRhVHlwZUZyb21BcmcoIGFyZyApXG5cdFx0XHQsIHRoaXMuZ2V0KCAndHlwZScgKVxuXHRcdCk7XG5cdH0sXG5cblx0Z2V0QXJnOiBmdW5jdGlvbiAoKSB7XG5cblx0XHRpZiAoICEgdGhpcy5hcmcgKSB7XG5cblx0XHRcdHZhciBhcmdzID0gQXJncy5nZXRBcmdzRnJvbUhpZXJhcmNoeShcblx0XHRcdFx0dGhpcy5nZXRIaWVyYXJjaHkoKVxuXHRcdFx0XHQsIHRoaXMucmVhY3Rpb24uZ2V0KCAnZXZlbnQnIClcblx0XHRcdCk7XG5cblx0XHRcdGlmICggYXJncyApIHtcblx0XHRcdFx0dGhpcy5hcmcgPSBhcmdzWyBhcmdzLmxlbmd0aCAtIDEgXTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHRyZXR1cm4gdGhpcy5hcmc7XG5cdH0sXG5cblx0Z2V0SGllcmFyY2h5OiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIHRoaXMuZ3JvdXAuZ2V0KCAnaGllcmFyY2h5JyApO1xuXHR9LFxuXG5cdGdldEZ1bGxIaWVyYXJjaHk6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHJldHVybiB0aGlzLmdyb3VwLmdldCggJ2dyb3VwcycgKS5oaWVyYXJjaHkuY29uY2F0KFxuXHRcdFx0dGhpcy5nZXRIaWVyYXJjaHkoKVxuXHRcdCk7XG5cdH0sXG5cblx0aXNOZXc6IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4gJ3VuZGVmaW5lZCcgPT09IHR5cGVvZiB0aGlzLnJlYWN0aW9uLmdldChcblx0XHRcdFsgJ2NvbmRpdGlvbnMnIF1cblx0XHRcdFx0LmNvbmNhdCggdGhpcy5nZXRGdWxsSGllcmFyY2h5KCkgKVxuXHRcdFx0XHQuY29uY2F0KCBbICdfY29uZGl0aW9ucycsIHRoaXMuaWQgXSApXG5cdFx0KTtcblx0fSxcblxuXHRzeW5jOiBmdW5jdGlvbiAoIG1ldGhvZCwgbW9kZWwsIG9wdGlvbnMgKSB7XG5cdFx0b3B0aW9ucy5lcnJvcihcblx0XHRcdHsgbWVzc2FnZTogJ0ZldGNoaW5nIGFuZCBzYXZpbmcgaG9vayBjb25kaXRpb25zIGlzIG5vdCBzdXBwb3J0ZWQuJyB9XG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbnNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBDb25kaXRpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbixcblx0Q29uZGl0aW9ucztcblxuQ29uZGl0aW9ucyA9IEJhY2tib25lLkNvbGxlY3Rpb24uZXh0ZW5kKHtcblxuXHQvLyBSZWZlcmVuY2UgdG8gdGhpcyBjb2xsZWN0aW9uJ3MgbW9kZWwuXG5cdG1vZGVsOiBDb25kaXRpb24sXG5cblx0Y29tcGFyYXRvcjogJ2lkJyxcblxuXHRzeW5jOiBmdW5jdGlvbiAoIG1ldGhvZCwgY29sbGVjdGlvbiwgb3B0aW9ucyApIHtcblx0XHRvcHRpb25zLmVycm9yKFxuXHRcdFx0eyBtZXNzYWdlOiAnRmV0Y2hpbmcgYW5kIHNhdmluZyBob29rIGNvbmRpdGlvbnMgaXMgbm90IHN1cHBvcnRlZC4nIH1cblx0XHQpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25zO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0Q29uZGl0aW9uID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvbixcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0JCA9IEJhY2tib25lLiQsXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0Q29uZGl0aW9uR3JvdXA7XG5cbkNvbmRpdGlvbkdyb3VwID0gQmFzZS5leHRlbmQoe1xuXG5cdGNsYXNzTmFtZTogJ2NvbmRpdGlvbi1ncm91cCcsXG5cblx0dGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1yZWFjdGlvbi1jb25kaXRpb24tZ3JvdXAnICksXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2FkZCcsIHRoaXMuYWRkT25lICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ3Jlc2V0JywgdGhpcy5yZW5kZXIgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAncmVtb3ZlJywgdGhpcy5tYXliZUhpZGUgKTtcblxuXHRcdHRoaXMubW9kZWwub24oICdhZGQnLCB0aGlzLnJlYWN0aW9uLmxvY2tPcGVuLCB0aGlzLnJlYWN0aW9uICk7XG5cdFx0dGhpcy5tb2RlbC5vbiggJ3JlbW92ZScsIHRoaXMucmVhY3Rpb24ubG9ja09wZW4sIHRoaXMucmVhY3Rpb24gKTtcblx0XHR0aGlzLm1vZGVsLm9uKCAncmVzZXQnLCB0aGlzLnJlYWN0aW9uLmxvY2tPcGVuLCB0aGlzLnJlYWN0aW9uICk7XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRlbC5odG1sKCB0aGlzLnRlbXBsYXRlKCkgKTtcblxuXHRcdHRoaXMubWF5YmVIaWRlKCk7XG5cblx0XHR0aGlzLiQoICcuY29uZGl0aW9uLWdyb3VwLXRpdGxlJyApLnRleHQoXG5cdFx0XHRBcmdzLmJ1aWxkSGllcmFyY2h5SHVtYW5JZChcblx0XHRcdFx0QXJncy5nZXRBcmdzRnJvbUhpZXJhcmNoeShcblx0XHRcdFx0XHR0aGlzLm1vZGVsLmdldCggJ2hpZXJhcmNoeScgKVxuXHRcdFx0XHRcdCwgdGhpcy5yZWFjdGlvbi5tb2RlbC5nZXQoICdldmVudCcgKVxuXHRcdFx0XHQpXG5cdFx0XHQpXG5cdFx0KTtcblxuXHRcdHRoaXMuYWRkQWxsKCk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHRhZGRPbmU6IGZ1bmN0aW9uICggY29uZGl0aW9uICkge1xuXG5cdFx0Y29uZGl0aW9uLnJlYWN0aW9uID0gdGhpcy5yZWFjdGlvbi5tb2RlbDtcblxuXHRcdHZhciB2aWV3ID0gbmV3IENvbmRpdGlvbigge1xuXHRcdFx0ZWw6ICQoICc8ZGl2IGNsYXNzPVwiY29uZGl0aW9uXCI+PC9kaXY+JyApLFxuXHRcdFx0bW9kZWw6IGNvbmRpdGlvbixcblx0XHRcdHJlYWN0aW9uOiB0aGlzLnJlYWN0aW9uXG5cdFx0fSApO1xuXG5cdFx0dmFyICR2aWV3ID0gdmlldy5yZW5kZXIoKS4kZWw7XG5cblx0XHR0aGlzLiRlbC5hcHBlbmQoICR2aWV3ICkuc2hvdygpO1xuXG5cdFx0aWYgKCBjb25kaXRpb24uaXNOZXcoKSApIHtcblx0XHRcdCR2aWV3LmZpbmQoICc6aW5wdXQ6dmlzaWJsZTplcSggMSApJyApLmZvY3VzKCk7XG5cdFx0fVxuXG5cdFx0dGhpcy5saXN0ZW5UbyggY29uZGl0aW9uLCAnZGVzdHJveScsIGZ1bmN0aW9uICgpIHtcblx0XHRcdHRoaXMubW9kZWwuZ2V0KCAnY29uZGl0aW9ucycgKS5yZW1vdmUoIGNvbmRpdGlvbi5pZCApO1xuXHRcdH0gKTtcblx0fSxcblxuXHRhZGRBbGw6IGZ1bmN0aW9uICgpIHtcblx0XHR0aGlzLm1vZGVsLmdldCggJ2NvbmRpdGlvbnMnICkuZWFjaCggdGhpcy5hZGRPbmUsIHRoaXMgKTtcblx0fSxcblxuXHQvLyBIaWRlIHRoZSBncm91cCB3aGVuIGl0IGlzIGVtcHR5LlxuXHRtYXliZUhpZGU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdGlmICggMCA9PT0gdGhpcy5tb2RlbC5nZXQoICdjb25kaXRpb25zJyApLmxlbmd0aCApIHtcblx0XHRcdHRoaXMuJGVsLmhpZGUoKTtcblx0XHR9XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbkdyb3VwO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdENvbmRpdGlvbkdyb3VwVmlldyA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cCxcblx0QXJnSGllcmFyY2h5U2VsZWN0b3IgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQXJnSGllcmFyY2h5U2VsZWN0b3IsXG5cdENvbmRpdGlvblNlbGVjdG9yID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvblNlbGVjdG9yLFxuXHRFeHRlbnNpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5FeHRlbnNpb25zLFxuXHRBcmdzID0gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLFxuXHR0ZW1wbGF0ZSA9IHdwLndvcmRwb2ludHMuaG9va3MudGVtcGxhdGUsXG5cdCRjYWNoZSA9IHdwLndvcmRwb2ludHMuJGNhY2hlLFxuXHRDb25kaXRpb25Hcm91cHM7XG5cbkNvbmRpdGlvbkdyb3VwcyA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdjb25kaXRpb24tZ3JvdXBzJyxcblxuXHRjbGFzc05hbWU6ICdjb25kaXRpb25zJyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWNvbmRpdGlvbi1ncm91cHMnICksXG5cblx0ZXZlbnRzOiB7XG5cdFx0J2NsaWNrID4gLmNvbmRpdGlvbnMtdGl0bGUgLmFkZC1uZXcnOiAgICAgICAgICAgJ3Nob3dBcmdTZWxlY3RvcicsXG5cdFx0J2NsaWNrID4gLmFkZC1jb25kaXRpb24tZm9ybSAuY29uZmlybS1hZGQtbmV3JzogJ21heWJlQWRkTmV3Jyxcblx0XHQnY2xpY2sgPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5jYW5jZWwtYWRkLW5ldyc6ICAnY2FuY2VsQWRkTmV3J1xuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuQ29uZGl0aW9ucyA9IEV4dGVuc2lvbnMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuY29sbGVjdGlvbiwgJ2FkZCcsIHRoaXMuYWRkT25lICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5jb2xsZWN0aW9uLCAncmVzZXQnLCB0aGlzLnJlbmRlciApO1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5yZWFjdGlvbiwgJ2NhbmNlbCcsIHRoaXMuY2FuY2VsQWRkTmV3ICk7XG5cblx0XHR0aGlzLmNvbGxlY3Rpb24ub24oICd1cGRhdGUnLCB0aGlzLnJlYWN0aW9uLmxvY2tPcGVuLCB0aGlzLnJlYWN0aW9uICk7XG5cdFx0dGhpcy5jb2xsZWN0aW9uLm9uKCAncmVzZXQnLCB0aGlzLnJlYWN0aW9uLmxvY2tPcGVuLCB0aGlzLnJlYWN0aW9uICk7XG5cdH0sXG5cblx0cmVuZGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRlbC5odG1sKCB0aGlzLnRlbXBsYXRlKCkgKTtcblxuXHRcdHRoaXMuJGMgPSAkY2FjaGUuY2FsbCggdGhpcywgdGhpcy4kICk7XG5cblx0XHR0aGlzLmFkZEFsbCgpO1xuXG5cdFx0dGhpcy50cmlnZ2VyKCAncmVuZGVyJywgdGhpcyApO1xuXG5cdFx0Ly8gU2VlIGh0dHBzOi8vZ2l0aHViLmNvbS9Xb3JkUG9pbnRzL3dvcmRwb2ludHMvaXNzdWVzLzUyMC5cblx0XHRpZiAoIHRoaXMuQXJnU2VsZWN0b3IgKSB7XG5cblx0XHRcdHRoaXMuJCggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAuYXJnLXNlbGVjdG9ycycgKS5yZXBsYWNlV2l0aChcblx0XHRcdFx0dGhpcy5BcmdTZWxlY3Rvci4kZWxcblx0XHRcdCk7XG5cblx0XHRcdHRoaXMuJCggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAuY29uZGl0aW9uLXNlbGVjdG9yJyApLnJlcGxhY2VXaXRoKFxuXHRcdFx0XHR0aGlzLkNvbmRpdGlvblNlbGVjdG9yLiRlbFxuXHRcdFx0KTtcblxuXHRcdFx0dGhpcy5BcmdTZWxlY3Rvci5kZWxlZ2F0ZUV2ZW50cygpO1xuXHRcdFx0dGhpcy5Db25kaXRpb25TZWxlY3Rvci5kZWxlZ2F0ZUV2ZW50cygpO1xuXHRcdFx0dGhpcy5Db25kaXRpb25TZWxlY3Rvci50cmlnZ2VyQ2hhbmdlKCk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHRoaXM7XG5cdH0sXG5cblx0YWRkQWxsOiBmdW5jdGlvbiAoKSB7XG5cdFx0dGhpcy5jb2xsZWN0aW9uLmVhY2goIHRoaXMuYWRkT25lLCB0aGlzICk7XG5cdH0sXG5cblx0YWRkT25lOiBmdW5jdGlvbiAoIENvbmRpdGlvbkdyb3VwICkge1xuXG5cdFx0dmFyIHZpZXcgPSBuZXcgQ29uZGl0aW9uR3JvdXBWaWV3KHtcblx0XHRcdG1vZGVsOiBDb25kaXRpb25Hcm91cCxcblx0XHRcdHJlYWN0aW9uOiB0aGlzLnJlYWN0aW9uXG5cdFx0fSk7XG5cblx0XHR0aGlzLiRjKCAnPiAuY29uZGl0aW9uLWdyb3VwcycgKS5hcHBlbmQoIHZpZXcucmVuZGVyKCkuJGVsICk7XG5cdH0sXG5cblx0c2hvd0FyZ1NlbGVjdG9yOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRjKCAnPiAuY29uZGl0aW9ucy10aXRsZSAuYWRkLW5ldycgKS5hdHRyKCAnZGlzYWJsZWQnLCB0cnVlICk7XG5cblx0XHRpZiAoIHR5cGVvZiB0aGlzLkFyZ1NlbGVjdG9yID09PSAndW5kZWZpbmVkJyApIHtcblxuXHRcdFx0dmFyIGFyZ3MgPSB0aGlzLmNvbGxlY3Rpb24uZ2V0QXJncygpO1xuXHRcdFx0dmFyIENvbmRpdGlvbnMgPSB0aGlzLkNvbmRpdGlvbnM7XG5cdFx0XHR2YXIgYXJnRmlsdGVycyA9IENvbmRpdGlvbnMuYXJnRmlsdGVycztcblx0XHRcdHZhciBpc0VudGl0eUFycmF5ID0gKCB0aGlzLmNvbGxlY3Rpb24uaGllcmFyY2h5LnNsaWNlKCAtMiApLnRvU3RyaW5nKCkgPT09ICdzZXR0aW5ncyxjb25kaXRpb25zJyApO1xuXHRcdFx0dmFyIGhhc0NvbmRpdGlvbnMgPSBmdW5jdGlvbiAoIGFyZyApIHtcblxuXHRcdFx0XHR2YXIgZGF0YVR5cGUgPSBDb25kaXRpb25zLmdldERhdGFUeXBlRnJvbUFyZyggYXJnICk7XG5cdFx0XHRcdHZhciBjb25kaXRpb25zID0gQ29uZGl0aW9ucy5nZXRCeURhdGFUeXBlKCBkYXRhVHlwZSApO1xuXG5cdFx0XHRcdGZvciAoIHZhciBpID0gMDsgaSA8IGFyZ0ZpbHRlcnMubGVuZ3RoOyBpKysgKSB7XG5cdFx0XHRcdFx0aWYgKCAhIGFyZ0ZpbHRlcnNbIGkgXSggYXJnLCBkYXRhVHlwZSwgaXNFbnRpdHlBcnJheSwgY29uZGl0aW9ucyApICkge1xuXHRcdFx0XHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXG5cdFx0XHRcdHJldHVybiAhIF8uaXNFbXB0eSggY29uZGl0aW9ucyApO1xuXHRcdFx0fTtcblxuXHRcdFx0dmFyIGhpZXJhcmNoaWVzID0gQXJncy5nZXRIaWVyYXJjaGllc01hdGNoaW5nKFxuXHRcdFx0XHR7IHRvcDogYXJncy5tb2RlbHMsIGVuZDogaGFzQ29uZGl0aW9ucyB9XG5cdFx0XHQpO1xuXG5cdFx0XHRpZiAoIF8uaXNFbXB0eSggaGllcmFyY2hpZXMgKSApIHtcblxuXHRcdFx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5uby1jb25kaXRpb25zJyApLnNob3coKTtcblxuXHRcdFx0fSBlbHNlIHtcblxuXHRcdFx0XHR0aGlzLkFyZ1NlbGVjdG9yID0gbmV3IEFyZ0hpZXJhcmNoeVNlbGVjdG9yKHtcblx0XHRcdFx0XHRoaWVyYXJjaGllczogaGllcmFyY2hpZXMsXG5cdFx0XHRcdFx0ZWw6IHRoaXMuJCggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAuYXJnLXNlbGVjdG9ycycgKVxuXHRcdFx0XHR9KTtcblxuXHRcdFx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLkFyZ1NlbGVjdG9yLCAnY2hhbmdlJywgdGhpcy5tYXliZVNob3dDb25kaXRpb25TZWxlY3RvciApO1xuXG5cdFx0XHRcdHRoaXMuQXJnU2VsZWN0b3IucmVuZGVyKCk7XG5cblx0XHRcdFx0dGhpcy5BcmdTZWxlY3Rvci4kc2VsZWN0LmNoYW5nZSgpO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHRoaXMuJGMoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0nICkuc2xpZGVEb3duKCk7XG5cdH0sXG5cblx0Z2V0QXJnVHlwZTogZnVuY3Rpb24gKCBhcmcgKSB7XG5cblx0XHR2YXIgYXJnVHlwZTtcblxuXHRcdGlmICggISBhcmcgfHwgISBhcmcuZ2V0ICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGFyZ1R5cGUgPSB0aGlzLkNvbmRpdGlvbnMuZ2V0RGF0YVR5cGVGcm9tQXJnKCBhcmcgKTtcblxuXHRcdC8vIFdlIGNvbXByZXNzIHJlbGF0aW9uc2hpcHMgdG8gYXZvaWQgcmVkdW5kYW5jeS5cblx0XHRpZiAoICdyZWxhdGlvbnNoaXAnID09PSBhcmdUeXBlICkge1xuXHRcdFx0YXJnVHlwZSA9IHRoaXMuZ2V0QXJnVHlwZSggYXJnLmdldENoaWxkKCBhcmcuZ2V0KCAnc2Vjb25kYXJ5JyApICkgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gYXJnVHlwZTtcblx0fSxcblxuXHRtYXliZVNob3dDb25kaXRpb25TZWxlY3RvcjogZnVuY3Rpb24gKCBhcmdTZWxlY3RvcnMsIGFyZyApIHtcblxuXHRcdHZhciBhcmdUeXBlID0gdGhpcy5nZXRBcmdUeXBlKCBhcmcgKTtcblxuXHRcdGlmICggISBhcmdUeXBlICkge1xuXHRcdFx0aWYgKCB0aGlzLiRjb25kaXRpb25TZWxlY3RvciApIHtcblx0XHRcdFx0dGhpcy4kY29uZGl0aW9uU2VsZWN0b3IuaGlkZSgpO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSB0aGlzLkNvbmRpdGlvbnMuZ2V0QnlEYXRhVHlwZSggYXJnVHlwZSApO1xuXG5cdFx0aWYgKCAhIHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IgKSB7XG5cblx0XHRcdHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IgPSBuZXcgQ29uZGl0aW9uU2VsZWN0b3Ioe1xuXHRcdFx0XHRlbDogdGhpcy4kKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5jb25kaXRpb24tc2VsZWN0b3InIClcblx0XHRcdH0pO1xuXG5cdFx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLkNvbmRpdGlvblNlbGVjdG9yLCAnY2hhbmdlJywgdGhpcy5jb25kaXRpb25TZWxlY3Rpb25DaGFuZ2UgKTtcblxuXHRcdFx0dGhpcy4kY29uZGl0aW9uU2VsZWN0b3IgPSB0aGlzLkNvbmRpdGlvblNlbGVjdG9yLiRlbDtcblx0XHR9XG5cblx0XHR0aGlzLkNvbmRpdGlvblNlbGVjdG9yLmNvbGxlY3Rpb24ucmVzZXQoIF8udG9BcnJheSggY29uZGl0aW9ucyApICk7XG5cblx0XHR0aGlzLiRjb25kaXRpb25TZWxlY3Rvci5zaG93KCkuZmluZCggJ3NlbGVjdCcgKS5jaGFuZ2UoKTtcblx0fSxcblxuXHRjYW5jZWxBZGROZXc6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGMoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0nICkuc2xpZGVVcCgpO1xuXHRcdHRoaXMuJGMoICc+IC5jb25kaXRpb25zLXRpdGxlIC5hZGQtbmV3JyApLmF0dHIoICdkaXNhYmxlZCcsIGZhbHNlICk7XG5cdH0sXG5cblx0Y29uZGl0aW9uU2VsZWN0aW9uQ2hhbmdlOiBmdW5jdGlvbiAoIHNlbGVjdG9yLCB2YWx1ZSApIHtcblxuXHRcdHRoaXMuJGMoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmNvbmZpcm0tYWRkLW5ldycgKVxuXHRcdFx0LmF0dHIoICdkaXNhYmxlZCcsICEgdmFsdWUgKTtcblx0fSxcblxuXHRtYXliZUFkZE5ldzogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIHNlbGVjdGVkID0gdGhpcy5Db25kaXRpb25TZWxlY3Rvci5nZXRTZWxlY3RlZCgpO1xuXG5cdFx0aWYgKCAhIHNlbGVjdGVkICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHZhciBoaWVyYXJjaHkgPSB0aGlzLkFyZ1NlbGVjdG9yLmdldEhpZXJhcmNoeSgpLFxuXHRcdFx0aWQgPSB0aGlzLmNvbGxlY3Rpb24uZ2V0SWRGcm9tSGllcmFyY2h5KCBoaWVyYXJjaHkgKSxcblx0XHRcdENvbmRpdGlvbkdyb3VwID0gdGhpcy5jb2xsZWN0aW9uLmdldCggaWQgKTtcblxuXHRcdGlmICggISBDb25kaXRpb25Hcm91cCApIHtcblx0XHRcdENvbmRpdGlvbkdyb3VwID0gdGhpcy5jb2xsZWN0aW9uLmFkZCh7XG5cdFx0XHRcdGlkOiBpZCxcblx0XHRcdFx0aGllcmFyY2h5OiBoaWVyYXJjaHksXG5cdFx0XHRcdGdyb3VwczogdGhpcy5jb2xsZWN0aW9uXG5cdFx0XHR9KTtcblx0XHR9XG5cblx0XHRDb25kaXRpb25Hcm91cC5hZGQoIHsgdHlwZTogc2VsZWN0ZWQgfSApO1xuXG5cdFx0d3AuYTExeS5zcGVhayggdGhpcy5Db25kaXRpb25zLmRhdGEubDEwbi5hZGRlZF9jb25kaXRpb24gKTtcblxuXHRcdHRoaXMuJGMoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0nICkuaGlkZSgpO1xuXHRcdHRoaXMuJGMoICc+IC5jb25kaXRpb25zLXRpdGxlIC5hZGQtbmV3JyApLmF0dHIoICdkaXNhYmxlZCcsIGZhbHNlICk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbkdyb3VwcztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvblNlbGVjdG9yXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdENvbmRpdGlvblR5cGVzID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlcyxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHRDb25kaXRpb25TZWxlY3RvcjtcblxuQ29uZGl0aW9uU2VsZWN0b3IgPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAnY29uZGl0aW9uLXNlbGVjdG9yJyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWNvbmRpdGlvbi1zZWxlY3RvcicgKSxcblxuXHRvcHRpb25UZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWFyZy1vcHRpb24nICksXG5cblx0ZXZlbnRzOiB7XG5cdFx0J2NoYW5nZSBzZWxlY3QnOiAndHJpZ2dlckNoYW5nZSdcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIG9wdGlvbnMgKSB7XG5cblx0XHR0aGlzLmxhYmVsID0gb3B0aW9ucy5sYWJlbDtcblxuXHRcdGlmICggISB0aGlzLmNvbGxlY3Rpb24gKSB7XG5cdFx0XHR0aGlzLmNvbGxlY3Rpb24gPSBuZXcgQ29uZGl0aW9uVHlwZXMoeyBjb21wYXJhdG9yOiAndGl0bGUnIH0pO1xuXHRcdH1cblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuY29sbGVjdGlvbiwgJ3VwZGF0ZScsIHRoaXMucmVuZGVyICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5jb2xsZWN0aW9uLCAncmVzZXQnLCB0aGlzLnJlbmRlciApO1xuXHR9LFxuXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbChcblx0XHRcdHRoaXMudGVtcGxhdGUoXG5cdFx0XHRcdHsgbGFiZWw6IHRoaXMubGFiZWwsIG5hbWU6IHRoaXMuY2lkICsgJ19jb25kaXRpb25fc2VsZWN0b3InIH1cblx0XHRcdClcblx0XHQpO1xuXG5cdFx0dGhpcy4kc2VsZWN0ID0gdGhpcy4kKCAnc2VsZWN0JyApO1xuXG5cdFx0dGhpcy5jb2xsZWN0aW9uLmVhY2goIGZ1bmN0aW9uICggY29uZGl0aW9uICkge1xuXG5cdFx0XHR0aGlzLiRzZWxlY3QuYXBwZW5kKCB0aGlzLm9wdGlvblRlbXBsYXRlKCBjb25kaXRpb24uYXR0cmlidXRlcyApICk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXInLCB0aGlzICk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHR0cmlnZ2VyQ2hhbmdlOiBmdW5jdGlvbiAoIGV2ZW50ICkge1xuXG5cdFx0dGhpcy50cmlnZ2VyKCAnY2hhbmdlJywgdGhpcywgdGhpcy5nZXRTZWxlY3RlZCgpLCBldmVudCApO1xuXHR9LFxuXG5cdGdldFNlbGVjdGVkOiBmdW5jdGlvbiAoKSB7XG5cblx0XHRyZXR1cm4gdGhpcy4kc2VsZWN0LnZhbCgpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25TZWxlY3RvcjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvblxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHR0ZW1wbGF0ZSA9IHdwLndvcmRwb2ludHMuaG9va3MudGVtcGxhdGUsXG5cdEV4dGVuc2lvbnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkV4dGVuc2lvbnMsXG5cdEZpZWxkcyA9IHdwLndvcmRwb2ludHMuaG9va3MuRmllbGRzLFxuXHRDb25kaXRpb247XG5cbkNvbmRpdGlvbiA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdjb25kaXRpb24nLFxuXG5cdGNsYXNzTmFtZTogJ3dvcmRwb2ludHMtaG9vay1jb25kaXRpb24nLFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stcmVhY3Rpb24tY29uZGl0aW9uJyApLFxuXG5cdGV2ZW50czoge1xuXHRcdCdjbGljayAuZGVsZXRlJzogJ2Rlc3Ryb3knXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2NoYW5nZScsIHRoaXMucmVuZGVyICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2Rlc3Ryb3knLCB0aGlzLnJlbW92ZSApO1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2ludmFsaWQnLCB0aGlzLm1vZGVsLnJlYWN0aW9uLnNob3dFcnJvciApO1xuXG5cdFx0dGhpcy5leHRlbnNpb24gPSBFeHRlbnNpb25zLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cdH0sXG5cblx0Ly8gRGlzcGxheSB0aGUgY29uZGl0aW9uIHNldHRpbmdzIGZvcm0uXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbCggdGhpcy50ZW1wbGF0ZSgpICk7XG5cblx0XHR0aGlzLiR0aXRsZSA9IHRoaXMuJCggJy5jb25kaXRpb24tdGl0bGUnICk7XG5cdFx0dGhpcy4kc2V0dGluZ3MgPSB0aGlzLiQoICcuY29uZGl0aW9uLXNldHRpbmdzJyApO1xuXG5cdFx0dGhpcy5yZW5kZXJUaXRsZSgpO1xuXHRcdHRoaXMucmVuZGVyU2V0dGluZ3MoKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcicsIHRoaXMgKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdHJlbmRlclRpdGxlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgY29uZGl0aW9uVHlwZSA9IHRoaXMubW9kZWwuZ2V0VHlwZSgpO1xuXG5cdFx0aWYgKCBjb25kaXRpb25UeXBlICkge1xuXHRcdFx0dGhpcy4kdGl0bGUudGV4dCggY29uZGl0aW9uVHlwZS50aXRsZSApO1xuXHRcdH1cblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjp0aXRsZScsIHRoaXMgKTtcblx0fSxcblxuXHRyZW5kZXJTZXR0aW5nczogZnVuY3Rpb24gKCkge1xuXG5cdFx0Ly8gQnVpbGQgdGhlIGZpZWxkcyBiYXNlZCBvbiB0aGUgY29uZGl0aW9uIHR5cGUuXG5cdFx0dmFyIGNvbmRpdGlvblR5cGUgPSB0aGlzLm1vZGVsLmdldFR5cGUoKSxcblx0XHRcdGZpZWxkcyA9ICcnO1xuXG5cdFx0dmFyIGZpZWxkTmFtZVByZWZpeCA9IF8uY2xvbmUoIHRoaXMubW9kZWwuZ2V0RnVsbEhpZXJhcmNoeSgpICk7XG5cdFx0ZmllbGROYW1lUHJlZml4LnVuc2hpZnQoICdjb25kaXRpb25zJyApO1xuXHRcdGZpZWxkTmFtZVByZWZpeC5wdXNoKFxuXHRcdFx0J19jb25kaXRpb25zJ1xuXHRcdFx0LCB0aGlzLm1vZGVsLmdldCggJ2lkJyApXG5cdFx0XHQsICdzZXR0aW5ncydcblx0XHQpO1xuXG5cdFx0dmFyIGZpZWxkTmFtZSA9IF8uY2xvbmUoIGZpZWxkTmFtZVByZWZpeCApO1xuXG5cdFx0ZmllbGROYW1lLnBvcCgpO1xuXHRcdGZpZWxkTmFtZS5wdXNoKCAndHlwZScgKTtcblxuXHRcdGZpZWxkcyArPSBGaWVsZHMuY3JlYXRlKFxuXHRcdFx0ZmllbGROYW1lXG5cdFx0XHQsIHRoaXMubW9kZWwuZ2V0KCAndHlwZScgKVxuXHRcdFx0LCB7IHR5cGU6ICdoaWRkZW4nIH1cblx0XHQpO1xuXG5cdFx0aWYgKCBjb25kaXRpb25UeXBlICkge1xuXHRcdFx0dmFyIGNvbnRyb2xsZXIgPSB0aGlzLmV4dGVuc2lvbi5nZXRDb250cm9sbGVyKFxuXHRcdFx0XHRjb25kaXRpb25UeXBlLmRhdGFfdHlwZVxuXHRcdFx0XHQsIGNvbmRpdGlvblR5cGUuc2x1Z1xuXHRcdFx0KTtcblxuXHRcdFx0aWYgKCBjb250cm9sbGVyICkge1xuXHRcdFx0XHRmaWVsZHMgKz0gY29udHJvbGxlci5yZW5kZXJTZXR0aW5ncyggdGhpcywgZmllbGROYW1lUHJlZml4ICk7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0dGhpcy4kc2V0dGluZ3MuYXBwZW5kKCBmaWVsZHMgKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjpzZXR0aW5ncycsIHRoaXMgKTtcblx0fSxcblxuXHQvLyBSZW1vdmUgdGhlIGl0ZW0sIGRlc3Ryb3kgdGhlIG1vZGVsLlxuXHRkZXN0cm95OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR3cC5hMTF5LnNwZWFrKCB0aGlzLmV4dGVuc2lvbi5kYXRhLmwxMG4uZGVsZXRlZF9jb25kaXRpb24gKTtcblxuXHRcdHRoaXMubW9kZWwuZGVzdHJveSgpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb247XG4iXX0=
