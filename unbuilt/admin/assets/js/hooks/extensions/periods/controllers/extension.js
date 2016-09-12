/**
 * wp.wordpoints.hooks.extension.Periods
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	SimplePeriod = wp.wordpoints.hooks.view.SimplePeriod,
	Args = wp.wordpoints.hooks.Args,
	template = wp.wordpoints.hooks.template,
	Periods;

Periods = Extension.extend({

	defaults: {
		slug: 'periods'
	},

	template: template( 'hook-periods' ),

	initReaction: function ( reaction ) {

		if ( ! this.showForReaction( reaction ) ) {
			return;
		}

		this.listenTo( reaction, 'render:fields', function ( $el, currentActionType ) {

			$el.append( this.template() );

			var simplePeriod = new SimplePeriod( {
				extension: this,
				reaction: reaction,
				actionType: currentActionType
			} );

			$el.find( '.periods' ).append( simplePeriod.render().$el );
		});
	},

	showForReaction: function ( reaction ) {
		return Args.isEventRepeatable( reaction.model.get( 'event' ) );
	}

} );

module.exports = Periods;
