<?php

/**
 * Points type delete class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Routine to delete a points type.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Type_Delete extends WordPoints_Routine {

	/**
	 * The slug of the points type being deleted.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * @since 2.4.0
	 *
	 * @param string $slug The slug of the points type to delete.
	 */
	public function __construct( $slug ) {

		$this->slug         = $slug;
		$this->network_wide = is_wordpoints_network_active();
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$points_types = wordpoints_get_points_types();

		if ( ! isset( $points_types[ $this->slug ] ) ) {
			return false;
		}

		/**
		 * Fires when a points type is being deleted.
		 *
		 * @since 2.1.0
		 *
		 * @param string $slug      The slug of the points type being deleted.
		 * @param array  $settings The settings of the points type being deleted.
		 */
		do_action( 'wordpoints_delete_points_type', $this->slug, $points_types[ $this->slug ] );

		$meta_key = wordpoints_get_points_user_meta_key( $this->slug );

		global $wpdb;

		// Delete log meta for this points type.
		$query = new WordPoints_Points_Logs_Query(
			array( 'field' => 'id', 'points_type' => $this->slug )
		);

		$log_ids = $query->get( 'col' );

		foreach ( $log_ids as $log_id ) {
			wordpoints_points_log_delete_all_metadata( $log_id );
		}

		// Delete logs for this points type.
		$wpdb->delete( $wpdb->wordpoints_points_logs, array( 'points_type' => $this->slug ) );

		wordpoints_flush_points_logs_caches( array( 'points_type' => $this->slug ) );

		// Delete all user points of this type.
		delete_metadata( 'user', 0, wp_slash( $meta_key ), '', true );

		parent::run();

		unset( $points_types[ $this->slug ] );

		wordpoints_update_maybe_network_option(
			'wordpoints_points_types'
			, $points_types
		);

		return true;
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_network() {
		if ( $this->network_wide ) {
			$this->delete_points_hooks();
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_site() {
		$this->delete_points_hooks();
	}

	/**
	 * @since 2.4.0
	 */
	protected function run_for_single() {
		$this->delete_points_hooks();
	}

	/**
	 * Deletes the points hooks for this points type.
	 *
	 * @since 2.4.0
	 */
	protected function delete_points_hooks() {

		// Delete hooks associated with this points type.
		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		unset( $points_types_hooks[ $this->slug ] );

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

		// Delete reactions associated with this points type.
		foreach ( wordpoints_hooks()->get_reaction_stores( 'points' ) as $reaction_store ) {
			foreach ( $reaction_store->get_reactions() as $reaction ) {
				if ( $this->slug === $reaction->get_meta( 'points_type' ) ) {
					$reaction_store->delete_reaction( $reaction->get_id() );
				}
			}
		}
	}
}

// EOF
