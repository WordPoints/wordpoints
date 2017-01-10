<?php

/**
 * Test case for the entity classes.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the entity classes.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_User
 * @covers WordPoints_Entity_User_Roles
 * @covers WordPoints_Entity_Post
 * @covers WordPoints_Entity_Post_Author
 * @covers WordPoints_Entity_Post_Content
 * @covers WordPoints_Entity_Comment
 * @covers WordPoints_Entity_Comment_Author
 * @covers WordPoints_Entity_Comment_Post
 * @covers WordPoints_Entity_User_Role
 */
class WordPoints_All_Entities_Test extends WordPoints_PHPUnit_TestCase_Entities {

	/**
	 * Provides a list of entities.
	 *
	 * @since 2.1.0
	 *
	 * @return array The list of entities to test.
	 */
	public function data_provider_entities() {

		global $wpdb;

		$this->factory = new WP_UnitTest_Factory();
		$this->factory->wordpoints = WordPoints_PHPUnit_Factory::$factory;

		$entities = array(
			'user'    => array(
				array(
					'class'          => 'WordPoints_Entity_User',
					'slug'           => 'user',
					'id_field'       => 'ID',
					'human_id_field' => 'display_name',
					'context'        => '',
					'storage_info'   => array(
						'type' => 'db',
						'info' => array(
							'type'       => 'table',
							'table_name' => $wpdb->users,
						),
					),
					'the_context'    => array(),
					'create_func'    => array( $this->factory->user, 'create_and_get' ),
					'delete_func'    => array( $this, 'delete_user' ),
					'children'       => array(
						'roles' => array(
							'class'            => 'WordPoints_Entity_User_Roles',
							'primary'          => 'user',
							'related'          => 'user_role{}',
							'storage_info'     => array(
								'type' => 'db',
								'info' => array(
									'type'             => 'table',
									'table_name'       => $wpdb->usermeta,
									'primary_id_field' => 'user_id',
									'related_id_field' => array(
										'type'  => 'serialized_array',
										'field' => 'meta_value',
									),
									'conditions'       => array(
										array(
											'field' => 'meta_key',
											'value' => $wpdb->get_blog_prefix() . 'capabilities',
										),
									),
								),
							),
						),
					),
				),
			),
			'post'    => array(
				array(
					'class'          => 'WordPoints_Entity_Post',
					'slug'           => 'post\post',
					'id_field'       => 'ID',
					'human_id_field' => 'post_title',
					'storage_info'   => array(
						'type' => 'db',
						'info' => array(
							'type'       => 'table',
							'table_name' => $wpdb->posts,
						),
					),
					'create_func'    => array( $this, 'create_post' ),
					'delete_func'    => array( $this, 'delete_post' ),
					'cant_view'      => $this->factory->post->create(
						array( 'post_status' => 'private' )
					),
					'children'       => array(
						'author' => array(
							'class'   => 'WordPoints_Entity_Post_Author',
							'primary' => 'post',
							'related' => 'user',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
							        'field' => 'post_author',
								),
							),
						),
						'content' => array(
							'class'     => 'WordPoints_Entity_Post_Content',
							'data_type' => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
							        'field' => 'post_content',
								),
							),
						),
					),
				),
			),
			'comment' => array(
				array(
					'class'          => 'WordPoints_Entity_Comment',
					'slug'           => 'comment\post',
					'id_field'       => 'comment_ID',
					'human_id_field' => 'comment_content',
					'storage_info'   => array(
						'type' => 'db',
						'info' => array(
							'type'       => 'table',
							'table_name' => $wpdb->comments,
						),
					),
					'create_func'    => array( $this, 'create_comment' ),
					'delete_func'    => array( $this, 'delete_comment' ),
					'cant_view'      => $this->factory->comment->create(
						array(
							'comment_post_ID' => $this->factory->post->create(
								array(
									'post_status' => 'private',
									'post_author' => $this->factory->user->create(
										array(
											'user_login' => 'Comment entity tester',
										)
									),
								)
							),
						)
					),
					'children'       => array(
						'author' => array(
							'class'   => 'WordPoints_Entity_Comment_Author',
							'primary' => 'comment',
							'related' => 'user',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'user_id',
								),
							),
						),
						'post' => array(
							'class'   => 'WordPoints_Entity_Comment_Post',
							'primary' => 'comment',
							'related' => 'post',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'comment_post_ID',
								),
							),
						),
					),
				),
			),
			'user_role' => array(
				array(
					'class'          => 'WordPoints_Entity_User_Role',
					'slug'           => 'user_role',
					'id_field'       => 'name',
					'get_human_id'   => array( $this, 'get_role_display_name' ),
					'storage_info'   => array(
						'type' => 'array',
						'info' => array( 'type' => 'method' ),
					),
					'create_func'    => array( $this->factory->wordpoints->user_role, 'create_and_get' ),
					'delete_func'    => 'remove_role',
				),
			),
		);

		return $entities;
	}

}

// EOF
