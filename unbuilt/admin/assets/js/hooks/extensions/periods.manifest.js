var hooks = wp.wordpoints.hooks;

// Views.
hooks.view.SimplePeriod = require( './periods/views/simple-period.js' );

// Controllers.
hooks.extension.Periods = require( './periods/controllers/extension.js' );

var Periods = new hooks.extension.Periods();

// Register the extension.
hooks.Extensions.add( Periods );

// EOF
