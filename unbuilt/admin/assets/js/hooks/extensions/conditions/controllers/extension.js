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
