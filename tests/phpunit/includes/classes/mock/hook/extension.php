<?php

/**
 * Mock hook extension class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock hook extension class for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Hook_Extension
	extends WordPoints_Hook_Extension
	implements WordPoints_Hook_Extension_Hit_ListenerI {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'test_extension';

	/**
	 * Whether the event should hit the target.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	public $should_hit = true;

	/**
	 * The args passed to should_hit() each time it was called.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	public $hit_checks = array();

	/**
	 * The args passed to after_hit() each time it was called.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	public $hits = array();

	/**
	 * The settings passed to the validator each time it was called.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	public $validations = array();

	/**
	 * A list of settings updates this extension has received.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	public $updates = array();

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {

		$this->hit_checks[] = $fire;

		return $this->should_hit;
	}

	/**
	 * Validates the extensions settings.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The extension's settings.
	 *
	 * @return array The validated settings.
	 */
	protected function validate_action_type_settings( $settings ) {

		$this->validations[] = array(
			'settings' => $settings,
			'validator' => $this->validator,
			'event_args' => $this->event_args,
			'field_stack' => $this->validator->get_field_stack(),
		);

		if ( ! empty( $settings['fail'] ) ) {
			$this->validator->add_error( $settings['fail'], 'fail' );
			$settings = array();
		}

		return $settings;
	}

	/**
	 * @since 2.1.0
	 */
	public function update_settings( WordPoints_Hook_ReactionI $reaction, array $settings ) {

		$this->updates[] = array( 'reaction' => $reaction, 'settings' => $settings );

		parent::update_settings( $reaction, $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function after_hit( WordPoints_Hook_Fire $fire ) {
		$this->hits[] = $fire;
	}
}

// EOF
