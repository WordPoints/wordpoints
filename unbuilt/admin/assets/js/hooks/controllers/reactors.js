/**
 * wp.wordpoints.hooks.controller.Reactors
 *
 * @class
 * @augments Backbone.Collection
 * @augments wp.wordpoints.hooks.controller.Extensions
 */
var Extensions = wp.wordpoints.hooks.controller.Extensions,
	Reactor = wp.wordpoints.hooks.controller.Reactor,
	Reactors;

Reactors = Extensions.extend({
	model: Reactor
});

module.exports = Reactors;