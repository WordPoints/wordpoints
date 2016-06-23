/**
 * wp.wordpoints.hooks.model.ConditionType
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	ConditionType;

ConditionType = Base.extend({
	idAttribute: 'slug'
});

module.exports = ConditionType;
