<?php

/**
 * Test case for WordPoints_Hook_Hit_Query.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the WordPoints_Hook_Hit_Query class.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Hit_Query
 */
class WordPoints_Hook_Hit_Query_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test constructing the class.
	 *
	 * @since 2.1.0
	 */
	public function test_construct_defaults() {

		$query = new WordPoints_Hook_Hit_Query();

		$this->assertSame( 'date', $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test constructing the class with custom args.
	 *
	 * @since 2.1.0
	 */
	public function test_construct_with_args() {

		$query = new WordPoints_Hook_Hit_Query( array( 'order_by' => 'id' ) );

		$this->assertSame( 'id', $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test constructing the class with deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Hook_Hit_Query::__construct
	 */
	public function test_construct_with_deprecated_args() {

		$query = new WordPoints_Hook_Hit_Query(
			array(
				'primary_arg_guid'          => 'test',
				'primary_arg_guid__compare' => 'test_compare',
				'primary_arg_guid__in'      => 'test_in',
				'primary_arg_guid__not_in'  => 'test_not_in',
			)
		);

		$this->assertSame( 'test', $query->get_arg( 'signature_arg_guids' ) );
		$this->assertSame( 'test_compare', $query->get_arg( 'signature_arg_guids__compare' ) );
		$this->assertSame( 'test_in', $query->get_arg( 'signature_arg_guids__in' ) );
		$this->assertSame( 'test_not_in', $query->get_arg( 'signature_arg_guids__not_in' ) );
	}

	/**
	 * Test getting the deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Hook_Hit_Query::get_arg
	 */
	public function test_get_deprecated_args() {

		$query = new WordPoints_Hook_Hit_Query(
			array(
				'signature_arg_guids'          => 'test',
				'signature_arg_guids__compare' => 'test_compare',
				'signature_arg_guids__in'      => 'test_in',
				'signature_arg_guids__not_in'  => 'test_not_in',
			)
		);

		$this->assertSame( 'test', $query->get_arg( 'primary_arg_guid' ) );
		$this->assertSame( 'test_compare', $query->get_arg( 'primary_arg_guid__compare' ) );
		$this->assertSame( 'test_in', $query->get_arg( 'primary_arg_guid__in' ) );
		$this->assertSame( 'test_not_in', $query->get_arg( 'primary_arg_guid__not_in' ) );
	}

	/**
	 * Test setting the deprecated args.
	 *
	 * @since 2.3.0
	 *
	 * @expectedDeprecated WordPoints_Hook_Hit_Query::set_args
	 */
	public function test_set_deprecated_args() {

		$query = new WordPoints_Hook_Hit_Query();
		$query->set_args(
			array(
				'primary_arg_guid'          => 'test',
				'primary_arg_guid__compare' => 'test_compare',
				'primary_arg_guid__in'      => 'test_in',
				'primary_arg_guid__not_in'  => 'test_not_in',
			)
		);

		$this->assertSame( 'test', $query->get_arg( 'signature_arg_guids' ) );
		$this->assertSame( 'test_compare', $query->get_arg( 'signature_arg_guids__compare' ) );
		$this->assertSame( 'test_in', $query->get_arg( 'signature_arg_guids__in' ) );
		$this->assertSame( 'test_not_in', $query->get_arg( 'signature_arg_guids__not_in' ) );
	}

	/**
	 * Test that get() returns the matching rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$query = new WordPoints_Hook_Hit_Query();

		$this->insert_rows( 2 );

		$results = $query->get();

		$this->assertCount( 2, $results );
		$this->assertIsRow( $results[0] );
		$this->assertIsRow( $results[1] );
	}

	/**
	 * Test the meta_query arg.
	 *
	 * This is just a very basic test to make sure that WP_Meta_Query is indeed
	 * supported.
	 *
	 * @since 2.1.0
	 */
	public function test_meta_query_arg() {

		$this->insert_rows( 1, array(), array( 'test' => 1 ) );
		$this->insert_rows( 2, array(), array( 'test' => 5 ) );

		$query = new WordPoints_Hook_Hit_Query(
			array( 'meta_query' => array( array( 'key' => 'test', 'value' => 1 ) ) )
		);

		$this->assertSame( 1, $query->count() );
	}

	/**
	 * Test the 'date_query' args.
	 *
	 * This is just a very basic test to make sure that WP_Date_Query is indeed
	 * supported.
	 *
	 * @since 2.1.0
	 */
	public function test_date_query_arg() {

		$date = date( 'Y-m-d H:i:s', time() + MINUTE_IN_SECONDS );

		$this->insert_rows( 1 );
		$this->insert_rows( 1, array( 'date' => $date ) );

		$query = new WordPoints_Hook_Hit_Query(
			array( 'date_query' => array( array( 'before' => $date ) ) )
		);

		$this->assertSame( 1, $query->count() );
	}

	/**
	 * Insert some rows into the hits table.
	 *
	 * @since 2.1.0
	 *
	 * @param int   $count  The number of rows to insert.
	 * @param array $values The values for the columns.
	 * @param array $meta   Metadata for each row.
	 */
	protected function insert_rows( $count, $values = array(), $meta = array() ) {

		global $wpdb;

		$values = array_merge(
			array(
				'action_type'         => 'fire',
				'signature_arg_guids' => wp_json_encode(
					array( 'test_entity' => 1, 'site' => 1, 'network' => 1 )
				),
				'event'               => 'test_event',
				'reactor'             => 'test_reactor',
				'reaction_mode'       => 'standard',
				'reaction_store'      => 'test',
				'reaction_context_id' => wp_json_encode(
					array( 'site' => 1, 'network' => 1 )
				),
				'reaction_id'         => 1,
				'date'                => current_time( 'mysql', true ),
			)
			, $values
		);

		for ( $i = 0; $i < $count; $i++ ) {

			$wpdb->insert( $wpdb->wordpoints_hook_hits, $values );

			$id = $wpdb->insert_id;

			foreach ( $meta as $key => $value ) {
				add_metadata( 'wordpoints_hook_hit', $id, $key, $value );
			}
		}
	}

	/**
	 * Assert that a value matches the format of a table row.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $row The value that is expected to be a row from the hits table.
	 */
	protected function assertIsRow( $row ) {

		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertObjectHasAttribute( 'action_type', $row );
		$this->assertObjectHasAttribute( 'signature_arg_guids', $row );
		$this->assertObjectHasAttribute( 'event', $row );
		$this->assertObjectHasAttribute( 'reactor', $row );
		$this->assertObjectHasAttribute( 'reaction_mode', $row );
		$this->assertObjectHasAttribute( 'reaction_store', $row );
		$this->assertObjectHasAttribute( 'reaction_context_id', $row );
		$this->assertObjectHasAttribute( 'reaction_id', $row );
		$this->assertObjectHasAttribute( 'date', $row );
	}
}

// EOF
