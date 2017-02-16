<?php

/**
 * Blocker hook extension class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Blocks a fire from hitting.
 *
 * Useful when you want to block fires of a specific action type for a reaction.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Extension_Blocker extends WordPoints_Hook_Extension {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'blocker';

	/**
	 * @since 2.3.0
	 */
	protected function validate_extension_settings( $settings ) {

		if ( ! is_array( $settings ) ) {
			return wp_validate_boolean( $settings );
		}

		return parent::validate_extension_settings( $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function validate_action_type_settings( $settings ) {
		return (bool) $settings;
	}

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {
		return ! $this->get_settings_from_fire( $fire );
	}
}

// EOF
