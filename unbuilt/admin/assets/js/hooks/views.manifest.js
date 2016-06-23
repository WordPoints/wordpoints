var hooks = wp.wordpoints.hooks,
	$ = jQuery,
	data;

// Load the application once the DOM is ready.
$( function () {

	// Let all parts of the app know that we're about to start.
	hooks.trigger( 'init' );

	// We kick things off by creating the **Groups**.
	// Instead of generating new elements, bind to the existing skeletons of
	// the groups already present in the HTML.
	$( '.wordpoints-hook-reaction-group-container' ).each( function () {

		var $this = $( this ),
			event;

		event = $this
			.find( '.wordpoints-hook-reaction-group' )
				.data( 'wordpoints-hooks-hook-event' );

		new hooks.view.Reactions( {
			el: $this,
			model: new hooks.model.Reactions( data.reactions[ event ] )
		} );
	} );
});

// Link any localized strings.
hooks.view.l10n = window.WordPointsHooksAdminL10n || {};

// Link any settings.
data = hooks.view.data = window.WordPointsHooksAdminData || {};

// Load the controllers.
hooks.controller.Fields     = require( './controllers/fields.js' );
hooks.controller.Extension  = require( './controllers/extension.js' );
hooks.controller.Extensions = require( './controllers/extensions.js' );
hooks.controller.Reactor    = require( './controllers/reactor.js' );
hooks.controller.Reactors   = require( './controllers/reactors.js' );
hooks.controller.Args       = require( './controllers/args.js' );

// Start them up here so that we can begin using them.
hooks.Fields     = new hooks.controller.Fields( { fields: data.fields } );
hooks.Reactors   = new hooks.controller.Reactors();
hooks.Extensions = new hooks.controller.Extensions();
hooks.Args       = new hooks.controller.Args({ events: data.events, entities: data.entities });

// Load the views.
hooks.view.Base              = require( './views/base.js' );
hooks.view.Reaction          = require( './views/reaction.js' );
hooks.view.Reactions         = require( './views/reactions.js' );
hooks.view.ArgOption         = require( './views/arg-option.js' );
hooks.view.ArgSelector       = require( './views/arg-selector.js' );
hooks.view.ArgSelectors      = require( './views/arg-selectors.js' );
hooks.view.ArgSelector2      = require( './views/arg-selector2.js' );
