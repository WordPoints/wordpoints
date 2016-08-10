<?php

/**
 * Mock hooks app class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock hooks app class for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hooks extends WordPoints_Hooks {

	/**
	 * A list of event fires.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	public $fires = array();

	/**
	 * @since 2.1.0
	 */
	public function fire(
		$event_slug,
		WordPoints_Hook_Event_Args $event_args,
		$action_type
	) {

		$this->fires[] = array(
			'action_type' => $action_type,
			'event_args'  => $event_args,
			'event_slug'  => $event_slug,
		);

		parent::fire( $event_slug, $event_args, $action_type );
	}
}

// EOF
