<?php

/**
 * String contains hook condition class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * A hook condition that requires a string to contain a substring.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Condition_String_Contains extends WordPoints_Hook_Condition {

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Contains', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_settings_fields() {
		return array(
			'value' => array(
				'type'     => 'text',
				'label'    => __( 'Word or phrase (case sensitive)', 'wordpoints' ),
				'required' => true,
			),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function is_met( array $settings, WordPoints_Hook_Event_Args $args ) {

		return false !== strpos(
			$args->get_current()->get_the_value()
			, $settings['value']
		);
	}
}

// EOF
