<?php

/**
 * Int signature arg GUIDs hook hits updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates signature args GUIDs in the hook hits table to be integers.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Hook_Hits_Signature_Arg_GUIDs_Int
	implements WordPoints_RoutineI {

	/**
	 * The entity slugs to fix the GUIDs for.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $entity_slugs;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $entity_slugs The entity slugs to fix GUIDs for.
	 */
	public function __construct( array $entity_slugs ) {

		$this->entity_slugs = $entity_slugs;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		foreach ( $this->entity_slugs as $slug ) {

			$query = new WordPoints_Hook_Hit_Query(
				array(
					'fields'                       => array( 'id', 'signature_arg_guids' ),
					'signature_arg_guids'          => '%{' . $wpdb->esc_like( wp_json_encode( $slug ) ) . ':"%',
					'signature_arg_guids__compare' => 'LIKE',
				)
			);

			foreach ( $query->get() as $hit ) {

				$guids = json_decode( $hit->signature_arg_guids, true );
				$guids = $this->fix_guid_types( $guids, $slug );

				$wpdb->update(
					$wpdb->wordpoints_hook_hits
					, array( 'signature_arg_guids' => wp_json_encode( $guids ) )
					, array( 'id' => $hit->id )
				); // WPCS: cache OK.
			}
		}
	}

	/**
	 * Ensures that the GUIDs for entities with the provided slug are integers.
	 *
	 * @since 2.4.0
	 *
	 * @param array  $guids The GUIDs to correct.
	 * @param string $slug  The slug of the entity to fix the IDs for.
	 *
	 * @return array The fixed GUIDs.
	 */
	protected function fix_guid_types( $guids, $slug ) {

		foreach ( $guids as $entity_slug => $id ) {

			if ( $entity_slug === $slug && is_string( $id ) ) {
				$guids[ $entity_slug ] = (int) $id;
			} elseif ( is_array( $id ) ) {
				$guids[ $entity_slug ] = $this->fix_guid_types( $id, $slug );
			}
		}

		return $guids;
	}
}

// EOF
