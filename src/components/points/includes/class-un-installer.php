<?php

/**
 * Class for un/installing the points component.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Un/installs the points component.
 *
 * @since 1.8.0
 */
class WordPoints_Points_Un_Installer extends WordPoints_Un_Installer_Base {

	//
	// Protected Vars.
	//

	/**
	 * @since 2.0.0
	 */
	protected $type = 'component';

	/**
	 * @since 1.8.0
	 */
	protected $updates = array(
		'1.2.0'  => array( 'single' => true, /*     -     */ 'network' => true ),
		'1.4.0'  => array( 'single' => true, 'site' => true, 'network' => true ),
		'1.5.0'  => array( /*      -      */ 'site' => true  /*      -      */ ),
		'1.5.1'  => array( 'single' => true, /*     -     */ 'network' => true ),
		'1.8.0'  => array( /*      -      */ 'site' => true  /*      -      */ ),
		'1.9.0'  => array( 'single' => true, 'site' => true, 'network' => true ),
		'1.10.0' => array( 'single' => true, /*     -     */ 'network' => true ),
		'2.0.0'  => array( 'single' => true, /*     -     */ 'network' => true ),
		'2.1.0'  => array( 'single' => true, /*     -     */ 'network' => true ),
		'2.1.4'  => array( 'single' => true, 'site' => true, /*      -      */ ),
	);

	/**
	 * @since 2.0.0
	 */
	protected $schema = array(
		'global' => array(
			'tables' => array(
				'wordpoints_points_logs' => "
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					log_type VARCHAR(255) NOT NULL,
					points BIGINT(20) NOT NULL,
					points_type VARCHAR(255) NOT NULL,
					text LONGTEXT,
					blog_id SMALLINT(5) UNSIGNED NOT NULL,
					site_id SMALLINT(5) UNSIGNED NOT NULL,
					date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY  (id),
					KEY user_id (user_id),
					KEY points_type (points_type(191)),
					KEY log_type (log_type(191))",
				'wordpoints_points_log_meta' => '
					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					log_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					meta_key VARCHAR(255) DEFAULT NULL,
					meta_value LONGTEXT,
					PRIMARY KEY  (meta_id),
					KEY log_id (log_id),
					KEY meta_key (meta_key(191))',
			),
		),
	);

	/**
	 * @since 2.0.0
	 */
	protected $uninstall = array(
		'local' => array(
			'widgets' => array(
				'wordpoints_points_logs_widget',
				'wordpoints_top_users_widget',
				'wordpoints_points_widget',
			),
			'options' => array(
				'wordpoints_%_hook_legacy',
			),
		),
		'global' => array(
			'options' => array(
				'wordpoints_disabled_points_hooks_edit_points_types',
			),
		),
		'universal' => array(
			'options' => array(
				'wordpoints_points_types',
				'wordpoints_default_points_type',
				'wordpoints_points_types_hooks',
				'wordpoints_legacy_points_hooks_disabled',
				'wordpoints_imported_points_hooks',
			),
			'points_hooks' => array(
				'wordpoints_registration_points_hook',
				'wordpoints_post_points_hook',
				'wordpoints_post_delete_points_hook',
				'wordpoints_comment_points_hook',
				'wordpoints_comment_removed_points_hook',
				'wordpoints_periodic_points_hook',
				'wordpoints_comment_received_points_hook',
			),
			'user_meta' => array(
				'wordpoints_points_period_start',
				'wordpoints_points-%',
			),
			'comment_meta' => array(
				'wordpoints_last_status-%',
			),
			'meta_boxes' => array(
				'wordpoints_points_types' => array(),
			),
		),
	);

	/**
	 * @since 2.0.0
	 */
	protected $custom_caps_getter = 'wordpoints_points_get_custom_caps';

	/**
	 * The network mode of the points hooks before the updates began.
	 *
	 * Only set if updating from pre-1.4.0.
	 *
	 * @since 1.8.0
	 *
	 * @type bool $points_hooks_network_mode
	 */
	protected $points_hooks_network_mode;

	/**
	 * @since 1.8.0
	 */
	protected function before_update() {

		parent::before_update();

		if ( 1 === version_compare( '1.4.0', $this->updating_from ) ) {
			add_filter( 'wordpoints_points_hook_update_callback', array( $this, '_1_4_0_clean_hook_settings' ), 10, 4 );
		}

		if ( 1 === version_compare( '1.5.0', $this->updating_from ) ) {

			if ( ! $this->network_wide ) {
				unset( $this->updates['1_5_0'] );
			}
		}

		if ( $this->network_wide ) {
			unset( $this->updates['1_8_0'] );
		}

		if ( 1 === version_compare( '1.9.0', $this->updating_from ) ) {

			// If we're updating to 1.4.0, we initialize the hooks early, because
			// we use them during the update.
			remove_action( 'wordpoints_modules_loaded', array( 'WordPoints_Points_Hooks', 'initialize_hooks' ) );

			WordPoints_Points_Hooks::register(
				'WordPoints_Comment_Removed_Points_Hook'
			);

			WordPoints_Points_Hooks::register(
				'WordPoints_Post_Delete_Points_Hook'
			);

			WordPoints_Points_Hooks::initialize_hooks();

			// Default to network mode off during the tests, but save the current
			// mode so we can restore it afterward.
			$this->points_hooks_network_mode = WordPoints_Points_Hooks::get_network_mode();
			WordPoints_Points_Hooks::set_network_mode( false );
		}
	}

	/**
	 * @since 1.8.0
	 */
	protected function after_update() {

		if ( isset( $this->points_hooks_network_mode ) ) {

			WordPoints_Points_Hooks::set_network_mode( $this->points_hooks_network_mode );

			remove_filter( 'wordpoints_points_hook_update_callback', array( $this, '_1_4_0_clean_hook_settings' ), 10 );
		}
	}

	/**
	 * @since 2.0.0
	 */
	protected function install_custom_caps() {

		/*
		 * Regenerate the custom caps every time on multisite, because they depend on
		 * network activation status.
		 */
		if ( 'site' === $this->context ) {
			wordpoints_remove_custom_caps( $this->custom_caps_keys );
		}

		wordpoints_add_custom_caps( $this->custom_caps );
	}

	/**
	 * @since 2.1.0
	 */
	protected function install_network() {

		parent::install_network();

		$this->disable_legacy_hooks();
	}

	/**
	 * @since 2.1.0
	 */
	protected function install_site() {

		parent::install_site();

		$this->disable_legacy_hooks();
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_single() {

		parent::install_single();

		add_option( 'wordpoints_default_points_type', '' );

		$this->disable_legacy_hooks();
	}

	/**
	 * Disable the legacy points hooks.
	 *
	 * @since 2.1.0
	 */
	protected function disable_legacy_hooks() {

		wordpoints_add_maybe_network_option(
			'wordpoints_legacy_points_hooks_disabled'
			, array(
				'wordpoints_post_points_hook' => true,
				'wordpoints_comment_points_hook' => true,
				'wordpoints_comment_received_points_hook' => true,
				'wordpoints_periodic_points_hook' => true,
				'wordpoints_registration_points_hook' => true,
			)
			, 'network' === $this->context
		);
	}

	/**
	 * @since 1.8.0
	 */
	protected function load_dependencies() {

		// For the sake of modules.
		WordPoints_Class_Autoloader::register_dir(
			WORDPOINTS_DIR . 'components/points/classes'
		);

		require_once WORDPOINTS_DIR . '/components/points/includes/constants.php';
		require_once WORDPOINTS_DIR . '/components/points/includes/functions.php';
		require_once WORDPOINTS_DIR . '/components/points/includes/points.php';
	}

	/**
	 * Update the plugin to 1.2.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_network_to_1_2_0() {

		$this->_1_2_0_remove_points_logs_for_deleted_users();
		$this->_1_2_0_regenerate_points_logs_for_deleted_posts();
		$this->_1_2_0_regenerate_points_logs_for_deleted_comments();
	}

	/**
	 * Update the plugin to 1.2.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_single_to_1_2_0() {
		$this->update_network_to_1_2_0();
	}

	/**
	 * Remove the points logs of users who have been deleted.
	 *
	 * @since 1.8.0
	 */
	protected function _1_2_0_remove_points_logs_for_deleted_users() {

		global $wpdb;

		$log_ids = $wpdb->get_col(
			"
				SELECT wppl.id
				FROM {$wpdb->wordpoints_points_logs} AS wppl
				LEFT JOIN {$wpdb->users} as u
					ON wppl.user_id = u.ID
				WHERE u.ID IS NULL
			"
		); // WPCS: cache pass.

		if ( $log_ids && is_array( $log_ids ) ) {

			$wpdb->query( // WPCS: unprepared SQL OK
				"
					DELETE
					FROM {$wpdb->wordpoints_points_logs}
					WHERE `id` IN (" . implode( ',', array_map( 'absint', $log_ids ) ) . ')
				'
			); // WPCS: cache pass (points logs weren't cached until 1.5.0).

			foreach ( $log_ids as $log_id ) {
				wordpoints_points_log_delete_all_metadata( $log_id );
			}
		}
	}

	/**
	 * Regenerate the points logs for deleted posts.
	 *
	 * @since 1.8.0
	 */
	protected function _1_2_0_regenerate_points_logs_for_deleted_posts() {

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"
				SELECT wpplm.meta_value
				FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
				LEFT JOIN {$wpdb->posts} AS p
					ON p.ID = wpplm.meta_value
				WHERE p.ID IS NULL
					AND wpplm.meta_key = 'post_id'
			"
		); // WPCS: cache pass.

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_post_points_hook' );

		if ( $post_ids && is_array( $post_ids ) && $hook ) {
			foreach ( $post_ids as $post_id ) {
				$hook->clean_logs_on_post_deletion( $post_id );
			}
		}
	}

	/**
	 * Regenerate the points logs for deleted comments.
	 *
	 * @since 1.8.0
	 */
	protected function _1_2_0_regenerate_points_logs_for_deleted_comments() {

		global $wpdb;

		$comment_ids = $wpdb->get_col(
			"
				SELECT wpplm.meta_value
				FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
				LEFT JOIN {$wpdb->comments} AS c
					ON c.comment_ID = wpplm.meta_value
				WHERE c.comment_ID IS NULL
					AND wpplm.meta_key = 'comment_id'
			"
		); // WPCS: cache pass.

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

		if ( $comment_ids && is_array( $comment_ids ) && $hook ) {
			foreach ( $comment_ids as $comment_id ) {
				$hook->clean_logs_on_comment_deletion( $comment_id );
			}
		}
	}

	/**
	 * Update a network to 1.4.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_network_to_1_4_0() {

		if ( $this->network_wide ) {

			// Split the network-wide points hooks.
			$network_mode = WordPoints_Points_Hooks::get_network_mode();
			WordPoints_Points_Hooks::set_network_mode( true );
			$this->_1_4_0_split_post_hooks();
			$this->_1_4_0_split_comment_hooks();
			WordPoints_Points_Hooks::set_network_mode( $network_mode );
		}
	}

	/**
	 * Update a site on the network to 1.4.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_4_0() {

		$this->_1_4_0_split_post_hooks();
		$this->_1_4_0_split_comment_hooks();
		$this->_1_4_0_clean_points_logs();
	}

	/**
	 * Update a single site to 1.4.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_single_to_1_4_0() {

		$this->update_site_to_1_4_0();
	}

	/**
	 * Split the post delete points hooks from the post points hooks.
	 *
	 * @since 1.8.0
	 */
	protected function _1_4_0_split_post_hooks() {

		$this->_1_4_0_split_points_hooks(
			'wordpoints_post_points_hook'
			, 'wordpoints_post_delete_points_hook'
			, 'publish'
			, 'trash'
		);
	}

	/**
	 * Split the commend removed points hooks from the comment points hooks.
	 *
	 * @since 1.8.0
	 */
	protected function _1_4_0_split_comment_hooks() {

		$this->_1_4_0_split_points_hooks(
			'wordpoints_comment_points_hook'
			, 'wordpoints_comment_removed_points_hook'
			, 'approve'
			, 'disapprove'
		);
	}

	/**
	 * Split a set of points hooks.
	 *
	 * @since 1.8.0
	 *
	 * @param string $hook      The slug of the hook type to split.
	 * @param string $new_hook  The slug of the new hook that this one is being split into.
	 * @param string $key       The settings key for the hook that holds the points.
	 * @param string $split_key The settings key for points that is being split.
	 */
	protected function _1_4_0_split_points_hooks( $hook, $new_hook, $key, $split_key ) {

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$hook_type = 'network';
			$network_ = 'network_';
		} else {
			$hook_type = 'standard';
			$network_ = '';
		}

		$new_hook = WordPoints_Points_Hooks::get_handler_by_id_base( $new_hook );
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $hook );

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();
		$instances = $hook->get_instances( $hook_type );

		// Loop through all of the post hook instances.
		foreach ( $instances as $number => $settings ) {

			// Don't split the hook if it is just a placeholder, or it's already split.
			if ( 0 === (int) $number || ! isset( $settings[ $key ], $settings[ $split_key ] ) ) {
				continue;
			}

			if ( ! isset( $settings['post_type'] ) ) {
				$settings['post_type'] = 'ALL';
			}

			// If the trash points are set, create a post delete points hook instead.
			if ( isset( $settings[ $split_key ] ) && wordpoints_posint( $settings[ $split_key ] ) ) {

				$new_hook->update_callback(
					array(
						'points'    => $settings[ $split_key ],
						'post_type' => $settings['post_type'],
					)
					, $new_hook->next_hook_id_number()
				);

				// Make sure the correct points type is retrieved for network hooks.
				$points_type = $hook->points_type( $network_ . $number );

				// Add this instance to the points-types-hooks list.
				$points_types_hooks[ $points_type ][] = $new_hook->get_id( $number );
			}

			// If the publish points are set, update the settings of the hook.
			if ( isset( $settings[ $key ] ) && wordpoints_posint( $settings[ $key ] ) ) {

				$settings['points'] = $settings[ $key ];

				$hook->update_callback( $settings, $number );

			} else {

				// If not, delete this instance.
				$hook->delete_callback( $hook->get_id( $number ) );
			}

		} // End foreach ( $instances ).

		WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );
	}

	/**
	 * Clean the settings for the post and comment points hooks.
	 *
	 * Removes old and no longer used settings from the comment and post points hooks.
	 *
	 * @since 1.8.0
	 *
	 * @filter wordpoints_points_hook_update_callback Added during the update to 1.4.0.
	 *
	 * @param array                  $instance     The settings for the instance.
	 * @param array                  $new_instance The new settings for the instance.
	 * @param array                  $old_instance The old settings for the instance.
	 * @param WordPoints_Points_Hook $hook         The hook object.
	 *
	 * @return array The filtered instance settings.
	 */
	public function _1_4_0_clean_hook_settings( $instance, $new_instance, $old_instance, $hook ) {

		if ( $hook instanceof WordPoints_Post_Points_Hook ) {
			unset( $instance['trash'], $instance['publish'] );
		} elseif ( $hook instanceof WordPoints_Comment_Points_Hook ) {
			unset( $instance['approve'], $instance['disapprove'] );
		}

		return $instance;
	}

	/**
	 * Clean the comment_approve points logs for posts that have been deleted.
	 *
	 * @since 1.8.0
	 */
	protected function _1_4_0_clean_points_logs() {

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"
				SELECT wpplm.meta_value
				FROM {$wpdb->wordpoints_points_log_meta} AS wpplm
				LEFT JOIN {$wpdb->posts} AS p
					ON p.ID = wpplm.meta_value
				LEFT JOIN {$wpdb->wordpoints_points_logs} As wppl
					ON wppl.id = wpplm.log_id
				WHERE p.ID IS NULL
					AND wpplm.meta_key = 'post_id'
					AND wppl.log_type = 'comment_approve'
			"
		); // WPCS: cache pass.

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( 'wordpoints_comment_points_hook' );

		if ( $post_ids && is_array( $post_ids ) && $hook ) {
			foreach ( $post_ids as $post_id ) {
				$hook->clean_logs_on_post_deletion( $post_id );
			}
		}
	}

	/**
	 * Update a site on the network to 1.5.0.
	 *
	 * Prior to 1.5.0, capabilities weren't automatically added to new sites when
	 * WordPoints was in network mode.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_5_0() {

		wordpoints_add_custom_caps( $this->custom_caps );
	}

	/**
	 * Update the network to 1.5.1.
	 *
	 * @since 1.8.0
	 */
	protected function update_network_to_1_5_1() {

		global $wpdb;

		if ( empty( $wpdb->charset ) ) {
			return;
		}

		$charset_collate = " CHARACTER SET {$wpdb->charset}";

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$wpdb->query( "ALTER TABLE {$wpdb->wordpoints_points_logs} CONVERT TO {$charset_collate}" ); // WPCS: unprepared SQL, cache pass.
		$wpdb->query( "ALTER TABLE {$wpdb->wordpoints_points_log_meta} CONVERT TO {$charset_collate}" ); // WPCS: unprepared SQL, cache pass.
	}

	/**
	 * Update a single site to 1.5.1.
	 *
	 * @since 1.8.0
	 */
	protected function update_single_to_1_5_1() {
		$this->update_network_to_1_5_1();
	}

	/**
	 * Update a site to 1.8.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_8_0() {
		$this->add_installed_site_id();
	}

	/**
	 * Update a network to 1.9.0.
	 *
	 * @since 1.9.0
	 */
	protected function update_network_to_1_9_0() {

		if ( $this->network_wide ) {

			// Combine the network-wide points hooks.
			$network_mode = WordPoints_Points_Hooks::get_network_mode();
			WordPoints_Points_Hooks::set_network_mode( true );
			$this->_1_9_0_combine_hooks( 'comment', 'comment_removed' );
			$this->_1_9_0_combine_hooks( 'post', 'post_delete' );
			WordPoints_Points_Hooks::set_network_mode( $network_mode );
		}
	}

	/**
	 * Update a site to 1.9.0.
	 *
	 * @since 1.9.0
	 */
	protected function update_site_to_1_9_0() {
		$this->_1_9_0_combine_hooks( 'comment', 'comment_removed' );
		$this->_1_9_0_combine_hooks( 'post', 'post_delete' );
	}

	/**
	 * Update a single site to 1.9.0.
	 *
	 * @since 1.9.0
	 */
	protected function update_single_to_1_9_0() {
		$this->_1_9_0_combine_hooks( 'comment', 'comment_removed' );
		$this->_1_9_0_combine_hooks( 'post', 'post_delete' );
	}

	/**
	 * Combine any Comment/Comment Removed or Post/Post Delete hook instance pairs.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type         The primary hook type that awards the points.
	 * @param string $reverse_type The counterpart hook type that reverses points.
	 */
	protected function _1_9_0_combine_hooks( $type, $reverse_type ) {

		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			"wordpoints_{$type}_points_hook"
		);
		$reverse_hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			"wordpoints_{$reverse_type}_points_hook"
		);

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$hook_type = 'network';
			$network_ = 'network_';
		} else {
			$hook_type = 'standard';
			$network_ = '';
		}

		$hook_instances = $hook->get_instances( $hook_type );
		$hook_reverse_instances = $reverse_hook->get_instances( $hook_type );

		$default_points = ( 'post' === $hook_type ) ? 20 : 10;
		$defaults = array( 'points' => $default_points, 'post_type' => 'ALL' );

		// Get the hooks into an array that is indexed by post type and the
		// number of points. This allows us to easily check for any counterparts when
		// we loop through the reverse type hooks below. It is even safe if a user
		// is doing something crazy like multiple hooks for the same post type.
		$hook_instances_indexed = array();

		foreach ( $hook_instances as $number => $instance ) {

			$instance = array_merge( $defaults, $instance );

			$hook_instances_indexed
				[ $hook->points_type( $network_ . $number ) ]
				[ $instance['post_type'] ]
				[ $instance['points'] ]
				[] = $number;
		}

		foreach ( $hook_reverse_instances as $number => $instance ) {

			$instance = array_merge( $defaults, $instance );

			$points_type = $reverse_hook->points_type( $network_ . $number );

			// We use empty() instead of isset() because array_pop() below may leave
			// us with an empty array as the value.
			if ( empty( $hook_instances_indexed[ $points_type ][ $instance['post_type'] ][ $instance['points'] ] ) ) {
				continue;
			}

			$comment_instance_number = array_pop(
				$hook_instances_indexed[ $points_type ][ $instance['post_type'] ][ $instance['points'] ]
			);

			// We need to unset this instance from the list of hook instances. It
			// is expected for it to be automatically reversed, and that is the
			// default setting. If we don't unset it here it will get auto-reversal
			// turned off below, which isn't what we want.
			unset( $hook_instances[ $comment_instance_number ] );

			// Now we can just delete this reverse hook instance.
			$reverse_hook->delete_callback(
				$reverse_hook->get_id( $number )
			);
		}

		// Any hooks left in the array are not paired with a reverse type hook, and
		// aren't expected to auto-reverse, so we need to turn their auto-reversal
		// setting off.
		if ( ! empty( $hook_instances ) ) {

			foreach ( $hook_instances as $number => $instance ) {
				$instance['auto_reverse'] = 0;
				$hook->update_callback( $instance, $number );
			}

			// We add a flag to the database so we'll know to enable legacy features.
			update_site_option(
				"wordpoints_{$type}_hook_legacy"
				, true
			);
		}

		// Now we check if there are any unpaired reverse type hooks. If there are
		// we'll set this flag in the database that will keep some legacy features
		// enabled.
		if ( $reverse_hook->get_instances( $hook_type ) ) {
			update_site_option(
				"wordpoints_{$reverse_type}_hook_legacy"
				, true
			);
		}
	}

	/**
	 * Update a site to 1.10.0.
	 *
	 * @since 1.10.0
	 */
	protected function update_network_to_1_10_0() {
		$this->_1_10_0_delete_post_title_points_log_meta( true );
	}

	/**
	 * Update a single site to 1.10.0.
	 *
	 * @since 1.10.0
	 */
	protected function update_single_to_1_10_0() {
		$this->_1_10_0_delete_post_title_points_log_meta();
	}

	/**
	 * Delete the no longer used 'post_title' metadata from post delete points logs.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $network_wide Whether to delete all of the metadata for the whole
	 *                           network, or just the current site (default).
	 */
	protected function _1_10_0_delete_post_title_points_log_meta( $network_wide = false ) {

		$query_args = array(
			'log_type'   => 'post_delete',
			'meta_query' => array(
				array( 'key' => 'post_title', 'compare' => 'EXISTS' ),
			),
		);

		if ( $network_wide ) {
			$query_args['blog_id'] = false;
		}

		$query = new WordPoints_Points_Logs_Query( $query_args );

		$logs = $query->get();

		foreach ( $logs as $log ) {
			wordpoints_delete_points_log_meta( $log->id, 'post_title' );
		}

		wordpoints_regenerate_points_logs( $logs );
	}

	/**
	 * Update a site to 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function update_network_to_2_0_0() {

		global $wpdb;

		// So that we can change tables to utf8mb4, we need to shorten the index
		// lengths to less than 767 bytes;
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_points_logs}
			DROP INDEX points_type,
			ADD INDEX points_type(points_type(191))
			"
		); // WPCS: cache pass.

		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_points_logs}
			DROP INDEX log_type,
			ADD INDEX log_type(log_type(191))
			"
		); // WPCS: cache pass.

		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_points_log_meta}
			DROP INDEX meta_key,
			ADD INDEX meta_key(meta_key(191))
			"
		); // WPCS: cache pass.

		$this->maybe_update_tables_to_utf8mb4( 'global' );
	}

	/**
	 * Update a single site to 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function update_single_to_2_0_0() {
		$this->update_network_to_2_0_0();
	}

	/**
	 * Update a multisite network to 2.1.0
	 *
	 * @since 2.1.0
	 */
	protected function update_network_to_2_1_0() {
		add_site_option( 'wordpoints_disabled_points_hooks_edit_points_types', true );
	}

	/**
	 * Update a single site to 2.1.0
	 *
	 * @since 2.1.0
	 */
	protected function update_single_to_2_1_0() {
		add_option( 'wordpoints_disabled_points_hooks_edit_points_types', true );
	}

	/**
	 * Update a site on the network to 2.1.4.
	 *
	 * @since 2.1.4
	 */
	protected function update_site_to_2_1_4() {
		$this->update_single_to_2_1_4();
	}

	/**
	 * Update a single site to 2.1.4.
	 *
	 * @since 2.1.4
	 */
	protected function update_single_to_2_1_4() {

		$post_types = $this->_2_1_4_get_post_types();
		$reversal_log_types = $this->_2_1_4_get_reversal_log_types( $post_types );
		$hits = $this->_2_1_4_get_hits_with_multiple_logs();

		$logs_to_delete = array();

		foreach ( $hits as $hit ) {

			$log_ids = explode( ',', $hit->log_ids );

			// If there weren't exactly two of them, we don't know what to do.
			if ( count( $log_ids ) !== 2 ) {
				continue;
			}

			$query = new WordPoints_Points_Logs_Query(
				array(
					'id__in'       => $log_ids,
					'log_type__in' => array_keys( $reversal_log_types ),
				)
			);

			// And if they aren't both the correct types, we don't know what to do.
			if ( $query->count() !== 2 ) {
				continue;
			}

			$original_log_ids = $this->_2_1_4_get_original_log_ids( $log_ids );

			$post_publish_log_id = min( array_keys( $original_log_ids ) );
			$post_update_log_id = max( array_keys( $original_log_ids ) );
			$post_publish_reverse_log_id = $original_log_ids[ $post_publish_log_id ];
			$post_update_reverse_log_id = $original_log_ids[ $post_update_log_id ];

			$query = new WordPoints_Points_Logs_Query(
				array( 'id__in' => array( $post_publish_reverse_log_id ) )
			);

			$post_publish_reverse_log = $query->get( 'row' );

			$post_type = str_replace(
				'reverse-post_publish\\'
				, ''
				, $post_publish_reverse_log->log_type
			);

			$post_id = wordpoints_get_points_log_meta(
				$post_publish_log_id
				, 'post\\' . $post_type
				, true
			);

			$post_id_2 = wordpoints_get_points_log_meta(
				$post_update_log_id
				, 'post\\' . $post_type
				, true
			);

			if ( $post_id !== $post_id_2 ) {
				continue;
			}

			$logs_to_delete[] = $post_publish_reverse_log_id;
			$logs_to_delete[] = $post_update_reverse_log_id;
			$logs_to_delete[] = $post_update_log_id;

			// Give the user their points back, as they were removed in error.
			$this->_2_1_4_revert_log( $post_publish_reverse_log );

			$this->_2_1_4_mark_unreversed( $post_publish_log_id );

			// Now clean up any later updates.
			$logs_to_delete = $this->_2_1_4_clean_other_logs(
				$post_id
				, $post_type
				, $logs_to_delete
			);

		} // End foreach ( $hits ).

		// Now the legacy logs.
		$legacy_logs = $this->_2_1_4_get_legacy_reactor_logs( $post_types );
		$post_ids = $this->_2_1_4_get_legacy_points_hook_post_ids();

		foreach ( $post_ids as $post_id ) {

			if ( ! isset( $legacy_logs[ $post_id ] ) ) {
				continue;
			}

			array_map( array( $this, '_2_1_4_revert_log' ), $legacy_logs[ $post_id ] );

			$logs_to_delete = array_merge(
				$logs_to_delete
				, wp_list_pluck( $legacy_logs[ $post_id ], 'id' )
			);

			unset( $legacy_logs[ $post_id ] );
		}

		foreach ( $legacy_logs as $logs ) {
			if ( count( $logs ) > 1 ) {

				// The first one is the original, so keep it.
				unset( $logs[0] );

				array_map( array( $this, '_2_1_4_revert_log' ), $logs );

				$logs_to_delete = array_merge(
					$logs_to_delete
					, wp_list_pluck( $logs, 'id' )
				);
			}
		}

		// Now delete the logs.
		if ( $logs_to_delete ) {
			$this->_2_1_4_delete_logs( $logs_to_delete );
		}
	}

	/**
	 * Get the post types used by the hooks API.
	 *
	 * @since 2.1.4
	 *
	 * @return string[] The post types that we award points for.
	 */
	protected function _2_1_4_get_post_types() {

		$post_types = get_post_types( array( 'public' => true ) );

		/**
		 * Filter which post types to register hook events for.
		 *
		 * @since 2.1.0
		 *
		 * @param string[] The post type slugs ("names").
		 */
		$post_types = apply_filters(
			'wordpoints_register_hook_events_for_post_types'
			, $post_types
		);

		return $post_types;
	}

	/**
	 * Get the slugs of the reversal events that we are interested in.
	 *
	 * @since 2.1.4
	 *
	 * @return array The slugs of the events.
	 */
	protected function _2_1_4_get_reversal_log_types( $post_types ) {

		$event_slugs = array();

		foreach ( $post_types as $slug ) {
			$event_slugs[ "reverse-post_publish\\{$slug}" ] = true;
		}

		return $event_slugs;
	}

	/**
	 * Get all duplicate logs for a single hit.
	 *
	 * Finds all points logs where two logs are for the same hit, and returns the
	 * IDs of those hits and the IDs of the logs for each.
	 *
	 * @since 2.1.4
	 *
	 * @return object[] Array of rows, each row consisting of the `hit_id` and
	 *                  `log_ids`, the later being the IDs of all of the logs
	 *                  concatenated together using commas.
	 */
	protected function _2_1_4_get_hits_with_multiple_logs() {

		global $wpdb;

		$hits = $wpdb->get_results(
			"
				SELECT `meta_value` AS `hit_id`, GROUP_CONCAT(`log_id`) AS `log_ids`
				FROM `{$wpdb->wordpoints_points_log_meta}`
                WHERE `meta_key` = 'hook_hit_id'
				GROUP BY `meta_value`
				HAVING COUNT(*) > 1
			"
		); // WPCS: cache OK.

		return $hits;
	}

	/**
	 * Get the IDs of the original logs for a bunch of reversal logs.
	 *
	 * @since 2.1.4
	 *
	 * @param int[] $log_ids  The IDs of the reversal logs.
	 *
	 * @return array The IDs of the original logs.
	 */
	protected function _2_1_4_get_original_log_ids( $log_ids ) {

		$original_log_ids = array();

		foreach ( $log_ids as $log_id ) {

			$original_log_id = wordpoints_get_points_log_meta(
				$log_id
				, 'original_log_id'
				, true
			);

			$original_log_ids[ $original_log_id ] = $log_id;
		}

		return $original_log_ids;
	}

	/**
	 * Delete some points logs.
	 *
	 * The hits for the logs will also be deleted.
	 *
	 * @since 2.1.4
	 *
	 * @param int[] $log_ids The IDs of the logs to delete.
	 */
	protected function _2_1_4_delete_logs( $log_ids ) {

		$hits_to_delete = array();

		global $wpdb;

		foreach ( $log_ids as $log_id ) {

			$hit_id = wordpoints_get_points_log_meta(
				$log_id
				, 'hook_hit_id'
				, true
			);

			if ( $hit_id ) {
				$hits_to_delete[] = $hit_id;
			}

			wordpoints_points_log_delete_all_metadata( $log_id );
		}

		$wpdb->query( // WPCS: unprepared SQL OK.
			"
				DELETE
				FROM `{$wpdb->wordpoints_points_logs}`
				WHERE `id` IN (" . wordpoints_prepare__in( $log_ids, '%d' ) . ')
			'
		);

		wordpoints_flush_points_logs_caches();

		// Now delete the hits.
		if ( $hits_to_delete ) {
			$this->_2_1_4_delete_hits( $hits_to_delete );
		}
	}

	/**
	 * Delete some hook hits.
	 *
	 * @since 2.1.4
	 *
	 * @param int[] $hit_ids The IDs of the hits to delete.
	 */
	protected function _2_1_4_delete_hits( $hit_ids ) {

		global $wpdb;

		foreach ( $hit_ids as $hit_id ) {

			$hit_ids[] = get_metadata(
				'wordpoints_hook_hit'
				, $hit_id
				, 'hook_hit_id'
				, true
			);

			delete_metadata( 'wordpoints_hook_hit', $hit_id, '', '', true );
		}

		$wpdb->query( // WPCS: unprepared SQL OK.
			"
				DELETE
				FROM `{$wpdb->wordpoints_hook_hits}`
				WHERE `id` IN (" . wordpoints_prepare__in( $hit_ids, '%d' ) . ')
			'
		); // WPCS: cache OK.
	}

	/**
	 * Cleans out any other logs relating to a post.
	 *
	 * @since 2.1.4
	 *
	 * @param int    $post_id        The ID of the post to clean logs for.
	 * @param string $post_type      The post type that this post is of.
	 * @param int[]  $logs_to_delete A list of logs that are being deleted.
	 *
	 * @return int[] The list of logs to be deleted.
	 */
	protected function _2_1_4_clean_other_logs( $post_id, $post_type, $logs_to_delete ) {

		$query = new WordPoints_Points_Logs_Query(
			array(
				'order'        => 'ASC',
				'order_by'      => 'id',
				'id__not_in'   => $logs_to_delete,
				'log_type__in' => array( 'post_publish\\' . $post_type ),
				'meta_query'   => array(
					array(
						'key'   => 'post\\' . $post_type,
						'value' => $post_id,
					),
				),
			)
		);

		$other_logs = $query->get();

		$query = new WordPoints_Points_Logs_Query(
			array(
				'log_type'   => 'reverse-post_publish\\' . $post_type,
				'id__not_in' => $logs_to_delete,
				'meta_query' => array(
					array(
						'key'     => 'original_log_id',
						'value'   => wp_list_pluck( $other_logs, 'id' ),
						'compare' => 'IN',
					),
				),
			)
		);

		$reversal_logs = array();

		foreach ( $query->get() as $log ) {

			$original_log_id = wordpoints_get_points_log_meta(
				$log->id
				, 'original_log_id'
				, true
			);

			$reversal_logs[ $original_log_id ] = $log;
		}

		foreach ( $other_logs as $index => $log ) {

			// If this is a log that was reversed within less than a second or so
			// of its occurrence, it is almost certainly the result of another
			// update, and it is just cluttering things up.
			if (
				isset( $reversal_logs[ $log->id ] )
				&& strtotime( $reversal_logs[ $log->id ]->date ) - strtotime( $log->date ) < 2
			) {
				$logs_to_delete[] = $log->id;
				$logs_to_delete[] = $reversal_logs[ $log->id ]->id;
			}
		}

		return $logs_to_delete;
	}

	/**
	 * Give a user back the points that a log removed.
	 *
	 * @since 2.1.4
	 *
	 * @param object $log The points log object.
	 */
	protected function _2_1_4_revert_log( $log ) {

		add_filter( 'wordpoints_points_log', '__return_false' );

		wordpoints_alter_points(
			$log->user_id
			, -$log->points
			, $log->points_type
			, $log->log_type
		);

		remove_filter( 'wordpoints_points_log', '__return_false' );
	}

	/**
	 * Mark a points log as unreversed.
	 *
	 * @since 2.1.4
	 *
	 * @param int $log_id The ID of the log to mark as unreversed.
	 */
	protected function _2_1_4_mark_unreversed( $log_id ) {

		wordpoints_delete_points_log_meta( $log_id, 'auto_reversed' );

		$hit_id = wordpoints_get_points_log_meta( $log_id, 'hook_hit_id', true );

		delete_metadata( 'wordpoints_hook_hit', $hit_id, 'reverse_fired' );
	}

	/**
	 * Get all of the legacy logs grouped by post ID.
	 *
	 * @since 2.1.4
	 *
	 * @param string[] $post_types The post types to retrieve logs for.
	 *
	 * @return array[] The logs, grouped by post ID.
	 */
	protected function _2_1_4_get_legacy_reactor_logs( $post_types ) {

		$legacy_log_types = array();

		foreach ( $post_types as $post_type ) {
			$legacy_log_types[] = "points_legacy_post_publish\\{$post_type}";
		}

		$query = new WordPoints_Points_Logs_Query(
			array( 'log_type__in' => $legacy_log_types, 'order' => 'ASC' )
		);

		$legacy_logs = array();

		foreach ( $query->get() as $legacy_log ) {

			$post_type = str_replace(
				'points_legacy_post_publish\\'
				, ''
				, $legacy_log->log_type
			);

			$post_id = wordpoints_get_points_log_meta(
				$legacy_log->id
				, "post\\{$post_type}"
				, true
			);

			$legacy_logs[ $post_id ][] = $legacy_log;
		}

		return $legacy_logs;
	}

	/**
	 * Get the post IDs from the old points hooks logs.
	 *
	 * @since 2.1.4
	 *
	 * @return int[] The post IDs for the points hooks logs.
	 */
	protected function _2_1_4_get_legacy_points_hook_post_ids() {

		global $wpdb;

		$post_ids = $wpdb->get_col(
			"
				SELECT `meta_value`
				FROM `{$wpdb->wordpoints_points_log_meta}` AS `meta`
				INNER JOIN `{$wpdb->wordpoints_points_logs}` AS `log`
					ON `log`.`id` = `meta`.`log_id`
	            WHERE `meta_key` = 'post_id'
	            	AND `log`.`log_type` = 'post_publish'
			"
		); // WPCS: cache OK.

		return $post_ids;
	}
}

return 'WordPoints_Points_Un_Installer';

// EOF
