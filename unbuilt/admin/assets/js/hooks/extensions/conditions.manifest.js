var hooks = wp.wordpoints.hooks;

// Models
hooks.model.Condition       = require( './conditions/models/condition.js' );
hooks.model.Conditions      = require( './conditions/models/conditions.js' );
hooks.model.ConditionGroup  = require( './conditions/models/condition-group.js' );
hooks.model.ConditionGroups = require( './conditions/models/condition-groups.js' );
hooks.model.ConditionType   = require( './conditions/models/condition-type.js' );
hooks.model.ConditionTypes  = require( './conditions/models/condition-types.js' );

// Views
hooks.view.Condition         = require( './conditions/views/condition.js' );
hooks.view.ConditionGroup    = require( './conditions/views/condition-group.js' );
hooks.view.ConditionSelector = require( './conditions/views/condition-selector.js' );
hooks.view.ConditionGroups   = require( './conditions/views/condition-groups.js' );

// Controllers.
hooks.extension.Conditions = require( './conditions/controllers/extension.js' );
hooks.extension.Conditions.Condition = require( './conditions/controllers/condition.js' );

var Conditions = new hooks.extension.Conditions();

// Conditions.
var Equals = require( './conditions/controllers/conditions/equals.js' );

Conditions.registerController( 'text', 'equals', Equals );
Conditions.registerController( 'entity', 'equals', Equals );
Conditions.registerController( 'entity_array', 'equals', Equals );
Conditions.registerController(
	'entity_array'
	, 'contains'
	, require( './conditions/controllers/conditions/entity-array-contains.js' )
);

// Register the extension.
hooks.Extensions.add( Conditions );

// EOF
