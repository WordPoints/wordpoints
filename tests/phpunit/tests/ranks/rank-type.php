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

		$this->assertEquals( 'test', $this->mock_rank_type->get_slug() );
	}

	/**
	 * Test that get_name() returns the rank type's name.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::get_name
	 */
	public function test_get_name() {

		$this->assertEquals( 'Test', $this->mock_rank_type->get_name() );
	}

	/**
	 * Test that get_meta_fields() returns the rank type's meta fields.
	 *
	 * @since 1.9.1
	 *
	 * @covers WordPoints_Rank_Type::get_meta_fields
	 */
	public function test_get_meta_fields() {

		$this->assertEquals(
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

		$this->assertEquals( 1, $inputs->length );

		$this->assertEquals(
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

		$this->assertEquals( 1, $inputs->length );

		$this->assertEquals(
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

		$this->assertEquals( 1, $inputs->length );

		$this->assertEquals(
			25
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

		$this->assertEquals( 1, $inputs->length );

		$this->assertEquals(
			25
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

		$this->assertEmpty( $form );
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

		$this->assertEquals( 1, $inputs->length );

		$this->assertEquals( 'Field label', trim( $inputs->item( 0 )->textContent ) );
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

		$this->assertEquals( 1, $inputs->length );

		$this->assertEquals(
			'0'
			, $inputs->item( 0 )->attributes->getNamedItem( 'value' )->nodeValue
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

		$document = new DOMDocument;
		$document->loadHTML( $form );
		$xpath    = new DOMXPath( $document );

		return $xpath;
	}
}

// EOF
