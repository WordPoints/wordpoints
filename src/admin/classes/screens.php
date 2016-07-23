<?php

/**
 * Administration screens class.
 *
 * @package WordPoints\Administration
 * @since 2.1.0
 */

/**
 * Handles the display of administration screens.
 *
 * @since 2.1.0
 */
class WordPoints_Admin_Screens extends WordPoints_Class_Registry {

	/**
	 * The object for the current administration screen.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Admin_Screen
	 */
	protected $current_screen;

	/**
	 * @since 2.1.0
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'set_current_screen' ) );
	}

	/**
	 * Set the current screen.
	 *
	 * @since 2.1.0
	 *
	 * @param WP_Screen $current_screen The WP_Screen object for the current screen.
	 */
	public function set_current_screen( $current_screen ) {

		$screen_id = $current_screen->id;

		if ( is_network_admin() ) {
			$screen_id = substr( $screen_id, 0, -8 /* -network */ );
		}

		$screen = $this->get( $screen_id );

		if ( ! ( $screen instanceof WordPoints_Admin_Screen ) ) {
			return;
		}

		$this->current_screen = $screen;

		add_action( "load-{$screen_id}", array( $this->current_screen, 'load' ) );
	}

	/**
	 * Display the current screen.
	 *
	 * @since 2.1.0
	 */
	public function display() {

		$this->current_screen->display();
	}
}

// EOF
