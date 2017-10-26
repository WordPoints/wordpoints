<?php

/**
 * Int entity GUIDs log meta updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates entity GUIDs in the points log meta table to be integers.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_Log_Meta_Entity_GUIDs_Int
	extends WordPoints_Updater_Hook_Hits_Signature_Arg_GUIDs_Int {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		foreach ( $this->entity_slugs as $slug ) {

			$meta_key = "{$slug}_guid";

			$query = new WordPoints_Points_Logs_Query(
				array(
					'fields'       => 'id',
					'meta_key'     => $meta_key,
					'meta_value'   => '{' . wp_json_encode( $slug ) . ':"',
					'meta_compare' => 'LIKE',
				)
			);

			foreach ( $query->get( 'col' ) as $log_id ) {

				$guids = wordpoints_get_points_log_meta( $log_id, $meta_key, true );
				$guids = json_decode( $guids, true );
				$guids = $this->fix_guid_types( $guids, $slug );

				wordpoints_update_points_log_meta( $log_id, $meta_key, wp_json_encode( $guids ) );
			}
		}
	}
}

// EOF
