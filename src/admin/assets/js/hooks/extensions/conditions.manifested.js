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
	},

	/**
	 * Arg filter to disallow identity conditions on entities that aren't enumerable.
	 */
	onlyEnumerableEntities: function ( arg, dataType ) {
		return ! ( dataType === 'entity' && _.isEmpty( arg.get( 'values' ) ) );
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9uLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZW50aXR5LWFycmF5LWNvbnRhaW5zLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZXF1YWxzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2V4dGVuc2lvbnMvY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLWdyb3VwLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tZ3JvdXBzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tdHlwZS5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2V4dGVuc2lvbnMvY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLXR5cGVzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24uanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbnMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLWdyb3VwLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cHMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLXNlbGVjdG9yLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDNUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM5REE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDL1BBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3ZJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMzS0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDZkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDaklBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3hCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3hGQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbk9BO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwidmFyIGhvb2tzID0gd3Aud29yZHBvaW50cy5ob29rcztcblxuLy8gTW9kZWxzXG5ob29rcy5tb2RlbC5Db25kaXRpb24gICAgICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24uanMnICk7XG5ob29rcy5tb2RlbC5Db25kaXRpb25zICAgICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb25zLmpzJyApO1xuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXAgID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLWdyb3VwLmpzJyApO1xuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXBzID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLWdyb3Vwcy5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvblR5cGUgICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi10eXBlLmpzJyApO1xuaG9va3MubW9kZWwuQ29uZGl0aW9uVHlwZXMgID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLXR5cGVzLmpzJyApO1xuXG4vLyBWaWV3c1xuaG9va3Mudmlldy5Db25kaXRpb24gICAgICAgICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLmpzJyApO1xuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cCAgICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLWdyb3VwLmpzJyApO1xuaG9va3Mudmlldy5Db25kaXRpb25TZWxlY3RvciA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLXNlbGVjdG9yLmpzJyApO1xuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cHMgICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLWdyb3Vwcy5qcycgKTtcblxuLy8gQ29udHJvbGxlcnMuXG5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucyA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvZXh0ZW5zaW9uLmpzJyApO1xuaG9va3MuZXh0ZW5zaW9uLkNvbmRpdGlvbnMuQ29uZGl0aW9uID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9jb250cm9sbGVycy9jb25kaXRpb24uanMnICk7XG5cbnZhciBDb25kaXRpb25zID0gbmV3IGhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zKCk7XG5cbi8vIENvbmRpdGlvbnMuXG52YXIgRXF1YWxzID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9jb250cm9sbGVycy9jb25kaXRpb25zL2VxdWFscy5qcycgKTtcblxuQ29uZGl0aW9ucy5yZWdpc3RlckNvbnRyb2xsZXIoICdkZWNpbWFsX251bWJlcicsICdlcXVhbHMnLCBFcXVhbHMgKTtcbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAnZW50aXR5JywgJ2VxdWFscycsIEVxdWFscyApO1xuQ29uZGl0aW9ucy5yZWdpc3RlckNvbnRyb2xsZXIoICdlbnRpdHlfYXJyYXknLCAnZXF1YWxzJywgRXF1YWxzICk7XG5Db25kaXRpb25zLnJlZ2lzdGVyQ29udHJvbGxlciggJ2VudGl0eV9hcnJheScsICdjb250YWlucycsIHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9ucy9lbnRpdHktYXJyYXktY29udGFpbnMuanMnICkgKTtcbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAnaW50ZWdlcicsICdlcXVhbHMnLCBFcXVhbHMgKTtcbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAndGV4dCcsICdlcXVhbHMnLCBFcXVhbHMgKTtcblxuLy8gUmVnaXN0ZXIgdGhlIGV4dGVuc2lvbi5cbmhvb2tzLkV4dGVuc2lvbnMuYWRkKCBDb25kaXRpb25zICk7XG5cbi8vIEVPRlxuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLmNvbmRpdGlvbi5Db25kaXRpb25cbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICovXG5cbnZhciBGaWVsZHMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkZpZWxkcyxcblx0Q29uZGl0aW9uO1xuXG5Db25kaXRpb24gPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0c2x1ZzogJycsXG5cdFx0ZmllbGRzOiBbXVxuXHR9LFxuXG5cdGlkQXR0cmlidXRlOiAnc2x1ZycsXG5cblx0cmVuZGVyU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBmaWVsZE5hbWVQcmVmaXggKSB7XG5cblx0XHR2YXIgZmllbGRzSFRNTCA9ICcnO1xuXG5cdFx0Xy5lYWNoKCB0aGlzLmdldCggJ2ZpZWxkcycgKSwgZnVuY3Rpb24gKCBzZXR0aW5nLCBuYW1lICkge1xuXG5cdFx0XHR2YXIgZmllbGROYW1lID0gXy5jbG9uZSggZmllbGROYW1lUHJlZml4ICk7XG5cblx0XHRcdGZpZWxkTmFtZS5wdXNoKCBuYW1lICk7XG5cblx0XHRcdGZpZWxkc0hUTUwgKz0gRmllbGRzLmNyZWF0ZShcblx0XHRcdFx0ZmllbGROYW1lXG5cdFx0XHRcdCwgY29uZGl0aW9uLm1vZGVsLmF0dHJpYnV0ZXMuc2V0dGluZ3NbIG5hbWUgXVxuXHRcdFx0XHQsIHNldHRpbmdcblx0XHRcdCk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHRyZXR1cm4gZmllbGRzSFRNTDtcblx0fSxcblxuXHR2YWxpZGF0ZVNldHRpbmdzOiBmdW5jdGlvbiAoKSB7fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLmNvbmRpdGlvbi5FbnRpdHlBcnJheUNvbnRhaW5zXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvblxuICovXG5cbnZhciBDb25kaXRpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvbixcblx0Q29uZGl0aW9uR3JvdXBzID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25Hcm91cHMsXG5cdENvbmRpdGlvbkdyb3Vwc1ZpZXcgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBzLFxuXHRFeHRlbnNpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5FeHRlbnNpb25zLFxuXHRBcmdzQ29sbGVjdGlvbiA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQXJncyxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0RW50aXR5QXJyYXlDb250YWlucztcblxuRW50aXR5QXJyYXlDb250YWlucyA9IENvbmRpdGlvbi5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0c2x1ZzogJ2VudGl0eV9hcnJheV9jb250YWlucydcblx0fSxcblxuXHRyZW5kZXJTZXR0aW5nczogZnVuY3Rpb24gKCBjb25kaXRpb24sIGZpZWxkTmFtZVByZWZpeCApIHtcblxuXHRcdC8vIFJlbmRlciB0aGUgbWFpbiBmaWVsZHMuXG5cdFx0dmFyIGZpZWxkcyA9IHRoaXMuY29uc3RydWN0b3IuX19zdXBlcl9fLnJlbmRlclNldHRpbmdzLmFwcGx5KFxuXHRcdFx0dGhpc1xuXHRcdFx0LCBbIGNvbmRpdGlvbiwgZmllbGROYW1lUHJlZml4IF1cblx0XHQpO1xuXG5cdFx0Y29uZGl0aW9uLiRzZXR0aW5ncy5hcHBlbmQoIGZpZWxkcyApO1xuXG5cdFx0Ly8gUmVuZGVyIHZpZXcgZm9yIHN1Yi1jb25kaXRpb25zLlxuXHRcdHZhciBhcmcgPSBBcmdzLmdldEVudGl0eShcblx0XHRcdGNvbmRpdGlvbi5tb2RlbC5nZXRBcmcoKS5nZXQoICdlbnRpdHlfc2x1ZycgKVxuXHRcdCk7XG5cblx0XHRjb25kaXRpb24ubW9kZWwuc3ViR3JvdXBzID0gbmV3IENvbmRpdGlvbkdyb3VwcyggbnVsbCwge1xuXHRcdFx0YXJnczogbmV3IEFyZ3NDb2xsZWN0aW9uKCBbIGFyZyBdICksXG5cdFx0XHRoaWVyYXJjaHk6IGNvbmRpdGlvbi5tb2RlbC5nZXRGdWxsSGllcmFyY2h5KCkuY29uY2F0KFxuXHRcdFx0XHRbICdfY29uZGl0aW9ucycsIGNvbmRpdGlvbi5tb2RlbC5pZCwgJ3NldHRpbmdzJywgJ2NvbmRpdGlvbnMnIF1cblx0XHRcdCksXG5cdFx0XHRyZWFjdGlvbjogY29uZGl0aW9uLnJlYWN0aW9uLm1vZGVsLFxuXHRcdFx0X2NvbmRpdGlvbnM6IGNvbmRpdGlvbi5tb2RlbC5nZXQoICdzZXR0aW5ncycgKS5jb25kaXRpb25zXG5cdFx0fSApO1xuXG5cdFx0dmFyIHZpZXcgPSBuZXcgQ29uZGl0aW9uR3JvdXBzVmlldygge1xuXHRcdFx0Y29sbGVjdGlvbjogY29uZGl0aW9uLm1vZGVsLnN1Ykdyb3Vwcyxcblx0XHRcdHJlYWN0aW9uOiBjb25kaXRpb24ucmVhY3Rpb25cblx0XHR9KTtcblxuXHRcdGNvbmRpdGlvbi4kc2V0dGluZ3MuYXBwZW5kKCB2aWV3LnJlbmRlcigpLiRlbCApO1xuXG5cdFx0cmV0dXJuICcnO1xuXHR9LFxuXG5cdHZhbGlkYXRlU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBzZXR0aW5ncywgZXJyb3JzICkge1xuXG5cdFx0RXh0ZW5zaW9ucy5nZXQoICdjb25kaXRpb25zJyApLnZhbGlkYXRlQ29uZGl0aW9ucyhcblx0XHRcdFsgY29uZGl0aW9uLnN1Ykdyb3VwcyBdXG5cdFx0XHQsIHNldHRpbmdzLmNvbmRpdGlvbnNcblx0XHRcdCwgZXJyb3JzXG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gRW50aXR5QXJyYXlDb250YWlucztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5jb25kaXRpb24uRXF1YWxzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvblxuICovXG5cbnZhciBDb25kaXRpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvbixcblx0RXF1YWxzO1xuXG5FcXVhbHMgPSBDb25kaXRpb24uZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdHNsdWc6ICdlcXVhbHMnXG5cdH0sXG5cblx0cmVuZGVyU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBmaWVsZE5hbWVQcmVmaXggKSB7XG5cblx0XHR2YXIgZmllbGRzID0gdGhpcy5nZXQoICdmaWVsZHMnICksXG5cdFx0XHRhcmcgPSBjb25kaXRpb24ubW9kZWwuZ2V0QXJnKCk7XG5cblx0XHQvLyBXZSByZW5kZXIgdGhlIGB2YWx1ZWAgZmllbGQgZGlmZmVyZW50bHkgYmFzZWQgb24gdGhlIHR5cGUgb2YgYXJndW1lbnQuXG5cdFx0aWYgKCBhcmcgKSB7XG5cblx0XHRcdHZhciB0eXBlID0gYXJnLmdldCggJ190eXBlJyApO1xuXG5cdFx0XHRmaWVsZHMgPSBfLmV4dGVuZCgge30sIGZpZWxkcyApO1xuXG5cdFx0XHRzd2l0Y2ggKCB0eXBlICkge1xuXG5cdFx0XHRcdGNhc2UgJ2F0dHInOlxuXHRcdFx0XHRcdGZpZWxkcy52YWx1ZSA9IF8uZXh0ZW5kKFxuXHRcdFx0XHRcdFx0e31cblx0XHRcdFx0XHRcdCwgZmllbGRzLnZhbHVlXG5cdFx0XHRcdFx0XHQsIHsgdHlwZTogYXJnLmdldCggJ2RhdGFfdHlwZScgKSB9XG5cdFx0XHRcdFx0KTtcblx0XHRcdFx0XHQvKiBmYWxscyB0aHJvdWdoICovXG5cdFx0XHRcdGNhc2UgJ2VudGl0eSc6XG5cdFx0XHRcdFx0dmFyIHZhbHVlcyA9IGFyZy5nZXQoICd2YWx1ZXMnICk7XG5cblx0XHRcdFx0XHRpZiAoIHZhbHVlcyApIHtcblxuXHRcdFx0XHRcdFx0ZmllbGRzLnZhbHVlID0gXy5leHRlbmQoXG5cdFx0XHRcdFx0XHRcdHt9XG5cdFx0XHRcdFx0XHRcdCwgZmllbGRzLnZhbHVlXG5cdFx0XHRcdFx0XHRcdCwgeyB0eXBlOiAnc2VsZWN0Jywgb3B0aW9uczogdmFsdWVzIH1cblx0XHRcdFx0XHRcdCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0fVxuXG5cdFx0XHR0aGlzLnNldCggJ2ZpZWxkcycsIGZpZWxkcyApO1xuXHRcdH1cblxuXHRcdHJldHVybiB0aGlzLmNvbnN0cnVjdG9yLl9fc3VwZXJfXy5yZW5kZXJTZXR0aW5ncy5hcHBseShcblx0XHRcdHRoaXNcblx0XHRcdCwgWyBjb25kaXRpb24sIGZpZWxkTmFtZVByZWZpeCBdXG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gRXF1YWxzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uXG4gKlxuICpcbiAqL1xudmFyIEV4dGVuc2lvbiA9IHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb24sXG5cdENvbmRpdGlvbkdyb3VwcyA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXBzLFxuXHRDb25kaXRpb25zR3JvdXBzVmlldyA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cHMsXG5cdGdldERlZXAgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnV0aWwuZ2V0RGVlcCxcblx0ZGF0YSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5kYXRhLFxuXHRDb25kaXRpb25zO1xuXG5Db25kaXRpb25zID0gRXh0ZW5zaW9uLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IHtcblx0XHRzbHVnOiAnY29uZGl0aW9ucydcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLmFyZ0ZpbHRlcnMgPSBbIHRoaXMub25seUVudW1lcmFibGVFbnRpdGllcyBdO1xuXHRcdHRoaXMuZGF0YVR5cGUgPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoIHsgaWRBdHRyaWJ1dGU6ICdzbHVnJyB9ICk7XG5cdFx0dGhpcy5jb250cm9sbGVycyA9IG5ldyBCYWNrYm9uZS5Db2xsZWN0aW9uKFxuXHRcdFx0W11cblx0XHRcdCwgeyBjb21wYXJhdG9yOiAnc2x1ZycsIG1vZGVsOiB0aGlzLmRhdGFUeXBlIH1cblx0XHQpO1xuXHR9LFxuXG5cdGluaXRSZWFjdGlvbjogZnVuY3Rpb24gKCByZWFjdGlvbiApIHtcblxuXHRcdHJlYWN0aW9uLmNvbmRpdGlvbnMgPSB7fTtcblx0XHRyZWFjdGlvbi5tb2RlbC5jb25kaXRpb25zID0ge307XG5cblx0XHR2YXIgY29uZGl0aW9ucyA9IHJlYWN0aW9uLm1vZGVsLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHRpZiAoICEgY29uZGl0aW9ucyApIHtcblx0XHRcdGNvbmRpdGlvbnMgPSB7fTtcblx0XHR9XG5cblx0XHR2YXIgYWN0aW9uVHlwZXMgPSBfLmtleXMoXG5cdFx0XHRkYXRhLmV2ZW50X2FjdGlvbl90eXBlc1sgcmVhY3Rpb24ubW9kZWwuZ2V0KCAnZXZlbnQnICkgXVxuXHRcdCk7XG5cblx0XHRpZiAoICEgYWN0aW9uVHlwZXMgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0YWN0aW9uVHlwZXMgPSBfLmludGVyc2VjdGlvbihcblx0XHRcdHJlYWN0aW9uLlJlYWN0b3IuZ2V0KCAnYWN0aW9uX3R5cGVzJyApXG5cdFx0XHQsIGFjdGlvblR5cGVzXG5cdFx0KTtcblxuXHRcdF8uZWFjaCggYWN0aW9uVHlwZXMsIGZ1bmN0aW9uICggYWN0aW9uVHlwZSApIHtcblxuXHRcdFx0dmFyIGNvbmRpdGlvbkdyb3VwcyA9IGNvbmRpdGlvbnNbIGFjdGlvblR5cGUgXTtcblxuXHRcdFx0aWYgKCAhIGNvbmRpdGlvbkdyb3VwcyApIHtcblx0XHRcdFx0Y29uZGl0aW9uR3JvdXBzID0gW107XG5cdFx0XHR9XG5cblx0XHRcdHJlYWN0aW9uLm1vZGVsLmNvbmRpdGlvbnNbIGFjdGlvblR5cGUgXSA9IG5ldyBDb25kaXRpb25Hcm91cHMoIG51bGwsIHtcblx0XHRcdFx0aGllcmFyY2h5OiBbIGFjdGlvblR5cGUgXSxcblx0XHRcdFx0cmVhY3Rpb246IHJlYWN0aW9uLm1vZGVsLFxuXHRcdFx0XHRfY29uZGl0aW9uczogY29uZGl0aW9uR3JvdXBzXG5cdFx0XHR9ICk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHR2YXIgYXBwZW5kZWQgPSBmYWxzZTtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHJlYWN0aW9uLCAncmVuZGVyOmZpZWxkcycsIGZ1bmN0aW9uICggJGVsLCBjdXJyZW50QWN0aW9uVHlwZSApIHtcblxuXHRcdFx0dmFyIGNvbmRpdGlvbnNWaWV3ID0gcmVhY3Rpb24uY29uZGl0aW9uc1sgY3VycmVudEFjdGlvblR5cGUgXTtcblxuXHRcdFx0aWYgKCAhIGNvbmRpdGlvbnNWaWV3ICkge1xuXHRcdFx0XHRjb25kaXRpb25zVmlldyA9IHJlYWN0aW9uLmNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF0gPSBuZXcgQ29uZGl0aW9uc0dyb3Vwc1ZpZXcoIHtcblx0XHRcdFx0XHRjb2xsZWN0aW9uOiByZWFjdGlvbi5tb2RlbC5jb25kaXRpb25zWyBjdXJyZW50QWN0aW9uVHlwZSBdLFxuXHRcdFx0XHRcdHJlYWN0aW9uOiByZWFjdGlvblxuXHRcdFx0XHR9KTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gSWYgd2UndmUgYWxyZWFkeSBhcHBlbmRlZCB0aGUgY29udGFpbmVyIHZpZXcgdG8gdGhlIHJlYWN0aW9uIHZpZXcsXG5cdFx0XHQvLyB0aGVuIHdlIGRvbid0IG5lZWQgdG8gZG8gdGhhdCBhZ2Fpbi5cblx0XHRcdGlmICggYXBwZW5kZWQgKSB7XG5cblx0XHRcdFx0dmFyIGNvbmRpdGlvbnNDb2xsZWN0aW9uID0gcmVhY3Rpb24ubW9kZWwuY29uZGl0aW9uc1sgY3VycmVudEFjdGlvblR5cGUgXTtcblx0XHRcdFx0dmFyIGNvbmRpdGlvbnMgPSByZWFjdGlvbi5tb2RlbC5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0XHRcdGlmICggISBjb25kaXRpb25zICkge1xuXHRcdFx0XHRcdGNvbmRpdGlvbnMgPSB7fTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIEhvd2V2ZXIsIHdlIGRvIG5lZWQgdG8gdXBkYXRlIHRoZSBjb25kaXRpb24gY29sbGVjdGlvbiwgaW4gY2FzZVxuXHRcdFx0XHQvLyBzb21lIG9mIHRoZSBjb25kaXRpb24gbW9kZWxzIGhhdmUgYmVlbiByZW1vdmVkIG9yIG5ldyBvbmVzIGFkZGVkLlxuXHRcdFx0XHRjb25kaXRpb25zQ29sbGVjdGlvbi5zZXQoXG5cdFx0XHRcdFx0Y29uZGl0aW9uc0NvbGxlY3Rpb24ubWFwQ29uZGl0aW9uR3JvdXBzKFxuXHRcdFx0XHRcdFx0Y29uZGl0aW9uc1sgY3VycmVudEFjdGlvblR5cGUgXSB8fCBbXVxuXHRcdFx0XHRcdClcblx0XHRcdFx0XHQsIHsgcGFyc2U6IHRydWUgfVxuXHRcdFx0XHQpO1xuXG5cdFx0XHRcdC8vIEFuZCB0aGVuIHJlLXJlbmRlciBldmVyeXRoaW5nLlxuXHRcdFx0XHRjb25kaXRpb25zVmlldy5yZW5kZXIoKTtcblxuXHRcdFx0fSBlbHNlIHtcblxuXHRcdFx0XHQkZWwuYXBwZW5kKCBjb25kaXRpb25zVmlldy5yZW5kZXIoKS4kZWwgKTtcblxuXHRcdFx0XHRhcHBlbmRlZCA9IHRydWU7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH0sXG5cblx0Z2V0RGF0YVR5cGVGcm9tQXJnOiBmdW5jdGlvbiAoIGFyZyApIHtcblxuXHRcdHZhciBhcmdUeXBlID0gYXJnLmdldCggJ190eXBlJyApO1xuXG5cdFx0c3dpdGNoICggYXJnVHlwZSApIHtcblxuXHRcdFx0Y2FzZSAnYXR0cic6XG5cdFx0XHRcdHJldHVybiBhcmcuZ2V0KCAnZGF0YV90eXBlJyApO1xuXG5cdFx0XHRjYXNlICdhcnJheSc6XG5cdFx0XHRcdHJldHVybiAnZW50aXR5X2FycmF5JztcblxuXHRcdFx0ZGVmYXVsdDpcblx0XHRcdFx0cmV0dXJuIGFyZ1R5cGU7XG5cdFx0fVxuXHR9LFxuXG5cdHZhbGlkYXRlUmVhY3Rpb246IGZ1bmN0aW9uICggbW9kZWwsIGF0dHJpYnV0ZXMsIGVycm9ycywgb3B0aW9ucyApIHtcblxuXHRcdC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9Xb3JkUG9pbnRzL3dvcmRwb2ludHMvaXNzdWVzLzUxOS5cblx0XHRpZiAoICEgb3B0aW9ucy5yYXdBdHRzLmNvbmRpdGlvbnMgKSB7XG5cdFx0XHRkZWxldGUgYXR0cmlidXRlcy5jb25kaXRpb25zO1xuXHRcdFx0ZGVsZXRlIG1vZGVsLmF0dHJpYnV0ZXMuY29uZGl0aW9ucztcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aGlzLnZhbGlkYXRlQ29uZGl0aW9ucyggbW9kZWwuY29uZGl0aW9ucywgYXR0cmlidXRlcy5jb25kaXRpb25zLCBlcnJvcnMgKTtcblx0fSxcblxuXHR2YWxpZGF0ZUNvbmRpdGlvbnM6IGZ1bmN0aW9uICggY29uZGl0aW9ucywgc2V0dGluZ3MsIGVycm9ycyApIHtcblxuXHRcdF8uZWFjaCggY29uZGl0aW9ucywgZnVuY3Rpb24gKCBncm91cHMgKSB7XG5cdFx0XHRncm91cHMuZWFjaCggZnVuY3Rpb24gKCBncm91cCApIHtcblx0XHRcdFx0Z3JvdXAuZ2V0KCAnY29uZGl0aW9ucycgKS5lYWNoKCBmdW5jdGlvbiAoIGNvbmRpdGlvbiApIHtcblxuXHRcdFx0XHRcdHZhciBuZXdFcnJvcnMgPSBbXSxcblx0XHRcdFx0XHRcdGhpZXJhcmNoeSA9IGNvbmRpdGlvbi5nZXRIaWVyYXJjaHkoKS5jb25jYXQoXG5cdFx0XHRcdFx0XHRcdFsgJ19jb25kaXRpb25zJywgY29uZGl0aW9uLmlkIF1cblx0XHRcdFx0XHRcdCk7XG5cblx0XHRcdFx0XHRpZiAoIGdyb3Vwcy5oaWVyYXJjaHkubGVuZ3RoID09PSAxICkge1xuXHRcdFx0XHRcdFx0aGllcmFyY2h5LnVuc2hpZnQoIGdyb3Vwcy5oaWVyYXJjaHlbMF0gKTtcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRjb25kaXRpb24udmFsaWRhdGUoXG5cdFx0XHRcdFx0XHRnZXREZWVwKCBzZXR0aW5ncywgaGllcmFyY2h5IClcblx0XHRcdFx0XHRcdCwge31cblx0XHRcdFx0XHRcdCwgbmV3RXJyb3JzXG5cdFx0XHRcdFx0KTtcblxuXHRcdFx0XHRcdGlmICggISBfLmlzRW1wdHkoIG5ld0Vycm9ycyApICkge1xuXG5cdFx0XHRcdFx0XHRoaWVyYXJjaHkudW5zaGlmdCggJ2NvbmRpdGlvbnMnICk7XG5cdFx0XHRcdFx0XHRoaWVyYXJjaHkucHVzaCggJ3NldHRpbmdzJyApO1xuXG5cdFx0XHRcdFx0XHRmb3IgKCB2YXIgaSA9IDA7IGkgPCBuZXdFcnJvcnMubGVuZ3RoOyBpKysgKSB7XG5cblx0XHRcdFx0XHRcdFx0bmV3RXJyb3JzWyBpIF0uZmllbGQgPSBoaWVyYXJjaHkuY29uY2F0KFxuXHRcdFx0XHRcdFx0XHRcdF8uaXNBcnJheSggbmV3RXJyb3JzWyBpIF0uZmllbGQgKVxuXHRcdFx0XHRcdFx0XHRcdFx0PyBuZXdFcnJvcnNbIGkgXS5maWVsZFxuXHRcdFx0XHRcdFx0XHRcdFx0OiBbIG5ld0Vycm9yc1sgaSBdLmZpZWxkIF1cblx0XHRcdFx0XHRcdFx0KTtcblxuXHRcdFx0XHRcdFx0XHRlcnJvcnMucHVzaCggbmV3RXJyb3JzWyBpIF0gKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdH0pO1xuXHRcdFx0fSk7XG5cdFx0fSk7XG5cdH0sXG5cblx0Z2V0VHlwZTogZnVuY3Rpb24gKCBkYXRhVHlwZSwgc2x1ZyApIHtcblxuXHRcdGlmICggdHlwZW9mIHRoaXMuZGF0YS5jb25kaXRpb25zWyBkYXRhVHlwZSBdID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHRpZiAoIHR5cGVvZiB0aGlzLmRhdGEuY29uZGl0aW9uc1sgZGF0YVR5cGUgXVsgc2x1ZyBdID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHRyZXR1cm4gdGhpcy5kYXRhLmNvbmRpdGlvbnNbIGRhdGFUeXBlIF1bIHNsdWcgXTtcblx0fSxcblxuXHQvLyBHZXQgYWxsIGNvbmRpdGlvbnMgZm9yIGEgY2VydGFpbiBkYXRhIHR5cGUuXG5cdGdldEJ5RGF0YVR5cGU6IGZ1bmN0aW9uICggZGF0YVR5cGUgKSB7XG5cblx0XHRyZXR1cm4gdGhpcy5kYXRhLmNvbmRpdGlvbnNbIGRhdGFUeXBlIF07XG5cdH0sXG5cblx0Z2V0Q29udHJvbGxlcjogZnVuY3Rpb24gKCBkYXRhVHlwZVNsdWcsIHNsdWcgKSB7XG5cblx0XHR2YXIgZGF0YVR5cGUgPSB0aGlzLmNvbnRyb2xsZXJzLmdldCggZGF0YVR5cGVTbHVnICksXG5cdFx0XHRjb250cm9sbGVyO1xuXG5cdFx0aWYgKCBkYXRhVHlwZSApIHtcblx0XHRcdGNvbnRyb2xsZXIgPSBkYXRhVHlwZS5nZXQoICdjb250cm9sbGVycycgKVsgc2x1ZyBdO1xuXHRcdH1cblxuXHRcdGlmICggISBjb250cm9sbGVyICkge1xuXHRcdFx0Y29udHJvbGxlciA9IENvbmRpdGlvbnMuQ29uZGl0aW9uO1xuXHRcdH1cblxuXHRcdHZhciB0eXBlID0gdGhpcy5nZXRUeXBlKCBkYXRhVHlwZVNsdWcsIHNsdWcgKTtcblxuXHRcdGlmICggISB0eXBlICkge1xuXHRcdFx0dHlwZSA9IHsgc2x1Zzogc2x1ZyB9O1xuXHRcdH1cblxuXHRcdHJldHVybiBuZXcgY29udHJvbGxlciggdHlwZSApO1xuXHR9LFxuXG5cdHJlZ2lzdGVyQ29udHJvbGxlcjogZnVuY3Rpb24gKCBkYXRhVHlwZVNsdWcsIHNsdWcsIGNvbnRyb2xsZXIgKSB7XG5cblx0XHR2YXIgZGF0YVR5cGUgPSB0aGlzLmNvbnRyb2xsZXJzLmdldCggZGF0YVR5cGVTbHVnICk7XG5cblx0XHRpZiAoICEgZGF0YVR5cGUgKSB7XG5cdFx0XHRkYXRhVHlwZSA9IG5ldyB0aGlzLmRhdGFUeXBlKHtcblx0XHRcdFx0c2x1ZzogZGF0YVR5cGVTbHVnLFxuXHRcdFx0XHRjb250cm9sbGVyczoge31cblx0XHRcdH0pO1xuXG5cdFx0XHR0aGlzLmNvbnRyb2xsZXJzLmFkZCggZGF0YVR5cGUgKTtcblx0XHR9XG5cblx0XHRkYXRhVHlwZS5nZXQoICdjb250cm9sbGVycycgKVsgc2x1ZyBdID0gY29udHJvbGxlcjtcblx0fSxcblxuXHQvKipcblx0ICogQXJnIGZpbHRlciB0byBkaXNhbGxvdyBpZGVudGl0eSBjb25kaXRpb25zIG9uIGVudGl0aWVzIHRoYXQgYXJlbid0IGVudW1lcmFibGUuXG5cdCAqL1xuXHRvbmx5RW51bWVyYWJsZUVudGl0aWVzOiBmdW5jdGlvbiAoIGFyZywgZGF0YVR5cGUgKSB7XG5cdFx0cmV0dXJuICEgKCBkYXRhVHlwZSA9PT0gJ2VudGl0eScgJiYgXy5pc0VtcHR5KCBhcmcuZ2V0KCAndmFsdWVzJyApICkgKTtcblx0fVxuXG59ICk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9ucztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25Hcm91cFxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLkNvbGxlY3Rpb25cbiAqL1xudmFyIENvbmRpdGlvbnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbnMsXG5cdENvbmRpdGlvbkdyb3VwO1xuXG4vLyBUaGlzIGlzIGEgbW9kZWwgYWx0aG91Z2ggd2Ugb3JpZ2luYWxseSB0aG91Z2h0IGl0IG91Z2h0IHRvIGJlIGEgY29sbGVjdGlvbixcbi8vIGJlY2F1c2UgQmFja2JvbmUgZG9lc24ndCBzdXBwb3J0IHN1Yi1jb2xsZWN0aW9ucy4gVGhpcyBpcyB0aGUgY2xvc2VzdCB0aGluZ1xuLy8gdG8gYSBzdWItY29sbGVjdGlvbi4gU2VlIGh0dHBzOi8vc3RhY2tvdmVyZmxvdy5jb20vcS8xMDM4ODE5OS8xOTI0MTI4LlxuQ29uZGl0aW9uR3JvdXAgPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuIHtcblx0XHRcdGlkOiAnJyxcblx0XHRcdGhpZXJhcmNoeTogW10sXG5cdFx0XHRjb25kaXRpb25zOiBuZXcgQ29uZGl0aW9ucygpLFxuXHRcdFx0Z3JvdXBzOiBudWxsLFxuXHRcdFx0cmVhY3Rpb246IG51bGxcblx0XHR9O1xuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICggYXR0cmlidXRlcyApIHtcblxuXHRcdC8vIFNldCB1cCBldmVudCBwcm94eWluZy5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLmF0dHJpYnV0ZXMuY29uZGl0aW9ucywgJ2FsbCcsIHRoaXMudHJpZ2dlciApO1xuXG5cdFx0Ly8gQWRkIHRoZSBjb25kaXRpb25zIHRvIHRoZSBjb2xsZWN0aW9uLlxuXHRcdGlmICggYXR0cmlidXRlcy5fY29uZGl0aW9ucyApIHtcblx0XHRcdHRoaXMucmVzZXQoIGF0dHJpYnV0ZXMuX2NvbmRpdGlvbnMgKTtcblx0XHR9XG5cdH0sXG5cblx0Ly8gTWFrZSBzdXJlIHRoYXQgdGhlIG1vZGVsIGlkcyBhcmUgcHJvcGVybHkgc2V0LiBDb25kaXRpb25zIGFyZSBpZGVudGlmaWVkXG5cdC8vIGJ5IHRoZSBpbmRleCBvZiB0aGUgYXJyYXkgaW4gd2hpY2ggdGhleSBhcmUgc3RvcmVkLiBXZSBjb3B5IHRoZSBrZXlzIHRvXG5cdC8vIHRoZSBpZCBhdHRyaWJ1dGVzIG9mIHRoZSBtb2RlbHMuXG5cdHJlc2V0OiBmdW5jdGlvbiAoIG1vZGVscywgb3B0aW9ucyApIHtcblxuXHRcdG9wdGlvbnMgPSBvcHRpb25zIHx8IHt9O1xuXHRcdG9wdGlvbnMuZ3JvdXAgPSB0aGlzO1xuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSB0aGlzLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHR0aGlzLnNldElkcyggbW9kZWxzLCAwICk7XG5cblx0XHRyZXR1cm4gY29uZGl0aW9ucy5yZXNldC5jYWxsKCBjb25kaXRpb25zLCBtb2RlbHMsIG9wdGlvbnMgKTtcblx0fSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgVXBkYXRlIHRoZSBjb25kaXRpb25zIGNvbGxlY3Rpb24uXG5cdCAqXG5cdCAqIFRoaXMgaXMgYSB3cmFwcGVyIGZvciB0aGUgYHNldCgpYCBtZXRob2Qgb2YgdGhlIGNvbGxlY3Rpb24gc3RvcmVkIGluIHRoZVxuXHQgKiBgY29uZGl0aW9uc2AgYXR0cmlidXRlIG9mIHRoaXMgTW9kZWwuIEl0IGVuc3VyZXMgdGhhdCB0aGUgcGFzc2VkIG1vZGVsXG5cdCAqIG9iamVjdHMgaGF2ZSBiZWVuIGdpdmVuIHByb3BlciBJRHMsIGFuZCBzZXRzIG9wdGlvbnMuZ3JvdXAgdG8gdGhpcyBvYmplY3QuXG5cdCAqXG5cdCAqIE5vdGUgdGhhdCB0aGUgYF9jb25kaXRpb25zYCBhdHRyaWJ1dGUgaXRzZWxmIGlzIG5vdCBtb2RpZmllZCwgb25seSB0aGVcblx0ICogY29sbGVjdGlvbiB0aGF0IGlzIHN0b3JlZCBpbiB0aGUgYGNvbmRpdGlvbnNgIGF0dHJpYnV0ZS5cblx0ICpcblx0ICogQHNpbmNlIDIuMS4zXG5cdCAqXG5cdCAqIEBwYXJhbSB7T2JqZWN0W119IG1vZGVscyAgICAgICAgICAgICAgICAgICAgLSBUaGUgY29uZGl0aW9ucy5cblx0ICogQHBhcmFtIHtPYmplY3R9ICAgW29wdGlvbnM9eyBncm91cDogdGhpcyB9XSAtIE9wdGlvbnMgdG8gcGFzcyB0b1xuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYENvbmRpdGlvbnMuc2V0KClgLiBUaGUgYGdyb3VwYFxuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgd2lsbCBhbHdheXMgYmUgc2V0IHRvIGB0aGlzYC5cblx0ICpcblx0ICogQHJldHVybnMge09iamVjdFtdfSBUaGUgYWRkZWQgbW9kZWxzLlxuXHQgKi9cblx0c2V0Q29uZGl0aW9uczogZnVuY3Rpb24gKCBtb2RlbHMsIG9wdGlvbnMgKSB7XG5cblx0XHRvcHRpb25zID0gb3B0aW9ucyB8fCB7fTtcblx0XHRvcHRpb25zLmdyb3VwID0gdGhpcztcblxuXHRcdHZhciBjb25kaXRpb25zID0gdGhpcy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0dGhpcy5zZXRJZHMoIG1vZGVscywgMCApO1xuXG5cdFx0cmV0dXJuIGNvbmRpdGlvbnMuc2V0LmNhbGwoIGNvbmRpdGlvbnMsIG1vZGVscywgb3B0aW9ucyApO1xuXHR9LFxuXG5cdGFkZDogZnVuY3Rpb24gKCBtb2RlbHMsIG9wdGlvbnMgKSB7XG5cblx0XHRvcHRpb25zID0gb3B0aW9ucyB8fCB7fTtcblx0XHRvcHRpb25zLmdyb3VwID0gdGhpcztcblxuXHRcdHZhciBjb25kaXRpb25zID0gdGhpcy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0dGhpcy5zZXRJZHMoIG1vZGVscywgdGhpcy5nZXROZXh0SWQoKSApO1xuXG5cdFx0cmV0dXJuIGNvbmRpdGlvbnMuYWRkLmNhbGwoIGNvbmRpdGlvbnMsIG1vZGVscywgb3B0aW9ucyApO1xuXHR9LFxuXG5cdGdldE5leHRJZDogZnVuY3Rpb24oKSB7XG5cblx0XHR2YXIgY29uZGl0aW9ucyA9IHRoaXMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdGlmICggIWNvbmRpdGlvbnMubGVuZ3RoICkge1xuXHRcdFx0cmV0dXJuIDA7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHBhcnNlSW50KCBjb25kaXRpb25zLnNvcnQoKS5sYXN0KCkuZ2V0KCAnaWQnICksIDEwICkgKyAxO1xuXHR9LFxuXG5cdHNldElkczogZnVuY3Rpb24gKCBtb2RlbHMsIHN0YXJ0SWQgKSB7XG5cblx0XHRpZiAoICEgbW9kZWxzICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdF8uZWFjaCggXy5pc0FycmF5KCBtb2RlbHMgKSA/IG1vZGVscyA6IFsgbW9kZWxzIF0sIGZ1bmN0aW9uICggbW9kZWwsIGlkICkge1xuXG5cdFx0XHRpZiAoIHN0YXJ0SWQgIT09IDAgKSB7XG5cdFx0XHRcdG1vZGVsLmlkID0gc3RhcnRJZCsrO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0bW9kZWwuaWQgPSBpZDtcblx0XHRcdH1cblxuXHRcdFx0Ly8gVGhpcyB3aWxsIGJlIHNldCB3aGVuIGFuIG9iamVjdCBpcyBjb252ZXJ0ZWQgdG8gYSBtb2RlbCwgYnV0IGlmIGl0IGlzXG5cdFx0XHQvLyBhIG1vZGVsIGFscmVhZHksIHdlIG5lZWQgdG8gc2V0IGl0IGhlcmUuXG5cdFx0XHRpZiAoIG1vZGVsIGluc3RhbmNlb2YgQmFja2JvbmUuTW9kZWwgKSB7XG5cdFx0XHRcdG1vZGVsLmdyb3VwID0gdGhpcztcblx0XHRcdH1cblxuXHRcdH0sIHRoaXMgKTtcblx0fSxcblxuXHRzeW5jOiBmdW5jdGlvbiAoIG1ldGhvZCwgY29sbGVjdGlvbiwgb3B0aW9ucyApIHtcblx0XHRvcHRpb25zLmVycm9yKFxuXHRcdFx0eyBtZXNzYWdlOiAnRmV0Y2hpbmcgYW5kIHNhdmluZyBncm91cHMgb2YgaG9vayBjb25kaXRpb25zIGlzIG5vdCBzdXBwb3J0ZWQuJyB9XG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uR3JvdXA7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXBzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuQ29sbGVjdGlvblxuICovXG52YXIgQ29uZGl0aW9uR3JvdXAgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3VwLFxuXHRBcmdzID0gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLFxuXHRDb25kaXRpb25Hcm91cHM7XG5cbi8qKlxuICogT2JqZWN0IGZvcm1hdCBmb3IgbW9kZWxzIGV4cGVjdGVkIGJ5IHRoaXMgY29sbGVjdGlvbi5cbiAqXG4gKiBAdHlwZWRlZiB7T2JqZWN0fSBSYXdDb25kaXRpb25Hcm91cFxuICpcbiAqIEBwcm9wZXJ0eSB7c3RyaW5nfSAgICAgICAgICBpZCAgICAgICAgICAtIFRoZSBJRCBvZiB0aGUgZ3JvdXAuXG4gKiBAcHJvcGVydHkge0FycmF5fSAgICAgICAgICAgaGllcmFyY2h5ICAgLSBUaGUgaGllcmFyY2h5IGZvciB0aGUgZ3JvdXAuXG4gKiBAcHJvcGVydHkge0NvbmRpdGlvbkdyb3Vwc30gZ3JvdXBzICAgICAgLSBUaGUgY29sbGVjdGlvbiBmb3IgdGhlIGdyb3VwLlxuICogQHByb3BlcnR5IHtBcnJheX0gICAgICAgICAgIF9jb25kaXRpb25zIC0gVGhlIGNvbmRpdGlvbnMgaW4gdGhlIGdyb3VwLlxuICovXG5cbkNvbmRpdGlvbkdyb3VwcyA9IEJhY2tib25lLkNvbGxlY3Rpb24uZXh0ZW5kKHtcblxuXHRtb2RlbDogQ29uZGl0aW9uR3JvdXAsXG5cblx0aGllcmFyY2h5OiBbXSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIG1vZGVscywgb3B0aW9ucyApIHtcblxuXHRcdGlmICggb3B0aW9ucy5hcmdzICkge1xuXHRcdFx0dGhpcy5hcmdzID0gb3B0aW9ucy5hcmdzO1xuXHRcdH1cblxuXHRcdGlmICggb3B0aW9ucy5oaWVyYXJjaHkgKSB7XG5cdFx0XHR0aGlzLmhpZXJhcmNoeSA9IG9wdGlvbnMuaGllcmFyY2h5O1xuXHRcdH1cblxuXHRcdGlmICggb3B0aW9ucy5yZWFjdGlvbiApIHtcblx0XHRcdHRoaXMucmVhY3Rpb24gPSBvcHRpb25zLnJlYWN0aW9uO1xuXHRcdH1cblxuXHRcdGlmICggb3B0aW9ucy5fY29uZGl0aW9ucyApIHtcblx0XHRcdHRoaXMubWFwQ29uZGl0aW9ucyggb3B0aW9ucy5fY29uZGl0aW9ucyApO1xuXHRcdH1cblx0fSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgQ29udmVydHMgYSBjb25kaXRpb25zIGhpZXJhcmNoeSBpbnRvIGFuIGFycmF5IG9mIGNvbmRpdGlvbiBncm91cHMuXG5cdCAqXG5cdCAqIFRoZSBjb25kaXRpb25zLCBhcyBzYXZlZCBpbiB0aGUgZGF0YWJhc2UsIGFyZSBpbiBhIG5lc3RlZCBoaWVyYXJjaHkgYmFzZWQgb25cblx0ICogd2hpY2ggKHN1YilhcmdzIHRoZXkgYXJlIGZvci4gVGhlcmVmb3JlIGl0IGlzIG5lY2Vzc2FyeSB0byBwYXJzZSB0aGUgaGllcmFyY2h5XG5cdCAqIGludG8gYSBzaW1wbGUgYXJyYXkgY29udGFpbmluZyB0aGUgY29uZGl0aW9uIGluZm9ybWF0aW9uIGFuZCB0aGUgYXJnIGhpZXJhcmNoeVxuXHQgKiBmb3IgaXQuXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjEuM1xuXHQgKlxuXHQgKiBAcGFyYW0ge09iamVjdH0gICAgICAgICAgICAgIGNvbmRpdGlvbnMgICAgIC0gVGhlIGNvbmRpdGlvbnMgaGllcmFyY2h5LlxuXHQgKiBAcGFyYW0ge1Jhd0NvbmRpdGlvbkdyb3VwW119IFtncm91cHM9W11dICAgIC0gVGhlIGFycmF5IG9mIGNvbmRpdGlvbiBncm91cHMuXG5cdCAqIEBwYXJhbSB7QXJyYXl9ICAgICAgICAgICAgICAgW2hpZXJhcmNoeT1bXV0gLSBUaGUgY3VycmVudCBsb2NhdGlvbiB3aXRoaW4gdGhlXG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjb25kaXRpb25zIGhpZXJhcmNoeS5cblx0ICpcblx0ICogQHJldHVybnMge1Jhd0NvbmRpdGlvbkdyb3VwW119IFRoZSBwYXJzZWQgZ3JvdXBzIGluIHRoZSBmb3JtYXQgZm9yIG1vZGVsc1xuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZXhwZWN0ZWQgYnkgdGhpcyBjb2xsZWN0aW9uLlxuXHQgKi9cblx0bWFwQ29uZGl0aW9uR3JvdXBzOiBmdW5jdGlvbiAoIGNvbmRpdGlvbnMsIGdyb3VwcywgaGllcmFyY2h5ICkge1xuXG5cdFx0aGllcmFyY2h5ID0gaGllcmFyY2h5IHx8IFtdO1xuXHRcdGdyb3VwcyA9IGdyb3VwcyB8fCBbXTtcblxuXHRcdF8uZWFjaCggY29uZGl0aW9ucywgZnVuY3Rpb24gKCBhcmcsIHNsdWcgKSB7XG5cblx0XHRcdGlmICggc2x1ZyA9PT0gJ19jb25kaXRpb25zJyApIHtcblxuXHRcdFx0XHRncm91cHMucHVzaCgge1xuXHRcdFx0XHRcdGlkOiAgICAgICAgICB0aGlzLmdldElkRnJvbUhpZXJhcmNoeSggaGllcmFyY2h5ICksXG5cdFx0XHRcdFx0aGllcmFyY2h5OiAgIF8uY2xvbmUoIGhpZXJhcmNoeSApLFxuXHRcdFx0XHRcdGdyb3VwczogICAgICB0aGlzLFxuXHRcdFx0XHRcdF9jb25kaXRpb25zOiBfLnRvQXJyYXkoIGFyZyApXG5cdFx0XHRcdH0gKTtcblxuXHRcdFx0fSBlbHNlIHtcblxuXHRcdFx0XHRoaWVyYXJjaHkucHVzaCggc2x1ZyApO1xuXG5cdFx0XHRcdHRoaXMubWFwQ29uZGl0aW9uR3JvdXBzKCBhcmcsIGdyb3VwcywgaGllcmFyY2h5ICk7XG5cblx0XHRcdFx0aGllcmFyY2h5LnBvcCgpO1xuXHRcdFx0fVxuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0cmV0dXJuIGdyb3Vwcztcblx0fSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgUGFyc2VzIGEgY29uZGl0aW9ucyBoaWVyYXJjaHkgYW5kIGFkZHMgZWFjaCBncm91cCB0byB0aGUgY29sbGVjdGlvbi5cblx0ICpcblx0ICogQHNpbmNlIDIuMS4wXG5cdCAqIEBzaW5jZSAyLjEuMyBUaGUgaGllcmFyY2h5IGFyZyB3YXMgZGVwcmVjYXRlZC5cblx0ICpcblx0ICogQHBhcmFtIHtBcnJheX0gY29uZGl0aW9ucyAgLSBUaGUgcmF3IGNvbmRpdGlvbnMgaGllcmFyY2h5IHRvIHBhcnNlLlxuXHQgKiBAcGFyYW0ge0FycmF5fSBbaGllcmFyY2h5XSAtIERlcHJlY2F0ZWQuIFByZXZpb3VzbHkgdXNlZCB0byB0cmFjayB0aGUgY3VycmVudFxuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxvY2F0aW9uIHdpdGhpbiB0aGUgY29uZGl0aW9ucyBoaWVyYXJjaHkuXG5cdCAqL1xuXHRtYXBDb25kaXRpb25zOiBmdW5jdGlvbiAoIGNvbmRpdGlvbnMsIGhpZXJhcmNoeSApIHtcblxuXHRcdHZhciBncm91cHMgPSB0aGlzLm1hcENvbmRpdGlvbkdyb3VwcyggY29uZGl0aW9ucywgW10sIGhpZXJhcmNoeSApO1xuXG5cdFx0dGhpcy5yZXNldCggZ3JvdXBzICk7XG5cdH0sXG5cblx0Z2V0SWRGcm9tSGllcmFyY2h5OiBmdW5jdGlvbiAoIGhpZXJhcmNoeSApIHtcblx0XHRyZXR1cm4gaGllcmFyY2h5LmpvaW4oICcuJyApO1xuXHR9LFxuXG5cdGdldEFyZ3M6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBhcmdzID0gdGhpcy5hcmdzO1xuXG5cdFx0aWYgKCAhIGFyZ3MgKSB7XG5cdFx0XHRhcmdzID0gQXJncy5nZXRFdmVudEFyZ3MoIHRoaXMucmVhY3Rpb24uZ2V0KCAnZXZlbnQnICkgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gYXJncztcblx0fSxcblxuXHQvKipcblx0ICogQHN1bW1hcnkgUGFyc2VzIGEgcmF3IHZhbHVlIGludG8gYSBsaXN0IG9mIG1vZGVscy5cblx0ICpcblx0ICogSW1wbGVtZW50ZWQgaGVyZSBzbyBpZiB0aGUgbW9kZWxzIGFyZSBnb2luZyB0byBiZSBtZXJnZWQgd2l0aCBjb3JyZXNwb25kaW5nXG5cdCAqIG9uZXMgaW4gdGhlIGV4aXN0aW5nIGNvbGxlY3Rpb24sIHdlIGNhbiBnbyBhaGVhZCBhbmQgdXBkYXRlIHRoZSBgY29uZGl0aW9uc2Bcblx0ICogY29sbGVjdGlvbiBvZiB0aGUgZXhpc3RpbmcgbW9kZWxzIGJhc2VkIG9uIHRoZWlyIHBhc3NlZCBpbiBgX2NvbmRpdGlvbnNgXG5cdCAqIGF0dHJpYnV0ZS4gT3RoZXJ3aXNlIHRoZSBjb25kaXRpb25zIGNvbGxlY3Rpb24gd291bGQgbm90IGJlIHVwZGF0ZWQuIFNlZSBbdGhlXG5cdCAqIGRpc2N1c3Npb24gb24gR2l0SHViXXtAbGluayBodHRwczovL2dpdGh1Yi5jb20vV29yZFBvaW50cy93b3JkcG9pbnRzL2lzc3Vlcy9cbiAgICAgKiA1MTcjaXNzdWVjb21tZW50LTI1MDMwNzE0N30gZm9yIG1vcmUgaW5mb3JtYXRpb24gb24gd2h5IHdlIGRvIGl0IHRoaXMgd2F5LlxuXHQgKlxuXHQgKiBAc2luY2UgMi4xLjNcblx0ICpcblx0ICogQHBhcmFtIHtPYmplY3R8T2JqZWN0W119IHJlc3AgICAgLSBUaGUgcmF3IG1vZGVsKHMpLlxuXHQgKiBAcGFyYW0ge09iamVjdH0gICAgICAgICAgb3B0aW9ucyAtIE9wdGlvbnMgcGFzc2VkIGZyb20gYHNldCgpYC5cblx0ICpcblx0ICogQHJldHVybnMge09iamVjdHxPYmplY3RbXX0gVGhlIGNvbmRpdGlvbiBtb2RlbHMsIHdpdGggYGNvbmRpdGlvbnNgIHByb3BlcnR5XG5cdCAqICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNldCBhcyBuZWVkZWQuXG5cdCAqL1xuXHRwYXJzZTogZnVuY3Rpb24gKCByZXNwLCBvcHRpb25zICkge1xuXG5cdFx0aWYgKCAhIG9wdGlvbnMubWVyZ2UgKSB7XG5cdFx0XHRyZXR1cm4gcmVzcDtcblx0XHR9XG5cblx0XHR2YXIgbW9kZWxzID0gXy5pc0FycmF5KCByZXNwICkgPyByZXNwIDogW3Jlc3BdLFxuXHRcdFx0bW9kZWw7XG5cblx0XHRmb3IgKCB2YXIgaSA9IDA7IGkgPCBtb2RlbHMubGVuZ3RoOyBpKysgKSB7XG5cblx0XHRcdG1vZGVsID0gdGhpcy5nZXQoIG1vZGVsc1sgaSBdLmlkICk7XG5cblx0XHRcdGlmICggISBtb2RlbCApIHtcblx0XHRcdFx0Y29udGludWU7XG5cdFx0XHR9XG5cblx0XHRcdG1vZGVsLnNldENvbmRpdGlvbnMoIG1vZGVsc1sgaSBdLl9jb25kaXRpb25zLCBvcHRpb25zICk7XG5cblx0XHRcdG1vZGVsc1sgaSBdLmNvbmRpdGlvbnMgPSBtb2RlbC5nZXQoICdjb25kaXRpb25zJyApO1xuXHRcdH1cblxuXHRcdHJldHVybiByZXNwO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25Hcm91cHM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uVHlwZVxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlLFxuXHRDb25kaXRpb25UeXBlO1xuXG5Db25kaXRpb25UeXBlID0gQmFzZS5leHRlbmQoe1xuXHRpZEF0dHJpYnV0ZTogJ3NsdWcnXG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25UeXBlO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvblR5cGVzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuQ29sbGVjdGlvblxuICovXG52YXIgQ29uZGl0aW9uVHlwZSA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uVHlwZSxcblx0Q29uZGl0aW9uVHlwZXM7XG5cbkNvbmRpdGlvblR5cGVzID0gQmFja2JvbmUuQ29sbGVjdGlvbi5leHRlbmQoe1xuXG5cdG1vZGVsOiBDb25kaXRpb25UeXBlXG5cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvblR5cGVzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvblxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLk1vZGVsXG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5CYXNlLFxuXHRBcmdzID0gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLFxuXHRFeHRlbnNpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5FeHRlbnNpb25zLFxuXHRGaWVsZHMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkZpZWxkcyxcblx0Q29uZGl0aW9uO1xuXG5Db25kaXRpb24gPSBCYXNlLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IHtcblx0XHR0eXBlOiAnJyxcblx0XHRzZXR0aW5nczogW11cblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIGF0dHJpYnV0ZXMsIG9wdGlvbnMgKSB7XG5cdFx0aWYgKCBvcHRpb25zLmdyb3VwICkge1xuXHRcdFx0dGhpcy5ncm91cCA9IG9wdGlvbnMuZ3JvdXA7XG5cdFx0fVxuXHR9LFxuXG5cdHZhbGlkYXRlOiBmdW5jdGlvbiAoIGF0dHJpYnV0ZXMsIG9wdGlvbnMsIGVycm9ycyApIHtcblxuXHRcdGVycm9ycyA9IGVycm9ycyB8fCBbXTtcblxuXHRcdHZhciBjb25kaXRpb25UeXBlID0gdGhpcy5nZXRUeXBlKCk7XG5cblx0XHRpZiAoICEgY29uZGl0aW9uVHlwZSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR2YXIgZmllbGRzID0gY29uZGl0aW9uVHlwZS5maWVsZHM7XG5cblx0XHRGaWVsZHMudmFsaWRhdGUoXG5cdFx0XHRmaWVsZHNcblx0XHRcdCwgYXR0cmlidXRlcy5zZXR0aW5nc1xuXHRcdFx0LCBlcnJvcnNcblx0XHQpO1xuXG5cdFx0dmFyIGNvbnRyb2xsZXIgPSB0aGlzLmdldENvbnRyb2xsZXIoKTtcblxuXHRcdGlmICggY29udHJvbGxlciApIHtcblx0XHRcdGNvbnRyb2xsZXIudmFsaWRhdGVTZXR0aW5ncyggdGhpcywgYXR0cmlidXRlcy5zZXR0aW5ncywgZXJyb3JzICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGVycm9ycztcblx0fSxcblxuXHRnZXRDb250cm9sbGVyOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgYXJnID0gdGhpcy5nZXRBcmcoKTtcblxuXHRcdGlmICggISBhcmcgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0dmFyIENvbmRpdGlvbnMgPSBFeHRlbnNpb25zLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHRyZXR1cm4gQ29uZGl0aW9ucy5nZXRDb250cm9sbGVyKFxuXHRcdFx0Q29uZGl0aW9ucy5nZXREYXRhVHlwZUZyb21BcmcoIGFyZyApXG5cdFx0XHQsIHRoaXMuZ2V0KCAndHlwZScgKVxuXHRcdCk7XG5cdH0sXG5cblx0Z2V0VHlwZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGFyZyA9IHRoaXMuZ2V0QXJnKCk7XG5cblx0XHRpZiAoICEgYXJnICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHZhciBDb25kaXRpb25zID0gRXh0ZW5zaW9ucy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0cmV0dXJuIENvbmRpdGlvbnMuZ2V0VHlwZShcblx0XHRcdENvbmRpdGlvbnMuZ2V0RGF0YVR5cGVGcm9tQXJnKCBhcmcgKVxuXHRcdFx0LCB0aGlzLmdldCggJ3R5cGUnIClcblx0XHQpO1xuXHR9LFxuXG5cdGdldEFyZzogZnVuY3Rpb24gKCkge1xuXG5cdFx0aWYgKCAhIHRoaXMuYXJnICkge1xuXG5cdFx0XHR2YXIgYXJncyA9IEFyZ3MuZ2V0QXJnc0Zyb21IaWVyYXJjaHkoXG5cdFx0XHRcdHRoaXMuZ2V0SGllcmFyY2h5KClcblx0XHRcdFx0LCB0aGlzLnJlYWN0aW9uLmdldCggJ2V2ZW50JyApXG5cdFx0XHQpO1xuXG5cdFx0XHRpZiAoIGFyZ3MgKSB7XG5cdFx0XHRcdHRoaXMuYXJnID0gYXJnc1sgYXJncy5sZW5ndGggLSAxIF07XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHRoaXMuYXJnO1xuXHR9LFxuXG5cdGdldEhpZXJhcmNoeTogZnVuY3Rpb24gKCkge1xuXHRcdHJldHVybiB0aGlzLmdyb3VwLmdldCggJ2hpZXJhcmNoeScgKTtcblx0fSxcblxuXHRnZXRGdWxsSGllcmFyY2h5OiBmdW5jdGlvbiAoKSB7XG5cblx0XHRyZXR1cm4gdGhpcy5ncm91cC5nZXQoICdncm91cHMnICkuaGllcmFyY2h5LmNvbmNhdChcblx0XHRcdHRoaXMuZ2V0SGllcmFyY2h5KClcblx0XHQpO1xuXHR9LFxuXG5cdGlzTmV3OiBmdW5jdGlvbiAoKSB7XG5cdFx0cmV0dXJuICd1bmRlZmluZWQnID09PSB0eXBlb2YgdGhpcy5yZWFjdGlvbi5nZXQoXG5cdFx0XHRbICdjb25kaXRpb25zJyBdXG5cdFx0XHRcdC5jb25jYXQoIHRoaXMuZ2V0RnVsbEhpZXJhcmNoeSgpIClcblx0XHRcdFx0LmNvbmNhdCggWyAnX2NvbmRpdGlvbnMnLCB0aGlzLmlkIF0gKVxuXHRcdCk7XG5cdH0sXG5cblx0c3luYzogZnVuY3Rpb24gKCBtZXRob2QsIG1vZGVsLCBvcHRpb25zICkge1xuXHRcdG9wdGlvbnMuZXJyb3IoXG5cdFx0XHR7IG1lc3NhZ2U6ICdGZXRjaGluZyBhbmQgc2F2aW5nIGhvb2sgY29uZGl0aW9ucyBpcyBub3Qgc3VwcG9ydGVkLicgfVxuXHRcdCk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25zXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuQ29sbGVjdGlvblxuICovXG52YXIgQ29uZGl0aW9uID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb24sXG5cdENvbmRpdGlvbnM7XG5cbkNvbmRpdGlvbnMgPSBCYWNrYm9uZS5Db2xsZWN0aW9uLmV4dGVuZCh7XG5cblx0Ly8gUmVmZXJlbmNlIHRvIHRoaXMgY29sbGVjdGlvbidzIG1vZGVsLlxuXHRtb2RlbDogQ29uZGl0aW9uLFxuXG5cdGNvbXBhcmF0b3I6ICdpZCcsXG5cblx0c3luYzogZnVuY3Rpb24gKCBtZXRob2QsIGNvbGxlY3Rpb24sIG9wdGlvbnMgKSB7XG5cdFx0b3B0aW9ucy5lcnJvcihcblx0XHRcdHsgbWVzc2FnZTogJ0ZldGNoaW5nIGFuZCBzYXZpbmcgaG9vayBjb25kaXRpb25zIGlzIG5vdCBzdXBwb3J0ZWQuJyB9XG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9ucztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvbkdyb3VwXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdENvbmRpdGlvbiA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb24sXG5cdEFyZ3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MsXG5cdCQgPSBCYWNrYm9uZS4kLFxuXHR0ZW1wbGF0ZSA9IHdwLndvcmRwb2ludHMuaG9va3MudGVtcGxhdGUsXG5cdENvbmRpdGlvbkdyb3VwO1xuXG5Db25kaXRpb25Hcm91cCA9IEJhc2UuZXh0ZW5kKHtcblxuXHRjbGFzc05hbWU6ICdjb25kaXRpb24tZ3JvdXAnLFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stcmVhY3Rpb24tY29uZGl0aW9uLWdyb3VwJyApLFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdhZGQnLCB0aGlzLmFkZE9uZSApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdyZXNldCcsIHRoaXMucmVuZGVyICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ3JlbW92ZScsIHRoaXMubWF5YmVIaWRlICk7XG5cblx0XHR0aGlzLm1vZGVsLm9uKCAnYWRkJywgdGhpcy5yZWFjdGlvbi5sb2NrT3BlbiwgdGhpcy5yZWFjdGlvbiApO1xuXHRcdHRoaXMubW9kZWwub24oICdyZW1vdmUnLCB0aGlzLnJlYWN0aW9uLmxvY2tPcGVuLCB0aGlzLnJlYWN0aW9uICk7XG5cdFx0dGhpcy5tb2RlbC5vbiggJ3Jlc2V0JywgdGhpcy5yZWFjdGlvbi5sb2NrT3BlbiwgdGhpcy5yZWFjdGlvbiApO1xuXHR9LFxuXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbCggdGhpcy50ZW1wbGF0ZSgpICk7XG5cblx0XHR0aGlzLm1heWJlSGlkZSgpO1xuXG5cdFx0dGhpcy4kKCAnLmNvbmRpdGlvbi1ncm91cC10aXRsZScgKS50ZXh0KFxuXHRcdFx0QXJncy5idWlsZEhpZXJhcmNoeUh1bWFuSWQoXG5cdFx0XHRcdEFyZ3MuZ2V0QXJnc0Zyb21IaWVyYXJjaHkoXG5cdFx0XHRcdFx0dGhpcy5tb2RlbC5nZXQoICdoaWVyYXJjaHknIClcblx0XHRcdFx0XHQsIHRoaXMucmVhY3Rpb24ubW9kZWwuZ2V0KCAnZXZlbnQnIClcblx0XHRcdFx0KVxuXHRcdFx0KVxuXHRcdCk7XG5cblx0XHR0aGlzLmFkZEFsbCgpO1xuXG5cdFx0cmV0dXJuIHRoaXM7XG5cdH0sXG5cblx0YWRkT25lOiBmdW5jdGlvbiAoIGNvbmRpdGlvbiApIHtcblxuXHRcdGNvbmRpdGlvbi5yZWFjdGlvbiA9IHRoaXMucmVhY3Rpb24ubW9kZWw7XG5cblx0XHR2YXIgdmlldyA9IG5ldyBDb25kaXRpb24oIHtcblx0XHRcdGVsOiAkKCAnPGRpdiBjbGFzcz1cImNvbmRpdGlvblwiPjwvZGl2PicgKSxcblx0XHRcdG1vZGVsOiBjb25kaXRpb24sXG5cdFx0XHRyZWFjdGlvbjogdGhpcy5yZWFjdGlvblxuXHRcdH0gKTtcblxuXHRcdHZhciAkdmlldyA9IHZpZXcucmVuZGVyKCkuJGVsO1xuXG5cdFx0dGhpcy4kZWwuYXBwZW5kKCAkdmlldyApLnNob3coKTtcblxuXHRcdGlmICggY29uZGl0aW9uLmlzTmV3KCkgKSB7XG5cdFx0XHQkdmlldy5maW5kKCAnOmlucHV0OnZpc2libGU6ZXEoIDEgKScgKS5mb2N1cygpO1xuXHRcdH1cblxuXHRcdHRoaXMubGlzdGVuVG8oIGNvbmRpdGlvbiwgJ2Rlc3Ryb3knLCBmdW5jdGlvbiAoKSB7XG5cdFx0XHR0aGlzLm1vZGVsLmdldCggJ2NvbmRpdGlvbnMnICkucmVtb3ZlKCBjb25kaXRpb24uaWQgKTtcblx0XHR9ICk7XG5cdH0sXG5cblx0YWRkQWxsOiBmdW5jdGlvbiAoKSB7XG5cdFx0dGhpcy5tb2RlbC5nZXQoICdjb25kaXRpb25zJyApLmVhY2goIHRoaXMuYWRkT25lLCB0aGlzICk7XG5cdH0sXG5cblx0Ly8gSGlkZSB0aGUgZ3JvdXAgd2hlbiBpdCBpcyBlbXB0eS5cblx0bWF5YmVIaWRlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHRpZiAoIDAgPT09IHRoaXMubW9kZWwuZ2V0KCAnY29uZGl0aW9ucycgKS5sZW5ndGggKSB7XG5cdFx0XHR0aGlzLiRlbC5oaWRlKCk7XG5cdFx0fVxuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25Hcm91cDtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvbkdyb3Vwc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHRDb25kaXRpb25Hcm91cFZpZXcgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXAsXG5cdEFyZ0hpZXJhcmNoeVNlbGVjdG9yID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkFyZ0hpZXJhcmNoeVNlbGVjdG9yLFxuXHRDb25kaXRpb25TZWxlY3RvciA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25TZWxlY3Rvcixcblx0RXh0ZW5zaW9ucyA9IHdwLndvcmRwb2ludHMuaG9va3MuRXh0ZW5zaW9ucyxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHQkY2FjaGUgPSB3cC53b3JkcG9pbnRzLiRjYWNoZSxcblx0Q29uZGl0aW9uR3JvdXBzO1xuXG5Db25kaXRpb25Hcm91cHMgPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAnY29uZGl0aW9uLWdyb3VwcycsXG5cblx0Y2xhc3NOYW1lOiAnY29uZGl0aW9ucycsXG5cblx0dGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1jb25kaXRpb24tZ3JvdXBzJyApLFxuXG5cdGV2ZW50czoge1xuXHRcdCdjbGljayA+IC5jb25kaXRpb25zLXRpdGxlIC5hZGQtbmV3JzogICAgICAgICAgICdzaG93QXJnU2VsZWN0b3InLFxuXHRcdCdjbGljayA+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmNvbmZpcm0tYWRkLW5ldyc6ICdtYXliZUFkZE5ldycsXG5cdFx0J2NsaWNrID4gLmFkZC1jb25kaXRpb24tZm9ybSAuY2FuY2VsLWFkZC1uZXcnOiAgJ2NhbmNlbEFkZE5ldydcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLkNvbmRpdGlvbnMgPSBFeHRlbnNpb25zLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLmNvbGxlY3Rpb24sICdhZGQnLCB0aGlzLmFkZE9uZSApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuY29sbGVjdGlvbiwgJ3Jlc2V0JywgdGhpcy5yZW5kZXIgKTtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMucmVhY3Rpb24sICdjYW5jZWwnLCB0aGlzLmNhbmNlbEFkZE5ldyApO1xuXG5cdFx0dGhpcy5jb2xsZWN0aW9uLm9uKCAndXBkYXRlJywgdGhpcy5yZWFjdGlvbi5sb2NrT3BlbiwgdGhpcy5yZWFjdGlvbiApO1xuXHRcdHRoaXMuY29sbGVjdGlvbi5vbiggJ3Jlc2V0JywgdGhpcy5yZWFjdGlvbi5sb2NrT3BlbiwgdGhpcy5yZWFjdGlvbiApO1xuXHR9LFxuXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbCggdGhpcy50ZW1wbGF0ZSgpICk7XG5cblx0XHR0aGlzLiRjID0gJGNhY2hlLmNhbGwoIHRoaXMsIHRoaXMuJCApO1xuXG5cdFx0dGhpcy5hZGRBbGwoKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcicsIHRoaXMgKTtcblxuXHRcdC8vIFNlZSBodHRwczovL2dpdGh1Yi5jb20vV29yZFBvaW50cy93b3JkcG9pbnRzL2lzc3Vlcy81MjAuXG5cdFx0aWYgKCB0aGlzLkFyZ1NlbGVjdG9yICkge1xuXG5cdFx0XHR0aGlzLiQoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmFyZy1zZWxlY3RvcnMnICkucmVwbGFjZVdpdGgoXG5cdFx0XHRcdHRoaXMuQXJnU2VsZWN0b3IuJGVsXG5cdFx0XHQpO1xuXG5cdFx0XHR0aGlzLiQoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmNvbmRpdGlvbi1zZWxlY3RvcicgKS5yZXBsYWNlV2l0aChcblx0XHRcdFx0dGhpcy5Db25kaXRpb25TZWxlY3Rvci4kZWxcblx0XHRcdCk7XG5cblx0XHRcdHRoaXMuQXJnU2VsZWN0b3IuZGVsZWdhdGVFdmVudHMoKTtcblx0XHRcdHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IuZGVsZWdhdGVFdmVudHMoKTtcblx0XHRcdHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IudHJpZ2dlckNoYW5nZSgpO1xuXHRcdH1cblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdGFkZEFsbDogZnVuY3Rpb24gKCkge1xuXHRcdHRoaXMuY29sbGVjdGlvbi5lYWNoKCB0aGlzLmFkZE9uZSwgdGhpcyApO1xuXHR9LFxuXG5cdGFkZE9uZTogZnVuY3Rpb24gKCBDb25kaXRpb25Hcm91cCApIHtcblxuXHRcdHZhciB2aWV3ID0gbmV3IENvbmRpdGlvbkdyb3VwVmlldyh7XG5cdFx0XHRtb2RlbDogQ29uZGl0aW9uR3JvdXAsXG5cdFx0XHRyZWFjdGlvbjogdGhpcy5yZWFjdGlvblxuXHRcdH0pO1xuXG5cdFx0dGhpcy4kYyggJz4gLmNvbmRpdGlvbi1ncm91cHMnICkuYXBwZW5kKCB2aWV3LnJlbmRlcigpLiRlbCApO1xuXHR9LFxuXG5cdHNob3dBcmdTZWxlY3RvcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kYyggJz4gLmNvbmRpdGlvbnMtdGl0bGUgLmFkZC1uZXcnICkuYXR0ciggJ2Rpc2FibGVkJywgdHJ1ZSApO1xuXG5cdFx0aWYgKCB0eXBlb2YgdGhpcy5BcmdTZWxlY3RvciA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cblx0XHRcdHZhciBhcmdzID0gdGhpcy5jb2xsZWN0aW9uLmdldEFyZ3MoKTtcblx0XHRcdHZhciBDb25kaXRpb25zID0gdGhpcy5Db25kaXRpb25zO1xuXHRcdFx0dmFyIGFyZ0ZpbHRlcnMgPSBDb25kaXRpb25zLmFyZ0ZpbHRlcnM7XG5cdFx0XHR2YXIgaXNFbnRpdHlBcnJheSA9ICggdGhpcy5jb2xsZWN0aW9uLmhpZXJhcmNoeS5zbGljZSggLTIgKS50b1N0cmluZygpID09PSAnc2V0dGluZ3MsY29uZGl0aW9ucycgKTtcblx0XHRcdHZhciBoYXNDb25kaXRpb25zID0gZnVuY3Rpb24gKCBhcmcgKSB7XG5cblx0XHRcdFx0dmFyIGRhdGFUeXBlID0gQ29uZGl0aW9ucy5nZXREYXRhVHlwZUZyb21BcmcoIGFyZyApO1xuXHRcdFx0XHR2YXIgY29uZGl0aW9ucyA9IENvbmRpdGlvbnMuZ2V0QnlEYXRhVHlwZSggZGF0YVR5cGUgKTtcblxuXHRcdFx0XHRmb3IgKCB2YXIgaSA9IDA7IGkgPCBhcmdGaWx0ZXJzLmxlbmd0aDsgaSsrICkge1xuXHRcdFx0XHRcdGlmICggISBhcmdGaWx0ZXJzWyBpIF0oIGFyZywgZGF0YVR5cGUsIGlzRW50aXR5QXJyYXksIGNvbmRpdGlvbnMgKSApIHtcblx0XHRcdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRyZXR1cm4gISBfLmlzRW1wdHkoIGNvbmRpdGlvbnMgKTtcblx0XHRcdH07XG5cblx0XHRcdHZhciBoaWVyYXJjaGllcyA9IEFyZ3MuZ2V0SGllcmFyY2hpZXNNYXRjaGluZyhcblx0XHRcdFx0eyB0b3A6IGFyZ3MubW9kZWxzLCBlbmQ6IGhhc0NvbmRpdGlvbnMgfVxuXHRcdFx0KTtcblxuXHRcdFx0aWYgKCBfLmlzRW1wdHkoIGhpZXJhcmNoaWVzICkgKSB7XG5cblx0XHRcdFx0dGhpcy4kYyggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAubm8tY29uZGl0aW9ucycgKS5zaG93KCk7XG5cblx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0dGhpcy5BcmdTZWxlY3RvciA9IG5ldyBBcmdIaWVyYXJjaHlTZWxlY3Rvcih7XG5cdFx0XHRcdFx0aGllcmFyY2hpZXM6IGhpZXJhcmNoaWVzLFxuXHRcdFx0XHRcdGVsOiB0aGlzLiQoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmFyZy1zZWxlY3RvcnMnIClcblx0XHRcdFx0fSk7XG5cblx0XHRcdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5BcmdTZWxlY3RvciwgJ2NoYW5nZScsIHRoaXMubWF5YmVTaG93Q29uZGl0aW9uU2VsZWN0b3IgKTtcblxuXHRcdFx0XHR0aGlzLkFyZ1NlbGVjdG9yLnJlbmRlcigpO1xuXG5cdFx0XHRcdHRoaXMuQXJnU2VsZWN0b3IuJHNlbGVjdC5jaGFuZ2UoKTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtJyApLnNsaWRlRG93bigpO1xuXHR9LFxuXG5cdGdldEFyZ1R5cGU6IGZ1bmN0aW9uICggYXJnICkge1xuXG5cdFx0dmFyIGFyZ1R5cGU7XG5cblx0XHRpZiAoICEgYXJnIHx8ICEgYXJnLmdldCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRhcmdUeXBlID0gdGhpcy5Db25kaXRpb25zLmdldERhdGFUeXBlRnJvbUFyZyggYXJnICk7XG5cblx0XHQvLyBXZSBjb21wcmVzcyByZWxhdGlvbnNoaXBzIHRvIGF2b2lkIHJlZHVuZGFuY3kuXG5cdFx0aWYgKCAncmVsYXRpb25zaGlwJyA9PT0gYXJnVHlwZSApIHtcblx0XHRcdGFyZ1R5cGUgPSB0aGlzLmdldEFyZ1R5cGUoIGFyZy5nZXRDaGlsZCggYXJnLmdldCggJ3NlY29uZGFyeScgKSApICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGFyZ1R5cGU7XG5cdH0sXG5cblx0bWF5YmVTaG93Q29uZGl0aW9uU2VsZWN0b3I6IGZ1bmN0aW9uICggYXJnU2VsZWN0b3JzLCBhcmcgKSB7XG5cblx0XHR2YXIgYXJnVHlwZSA9IHRoaXMuZ2V0QXJnVHlwZSggYXJnICk7XG5cblx0XHRpZiAoICEgYXJnVHlwZSApIHtcblx0XHRcdGlmICggdGhpcy4kY29uZGl0aW9uU2VsZWN0b3IgKSB7XG5cdFx0XHRcdHRoaXMuJGNvbmRpdGlvblNlbGVjdG9yLmhpZGUoKTtcblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHZhciBjb25kaXRpb25zID0gdGhpcy5Db25kaXRpb25zLmdldEJ5RGF0YVR5cGUoIGFyZ1R5cGUgKTtcblxuXHRcdGlmICggISB0aGlzLkNvbmRpdGlvblNlbGVjdG9yICkge1xuXG5cdFx0XHR0aGlzLkNvbmRpdGlvblNlbGVjdG9yID0gbmV3IENvbmRpdGlvblNlbGVjdG9yKHtcblx0XHRcdFx0ZWw6IHRoaXMuJCggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAuY29uZGl0aW9uLXNlbGVjdG9yJyApXG5cdFx0XHR9KTtcblxuXHRcdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5Db25kaXRpb25TZWxlY3RvciwgJ2NoYW5nZScsIHRoaXMuY29uZGl0aW9uU2VsZWN0aW9uQ2hhbmdlICk7XG5cblx0XHRcdHRoaXMuJGNvbmRpdGlvblNlbGVjdG9yID0gdGhpcy5Db25kaXRpb25TZWxlY3Rvci4kZWw7XG5cdFx0fVxuXG5cdFx0dGhpcy5Db25kaXRpb25TZWxlY3Rvci5jb2xsZWN0aW9uLnJlc2V0KCBfLnRvQXJyYXkoIGNvbmRpdGlvbnMgKSApO1xuXG5cdFx0dGhpcy4kY29uZGl0aW9uU2VsZWN0b3Iuc2hvdygpLmZpbmQoICdzZWxlY3QnICkuY2hhbmdlKCk7XG5cdH0sXG5cblx0Y2FuY2VsQWRkTmV3OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtJyApLnNsaWRlVXAoKTtcblx0XHR0aGlzLiRjKCAnPiAuY29uZGl0aW9ucy10aXRsZSAuYWRkLW5ldycgKS5hdHRyKCAnZGlzYWJsZWQnLCBmYWxzZSApO1xuXHR9LFxuXG5cdGNvbmRpdGlvblNlbGVjdGlvbkNoYW5nZTogZnVuY3Rpb24gKCBzZWxlY3RvciwgdmFsdWUgKSB7XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5jb25maXJtLWFkZC1uZXcnIClcblx0XHRcdC5hdHRyKCAnZGlzYWJsZWQnLCAhIHZhbHVlICk7XG5cdH0sXG5cblx0bWF5YmVBZGROZXc6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBzZWxlY3RlZCA9IHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IuZ2V0U2VsZWN0ZWQoKTtcblxuXHRcdGlmICggISBzZWxlY3RlZCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR2YXIgaGllcmFyY2h5ID0gdGhpcy5BcmdTZWxlY3Rvci5nZXRIaWVyYXJjaHkoKSxcblx0XHRcdGlkID0gdGhpcy5jb2xsZWN0aW9uLmdldElkRnJvbUhpZXJhcmNoeSggaGllcmFyY2h5ICksXG5cdFx0XHRDb25kaXRpb25Hcm91cCA9IHRoaXMuY29sbGVjdGlvbi5nZXQoIGlkICk7XG5cblx0XHRpZiAoICEgQ29uZGl0aW9uR3JvdXAgKSB7XG5cdFx0XHRDb25kaXRpb25Hcm91cCA9IHRoaXMuY29sbGVjdGlvbi5hZGQoe1xuXHRcdFx0XHRpZDogaWQsXG5cdFx0XHRcdGhpZXJhcmNoeTogaGllcmFyY2h5LFxuXHRcdFx0XHRncm91cHM6IHRoaXMuY29sbGVjdGlvblxuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0Q29uZGl0aW9uR3JvdXAuYWRkKCB7IHR5cGU6IHNlbGVjdGVkIH0gKTtcblxuXHRcdHdwLmExMXkuc3BlYWsoIHRoaXMuQ29uZGl0aW9ucy5kYXRhLmwxMG4uYWRkZWRfY29uZGl0aW9uICk7XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtJyApLmhpZGUoKTtcblx0XHR0aGlzLiRjKCAnPiAuY29uZGl0aW9ucy10aXRsZSAuYWRkLW5ldycgKS5hdHRyKCAnZGlzYWJsZWQnLCBmYWxzZSApO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25Hcm91cHM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25TZWxlY3RvclxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHRDb25kaXRpb25UeXBlcyA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uVHlwZXMsXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0Q29uZGl0aW9uU2VsZWN0b3I7XG5cbkNvbmRpdGlvblNlbGVjdG9yID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ2NvbmRpdGlvbi1zZWxlY3RvcicsXG5cblx0dGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1jb25kaXRpb24tc2VsZWN0b3InICksXG5cblx0b3B0aW9uVGVtcGxhdGU6IHRlbXBsYXRlKCAnaG9vay1hcmctb3B0aW9uJyApLFxuXG5cdGV2ZW50czoge1xuXHRcdCdjaGFuZ2Ugc2VsZWN0JzogJ3RyaWdnZXJDaGFuZ2UnXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBvcHRpb25zICkge1xuXG5cdFx0dGhpcy5sYWJlbCA9IG9wdGlvbnMubGFiZWw7XG5cblx0XHRpZiAoICEgdGhpcy5jb2xsZWN0aW9uICkge1xuXHRcdFx0dGhpcy5jb2xsZWN0aW9uID0gbmV3IENvbmRpdGlvblR5cGVzKHsgY29tcGFyYXRvcjogJ3RpdGxlJyB9KTtcblx0XHR9XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLmNvbGxlY3Rpb24sICd1cGRhdGUnLCB0aGlzLnJlbmRlciApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuY29sbGVjdGlvbiwgJ3Jlc2V0JywgdGhpcy5yZW5kZXIgKTtcblx0fSxcblxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGVsLmh0bWwoXG5cdFx0XHR0aGlzLnRlbXBsYXRlKFxuXHRcdFx0XHR7IGxhYmVsOiB0aGlzLmxhYmVsLCBuYW1lOiB0aGlzLmNpZCArICdfY29uZGl0aW9uX3NlbGVjdG9yJyB9XG5cdFx0XHQpXG5cdFx0KTtcblxuXHRcdHRoaXMuJHNlbGVjdCA9IHRoaXMuJCggJ3NlbGVjdCcgKTtcblxuXHRcdHRoaXMuY29sbGVjdGlvbi5lYWNoKCBmdW5jdGlvbiAoIGNvbmRpdGlvbiApIHtcblxuXHRcdFx0dGhpcy4kc2VsZWN0LmFwcGVuZCggdGhpcy5vcHRpb25UZW1wbGF0ZSggY29uZGl0aW9uLmF0dHJpYnV0ZXMgKSApO1xuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0dGhpcy50cmlnZ2VyKCAncmVuZGVyJywgdGhpcyApO1xuXG5cdFx0cmV0dXJuIHRoaXM7XG5cdH0sXG5cblx0dHJpZ2dlckNoYW5nZTogZnVuY3Rpb24gKCBldmVudCApIHtcblxuXHRcdHRoaXMudHJpZ2dlciggJ2NoYW5nZScsIHRoaXMsIHRoaXMuZ2V0U2VsZWN0ZWQoKSwgZXZlbnQgKTtcblx0fSxcblxuXHRnZXRTZWxlY3RlZDogZnVuY3Rpb24gKCkge1xuXG5cdFx0cmV0dXJuIHRoaXMuJHNlbGVjdC52YWwoKTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uU2VsZWN0b3I7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25cbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHRFeHRlbnNpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5FeHRlbnNpb25zLFxuXHRGaWVsZHMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkZpZWxkcyxcblx0Q29uZGl0aW9uO1xuXG5Db25kaXRpb24gPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAnY29uZGl0aW9uJyxcblxuXHRjbGFzc05hbWU6ICd3b3JkcG9pbnRzLWhvb2stY29uZGl0aW9uJyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLXJlYWN0aW9uLWNvbmRpdGlvbicgKSxcblxuXHRldmVudHM6IHtcblx0XHQnY2xpY2sgLmRlbGV0ZSc6ICdkZXN0cm95J1xuXHR9LFxuXG5cdGluaXRpYWxpemU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdjaGFuZ2UnLCB0aGlzLnJlbmRlciApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdkZXN0cm95JywgdGhpcy5yZW1vdmUgKTtcblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdpbnZhbGlkJywgdGhpcy5tb2RlbC5yZWFjdGlvbi5zaG93RXJyb3IgKTtcblxuXHRcdHRoaXMuZXh0ZW5zaW9uID0gRXh0ZW5zaW9ucy5nZXQoICdjb25kaXRpb25zJyApO1xuXHR9LFxuXG5cdC8vIERpc3BsYXkgdGhlIGNvbmRpdGlvbiBzZXR0aW5ncyBmb3JtLlxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGVsLmh0bWwoIHRoaXMudGVtcGxhdGUoKSApO1xuXG5cdFx0dGhpcy4kdGl0bGUgPSB0aGlzLiQoICcuY29uZGl0aW9uLXRpdGxlJyApO1xuXHRcdHRoaXMuJHNldHRpbmdzID0gdGhpcy4kKCAnLmNvbmRpdGlvbi1zZXR0aW5ncycgKTtcblxuXHRcdHRoaXMucmVuZGVyVGl0bGUoKTtcblx0XHR0aGlzLnJlbmRlclNldHRpbmdzKCk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXInLCB0aGlzICk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHRyZW5kZXJUaXRsZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGNvbmRpdGlvblR5cGUgPSB0aGlzLm1vZGVsLmdldFR5cGUoKTtcblxuXHRcdGlmICggY29uZGl0aW9uVHlwZSApIHtcblx0XHRcdHRoaXMuJHRpdGxlLnRleHQoIGNvbmRpdGlvblR5cGUudGl0bGUgKTtcblx0XHR9XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXI6dGl0bGUnLCB0aGlzICk7XG5cdH0sXG5cblx0cmVuZGVyU2V0dGluZ3M6IGZ1bmN0aW9uICgpIHtcblxuXHRcdC8vIEJ1aWxkIHRoZSBmaWVsZHMgYmFzZWQgb24gdGhlIGNvbmRpdGlvbiB0eXBlLlxuXHRcdHZhciBjb25kaXRpb25UeXBlID0gdGhpcy5tb2RlbC5nZXRUeXBlKCksXG5cdFx0XHRmaWVsZHMgPSAnJztcblxuXHRcdHZhciBmaWVsZE5hbWVQcmVmaXggPSBfLmNsb25lKCB0aGlzLm1vZGVsLmdldEZ1bGxIaWVyYXJjaHkoKSApO1xuXHRcdGZpZWxkTmFtZVByZWZpeC51bnNoaWZ0KCAnY29uZGl0aW9ucycgKTtcblx0XHRmaWVsZE5hbWVQcmVmaXgucHVzaChcblx0XHRcdCdfY29uZGl0aW9ucydcblx0XHRcdCwgdGhpcy5tb2RlbC5nZXQoICdpZCcgKVxuXHRcdFx0LCAnc2V0dGluZ3MnXG5cdFx0KTtcblxuXHRcdHZhciBmaWVsZE5hbWUgPSBfLmNsb25lKCBmaWVsZE5hbWVQcmVmaXggKTtcblxuXHRcdGZpZWxkTmFtZS5wb3AoKTtcblx0XHRmaWVsZE5hbWUucHVzaCggJ3R5cGUnICk7XG5cblx0XHRmaWVsZHMgKz0gRmllbGRzLmNyZWF0ZShcblx0XHRcdGZpZWxkTmFtZVxuXHRcdFx0LCB0aGlzLm1vZGVsLmdldCggJ3R5cGUnIClcblx0XHRcdCwgeyB0eXBlOiAnaGlkZGVuJyB9XG5cdFx0KTtcblxuXHRcdGlmICggY29uZGl0aW9uVHlwZSApIHtcblx0XHRcdHZhciBjb250cm9sbGVyID0gdGhpcy5leHRlbnNpb24uZ2V0Q29udHJvbGxlcihcblx0XHRcdFx0Y29uZGl0aW9uVHlwZS5kYXRhX3R5cGVcblx0XHRcdFx0LCBjb25kaXRpb25UeXBlLnNsdWdcblx0XHRcdCk7XG5cblx0XHRcdGlmICggY29udHJvbGxlciApIHtcblx0XHRcdFx0ZmllbGRzICs9IGNvbnRyb2xsZXIucmVuZGVyU2V0dGluZ3MoIHRoaXMsIGZpZWxkTmFtZVByZWZpeCApO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHRoaXMuJHNldHRpbmdzLmFwcGVuZCggZmllbGRzICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXI6c2V0dGluZ3MnLCB0aGlzICk7XG5cdH0sXG5cblx0Ly8gUmVtb3ZlIHRoZSBpdGVtLCBkZXN0cm95IHRoZSBtb2RlbC5cblx0ZGVzdHJveTogZnVuY3Rpb24gKCkge1xuXG5cdFx0d3AuYTExeS5zcGVhayggdGhpcy5leHRlbnNpb24uZGF0YS5sMTBuLmRlbGV0ZWRfY29uZGl0aW9uICk7XG5cblx0XHR0aGlzLm1vZGVsLmRlc3Ryb3koKTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uO1xuIl19
