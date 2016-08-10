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
