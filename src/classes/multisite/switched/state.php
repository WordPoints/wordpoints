<?php

/**
 * Multisite switched state class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Represents the current switched state on multisite.
 *
 * Can be used to back up the current switched state, and then restore it later
 * without having to call `restore_current_blog()` after every call to
 * `switch_to_blog()`.
 *
 * @since 2.2.0
 */
class WordPoints_Multisite_Switched_State {

	/**
	 * Used to backup values so that they can be restored later.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	protected $backup = array();

	/**
	 * Backup the globals that determine the current site and switched state.
	 *
	 * @since 2.2.0
	 *
	 * @link https://wordpress.stackexchange.com/a/89114/27757
	 *
	 * @return int The current site ID.
	 */
	public function backup() {

		$this->backup['original_blog_id'] = get_current_blog_id();

		if ( isset( $GLOBALS['_wp_switched_stack'] ) ) {
			$this->backup['switched_stack'] = $GLOBALS['_wp_switched_stack'];
		}

		if ( isset( $GLOBALS['switched'] ) ) {
			$this->backup['switched'] = $GLOBALS['switched'];
		}

		return $this->backup['original_blog_id'];
	}

	/**
	 * Restore the globals that determine the current site and switched state.
	 *
	 * @since 2.2.0
	 *
	 * @link https://wordpress.stackexchange.com/a/89114/27757
	 *
	 * @return int The original site ID.
	 */
	public function restore() {

		switch_to_blog( $this->backup['original_blog_id'] );

		if ( isset( $this->backup['switched_stack'] ) ) {
			$GLOBALS['_wp_switched_stack'] = $this->backup['switched_stack'];
		} else {
			unset( $GLOBALS['_wp_switched_stack'] );
		}

		if ( isset( $this->backup['switched'] ) ) {
			$GLOBALS['switched'] = $this->backup['switched'];
		} else {
			unset( $GLOBALS['switched'] );
		}

		return $this->backup['original_blog_id'];
	}
}

// EOF
