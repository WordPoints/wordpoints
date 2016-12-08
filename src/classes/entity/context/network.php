<?php

/**
 * Network entity context class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the network context.
 *
 * On multisite installs there are many "sites" on a "network".
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Context_Network extends WordPoints_Entity_Context {

	/**
	 * @since 2.1.0
	 */
	public function get_current_id() {
		return get_current_network_id();
	}

	/**
	 * @since 2.2.0
	 */
	public function switch_to( $id ) {

		if ( function_exists( 'switch_to_network' ) ) {
			return switch_to_network( $id );
		}

		return false;
	}

	/**
	 * @since 2.2.0
	 */
	public function switch_back() {

		if ( function_exists( 'restore_current_network' ) ) {
			return restore_current_network();
		}

		return false;
	}
}

// EOF
