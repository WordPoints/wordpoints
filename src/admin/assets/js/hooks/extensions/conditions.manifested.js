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
	data = wp.wordpoints.hooks.view.data,
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

		this.model.destroy();
	}
});

module.exports = Condition;

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset:utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMubWFuaWZlc3QuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9uLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZW50aXR5LWFycmF5LWNvbnRhaW5zLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2NvbmRpdGlvbnMvZXF1YWxzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2V4dGVuc2lvbnMvY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLWdyb3VwLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tZ3JvdXBzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tdHlwZS5qcyIsInVuYnVpbHQvYWRtaW4vYXNzZXRzL2pzL2hvb2tzL2V4dGVuc2lvbnMvY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLXR5cGVzLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24uanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbnMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLWdyb3VwLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cHMuanMiLCJ1bmJ1aWx0L2FkbWluL2Fzc2V0cy9qcy9ob29rcy9leHRlbnNpb25zL2NvbmRpdGlvbnMvdmlld3MvY29uZGl0aW9uLXNlbGVjdG9yLmpzIiwidW5idWlsdC9hZG1pbi9hc3NldHMvanMvaG9va3MvZXh0ZW5zaW9ucy9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN0Q0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzVDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25FQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDOURBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdlBBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3ZJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMzS0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDZkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDaklBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3hCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3hGQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwT0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsInZhciBob29rcyA9IHdwLndvcmRwb2ludHMuaG9va3M7XG5cbi8vIE1vZGVsc1xuaG9va3MubW9kZWwuQ29uZGl0aW9uICAgICAgID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9uLmpzJyApO1xuaG9va3MubW9kZWwuQ29uZGl0aW9ucyAgICAgID0gcmVxdWlyZSggJy4vY29uZGl0aW9ucy9tb2RlbHMvY29uZGl0aW9ucy5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3VwICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi1ncm91cC5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3VwcyA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi1ncm91cHMuanMnICk7XG5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL21vZGVscy9jb25kaXRpb24tdHlwZS5qcycgKTtcbmhvb2tzLm1vZGVsLkNvbmRpdGlvblR5cGVzICA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvbW9kZWxzL2NvbmRpdGlvbi10eXBlcy5qcycgKTtcblxuLy8gVmlld3Ncbmhvb2tzLnZpZXcuQ29uZGl0aW9uICAgICAgICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi5qcycgKTtcbmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXAgICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cC5qcycgKTtcbmhvb2tzLnZpZXcuQ29uZGl0aW9uU2VsZWN0b3IgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1zZWxlY3Rvci5qcycgKTtcbmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBzICAgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL3ZpZXdzL2NvbmRpdGlvbi1ncm91cHMuanMnICk7XG5cbi8vIENvbnRyb2xsZXJzLlxuaG9va3MuZXh0ZW5zaW9uLkNvbmRpdGlvbnMgPSByZXF1aXJlKCAnLi9jb25kaXRpb25zL2NvbnRyb2xsZXJzL2V4dGVuc2lvbi5qcycgKTtcbmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvbiA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9uLmpzJyApO1xuXG52YXIgQ29uZGl0aW9ucyA9IG5ldyBob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucygpO1xuXG4vLyBDb25kaXRpb25zLlxudmFyIEVxdWFscyA9IHJlcXVpcmUoICcuL2NvbmRpdGlvbnMvY29udHJvbGxlcnMvY29uZGl0aW9ucy9lcXVhbHMuanMnICk7XG5cbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAndGV4dCcsICdlcXVhbHMnLCBFcXVhbHMgKTtcbkNvbmRpdGlvbnMucmVnaXN0ZXJDb250cm9sbGVyKCAnZW50aXR5JywgJ2VxdWFscycsIEVxdWFscyApO1xuQ29uZGl0aW9ucy5yZWdpc3RlckNvbnRyb2xsZXIoICdlbnRpdHlfYXJyYXknLCAnZXF1YWxzJywgRXF1YWxzICk7XG5Db25kaXRpb25zLnJlZ2lzdGVyQ29udHJvbGxlcihcblx0J2VudGl0eV9hcnJheSdcblx0LCAnY29udGFpbnMnXG5cdCwgcmVxdWlyZSggJy4vY29uZGl0aW9ucy9jb250cm9sbGVycy9jb25kaXRpb25zL2VudGl0eS1hcnJheS1jb250YWlucy5qcycgKVxuKTtcblxuLy8gUmVnaXN0ZXIgdGhlIGV4dGVuc2lvbi5cbmhvb2tzLkV4dGVuc2lvbnMuYWRkKCBDb25kaXRpb25zICk7XG5cbi8vIEVPRlxuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLmNvbmRpdGlvbi5Db25kaXRpb25cbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICovXG5cbnZhciBGaWVsZHMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkZpZWxkcyxcblx0Q29uZGl0aW9uO1xuXG5Db25kaXRpb24gPSBCYWNrYm9uZS5Nb2RlbC5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0c2x1ZzogJycsXG5cdFx0ZmllbGRzOiBbXVxuXHR9LFxuXG5cdGlkQXR0cmlidXRlOiAnc2x1ZycsXG5cblx0cmVuZGVyU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBmaWVsZE5hbWVQcmVmaXggKSB7XG5cblx0XHR2YXIgZmllbGRzSFRNTCA9ICcnO1xuXG5cdFx0Xy5lYWNoKCB0aGlzLmdldCggJ2ZpZWxkcycgKSwgZnVuY3Rpb24gKCBzZXR0aW5nLCBuYW1lICkge1xuXG5cdFx0XHR2YXIgZmllbGROYW1lID0gXy5jbG9uZSggZmllbGROYW1lUHJlZml4ICk7XG5cblx0XHRcdGZpZWxkTmFtZS5wdXNoKCBuYW1lICk7XG5cblx0XHRcdGZpZWxkc0hUTUwgKz0gRmllbGRzLmNyZWF0ZShcblx0XHRcdFx0ZmllbGROYW1lXG5cdFx0XHRcdCwgY29uZGl0aW9uLm1vZGVsLmF0dHJpYnV0ZXMuc2V0dGluZ3NbIG5hbWUgXVxuXHRcdFx0XHQsIHNldHRpbmdcblx0XHRcdCk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHRyZXR1cm4gZmllbGRzSFRNTDtcblx0fSxcblxuXHR2YWxpZGF0ZVNldHRpbmdzOiBmdW5jdGlvbiAoKSB7fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLmNvbmRpdGlvbi5FbnRpdHlBcnJheUNvbnRhaW5zXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvblxuICovXG5cbnZhciBDb25kaXRpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvbixcblx0Q29uZGl0aW9uR3JvdXBzID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25Hcm91cHMsXG5cdENvbmRpdGlvbkdyb3Vwc1ZpZXcgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uR3JvdXBzLFxuXHRFeHRlbnNpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5FeHRlbnNpb25zLFxuXHRBcmdzQ29sbGVjdGlvbiA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQXJncyxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0RW50aXR5QXJyYXlDb250YWlucztcblxuRW50aXR5QXJyYXlDb250YWlucyA9IENvbmRpdGlvbi5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0c2x1ZzogJ2VudGl0eV9hcnJheV9jb250YWlucydcblx0fSxcblxuXHRyZW5kZXJTZXR0aW5nczogZnVuY3Rpb24gKCBjb25kaXRpb24sIGZpZWxkTmFtZVByZWZpeCApIHtcblxuXHRcdC8vIFJlbmRlciB0aGUgbWFpbiBmaWVsZHMuXG5cdFx0dmFyIGZpZWxkcyA9IHRoaXMuY29uc3RydWN0b3IuX19zdXBlcl9fLnJlbmRlclNldHRpbmdzLmFwcGx5KFxuXHRcdFx0dGhpc1xuXHRcdFx0LCBbIGNvbmRpdGlvbiwgZmllbGROYW1lUHJlZml4IF1cblx0XHQpO1xuXG5cdFx0Y29uZGl0aW9uLiRzZXR0aW5ncy5hcHBlbmQoIGZpZWxkcyApO1xuXG5cdFx0Ly8gUmVuZGVyIHZpZXcgZm9yIHN1Yi1jb25kaXRpb25zLlxuXHRcdHZhciBhcmcgPSBBcmdzLmdldEVudGl0eShcblx0XHRcdGNvbmRpdGlvbi5tb2RlbC5nZXRBcmcoKS5nZXQoICdlbnRpdHlfc2x1ZycgKVxuXHRcdCk7XG5cblx0XHRjb25kaXRpb24ubW9kZWwuc3ViR3JvdXBzID0gbmV3IENvbmRpdGlvbkdyb3VwcyggbnVsbCwge1xuXHRcdFx0YXJnczogbmV3IEFyZ3NDb2xsZWN0aW9uKCBbIGFyZyBdICksXG5cdFx0XHRoaWVyYXJjaHk6IGNvbmRpdGlvbi5tb2RlbC5nZXRGdWxsSGllcmFyY2h5KCkuY29uY2F0KFxuXHRcdFx0XHRbICdfY29uZGl0aW9ucycsIGNvbmRpdGlvbi5tb2RlbC5pZCwgJ3NldHRpbmdzJywgJ2NvbmRpdGlvbnMnIF1cblx0XHRcdCksXG5cdFx0XHRyZWFjdGlvbjogY29uZGl0aW9uLnJlYWN0aW9uLm1vZGVsLFxuXHRcdFx0X2NvbmRpdGlvbnM6IGNvbmRpdGlvbi5tb2RlbC5nZXQoICdzZXR0aW5ncycgKS5jb25kaXRpb25zXG5cdFx0fSApO1xuXG5cdFx0dmFyIHZpZXcgPSBuZXcgQ29uZGl0aW9uR3JvdXBzVmlldygge1xuXHRcdFx0Y29sbGVjdGlvbjogY29uZGl0aW9uLm1vZGVsLnN1Ykdyb3Vwcyxcblx0XHRcdHJlYWN0aW9uOiBjb25kaXRpb24ucmVhY3Rpb25cblx0XHR9KTtcblxuXHRcdGNvbmRpdGlvbi4kc2V0dGluZ3MuYXBwZW5kKCB2aWV3LnJlbmRlcigpLiRlbCApO1xuXG5cdFx0cmV0dXJuICcnO1xuXHR9LFxuXG5cdHZhbGlkYXRlU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBzZXR0aW5ncywgZXJyb3JzICkge1xuXG5cdFx0RXh0ZW5zaW9ucy5nZXQoICdjb25kaXRpb25zJyApLnZhbGlkYXRlQ29uZGl0aW9ucyhcblx0XHRcdFsgY29uZGl0aW9uLnN1Ykdyb3VwcyBdXG5cdFx0XHQsIHNldHRpbmdzLmNvbmRpdGlvbnNcblx0XHRcdCwgZXJyb3JzXG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gRW50aXR5QXJyYXlDb250YWlucztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5leHRlbnNpb24uQ29uZGl0aW9ucy5jb25kaXRpb24uRXF1YWxzXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvblxuICovXG5cbnZhciBDb25kaXRpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zLkNvbmRpdGlvbixcblx0RXF1YWxzO1xuXG5FcXVhbHMgPSBDb25kaXRpb24uZXh0ZW5kKHtcblxuXHRkZWZhdWx0czoge1xuXHRcdHNsdWc6ICdlcXVhbHMnXG5cdH0sXG5cblx0cmVuZGVyU2V0dGluZ3M6IGZ1bmN0aW9uICggY29uZGl0aW9uLCBmaWVsZE5hbWVQcmVmaXggKSB7XG5cblx0XHR2YXIgZmllbGRzID0gdGhpcy5nZXQoICdmaWVsZHMnICksXG5cdFx0XHRhcmcgPSBjb25kaXRpb24ubW9kZWwuZ2V0QXJnKCk7XG5cblx0XHQvLyBXZSByZW5kZXIgdGhlIGB2YWx1ZWAgZmllbGQgZGlmZmVyZW50bHkgYmFzZWQgb24gdGhlIHR5cGUgb2YgYXJndW1lbnQuXG5cdFx0aWYgKCBhcmcgKSB7XG5cblx0XHRcdHZhciB0eXBlID0gYXJnLmdldCggJ190eXBlJyApO1xuXG5cdFx0XHRmaWVsZHMgPSBfLmV4dGVuZCgge30sIGZpZWxkcyApO1xuXG5cdFx0XHRzd2l0Y2ggKCB0eXBlICkge1xuXG5cdFx0XHRcdGNhc2UgJ2F0dHInOlxuXHRcdFx0XHRcdGZpZWxkcy52YWx1ZSA9IF8uZXh0ZW5kKFxuXHRcdFx0XHRcdFx0e31cblx0XHRcdFx0XHRcdCwgZmllbGRzLnZhbHVlXG5cdFx0XHRcdFx0XHQsIHsgdHlwZTogYXJnLmdldCggJ2RhdGFfdHlwZScgKSB9XG5cdFx0XHRcdFx0KTtcblx0XHRcdFx0XHQvKiBmYWxscyB0aHJvdWdoICovXG5cdFx0XHRcdGNhc2UgJ2VudGl0eSc6XG5cdFx0XHRcdFx0dmFyIHZhbHVlcyA9IGFyZy5nZXQoICd2YWx1ZXMnICk7XG5cblx0XHRcdFx0XHRpZiAoIHZhbHVlcyApIHtcblxuXHRcdFx0XHRcdFx0ZmllbGRzLnZhbHVlID0gXy5leHRlbmQoXG5cdFx0XHRcdFx0XHRcdHt9XG5cdFx0XHRcdFx0XHRcdCwgZmllbGRzLnZhbHVlXG5cdFx0XHRcdFx0XHRcdCwgeyB0eXBlOiAnc2VsZWN0Jywgb3B0aW9uczogdmFsdWVzIH1cblx0XHRcdFx0XHRcdCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0fVxuXG5cdFx0XHR0aGlzLnNldCggJ2ZpZWxkcycsIGZpZWxkcyApO1xuXHRcdH1cblxuXHRcdHJldHVybiB0aGlzLmNvbnN0cnVjdG9yLl9fc3VwZXJfXy5yZW5kZXJTZXR0aW5ncy5hcHBseShcblx0XHRcdHRoaXNcblx0XHRcdCwgWyBjb25kaXRpb24sIGZpZWxkTmFtZVByZWZpeCBdXG5cdFx0KTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gRXF1YWxzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLmV4dGVuc2lvbi5Db25kaXRpb25zXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuTW9kZWxcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLmNvbnRyb2xsZXIuRXh0ZW5zaW9uXG4gKlxuICpcbiAqL1xudmFyIEV4dGVuc2lvbiA9IHdwLndvcmRwb2ludHMuaG9va3MuY29udHJvbGxlci5FeHRlbnNpb24sXG5cdENvbmRpdGlvbkdyb3VwcyA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXBzLFxuXHRDb25kaXRpb25zR3JvdXBzVmlldyA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cHMsXG5cdGdldERlZXAgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnV0aWwuZ2V0RGVlcCxcblx0ZGF0YSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5kYXRhLFxuXHRDb25kaXRpb25zO1xuXG5Db25kaXRpb25zID0gRXh0ZW5zaW9uLmV4dGVuZCh7XG5cblx0ZGVmYXVsdHM6IHtcblx0XHRzbHVnOiAnY29uZGl0aW9ucydcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLmRhdGFUeXBlID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKCB7IGlkQXR0cmlidXRlOiAnc2x1ZycgfSApO1xuXHRcdHRoaXMuY29udHJvbGxlcnMgPSBuZXcgQmFja2JvbmUuQ29sbGVjdGlvbihcblx0XHRcdFtdXG5cdFx0XHQsIHsgY29tcGFyYXRvcjogJ3NsdWcnLCBtb2RlbDogdGhpcy5kYXRhVHlwZSB9XG5cdFx0KTtcblx0fSxcblxuXHRpbml0UmVhY3Rpb246IGZ1bmN0aW9uICggcmVhY3Rpb24gKSB7XG5cblx0XHRyZWFjdGlvbi5jb25kaXRpb25zID0ge307XG5cdFx0cmVhY3Rpb24ubW9kZWwuY29uZGl0aW9ucyA9IHt9O1xuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSByZWFjdGlvbi5tb2RlbC5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0aWYgKCAhIGNvbmRpdGlvbnMgKSB7XG5cdFx0XHRjb25kaXRpb25zID0ge307XG5cdFx0fVxuXG5cdFx0dmFyIGFjdGlvblR5cGVzID0gXy5rZXlzKFxuXHRcdFx0ZGF0YS5ldmVudF9hY3Rpb25fdHlwZXNbIHJlYWN0aW9uLm1vZGVsLmdldCggJ2V2ZW50JyApIF1cblx0XHQpO1xuXG5cdFx0aWYgKCAhIGFjdGlvblR5cGVzICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGFjdGlvblR5cGVzID0gXy5pbnRlcnNlY3Rpb24oXG5cdFx0XHRyZWFjdGlvbi5SZWFjdG9yLmdldCggJ2FjdGlvbl90eXBlcycgKVxuXHRcdFx0LCBhY3Rpb25UeXBlc1xuXHRcdCk7XG5cblx0XHRfLmVhY2goIGFjdGlvblR5cGVzLCBmdW5jdGlvbiAoIGFjdGlvblR5cGUgKSB7XG5cblx0XHRcdHZhciBjb25kaXRpb25Hcm91cHMgPSBjb25kaXRpb25zWyBhY3Rpb25UeXBlIF07XG5cblx0XHRcdGlmICggISBjb25kaXRpb25Hcm91cHMgKSB7XG5cdFx0XHRcdGNvbmRpdGlvbkdyb3VwcyA9IFtdO1xuXHRcdFx0fVxuXG5cdFx0XHRyZWFjdGlvbi5tb2RlbC5jb25kaXRpb25zWyBhY3Rpb25UeXBlIF0gPSBuZXcgQ29uZGl0aW9uR3JvdXBzKCBudWxsLCB7XG5cdFx0XHRcdGhpZXJhcmNoeTogWyBhY3Rpb25UeXBlIF0sXG5cdFx0XHRcdHJlYWN0aW9uOiByZWFjdGlvbi5tb2RlbCxcblx0XHRcdFx0X2NvbmRpdGlvbnM6IGNvbmRpdGlvbkdyb3Vwc1xuXHRcdFx0fSApO1xuXG5cdFx0fSwgdGhpcyApO1xuXG5cdFx0dmFyIGFwcGVuZGVkID0gZmFsc2U7XG5cblx0XHR0aGlzLmxpc3RlblRvKCByZWFjdGlvbiwgJ3JlbmRlcjpmaWVsZHMnLCBmdW5jdGlvbiAoICRlbCwgY3VycmVudEFjdGlvblR5cGUgKSB7XG5cblx0XHRcdHZhciBjb25kaXRpb25zVmlldyA9IHJlYWN0aW9uLmNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF07XG5cblx0XHRcdGlmICggISBjb25kaXRpb25zVmlldyApIHtcblx0XHRcdFx0Y29uZGl0aW9uc1ZpZXcgPSByZWFjdGlvbi5jb25kaXRpb25zWyBjdXJyZW50QWN0aW9uVHlwZSBdID0gbmV3IENvbmRpdGlvbnNHcm91cHNWaWV3KCB7XG5cdFx0XHRcdFx0Y29sbGVjdGlvbjogcmVhY3Rpb24ubW9kZWwuY29uZGl0aW9uc1sgY3VycmVudEFjdGlvblR5cGUgXSxcblx0XHRcdFx0XHRyZWFjdGlvbjogcmVhY3Rpb25cblx0XHRcdFx0fSk7XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIHdlJ3ZlIGFscmVhZHkgYXBwZW5kZWQgdGhlIGNvbnRhaW5lciB2aWV3IHRvIHRoZSByZWFjdGlvbiB2aWV3LFxuXHRcdFx0Ly8gdGhlbiB3ZSBkb24ndCBuZWVkIHRvIGRvIHRoYXQgYWdhaW4uXG5cdFx0XHRpZiAoIGFwcGVuZGVkICkge1xuXG5cdFx0XHRcdHZhciBjb25kaXRpb25zQ29sbGVjdGlvbiA9IHJlYWN0aW9uLm1vZGVsLmNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF07XG5cdFx0XHRcdHZhciBjb25kaXRpb25zID0gcmVhY3Rpb24ubW9kZWwuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdFx0XHRpZiAoICEgY29uZGl0aW9ucyApIHtcblx0XHRcdFx0XHRjb25kaXRpb25zID0ge307XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBIb3dldmVyLCB3ZSBkbyBuZWVkIHRvIHVwZGF0ZSB0aGUgY29uZGl0aW9uIGNvbGxlY3Rpb24sIGluIGNhc2Vcblx0XHRcdFx0Ly8gc29tZSBvZiB0aGUgY29uZGl0aW9uIG1vZGVscyBoYXZlIGJlZW4gcmVtb3ZlZCBvciBuZXcgb25lcyBhZGRlZC5cblx0XHRcdFx0Y29uZGl0aW9uc0NvbGxlY3Rpb24uc2V0KFxuXHRcdFx0XHRcdGNvbmRpdGlvbnNDb2xsZWN0aW9uLm1hcENvbmRpdGlvbkdyb3Vwcyhcblx0XHRcdFx0XHRcdGNvbmRpdGlvbnNbIGN1cnJlbnRBY3Rpb25UeXBlIF0gfHwgW11cblx0XHRcdFx0XHQpXG5cdFx0XHRcdFx0LCB7IHBhcnNlOiB0cnVlIH1cblx0XHRcdFx0KTtcblxuXHRcdFx0XHQvLyBBbmQgdGhlbiByZS1yZW5kZXIgZXZlcnl0aGluZy5cblx0XHRcdFx0Y29uZGl0aW9uc1ZpZXcucmVuZGVyKCk7XG5cblx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0JGVsLmFwcGVuZCggY29uZGl0aW9uc1ZpZXcucmVuZGVyKCkuJGVsICk7XG5cblx0XHRcdFx0YXBwZW5kZWQgPSB0cnVlO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9LFxuXG5cdGdldERhdGFUeXBlRnJvbUFyZzogZnVuY3Rpb24gKCBhcmcgKSB7XG5cblx0XHR2YXIgYXJnVHlwZSA9IGFyZy5nZXQoICdfdHlwZScgKTtcblxuXHRcdHN3aXRjaCAoIGFyZ1R5cGUgKSB7XG5cblx0XHRcdGNhc2UgJ2F0dHInOlxuXHRcdFx0XHRyZXR1cm4gYXJnLmdldCggJ2RhdGFfdHlwZScgKTtcblxuXHRcdFx0Y2FzZSAnYXJyYXknOlxuXHRcdFx0XHRyZXR1cm4gJ2VudGl0eV9hcnJheSc7XG5cblx0XHRcdGRlZmF1bHQ6XG5cdFx0XHRcdHJldHVybiBhcmdUeXBlO1xuXHRcdH1cblx0fSxcblxuXHR2YWxpZGF0ZVJlYWN0aW9uOiBmdW5jdGlvbiAoIG1vZGVsLCBhdHRyaWJ1dGVzLCBlcnJvcnMsIG9wdGlvbnMgKSB7XG5cblx0XHQvLyBodHRwczovL2dpdGh1Yi5jb20vV29yZFBvaW50cy93b3JkcG9pbnRzL2lzc3Vlcy81MTkuXG5cdFx0aWYgKCAhIG9wdGlvbnMucmF3QXR0cy5jb25kaXRpb25zICkge1xuXHRcdFx0ZGVsZXRlIGF0dHJpYnV0ZXMuY29uZGl0aW9ucztcblx0XHRcdGRlbGV0ZSBtb2RlbC5hdHRyaWJ1dGVzLmNvbmRpdGlvbnM7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGhpcy52YWxpZGF0ZUNvbmRpdGlvbnMoIG1vZGVsLmNvbmRpdGlvbnMsIGF0dHJpYnV0ZXMuY29uZGl0aW9ucywgZXJyb3JzICk7XG5cdH0sXG5cblx0dmFsaWRhdGVDb25kaXRpb25zOiBmdW5jdGlvbiAoIGNvbmRpdGlvbnMsIHNldHRpbmdzLCBlcnJvcnMgKSB7XG5cblx0XHRfLmVhY2goIGNvbmRpdGlvbnMsIGZ1bmN0aW9uICggZ3JvdXBzICkge1xuXHRcdFx0Z3JvdXBzLmVhY2goIGZ1bmN0aW9uICggZ3JvdXAgKSB7XG5cdFx0XHRcdGdyb3VwLmdldCggJ2NvbmRpdGlvbnMnICkuZWFjaCggZnVuY3Rpb24gKCBjb25kaXRpb24gKSB7XG5cblx0XHRcdFx0XHR2YXIgbmV3RXJyb3JzID0gW10sXG5cdFx0XHRcdFx0XHRoaWVyYXJjaHkgPSBjb25kaXRpb24uZ2V0SGllcmFyY2h5KCkuY29uY2F0KFxuXHRcdFx0XHRcdFx0XHRbICdfY29uZGl0aW9ucycsIGNvbmRpdGlvbi5pZCBdXG5cdFx0XHRcdFx0XHQpO1xuXG5cdFx0XHRcdFx0aWYgKCBncm91cHMuaGllcmFyY2h5Lmxlbmd0aCA9PT0gMSApIHtcblx0XHRcdFx0XHRcdGhpZXJhcmNoeS51bnNoaWZ0KCBncm91cHMuaGllcmFyY2h5WzBdICk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y29uZGl0aW9uLnZhbGlkYXRlKFxuXHRcdFx0XHRcdFx0Z2V0RGVlcCggc2V0dGluZ3MsIGhpZXJhcmNoeSApXG5cdFx0XHRcdFx0XHQsIHt9XG5cdFx0XHRcdFx0XHQsIG5ld0Vycm9yc1xuXHRcdFx0XHRcdCk7XG5cblx0XHRcdFx0XHRpZiAoICEgXy5pc0VtcHR5KCBuZXdFcnJvcnMgKSApIHtcblxuXHRcdFx0XHRcdFx0aGllcmFyY2h5LnVuc2hpZnQoICdjb25kaXRpb25zJyApO1xuXHRcdFx0XHRcdFx0aGllcmFyY2h5LnB1c2goICdzZXR0aW5ncycgKTtcblxuXHRcdFx0XHRcdFx0Zm9yICggdmFyIGkgPSAwOyBpIDwgbmV3RXJyb3JzLmxlbmd0aDsgaSsrICkge1xuXG5cdFx0XHRcdFx0XHRcdG5ld0Vycm9yc1sgaSBdLmZpZWxkID0gaGllcmFyY2h5LmNvbmNhdChcblx0XHRcdFx0XHRcdFx0XHRfLmlzQXJyYXkoIG5ld0Vycm9yc1sgaSBdLmZpZWxkIClcblx0XHRcdFx0XHRcdFx0XHRcdD8gbmV3RXJyb3JzWyBpIF0uZmllbGRcblx0XHRcdFx0XHRcdFx0XHRcdDogWyBuZXdFcnJvcnNbIGkgXS5maWVsZCBdXG5cdFx0XHRcdFx0XHRcdCk7XG5cblx0XHRcdFx0XHRcdFx0ZXJyb3JzLnB1c2goIG5ld0Vycm9yc1sgaSBdICk7XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9KTtcblx0XHRcdH0pO1xuXHRcdH0pO1xuXHR9LFxuXG5cdGdldFR5cGU6IGZ1bmN0aW9uICggZGF0YVR5cGUsIHNsdWcgKSB7XG5cblx0XHRpZiAoIHR5cGVvZiB0aGlzLmRhdGEuY29uZGl0aW9uc1sgZGF0YVR5cGUgXSA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0aWYgKCB0eXBlb2YgdGhpcy5kYXRhLmNvbmRpdGlvbnNbIGRhdGFUeXBlIF1bIHNsdWcgXSA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHRoaXMuZGF0YS5jb25kaXRpb25zWyBkYXRhVHlwZSBdWyBzbHVnIF07XG5cdH0sXG5cblx0Ly8gR2V0IGFsbCBjb25kaXRpb25zIGZvciBhIGNlcnRhaW4gZGF0YSB0eXBlLlxuXHRnZXRCeURhdGFUeXBlOiBmdW5jdGlvbiAoIGRhdGFUeXBlICkge1xuXG5cdFx0cmV0dXJuIHRoaXMuZGF0YS5jb25kaXRpb25zWyBkYXRhVHlwZSBdO1xuXHR9LFxuXG5cdGdldENvbnRyb2xsZXI6IGZ1bmN0aW9uICggZGF0YVR5cGVTbHVnLCBzbHVnICkge1xuXG5cdFx0dmFyIGRhdGFUeXBlID0gdGhpcy5jb250cm9sbGVycy5nZXQoIGRhdGFUeXBlU2x1ZyApLFxuXHRcdFx0Y29udHJvbGxlcjtcblxuXHRcdGlmICggZGF0YVR5cGUgKSB7XG5cdFx0XHRjb250cm9sbGVyID0gZGF0YVR5cGUuZ2V0KCAnY29udHJvbGxlcnMnIClbIHNsdWcgXTtcblx0XHR9XG5cblx0XHRpZiAoICEgY29udHJvbGxlciApIHtcblx0XHRcdGNvbnRyb2xsZXIgPSBDb25kaXRpb25zLkNvbmRpdGlvbjtcblx0XHR9XG5cblx0XHR2YXIgdHlwZSA9IHRoaXMuZ2V0VHlwZSggZGF0YVR5cGVTbHVnLCBzbHVnICk7XG5cblx0XHRpZiAoICEgdHlwZSApIHtcblx0XHRcdHR5cGUgPSB7IHNsdWc6IHNsdWcgfTtcblx0XHR9XG5cblx0XHRyZXR1cm4gbmV3IGNvbnRyb2xsZXIoIHR5cGUgKTtcblx0fSxcblxuXHRyZWdpc3RlckNvbnRyb2xsZXI6IGZ1bmN0aW9uICggZGF0YVR5cGVTbHVnLCBzbHVnLCBjb250cm9sbGVyICkge1xuXG5cdFx0dmFyIGRhdGFUeXBlID0gdGhpcy5jb250cm9sbGVycy5nZXQoIGRhdGFUeXBlU2x1ZyApO1xuXG5cdFx0aWYgKCAhIGRhdGFUeXBlICkge1xuXHRcdFx0ZGF0YVR5cGUgPSBuZXcgdGhpcy5kYXRhVHlwZSh7XG5cdFx0XHRcdHNsdWc6IGRhdGFUeXBlU2x1Zyxcblx0XHRcdFx0Y29udHJvbGxlcnM6IHt9XG5cdFx0XHR9KTtcblxuXHRcdFx0dGhpcy5jb250cm9sbGVycy5hZGQoIGRhdGFUeXBlICk7XG5cdFx0fVxuXG5cdFx0ZGF0YVR5cGUuZ2V0KCAnY29udHJvbGxlcnMnIClbIHNsdWcgXSA9IGNvbnRyb2xsZXI7XG5cdH1cblxufSApO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbnM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uR3JvdXBcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Db2xsZWN0aW9uXG4gKi9cbnZhciBDb25kaXRpb25zID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25zLFxuXHRDb25kaXRpb25Hcm91cDtcblxuLy8gVGhpcyBpcyBhIG1vZGVsIGFsdGhvdWdoIHdlIG9yaWdpbmFsbHkgdGhvdWdodCBpdCBvdWdodCB0byBiZSBhIGNvbGxlY3Rpb24sXG4vLyBiZWNhdXNlIEJhY2tib25lIGRvZXNuJ3Qgc3VwcG9ydCBzdWItY29sbGVjdGlvbnMuIFRoaXMgaXMgdGhlIGNsb3Nlc3QgdGhpbmdcbi8vIHRvIGEgc3ViLWNvbGxlY3Rpb24uIFNlZSBodHRwczovL3N0YWNrb3ZlcmZsb3cuY29tL3EvMTAzODgxOTkvMTkyNDEyOC5cbkNvbmRpdGlvbkdyb3VwID0gQmFja2JvbmUuTW9kZWwuZXh0ZW5kKHtcblxuXHRkZWZhdWx0czogZnVuY3Rpb24gKCkge1xuXHRcdHJldHVybiB7XG5cdFx0XHRpZDogJycsXG5cdFx0XHRoaWVyYXJjaHk6IFtdLFxuXHRcdFx0Y29uZGl0aW9uczogbmV3IENvbmRpdGlvbnMoKSxcblx0XHRcdGdyb3VwczogbnVsbCxcblx0XHRcdHJlYWN0aW9uOiBudWxsXG5cdFx0fTtcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIGF0dHJpYnV0ZXMgKSB7XG5cblx0XHQvLyBTZXQgdXAgZXZlbnQgcHJveHlpbmcuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5hdHRyaWJ1dGVzLmNvbmRpdGlvbnMsICdhbGwnLCB0aGlzLnRyaWdnZXIgKTtcblxuXHRcdC8vIEFkZCB0aGUgY29uZGl0aW9ucyB0byB0aGUgY29sbGVjdGlvbi5cblx0XHRpZiAoIGF0dHJpYnV0ZXMuX2NvbmRpdGlvbnMgKSB7XG5cdFx0XHR0aGlzLnJlc2V0KCBhdHRyaWJ1dGVzLl9jb25kaXRpb25zICk7XG5cdFx0fVxuXHR9LFxuXG5cdC8vIE1ha2Ugc3VyZSB0aGF0IHRoZSBtb2RlbCBpZHMgYXJlIHByb3Blcmx5IHNldC4gQ29uZGl0aW9ucyBhcmUgaWRlbnRpZmllZFxuXHQvLyBieSB0aGUgaW5kZXggb2YgdGhlIGFycmF5IGluIHdoaWNoIHRoZXkgYXJlIHN0b3JlZC4gV2UgY29weSB0aGUga2V5cyB0b1xuXHQvLyB0aGUgaWQgYXR0cmlidXRlcyBvZiB0aGUgbW9kZWxzLlxuXHRyZXNldDogZnVuY3Rpb24gKCBtb2RlbHMsIG9wdGlvbnMgKSB7XG5cblx0XHRvcHRpb25zID0gb3B0aW9ucyB8fCB7fTtcblx0XHRvcHRpb25zLmdyb3VwID0gdGhpcztcblxuXHRcdHZhciBjb25kaXRpb25zID0gdGhpcy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0dGhpcy5zZXRJZHMoIG1vZGVscywgMCApO1xuXG5cdFx0cmV0dXJuIGNvbmRpdGlvbnMucmVzZXQuY2FsbCggY29uZGl0aW9ucywgbW9kZWxzLCBvcHRpb25zICk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzdW1tYXJ5IFVwZGF0ZSB0aGUgY29uZGl0aW9ucyBjb2xsZWN0aW9uLlxuXHQgKlxuXHQgKiBUaGlzIGlzIGEgd3JhcHBlciBmb3IgdGhlIGBzZXQoKWAgbWV0aG9kIG9mIHRoZSBjb2xsZWN0aW9uIHN0b3JlZCBpbiB0aGVcblx0ICogYGNvbmRpdGlvbnNgIGF0dHJpYnV0ZSBvZiB0aGlzIE1vZGVsLiBJdCBlbnN1cmVzIHRoYXQgdGhlIHBhc3NlZCBtb2RlbFxuXHQgKiBvYmplY3RzIGhhdmUgYmVlbiBnaXZlbiBwcm9wZXIgSURzLCBhbmQgc2V0cyBvcHRpb25zLmdyb3VwIHRvIHRoaXMgb2JqZWN0LlxuXHQgKlxuXHQgKiBOb3RlIHRoYXQgdGhlIGBfY29uZGl0aW9uc2AgYXR0cmlidXRlIGl0c2VsZiBpcyBub3QgbW9kaWZpZWQsIG9ubHkgdGhlXG5cdCAqIGNvbGxlY3Rpb24gdGhhdCBpcyBzdG9yZWQgaW4gdGhlIGBjb25kaXRpb25zYCBhdHRyaWJ1dGUuXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjEuM1xuXHQgKlxuXHQgKiBAcGFyYW0ge09iamVjdFtdfSBtb2RlbHMgICAgICAgICAgICAgICAgICAgIC0gVGhlIGNvbmRpdGlvbnMuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSAgIFtvcHRpb25zPXsgZ3JvdXA6IHRoaXMgfV0gLSBPcHRpb25zIHRvIHBhc3MgdG9cblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGBDb25kaXRpb25zLnNldCgpYC4gVGhlIGBncm91cGBcblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHdpbGwgYWx3YXlzIGJlIHNldCB0byBgdGhpc2AuXG5cdCAqXG5cdCAqIEByZXR1cm5zIHtPYmplY3RbXX0gVGhlIGFkZGVkIG1vZGVscy5cblx0ICovXG5cdHNldENvbmRpdGlvbnM6IGZ1bmN0aW9uICggbW9kZWxzLCBvcHRpb25zICkge1xuXG5cdFx0b3B0aW9ucyA9IG9wdGlvbnMgfHwge307XG5cdFx0b3B0aW9ucy5ncm91cCA9IHRoaXM7XG5cblx0XHR2YXIgY29uZGl0aW9ucyA9IHRoaXMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdHRoaXMuc2V0SWRzKCBtb2RlbHMsIDAgKTtcblxuXHRcdHJldHVybiBjb25kaXRpb25zLnNldC5jYWxsKCBjb25kaXRpb25zLCBtb2RlbHMsIG9wdGlvbnMgKTtcblx0fSxcblxuXHRhZGQ6IGZ1bmN0aW9uICggbW9kZWxzLCBvcHRpb25zICkge1xuXG5cdFx0b3B0aW9ucyA9IG9wdGlvbnMgfHwge307XG5cdFx0b3B0aW9ucy5ncm91cCA9IHRoaXM7XG5cblx0XHR2YXIgY29uZGl0aW9ucyA9IHRoaXMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdHRoaXMuc2V0SWRzKCBtb2RlbHMsIHRoaXMuZ2V0TmV4dElkKCkgKTtcblxuXHRcdHJldHVybiBjb25kaXRpb25zLmFkZC5jYWxsKCBjb25kaXRpb25zLCBtb2RlbHMsIG9wdGlvbnMgKTtcblx0fSxcblxuXHRnZXROZXh0SWQ6IGZ1bmN0aW9uKCkge1xuXG5cdFx0dmFyIGNvbmRpdGlvbnMgPSB0aGlzLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cblx0XHRpZiAoICFjb25kaXRpb25zLmxlbmd0aCApIHtcblx0XHRcdHJldHVybiAwO1xuXHRcdH1cblxuXHRcdHJldHVybiBwYXJzZUludCggY29uZGl0aW9ucy5zb3J0KCkubGFzdCgpLmdldCggJ2lkJyApLCAxMCApICsgMTtcblx0fSxcblxuXHRzZXRJZHM6IGZ1bmN0aW9uICggbW9kZWxzLCBzdGFydElkICkge1xuXG5cdFx0aWYgKCAhIG1vZGVscyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRfLmVhY2goIF8uaXNBcnJheSggbW9kZWxzICkgPyBtb2RlbHMgOiBbIG1vZGVscyBdLCBmdW5jdGlvbiAoIG1vZGVsLCBpZCApIHtcblxuXHRcdFx0aWYgKCBzdGFydElkICE9PSAwICkge1xuXHRcdFx0XHRtb2RlbC5pZCA9IHN0YXJ0SWQrKztcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdG1vZGVsLmlkID0gaWQ7XG5cdFx0XHR9XG5cblx0XHRcdC8vIFRoaXMgd2lsbCBiZSBzZXQgd2hlbiBhbiBvYmplY3QgaXMgY29udmVydGVkIHRvIGEgbW9kZWwsIGJ1dCBpZiBpdCBpc1xuXHRcdFx0Ly8gYSBtb2RlbCBhbHJlYWR5LCB3ZSBuZWVkIHRvIHNldCBpdCBoZXJlLlxuXHRcdFx0aWYgKCBtb2RlbCBpbnN0YW5jZW9mIEJhY2tib25lLk1vZGVsICkge1xuXHRcdFx0XHRtb2RlbC5ncm91cCA9IHRoaXM7XG5cdFx0XHR9XG5cblx0XHR9LCB0aGlzICk7XG5cdH0sXG5cblx0c3luYzogZnVuY3Rpb24gKCBtZXRob2QsIGNvbGxlY3Rpb24sIG9wdGlvbnMgKSB7XG5cdFx0b3B0aW9ucy5lcnJvcihcblx0XHRcdHsgbWVzc2FnZTogJ0ZldGNoaW5nIGFuZCBzYXZpbmcgZ3JvdXBzIG9mIGhvb2sgY29uZGl0aW9ucyBpcyBub3Qgc3VwcG9ydGVkLicgfVxuXHRcdCk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbkdyb3VwO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvbkdyb3Vwc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLkNvbGxlY3Rpb25cbiAqL1xudmFyIENvbmRpdGlvbkdyb3VwID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25Hcm91cCxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0Q29uZGl0aW9uR3JvdXBzO1xuXG4vKipcbiAqIE9iamVjdCBmb3JtYXQgZm9yIG1vZGVscyBleHBlY3RlZCBieSB0aGlzIGNvbGxlY3Rpb24uXG4gKlxuICogQHR5cGVkZWYge09iamVjdH0gUmF3Q29uZGl0aW9uR3JvdXBcbiAqXG4gKiBAcHJvcGVydHkge3N0cmluZ30gICAgICAgICAgaWQgICAgICAgICAgLSBUaGUgSUQgb2YgdGhlIGdyb3VwLlxuICogQHByb3BlcnR5IHtBcnJheX0gICAgICAgICAgIGhpZXJhcmNoeSAgIC0gVGhlIGhpZXJhcmNoeSBmb3IgdGhlIGdyb3VwLlxuICogQHByb3BlcnR5IHtDb25kaXRpb25Hcm91cHN9IGdyb3VwcyAgICAgIC0gVGhlIGNvbGxlY3Rpb24gZm9yIHRoZSBncm91cC5cbiAqIEBwcm9wZXJ0eSB7QXJyYXl9ICAgICAgICAgICBfY29uZGl0aW9ucyAtIFRoZSBjb25kaXRpb25zIGluIHRoZSBncm91cC5cbiAqL1xuXG5Db25kaXRpb25Hcm91cHMgPSBCYWNrYm9uZS5Db2xsZWN0aW9uLmV4dGVuZCh7XG5cblx0bW9kZWw6IENvbmRpdGlvbkdyb3VwLFxuXG5cdGhpZXJhcmNoeTogW10sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBtb2RlbHMsIG9wdGlvbnMgKSB7XG5cblx0XHRpZiAoIG9wdGlvbnMuYXJncyApIHtcblx0XHRcdHRoaXMuYXJncyA9IG9wdGlvbnMuYXJncztcblx0XHR9XG5cblx0XHRpZiAoIG9wdGlvbnMuaGllcmFyY2h5ICkge1xuXHRcdFx0dGhpcy5oaWVyYXJjaHkgPSBvcHRpb25zLmhpZXJhcmNoeTtcblx0XHR9XG5cblx0XHRpZiAoIG9wdGlvbnMucmVhY3Rpb24gKSB7XG5cdFx0XHR0aGlzLnJlYWN0aW9uID0gb3B0aW9ucy5yZWFjdGlvbjtcblx0XHR9XG5cblx0XHRpZiAoIG9wdGlvbnMuX2NvbmRpdGlvbnMgKSB7XG5cdFx0XHR0aGlzLm1hcENvbmRpdGlvbnMoIG9wdGlvbnMuX2NvbmRpdGlvbnMgKTtcblx0XHR9XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzdW1tYXJ5IENvbnZlcnRzIGEgY29uZGl0aW9ucyBoaWVyYXJjaHkgaW50byBhbiBhcnJheSBvZiBjb25kaXRpb24gZ3JvdXBzLlxuXHQgKlxuXHQgKiBUaGUgY29uZGl0aW9ucywgYXMgc2F2ZWQgaW4gdGhlIGRhdGFiYXNlLCBhcmUgaW4gYSBuZXN0ZWQgaGllcmFyY2h5IGJhc2VkIG9uXG5cdCAqIHdoaWNoIChzdWIpYXJncyB0aGV5IGFyZSBmb3IuIFRoZXJlZm9yZSBpdCBpcyBuZWNlc3NhcnkgdG8gcGFyc2UgdGhlIGhpZXJhcmNoeVxuXHQgKiBpbnRvIGEgc2ltcGxlIGFycmF5IGNvbnRhaW5pbmcgdGhlIGNvbmRpdGlvbiBpbmZvcm1hdGlvbiBhbmQgdGhlIGFyZyBoaWVyYXJjaHlcblx0ICogZm9yIGl0LlxuXHQgKlxuXHQgKiBAc2luY2UgMi4xLjNcblx0ICpcblx0ICogQHBhcmFtIHtPYmplY3R9ICAgICAgICAgICAgICBjb25kaXRpb25zICAgICAtIFRoZSBjb25kaXRpb25zIGhpZXJhcmNoeS5cblx0ICogQHBhcmFtIHtSYXdDb25kaXRpb25Hcm91cFtdfSBbZ3JvdXBzPVtdXSAgICAtIFRoZSBhcnJheSBvZiBjb25kaXRpb24gZ3JvdXBzLlxuXHQgKiBAcGFyYW0ge0FycmF5fSAgICAgICAgICAgICAgIFtoaWVyYXJjaHk9W11dIC0gVGhlIGN1cnJlbnQgbG9jYXRpb24gd2l0aGluIHRoZVxuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uZGl0aW9ucyBoaWVyYXJjaHkuXG5cdCAqXG5cdCAqIEByZXR1cm5zIHtSYXdDb25kaXRpb25Hcm91cFtdfSBUaGUgcGFyc2VkIGdyb3VwcyBpbiB0aGUgZm9ybWF0IGZvciBtb2RlbHNcblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGV4cGVjdGVkIGJ5IHRoaXMgY29sbGVjdGlvbi5cblx0ICovXG5cdG1hcENvbmRpdGlvbkdyb3VwczogZnVuY3Rpb24gKCBjb25kaXRpb25zLCBncm91cHMsIGhpZXJhcmNoeSApIHtcblxuXHRcdGhpZXJhcmNoeSA9IGhpZXJhcmNoeSB8fCBbXTtcblx0XHRncm91cHMgPSBncm91cHMgfHwgW107XG5cblx0XHRfLmVhY2goIGNvbmRpdGlvbnMsIGZ1bmN0aW9uICggYXJnLCBzbHVnICkge1xuXG5cdFx0XHRpZiAoIHNsdWcgPT09ICdfY29uZGl0aW9ucycgKSB7XG5cblx0XHRcdFx0Z3JvdXBzLnB1c2goIHtcblx0XHRcdFx0XHRpZDogICAgICAgICAgdGhpcy5nZXRJZEZyb21IaWVyYXJjaHkoIGhpZXJhcmNoeSApLFxuXHRcdFx0XHRcdGhpZXJhcmNoeTogICBfLmNsb25lKCBoaWVyYXJjaHkgKSxcblx0XHRcdFx0XHRncm91cHM6ICAgICAgdGhpcyxcblx0XHRcdFx0XHRfY29uZGl0aW9uczogXy50b0FycmF5KCBhcmcgKVxuXHRcdFx0XHR9ICk7XG5cblx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0aGllcmFyY2h5LnB1c2goIHNsdWcgKTtcblxuXHRcdFx0XHR0aGlzLm1hcENvbmRpdGlvbkdyb3VwcyggYXJnLCBncm91cHMsIGhpZXJhcmNoeSApO1xuXG5cdFx0XHRcdGhpZXJhcmNoeS5wb3AoKTtcblx0XHRcdH1cblxuXHRcdH0sIHRoaXMgKTtcblxuXHRcdHJldHVybiBncm91cHM7XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzdW1tYXJ5IFBhcnNlcyBhIGNvbmRpdGlvbnMgaGllcmFyY2h5IGFuZCBhZGRzIGVhY2ggZ3JvdXAgdG8gdGhlIGNvbGxlY3Rpb24uXG5cdCAqXG5cdCAqIEBzaW5jZSAyLjEuMFxuXHQgKiBAc2luY2UgMi4xLjMgVGhlIGhpZXJhcmNoeSBhcmcgd2FzIGRlcHJlY2F0ZWQuXG5cdCAqXG5cdCAqIEBwYXJhbSB7QXJyYXl9IGNvbmRpdGlvbnMgIC0gVGhlIHJhdyBjb25kaXRpb25zIGhpZXJhcmNoeSB0byBwYXJzZS5cblx0ICogQHBhcmFtIHtBcnJheX0gW2hpZXJhcmNoeV0gLSBEZXByZWNhdGVkLiBQcmV2aW91c2x5IHVzZWQgdG8gdHJhY2sgdGhlIGN1cnJlbnRcblx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsb2NhdGlvbiB3aXRoaW4gdGhlIGNvbmRpdGlvbnMgaGllcmFyY2h5LlxuXHQgKi9cblx0bWFwQ29uZGl0aW9uczogZnVuY3Rpb24gKCBjb25kaXRpb25zLCBoaWVyYXJjaHkgKSB7XG5cblx0XHR2YXIgZ3JvdXBzID0gdGhpcy5tYXBDb25kaXRpb25Hcm91cHMoIGNvbmRpdGlvbnMsIFtdLCBoaWVyYXJjaHkgKTtcblxuXHRcdHRoaXMucmVzZXQoIGdyb3VwcyApO1xuXHR9LFxuXG5cdGdldElkRnJvbUhpZXJhcmNoeTogZnVuY3Rpb24gKCBoaWVyYXJjaHkgKSB7XG5cdFx0cmV0dXJuIGhpZXJhcmNoeS5qb2luKCAnLicgKTtcblx0fSxcblxuXHRnZXRBcmdzOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgYXJncyA9IHRoaXMuYXJncztcblxuXHRcdGlmICggISBhcmdzICkge1xuXHRcdFx0YXJncyA9IEFyZ3MuZ2V0RXZlbnRBcmdzKCB0aGlzLnJlYWN0aW9uLmdldCggJ2V2ZW50JyApICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGFyZ3M7XG5cdH0sXG5cblx0LyoqXG5cdCAqIEBzdW1tYXJ5IFBhcnNlcyBhIHJhdyB2YWx1ZSBpbnRvIGEgbGlzdCBvZiBtb2RlbHMuXG5cdCAqXG5cdCAqIEltcGxlbWVudGVkIGhlcmUgc28gaWYgdGhlIG1vZGVscyBhcmUgZ29pbmcgdG8gYmUgbWVyZ2VkIHdpdGggY29ycmVzcG9uZGluZ1xuXHQgKiBvbmVzIGluIHRoZSBleGlzdGluZyBjb2xsZWN0aW9uLCB3ZSBjYW4gZ28gYWhlYWQgYW5kIHVwZGF0ZSB0aGUgYGNvbmRpdGlvbnNgXG5cdCAqIGNvbGxlY3Rpb24gb2YgdGhlIGV4aXN0aW5nIG1vZGVscyBiYXNlZCBvbiB0aGVpciBwYXNzZWQgaW4gYF9jb25kaXRpb25zYFxuXHQgKiBhdHRyaWJ1dGUuIE90aGVyd2lzZSB0aGUgY29uZGl0aW9ucyBjb2xsZWN0aW9uIHdvdWxkIG5vdCBiZSB1cGRhdGVkLiBTZWUgW3RoZVxuXHQgKiBkaXNjdXNzaW9uIG9uIEdpdEh1Yl17QGxpbmsgaHR0cHM6Ly9naXRodWIuY29tL1dvcmRQb2ludHMvd29yZHBvaW50cy9pc3N1ZXMvXG4gICAgICogNTE3I2lzc3VlY29tbWVudC0yNTAzMDcxNDd9IGZvciBtb3JlIGluZm9ybWF0aW9uIG9uIHdoeSB3ZSBkbyBpdCB0aGlzIHdheS5cblx0ICpcblx0ICogQHNpbmNlIDIuMS4zXG5cdCAqXG5cdCAqIEBwYXJhbSB7T2JqZWN0fE9iamVjdFtdfSByZXNwICAgIC0gVGhlIHJhdyBtb2RlbChzKS5cblx0ICogQHBhcmFtIHtPYmplY3R9ICAgICAgICAgIG9wdGlvbnMgLSBPcHRpb25zIHBhc3NlZCBmcm9tIGBzZXQoKWAuXG5cdCAqXG5cdCAqIEByZXR1cm5zIHtPYmplY3R8T2JqZWN0W119IFRoZSBjb25kaXRpb24gbW9kZWxzLCB3aXRoIGBjb25kaXRpb25zYCBwcm9wZXJ0eVxuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZXQgYXMgbmVlZGVkLlxuXHQgKi9cblx0cGFyc2U6IGZ1bmN0aW9uICggcmVzcCwgb3B0aW9ucyApIHtcblxuXHRcdGlmICggISBvcHRpb25zLm1lcmdlICkge1xuXHRcdFx0cmV0dXJuIHJlc3A7XG5cdFx0fVxuXG5cdFx0dmFyIG1vZGVscyA9IF8uaXNBcnJheSggcmVzcCApID8gcmVzcCA6IFtyZXNwXSxcblx0XHRcdG1vZGVsO1xuXG5cdFx0Zm9yICggdmFyIGkgPSAwOyBpIDwgbW9kZWxzLmxlbmd0aDsgaSsrICkge1xuXG5cdFx0XHRtb2RlbCA9IHRoaXMuZ2V0KCBtb2RlbHNbIGkgXS5pZCApO1xuXG5cdFx0XHRpZiAoICEgbW9kZWwgKSB7XG5cdFx0XHRcdGNvbnRpbnVlO1xuXHRcdFx0fVxuXG5cdFx0XHRtb2RlbC5zZXRDb25kaXRpb25zKCBtb2RlbHNbIGkgXS5fY29uZGl0aW9ucywgb3B0aW9ucyApO1xuXG5cdFx0XHRtb2RlbHNbIGkgXS5jb25kaXRpb25zID0gbW9kZWwuZ2V0KCAnY29uZGl0aW9ucycgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gcmVzcDtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uR3JvdXBzO1xuIiwiLyoqXG4gKiB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvblR5cGVcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQmFzZSxcblx0Q29uZGl0aW9uVHlwZTtcblxuQ29uZGl0aW9uVHlwZSA9IEJhc2UuZXh0ZW5kKHtcblx0aWRBdHRyaWJ1dGU6ICdzbHVnJ1xufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uVHlwZTtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLkNvbGxlY3Rpb25cbiAqL1xudmFyIENvbmRpdGlvblR5cGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLm1vZGVsLkNvbmRpdGlvblR5cGUsXG5cdENvbmRpdGlvblR5cGVzO1xuXG5Db25kaXRpb25UeXBlcyA9IEJhY2tib25lLkNvbGxlY3Rpb24uZXh0ZW5kKHtcblxuXHRtb2RlbDogQ29uZGl0aW9uVHlwZVxuXG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25UeXBlcztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25cbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5Nb2RlbFxuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQmFzZSxcblx0QXJncyA9IHdwLndvcmRwb2ludHMuaG9va3MuQXJncyxcblx0RXh0ZW5zaW9ucyA9IHdwLndvcmRwb2ludHMuaG9va3MuRXh0ZW5zaW9ucyxcblx0RmllbGRzID0gd3Aud29yZHBvaW50cy5ob29rcy5GaWVsZHMsXG5cdENvbmRpdGlvbjtcblxuQ29uZGl0aW9uID0gQmFzZS5leHRlbmQoe1xuXG5cdGRlZmF1bHRzOiB7XG5cdFx0dHlwZTogJycsXG5cdFx0c2V0dGluZ3M6IFtdXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCBhdHRyaWJ1dGVzLCBvcHRpb25zICkge1xuXHRcdGlmICggb3B0aW9ucy5ncm91cCApIHtcblx0XHRcdHRoaXMuZ3JvdXAgPSBvcHRpb25zLmdyb3VwO1xuXHRcdH1cblx0fSxcblxuXHR2YWxpZGF0ZTogZnVuY3Rpb24gKCBhdHRyaWJ1dGVzLCBvcHRpb25zLCBlcnJvcnMgKSB7XG5cblx0XHRlcnJvcnMgPSBlcnJvcnMgfHwgW107XG5cblx0XHR2YXIgY29uZGl0aW9uVHlwZSA9IHRoaXMuZ2V0VHlwZSgpO1xuXG5cdFx0aWYgKCAhIGNvbmRpdGlvblR5cGUgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dmFyIGZpZWxkcyA9IGNvbmRpdGlvblR5cGUuZmllbGRzO1xuXG5cdFx0RmllbGRzLnZhbGlkYXRlKFxuXHRcdFx0ZmllbGRzXG5cdFx0XHQsIGF0dHJpYnV0ZXMuc2V0dGluZ3Ncblx0XHRcdCwgZXJyb3JzXG5cdFx0KTtcblxuXHRcdHZhciBjb250cm9sbGVyID0gdGhpcy5nZXRDb250cm9sbGVyKCk7XG5cblx0XHRpZiAoIGNvbnRyb2xsZXIgKSB7XG5cdFx0XHRjb250cm9sbGVyLnZhbGlkYXRlU2V0dGluZ3MoIHRoaXMsIGF0dHJpYnV0ZXMuc2V0dGluZ3MsIGVycm9ycyApO1xuXHRcdH1cblxuXHRcdHJldHVybiBlcnJvcnM7XG5cdH0sXG5cblx0Z2V0Q29udHJvbGxlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dmFyIGFyZyA9IHRoaXMuZ2V0QXJnKCk7XG5cblx0XHRpZiAoICEgYXJnICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdHZhciBDb25kaXRpb25zID0gRXh0ZW5zaW9ucy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0cmV0dXJuIENvbmRpdGlvbnMuZ2V0Q29udHJvbGxlcihcblx0XHRcdENvbmRpdGlvbnMuZ2V0RGF0YVR5cGVGcm9tQXJnKCBhcmcgKVxuXHRcdFx0LCB0aGlzLmdldCggJ3R5cGUnIClcblx0XHQpO1xuXHR9LFxuXG5cdGdldFR5cGU6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBhcmcgPSB0aGlzLmdldEFyZygpO1xuXG5cdFx0aWYgKCAhIGFyZyApIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cblx0XHR2YXIgQ29uZGl0aW9ucyA9IEV4dGVuc2lvbnMuZ2V0KCAnY29uZGl0aW9ucycgKTtcblxuXHRcdHJldHVybiBDb25kaXRpb25zLmdldFR5cGUoXG5cdFx0XHRDb25kaXRpb25zLmdldERhdGFUeXBlRnJvbUFyZyggYXJnIClcblx0XHRcdCwgdGhpcy5nZXQoICd0eXBlJyApXG5cdFx0KTtcblx0fSxcblxuXHRnZXRBcmc6IGZ1bmN0aW9uICgpIHtcblxuXHRcdGlmICggISB0aGlzLmFyZyApIHtcblxuXHRcdFx0dmFyIGFyZ3MgPSBBcmdzLmdldEFyZ3NGcm9tSGllcmFyY2h5KFxuXHRcdFx0XHR0aGlzLmdldEhpZXJhcmNoeSgpXG5cdFx0XHRcdCwgdGhpcy5yZWFjdGlvbi5nZXQoICdldmVudCcgKVxuXHRcdFx0KTtcblxuXHRcdFx0aWYgKCBhcmdzICkge1xuXHRcdFx0XHR0aGlzLmFyZyA9IGFyZ3NbIGFyZ3MubGVuZ3RoIC0gMSBdO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHJldHVybiB0aGlzLmFyZztcblx0fSxcblxuXHRnZXRIaWVyYXJjaHk6IGZ1bmN0aW9uICgpIHtcblx0XHRyZXR1cm4gdGhpcy5ncm91cC5nZXQoICdoaWVyYXJjaHknICk7XG5cdH0sXG5cblx0Z2V0RnVsbEhpZXJhcmNoeTogZnVuY3Rpb24gKCkge1xuXG5cdFx0cmV0dXJuIHRoaXMuZ3JvdXAuZ2V0KCAnZ3JvdXBzJyApLmhpZXJhcmNoeS5jb25jYXQoXG5cdFx0XHR0aGlzLmdldEhpZXJhcmNoeSgpXG5cdFx0KTtcblx0fSxcblxuXHRpc05ldzogZnVuY3Rpb24gKCkge1xuXHRcdHJldHVybiAndW5kZWZpbmVkJyA9PT0gdHlwZW9mIHRoaXMucmVhY3Rpb24uZ2V0KFxuXHRcdFx0WyAnY29uZGl0aW9ucycgXVxuXHRcdFx0XHQuY29uY2F0KCB0aGlzLmdldEZ1bGxIaWVyYXJjaHkoKSApXG5cdFx0XHRcdC5jb25jYXQoIFsgJ19jb25kaXRpb25zJywgdGhpcy5pZCBdIClcblx0XHQpO1xuXHR9LFxuXG5cdHN5bmM6IGZ1bmN0aW9uICggbWV0aG9kLCBtb2RlbCwgb3B0aW9ucyApIHtcblx0XHRvcHRpb25zLmVycm9yKFxuXHRcdFx0eyBtZXNzYWdlOiAnRmV0Y2hpbmcgYW5kIHNhdmluZyBob29rIGNvbmRpdGlvbnMgaXMgbm90IHN1cHBvcnRlZC4nIH1cblx0XHQpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb247XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uc1xuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLkNvbGxlY3Rpb25cbiAqL1xudmFyIENvbmRpdGlvbiA9IHdwLndvcmRwb2ludHMuaG9va3MubW9kZWwuQ29uZGl0aW9uLFxuXHRDb25kaXRpb25zO1xuXG5Db25kaXRpb25zID0gQmFja2JvbmUuQ29sbGVjdGlvbi5leHRlbmQoe1xuXG5cdC8vIFJlZmVyZW5jZSB0byB0aGlzIGNvbGxlY3Rpb24ncyBtb2RlbC5cblx0bW9kZWw6IENvbmRpdGlvbixcblxuXHRjb21wYXJhdG9yOiAnaWQnLFxuXG5cdHN5bmM6IGZ1bmN0aW9uICggbWV0aG9kLCBjb2xsZWN0aW9uLCBvcHRpb25zICkge1xuXHRcdG9wdGlvbnMuZXJyb3IoXG5cdFx0XHR7IG1lc3NhZ2U6ICdGZXRjaGluZyBhbmQgc2F2aW5nIGhvb2sgY29uZGl0aW9ucyBpcyBub3Qgc3VwcG9ydGVkLicgfVxuXHRcdCk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbnM7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cFxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHRDb25kaXRpb24gPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uLFxuXHRBcmdzID0gd3Aud29yZHBvaW50cy5ob29rcy5BcmdzLFxuXHQkID0gQmFja2JvbmUuJCxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHRDb25kaXRpb25Hcm91cDtcblxuQ29uZGl0aW9uR3JvdXAgPSBCYXNlLmV4dGVuZCh7XG5cblx0Y2xhc3NOYW1lOiAnY29uZGl0aW9uLWdyb3VwJyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLXJlYWN0aW9uLWNvbmRpdGlvbi1ncm91cCcgKSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAnYWRkJywgdGhpcy5hZGRPbmUgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLm1vZGVsLCAncmVzZXQnLCB0aGlzLnJlbmRlciApO1xuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMubW9kZWwsICdyZW1vdmUnLCB0aGlzLm1heWJlSGlkZSApO1xuXG5cdFx0dGhpcy5tb2RlbC5vbiggJ2FkZCcsIHRoaXMucmVhY3Rpb24ubG9ja09wZW4sIHRoaXMucmVhY3Rpb24gKTtcblx0XHR0aGlzLm1vZGVsLm9uKCAncmVtb3ZlJywgdGhpcy5yZWFjdGlvbi5sb2NrT3BlbiwgdGhpcy5yZWFjdGlvbiApO1xuXHRcdHRoaXMubW9kZWwub24oICdyZXNldCcsIHRoaXMucmVhY3Rpb24ubG9ja09wZW4sIHRoaXMucmVhY3Rpb24gKTtcblx0fSxcblxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGVsLmh0bWwoIHRoaXMudGVtcGxhdGUoKSApO1xuXG5cdFx0dGhpcy5tYXliZUhpZGUoKTtcblxuXHRcdHRoaXMuJCggJy5jb25kaXRpb24tZ3JvdXAtdGl0bGUnICkudGV4dChcblx0XHRcdEFyZ3MuYnVpbGRIaWVyYXJjaHlIdW1hbklkKFxuXHRcdFx0XHRBcmdzLmdldEFyZ3NGcm9tSGllcmFyY2h5KFxuXHRcdFx0XHRcdHRoaXMubW9kZWwuZ2V0KCAnaGllcmFyY2h5JyApXG5cdFx0XHRcdFx0LCB0aGlzLnJlYWN0aW9uLm1vZGVsLmdldCggJ2V2ZW50JyApXG5cdFx0XHRcdClcblx0XHRcdClcblx0XHQpO1xuXG5cdFx0dGhpcy5hZGRBbGwoKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdGFkZE9uZTogZnVuY3Rpb24gKCBjb25kaXRpb24gKSB7XG5cblx0XHRjb25kaXRpb24ucmVhY3Rpb24gPSB0aGlzLnJlYWN0aW9uLm1vZGVsO1xuXG5cdFx0dmFyIHZpZXcgPSBuZXcgQ29uZGl0aW9uKCB7XG5cdFx0XHRlbDogJCggJzxkaXYgY2xhc3M9XCJjb25kaXRpb25cIj48L2Rpdj4nICksXG5cdFx0XHRtb2RlbDogY29uZGl0aW9uLFxuXHRcdFx0cmVhY3Rpb246IHRoaXMucmVhY3Rpb25cblx0XHR9ICk7XG5cblx0XHR2YXIgJHZpZXcgPSB2aWV3LnJlbmRlcigpLiRlbDtcblxuXHRcdHRoaXMuJGVsLmFwcGVuZCggJHZpZXcgKS5zaG93KCk7XG5cblx0XHRpZiAoIGNvbmRpdGlvbi5pc05ldygpICkge1xuXHRcdFx0JHZpZXcuZmluZCggJzppbnB1dDp2aXNpYmxlOmVxKCAxICknICkuZm9jdXMoKTtcblx0XHR9XG5cblx0XHR0aGlzLmxpc3RlblRvKCBjb25kaXRpb24sICdkZXN0cm95JywgZnVuY3Rpb24gKCkge1xuXHRcdFx0dGhpcy5tb2RlbC5nZXQoICdjb25kaXRpb25zJyApLnJlbW92ZSggY29uZGl0aW9uLmlkICk7XG5cdFx0fSApO1xuXHR9LFxuXG5cdGFkZEFsbDogZnVuY3Rpb24gKCkge1xuXHRcdHRoaXMubW9kZWwuZ2V0KCAnY29uZGl0aW9ucycgKS5lYWNoKCB0aGlzLmFkZE9uZSwgdGhpcyApO1xuXHR9LFxuXG5cdC8vIEhpZGUgdGhlIGdyb3VwIHdoZW4gaXQgaXMgZW1wdHkuXG5cdG1heWJlSGlkZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0aWYgKCAwID09PSB0aGlzLm1vZGVsLmdldCggJ2NvbmRpdGlvbnMnICkubGVuZ3RoICkge1xuXHRcdFx0dGhpcy4kZWwuaGlkZSgpO1xuXHRcdH1cblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uR3JvdXA7XG4iLCIvKipcbiAqIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5Db25kaXRpb25Hcm91cHNcbiAqXG4gKiBAY2xhc3NcbiAqIEBhdWdtZW50cyBCYWNrYm9uZS5WaWV3XG4gKiBAYXVnbWVudHMgd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2VcbiAqL1xudmFyIEJhc2UgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZSxcblx0Q29uZGl0aW9uR3JvdXBWaWV3ID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvbkdyb3VwLFxuXHRBcmdIaWVyYXJjaHlTZWxlY3RvciA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5BcmdIaWVyYXJjaHlTZWxlY3Rvcixcblx0Q29uZGl0aW9uU2VsZWN0b3IgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQ29uZGl0aW9uU2VsZWN0b3IsXG5cdEV4dGVuc2lvbnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkV4dGVuc2lvbnMsXG5cdEFyZ3MgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkFyZ3MsXG5cdHRlbXBsYXRlID0gd3Aud29yZHBvaW50cy5ob29rcy50ZW1wbGF0ZSxcblx0JGNhY2hlID0gd3Aud29yZHBvaW50cy4kY2FjaGUsXG5cdENvbmRpdGlvbkdyb3VwcztcblxuQ29uZGl0aW9uR3JvdXBzID0gQmFzZS5leHRlbmQoe1xuXG5cdG5hbWVzcGFjZTogJ2NvbmRpdGlvbi1ncm91cHMnLFxuXG5cdGNsYXNzTmFtZTogJ2NvbmRpdGlvbnMnLFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stY29uZGl0aW9uLWdyb3VwcycgKSxcblxuXHRldmVudHM6IHtcblx0XHQnY2xpY2sgPiAuY29uZGl0aW9ucy10aXRsZSAuYWRkLW5ldyc6ICAgICAgICAgICAnc2hvd0FyZ1NlbGVjdG9yJyxcblx0XHQnY2xpY2sgPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5jb25maXJtLWFkZC1uZXcnOiAnbWF5YmVBZGROZXcnLFxuXHRcdCdjbGljayA+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmNhbmNlbC1hZGQtbmV3JzogICdjYW5jZWxBZGROZXcnXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5Db25kaXRpb25zID0gRXh0ZW5zaW9ucy5nZXQoICdjb25kaXRpb25zJyApO1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5jb2xsZWN0aW9uLCAnYWRkJywgdGhpcy5hZGRPbmUgKTtcblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLmNvbGxlY3Rpb24sICdyZXNldCcsIHRoaXMucmVuZGVyICk7XG5cblx0XHR0aGlzLmxpc3RlblRvKCB0aGlzLnJlYWN0aW9uLCAnY2FuY2VsJywgdGhpcy5jYW5jZWxBZGROZXcgKTtcblxuXHRcdHRoaXMuY29sbGVjdGlvbi5vbiggJ3VwZGF0ZScsIHRoaXMucmVhY3Rpb24ubG9ja09wZW4sIHRoaXMucmVhY3Rpb24gKTtcblx0XHR0aGlzLmNvbGxlY3Rpb24ub24oICdyZXNldCcsIHRoaXMucmVhY3Rpb24ubG9ja09wZW4sIHRoaXMucmVhY3Rpb24gKTtcblx0fSxcblxuXHRyZW5kZXI6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGVsLmh0bWwoIHRoaXMudGVtcGxhdGUoKSApO1xuXG5cdFx0dGhpcy4kYyA9ICRjYWNoZS5jYWxsKCB0aGlzLCB0aGlzLiQgKTtcblxuXHRcdHRoaXMuYWRkQWxsKCk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXInLCB0aGlzICk7XG5cblx0XHQvLyBTZWUgaHR0cHM6Ly9naXRodWIuY29tL1dvcmRQb2ludHMvd29yZHBvaW50cy9pc3N1ZXMvNTIwLlxuXHRcdGlmICggdGhpcy5BcmdTZWxlY3RvciApIHtcblxuXHRcdFx0dGhpcy4kKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5hcmctc2VsZWN0b3JzJyApLnJlcGxhY2VXaXRoKFxuXHRcdFx0XHR0aGlzLkFyZ1NlbGVjdG9yLiRlbFxuXHRcdFx0KTtcblxuXHRcdFx0dGhpcy4kKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5jb25kaXRpb24tc2VsZWN0b3InICkucmVwbGFjZVdpdGgoXG5cdFx0XHRcdHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IuJGVsXG5cdFx0XHQpO1xuXG5cdFx0XHR0aGlzLkFyZ1NlbGVjdG9yLmRlbGVnYXRlRXZlbnRzKCk7XG5cdFx0XHR0aGlzLkNvbmRpdGlvblNlbGVjdG9yLmRlbGVnYXRlRXZlbnRzKCk7XG5cdFx0XHR0aGlzLkNvbmRpdGlvblNlbGVjdG9yLnRyaWdnZXJDaGFuZ2UoKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHRhZGRBbGw6IGZ1bmN0aW9uICgpIHtcblx0XHR0aGlzLmNvbGxlY3Rpb24uZWFjaCggdGhpcy5hZGRPbmUsIHRoaXMgKTtcblx0fSxcblxuXHRhZGRPbmU6IGZ1bmN0aW9uICggQ29uZGl0aW9uR3JvdXAgKSB7XG5cblx0XHR2YXIgdmlldyA9IG5ldyBDb25kaXRpb25Hcm91cFZpZXcoe1xuXHRcdFx0bW9kZWw6IENvbmRpdGlvbkdyb3VwLFxuXHRcdFx0cmVhY3Rpb246IHRoaXMucmVhY3Rpb25cblx0XHR9KTtcblxuXHRcdHRoaXMuJGMoICc+IC5jb25kaXRpb24tZ3JvdXBzJyApLmFwcGVuZCggdmlldy5yZW5kZXIoKS4kZWwgKTtcblx0fSxcblxuXHRzaG93QXJnU2VsZWN0b3I6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHRoaXMuJGMoICc+IC5jb25kaXRpb25zLXRpdGxlIC5hZGQtbmV3JyApLmF0dHIoICdkaXNhYmxlZCcsIHRydWUgKTtcblxuXHRcdGlmICggdHlwZW9mIHRoaXMuQXJnU2VsZWN0b3IgPT09ICd1bmRlZmluZWQnICkge1xuXG5cdFx0XHR2YXIgYXJncyA9IHRoaXMuY29sbGVjdGlvbi5nZXRBcmdzKCk7XG5cdFx0XHR2YXIgQ29uZGl0aW9ucyA9IHRoaXMuQ29uZGl0aW9ucztcblx0XHRcdHZhciBpc0VudGl0eUFycmF5ID0gKCB0aGlzLmNvbGxlY3Rpb24uaGllcmFyY2h5LnNsaWNlKCAtMiApLnRvU3RyaW5nKCkgPT09ICdzZXR0aW5ncyxjb25kaXRpb25zJyApO1xuXHRcdFx0dmFyIGhhc0NvbmRpdGlvbnMgPSBmdW5jdGlvbiAoIGFyZyApIHtcblxuXHRcdFx0XHR2YXIgZGF0YVR5cGUgPSBDb25kaXRpb25zLmdldERhdGFUeXBlRnJvbUFyZyggYXJnICk7XG5cblx0XHRcdFx0Ly8gV2UgZG9uJ3QgYWxsb3cgaWRlbnRpdHkgY29uZGl0aW9ucyBvbiB0b3AtbGV2ZWwgZW50aXRpZXMuXG5cdFx0XHRcdGlmIChcblx0XHRcdFx0XHQhIGlzRW50aXR5QXJyYXlcblx0XHRcdFx0XHQmJiBkYXRhVHlwZSA9PT0gJ2VudGl0eSdcblx0XHRcdFx0XHQmJiBfLmlzRW1wdHkoIGFyZy5oaWVyYXJjaHkgKVxuXHRcdFx0XHQpIHtcblx0XHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHR2YXIgY29uZGl0aW9ucyA9IENvbmRpdGlvbnMuZ2V0QnlEYXRhVHlwZSggZGF0YVR5cGUgKTtcblxuXHRcdFx0XHRyZXR1cm4gISBfLmlzRW1wdHkoIGNvbmRpdGlvbnMgKTtcblx0XHRcdH07XG5cblx0XHRcdHZhciBoaWVyYXJjaGllcyA9IEFyZ3MuZ2V0SGllcmFyY2hpZXNNYXRjaGluZyhcblx0XHRcdFx0eyB0b3A6IGFyZ3MubW9kZWxzLCBlbmQ6IGhhc0NvbmRpdGlvbnMgfVxuXHRcdFx0KTtcblxuXHRcdFx0aWYgKCBfLmlzRW1wdHkoIGhpZXJhcmNoaWVzICkgKSB7XG5cblx0XHRcdFx0dGhpcy4kYyggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAubm8tY29uZGl0aW9ucycgKS5zaG93KCk7XG5cblx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0dGhpcy5BcmdTZWxlY3RvciA9IG5ldyBBcmdIaWVyYXJjaHlTZWxlY3Rvcih7XG5cdFx0XHRcdFx0aGllcmFyY2hpZXM6IGhpZXJhcmNoaWVzLFxuXHRcdFx0XHRcdGVsOiB0aGlzLiQoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0gLmFyZy1zZWxlY3RvcnMnIClcblx0XHRcdFx0fSk7XG5cblx0XHRcdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5BcmdTZWxlY3RvciwgJ2NoYW5nZScsIHRoaXMubWF5YmVTaG93Q29uZGl0aW9uU2VsZWN0b3IgKTtcblxuXHRcdFx0XHR0aGlzLkFyZ1NlbGVjdG9yLnJlbmRlcigpO1xuXG5cdFx0XHRcdHRoaXMuQXJnU2VsZWN0b3IuJHNlbGVjdC5jaGFuZ2UoKTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtJyApLnNsaWRlRG93bigpO1xuXHR9LFxuXG5cdGdldEFyZ1R5cGU6IGZ1bmN0aW9uICggYXJnICkge1xuXG5cdFx0dmFyIGFyZ1R5cGU7XG5cblx0XHRpZiAoICEgYXJnIHx8ICEgYXJnLmdldCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRhcmdUeXBlID0gdGhpcy5Db25kaXRpb25zLmdldERhdGFUeXBlRnJvbUFyZyggYXJnICk7XG5cblx0XHQvLyBXZSBjb21wcmVzcyByZWxhdGlvbnNoaXBzIHRvIGF2b2lkIHJlZHVuZGFuY3kuXG5cdFx0aWYgKCAncmVsYXRpb25zaGlwJyA9PT0gYXJnVHlwZSApIHtcblx0XHRcdGFyZ1R5cGUgPSB0aGlzLmdldEFyZ1R5cGUoIGFyZy5nZXRDaGlsZCggYXJnLmdldCggJ3NlY29uZGFyeScgKSApICk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIGFyZ1R5cGU7XG5cdH0sXG5cblx0bWF5YmVTaG93Q29uZGl0aW9uU2VsZWN0b3I6IGZ1bmN0aW9uICggYXJnU2VsZWN0b3JzLCBhcmcgKSB7XG5cblx0XHR2YXIgYXJnVHlwZSA9IHRoaXMuZ2V0QXJnVHlwZSggYXJnICk7XG5cblx0XHRpZiAoICEgYXJnVHlwZSApIHtcblx0XHRcdGlmICggdGhpcy4kY29uZGl0aW9uU2VsZWN0b3IgKSB7XG5cdFx0XHRcdHRoaXMuJGNvbmRpdGlvblNlbGVjdG9yLmhpZGUoKTtcblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHZhciBjb25kaXRpb25zID0gdGhpcy5Db25kaXRpb25zLmdldEJ5RGF0YVR5cGUoIGFyZ1R5cGUgKTtcblxuXHRcdGlmICggISB0aGlzLkNvbmRpdGlvblNlbGVjdG9yICkge1xuXG5cdFx0XHR0aGlzLkNvbmRpdGlvblNlbGVjdG9yID0gbmV3IENvbmRpdGlvblNlbGVjdG9yKHtcblx0XHRcdFx0ZWw6IHRoaXMuJCggJz4gLmFkZC1jb25kaXRpb24tZm9ybSAuY29uZGl0aW9uLXNlbGVjdG9yJyApXG5cdFx0XHR9KTtcblxuXHRcdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5Db25kaXRpb25TZWxlY3RvciwgJ2NoYW5nZScsIHRoaXMuY29uZGl0aW9uU2VsZWN0aW9uQ2hhbmdlICk7XG5cblx0XHRcdHRoaXMuJGNvbmRpdGlvblNlbGVjdG9yID0gdGhpcy5Db25kaXRpb25TZWxlY3Rvci4kZWw7XG5cdFx0fVxuXG5cdFx0dGhpcy5Db25kaXRpb25TZWxlY3Rvci5jb2xsZWN0aW9uLnJlc2V0KCBfLnRvQXJyYXkoIGNvbmRpdGlvbnMgKSApO1xuXG5cdFx0dGhpcy4kY29uZGl0aW9uU2VsZWN0b3Iuc2hvdygpLmZpbmQoICdzZWxlY3QnICkuY2hhbmdlKCk7XG5cdH0sXG5cblx0Y2FuY2VsQWRkTmV3OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtJyApLnNsaWRlVXAoKTtcblx0XHR0aGlzLiRjKCAnPiAuY29uZGl0aW9ucy10aXRsZSAuYWRkLW5ldycgKS5hdHRyKCAnZGlzYWJsZWQnLCBmYWxzZSApO1xuXHR9LFxuXG5cdGNvbmRpdGlvblNlbGVjdGlvbkNoYW5nZTogZnVuY3Rpb24gKCBzZWxlY3RvciwgdmFsdWUgKSB7XG5cblx0XHR0aGlzLiRjKCAnPiAuYWRkLWNvbmRpdGlvbi1mb3JtIC5jb25maXJtLWFkZC1uZXcnIClcblx0XHRcdC5hdHRyKCAnZGlzYWJsZWQnLCAhIHZhbHVlICk7XG5cdH0sXG5cblx0bWF5YmVBZGROZXc6IGZ1bmN0aW9uICgpIHtcblxuXHRcdHZhciBzZWxlY3RlZCA9IHRoaXMuQ29uZGl0aW9uU2VsZWN0b3IuZ2V0U2VsZWN0ZWQoKTtcblxuXHRcdGlmICggISBzZWxlY3RlZCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR2YXIgaGllcmFyY2h5ID0gdGhpcy5BcmdTZWxlY3Rvci5nZXRIaWVyYXJjaHkoKSxcblx0XHRcdGlkID0gdGhpcy5jb2xsZWN0aW9uLmdldElkRnJvbUhpZXJhcmNoeSggaGllcmFyY2h5ICksXG5cdFx0XHRDb25kaXRpb25Hcm91cCA9IHRoaXMuY29sbGVjdGlvbi5nZXQoIGlkICk7XG5cblx0XHRpZiAoICEgQ29uZGl0aW9uR3JvdXAgKSB7XG5cdFx0XHRDb25kaXRpb25Hcm91cCA9IHRoaXMuY29sbGVjdGlvbi5hZGQoe1xuXHRcdFx0XHRpZDogaWQsXG5cdFx0XHRcdGhpZXJhcmNoeTogaGllcmFyY2h5LFxuXHRcdFx0XHRncm91cHM6IHRoaXMuY29sbGVjdGlvblxuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0Q29uZGl0aW9uR3JvdXAuYWRkKCB7IHR5cGU6IHNlbGVjdGVkIH0gKTtcblxuXHRcdHRoaXMuJGMoICc+IC5hZGQtY29uZGl0aW9uLWZvcm0nICkuaGlkZSgpO1xuXHRcdHRoaXMuJGMoICc+IC5jb25kaXRpb25zLXRpdGxlIC5hZGQtbmV3JyApLmF0dHIoICdkaXNhYmxlZCcsIGZhbHNlICk7XG5cdH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cyA9IENvbmRpdGlvbkdyb3VwcztcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvblNlbGVjdG9yXG4gKlxuICogQGNsYXNzXG4gKiBAYXVnbWVudHMgQmFja2JvbmUuVmlld1xuICogQGF1Z21lbnRzIHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlXG4gKi9cbnZhciBCYXNlID0gd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkJhc2UsXG5cdENvbmRpdGlvblR5cGVzID0gd3Aud29yZHBvaW50cy5ob29rcy5tb2RlbC5Db25kaXRpb25UeXBlcyxcblx0dGVtcGxhdGUgPSB3cC53b3JkcG9pbnRzLmhvb2tzLnRlbXBsYXRlLFxuXHRDb25kaXRpb25TZWxlY3RvcjtcblxuQ29uZGl0aW9uU2VsZWN0b3IgPSBCYXNlLmV4dGVuZCh7XG5cblx0bmFtZXNwYWNlOiAnY29uZGl0aW9uLXNlbGVjdG9yJyxcblxuXHR0ZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWNvbmRpdGlvbi1zZWxlY3RvcicgKSxcblxuXHRvcHRpb25UZW1wbGF0ZTogdGVtcGxhdGUoICdob29rLWFyZy1vcHRpb24nICksXG5cblx0ZXZlbnRzOiB7XG5cdFx0J2NoYW5nZSBzZWxlY3QnOiAndHJpZ2dlckNoYW5nZSdcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbiAoIG9wdGlvbnMgKSB7XG5cblx0XHR0aGlzLmxhYmVsID0gb3B0aW9ucy5sYWJlbDtcblxuXHRcdGlmICggISB0aGlzLmNvbGxlY3Rpb24gKSB7XG5cdFx0XHR0aGlzLmNvbGxlY3Rpb24gPSBuZXcgQ29uZGl0aW9uVHlwZXMoeyBjb21wYXJhdG9yOiAndGl0bGUnIH0pO1xuXHRcdH1cblxuXHRcdHRoaXMubGlzdGVuVG8oIHRoaXMuY29sbGVjdGlvbiwgJ3VwZGF0ZScsIHRoaXMucmVuZGVyICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5jb2xsZWN0aW9uLCAncmVzZXQnLCB0aGlzLnJlbmRlciApO1xuXHR9LFxuXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbChcblx0XHRcdHRoaXMudGVtcGxhdGUoXG5cdFx0XHRcdHsgbGFiZWw6IHRoaXMubGFiZWwsIG5hbWU6IHRoaXMuY2lkICsgJ19jb25kaXRpb25fc2VsZWN0b3InIH1cblx0XHRcdClcblx0XHQpO1xuXG5cdFx0dGhpcy4kc2VsZWN0ID0gdGhpcy4kKCAnc2VsZWN0JyApO1xuXG5cdFx0dGhpcy5jb2xsZWN0aW9uLmVhY2goIGZ1bmN0aW9uICggY29uZGl0aW9uICkge1xuXG5cdFx0XHR0aGlzLiRzZWxlY3QuYXBwZW5kKCB0aGlzLm9wdGlvblRlbXBsYXRlKCBjb25kaXRpb24uYXR0cmlidXRlcyApICk7XG5cblx0XHR9LCB0aGlzICk7XG5cblx0XHR0aGlzLnRyaWdnZXIoICdyZW5kZXInLCB0aGlzICk7XG5cblx0XHRyZXR1cm4gdGhpcztcblx0fSxcblxuXHR0cmlnZ2VyQ2hhbmdlOiBmdW5jdGlvbiAoIGV2ZW50ICkge1xuXG5cdFx0dGhpcy50cmlnZ2VyKCAnY2hhbmdlJywgdGhpcywgdGhpcy5nZXRTZWxlY3RlZCgpLCBldmVudCApO1xuXHR9LFxuXG5cdGdldFNlbGVjdGVkOiBmdW5jdGlvbiAoKSB7XG5cblx0XHRyZXR1cm4gdGhpcy4kc2VsZWN0LnZhbCgpO1xuXHR9XG59KTtcblxubW9kdWxlLmV4cG9ydHMgPSBDb25kaXRpb25TZWxlY3RvcjtcbiIsIi8qKlxuICogd3Aud29yZHBvaW50cy5ob29rcy52aWV3LkNvbmRpdGlvblxuICpcbiAqIEBjbGFzc1xuICogQGF1Z21lbnRzIEJhY2tib25lLlZpZXdcbiAqIEBhdWdtZW50cyB3cC53b3JkcG9pbnRzLmhvb2tzLnZpZXcuQmFzZVxuICovXG52YXIgQmFzZSA9IHdwLndvcmRwb2ludHMuaG9va3Mudmlldy5CYXNlLFxuXHR0ZW1wbGF0ZSA9IHdwLndvcmRwb2ludHMuaG9va3MudGVtcGxhdGUsXG5cdEV4dGVuc2lvbnMgPSB3cC53b3JkcG9pbnRzLmhvb2tzLkV4dGVuc2lvbnMsXG5cdEZpZWxkcyA9IHdwLndvcmRwb2ludHMuaG9va3MuRmllbGRzLFxuXHRDb25kaXRpb247XG5cbkNvbmRpdGlvbiA9IEJhc2UuZXh0ZW5kKHtcblxuXHRuYW1lc3BhY2U6ICdjb25kaXRpb24nLFxuXG5cdGNsYXNzTmFtZTogJ3dvcmRwb2ludHMtaG9vay1jb25kaXRpb24nLFxuXG5cdHRlbXBsYXRlOiB0ZW1wbGF0ZSggJ2hvb2stcmVhY3Rpb24tY29uZGl0aW9uJyApLFxuXG5cdGV2ZW50czoge1xuXHRcdCdjbGljayAuZGVsZXRlJzogJ2Rlc3Ryb3knXG5cdH0sXG5cblx0aW5pdGlhbGl6ZTogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2NoYW5nZScsIHRoaXMucmVuZGVyICk7XG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2Rlc3Ryb3knLCB0aGlzLnJlbW92ZSApO1xuXG5cdFx0dGhpcy5saXN0ZW5UbyggdGhpcy5tb2RlbCwgJ2ludmFsaWQnLCB0aGlzLm1vZGVsLnJlYWN0aW9uLnNob3dFcnJvciApO1xuXG5cdFx0dGhpcy5leHRlbnNpb24gPSBFeHRlbnNpb25zLmdldCggJ2NvbmRpdGlvbnMnICk7XG5cdH0sXG5cblx0Ly8gRGlzcGxheSB0aGUgY29uZGl0aW9uIHNldHRpbmdzIGZvcm0uXG5cdHJlbmRlcjogZnVuY3Rpb24gKCkge1xuXG5cdFx0dGhpcy4kZWwuaHRtbCggdGhpcy50ZW1wbGF0ZSgpICk7XG5cblx0XHR0aGlzLiR0aXRsZSA9IHRoaXMuJCggJy5jb25kaXRpb24tdGl0bGUnICk7XG5cdFx0dGhpcy4kc2V0dGluZ3MgPSB0aGlzLiQoICcuY29uZGl0aW9uLXNldHRpbmdzJyApO1xuXG5cdFx0dGhpcy5yZW5kZXJUaXRsZSgpO1xuXHRcdHRoaXMucmVuZGVyU2V0dGluZ3MoKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcicsIHRoaXMgKTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9LFxuXG5cdHJlbmRlclRpdGxlOiBmdW5jdGlvbiAoKSB7XG5cblx0XHR2YXIgY29uZGl0aW9uVHlwZSA9IHRoaXMubW9kZWwuZ2V0VHlwZSgpO1xuXG5cdFx0aWYgKCBjb25kaXRpb25UeXBlICkge1xuXHRcdFx0dGhpcy4kdGl0bGUudGV4dCggY29uZGl0aW9uVHlwZS50aXRsZSApO1xuXHRcdH1cblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjp0aXRsZScsIHRoaXMgKTtcblx0fSxcblxuXHRyZW5kZXJTZXR0aW5nczogZnVuY3Rpb24gKCkge1xuXG5cdFx0Ly8gQnVpbGQgdGhlIGZpZWxkcyBiYXNlZCBvbiB0aGUgY29uZGl0aW9uIHR5cGUuXG5cdFx0dmFyIGNvbmRpdGlvblR5cGUgPSB0aGlzLm1vZGVsLmdldFR5cGUoKSxcblx0XHRcdGZpZWxkcyA9ICcnO1xuXG5cdFx0dmFyIGZpZWxkTmFtZVByZWZpeCA9IF8uY2xvbmUoIHRoaXMubW9kZWwuZ2V0RnVsbEhpZXJhcmNoeSgpICk7XG5cdFx0ZmllbGROYW1lUHJlZml4LnVuc2hpZnQoICdjb25kaXRpb25zJyApO1xuXHRcdGZpZWxkTmFtZVByZWZpeC5wdXNoKFxuXHRcdFx0J19jb25kaXRpb25zJ1xuXHRcdFx0LCB0aGlzLm1vZGVsLmdldCggJ2lkJyApXG5cdFx0XHQsICdzZXR0aW5ncydcblx0XHQpO1xuXG5cdFx0dmFyIGZpZWxkTmFtZSA9IF8uY2xvbmUoIGZpZWxkTmFtZVByZWZpeCApO1xuXG5cdFx0ZmllbGROYW1lLnBvcCgpO1xuXHRcdGZpZWxkTmFtZS5wdXNoKCAndHlwZScgKTtcblxuXHRcdGZpZWxkcyArPSBGaWVsZHMuY3JlYXRlKFxuXHRcdFx0ZmllbGROYW1lXG5cdFx0XHQsIHRoaXMubW9kZWwuZ2V0KCAndHlwZScgKVxuXHRcdFx0LCB7IHR5cGU6ICdoaWRkZW4nIH1cblx0XHQpO1xuXG5cdFx0aWYgKCBjb25kaXRpb25UeXBlICkge1xuXHRcdFx0dmFyIGNvbnRyb2xsZXIgPSB0aGlzLmV4dGVuc2lvbi5nZXRDb250cm9sbGVyKFxuXHRcdFx0XHRjb25kaXRpb25UeXBlLmRhdGFfdHlwZVxuXHRcdFx0XHQsIGNvbmRpdGlvblR5cGUuc2x1Z1xuXHRcdFx0KTtcblxuXHRcdFx0aWYgKCBjb250cm9sbGVyICkge1xuXHRcdFx0XHRmaWVsZHMgKz0gY29udHJvbGxlci5yZW5kZXJTZXR0aW5ncyggdGhpcywgZmllbGROYW1lUHJlZml4ICk7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0dGhpcy4kc2V0dGluZ3MuYXBwZW5kKCBmaWVsZHMgKTtcblxuXHRcdHRoaXMudHJpZ2dlciggJ3JlbmRlcjpzZXR0aW5ncycsIHRoaXMgKTtcblx0fSxcblxuXHQvLyBSZW1vdmUgdGhlIGl0ZW0sIGRlc3Ryb3kgdGhlIG1vZGVsLlxuXHRkZXN0cm95OiBmdW5jdGlvbiAoKSB7XG5cblx0XHR0aGlzLm1vZGVsLmRlc3Ryb3koKTtcblx0fVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gQ29uZGl0aW9uO1xuIl19
