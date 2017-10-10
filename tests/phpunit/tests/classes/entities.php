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
 * @covers WordPoints_Entity_Comment
 * @covers WordPoints_Entity_Comment_Author
 * @covers WordPoints_Entity_Comment_Content
 * @covers WordPoints_Entity_Comment_Date
 * @covers WordPoints_Entity_Comment_Parent
 * @covers WordPoints_Entity_Comment_Post
 * @covers WordPoints_Entity_Post
 * @covers WordPoints_Entity_Post_Author
 * @covers WordPoints_Entity_Post_Comment_Count
 * @covers WordPoints_Entity_Post_Content
 * @covers WordPoints_Entity_Post_Date_Modified
 * @covers WordPoints_Entity_Post_Date_Published
 * @covers WordPoints_Entity_Post_Excerpt
 * @covers WordPoints_Entity_Post_Parent
 * @covers WordPoints_Entity_Post_Terms
 * @covers WordPoints_Entity_Post_Title
 * @covers WordPoints_Entity_Term
 * @covers WordPoints_Entity_Term_Count
 * @covers WordPoints_Entity_Term_Description
 * @covers WordPoints_Entity_Term_Name
 * @covers WordPoints_Entity_Term_Parent
 * @covers WordPoints_Entity_User
 * @covers WordPoints_Entity_User_Role
 * @covers WordPoints_Entity_User_Roles
 *
 * @covers WordPoints_Entity_Attr_Stored_DB_Table
 * @covers WordPoints_Entity_Relationship_Dynamic_Stored_Field
 * @covers WordPoints_Entity_Relationship_Stored_Field
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

		$this->factory             = new WP_UnitTest_Factory();
		$this->factory->wordpoints = WordPoints_PHPUnit_Factory::$factory;

		$entities = array(
			'user' => array(
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
							'class'        => 'WordPoints_Entity_User_Roles',
							'primary'      => 'user',
							'related'      => 'user_role{}',
							'storage_info' => array(
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
			'post' => array(
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
							'conditions' => array(
								array(
									'field' => 'post_type',
									'value' => 'post',
								),
							),
						),
					),
					'create_func'    => array( $this, 'create_post' ),
					'delete_func'    => array( $this, 'delete_post' ),
					'cant_view'      => $this->factory->post->create(
						array( 'post_status' => 'private' )
					),
					'children'       => array(
						'author' => array(
							'class'        => 'WordPoints_Entity_Post_Author',
							'primary'      => 'post\post',
							'related'      => 'user',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_author',
								),
							),
						),
						'comment_count' => array(
							'class'        => 'WordPoints_Entity_Post_Comment_Count',
							'data_type'    => 'integer',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'comment_count',
								),
							),
						),
						'content' => array(
							'class'        => 'WordPoints_Entity_Post_Content',
							'data_type'    => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_content',
								),
							),
						),
						'date_modified' => array(
							'class'        => 'WordPoints_Entity_Post_Date_Modified',
							'data_type'    => 'mysql_datetime',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_modified',
								),
							),
						),
						'date_published' => array(
							'class'        => 'WordPoints_Entity_Post_Date_Published',
							'data_type'    => 'mysql_datetime',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_date',
								),
							),
						),
						'excerpt' => array(
							'class'        => 'WordPoints_Entity_Post_Excerpt',
							'data_type'    => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_excerpt',
								),
							),
						),
						'parent' => array(
							'class'        => 'WordPoints_Entity_Post_Parent',
							'primary'      => 'post\post',
							'related'      => 'post\post',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_parent',
								),
							),
						),
						'terms\post_tag' => array(
							'class'        => 'WordPoints_Entity_Post_Terms',
							'primary'      => 'post\post',
							'related'      => 'term\post_tag{}',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'             => 'table',
									'table_name'       => $wpdb->term_relationships,
									'primary_id_field' => 'object_id',
									'related_id_field' => array(
										'table_name' => $wpdb->term_taxonomy,
										'on'         => array(
											'primary_field' => 'term_taxonomy_id',
											'join_field' => 'term_taxonomy_id',
										),
									),
								),
							),
						),
						'title' => array(
							'class'        => 'WordPoints_Entity_Post_Title',
							'data_type'    => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'post_title',
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
					'get_id'         => array( $this, 'get_comment_id' ),
					'human_id_field' => 'comment_content',
					'storage_info'   => array(
						'type' => 'db',
						'info' => array(
							'type'       => 'table',
							'table_name' => $wpdb->comments,
							'conditions' => array(
								array(
									'field' => array(
										'table_name' => $wpdb->posts,
										'on'         => array(
											'primary_field' => 'comment_post_ID',
											'join_field' => 'ID',
											'condition_field' => 'post_type',
										),
									),
									'value' => 'post',
								),
							),
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
							'class'        => 'WordPoints_Entity_Comment_Author',
							'primary'      => 'comment\post',
							'related'      => 'user',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'user_id',
								),
							),
						),
						'content' => array(
							'class'        => 'WordPoints_Entity_Comment_Content',
							'data_type'    => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'comment_content',
								),
							),
						),
						'date' => array(
							'class'        => 'WordPoints_Entity_Comment_Date',
							'data_type'    => 'mysql_datetime',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'comment_date',
								),
							),
						),
						'parent' => array(
							'class'        => 'WordPoints_Entity_Comment_Parent',
							'primary'      => 'comment\post',
							'related'      => 'comment\post',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'comment_parent',
								),
							),
						),
						'post' => array(
							'class'        => 'WordPoints_Entity_Comment_Post',
							'primary'      => 'comment\post',
							'related'      => 'post\post',
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
			'term' => array(
				array(
					'class'          => 'WordPoints_Entity_Term',
					'slug'           => 'term\category',
					'id_field'       => 'term_id',
					'human_id_field' => 'name',
					'storage_info'   => array(
						'type' => 'db',
						'info' => array(
							'type'       => 'table',
							'table_name' => $wpdb->terms,
							'conditions' => array(
								array(
									'field' => array(
										'table_name' => $wpdb->term_taxonomy,
										'on'         => array(
											'primary_field' => 'term_id',
											'join_field' => 'term_id',
											'condition_field' => 'taxonomy',
										),
									),
									'value' => 'category',
								),
							),
						),
					),
					'create_func'    => array( $this, 'create_category' ),
					'delete_func'    => array( $this, 'delete_category' ),
					'children'       => array(
						'count' => array(
							'class'        => 'WordPoints_Entity_Term_Count',
							'data_type'    => 'integer',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'            => 'table',
									'table_name'      => $wpdb->term_taxonomy,
									'attr_field'      => 'count',
									'entity_id_field' => 'term_id',
								),
							),
						),
						'name' => array(
							'class'        => 'WordPoints_Entity_Term_Name',
							'data_type'    => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'  => 'field',
									'field' => 'name',
								),
							),
						),
						'description' => array(
							'class'        => 'WordPoints_Entity_Term_Description',
							'data_type'    => 'text',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'            => 'table',
									'table_name'      => $wpdb->term_taxonomy,
									'attr_field'      => 'description',
									'entity_id_field' => 'term_id',
								),
							),
						),
						'parent' => array(
							'class'        => 'WordPoints_Entity_Term_Parent',
							'primary'      => 'term\category',
							'related'      => 'term\category',
							'storage_info' => array(
								'type' => 'db',
								'info' => array(
									'type'             => 'table',
									'table_name'       => $wpdb->term_taxonomy,
									'primary_id_field' => 'term_id',
									'related_id_field' => 'parent',
								),
							),
						),
					),
				),
			),
			'user_role' => array(
				array(
					'class'        => 'WordPoints_Entity_User_Role',
					'slug'         => 'user_role',
					'id_field'     => 'name',
					'get_human_id' => array( $this, 'get_role_display_name' ),
					'storage_info' => array(
						'type' => 'array',
						'info' => array( 'type' => 'method' ),
					),
					'create_func'  => array( $this->factory->wordpoints->user_role, 'create_and_get' ),
					'delete_func'  => 'remove_role',
				),
			),
		);

		return $entities;
	}

	/**
	 * Gets the ID for a comment.
	 *
	 * Needed because we compare the value strictly, and it is just a string instead
	 * of an int.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_Comment $comment The comment.
	 *
	 * @return int The comment ID.
	 */
	public function get_comment_id( $comment ) {
		return (int) $comment->comment_ID;
	}

	/**
	 * Creates a category.
	 *
	 * @since 2.4.0
	 *
	 * @return WP_Term $category The category.
	 */
	public function create_category() {
		return $this->factory->category->create_and_get(
			array( 'parent' => $this->factory->category->create() )
		);
	}

	/**
	 * Deletes a category.
	 *
	 * @since 2.4.0
	 *
	 * @param int $category_id The category ID.
	 */
	public function delete_category( $category_id ) {
		wp_delete_term( $category_id, 'category' );
	}
}

// EOF
