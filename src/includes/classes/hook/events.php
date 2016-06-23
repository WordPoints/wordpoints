<?php

/**
 * Hook events class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * A registry for the hook events.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Events extends WordPoints_App_Registry {

	/**
	 * The data for the events, indexed by slug.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected $event_data = array();

	/**
	 * A hook router.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Router
	 */
	protected $router;

	/**
	 * @since 2.1.0
	 */
	public function __construct( $slug ) {

		$hooks = wordpoints_hooks();

		$this->router = $hooks->get_sub_app( 'router' );

		parent::__construct( $slug );
	}

	/**
	 * @since 2.1.0
	 */
	public function init() {

		$this->sub_apps()->register( 'args', 'WordPoints_Class_Registry_Children' );

		parent::init();
	}

	/**
	 * @since 2.1.0
	 *
	 * @param string $slug  The slug for this event.
	 * @param string $class The name of the event class.
	 * @param array  $args  {
	 *        Other optional args.
	 *
	 *        @type array[] $actions The slugs of the actions that relate to this
	 *                               event, indexed by action type. If only a single
	 *                               action of a certain type is given a string may
	 *                               be provided, or an array of strings for multiple
	 *                               actions.
	 *
	 *        @type array[] $args    The args this event relates to.
	 * }
	 *
	 * @return bool Whether the event was registered.
	 */
	public function register( $slug, $class, array $args = array() ) {

		parent::register( $slug, $class, $args );

		if ( isset( $args['actions'] ) ) {
			foreach ( $args['actions'] as $type => $actions ) {
				foreach ( (array) $actions as $action_slug ) {
					$this->router->add_event_to_action( $slug, $action_slug, $type );
				}
			}
		}

		if ( isset( $args['args'] ) ) {
			$args_registry = $this->get_sub_app( 'args' );

			foreach ( $args['args'] as $arg_slug => $class ) {
				$args_registry->register( $slug, $arg_slug, $class );
			}
		}

		$this->event_data[ $slug ] = $args;

		return true;
	}

	/**
	 * @since 2.1.0
	 */
	public function deregister( $slug ) {

		if ( ! $this->is_registered( $slug ) ) {
			return;
		}

		parent::deregister( $slug );

		foreach ( (array) $this->event_data[ $slug ]['actions'] as $type => $actions ) {
			foreach ( (array) $actions as $action_slug ) {
				$this->router->remove_event_from_action( $slug, $action_slug, $type );
			}
		}

		$this->get_sub_app( 'args' )->deregister_children( $slug );

		unset( $this->event_data[ $slug ] );
	}
}

// EOF
