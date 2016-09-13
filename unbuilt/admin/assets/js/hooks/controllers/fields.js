/**
 * wp.wordpoints.hooks.controller.Fields
 *
 * @since 2.1.0
 *
 * @class
 * @augments Backbone.Model
 */
var $ = Backbone.$,
	hooks = wp.wordpoints.hooks,
	l10n = wp.wordpoints.hooks.view.l10n,
	template = wp.wordpoints.hooks.template,
	textTemplate = wp.wordpoints.hooks.textTemplate,
	Fields;

Fields = Backbone.Model.extend({

	defaults: {
		fields: {}
	},

	template: template( 'hook-reaction-field' ),
	templateHidden: template( 'hook-reaction-hidden-field' ),
	templateSelect: template( 'hook-reaction-select-field' ),

	emptyMessage: textTemplate( l10n.emptyField ),

	initialize: function () {

		this.listenTo( hooks, 'reaction:model:validate', this.validateReaction );
		this.listenTo( hooks, 'reaction:view:init', this.initReaction );

		this.attributes.fields.event = {
			type: 'hidden',
			required: true
		};
	},

	create: function ( name, value, data ) {

		if ( typeof value === 'undefined' && data['default'] ) {
			value = data['default'];
		}

		data = _.extend(
			{ name: this.getFieldName( name ), value: value }
			, data
		);

		switch ( data.type ) {
			case 'select':
				return this.createSelect( data );

			case 'hidden':
				return this.templateHidden( data );
		}

		var DataType = DataTypes.get( data.type );

		if ( DataType ) {
			return DataType.createField( data );
		} else {
			return this.template( data );
		}
	},

	createSelect: function ( data ) {

		var $template = $( '<div></div>' ).html( this.templateSelect( data ) ),
			options = '',
			foundValue = typeof data.value === 'undefined'
				|| typeof data.options[ data.value ] !== 'undefined';

		_.each( data.options, function ( option, index ) {

			var value, label;

			if ( option.value ) {
				value = option.value;
				label = option.label;

				if ( ! foundValue && data.value === value ) {
					foundValue = true;
				}
			} else {
				value = index;
				label = option;
			}

			options += $( '<option></option>' )
				.attr( 'value', value )
				.text( label ? label : value )
				.prop( 'outerHTML' );
		});

		// If the current value isn't in the list, add it in.
		if ( ! foundValue ) {
			options += $( '<option></option>' )
				.attr( 'value', data.value )
				.text( data.value )
				.prop( 'outerHTML' );
		}

		$template.find( 'select' )
			.append( options )
			.val( data.value )
			.find( ':selected' )
				.attr( 'selected', true );

		return $template.html();
	},

	getFieldName: function ( field ) {

		if ( _.isArray( field ) ) {

			field = _.clone( field );

			if ( 1 === field.length ) {
				field = field.shift();
			} else {
				field = field.shift() + '[' + field.join( '][' ) + ']';
			}
		}

		return field;
	},

	getAttrSlug: function ( reaction, fieldName ) {

		var name = fieldName;

		var nameParts = [],
			firstBracket = name.indexOf( '[' );

		// If this isn't an array-syntax name, we don't need to process it.
		if ( -1 === firstBracket ) {
			return name;
		}

		// Usually the bracket will be proceeded by something: `array[...]`.
		if ( 0 !== firstBracket ) {
			nameParts.push( name.substring( 0, firstBracket ) );
			name = name.substring( firstBracket );
		}

		nameParts = nameParts.concat( name.slice( 1, -1 ).split( '][' ) );

		// If the last element is empty, it is a non-associative array: `a[]`
		if ( nameParts[ nameParts.length - 1 ] === '' ) {
			nameParts.pop();
		}

		return nameParts;
	},

	// Get the data from a form as key => value pairs.
	getFormData: function ( reaction, $form ) {

		var formObj = {},
			inputs = $form.find( ':input' ).serializeArray();

		_.each( inputs, function ( input ) {
			formObj[ input.name ] = input.value;
		} );

		return this.arrayify( formObj );
	},

	arrayify: function ( formData ) {

		var arrayData = {};

		_.each( formData, function ( value, name ) {

			var nameParts = [],
				data = arrayData,
				isArray = false,
				firstBracket = name.indexOf( '[' );

			// If this isn't an array-syntax name, we don't need to process it.
			if ( -1 === firstBracket ) {
				data[ name ] = value;
				return;
			}

			// Usually the bracket will be proceeded by something: `array[...]`.
			if ( 0 !== firstBracket ) {
				nameParts.push( name.substring( 0, firstBracket ) );
				name = name.substring( firstBracket );
			}

			nameParts = nameParts.concat( name.slice( 1, -1 ).split( '][' ) );

			// If the last element is empty, it is a non-associative array: `a[]`
			if ( nameParts[ nameParts.length - 1 ] === '' ) {
				isArray = true;
				nameParts.pop();
			}

			var key = nameParts.pop();

			// Construct the hierarchical object.
			_.each( nameParts, function ( part ) {
				data = data[ part ] = ( data[ part ] || {} );
			});

			// Set the value.
			if ( isArray ) {

				if ( typeof data[ key ] === 'undefined' ) {
					data[ key ] = [];
				}

				data[ key ].push( value );

			} else {
				data[ key ] = value;
			}
		});

		return arrayData;
	},

	validate: function ( fields, attributes, errors ) {

		_.each( fields, function ( field, slug ) {
			if (
				field.required
				&& (
					typeof attributes[ slug ] === 'undefined'
					|| '' === $.trim( attributes[ slug ] )
				)
			) {
				errors.push( {
					field: slug,
					message: this.emptyMessage( field )
				} );
			}
		}, this );
	},

	initReaction: function ( reaction ) {

		this.listenTo( reaction, 'render:settings', this.renderReaction );
	},

	renderReaction: function ( $el, currentActionType, reaction ) {

		var fieldsHTML = '';

		_.each( this.get( 'fields' ), function ( field, name ) {

			fieldsHTML += this.create(
				name,
				reaction.model.get( name ),
				field
			);

		}, this );

		$el.html( fieldsHTML );
	},

	validateReaction: function ( reaction, attributes, errors ) {

		this.validate( this.get( 'fields' ), attributes, errors );
	}
});

var DataType = Backbone.Model.extend({

	idAttribute: 'slug',

	defaults: {
		inputType: 'text'
	},

	template: template( 'hook-reaction-field' ),

	createField: function ( data ) {

		return this.template(
			_.extend( {}, data, { type: this.get( 'inputType' ) } )
		);
	}
});

var DataTypes = new Backbone.Collection();

DataTypes.add( new DataType( { slug: 'text' } ) );
DataTypes.add( new DataType( { slug: 'integer', inputType: 'number' } ) );

module.exports = Fields;
