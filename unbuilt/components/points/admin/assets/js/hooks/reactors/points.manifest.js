var hooks = wp.wordpoints.hooks,
	data = wp.wordpoints.hooks.view.data.reactors;

hooks.on( 'init', function () {

	hooks.Reactors.add( new hooks.reactor.Points( data.points ) );

	if ( data.points_legacy ) {
		hooks.Reactors.add( new hooks.reactor.Points( data.points_legacy ) );
	}

	var periods = hooks.Extensions.get( 'periods' ),
		nativeShowForReaction = periods.showForReaction;

	// Legacy periods extension.
	var LegacyPeriods = new hooks.extension.Periods(
		{ slug: 'points_legacy_periods' }
	);

	LegacyPeriods.showForReaction = function ( reaction ) {

		if ( ! reaction.model.get( 'points_legacy_periods' ) ) {
			return false;
		}

		return nativeShowForReaction( reaction );
	};

	// Register the legacy periods extension.
	hooks.Extensions.add( LegacyPeriods );

	// Don't show regular periods extension when legacy periods are in use.
	periods.showForReaction = function ( reaction ) {

		if ( reaction.model.get( 'points_legacy_periods' ) ) {
			return false;
		}

		return nativeShowForReaction( reaction );
	};
});

hooks.reactor.Points = require( './points.js' );
