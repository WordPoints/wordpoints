<?php

/**
 * Test case for WordPoints_DB_Query.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the WordPoints_DB_Query class.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_DB_Query
 */
class WordPoints_DB_Query_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 2.1.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		global $wpdb;

		$wpdb->query(
			"
				CREATE TABLE {$wpdb->prefix}wordpoints_db_query_test (
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					int_col BIGINT(20) NOT NULL,
					text_col TEXT,
					date_col DATETIME,
					PRIMARY KEY (id)
				)
			"
		);

		$wpdb->query(
			"
				CREATE TABLE {$wpdb->prefix}wordpoints_db_query_testmeta (
					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					wordpoints_db_query_test_id BIGINT(20) UNSIGNED NOT NULL,
					meta_key VARCHAR(255) NOT NULL,
					meta_value LONGTEXT,
					PRIMARY KEY (meta_id)
				  )
			"
		);
	}

	/**
	 * @since 2.1.0
	 */
	public static function tearDownAfterClass() {

		global $wpdb;

		$wpdb->query( "DROP TABLE {$wpdb->prefix}wordpoints_db_query_test" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}wordpoints_db_query_testmeta" );

		parent::tearDownAfterClass();
	}

	/**
	 * Test constructing the class.
	 *
	 * @since 2.1.0
	 */
	public function test_construct_defaults() {

		$query = new WordPoints_DB_Query();

		$this->assertEquals( 0,      $query->get_arg( 'start' ) );
		$this->assertEquals( 'DESC', $query->get_arg( 'order' ) );
	}

	/**
	 * Test constructing the class with custom args.
	 *
	 * @since 2.1.0
	 */
	public function test_construct_with_args() {

		$query = new WordPoints_DB_Query( array( 'start' => 10, 'custom' => 'a' ) );

		$this->assertEquals( 10,     $query->get_arg( 'start' ) );
		$this->assertEquals( 'DESC', $query->get_arg( 'order' ) );
		$this->assertEquals( 'a',    $query->get_arg( 'custom' ) );
	}

	/**
	 * Test getting an arg that doesn't exit.
	 *
	 * @since 2.1.0
	 */
	public function test_get_nonexistent_arg() {

		$query = new WordPoints_DB_Query();

		$this->assertNull( $query->get_arg( 'nonexistent' ) );
	}

	/**
	 * Test that set_args() modifies the query args.
	 *
	 * @since 2.1.0
	 */
	public function test_set_args() {

		$query = new WordPoints_DB_Query( array( 'start' => 10, 'custom' => 'a' ) );

		$this->assertEquals( 10,     $query->get_arg( 'start' ) );
		$this->assertEquals( 'DESC', $query->get_arg( 'order' ) );
		$this->assertEquals( 'a',    $query->get_arg( 'custom' ) );

		$query->set_args( array( 'order' => 'ASC', 'custom' => 'b' ) );

		$this->assertEquals( 10,    $query->get_arg( 'start' ) );
		$this->assertEquals( 'ASC', $query->get_arg( 'order' ) );
		$this->assertEquals( 'b',   $query->get_arg( 'custom' ) );
	}

	/**
	 * Test that set_args() resets the query if it has already been prepared.
	 *
	 * @since 2.1.0
	 */
	public function test_set_args_modifies_query() {

		$query = new WordPoints_DB_Query();

		$this->assertStringNotMatchesFormat( '%aLIMIT 0, 1', $query->get_sql() );

		$query->set_args( array( 'limit' => 1 ) );

		$this->assertStringMatchesFormat( '%aLIMIT 0, 1', $query->get_sql() );
	}

	/**
	 * Test that set_args() returns the query instance.
	 *
	 * @since 2.1.0
	 */
	public function test_set_args_returns_query() {

		$query = new WordPoints_DB_Query( array( 'start' => 10, 'custom' => 'a' ) );

		$return = $query->set_args( array( 'order' => 'ASC', 'custom' => 'b' ) );

		$this->assertTrue( $return === $query );
	}

	/**
	 * Test that count() returns the number of matching rows.
	 *
	 * @since 2.1.0
	 */
	public function test_count() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->insert_rows( 1 );

		$this->assertEquals( 1, $query->count() );
	}

	/**
	 * Test that count() returns 0 when there are no rows.
	 *
	 * @since 2.1.0
	 */
	public function test_count_no_rows() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertEquals( 0, $query->count() );
	}

	/**
	 * Test that get() returns the matching rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->insert_rows( 2 );

		$results = $query->get();

		$this->assertCount( 2, $results );
		$this->assertIsRow( $results[0] );
		$this->assertIsRow( $results[1] );
	}

	/**
	 * Test that get() returns an empty array when there are no rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_no_rows() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertSame( array(), $query->get() );
	}

	/**
	 * Test that get() results returns the matching rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_results() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->insert_rows( 2 );

		$results = $query->get( 'results' );

		$this->assertCount( 2, $results );
		$this->assertIsRow( $results[0] );
		$this->assertIsRow( $results[1] );
	}

	/**
	 * Test that get() results returns an empty array when there are no rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_results_no_rows() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertSame( array(), $query->get( 'results' ) );
	}

	/**
	 * Test that get() row returns the first matching row.
	 *
	 * @since 2.1.0
	 */
	public function test_get_row() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->insert_rows( 2 );

		$row = $query->get( 'row' );

		$this->assertIsRow( $row );
	}

	/**
	 * Test that get() results returns an empty array when there are no rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_row_no_rows() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertNull( $query->get( 'row' ) );
	}

	/**
	 * Test that get() col returns the matching rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_col() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->insert_rows( 2 );

		$results = $query->get( 'col' );

		$this->assertCount( 2, $results );

		$this->assertInternalType( 'string', $results[0] );
		$this->assertStringMatchesFormat( '%d', $results[0] );

		$this->assertInternalType( 'string', $results[1] );
		$this->assertStringMatchesFormat( '%d', $results[1] );
	}

	/**
	 * Test that get() col returns an empty array when there are no rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_col_no_rows() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertSame( array(), $query->get( 'col' ) );
	}

	/**
	 * Test that get() var returns a value.
	 *
	 * @since 2.1.0
	 */
	public function test_get_var() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->insert_rows( 2 );

		$row = $query->get( 'var' );

		$this->assertInternalType( 'string', $row );
		$this->assertStringMatchesFormat( '%d', $row );
	}

	/**
	 * Test that get() var returns an empty array when there are no rows.
	 *
	 * @since 2.1.0
	 */
	public function test_get_var_no_rows() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertNull( $query->get( 'var' ) );
	}

	/**
	 * Test that get() with an invalid method returns false.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::get
	 */
	public function test_get_invalid() {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$this->assertFalse( $query->get( 'invalid' ) );
	}

	/**
	 * Test that get_sql() returns the SQL for the query.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_get_sql
	 *
	 * @param array  $args   The args to pass to get_sql().
	 * @param string $format The expected format of the resulting query.
	 */
	public function test_get_sql( $args, $format = null ) {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		if ( null === $format ) {
			$format = "SELECT `id`%s\nFROM `%swordpoints_db_query_test`\n%A";
		}

		$this->assertStringMatchesFormat(
			$format
			, call_user_func_array( array( $query, 'get_sql' ), $args )
		);
	}

	/**
	 * Provides sets of args for get_sql() and expected output formats.
	 *
	 * @since 2.1.0
	 *
	 * @return array The args and expected formats.
	 */
	public function data_provider_get_sql() {
		return array(
			'empty' => array( array() ),
			'SELECT' => array( array( 'SELECT' ) ),
			'invalid' => array( array( 'invalid' ) ),
			'SELECT COUNT' => array(
				array( 'SELECT COUNT' ),
				"SELECT COUNT(*)\nFROM `%swordpoints_db_query_test`\n%A",
			),
		);
	}

	/**
	 * Test that date_query_valid_columns_filter() adds date columns to the list.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_date_query_valid_columns_filter
	 *
	 * @param string[] $columns The column list to filter.
	 */
	public function test_date_query_valid_columns_filter( $columns ) {

		$query = new WordPoints_PHPUnit_Mock_DB_Query();

		$date_columns = $columns;
		$date_columns[] = 'date_col';

		$this->assertEquals(
			$date_columns
			, $query->date_query_valid_columns_filter( $columns )
		);
	}

	/**
	 * Provides sets of columns to pass to the date query filter.
	 *
	 * @since 2.1.0
	 *
	 * @return string[][][] The lists of columns.
	 */
	public function data_provider_date_query_valid_columns_filter() {
		return array(
			'empty' => array( array() ),
			'one' => array( array( 'another_col' ) ),
			'several' => array( array( 'one_col', 'another_col' ) ),
		);
	}

	/**
	 * Test that date_query_valid_columns_filter() adds date columns to the list.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_date_query_valid_columns_filter
	 *
	 * @param string[] $columns The column list to filter.
	 */
	public function test_date_query_valid_columns_filter_no_date_columns( $columns ) {

		$query = new WordPoints_DB_Query();

		$this->assertEquals(
			$columns
			, $query->date_query_valid_columns_filter( $columns )
		);
	}

	/**
	 * Test the 'fields' query arg as a string.
	 *
	 * @since 2.1.0
	 */
	public function test_fields_query_arg() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query( array( 'fields' => 'id' ) );

		$result = $query->get();

		$this->assertCount( 2, $result );

		$row = $result[0];
		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertCount( 1, (array) $row );

		$row = $result[1];
		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertCount( 1, (array) $row );
	}

	/**
	 * Test the 'fields' query arg as an array.
	 *
	 * @since 2.1.0
	 */
	public function test_fields_query_arg_array() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'fields' => array( 'id', 'int_col' ) )
		);

		$result = $query->get();

		$this->assertCount( 2, $result );

		$row = $result[0];
		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertObjectHasAttribute( 'int_col', $row );
		$this->assertCount( 2, (array) $row );

		$row = $result[1];
		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertObjectHasAttribute( 'int_col', $row );
		$this->assertCount( 2, (array) $row );
	}

	/**
	 * Test the 'fields' query arg as a string when it is invalid.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::prepare_select
	 */
	public function test_fields_query_arg_invalid() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'fields' => 'invalid' )
		);

		$result = $query->get();

		$this->assertCount( 2, $result );
		$this->assertIsRow( $result[0] );
		$this->assertIsRow( $result[1] );
	}

	/**
	 * Test the 'fields' query arg as an array when some of them are invalid.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::prepare_select
	 */
	public function test_fields_query_arg_array_invalid() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'fields' => array( 'id', 'invalid', 'int_col', 'bad' ) )
		);

		$result = $query->get();

		$this->assertCount( 2, $result );

		$row = $result[0];
		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertObjectHasAttribute( 'int_col', $row );
		$this->assertCount( 2, (array) $row );

		$row = $result[1];
		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertObjectHasAttribute( 'int_col', $row );
		$this->assertCount( 2, (array) $row );
	}

	/**
	 * Test the 'limit' query arg.
	 *
	 * @since 2.1.0
	 */
	public function test_limit_query_arg() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query( array( 'limit' => 1 ) );

		$this->assertEquals( 1, count( $query->get() ) );
	}

	/**
	 * Test the 'limit' query arg with an invalid value.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::prepare_limit
	 */
	public function test_limit_query_arg_invalid() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'limit' => 'invalid' )
		);

		$this->assertEquals( 2, count( $query->get() ) );
	}

	/**
	 * Test the 'limit' query arg with an invalid value.
	 *
	 * @since 2.1.0
	 */
	public function test_limit_query_arg_negative() {

		$this->insert_rows( 2 );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'limit' => -1 )
		);

		$this->assertEquals( 2, count( $query->get() ) );
	}

	/**
	 * Test the 'start' query arg.
	 *
	 * @since 2.1.0
	 */
	public function test_start_query_arg() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'start'    => 1,
				'limit'    => 2,
				'order_by' => 'int_col',
			)
		);

		$result = $query->get();

		$this->assertEquals( 1, count( $result ) );

		// Remember, order is descending, so this would be the second result.
		$this->assertEquals( 1, $result[0]->int_col );
	}

	/**
	 * Test the 'start' query arg when it is invalid.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::prepare_limit
	 */
	public function test_start_query_arg_invalid() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'start'    => 'invalid',
				'limit'    => 2,
				'order_by' => 'int_col',
			)
		);

		$result = $query->get();

		$this->assertEquals( 2, count( $result ) );
	}

	/**
	 * Test the 'start' query arg when it is negative.
	 *
	 * @since 2.1.0
	 */
	public function test_start_query_arg_negative() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'start'    => -3,
				'limit'    => 2,
				'order_by' => 'int_col',
			)
		);

		$result = $query->get();

		$this->assertEquals( 2, count( $result ) );
	}

	/**
	 * Test that the 'start' query arg is ignored when the limit isn't set.
	 *
	 * @since 2.1.0
	 */
	public function test_start_query_arg_limit_not_set() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'start'    => 1,
				'order_by' => 'int_col',
			)
		);

		$result = $query->get();

		$this->assertEquals( 2, count( $result ) );
	}

	/**
	 * Test there is no ordering by default.
	 *
	 * @since 2.1.0
	 */
	public function test_no_order_by_default() {

		$query = new WordPoints_DB_Query();

		$this->assertStringNotMatchesFormat( '%aORDER BY%a', $query->get_sql() );
	}

	/**
	 * Test the 'order_by' query arg.
	 *
	 * @since 2.1.0
	 */
	public function test_order_by_query_arg() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'order_by' => 'int_col' )
		);

		$result = $query->get();

		// Order is descending by default.
		$this->assertEquals( 3, $result[0]->int_col );
		$this->assertEquals( 2, $result[1]->int_col );
		$this->assertEquals( 1, $result[2]->int_col );
	}

	/**
	 * Test the 'order_by' query arg when it is 'meta_value'.
	 *
	 * @since 2.1.0
	 */
	public function test_order_by_query_arg_meta_value() {

		$this->insert_rows( 1, array(), array( 'test' => 1 ) );
		$this->insert_rows( 1, array(), array( 'test' => 3 ) );
		$this->insert_rows( 1, array(), array( 'test' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'meta_key' => 'test',
				'order_by' => 'meta_value',
			)
		);

		$result = $query->get();

		// Order is descending by default.
		$this->assertEquals(
			3
			, get_metadata( 'wordpoints_db_query_test', $result[0]->id, 'test', true )
		);

		$this->assertEquals(
			2
			, get_metadata( 'wordpoints_db_query_test', $result[1]->id, 'test', true )
		);

		$this->assertEquals(
			1
			, get_metadata( 'wordpoints_db_query_test', $result[2]->id, 'test', true )
		);
	}

	/**
	 * Test the 'order_by' query arg when it is 'meta_value'.
	 *
	 * @since 2.1.0
	 */
	public function test_order_by_query_arg_meta_value_meta_type() {

		$this->insert_rows( 1, array(), array( 'test' => 1 ) );
		$this->insert_rows( 1, array(), array( 'test' => 100 ) );
		$this->insert_rows( 1, array(), array( 'test' => 10 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'meta_key'  => 'test',
				'meta_type' => 'UNSIGNED',
				'order_by'  => 'meta_value',
			)
		);

		$result = $query->get();

		// Order is descending by default.
		$this->assertEquals(
			100
			, get_metadata( 'wordpoints_db_query_test', $result[0]->id, 'test', true )
		);

		$this->assertEquals(
			10
			, get_metadata( 'wordpoints_db_query_test', $result[1]->id, 'test', true )
		);

		$this->assertEquals(
			1
			, get_metadata( 'wordpoints_db_query_test', $result[2]->id, 'test', true )
		);
	}

	/**
	 * Test that the 'order_by' arg is ignored when it is invalid.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::prepare_order_by
	 */
	public function test_order_by_arg_invalid() {

		$query = new WordPoints_DB_Query( array( 'order_by' => 'invalid' ) );

		$this->assertStringNotMatchesFormat( '%aORDER BY%a', $query->get_sql() );
	}

	/**
	 * Test the 'order' query arg for ascending order.
	 *
	 * @since 2.1.0
	 */
	public function test_order_query_arg_ascending() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'order_by' => 'int_col',
				'order'    => 'ASC',
			)
		);

		$result = $query->get();

		$this->assertEquals( 1, $result[0]->int_col );
		$this->assertEquals( 2, $result[1]->int_col );
		$this->assertEquals( 3, $result[2]->int_col );
	}

	/**
	 * Test the 'order' query arg for descending order.
	 *
	 * @since 2.1.0
	 */
	public function test_order_query_arg_desc() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'order_by' => 'int_col',
				'order'    => 'DESC',
			)
		);

		$result = $query->get();

		$this->assertEquals( 3, $result[0]->int_col );
		$this->assertEquals( 2, $result[1]->int_col );
		$this->assertEquals( 1, $result[2]->int_col );
	}

	/**
	 * Test the 'order' query arg when it is invalid.
	 *
	 * @since 2.1.0
	 *
	 * @expectedIncorrectUsage WordPoints_DB_Query::prepare_order_by
	 */
	public function test_order_query_arg_invalid() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'order_by' => 'int_col',
				'order'    => 'invalid',
			)
		);

		$result = $query->get();

		// Defaults to descending.
		$this->assertEquals( 3, $result[0]->int_col );
		$this->assertEquals( 2, $result[1]->int_col );
		$this->assertEquals( 1, $result[2]->int_col );
	}

	/**
	 * Test the column query arg.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_values
	 *
	 * @param mixed $value   The value to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_query_arg( $value, $results ) {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 0 ) );
		$this->insert_rows( 1, array( 'int_col' => -4 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col' => $value )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to run a column query with.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values for a column query.
	 */
	public function data_provider_column_values() {
		return array(
			'valid' => array( 1, 1 ),
			'negative' => array( -4, 1 ),
			'zero' => array( 0, 1 ),
			'invalid' => array( 'a', 3 ),
		);
	}

	/**
	 * Test the column query arg when the column has a set of predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_values_predefined
	 *
	 * @param mixed $value   The value to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_query_arg_values( $value, $results ) {

		$this->insert_rows( 1, array( 'int_col' => 5 ) );
		$this->insert_rows( 1, array( 'int_col' => 1 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col' => $value )
		);

		$query->columns['int_col']['values'] = array( 1, 2, 3 );

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to run a query on a column with predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values for a column query.
	 */
	public function data_provider_column_values_predefined() {
		return array(
			'valid' => array( 1, 1 ),
			'invalid' => array( 5, 2 ),
		);
	}

	/**
	 * Test the column query arg when the column expects positive numbers.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_values_unsigned
	 *
	 * @param mixed $value   The value to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_query_arg_unsigned( $value, $results ) {

		$this->insert_rows( 1, array( 'id' => 1 ) );
		$this->insert_rows( 1, array( 'id' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query( array( 'id' => $value ) );

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to run a query on an unsigned column.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values for a column query.
	 */
	public function data_provider_column_values_unsigned() {
		return array(
			'positive' => array( 1, 1 ),
			'zero' => array( 0, 0 ),
			'negative' => array( -4, 2 ),
		);
	}

	/**
	 * Test the column compare query arg for integer columns.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_integer_column_compare
	 *
	 * @param string $comparator The comparison operator to use.
	 * @param int    $results    The number of expected results.
	 * @param mixed  $value      The value to query for.
	 */
	public function test_column_compare_query_arg_int( $comparator, $results, $value = null ) {

		$this->insert_rows( 1, array( 'int_col' => 10 ) );
		$this->insert_rows( 1, array( 'int_col' => 5 ) );
		$this->insert_rows( 1, array( 'int_col' => 1 ) );

		if ( null === $value ) {
			$value = 1;
		}

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col' => $value, 'int_col__compare' => $comparator )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides a list of possible comparisons for an integer column.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of possible column comparisons.
	 */
	public function data_provider_integer_column_compare() {
		return array(
			'=' => array( '=', 1 ),
			'!=' => array( '!=', 2 ),
			'<>' => array( '<>', 2 ),
			'>' => array( '>', 2 ),
			'<' => array( '<', 0 ),
			'>=' => array( '>=', 2, 5 ),
			'<=' => array( '<=', 1 ),
			'LIKE' => array( 'LIKE', 3, '1%' ),
			'NOT LIKE' => array( 'NOT LIKE', 3, '1%' ),
			'invalid' => array( 'invalid', 1 ),
		);
	}

	/**
	 * Test the column compare query arg for string columns.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_text_column_compare
	 *
	 * @param string $comparator The comparison operator to use.
	 * @param int    $results    The number of expected results.
	 * @param mixed  $value      The value to query for.
	 */
	public function test_column_compare_query_arg_text( $comparator, $results, $value = null ) {

		$this->insert_rows( 1, array( 'text_col' => 'Testing' ) );
		$this->insert_rows( 1, array( 'text_col' => 'Test' ) );
		$this->insert_rows( 1, array( 'text_col' => 'Eating' ) );

		if ( null === $value ) {
			$value = 'Test';
		}

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'text_col' => $value, 'text_col__compare' => $comparator )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides a list of possible comparisons for a text column.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of possible column comparisons.
	 */
	public function data_provider_text_column_compare() {
		return array(
			'=' => array( '=', 1 ),
			'!=' => array( '!=', 2 ),
			'<>' => array( '<>', 2 ),
			'>' => array( '>', 1 ),
			'<' => array( '<', 1 ),
			'>=' => array( '>=', 2 ),
			'<=' => array( '<=', 2 ),
			'LIKE' => array( 'LIKE', 2, 'Test%' ),
			'NOT LIKE' => array( 'NOT LIKE', 1, 'Test%' ),
			'invalid' => array( 'invalid', 1 ),
		);
	}

	/**
	 * Test the column in query arg.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_in_values
	 *
	 * @param array $in      The values to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_in_query_arg( $in, $results ) {

		$this->insert_rows( 1, array( 'int_col' => 5 ) );
		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 0 ) );
		$this->insert_rows( 1, array( 'int_col' => -4 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col__in' => $in )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to run a column in query with.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values for a column in query.
	 */
	public function data_provider_column_in_values() {
		return array(
			'empty' => array( array(), 4 ),
			'one' => array( array( 1 ), 1 ),
			'multiple' => array( array( 5, 1 ), 2 ),
			'negative' => array( array( -4 ), 1 ),
			'zero' => array( array( 0 ), 1 ),
			'invalid' => array( array( 'a' ), 4 ),
			'multiple_invalid' => array( array( 'a', 'b' ), 4 ),
			'mixed' => array( array( 1, 'a', 5, 'b' ), 2 ),
		);
	}

	/**
	 * Test the column in query arg when the column has a set of predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_in_predefined_values
	 *
	 * @param array $in      The values to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_in_query_arg_values( $in, $results ) {

		$this->insert_rows( 1 );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );
		$this->insert_rows( 1, array( 'int_col' => 1 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col__in' => $in )
		);

		$query->columns['int_col']['values'] = array( 1, 2, 3 );

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to use in queries on a column with predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values to use in a column in query.
	 */
	public function data_provider_column_in_predefined_values() {
		return array(
			'one_valid' => array( array( 1 ), 1 ),
			'multiple_valid' => array( array( 3, 1 ), 2 ),
			'one_invalid' => array( array( 10 ), 3 ),
			'multiple_invalid' => array( array( 5, 10 ), 3 ),
			'mixed' => array( array( 3, 5, 1, 10 ), 2 ),
		);
	}

	/**
	 * Test the column in arg when it expects a positive int.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_in_unsigned
	 *
	 * @param array $in      The values to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_in_query_arg_unsigned( $in, $results ) {

		$this->insert_rows( 1, array( 'id' => 1 ) );
		$this->insert_rows( 1, array( 'id' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'id__in' => $in )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to use in queries on a column with predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values to use in a column in query.
	 */
	public function data_provider_column_in_unsigned() {
		return array(
			'positive' => array( array( 1 ), 1 ),
			'zero' => array( array( 0 ), 0 ),
			'negative' => array( array( -4 ), 2 ),
		);
	}

	/**
	 * Test that the column query arg takes precedence over the column in arg.
	 *
	 * @since 2.1.0
	 */
	public function test_column_query_arg_takes_precedence_over_column_in() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col' => 1, 'int_col__in' => array( 1, 2 ) )
		);

		$this->assertEquals( 1, $query->count() );
	}

	/**
	 * Test the column not in query arg.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_not_in_values
	 *
	 * @param array $in      The values to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_not_in_query_arg( $in, $results ) {

		$this->insert_rows( 1, array( 'int_col' => 5 ) );
		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 0 ) );
		$this->insert_rows( 1, array( 'int_col' => -4 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col__not_in' => $in )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to run a column not in query with.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values for a column in query.
	 */
	public function data_provider_column_not_in_values() {
		return array(
			'empty' => array( array(), 4 ),
			'one' => array( array( 1 ), 3 ),
			'multiple' => array( array( 5, 1 ), 2 ),
			'negative' => array( array( -4 ), 3 ),
			'zero' => array( array( 0 ), 3 ),
			'invalid' => array( array( 'a' ), 4 ),
			'multiple_invalid' => array( array( 'a', 'b' ), 4 ),
			'mixed' => array( array( 1, 'a', 5, 'b' ), 2 ),
		);
	}

	/**
	 * Test the column not in query arg when the column has predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_not_in_predefined_values
	 *
	 * @param array $in      The values to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_not_in_query_arg_values( $in, $results ) {

		$this->insert_rows( 1 );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );
		$this->insert_rows( 1, array( 'int_col' => 1 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col__not_in' => $in )
		);

		$query->columns['int_col']['values'] = array( 1, 2, 3 );

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to use in queries on a column with predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values to use in a column in query.
	 */
	public function data_provider_column_not_in_predefined_values() {
		return array(
			'one_valid' => array( array( 1 ), 2 ),
			'multiple_valid' => array( array( 3, 1 ), 1 ),
			'one_invalid' => array( array( 10 ), 3 ),
			'multiple_invalid' => array( array( 5, 10 ), 3 ),
			'mixed' => array( array( 3, 5, 1, 10 ), 1 ),
		);
	}

	/**
	 * Test the column not in arg when it expects a positive int.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_column_not_in_unsigned
	 *
	 * @param array $in      The values to query for.
	 * @param int   $results The number of expected results.
	 */
	public function test_column_not_in_query_arg_unsigned( $in, $results ) {

		$this->insert_rows( 1, array( 'id' => 1 ) );
		$this->insert_rows( 1, array( 'id' => 2 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'id__not_in' => $in )
		);

		$this->assertEquals( $results, $query->count() );
	}

	/**
	 * Provides sets of values to use in queries on a column with predefined values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of values to use in a column in query.
	 */
	public function data_provider_column_not_in_unsigned() {
		return array(
			'positive' => array( array( 1 ), 1 ),
			'zero' => array( array( 0 ), 2 ),
			'negative' => array( array( -4 ), 2 ),
		);
	}

	/**
	 * Test that the column query arg takes precedence over the column not in arg.
	 *
	 * @since 2.1.0
	 */
	public function test_column_query_arg_takes_precedence_over_column_not_in() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'int_col' => 1, 'int_col__not_in' => array( 2 ) )
		);

		$this->assertEquals( 1, $query->count() );
	}

	/**
	 * Test that the column in query arg takes precedence over the column not in arg.
	 *
	 * @since 2.1.0
	 */
	public function test_column_in_query_arg_takes_precedence_over_column_not_in() {

		$this->insert_rows( 1, array( 'int_col' => 1 ) );
		$this->insert_rows( 1, array( 'int_col' => 2 ) );
		$this->insert_rows( 1, array( 'int_col' => 3 ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array(
				'int_col__in' => array( 1, 2 ),
				'int_col__not_in' => array( 1, 2, 3 ),
			)
		);

		$this->assertEquals( 2, $query->count() );
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

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'meta_query' => array( array( 'key' => 'test', 'value' => 1 ) ) )
		);

		$this->assertEquals( 1, $query->count() );
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
		$this->insert_rows( 1, array( 'date_col' => $date ) );

		$query = new WordPoints_PHPUnit_Mock_DB_Query(
			array( 'date_col_query' => array( array( 'before' => $date ) ) )
		);

		$this->assertEquals( 1, $query->count() );
	}

	/**
	 * Insert some rows into the test table.
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
				'int_col'  => 10,
				'text_col' => 'Testing',
				'date_col' => current_time( 'mysql' ),
			)
			, $values
		);

		for ( $i = 0; $i < $count; $i++ ) {

			$wpdb->insert(
				"{$wpdb->prefix}wordpoints_db_query_test"
				, $values
				, array( '%d', '%s', '%s' )
			);

			$id = $wpdb->insert_id;

			foreach ( $meta as $key => $value ) {
				add_metadata( 'wordpoints_db_query_test', $id, $key, $value );
			}
		}
	}

	/**
	 * Assert that a value matches the format of a table row.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $row The value that is expected to be a row from the test table.
	 */
	protected function assertIsRow( $row ) {

		$this->assertInternalType( 'object', $row );
		$this->assertObjectHasAttribute( 'id', $row );
		$this->assertObjectHasAttribute( 'int_col', $row );
		$this->assertObjectHasAttribute( 'text_col', $row );
		$this->assertEquals( 10, $row->int_col );
		$this->assertEquals( 'Testing', $row->text_col );
	}
}

// EOF
