<?php

/**
 * Site entity context class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the site context.
 *
 * On multisite installs there are many "sites" on a "network".
 *
 * @since 2.1.0
 */
class WordPoints_Entity_Context_Site extends WordPoints_Entity_Context {

	/**
	 * @since 2.1.0
	 */
	protected $parent_slug = 'network';

	/**
	 * @since 2.1.0
	 */
	public function get_current_id() {

		if ( ! is_multisite() ) {
			return 1;
		}

		if ( wordpoints_is_network_context() ) {
			return false;
		}

		return get_current_blog_id();
	}

	/**
	 * @since 2.2.0
	 */
	public function switch_to( $id ) {

		if ( ! is_multisite() ) {
			return false;
		}

		return switch_to_blog( $id );
	}

	/**
	 * @since 2.2.0
	 */
	public function switch_back() {

		if ( ! is_multisite() ) {
			return false;
		}

		return restore_current_blog();
	}
}

// EOF
