var hooks = wp.wordpoints.hooks;

// Controllers.
hooks.extension.Disable = require( './disable/controllers/extension.js' );

var Disable = new hooks.extension.Disable();

// Register the extension.
hooks.Extensions.add( Disable );

// EOF
