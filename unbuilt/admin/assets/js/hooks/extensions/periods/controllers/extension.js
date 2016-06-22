/**
 * wp.wordpoints.hooks.extension.Periods
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.controller.Extension
 */
var Extension = wp.wordpoints.hooks.controller.Extension,
	Fields = wp.wordpoints.hooks.Fields,
	Args = wp.wordpoints.hooks.Args,
	template = wp.wordpoints.hooks.template,
	$ = Backbone.$,
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

			var $periods = $( '<div></div>' ).html( this.template() );

			var name = [ 'periods', currentActionType, 0, 'length' ];

			var label = reaction.Reactor.get( 'periods_label' );

			if ( ! label ) {
				label = this.data.l10n.label;
			}

			$periods.find( '.periods' ).html(
				Fields.create(
					name
					, reaction.model.get( name )
					, {
						type: 'select',
						options: this.data.periods,
						label: label
					}
				)
			);

			$el.append( $periods.html() );
		});
	},

	showForReaction: function ( reaction ) {
		return Args.isEventRepeatable( reaction.model.get( 'event' ) );
	}

} );

module.exports = Periods;
