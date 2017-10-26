<?php

/**
 * Test case for caps functions.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests the caps functions.
 *
 * @since 2.4.0
 */
class WordPoints_Caps_Functions_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests wordpoints_map_custom_meta_caps().
	 *
	 * @since 2.4.0
	 *
	 * @covers ::wordpoints_map_custom_meta_caps
	 *
	 * @expectedDeprecated current_user_can
	 *
	 * @dataProvider data_provider_deprecated_caps
	 *
	 * @param string $cap The capability.
	 */
	public function test_mapping_deprecated_caps( $cap ) {

		$this->give_current_user_caps( array_keys( wordpoints_get_custom_caps() ) );

		$this->assertFalse( current_user_can( $cap ) );
	}

	/**
	 * Provides deprecated capabilities.
	 *
	 * @since 2.4.0
	 *
	 * @return string[][] Deprecated capabilities.
	 */
	public function data_provider_deprecated_caps() {
		return array(
			'install_wordpoints_modules'        => array( 'install_wordpoints_modules' ),
			'manage_network_wordpoints_modules' => array( 'manage_network_wordpoints_modules' ),
			'activate_wordpoints_modules'       => array( 'activate_wordpoints_modules' ),
			'delete_wordpoints_modules'         => array( 'install_wordpoints_modules' ),
		);
	}
}

// EOF
