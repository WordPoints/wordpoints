<?php

/**
 * Test case for the points component update to 2.1.0.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hooks_API_Un_Installer.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Points_Un_Installer::update_network_to_2_1_0
 * @covers WordPoints_Points_Un_Installer::update_single_to_2_1_0
 * @covers WordPoints_Points_Un_Installer::update_site_to_2_1_0
 * @covers WordPoints_Points_Legacy_Hook_To_Reaction_Importer
 */
class WordPoints_Points_2_1_0_Update_Test extends WordPoints_Points_UnitTestCase {

	/**
	 * @since 2.1.0
	 */
	protected $previous_version = '2.0.0';

	/**
	 * Test that it imports legacy points hooks on install.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_legacy_hooks
	 */
	public function test_imports_points_hooks( $legacy_slug, $settings, $imported_settings ) {

		$hook_type = "wordpoints_{$legacy_slug}_points_hook";
		$handler   = wordpointstests_add_points_hook( $hook_type, $settings );

		$this->assertEquals(
			array( 'points' => array( "{$hook_type}-1" ) )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array( 1 => $settings )
			, $handler->get_instances( 'standard' )
		);

		$this->update_component();

		$this->assertEquals(
			array( 'points' => array() )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array()
			, $handler->get_instances( 'standard' )
		);

		$reaction_store = wordpoints_hooks()->get_reaction_store( 'points' );

		$reactions = $reaction_store->get_reactions();

		$this->assertCount( 1, $reactions );

		$imported_settings['points_type'] = 'points';
		$imported_settings['points']      = $settings['points'];
		$imported_settings['reactor']     = 'points_legacy';

		$this->assertEquals( $imported_settings, $reactions[0]->get_all_meta() );

		$this->assertArrayHasKey(
			$hook_type
			, get_option( 'wordpoints_legacy_points_hooks_disabled' )
		);

		$this->assertEquals(
			array(
				array(
					'order'       => 0,
					'id_base'     => $hook_type,
					'instance'    => $settings,
					'points_type' => 'points',
					'reaction_id' => $reactions[0]->get_id(),
				),
			)
			, get_option( 'wordpoints_imported_points_hooks' )
		);

		$this->assertHookFires( $legacy_slug, $settings );
	}

	/**
	 * Test that it imports legacy points hooks on install.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_legacy_hooks
	 *
	 * @requires WordPoints network-active
	 */
	public function test_imports_points_hooks_network( $legacy_slug, $settings, $imported_settings ) {

		WordPoints_Points_Hooks::get_network_mode();
		wordpoints_hooks()->set_current_mode( 'standard' );

		$this->test_imports_points_hooks(
			$legacy_slug,
			$settings,
			$imported_settings
		);
	}

	/**
	 * Provides legacy hook import data.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Data for legacy hook imports.
	 */
	public function data_provider_legacy_hooks() {
		return array(
			'registration' => array(
				'legacy_slug' => 'registration',
				'settings'    => array( 'points' => 100 ),
				'import_settings' => array(
					'target'          => array( 'user' ),
					'event'           => 'user_register',
					'description'     => 'Registering with the site.',
					'log_text'        => 'Registration.',
					'legacy_log_type' => 'register',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'post' => array(
				'legacy_slug' => 'post',
				'settings'    => array(
					'points'       => 20,
					'post_type'    => 'post',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'post\post', 'author', 'user' ),
					'event'           => 'post_publish\post',
					'description'     => 'New Post published.',
					'log_text'        => 'Post published.',
					'legacy_log_type' => 'post_publish',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'page' => array(
				'legacy_slug' => 'post',
				'settings'    => array(
					'points'       => 20,
					'post_type'    => 'page',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'post\page', 'author', 'user' ),
					'event'           => 'post_publish\page',
					'description'     => 'New Page published.',
					'log_text'        => 'Page published.',
					'legacy_log_type' => 'post_publish',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'attachment' => array(
				'legacy_slug' => 'post',
				'settings'    => array(
					'points'       => 20,
					'post_type'    => 'attachment',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'post\attachment', 'author', 'user' ),
					'event'           => 'media_upload',
					'description'     => 'New Media published.',
					'log_text'        => 'Media published.',
					'legacy_log_type' => 'post_publish',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'comment' => array(
				'legacy_slug' => 'comment',
				'settings'    => array(
					'points'       => 10,
					'post_type'    => 'post',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'comment\post', 'author', 'user' ),
					'event'           => 'comment_leave\post',
					'description'     => 'Leaving a new comment on a Post.',
					'log_text'        => 'Comment on a Post.',
					'legacy_log_type' => 'comment_approve',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'comment_on_page' => array(
				'legacy_slug' => 'comment',
				'settings'    => array(
					'points'       => 10,
					'post_type'    => 'page',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'comment\page', 'author', 'user' ),
					'event'           => 'comment_leave\page',
					'description'     => 'Leaving a new comment on a Page.',
					'log_text'        => 'Comment on a Page.',
					'legacy_log_type' => 'comment_approve',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'comment_received' => array(
				'legacy_slug' => 'comment_received',
				'settings'    => array(
					'points'       => 10,
					'post_type'    => 'post',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'comment\post', 'post\post', 'post\post', 'author', 'user' ),
					'event'           => 'comment_leave\post',
					'description'     => 'Receiving a comment on a Post.',
					'log_text'        => 'Received a comment on a Post.',
					'legacy_log_type' => 'comment_received',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'comment_received_on_page' => array(
				'legacy_slug' => 'comment_received',
				'settings'    => array(
					'points'       => 10,
					'post_type'    => 'page',
					'auto_reverse' => 1,
				),
				'import_settings' => array(
					'target'          => array( 'comment\page', 'post\page', 'post\page', 'author', 'user' ),
					'event'           => 'comment_leave\page',
					'description'     => 'Receiving a comment on a Page.',
					'log_text'        => 'Received a comment on a Page.',
					'legacy_log_type' => 'comment_received',
					'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
				),
			),
			'periodic' => array(
				'legacy_slug' => 'periodic',
				'settings'    => array( 'points' => 10, 'period' => DAY_IN_SECONDS ),
				'import_settings' => array(
					'target'          => array( 'current:user' ),
					'event'           => 'user_visit',
					'description'     => 'Visiting the site at least once in a day.',
					'log_text'        => 'Daily points.',
					'legacy_log_type' => 'periodic',
				),
			),
		);
	}

	/**
	 * Test that it doesn't import hooks that it isn't supposed to.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_legacy_hooks_not_to_import
	 */
	public function test_does_not_import_points_hooks( $legacy_slug, $settings ) {

		delete_option( 'wordpoints_legacy_points_hooks_disabled' );

		$hook_type = "wordpoints_{$legacy_slug}_points_hook";
		$handler = wordpointstests_add_points_hook( $hook_type, $settings );

		$this->assertEquals(
			array( 'points' => array( "{$hook_type}-1" ) )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array( 1 => $settings )
			, $handler->get_instances( 'standard' )
		);

		$this->update_component();

		$this->assertEquals(
			array( 'points' => array( "{$hook_type}-1" ) )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array( 1 => $settings )
			, $handler->get_instances( 'standard' )
		);

		$reaction_store = wordpoints_hooks()->get_reaction_store( 'points' );

		$this->assertEmpty( $reaction_store->get_reactions() );

		$this->assertArrayNotHasKey(
			$hook_type
			, get_option( 'wordpoints_legacy_points_hooks_disabled' )
		);

		$this->assertEmpty( get_option( 'wordpoints_imported_points_hooks' ) );
	}

	/**
	 * Provides data for legacy hooks not to import.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] Data for legacy hooks not to import.
	 */
	public function data_provider_legacy_hooks_not_to_import() {
		return array(
			'no_reversal' => array(
				'legacy_slug' => 'post',
				'settings'    => array(
					'points'       => 20,
					'post_type'    => 'post',
					'auto_reverse' => 0,
				),
			),
			'extra_settings' => array(
				'legacy_slug' => 'registration',
				'settings'    => array(
					'points' => 20,
					'other'  => 'a',
				),
			),
		);
	}

	/**
	 * Test that it imports points hooks that use a custom description properly.
	 *
	 * @since 2.1.0
	 */
	public function test_imports_registration_points_hooks_custom_description() {

		$description = 'Custom description';
		$handler     = wordpointstests_add_points_hook(
			'wordpoints_registration_points_hook'
			, array( '_description' => $description )
		);

		$this->assertEquals(
			array( 'points' => array( 'wordpoints_registration_points_hook-1' ) )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array( 1 => array( 'points' => 100, '_description' => $description ) )
			, $handler->get_instances( 'standard' )
		);

		$this->update_component();

		$this->assertEquals(
			array( 'points' => array() )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array()
			, $handler->get_instances( 'standard' )
		);

		$reaction_store = wordpoints_hooks()->get_reaction_store( 'points' );

		$reactions = $reaction_store->get_reactions();

		$this->assertCount( 1, $reactions );

		$this->assertEquals(
			array(
				'target'          => array( 'user' ),
				'points_type'     => 'points',
				'points'          => 100,
				'reactor'         => 'points_legacy',
				'event'           => 'user_register',
				'description'     => $description,
				'log_text'        => 'Registration.',
				'legacy_log_type' => 'register',
				'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
			)
			, $reactions[0]->get_all_meta()
		);

		$this->assertArrayHasKey(
			'wordpoints_registration_points_hook'
			, get_option( 'wordpoints_legacy_points_hooks_disabled' )
		);

		$this->assertEquals(
			array(
				array(
					'order'       => 0,
					'id_base'     => 'wordpoints_registration_points_hook',
					'points_type' => 'points',
					'reaction_id' => $reactions[0]->get_id(),
					'instance'    => array(
						'points'       => 100,
						'_description' => $description,
					),
				),
			)
			, get_option( 'wordpoints_imported_points_hooks' )
		);
	}

	/**
	 * Test that it splits a hook for ALL post types on import.
	 *
	 * @since 2.1.0
	 */
	public function test_imports_post_type_all_points_hook() {

		$legacy_slug = 'post';

		$settings    = array(
			'points'       => 20,
			'post_type'    => 'ALL',
			'auto_reverse' => 1,
		);

		$imported_settings = array(
			'target'          => array( 'post\post', 'author', 'user' ),
			'event'           => 'post_publish\post',
			'description'     => 'New post published.',
			'log_text'        => 'Post published.',
			'legacy_log_type' => 'post_publish',
			'points_legacy_reversals' => array( 'toggle_off' => 'toggle_on' ),
		);

		$hook_type = "wordpoints_{$legacy_slug}_points_hook";
		$handler   = wordpointstests_add_points_hook( $hook_type, $settings );

		$this->assertEquals(
			array( 'points' => array( "{$hook_type}-1" ) )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array( 1 => $settings )
			, $handler->get_instances( 'standard' )
		);

		$this->update_component();

		$this->assertEquals(
			array( 'points' => array() )
			, WordPoints_Points_Hooks::get_points_types_hooks()
		);

		$this->assertEquals(
			array()
			, $handler->get_instances( 'standard' )
		);

		$reaction_store = wordpoints_hooks()->get_reaction_store( 'points' );

		$reactions = $reaction_store->get_reactions();

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		$this->assertCount( count( $post_types ), $reactions );

		$imported_settings['points_type'] = 'points';
		$imported_settings['points']      = $settings['points'];
		$imported_settings['reactor']     = 'points_legacy';

		$imported_points_hooks = get_option( 'wordpoints_imported_points_hooks' );

		$i = 0;

		foreach ( $post_types as $post_type ) {

			$labels = get_post_type_labels( $post_type );

			$post_type_settings = $imported_settings;
			$post_type_settings['log_text'] = str_replace(
				'Post'
				, $labels->singular_name
				, $post_type_settings['log_text']
			);

			if ( $post_type->name === 'attachment' ) {

				$post_type_settings['event'] = 'media_upload';

			} else {

				$post_type_settings['event'] = str_replace(
					'\post'
					, '\\' . $post_type->name
					, $post_type_settings['event']
				);
			}

			$post_type_settings['target'] = str_replace(
				'\post'
				, '\\' . $post_type->name
				, $post_type_settings['target']
			);

			$this->assertEquals(
				$post_type_settings
				, $reactions[ $i ]->get_all_meta()
			);

			$this->assertHookFires(
				$legacy_slug
				, array_merge( $settings, array( 'post_type' => $post_type->name ) )
			);

			$this->assertEquals(
				array(
					'order'       => 0,
					'id_base'     => $hook_type,
					'instance'    => $settings,
					'points_type' => 'points',
					'reaction_id' => $reactions[ $i ]->get_id(),
				)
				, $imported_points_hooks[ $i ]
			);

			$i++;
		}

		$this->assertArrayHasKey(
			$hook_type
			, get_option( 'wordpoints_legacy_points_hooks_disabled' )
		);
	}

	//
	// Assertions
	//

	/**
	 * Test that an imported hook reaction fires.
	 *
	 * @since 2.1.0
	 *
	 * @param string $legacy_slug The legacy hook slug.
	 * @param array  $settings    The legacy hook settings.
	 */
	protected function assertHookFires( $legacy_slug, $settings ) {

		$user_id = $this->factory->user->create();

		switch ( $legacy_slug ) {

			case 'registration': break;

			case 'post':
				$post_id = $this->factory->post->create(
					array(
						'post_author' => $user_id,
						'post_type'   => $settings['post_type'],
					)
				);
			break;

			case 'comment':
				$comment_id = $this->factory->comment->create(
					array(
						'user_id' => $user_id,
						'comment_post_ID' => $this->factory->post->create(
							array(
								'post_type' => $settings['post_type'],
							)
						),
					)
				);
			break;

			case 'comment_received':
				$comment_id = $this->factory->comment->create(
					array(
						'comment_post_ID' => $this->factory->post->create(
							array(
								'post_author' => $user_id,
								'post_type'   => $settings['post_type'],
							)
						),
					)
				);
			break;

			case 'periodic':
				wp_set_current_user( $user_id );

				do_action_ref_array( 'wp', array( &$GLOBALS['wp'] ) );
			break;

			default:
				$this->fail( 'Missing hook works assertion for legacy slug ' . $legacy_slug );
		}

		$this->assertEquals(
			$settings['points']
			, wordpoints_get_points( $user_id, 'points' )
		);

		switch ( $legacy_slug ) {

			case 'registration':
				$this->delete_user( $user_id );
			break;

			case 'post':
				if ( 'attachment' === $settings['post_type'] ) {
					wp_delete_post( $post_id );
				} else {
					wp_update_post(
						array( 'ID' => $post_id, 'post_status' => 'draft' )
					);
				}
			break;

			case 'comment':
			case 'comment_received':
				wp_trash_comment( $comment_id );
			break;

			case 'periodic': return;

			default:
				$this->fail( 'Missing hook works assertion for legacy slug ' . $legacy_slug );
		}

		$this->assertEquals(
			0
			, wordpoints_get_points( $user_id, 'points' )
		);
	}
}

// EOF
