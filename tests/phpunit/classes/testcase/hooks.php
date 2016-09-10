<?php

/**
 * Hooks test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent test case for testing the hooks API.
 *
 * @since 2.1.0
 */
abstract class WordPoints_PHPUnit_TestCase_Hooks extends WordPoints_PHPUnit_TestCase {

	//
	// Assertions.
	//

	/**
	 * Assert that an event is registered.
	 *
	 * @since 2.2.0
	 *
	 * @param string          $event_slug The slug of the event.
	 * @param string|string[] $arg_slugs The slugs of the args expected to be
	 *                                   registered for this event.
	 */
	protected function assertEventRegistered( $event_slug, $arg_slugs = array() ) {

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$this->assertTrue( $events->is_registered( $event_slug ) );

		foreach ( (array) $arg_slugs as $slug ) {

			$this->assertTrue(
				$events->get_sub_app( 'args' )->is_registered( $event_slug, $slug )
				, "The {$slug} arg must be registered for the {$event_slug} event."
			);
		}
	}

	/**
	 * Assert that an event is not registered.
	 *
	 * @since 2.2.0
	 *
	 * @param string          $event_slug The slug of the event.
	 * @param string|string[] $arg_slugs The slugs of the args expected to be
	 *                                   registered for this event.
	 */
	protected function assertEventNotRegistered( $event_slug, $arg_slugs = array() ) {

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$this->assertFalse( $events->is_registered( $event_slug ) );

		foreach ( (array) $arg_slugs as $slug ) {

			$this->assertFalse(
				$events->get_sub_app( 'args' )->is_registered( $event_slug, $slug )
				, "The {$slug} arg must not be registered for the {$event_slug} event."
			);
		}
	}

	//
	// Data Providers.
	//

	/**
	 * Provides several different sets of valid condition settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Sets of valid settings.
	 */
	public function data_provider_valid_condition_settings() {

		$action_type = 'test_fire';

		$conditions = array(
			'_conditions' => array(
				array(
					'type'     => 'test',
					'settings' => array( 'value' => 'a' ),
				),
			),
		);

		$entity = array( 'test_entity' => $conditions );
		$child = $both = array( 'test_entity' => array( 'child' => $conditions ) );

		$both['test_entity']['_conditions'] = $conditions['_conditions'];

		return array(
			'none' => array( array( $action_type => array() ) ),
			'empty' => array( array( 'conditions' => array( $action_type => array() ) ) ),
			'entity' => array( array( 'conditions' => array( $action_type => $entity ) ) ),
			'child' => array( array( 'conditions' => array( $action_type => $child ) ) ),
			'both' => array( array( 'conditions' => array( $action_type => $both ) ) ),
			'two_entities' => array(
				array(
					'conditions' => array(
						$action_type => array(
							'test_entity' => $conditions,
							'another' => $conditions,
						),
					),
				),
			),
		);
	}

	/**
	 * Provides an array of possible condition settings, each with one invalid item.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Every possible set of settings with one invalid item.
	 */
	public function data_provider_invalid_condition_settings() {

		$action_type = 'test_fire';

		$conditions = array(
			'_conditions' => array(
				array(
					'type'     => 'test',
					'settings' => array( 'value' => 'a' ),
				),
			),
		);

		$invalid_settings = array(
			'not_array' => array(
				array( 'conditions' => array( $action_type => 'not_array' ) ),
				array( 'conditions', $action_type ),
			),
			'invalid_entity' => array(
				array(
					'conditions' => array(
						$action_type => array( 'invalid_entity' => $conditions ),
					),
				),
				array( 'conditions', $action_type ),
			),
			'incorrect_data_type' => array(
				array(
					'conditions' => array(
						$action_type => array(
							'test_entity' => array( 'child' => $conditions ),
						),
					),
				),
				array( 'conditions', $action_type, 'test_entity', 'child', '_conditions', 0 ),
			),
		);

		$invalid_setting_fields = array(
			'type' => 'invalid',
			'settings' => array(),
		);

		foreach ( $conditions['_conditions'][0] as $slug => $value ) {

			$invalid_conditions = $conditions;

			unset( $invalid_conditions['_conditions'][0][ $slug ] );

			$field = array( 'conditions', $action_type, 'test_entity', '_conditions', 0 );

			$invalid_settings[ "no_{$slug}" ] = array(
				array(
					'conditions' => array(
						$action_type => array(
							'test_entity' => $invalid_conditions,
						),
					),
				),
				$field,
			);

			if ( isset( $invalid_setting_fields[ $slug ] ) ) {
				$invalid_conditions['_conditions'][0][ $slug ] = $invalid_setting_fields[ $slug ];

				$field[] = $slug;

				if ( 'settings' === $slug ) {
					$field[] = 'value';
				}

				$invalid_settings[ "invalid_{$slug}" ] = array(
					array(
						'conditions' => array(
							$action_type => array(
								'test_entity' => $invalid_conditions,
							),
						),
					),
					$field,
				);
			}
		}

		return $invalid_settings;
	}

	/**
	 * Provides an array of possible settings settings which are not met.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Condition settings that are unmet.
	 */
	public function data_provider_unmet_conditions() {

		$action_type = 'test_fire';

		$conditions = array(
			'_conditions' => array(
				array(
					'type'     => 'unmet',
					'settings' => array( 'value' => 'a' ),
				),
			),
		);

		$settings = array(
			'unmet_condition' => array(
				array(
					'conditions' => array(
						$action_type => array( 'test_entity' => $conditions ),
					),
				),
			),
			'unmet_child_condition' => array(
				array(
					'conditions' => array(
						$action_type => array(
							'test_entity' => array( 'child' => $conditions ),
						),
					),
				),
			),
		);

		return $settings;
	}

	/**
	 * Assert that one or more hits were logged.
	 *
	 * @since 2.1.0
	 *
	 * @param array $data  The hit data.
	 * @param int   $count The number of expected logs.
	 */
	public function assertHitsLogged( array $data, $count = 1 ) {

		$now = current_time( 'timestamp', true );

		$data = array_merge(
			array(
				'action_type' => 'test_fire',
				'primary_arg_guid' => '',
				'event' => 'test_event',
				'reactor' => 'test_reactor',
				'reaction_mode' => wordpoints_hooks()->get_current_mode(),
				'reaction_store' => 'test_reaction_store',
				'reaction_context_id' => array( 'site' => 1, 'network' => 1 ),
				'reaction_id' => 1,
			)
			, $data
		);

		$data['reaction_context_id'] = wp_json_encode(
			$data['reaction_context_id']
		);

		if ( is_array( $data['primary_arg_guid'] ) ) {
			$data['primary_arg_guid'] = wp_json_encode(
				$data['primary_arg_guid']
			);
		}

		if ( ! isset( $data['meta_key'] ) && ! isset( $data['meta_query'] ) ) {
			$data['meta_key'] = 'reversed_by';
			$data['meta_compare'] = 'NOT EXISTS';
		}

		$query = new WordPoints_Hook_Hit_Query( $data );
		$hits = $query->get();

		$this->assertCount( $count, $hits );

		foreach ( $hits as $hit ) {
			$this->assertLessThanOrEqual( 2, $now - strtotime( $hit->date, $now ) );
		}
	}
}

// EOF
