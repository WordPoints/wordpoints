/**
 * wp.wordpoints.hooks.view.Base
 *
 * @class
 * @augments Backbone.View
 * @augments wp.wordpoints.hooks.view.Base
 */
var Base = wp.wordpoints.hooks.view.Base,
	Fields = wp.wordpoints.hooks.Fields,
	Reactors = wp.wordpoints.hooks.Reactors,
	Args = wp.wordpoints.hooks.Args,
	$ = Backbone.$,
	l10n = wp.wordpoints.hooks.view.l10n,
	data = wp.wordpoints.hooks.view.data,
	Reaction;

// The DOM element for a reaction...
Reaction = Base.extend({

	namespace: 'reaction',

	className: 'wordpoints-hook-reaction',

	template: wp.wordpoints.hooks.template( 'hook-reaction' ),

	// The DOM events specific to an item.
	events: function () {

		var events = {
			'click .actions .delete': 'confirmDelete',
			'click .save':            'save',
			'click .cancel':          'cancel',
			'click .close':           'close',
			'click .edit':            'edit',
			'change .fields *':       'lockOpen'
		};

		/*
		 * Use feature detection to determine whether we should use the `input`
		 * event. Input is preferred but lacks support in legacy browsers.
		 */
		if ( 'oninput' in document.createElement( 'input' ) ) {
			events['input input'] = 'lockOpen';
		} else {
			events['keyup input'] = 'maybeLockOpen';
		}

		return events;
	},

	initialize: function () {

		this.listenTo( this.model, 'change:description', this.renderDescription );
		this.listenTo( this.model, 'change:reactor', this.setReactor );
		this.listenTo( this.model, 'change:reactor', this.renderTarget );
		this.listenTo( this.model, 'destroy', this.remove );
		this.listenTo( this.model, 'sync', this.showSuccess );
		this.listenTo( this.model, 'error', this.showAjaxErrors );
		this.listenTo( this.model, 'invalid', this.showValidationErrors );

		this.on( 'render:settings', this.renderTarget );

		this.setReactor();
	},

	render: function () {

		this.$el.html( this.template() );

		this.$title    = this.$( '.title' );
		this.$fields   = this.$( '.fields' );
		this.$settings = this.$fields.find( '.settings' );
		this.$target   = this.$fields.find( '.target' );

		this.renderDescription();

		this.trigger( 'render', this );

		return this;
	},

	// Re-render the title of the hook.
	renderDescription: function () {

		this.$title.text( this.model.get( 'description' ) );

		this.trigger( 'render:title', this );
	},

	renderFields: function () {

		var currentActionType = this.getCurrentActionType();

		this.trigger( 'render:settings', this.$settings, currentActionType, this );
		this.trigger( 'render:fields', this.$fields, currentActionType, this );

		this.renderedFields = true;
	},

	renderTarget: function () {

		var argTypes = this.Reactor.get( 'arg_types' ),
			end;

		// If there is just one arg type, we can use the `_.where()`-like syntax.
		if ( argTypes.length === 1 ) {

			end = { _canonical: argTypes[0], _type: 'entity' };

		} else {

			// Otherwise, we'll be need our own function, for `_.filter()`.
			end = function ( arg ) {
				return (
					arg.get( '_type' ) === 'entity'
					&& _.contains( argTypes, arg.get( '_canonical' ) )
				);
			};
		}

		var hierarchies = Args.getHierarchiesMatching( {
			event: this.model.get( 'event' ),
			end: end
		} );

		var options = [];

		_.each( hierarchies, function ( hierarchy ) {
			options.push( {
				label: Args.buildHierarchyHumanId( hierarchy ),
				value: _.pluck( _.pluck( hierarchy, 'attributes' ), 'slug' ).join( ',' )
			} );
		});

		var value = this.model.get( 'target' );

		if ( _.isArray( value ) ) {
			value = value.join( ',' );
		}

		var label = this.Reactor.get( 'target_label' );

		if ( ! label ) {
			label = l10n.target_label;
		}

		if ( ! this.model.isNew() ) {
			label += ' ' + l10n.cannotBeChanged;
		}

		var field = Fields.create(
			'target'
			, value
			, {
				type: 'select',
				options: options,
				label: label
			}
		);

		this.$target.html( field );

		if ( ! this.model.isNew() ) {
			this.$target.find( 'select' ).prop( 'disabled', true );
		}
	},

	setReactor: function () {
		this.Reactor = Reactors.get( this.model.get( 'reactor' ) );
	},

	// Get the current action type that settings are being displayed for.
	// Right now we just default this to the first action type that the reactor
	// supports which is registered for this event.
	getCurrentActionType: function () {

		var eventActionTypes = data.event_action_types[ this.model.get( 'event' ) ];

		if ( ! eventActionTypes ) {
			return;
		}

		var reactorActionTypes = this.Reactor.get( 'action_types' );

		// We loop through the reactor action types as the primary list, because it
		// is in order, while the event action types isn't in any particular order.
		// Otherwise we'd end up selecting the action types inconsistently.
		for ( var i = 0; i < reactorActionTypes.length; i++ ) {
			if ( eventActionTypes[ reactorActionTypes[ i ] ] ) {
				return reactorActionTypes[ i ];
			}
		}
	},

	// Toggle the visibility of the form.
	edit: function () {

		if ( ! this.renderedFields ) {
			this.renderFields();
		}

		// Then display the form.
		this.$fields.slideDown( 'fast' );
		this.$el.addClass( 'editing' );
	},

	// Close the form.
	close: function () {

		this.$fields.slideUp( 'fast' );
		this.$el.removeClass( 'editing' );
		this.$( '.success' ).hide();
	},

	// Maybe lock the form open when an input is altered.
	maybeLockOpen: function ( event ) {

		var $target = $( event.target );

		var attrSlug = Fields.getAttrSlug( this.model, $target.attr( 'name' ) );

		if ( $target.val() !== this.model.get( attrSlug ) + '' ) {
			this.lockOpen();
		}
	},

	// Lock the form open when the form values have been changed.
	lockOpen: function () {

		if ( this.cancelling ) {
			return;
		}

		this.$el.addClass( 'changed' );
		this.$( '.save' ).prop( 'disabled', false );
		this.$( '.success' ).fadeOut();
	},

	// Cancel editing or adding a new reaction.
	cancel: function () {

		if ( this.$el.hasClass( 'new' ) ) {

			this.model.collection.trigger( 'cancel-add-new' );
			this.remove();

			wp.a11y.speak( l10n.discardedReaction );

			return;
		}

		this.$el.removeClass( 'changed' );
		this.$( '.save' ).prop( 'disabled', true );

		this.cancelling = true;

		this.renderFields();

		this.trigger( 'cancel' );

		wp.a11y.speak( l10n.discardedChanges );

		this.cancelling = false;
	},

	// Save changes to the reaction.
	save: function () {

		this.wait();
		this.$( '.save' ).prop( 'disabled', true );

		wp.a11y.speak( l10n.saving );

		var formData = Fields.getFormData( this.model, this.$fields );

		if ( formData.target ) {
			formData.target = formData.target.split( ',' );
		}

		this.model.save( formData, { wait: true, rawAtts: formData } );
	},

	// Display a spinner while changes are being saved.
	wait: function () {

		this.$( '.spinner-overlay' ).show();
		this.$( '.err' ).slideUp();
	},

	// Confirm that a reaction is intended to be deleted before deleting it.
	confirmDelete: function () {

		var $dialog = $( '<div></div>' ),
			view = this;

		this.$( '.messages div' ).slideUp();

		$dialog
			.attr( 'title', l10n.confirmTitle )
			.append( $( '<p></p>' ).text( l10n.confirmAboutTo ) )
			.append(
				$( '<p></p>' )
					.append( $( '<b></b>' ).text( this.model.get( 'description' ) ) )
			)
			.append( $( '<p></p>' ).text( l10n.confirmDelete ) )
			.dialog({
				dialogClass: 'wp-dialog wordpoints-delete-hook-reaction-dialog',
				resizable: false,
				draggable: false,
				height: 'auto',
				modal: true,
				buttons: [
					{
						text: l10n.cancelText,
						'class': 'button',
						click: function() {
							$( this ).dialog( 'destroy' );
						}
					},
					{
						text: l10n.deleteText,
						'class': 'button button-primary',
						click: function() {
							$( this ).dialog( 'destroy' );
							view.destroy();
						}
					}
				]
			});
	},

	// Remove the item, destroy the model.
	destroy: function () {

		wp.a11y.speak( l10n.deleting );

		this.wait();

		this.model.destroy(
			{
				wait: true,
				success: function () {
					wp.a11y.speak( l10n.reactionDeleted );
				}
			}
		);
	},

	// Display errors when the model has invalid fields.
	showValidationErrors: function ( model, errors ) {
		this.showError( errors );
	},

	// Display an error when there is an Ajax failure.
	showAjaxErrors: function ( event, response ) {

		var errors;

		if ( ! _.isEmpty( response.errors ) ) {
			errors = response.errors;
		} else if ( response.message ) {
			errors = response.message;
		} else {
			errors = l10n.unexpectedError;
		}

		this.showError( errors );
	},

	showError: function ( errors ) {

		var generalErrors = [];
		var a11yErrors = [];
		var $errors = this.$( '.messages .err' );

		this.$( '.spinner-overlay' ).hide();

		// Sometimes we get a list of errors.
		if ( _.isArray( errors ) ) {

			// When that happens, we loop over them and try to display each of
			// them next to their associated field.
			_.each( errors, function ( error ) {

				var $field, escapedFieldName;

				// Sometimes some of the errors aren't for any particular field
				// though, so we collect them in an array an display them all
				// together a bit later.
				if ( ! error.field ) {
					generalErrors.push( error.message );
					return;
				}

				escapedFieldName = Fields.getFieldName( error.field )
						.replace( /[^a-z0-9-_\[\]\{}\\]/gi, '' )
						.replace( /\\/g, '\\\\' );

				// When a field is specified, we try to locate it.
				$field = this.$( '[name="' + escapedFieldName + '"]' );

				if ( 0 === $field.length ) {

					// However, there are times when the error is for a field set
					// and not a single field. In that case, we try to find the
					// fields in that set.
					$field = this.$( '[name^="' + escapedFieldName + '"]' );

					// If that fails, we just add this to the general errors.
					if ( 0 === $field.length ) {
						generalErrors.push( error.message );
						return;
					}

					$field = $field.first();
				}

				$field.before(
					$( '<div class="message err"></div>' ).text( error.message )
				);

				a11yErrors.push( error.message );

			}, this );

			$errors.html( '' );

			// There may be some general errors that we need to display to the user.
			// We also add an explanation that there are some fields that need to be
			// corrected, if there were some per-field errors, to make sure that they
			// see those errors as well (since they may not be in view).
			if ( generalErrors.length < errors.length ) {
				generalErrors.unshift( l10n.fieldsInvalid );
			}

			_.each( generalErrors, function ( error ) {
				$errors.append( $( '<p></p>' ).text( error ) );
			});

			// Notify unsighted users as well.
			a11yErrors.unshift( l10n.fieldsInvalid );

			wp.a11y.speak( a11yErrors.join( ' ' ) );

		} else {

			$errors.text( errors );
			wp.a11y.speak( errors );
		}

		$errors.fadeIn();
	},

	// Display a success message.
	showSuccess: function () {

		this.$( '.spinner-overlay' ).hide();

		this.$( '.success' )
			.text( l10n.changesSaved )
			.slideDown();

		wp.a11y.speak( l10n.reactionSaved );

		this.$target.find( 'select' ).prop( 'disabled', true );

		this.$el.removeClass( 'new changed' );
	}
});

module.exports = Reaction;
