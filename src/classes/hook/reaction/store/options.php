<?php

/**
 * Class for option table hook reaction storage method.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Stores hook reaction settings in options.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Reaction_Store_Options extends WordPoints_Hook_Reaction_Store {

	/**
	 * @since 2.1.0
	 */
	protected $reaction_class = 'WordPoints_Hook_Reaction_Options';

	/**
	 * @since 2.1.0
	 */
	public function reaction_exists( $id ) {
		return (bool) $this->get_option( $this->get_settings_option_name( $id ) );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reactions() {
		return $this->create_reaction_objects( $this->get_reaction_index() );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reactions_to_event( $event_slug ) {

		$index = $this->get_reaction_index();
		$index = wp_list_filter( $index, array( 'event' => $event_slug ) );
		return $this->create_reaction_objects( $index );
	}

	/**
	 * Get an index of the reaction for this reactor.
	 *
	 * The index is stored as an array of the following format:
	 *
	 * array(
	 *    1  => array( 'event' => 'post_publish',  'id' => 1  ),
	 *    23 => array( 'event' => 'user_register', 'id' => 23 ),
	 * );
	 *
	 * @since 2.1.0
	 *
	 * @return array[] The index array.
	 */
	protected function get_reaction_index() {

		$index = $this->get_option( $this->get_reaction_index_option_name() );

		if ( ! is_array( $index ) ) {
			$index = array();
		}

		return $index;
	}

	/**
	 * Update the index of the reactions for this reactor.
	 *
	 * @since 2.1.0
	 *
	 * @param array[] $index The index {@see self::get_reaction_index()}.
	 *
	 * @return bool Whether the index was updated successfully.
	 */
	protected function update_reaction_index( $index ) {

		return $this->update_option(
			$this->get_reaction_index_option_name()
			, $index
		);
	}

	/**
	 * Get the event for a reaction from the reaction index.
	 *
	 * This is only public because the reaction class needs to call it.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The ID of the reaction.
	 *
	 * @return string|false The event slug, or false if not found.
	 */
	public function get_reaction_event_from_index( $id ) {

		$index = $this->get_reaction_index();

		if ( ! isset( $index[ $id ]['event'] ) ) {
			return false;
		}

		return $index[ $id ]['event'];
	}

	/**
	 * Update the event for a reaction in the reaction index.
	 *
	 * This is only public because the reaction class needs to call it.
	 *
	 * @since 2.1.0
	 *
	 * @param int    $id    The ID of the reaction.
	 * @param string $event The slug of the event the reaction is to.
	 *
	 * @return bool Whether the event was updated successfully.
	 */
	public function update_reaction_event_in_index( $id, $event ) {

		$index = $this->get_reaction_index();

		if ( ! isset( $index[ $id ] ) ) {
			return false;
		}

		$index[ $id ]['event'] = $event;

		return $this->update_reaction_index( $index );
	}

	/**
	 * Converts an index into reaction objects.
	 *
	 * @since 2.1.0
	 *
	 * @param array $index A reaction index {@see self::get_reaction_index()}.
	 *
	 * @return WordPoints_Hook_Reaction_Options[] The objects for the reactions.
	 */
	protected function create_reaction_objects( $index ) {

		$reactions = array();

		foreach ( $index as $reaction ) {

			$object = $this->get_reaction( $reaction['id'] );

			if ( ! $object ) {
				continue;
			}

			$reactions[] = $object;
		}

		return $reactions;
	}

	/**
	 * @since 2.1.0
	 */
	public function delete_reaction( $id ) {

		if ( ! $this->reaction_exists( $id ) ) {
			return false;
		}

		$result = $this->delete_option( $this->get_settings_option_name( $id ) );

		if ( ! $result ) {
			return false;
		}

		$index = $this->get_reaction_index();

		unset( $index[ $id ] );

		return $this->update_reaction_index( $index );
	}

	/**
	 * @since 2.1.0
	 */
	protected function _create_reaction( $event_slug ) {

		$index = $this->get_reaction_index();

		$id = $this->get_next_id( $index );

		$settings = array( 'event' => $event_slug );

		$result = $this->add_option(
			$this->get_settings_option_name( $id )
			, $settings
		);

		if ( ! $result ) {
			return false;
		}

		$index[ $id ] = array( 'event' => $event_slug, 'id' => $id );

		if ( ! $this->update_reaction_index( $index ) ) {
			return false;
		}

		$this->update_option( $this->get_last_reaction_id_option_name(), $id );

		return $id;
	}

	/**
	 * Get the ID to use for the next reaction that we create.
	 *
	 * @since 2.1.0
	 *
	 * @param array[] $index The reaction index.
	 *
	 * @return int The ID to use for the next reaction.
	 */
	protected function get_next_id( $index ) {

		$id = 1;

		$last_id = $this->get_option( $this->get_last_reaction_id_option_name() );

		if ( wordpoints_posint( $last_id ) ) {
			$id = $last_id + 1;
		}

		// Double-check this against the index. This should avoid some issues from
		// race conditions.
		if ( ! empty( $index ) ) {

			$max = max( array_keys( $index ) );

			if ( $max >= $id ) {
				$id = $max + 1;
			}
		}

		return $id;
	}

	/**
	 * Get an option.
	 *
	 * This is public so that the reaction object can access it.
	 *
	 * @since 2.1.0
	 *
	 * @param string $name The option name.
	 *
	 * @return mixed The option value, or false.
	 */
	public function get_option( $name ) {
		return get_option( $name );
	}

	/**
	 * Add an option.
	 *
	 * @since 2.1.0
	 *
	 * @param string $name  The name of the option.
	 * @param mixed  $value The option value.
	 *
	 * @return bool Whether the option was added successfully.
	 */
	protected function add_option( $name, $value ) {
		return add_option( $name, $value );
	}

	/**
	 * Update an option.
	 *
	 * This is public so that the reaction object can access it.
	 *
	 * @since 2.1.0
	 *
	 * @param string $name  The option name.
	 * @param mixed  $value The option value.
	 *
	 * @return bool Whether the option was updated successfully.
	 */
	public function update_option( $name, $value ) {
		return update_option( $name, $value );
	}

	/**
	 * Delete an option.
	 *
	 * @since 2.1.0
	 *
	 * @param string $name The option name.
	 *
	 * @return bool Whether the option was deleted successfully.
	 */
	protected function delete_option( $name ) {
		return delete_option( $name );
	}

	/**
	 * Get the name of the option where the reaction's settings are stored.
	 *
	 * This is public so that the reaction object can access it.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The reaction ID.
	 *
	 * @return string The name of the option where the settings are stored.
	 */
	public function get_settings_option_name( $id ) {
		return "wordpoints_hook_reaction-{$this->slug}-{$this->mode_slug}-{$id}";
	}

	/**
	 * Get the name of the option where the reaction index is stored.
	 *
	 * @since 2.1.0
	 *
	 * @return string The name of the option where the reaction index is stored.
	 */
	protected function get_reaction_index_option_name() {
		return "wordpoints_hook_reaction_index-{$this->slug}-{$this->mode_slug}";
	}

	/**
	 * Get the name of the option where the last reaction ID is stored.
	 *
	 * @since 2.1.0
	 *
	 * @return string The name of the option where the last reaction ID is stored.
	 */
	protected function get_last_reaction_id_option_name() {
		return "wordpoints_hook_reaction_last_id-{$this->slug}-{$this->mode_slug}";
	}
}

// EOF
