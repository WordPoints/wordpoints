<?php

/**
 * A test case for the points component update to 2.4.0.
 *
 * @package WordPoints\Tests
 * @since 2.4.0
 */

/**
 * Test that the points component updates to 2.4.0 properly.
 *
 * @since 2.4.0
 *
 * @group points
 * @group update
 *
 * @covers WordPoints_Points_Installable::get_update_routine_factories
 * @covers WordPoints_Points_Updater_2_4_0_Condition_Contains
 */
class WordPoints_Points_2_4_0_Update_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.4.0
	 */
	protected $previous_version = '2.3.0';

	/**
	 * Tests that it sets the max to 0 if it is empty.
	 *
	 * @since 2.4.0
	 */
	public function test_sets_max_to_0_if_empty() {

		$reaction = $this->create_points_reaction();
		$reaction->update_meta(
			'conditions'
			, array(
				'fire' => array(
					'user' => array(
						'roles' => array(
							'user_role{}' => array(
								'_conditions' => array(
									array(
										'type'     => 'contains',
										'settings' => array( 'max' => '' ),
									),
								),
							),
						),
					),
				),
			)
		);

		$this->update_component();

		$meta = $reaction->get_meta( 'conditions' );

		$this->assertSame(
			0
			, $meta['fire']['user']['roles']['user_role{}']['_conditions'][0]['settings']['max']
		);
	}

	/**
	 * Tests the behavior for network hooks.
	 *
	 * @since 2.4.0
	 *
	 * @requires WordPoints network-active
	 */
	public function test_network_mode() {

		$hooks = wordpoints_hooks();
		$mode = $hooks->get_current_mode();
		$hooks->set_current_mode( 'network' );

		$this->test_sets_max_to_0_if_empty();

		$hooks->set_current_mode( $mode );
	}

	/**
	 * Tests that it processes sub-conditions as well.
	 *
	 * @since 2.4.0
	 */
	public function test_sub_condition() {

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'post_publish\\post',
				'target' => array( 'post\\post', 'author', 'user' ),
			)
		);

		// There's really no comments relationship, but it still demos the behavior.
		$reaction->update_meta(
			'conditions'
			, array(
				'fire' => array(
					'post\\post' => array(
						'comments' => array(
							'comment{}' => array(
								'_conditions' => array(
									array(
										'type'     => 'contains',
										'settings' => array(
											'max' => 5,
											'conditions' => array(
												'author' => array(
													'user' => array(
														'roles' => array(
															'user_role{}' => array(
																'_conditions' => array(
																	array(
																		'type'     => 'contains',
																		'settings' => array( 'max' => '' ),
																	),
																),
															),
														),
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			)
		);

		$this->update_component();

		$meta = $reaction->get_meta( 'conditions' );

		$this->assertSame(
			0
			, $meta['fire']['post\\post']['comments']['comment{}']['_conditions'][0]
				['settings']['conditions']['author']['user']['roles']['user_role{}']
				['_conditions'][0]['settings']['max']
		);
	}

	/**
	 * Tests that it only processes entity array conditions, not string conditions.
	 *
	 * @since 2.4.0
	 */
	public function test_string_contains_condition() {

		$reaction = $this->create_points_reaction(
			array(
				'event' => 'post_publish\\post',
				'target' => array( 'post\\post', 'author', 'user' ),
			)
		);

		$reaction->update_meta(
			'conditions'
			, array(
				'fire' => array(
					'post\\post' => array(
						'title' => array(
							'_conditions' => array(
								array(
									'type'     => 'contains',
									'settings' => array(
										'value' => 'test',
										'max'   => '',
									),
								),
							),
						),
					),
				),
			)
		);

		$this->update_component();

		$meta = $reaction->get_meta( 'conditions' );

		$this->assertSame(
			''
			, $meta['fire']['post\\post']['title']['_conditions'][0]['settings']['max']
		);
	}
}

// EOF
