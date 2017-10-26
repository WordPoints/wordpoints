<?php

/**
 * Test case for wordpoints_map_context_shortcuts().
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests wordpoints_map_context_shortcuts().
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_map_context_shortcuts
 */
class WordPoints_Map_Context_Shortcuts_Functions_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests mapping the contexts.
	 *
	 * @since 2.4.0
	 *
	 * @dataProvider data_provider_context_shortcuts
	 *
	 * @param string   $shortcut A context shortcut.
	 * @param string[] $contexts The contexts that the shortcut maps to.
	 */
	public function test_map_contexts( $shortcut, $contexts = null ) {

		if ( ! isset( $contexts ) ) {
			$contexts = array( $shortcut );
		}

		$array              = array();
		$array[ $shortcut ] = 'value';

		$results = wordpoints_map_context_shortcuts( $array );

		$this->assertSame( array_fill_keys( $contexts, 'value' ), $results );
	}

	/**
	 * Provides a list of shortcuts and the contexts that they map to.
	 *
	 * @since 2.4.0
	 *
	 * @return array[] Shortcuts and the contexts that they are for.
	 */
	public function data_provider_context_shortcuts() {
		return array(
			'single'    => array( 'single' ),
			'site'      => array( 'site' ),
			'network'   => array( 'network' ),
			'local'     => array( 'local', array( 'single', 'site' ) ),
			'global'    => array( 'global', array( 'single', 'network' ) ),
			'universal' => array( 'universal', array( 'single', 'site', 'network' ) ),
		);
	}

	/**
	 * Tests that context shortcuts don't overwrite the contexts but are merged.
	 *
	 * @since 2.4.0
	 */
	public function test_merged() {

		$array           = array();
		$array['single'] = array( 'test' => '' );
		$array['local']  = array( 'another' => '' );

		$results = wordpoints_map_context_shortcuts( $array );

		$this->assertSame(
			array(
				'single' => array( 'test' => '', 'another' => '' ),
				'site'   => array( 'another' => '' ),
			)
			, $results
		);
	}
}

// EOF
