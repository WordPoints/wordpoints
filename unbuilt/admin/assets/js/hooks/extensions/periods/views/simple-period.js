/**
 * wp.wordpoints.hooks.view.SimplePeriod
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	template = wp.wordpoints.hooks.template,
	Fields = wp.wordpoints.hooks.Fields,
	SimplePeriod;

SimplePeriod = Base.extend({

	namespace: 'period',

	className: 'wordpoints-hook-period',

	template: template( 'hook-reaction-simple-period' ),

	events: {
		'change .wordpoints-hook-period-sync': 'sync',
		'input .wordpoints-hook-period-sync': 'sync'
	},

	initialize: function ( options ) {

		this.extension = options.extension;
		this.reaction = options.reaction;
		this.actionType = options.actionType;
	},

	// Display the period settings form.
	render: function () {

		var name = [ this.extension.get( 'slug' ), this.actionType, 0, 'length' ];
		var periodLength = this.reaction.model.get( name );

		// Which units should this period be displayed in (second, minutes, &c.)?
		var unit, units;

		units = _.keys( this.extension.data.period_units ).sort( function ( a, b ) {
			return b - a; // We don't have to worry about NaN, etc.
		});

		for ( var i = 0; i < units.length; i++ ) {

			unit = units[ i ];

			if ( 0 === periodLength % unit ) {
				break;
			}
		}

		var template = this.template( {
			name: Fields.getFieldName( name ),
			length: periodLength,
			length_in_units: periodLength / unit,
			length_in_units_label: this.getPeriodLabel()
		} );

		template = Fields.createSelect(
			{ value: unit, options: this.extension.data.period_units }
			, template
		);

		this.$el.html( template );

		this.$length = this.$( '.wordpoints-hook-period-length' );
		this.$lengthInUnits = this.$( '.wordpoints-hook-period-length-in-units' );
		this.$units = this.$( '.wordpoints-hook-period-units' );

		this.trigger( 'render', this );

		return this;
	},

	getPeriodLabel: function () {

		var label = this.reaction.Reactor.get( 'periods_label' );

		if ( !label ) {
			label = this.extension.data.l10n.label;
		}

		return label;
	},

	sync: function () {
		this.$length.val( this.$lengthInUnits.val() * this.$units.val() );
	}

});

module.exports = SimplePeriod;
