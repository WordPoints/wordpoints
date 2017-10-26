<?php

/**
 * Hook action router class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Routes WordPress actions to WordPoints hook actions, and finally to hook events.
 *
 * Each WordPress action can have several different WordPoints hook actions hooked to
 * it. This router handles hooking into the WordPress action, and making sure the
 * hook actions are processed when it is fired. This allows us to hook to each action
 * once, even if multiple hook actions are registered for it.
 *
 * When a hook action is fired, the router then loops through the events which are
 * registered to fire on that hook action, and fires each of them.
 *
 * This arrangement allows for events and actions to be decoupled from WordPress
 * actions, and from each other as well. As a result, action classes don't have to
 * be loaded until the router is called on the action that they are attached to. The
 * event classes can be lazy-loaded as well.
 *
 * It also makes it possible for a hook action to abort firing any events if it
 * chooses to do so.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Router {

	/**
	 * The hooks app object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hooks
	 */
	protected $hooks;

	/**
	 * The actions registry object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Actions
	 */
	protected $actions;

	/**
	 * The events registry object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Events
	 */
	protected $events;

	/**
	 * The event args registry object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Class_Registry_ChildrenI
	 */
	protected $event_args;

	/**
	 * The actions, indexed by WordPress action/filter hooks.
	 *
	 * The indexes are of this format: "$action_or_filter_name,$priority".
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $action_index = array();

	/**
	 * The events, indexed by action slug and action type.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $event_index = array();

	/**
	 * @since 2.1.0
	 */
	public function __call( $name, $args ) {

		$this->route_action( $name, $args );

		// Return the first value, in case it is hooked to a filter.
		$return = null;
		if ( isset( $args[0] ) ) {
			$return = $args[0];
		}

		return $return;
	}

	/**
	 * Routes a WordPress action to WordPoints hook actions, and fires their events.
	 *
	 * @since 2.1.0
	 *
	 * @param string $name The action ID. This is not the slug of a hook action, but
	 *                     rather a unique ID for the WordPress action based on the
	 *                     action name and the priority.
	 * @param array  $args The args the action was fired with.
	 */
	protected function route_action( $name, $args ) {

		if ( ! isset( $this->action_index[ $name ] ) ) {
			return;
		}

		// We might normally do this in the constructor, however, the events
		// registry attempts to access the router in its own constructor. The result
		// of attempting to do this before the router itself has been fully
		// constructed is that the events registry gets null instead of the router.
		if ( ! isset( $this->hooks ) ) {

			$hooks = wordpoints_hooks();

			$this->hooks      = $hooks;
			$this->events     = $hooks->get_sub_app( 'events' );
			$this->actions    = $hooks->get_sub_app( 'actions' );
			$this->event_args = $this->events->get_sub_app( 'args' );
		}

		foreach ( $this->action_index[ $name ]['actions'] as $slug => $data ) {

			if ( ! isset( $this->event_index[ $slug ] ) ) {
				continue;
			}

			$action_object = $this->actions->get( $slug, $args, $data );

			if ( ! ( $action_object instanceof WordPoints_Hook_ActionI ) ) {
				continue;
			}

			if ( ! $action_object->should_fire() ) {
				continue;
			}

			foreach ( $this->event_index[ $slug ] as $type => $events ) {
				foreach ( $events as $event_slug => $unused ) {

					if ( ! $this->events->is_registered( $event_slug ) ) {
						continue;
					}

					$event_args = $this->event_args->get_children( $event_slug, array( $action_object ) );

					if ( empty( $event_args ) ) {
						continue;
					}

					$event_args = new WordPoints_Hook_Event_Args( $event_args );

					$this->hooks->fire( $event_slug, $event_args, $type );
				}
			}
		}
	}

	/**
	 * Register an action with the router.
	 *
	 * The arg number will be automatically determined based on $data['arg_index']
	 * and $data['requirements']. So in most cases $arg_number may be omitted.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the action.
	 * @param array  $args {
	 *        Other arguments.
	 *
	 *        @type string $action     The name of the WordPress action for this hook action.
	 *        @type int    $priority   The priority for the WordPress action. Default: 10.
	 *        @type int    $arg_number The number of args the action object expects. Default: 1.
	 *        @type array  $data {
	 *              Args that will be passed to the action object's constructor.
	 *
	 *              @type int[] $arg_index    List of args (starting from 0), indexed by slug.
	 *              @type array $requirements List of requirements, indexed by arg index (from 0).
	 *        }
	 * }
	 */
	public function add_action( $slug, array $args ) {

		$priority = 10;
		if ( isset( $args['priority'] ) ) {
			$priority = $args['priority'];
		}

		if ( ! isset( $args['action'] ) ) {
			return;
		}

		$method = "{$args['action']},{$priority}";

		$this->action_index[ $method ]['actions'][ $slug ] = array();

		$arg_number = 1;

		if ( isset( $args['data'] ) ) {

			if ( isset( $args['data']['arg_index'] ) ) {
				$arg_number = 1 + max( $args['data']['arg_index'] );
			}

			if ( isset( $args['data']['requirements'] ) ) {
				$requirements = 1 + max( array_keys( $args['data']['requirements'] ) );

				if ( $requirements > $arg_number ) {
					$arg_number = $requirements;
				}
			}

			$this->action_index[ $method ]['actions'][ $slug ] = $args['data'];
		}

		if ( isset( $args['arg_number'] ) ) {
			$arg_number = $args['arg_number'];
		}

		// If this action is already being routed, and will have enough args, we
		// don't need to hook to it again.
		if (
			isset( $this->action_index[ $method ]['arg_number'] )
			&& $this->action_index[ $method ]['arg_number'] >= $arg_number
		) {
			return;
		}

		$this->action_index[ $method ]['arg_number'] = $arg_number;

		add_action( $args['action'], array( $this, $method ), $priority, $arg_number );
	}

	/**
	 * Deregister an action with the router.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The action slug.
	 */
	public function remove_action( $slug ) {

		foreach ( $this->action_index as $method => $data ) {
			if ( isset( $data['actions'][ $slug ] ) ) {

				unset( $this->action_index[ $method ]['actions'][ $slug ] );

				if ( empty( $this->action_index[ $method ]['actions'] ) ) {

					unset( $this->action_index[ $method ] );

					list( $action, $priority ) = explode( ',', $method );

					remove_action( $action, array( $this, $method ), $priority );
				}
			}
		}
	}

	/**
	 * Hook an event to an action.
	 *
	 * @since 2.1.0
	 *
	 * @param string $event_slug The slug of the event.
	 * @param string $action_slug The slug of the action.
	 * @param string $action_type The type of action. Default is 'fire'.
	 */
	public function add_event_to_action( $event_slug, $action_slug, $action_type = 'fire' ) {
		$this->event_index[ $action_slug ][ $action_type ][ $event_slug ] = true;
	}

	/**
	 * Unhook an event from an action.
	 *
	 * @since 2.1.0
	 *
	 * @param string $event_slug  The slug of the event.
	 * @param string $action_slug The slug of the action.
	 * @param string $action_type The type of action. Default is 'fire'.
	 */
	public function remove_event_from_action( $event_slug, $action_slug, $action_type = 'fire' ) {
		unset( $this->event_index[ $action_slug ][ $action_type ][ $event_slug ] );
	}

	/**
	 * Get the event index.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] The event index.
	 */
	public function get_event_index() {

		if ( empty( $this->event_index ) ) {
			wordpoints_hooks()->get_sub_app( 'events' );
		}

		return $this->event_index;
	}
}

// EOF
