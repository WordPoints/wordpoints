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
