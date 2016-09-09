<?php

/**
 * Base entities test case class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Parent entities test case.
 *
 * @since 2.1.0
 */
abstract class WordPoints_PHPUnit_TestCase_Entities
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Data provider for the entities test.
	 *
	 * Should provide a list of entities to test.
	 *
	 * @since 2.2.0
	 *
	 * @return array[]
	 */
	abstract public function data_provider_entities();

	/**
	 * Test an entity
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_entities
	 *
	 * @param array $data The data for this test.
	 */
	public function test_entity( $data ) {

		$class = $data['class'];

		/** @var WordPoints_Entity $entity */
		$entity = new $class( $data['slug'] );

		$the_entity = call_user_func( $data['create_func'] );

		$the_id = $the_entity->{$data['id_field']};

		if ( isset( $data['human_id_field'] ) ) {
			$the_human_id = $the_entity->{$data['human_id_field']};
		} else {
			$the_human_id = call_user_func( $data['get_human_id'], $the_entity );
		}

		$this->assertNotEmpty( $entity->get_title() );

		if ( isset( $data['context'] ) ) {
			$this->assertEquals(
				$data['context'],
				$entity->get_context()
			);
		} else {
			$this->assertEquals( 'site', $entity->get_context() );
		}

		$this->assertEquals(
			$the_human_id
			, $entity->get_human_id( $the_id )
		);

		$this->assertTrue( $entity->exists( $the_id ) );

		$this->assertTrue( $entity->set_the_value( $the_entity ) );
		$this->assertEquals( $the_id, $entity->get_the_value() );
		$this->assertEquals( $the_id, $entity->get_the_id() );
		$this->assertEquals( $the_human_id, $entity->get_the_human_id() );

		if ( isset( $data['human_id_field'] ) ) {
			$this->assertEquals(
				$the_human_id
				, $entity->get_the_attr_value( $data['human_id_field'] )
			);
		}

		if ( isset( $data['context'] ) ) {

			$this->assertSame(
				$data['the_context'],
				$entity->get_the_context()
			);

		} else {

			$the_context = array(
				$data['slug'] => $the_id,
				'site'        => 1,
				'network'     => 1,
			);

			$this->assertSame(
				$the_context,
				$entity->get_the_guid()
			);

			unset( $the_context[ $data['slug'] ] );

			$this->assertSame(
				$the_context,
				$entity->get_the_context()
			);
		}

		if ( $entity instanceof WordPoints_Entity_Restricted_VisibilityI ) {

			$can_view = ( isset( $data['can_view'] ) ) ? $data['can_view'] : $the_id;

			foreach ( (array) $can_view as $user_id => $entity_id ) {

				if ( empty( $user_id ) ) {
					$user_id = $this->factory->user->create();
				}

				$this->assertTrue(
					$entity->user_can_view( $user_id, $entity_id )
				);
			}

			foreach ( (array) $data['cant_view'] as $user_id => $entity_id ) {

				if ( empty( $user_id ) ) {
					$user_id = $this->factory->user->create();
				}

				$this->assertFalse(
					$entity->user_can_view( $user_id, $entity_id )
				);
			}
		}

		if ( isset( $data['children'] ) ) {
			foreach ( $data['children'] as $slug => $child_data ) {

				$child = new $child_data['class']( $slug );

				$this->assertNotEmpty( $child->get_title() );

				$this->assertInstanceOf(
					'WordPoints_Entityish_StoredI',
					$child
				);

				$this->assertEquals(
					$child_data['storage_info']
					, $child->get_storage_info()
				);

				if ( $child instanceof WordPoints_Entity_Attr ) {

					$this->assertEquals(
						$child_data['data_type']
						, $child->get_data_type()
					);

					$child->set_the_value_from_entity( $entity );

				} elseif ( $child instanceof WordPoints_Entity_Relationship ) {

					$this->assertEquals(
						$child_data['primary']
						, $child->get_primary_entity_slug()
					);

					$this->assertEquals(
						$child_data['related']
						, $child->get_related_entity_slug()
					);

					$child->set_the_value_from_entity( $entity );
				}
			}
		}

		if ( $entity instanceof WordPoints_Entity_EnumerableI ) {
			$this->assertInternalType(
				'array',
				$entity->get_enumerated_values()
			);
		}

		if ( $entity instanceof WordPoints_Entity_Stored_Array ) {
			$this->assertInternalType(
				'array',
				$entity->get_storage_array()
			);
		}

		$this->assertInstanceOf(
			'WordPoints_Entityish_StoredI',
			$entity
		);
		$this->assertEquals(
			$data['storage_info'],
			$entity->get_storage_info()
		);

		call_user_func( $data['delete_func'], $the_id );

		if ( isset( $data['children'] ) ) {

			foreach ( $data['children'] as $slug => $child_data ) {

				$child = new $child_data['class']( $slug );

				// We're just checking that there are no errors here. Whether the
				// value will be set depends on the child and whether its value is
				// stored as field on the parent object or not.
				$child->set_the_value_from_entity( $entity );
			}

			$entity->set_the_value( null );

			foreach ( $data['children'] as $slug => $child_data ) {

				$child = new $child_data['class']( $slug );

				$child->set_the_value_from_entity( $entity );

				$this->assertNull( $child->get_the_value() );
			}
		}

		$this->assertFalse( $entity->exists( $the_id ) );
		$this->assertFalse( $entity->set_the_value( $the_id ) );
		$this->assertFalse( $entity->get_human_id( $the_id ) );
	}

	/**
	 * Creates a post.
	 *
	 * @since 2.1.0
	 *
	 * @return object The post object.
	 */
	public function create_post() {

		return $this->factory->post->create_and_get(
			array( 'post_author' => $this->factory->user->create() )
		);
	}

	/**
	 * Fully deletes a post.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The post ID.
	 */
	public function delete_post( $id ) {

		wp_delete_post( $id, true );
	}

	/**
	 * Creates a comment.
	 *
	 * @since 2.1.0
	 *
	 * @return object The comment object.
	 */
	public function create_comment() {

		return $this->factory->comment->create_and_get(
			array(
				'user_id'         => $this->factory->user->create(),
				'comment_post_ID' => $this->factory->post->create(),
			)
		);
	}

	/**
	 * Fully deletes a comment.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The comment ID.
	 */
	public function delete_comment( $id ) {

		wp_delete_comment( $id, true );
	}

	/**
	 * Creates a role.
	 *
	 * @since 2.1.0
	 * @deprecated 2.2.0 Use $this->factory->wordpoints->user_role->create_and_get()
	 *                   instead.
	 *
	 * @return object The role object.
	 */
	public function create_role() {

		_deprecated_function(
			__FUNCTION__
			, '2.2.0'
			, '$this->factory->wordpoints->user_role->create_and_get()'
		);

		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$role = $this->factory->wordpoints->user_role->create_and_get();

		$names = $wp_roles->get_names();

		// See https://core.trac.wordpress.org/ticket/34608
		$role->_display_name = $names[ $role->name ];

		return $role;
	}

	/**
	 * Gets the display name for a role.
	 *
	 * @since 2.2.0
	 *
	 * @param WP_Role $role The role object.
	 *
	 * @return string The display name of the role.
	 */
	public function get_role_display_name( $role ) {

		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$names = $wp_roles->get_names();

		// See https://core.trac.wordpress.org/ticket/34608
		return $names[ $role->name ];
	}
}

// EOF
