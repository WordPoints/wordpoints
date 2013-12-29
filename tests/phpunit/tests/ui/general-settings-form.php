<?php

/**
 * Test the general settings form.
 *
 * @package WordPoints\Tests
 * @since 1.0.1
 */

if ( ! class_exists( 'WordPoints_Selenium2TestCase' ) ) {
	return;
}

/**
 * General settings form test case.
 *
 * @since 1.0.1
 *
 * @group ui
 */
class WordPoints_General_Settings_Form_Test extends WordPoints_Selenium2TestCase {

	/**
	 * The user requires the manage_options capability.
	 *
	 * @since 1.0.1
	 *
	 * @type array $user_capabilities
	 */
	protected $user_capabilities = array( 'manage_options' => true );

	/**
	 * Test the excluded users input.
	 *
	 * @since 1.0.1
	 */
	public function test_excluded_users_input() {

		$this->url(
			add_query_arg(
				array(
					'page' => 'wordpoints_configure',
					'tab'  => 'general',
				)
				,admin_url()
			)
		);

		$this->byId( 'excluded_users' )->value( '5, 45, l, 7lkj, aa  s,, ,alkj,5' );

		// Submit the form.
		$this->clickOnElement( 'submit' );

		$this->assertEquals( array( '5', '45' ), wordpoints_get_excluded_users( '_tests' ) );
	}

	/**
	 * Clean up after tests.
	 *
	 * @since 1.0.1
	 */
	public function tearDown() {

		wordpoints_delete_network_option( 'wordpoints_excluded_users' );

		parent::tearDown();
	}
}

// end of file /tests/phpunit/tests/ui/general-settings-form.php