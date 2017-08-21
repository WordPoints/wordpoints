<?php

/**
 * Testcase for the WordPoints_Rank_Type class.
 *
 * @package WordPoints\Tests
 * @since 1.9.1
 */

/**
 * Test the WordPoints_Rank_Type class.
 *
 * @since 1.9.1
 *
 * @group ranks
 * @group rank_types
 */
class WordPoints_Rank_Type_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * The mock rank type used in the test.
	 *
	 * @since 1.9.1
	 *
	 * @var WordPoints_PHPUnit_Mock_Rank_Type
	 */
	protected $mock_rank_type;

	/**
	 * Set up for each test.
	 *
	 * @since 1.9.1
	 */
	public function setUp() {

		parent::setUp();

		$this->mock_rank_type = new WordPoints_PHPUnit_Mock_Rank_Type(
			array( 'slug' => 'test' )
		);
	}

	/**
	 * Test that get_slug() returns the rank type's slug.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::get_slug
	 */
	public function test_get_slug() {

		$this->assertSame( 'test', $this->mock_rank_type->get_slug() );
	}

	/**
	 * Test that get_name() returns the rank type's name.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::get_name
	 */
	public function test_get_name() {

		$this->assertSame( 'Test', $this->mock_rank_type->get_name() );
	}

	/**
	 * Test that get_meta_fields() returns the rank type's meta fields.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::get_meta_fields
	 */
	public function test_get_meta_fields() {

		$this->assertSame(
			array( 'test_meta' => array() )
			, $this->mock_rank_type->get_meta_fields()
		);
	}

	/**
	 * Test that display_rank_meta_form_fields() displays a text field's value.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_text_field() {

		$xpath = $this->get_rank_meta_form_xpath(
			array( 'test_field' => array( 'type' => 'text', 'default' => 'ddd' ) )
			, array( 'test_field' => 'testing' )
		);

		$inputs = $xpath->query( '//input[@type = "text"]' );

		$this->assertSame( 1, $inputs->length );

		$this->assertSame(
			'testing'
			, $inputs->item( 0 )->attributes->getNamedItem( 'value' )->nodeValue
		);
	}

	/**
	 * Test that display_rank_meta_form_fields() displays a field's default value.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_field_default() {

		$xpath = $this->get_rank_meta_form_xpath(
			array(
				'test_field' => array( 'type' => 'text', 'default' => 'default' ),
			)
		);

		$inputs = $xpath->query( '//input[@type = "text"]' );

		$this->assertSame( 1, $inputs->length );

		$this->assertSame(
			'default'
			, $inputs->item( 0 )->attributes->getNamedItem( 'value' )->nodeValue
		);
	}

	/**
	 * Test that display_rank_meta_form_fields() supports the 'number' field type.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_number_field() {

		$xpath = $this->get_rank_meta_form_xpath(
			array( 'test_field' => array( 'type' => 'number', 'default' => 0 ) )
			, array( 'test_field' => 25 )
		);

		$inputs = $xpath->query( '//input[@type = "number"]' );

		$this->assertSame( 1, $inputs->length );

		$this->assertSame(
			'25'
			, $inputs->item( 0 )->attributes->getNamedItem( 'value' )->nodeValue
		);
	}

	/**
	 * Test that display_rank_meta_form_fields() supports the 'hidden' field type.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_hidden_field() {

		$xpath = $this->get_rank_meta_form_xpath(
			array( 'test_field' => array( 'type' => 'hidden', 'default' => 0 ) )
			, array( 'test_field' => 25 )
		);

		$inputs = $xpath->query( '//input[@type = "hidden"]' );

		$this->assertSame( 1, $inputs->length );

		$this->assertSame(
			'25'
			, $inputs->item( 0 )->attributes->getNamedItem( 'value' )->nodeValue
		);
	}

	/**
	 * Test that display_rank_meta_form_fields() gives an error for invalid field types.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 *
	 * @expectedIncorrectUsage WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_invalid_field_type_error() {

		$rank_type = new WordPoints_PHPUnit_Mock_Rank_Type(
			array(
				'slug' => 'test',
				'meta_fields' => array(
					'bad' => array( 'type' => 'invalid', 'default' => '' ),
				),
			)
		);

		ob_start();
		$rank_type->display_rank_meta_form_fields();
		$form = ob_get_clean();

		$this->assertSame( '', $form );
	}

	/**
	 * Test that display_rank_meta_form_fields() displays a field's label.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_field_label() {

		$xpath = $this->get_rank_meta_form_xpath(
			array(
				'test_field' => array(
					'type' => 'text',
					'default' => 'default',
					'label' => 'Field label',
				),
			)
		);

		$inputs = $xpath->query( '//label' );

		$this->assertSame( 1, $inputs->length );

		$this->assertSame( 'Field label', trim( $inputs->item( 0 )->textContent ) );
	}

	/**
	 * Test that display_rank_meta_form_fields() displays field placeholders.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_field_placeholders() {

		$rank_type = new WordPoints_PHPUnit_Mock_Rank_Type(
			array(
				'slug' => 'test',
				'meta_fields' => array(
					'test_field' => array( 'type' => 'text', 'default' => '' ),
				),
			)
		);

		ob_start();
		$rank_type->display_rank_meta_form_fields(
			array()
			, array( 'placeholders' => true )
		);
		$form = ob_get_clean();

		$this->assertStringMatchesFormat(
			'%avalue="<% if ( typeof test_field !== "undefined" ) { print( test_field ); } %>"%a'
			, $form
		);
	}

	/**
	 * Test that display_rank_meta_form_fields() doesn't use placeholders for hidden fields.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::display_rank_meta_form_fields
	 */
	public function test_display_rank_meta_form_fields_hidden_field_placeholders() {

		$xpath = $this->get_rank_meta_form_xpath(
			array( 'test_field' => array( 'type' => 'hidden', 'default' => 0 ) )
			, array()
			, array( 'placeholders' => true )
		);

		$inputs = $xpath->query( '//input[@type = "hidden"]' );

		$this->assertSame( 1, $inputs->length );

		$this->assertSame(
			'0'
			, $inputs->item( 0 )->attributes->getNamedItem( 'value' )->nodeValue
		);
	}

	/**
	 * Tests the maybe increase user ranks method with a top rank.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_increase_user_ranks
	 */
	public function test_maybe_increase_user_ranks_no_next_rank() {

		$this->assertSame(
			array()
			, $this->mock_rank_type->maybe_increase_user_ranks(
				array( $this->factory->user->create() )
				, $this->factory->wordpoints->rank->create_and_get()
			)
		);
	}

	/**
	 * Tests maybe_increase_user_ranks() with a rank type that supports bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_increase_user_ranks
	 */
	public function test_maybe_increase_user_ranks_supports_bulk() {

		$user_ids = $this->factory->user->create_many( 2 );
		$rank_id = $this->factory->wordpoints->rank->create();

		$mock = $this->createPartialMock(
			'WordPoints_Rank_Type_Bulk_CheckI'
			, array( 'destruct', 'can_transition_user_ranks' )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_ranks' )
			->willReturn( array( $user_ids[1] ) );

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		$base_rank = wordpoints_get_rank( $base_rank_id );

		$this->assertSame(
			array( $user_ids[1] => $rank_id )
			, $this->mock_rank_type->maybe_increase_user_ranks( $user_ids, $base_rank )
		);
	}

	/**
	 * Tests maybe_increase_user_ranks() with a rank type that supports bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_increase_user_ranks()
	 */
	public function test_maybe_increase_user_ranks_supports_bulk_multiple() {

		$user_ids = $this->factory->user->create_many( 3 );
		$rank_id = $this->factory->wordpoints->rank->create();
		$rank_id_2 = $this->factory->wordpoints->rank->create(
			array( 'position' => 2 )
		);

		$mock = $this->createPartialMock(
			'WordPoints_Rank_Type_Bulk_CheckI'
			, array( 'destruct', 'can_transition_user_ranks' )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_ranks' )
			->will(
				$this->onConsecutiveCalls(
					array( $user_ids[0], $user_ids[1] )
					, array( $user_ids[1] )
				)
			);

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		$base_rank = wordpoints_get_rank( $base_rank_id );

		$this->assertSame(
			array( $user_ids[1] => $rank_id_2, $user_ids[0] => $rank_id )
			, $this->mock_rank_type->maybe_increase_user_ranks( $user_ids, $base_rank )
		);
	}

	/**
	 * Tests maybe_increase_user_ranks() with a rank type that can't do bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_increase_user_ranks()
	 */
	public function test_maybe_increase_user_ranks_not_bulk() {

		$user_ids = $this->factory->user->create_many( 2 );
		$rank_id = $this->factory->wordpoints->rank->create();

		$mock = $this->getMockForAbstractClass(
			'WordPoints_Rank_Type'
			, array( array( 'slug' => 'test' ) )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_rank' )
			->will(
				$this->onConsecutiveCalls( true, false )
			);

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		$base_rank = wordpoints_get_rank( $base_rank_id );

		$this->assertSame(
			array( $user_ids[0] => $rank_id )
			, $this->mock_rank_type->maybe_increase_user_ranks( $user_ids, $base_rank )
		);
	}

	/**
	 * Tests maybe_increase_user_ranks() with a rank type that can't do bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_increase_user_ranks()
	 */
	public function test_maybe_increase_user_ranks_not_bulk_multiple() {

		$user_ids = $this->factory->user->create_many( 3 );
		$rank_id = $this->factory->wordpoints->rank->create();
		$rank_id_2 = $this->factory->wordpoints->rank->create(
			array( 'position' => 2 )
		);

		$mock = $this->getMockForAbstractClass(
			'WordPoints_Rank_Type'
			, array( array( 'slug' => 'test' ) )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_rank' )
			->will(
				$this->onConsecutiveCalls( true, false, true, true, false, false )
			);

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		$base_rank = wordpoints_get_rank( $base_rank_id );

		$this->assertSame(
			array( $user_ids[0] => $rank_id, $user_ids[1] => $rank_id_2 )
			, $this->mock_rank_type->maybe_increase_user_ranks( $user_ids, $base_rank )
		);
	}

	/**
	 * Tests the maybe decrease user ranks method with a base rank.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_decrease_user_ranks
	 */
	public function test_maybe_decrease_user_ranks_no_previous_rank() {

		$user_id = $this->factory->user->create();

		$base_rank = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		$this->assertSame(
			array()
			, $this->mock_rank_type->maybe_decrease_user_ranks(
				array( $user_id ),
				wordpoints_get_rank( $base_rank )
			)
		);
	}

	/**
	 * Tests maybe_decrease_user_ranks() with a rank type that supports bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_decrease_user_ranks
	 */
	public function test_maybe_decrease_user_ranks_supports_bulk() {

		$user_ids = $this->factory->user->create_many( 2 );
		$rank = $this->factory->wordpoints->rank->create_and_get();

		$mock = $this->createPartialMock(
			'WordPoints_Rank_Type_Bulk_CheckI'
			, array( 'destruct', 'can_transition_user_ranks' )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_ranks' )
			->willReturn( array( $user_ids[0] ) );

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		// The other user can still have their rank, so it is this user that will
		// need to be decreased.
		$this->assertSame(
			array( $user_ids[1] => $base_rank_id )
			, $this->mock_rank_type->maybe_decrease_user_ranks( $user_ids, $rank )
		);
	}

	/**
	 * Tests maybe_decrease_user_ranks() with a rank type that supports bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_decrease_user_ranks
	 */
	public function test_maybe_decrease_user_ranks_supports_bulk_multiple() {

		$user_ids = $this->factory->user->create_many( 3 );
		$rank_id = $this->factory->wordpoints->rank->create();
		$rank_2 = $this->factory->wordpoints->rank->create_and_get(
			array( 'position' => 2 )
		);

		$mock = $this->createPartialMock(
			'WordPoints_Rank_Type_Bulk_CheckI'
			, array( 'destruct', 'can_transition_user_ranks' )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_ranks' )
			->will(
				$this->onConsecutiveCalls(
					array( $user_ids[0] )
					, array( $user_ids[1] )
				)
			);

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		// The other user can still have their rank, so it is these users that will
		// need to be decreased.
		$this->assertSame(
			array( $user_ids[2] => $base_rank_id, $user_ids[1] => $rank_id )
			, $this->mock_rank_type->maybe_decrease_user_ranks( $user_ids, $rank_2 )
		);
	}

	/**
	 * Tests maybe_decrease_user_ranks() with a rank type that can't do bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_decrease_user_ranks
	 */
	public function test_maybe_decrease_user_ranks_not_bulk() {

		$user_ids = $this->factory->user->create_many( 2 );
		$rank = $this->factory->wordpoints->rank->create_and_get();

		$mock = $this->getMockForAbstractClass(
			'WordPoints_Rank_Type'
			, array( array( 'slug' => 'test' ) )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_rank' )
			->will(
				$this->onConsecutiveCalls( true, false )
			);

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		// The other user can still have their rank, so it is this user that will
		// need to be decreased.
		$this->assertSame(
			array( $user_ids[1] => $base_rank_id )
			, $this->mock_rank_type->maybe_decrease_user_ranks( $user_ids, $rank )
		);
	}

	/**
	 * Tests maybe_decrease_user_ranks() with a rank type that can't do bulk checks.
	 *
	 * @since 2.4.0
	 *
	 * @covers WordPoints_Rank_Type::maybe_decrease_user_ranks
	 */
	public function test_maybe_decrease_user_ranks_not_bulk_multiple() {

		$user_ids = $this->factory->user->create_many( 3 );
		$rank = $this->factory->wordpoints->rank->create_and_get();
		$rank_2 = $this->factory->wordpoints->rank->create_and_get(
			array( 'position' => 2 )
		);

		$mock = $this->getMockForAbstractClass(
			'WordPoints_Rank_Type'
			, array( array( 'slug' => 'test' ) )
		);

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Types::register_type( $this->rank_type, $mock );

		/** @var PHPUnit_Framework_MockObject_MockObject $rank_type */
		$rank_type = WordPoints_Rank_Types::get_type( $this->rank_type );

		$rank_type->method( 'can_transition_user_rank' )
			->will(
				$this->onConsecutiveCalls( true, false, true, false, false, true )
			);

		$base_rank_id = WordPoints_Rank_Groups::get_group( $this->rank_group )
			->get_rank( 0 );

		// The other user can still have their rank, so it is these users that will
		// need to be decreased.
		$this->assertSame(
			array( $user_ids[1] => $rank->ID, $user_ids[2] => $base_rank_id )
			, $this->mock_rank_type->maybe_decrease_user_ranks( $user_ids, $rank_2 )
		);
	}

	//
	// Helpers.
	//

	/**
	 * Get and XPath object for the form for the meta fields.
	 *
	 * @since 1.9.1
	 *
	 * @param array $meta_fields The meta fields definition.
	 * @param array $meta        The predefined meta values.
	 * @param array $args        The form display args.
	 *
	 * @return DOMXPath XPath object loaded with the form HTML.
	 */
	protected function get_rank_meta_form_xpath(
		array $meta_fields = array(),
		array $meta = array(),
		array $args = array()
	) {

		$rank_type = $this->mock_rank_type;

		if ( ! empty( $meta_fields ) ) {
			$rank_type = new WordPoints_PHPUnit_Mock_Rank_Type(
				array( 'slug' => 'test', 'meta_fields' => $meta_fields )
			);
		}

		ob_start();
		$rank_type->display_rank_meta_form_fields( $meta, $args );
		$form = ob_get_clean();

		$document = new DOMDocument();
		$document->loadHTML( $form );
		$xpath    = new DOMXPath( $document );

		return $xpath;
	}
}

// EOF
