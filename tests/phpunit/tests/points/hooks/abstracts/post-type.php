<?php

/**
 * Testcase for the post type base points hook class.
 *
 * @package WordPoints\Tests
 * @since 1.9.0
 */

/**
 * Test the post type points hook base class.
 *
 * @since 1.9.0
 *
 * @group points
 * @group points_hooks
 */
class WordPoints_Post_Type_Points_Hook_Base_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that it is possible to disable auto-reversal when the label is set.
	 *
	 * @since 1.9.0
	 *
	 * @covers WordPoints_Post_Type_Points_Hook_Base::form()
	 */
	public function test_auto_reverse_disable_form_checkbox() {

		$xpath = $this->get_form_xpath(
			array( 'disable_auto_reverse_label' => 'Test label' )
		);

		$input = $xpath->query(
			'//input[@id = "hook-wordpoints_phpunit_mock_points_hook_post_type-__i__-auto_reverse"]'
		);
		$this->assertSame( 1, $input->length );

		$label = $xpath->query(
			'//label[@for = "hook-wordpoints_phpunit_mock_points_hook_post_type-__i__-auto_reverse"]'
		);
		$this->assertSame( 'Test label', $label->item( 0 )->textContent );
	}

	/**
	 * Test that auto-reversal setting is off by default.
	 *
	 * @since 1.9.0
	 *
	 * @covers WordPoints_Post_Type_Points_Hook_Base::form()
	 */
	public function test_auto_reverse_not_disabled_by_default() {

		$xpath = $this->get_form_xpath(
			array( 'disable_auto_reverse_label' => 'Test label' )
		);

		$input = $xpath->query(
			'//input[@id = "hook-wordpoints_phpunit_mock_points_hook_post_type-__i__-auto_reverse"]'
		);
		$this->assertSame( 1, $input->length );

		$value = $input->item( 0 )->attributes->getNamedItem( 'checked' )->nodeValue;
		$this->assertSame( 'checked', $value );
	}

	/**
	 * Test that auto-reversal checkbox is off when auto-reversal is disabled.
	 *
	 * @since 1.9.0
	 *
	 * @covers WordPoints_Post_Type_Points_Hook_Base::form()
	 */
	public function test_auto_reverse_disabled_checkbox_off() {

		$xpath = $this->get_form_xpath(
			array( 'disable_auto_reverse_label' => 'Test label' )
			, array( 'auto_reverse' => 0 )
		);

		$input = $xpath->query(
			'//input[@id = "hook-wordpoints_phpunit_mock_points_hook_post_type-1-auto_reverse"]'
		);
		$this->assertSame( 1, $input->length );

		$this->assertSame(
			null
			, $input->item( 0 )->attributes->getNamedItem( 'checked' )
		);
	}

	/**
	 * Test that it isn't possible to disable auto-reversal if the label is not set.
	 *
	 * @since 1.9.0
	 *
	 * @covers WordPoints_Post_Type_Points_Hook_Base::form()
	 */
	public function test_auto_reverse_cant_be_disabled_by_default() {

		$xpath = $this->get_form_xpath();

		$input = $xpath->query(
			'//input[@id = "hook-wordpoints_phpunit_mock_points_hook_post_type-__i__-auto_reverse"]'
		);
		$this->assertSame( 0, $input->length );
	}

	//
	// Helpers.
	//

	/**
	 * Load the form for the hook into an XPath query object.
	 *
	 * @since 1.9.0
	 *
	 * @param array $args     The args to pass to the hook object.
	 * @param array $instance The settings for the instance to display the form for.
	 *
	 * @return DOMXPath An XPath object loaded with the form.
	 */
	protected function get_form_xpath( array $args = array(), array $instance = array() ) {

		$hook = new WordPoints_PHPUnit_Mock_Points_Hook_Post_Type( __METHOD__, $args );

		$number = 0;

		if ( ! empty( $instance ) ) {

			$instance = array_merge( array( 'points' => 10 ), $instance );
			$hook->update_callback( $instance, 1 );
			$number = 1;
		}

		ob_start();
		$hook->form_callback( $number );
		$form = ob_get_clean();

		$document = new DOMDocument;
		$document->loadHTML( $form );
		$xpath = new DOMXPath( $document );

		return $xpath;
	}
}

// EOF
