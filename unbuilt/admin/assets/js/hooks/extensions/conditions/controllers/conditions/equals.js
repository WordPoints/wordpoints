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
