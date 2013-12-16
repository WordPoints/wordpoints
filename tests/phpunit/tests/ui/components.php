<?php

/**
 * Test component activation.
 *
 * @package WordPoints\Tests
 * @since 1.0.1
 */

/**
 * Components UI test case.
 *
 * @since 1.0.1
 *
 * @group ui
 */
class WordPoints_Component_UI_Test extends WordPoints_Selenium2TestCase {

	/**
	 * The user requires the manage_options capability.
	 *
	 * @since 1.0.1
	 *
	 * @type array $user_capabilities
	 */
	protected $user_capabilities = array( 'manage_options' => true );

	/**
	 * Set up before the tests.
	 *
	 * @since 1.0.1
	 */
	public function setUp() {

		parent::setUp();

		delete_option( 'wordpoints_active_components' );

		remove_filter( 'wordpoints_component_active', '__return_true', 100 );
	}

	/**
	 * Test activation/deactivation of a component.
	 *
	 * @since 1.0.1
	 */
	public function test_activation_deactivation() {

		$this->url(
			add_query_arg(
				array(
					'page' => 'wordpoints_configure',
					'tab'  => 'components',
				)
				,admin_url()
			)
		);

		// Activate the points component.
		$this->clickOnElement( 'wordpoints-component-activate_points' );
		$this->flush_cache();
		$this->assertTrue( wordpoints_component_is_active( 'points' ) );

		// Deactivate it.
		$this->clickOnElement( 'wordpoints-component-deactivate_points' );
		$this->flush_cache();
		$this->assertFalse( wordpoints_component_is_active( 'points' ) );
	}

	/**
	 * Clean up after the test.
	 *
	 * @since 1.0.1
	 */
	public function tearDown() {

		delete_option( 'wordpoints_active_components' );

		add_filter( 'wordpoints_component_active', '__return_true', 100 );

		parent::tearDown();
	}
}

// end of file /tests/phpunit/tests/ui/components.php