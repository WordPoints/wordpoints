<?php

/**
 * Test case for wordpoints_entities_get_current_context_id().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests wordpoints_entities_get_current_context_id().
 *
 * @since 2.1.0
 *
 * @covers ::wordpoints_entities_get_current_context_id
 */
class WordPoints_Entities_Get_Current_Context_ID_Functions_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test getting the current context id.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_contexts
	 */
	public function test_get_current_context_id( $context, $id ) {

		$this->assertSame(
			$id
			, wordpoints_entities_get_current_context_id( $context )
		);
	}

	/**
	 * Provides sets of slugs and the expected values.
	 *
	 * @since 2.1.0
	 */
	public function data_provider_contexts() {
		return array(
			'empty'        => array( '', array() ),
			'single'       => array( 'network', array( 'network' => 1 ) ),
			'multiple'     => array( 'site', array( 'site' => 1, 'network' => 1 ) ),
			'unregistered' => array( 'unregistered', false ),
		);
	}

	/**
	 * Test getting the current context id when out of scope of a context.
	 *
	 * @since 2.1.0
	 */
	public function test_get_current_context_id_out_of_context() {

		$this->mock_apps();

		$entities = wordpoints_entities();
		$entities->register(
			'test'
			, 'WordPoints_PHPUnit_Mock_Entity_Context_OutOfState'
		);

		$this->assertFalse(
			wordpoints_entities_get_current_context_id( 'test' )
		);
	}
}

// EOF
