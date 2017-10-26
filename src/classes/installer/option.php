<?php

/**
 * Option installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Installer for an option.
 *
 * @since 2.4.0
 */
class WordPoints_Installer_Option implements WordPoints_RoutineI {

	/**
	 * The option name.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * The option value.
	 *
	 * @since ${PROJECT_VERSION}
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Whether add this as a network option or a regular option.
	 *
	 * @since ${PROJECT_VERSION}
	 *
	 * @var bool
	 */
	protected $network;

	/**
	 * @since 2.4.0
	 *
	 * @param string $option  The option name.
	 * @param mixed  $value   The option value.
	 * @param bool   $network Whether to add a network or regular option.
	 */
	public function __construct( $option, $value, $network = false ) {

		$this->option  = $option;
		$this->value   = $value;
		$this->network = $network;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {
		wordpoints_add_maybe_network_option(
			$this->option
			, $this->value
			, $this->network
		);
	}
}

// EOF
