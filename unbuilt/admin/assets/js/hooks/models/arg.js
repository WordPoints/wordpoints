/**
 * wp.wordpoints.hooks.model.Arg
 *
 * @class
 * @augments Backbone.Model
 * @augments wp.wordpoints.hooks.model.Base
 */
var Base = wp.wordpoints.hooks.model.Base,
	Arg;

Arg = Base.extend({
	namespace: 'arg',
	idAttribute: 'slug'
});

module.exports = Arg;
