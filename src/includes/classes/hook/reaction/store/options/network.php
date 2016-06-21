<?php

/**
 * Class for network option table hook reaction storage method.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Stores hook reaction settings in network options.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Reaction_Store_Options_Network extends WordPoints_Hook_Reaction_Store_Options {

	/**
	 * @since 2.1.0
	 */
	protected $context = 'network';

	/**
	 * @since 2.1.0
	 */
	protected $reaction_class = 'WordPoints_Hook_Reaction_Options';

	/**
	 * @since 2.1.0
	 */
	public function get_option( $name ) {
		return get_site_option( $name );
	}

	/**
	 * @since 2.1.0
	 */
	protected function add_option( $name, $value ) {
		return add_site_option( $name, $value );
	}

	/**
	 * @since 2.1.0
	 */
	public function update_option( $name, $value ) {
		return update_site_option( $name, $value );
	}

	/**
	 * @since 2.1.0
	 */
	protected function delete_option( $name ) {
		return delete_site_option( $name );
	}
}

// EOF
