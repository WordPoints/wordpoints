var hooks = wp.wordpoints.hooks,
	data = wp.wordpoints.hooks.view.data.reactors;

hooks.on( 'init', function () {

	hooks.Reactors.add( new hooks.reactor.Points( data.points ) );

	if ( data.points_legacy ) {
		hooks.Reactors.add( new hooks.reactor.Points( data.points_legacy ) );
	}
});

hooks.reactor.Points = require( './points.js' );
