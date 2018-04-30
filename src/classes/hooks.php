<?php

/**
 * Class for the hooks app.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Hooks app.
 *
 * The hooks API consists primarily of actions, events, reactors, args, and
 * other extensions. Events are "fired" at various reactors when actions occur.
 * The args that the event relates to is passed to any extensions, along with
 * the list of predefined reactions. The extensions can then analyse the args and
 * the reaction specifications to determine whether the reactor should "hit" or
 * "miss" the target entity.
 *
 * @since 2.1.0
 *
 * @method null|object|WordPoints_Hook_Router|WordPoints_Hook_Actions|WordPoints_Hook_Events|WordPoints_Class_Registry_ChildrenI|WordPoints_Class_Registry|WordPoints_App get_sub_app( $slug )
 */
class WordPoints_Hooks extends WordPoints_App {

	/**
	 * The current mode of the API.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $current_mode;

	/**
	 * Register the sub apps when the app is constructed.
	 *
	 * @since 2.1.0
	 */
	protected function init() {

		$sub_apps = $this->sub_apps;
		$sub_apps->register( 'router', 'WordPoints_Hook_Router' );
		$sub_apps->register( 'actions', 'WordPoints_Hook_Actions' );
		$sub_apps->register( 'events', 'WordPoints_Hook_Events' );
		$sub_apps->register( 'reactors', 'WordPoints_Class_Registry_Persistent' );
		$sub_apps->register( 'reaction_stores', 'WordPoints_Class_Registry_Children' );
		$sub_apps->register( 'extensions', 'WordPoints_Class_Registry_Persistent' );
		$sub_apps->register( 'conditions', 'WordPoints_Class_Registry_Children' );

		parent::init();
	}

	/**
	 * Gets the current mode that the API is in.
	 *
	 * By default 'standard' mode is on, unless in network context (such as in the
	 * network admin) on multisite, when 'network' mode is the default.
	 *
	 * The current mode is used by reactors to determine which reaction type to offer
	 * access to through the $reactions property. This is allows for generic code for
	 * handling reactions to reference the $reactions property of the reactor, and
	 * what type of reactions it will get will be determined based on the current
	 * mode that is set.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of the current mode.
	 */
	public function get_current_mode() {

		if ( ! isset( $this->current_mode ) ) {
			$this->current_mode = ( wordpoints_is_network_context() ? 'network' : 'standard' );
		}

		return $this->current_mode;
	}

	/**
	 * Sets the current mode of the API.
	 *
	 * This function should be used very sparingly. The default mode which is set by
	 * WordPoints should work for you in most cases. The primary reason that you
	 * would ever need to set the mode yourself is if you have created your own
	 * custom mode. Otherwise you probably shouldn't be using this function.
	 *
	 * @since 2.1.0
	 *
	 * @param string $mode The slug of the mode to set as the current mode.
	 */
	public function set_current_mode( $mode ) {
		$this->current_mode = $mode;
	}

	/**
	 * Get a reaction storage object.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the reaction store to get.
	 *
	 * @return WordPoints_Hook_Reaction_StoreI|false The reaction storage object.
	 */
	public function get_reaction_store( $slug ) {

		$reaction_store = $this->get_sub_app( 'reaction_stores' )->get(
			$this->get_current_mode()
			, $slug
		);

		if ( ! $reaction_store instanceof WordPoints_Hook_Reaction_StoreI ) {
			return false;
		}

		// Allowing access to stores out-of-context would lead to strange behavior.
		if ( false === $reaction_store->get_context_id() ) {
			return false;
		}

		return $reaction_store;
	}

	/**
	 * Get all in-context reaction stores with a particular slug.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug Slug of the reaction stores to get.
	 *
	 * @return WordPoints_Hook_Reaction_StoreI[] The reaction stores with this slug.
	 */
	public function get_reaction_stores( $slug ) {

		$reaction_stores = $this->get_sub_app( 'reaction_stores' );

		$stores = array();

		foreach ( $reaction_stores->get_all_slugs() as $mode => $slugs ) {

			if ( ! in_array( $slug, $slugs, true ) ) {
				continue;
			}

			$store = $reaction_stores->get( $mode, $slug, array( $mode ) );

			if ( ! $store instanceof WordPoints_Hook_Reaction_StoreI ) {
				continue;
			}

			// Allowing access to stores out-of-context would lead to strange behavior.
			if ( false === $store->get_context_id() ) {
				continue;
			}

			$stores[ $mode ] = $store;
		}

		return $stores;
	}

	/**
	 * Fire an event at each of the reactions.
	 *
	 * @since 2.1.0
	 *
	 * @param string                     $event_slug  The slug of the event.
	 * @param WordPoints_Hook_Event_Args $event_args  The event args.
	 * @param string                     $action_type The type of action triggering
	 *                                                this fire of this event.
	 */
	public function fire(
		$event_slug,
		WordPoints_Hook_Event_Args $event_args,
		$action_type
	) {

		foreach ( $this->get_sub_app( 'reaction_stores' )->get_all() as $reaction_stores ) {
			foreach ( $reaction_stores as $reaction_store ) {

				if ( ! $reaction_store instanceof WordPoints_Hook_Reaction_StoreI ) {
					continue;
				}

				// Allowing access to stores out-of-context would lead to strange behavior.
				if ( false === $reaction_store->get_context_id() ) {
					continue;
				}

				foreach ( $reaction_store->get_reactions_to_event( $event_slug ) as $reaction ) {

					$fire = new WordPoints_Hook_Fire(
						$event_args
						, $reaction
						, $action_type
					);

					$this->fire_reaction( $fire );
				}
			}
		}
	}

	/**
	 * Fire for a particular reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The hook fire object.
	 */
	protected function fire_reaction( $fire ) {

		/** @var WordPoints_Hook_ReactorI $reactor */
		$reactor = $this->get_sub_app( 'reactors' )->get( $fire->reaction->get_reactor_slug() );

		if ( ! in_array( $fire->action_type, $reactor->get_action_types(), true ) ) {
			return;
		}

		$validator = new WordPoints_Hook_Reaction_Validator( $fire->reaction, true );
		$validator->validate();

		if ( $validator->had_errors() ) {
			return;
		}

		unset( $validator );

		if ( $reactor instanceof WordPoints_Hook_Reactor_Target_ValidatorI ) {

			$target = $fire->event_args->get_from_hierarchy(
				$fire->reaction->get_meta( 'target' )
			);

			if ( ! $reactor->can_hit( $target, $fire ) ) {
				return;
			}
		}

		/** @var WordPoints_Hook_ExtensionI[] $extensions */
		$extensions = $this->get_sub_app( 'extensions' )->get_all();

		foreach ( $extensions as $extension ) {
			if ( ! $extension->should_hit( $fire ) ) {
				foreach ( $extensions as $ext ) {
					if ( $ext instanceof WordPoints_Hook_Extension_Miss_ListenerI ) {
						$ext->after_miss( $fire );
					}
				}

				return;
			}
		}

		$fire->hit();

		$reactor->hit( $fire );

		foreach ( $extensions as $extension ) {
			if ( $extension instanceof WordPoints_Hook_Extension_Hit_ListenerI ) {
				$extension->after_hit( $fire );
			}
		}
	}
}

// EOF
