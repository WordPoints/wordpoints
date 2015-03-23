<?php

/**
 * Abstract class for un/installing a plugin/component/module.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Base class to be extended for un/installing a plugin/component/module.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Un_Installer_Base {

	//
	// Protected Vars.
	//

	/**
	 * The prefix to use for the name of the options the un/installer uses.
	 *
	 * @since 1.8.0
	 *
	 * @type string $option_prefix
	 */
	protected $option_prefix;

	/**
	 * A list of versions of this entity with updates.
	 *
	 * @since 1.8.0
	 *
	 * @type array $updates
	 */
	protected $updates = array();

	/**
	 * Whether the entity is being installed network wide.
	 *
	 * @since 1.8.0
	 *
	 * @type bool $network_wide
	 */
	protected $network_wide;

	/**
	 * The version being updated from.
	 *
	 * @since 1.8.0
	 *
	 * @type string $updating_from
	 */
	protected $updating_from;

	/**
	 * The version being updated to.
	 *
	 * @since 1.8.0
	 *
	 * @type string $updating_to
	 */
	protected $updating_to;

	/**
	 * The action currently being performed.
	 *
	 * Possible values: 'install', 'update', 'uninstall'.
	 *
	 * @since 2.0.0
	 *
	 * @type string $action
	 */
	protected $action;

	/**
	 * The current context being un/installed/updated.
	 *
	 * Possible values: 'single', 'site', 'network'.
	 *
	 * @since 2.0.0
	 *
	 * @type string $context
	 */
	protected $context;

	/**
	 * List of things to uninstall.
	 *
	 * @since 2.0.0
	 *
	 * @type array[] $uninstall {
	 *       Different kinds of things to uninstall.
	 *
	 *       @type array[] $list_tables {
	 *             List tables to uninstall, keyed by screen slug.
	 *
	 *             @type string   $parent  The slug of the parent screen.
	 *             @type string[] $options The options provided by this screen.
	 *                                     Defaults to [ 'per_page' ].
	 *       }
	 *       @type array[] $single {
	 *             Things to be uninstalled on a single site (non-multisite) install.
	 *
	 *             @type string[] $user_meta A list of keys for user metadata to delete.
	 *             @type string[] $options   A list of options to delete.
	 *             @type string[] $widgets   A list of widget slugs to uninstall.
	 *             @type string[] $points_hooks A list of points hooks to uninstall.
	 *             @type string[] $tables       A list of tables to uninstall. Base
	 *                                          DB prefix will be prepended.
	 *             @type string[] $comment_meta A list of keys for comment metadata
	 *                                          to delete.
	 *       }
	 *       @type array[] $site Things to be uninstalled on each site in a multisite
	 *                           network. See $single for list of keys. Note that
	 *                           'tables' will be prepended with blog prefix instead.
	 *       @type array[] $network Things to be uninstalled on a multisite network.
	 *                              See $single for list of keys. $options refers to
	 *                              network options.
	 *       @type array[] $local Things to be uninstalled on each site in a multisite
	 *                            network, and on a single site install. See $single
	 *                            for list of keys.
	 *       @type array[] $global Things to be uninstalled on a multisite network
	 *                             and on a single site install. See $single for list
	 *                             of keys.
	 *       @type array[] $universal Things to be uninstalled for $single, $site,
	 *                                and $network. See $single for list of keys.
	 * }
	 */
	protected $uninstall = array();

	/**
	 * The function to use to get the user capabilities used by this entity.
	 *
	 * The function should return an array of capabilities of the format processed
	 * by {@see wordpoints_add_custom_caps()}.
	 *
	 * @since 2.0.0
	 *
	 * @type callable $custom_caps_getter
	 */
	protected $custom_caps_getter;

	/**
	 * The entity's capabilities.
	 *
	 * Used to hold the list of capabilities during install, update, and uninstall,
	 * so that they don't have to be retrieved all over again for each site (if
	 * multisite).
	 *
	 * The array is of the format needed by {@see wordpoints_add_custom_caps()}.
	 *
	 * @since 2.0.0
	 *
	 * @type array $custom_caps
	 */
	protected $custom_caps;

	/**
	 * The entity's capabilities (keys only).
	 *
	 * Used to hold the list of capabilities during install and uninstall, so that
	 * they don't have to be retrieved all over again for each site (if multisite).
	 *
	 * The array is of the form needed by {@see wordpoints_remove_custom_caps()}.
	 *
	 * @since 2.0.0
	 *
	 * @type array $custom_caps_keys
	 */
	protected $custom_caps_keys;

	//
	// Public Methods.
	//

	/**
	 * Run the install routine.
	 *
	 * @since 1.8.0
	 *
	 * @param bool $network Whether the install should be network-wide on multisite.
	 */
	public function install( $network ) {

		$this->action = 'install';

		$this->network_wide = $network;

		$this->before_install();

		/**
		 * Include the upgrade script so that we can use dbDelta() to create DBs.
		 *
		 * @since 1.8.0
		 */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if ( is_multisite() ) {

			$this->context = 'network';
			$this->install_network();

			$this->context = 'site';

			if ( $network ) {

				update_site_option( "{$this->option_prefix}network_installed", true );

				if ( $this->do_per_site_install() ) {

					$original_blog_id = get_current_blog_id();

					foreach ( $this->get_all_site_ids() as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->install_site();
					}

					switch_to_blog( $original_blog_id );

					// See http://wordpress.stackexchange.com/a/89114/27757
					unset( $GLOBALS['_wp_switched_stack'] );
					$GLOBALS['switched'] = false;

				} else {

					// We'll check this later and let the user know that per-site
					// install was skipped.
					add_site_option( "{$this->option_prefix}network_install_skipped", true );
				}

			} else {

				$this->install_site();
				$this->add_installed_site_id();
			}

		} else {

			$this->context = 'single';
			$this->install_single();
		}
	}

	/**
	 * Run the uninstallation routine.
	 *
	 * @since 1.8.0
	 */
	public function uninstall() {

		$this->action = 'uninstall';

		$this->load_dependencies();

		$this->before_uninstall();

		if ( is_multisite() ) {

			if ( $this->do_per_site_uninstall() ) {

				$this->context = 'site';

				$original_blog_id = get_current_blog_id();

				$site_ids = $this->get_installed_site_ids();

				if ( ! $this->is_network_installed() ) {
					$site_ids = $this->validate_site_ids( $site_ids );
				}

				foreach ( $site_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->uninstall_site();
				}

				switch_to_blog( $original_blog_id );

				// See http://wordpress.stackexchange.com/a/89114/27757
				unset( $GLOBALS['_wp_switched_stack'] );
				$GLOBALS['switched'] = false;
			}

			$this->context = 'network';
			$this->uninstall_network();

			delete_site_option( "{$this->option_prefix}installed_sites" );
			delete_site_option( "{$this->option_prefix}network_installed" );
			delete_site_option( "{$this->option_prefix}network_install_skipped" );

		} else {

			$this->context = 'single';
			$this->uninstall_single();
		}
	}

	/**
	 * Update the entity.
	 *
	 * @since 1.8.0
	 *
	 * @param string $from    The version to update from.
	 * @param string $to      The version to update to.
	 * @param bool   $network Whether the entity is network active. Defaults to the
	 *                        state of WordPoints itself.
	 */
	public function update( $from, $to, $network = null ) {

		$this->action = 'update';

		if ( null === $network ) {
			$network = is_wordpoints_network_active();
		}

		$this->network_wide = $network;

		$updates = array();

		foreach ( $this->updates as $version => $types ) {

			if ( version_compare( $from, $version, '<' ) ) {
				$updates[ str_replace( '.', '_', $version ) ] = $types;
			}
		}

		$this->updates = $updates;

		if ( empty( $this->updates ) ) {
			return;
		}

		$this->updating_from = $from;
		$this->updating_to = $to;

		$this->before_update();

		/**
		 * Include the upgrade script so that we can use dbDelta() to create DBs.
		 *
		 * @since 1.8.0
		 */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if ( is_multisite() ) {

			$this->context = 'network';
			$this->update_( 'network', $this->get_updates_for( 'network' ) );

			$this->context = 'site';

			if ( $this->network_wide ) {

				if ( $this->do_per_site_update() ) {

					$updates = $this->get_updates_for( 'site' );

					$original_blog_id = get_current_blog_id();

					foreach ( $this->get_installed_site_ids() as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->update_( 'site', $updates );
					}

					switch_to_blog( $original_blog_id );

					// See http://wordpress.stackexchange.com/a/89114/27757
					unset( $GLOBALS['_wp_switched_stack'] );
					$GLOBALS['switched'] = false;

				} else {

					// We'll check this later and let the user know that per-site
					// update was skipped.
					add_site_option( "{$this->option_prefix}network_update_skipped", true );
				}

			} else {

				$this->update_( 'site', $this->get_updates_for( 'site' ) );
			}

		} else {

			$this->context = 'single';
			$this->update_( 'single', $this->get_updates_for( 'single' ) );
		}
	}

	//
	// Protected Methods.
	//

	/**
	 * Check whether we should run the install for each site in the network.
	 *
	 * On large networks we don't attempt the per-site install.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether to do the per-site installation.
	 */
	protected function do_per_site_install() {

		return ! wp_is_large_network();
	}

	/**
	 * Get the IDs of all sites on the network.
	 *
	 * @since 1.8.0
	 *
	 * @return array The IDs of all sites on the network.
	 */
	protected function get_all_site_ids() {

		global $wpdb;

		return $wpdb->get_col(
			"
				SELECT `blog_id`
				FROM `{$wpdb->blogs}`
				WHERE `site_id` = {$wpdb->siteid}
			"
		);
	}

	/**
	 * Check if this entity is network installed.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether the code is network installed.
	 */
	protected function is_network_installed() {

		return (bool) get_site_option( "{$this->option_prefix}network_installed" );
	}

	/**
	 * Check if we should run the uninstall for each site on the network.
	 *
	 * On large multisite networks we don't attempt the per-site uninstall.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether to do the per-site uninstallation.
	 */
	protected function do_per_site_uninstall() {

		if ( wp_is_large_network() ) {

			if ( $this->is_network_installed() ) {
				return false;
			} elseif ( count( $this->get_installed_site_ids() ) > 10000 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if we should run the update for each site on the network.
	 *
	 * On large multisite networks we don't attempt the per-site update.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether to do the per-site update.
	 */
	protected function do_per_site_update() {

		if ( $this->is_network_installed() && wp_is_large_network() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the IDs of all sites on which this is installed.
	 *
	 * @since 1.8.0
	 *
	 * @return array The IDs of the sites where this entity is installed.
	 */
	protected function get_installed_site_ids() {

		if ( $this->is_network_installed() ) {
			$sites = $this->get_all_site_ids();
		} else {
			$sites = wordpoints_get_array_option( "{$this->option_prefix}installed_sites", 'site' );
		}

		return $sites;
	}

	/**
	 * Add a site's ID to the list of the installed sites.
	 *
	 * @since 1.8.0
	 *
	 * @param int $id The ID of the site to add. Defaults to the current site's ID.
	 */
	protected function add_installed_site_id( $id = null ) {

		if ( empty( $id ) ) {
			$id = get_current_blog_id();
		}

		$sites = wordpoints_get_array_option( "{$this->option_prefix}installed_sites", 'site' );
		$sites[] = $id;

		update_site_option( "{$this->option_prefix}installed_sites", $sites );
	}

	/**
	 * Validate a list of site IDs against the database.
	 *
	 * @since 1.8.0
	 *
	 * @param array $site_ids The site IDs to validate.
	 *
	 * @return array The validated site IDs.
	 */
	protected function validate_site_ids( $site_ids ) {

		global $wpdb;

		$site_ids = $wpdb->get_col(
			"
				SELECT `blog_id`
				FROM `{$wpdb->blogs}`
				WHERE `blog_id` IN (" . implode( ',', array_map( 'absint', $site_ids ) ) . ")
					AND `site_id` = {$wpdb->siteid}
			"
		); // Cache pass, WPCS.

		return $site_ids;
	}

	/**
	 * Set a component's version.
	 *
	 * For use when installing a component.
	 *
	 * @since 1.8.0
	 *
	 * @param string $component The component's slug.
	 * @param string $version   The installed component version.
	 */
	protected function set_component_version( $component, $version ) {

		$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

		if ( empty( $wordpoints_data['components'][ $component ]['version'] ) ) {
			$wordpoints_data['components'][ $component ]['version'] = $version;
		}

		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * Load the capabilities of the entity being un/install/updated, if needed.
	 *
	 * @since 2.0.0
	 */
	protected function maybe_load_custom_caps() {

		if ( empty( $this->custom_caps_getter ) ) {
			return;
		}

		$this->custom_caps = call_user_func( $this->custom_caps_getter );
		$this->custom_caps_keys = array_keys( $this->custom_caps );

		if ( 'uninstall' === $this->action ) {
			$this->uninstall['local']['custom_caps'] = true;
		}
	}

	/**
	 * Run before installing.
	 *
	 * @since 1.8.0
	 */
	protected function before_install() {
		$this->maybe_load_custom_caps();
	}

	/**
	 * Install capabilities on the current site.
	 *
	 * @since 2.0.0
	 */
	protected function install_custom_caps() {

		if ( ! empty( $this->custom_caps ) ) {
			wordpoints_add_custom_caps( $this->custom_caps );
		}
	}

	/**
	 * Run before uninstalling, but after loading dependencies.
	 *
	 * @since 1.8.0
	 */
	protected function before_uninstall() {

		$this->maybe_load_custom_caps();

		$this->prepare_uninstall_list_tables();
		$this->map_uninstall_shortcut( 'widgets', 'options', array( 'prefix' => 'widget_' ) );
		$this->map_uninstall_shortcut( 'points_hooks', 'options', array( 'prefix' => 'wordpoints_hook-' ) );

		// This *must* happen *after* the list tables args are parsed.
		$this->map_uninstall_shortcuts();
	}

	/**
	 * Prepare to uninstall list tables.
	 *
	 * The 'list_tables' element of the {@see self::$uninstall} configuration array
	 * can provide a list of screens which provide list tables. In this way it acts
	 * as an easy shortcut, rather than all of the metadata keys associated with a
	 * list table having to be supplied in the 'user_meta' element. Duplication is
	 * thus reduced, and it is not longer necessary to mess with the complexity of
	 * list table options.
	 *
	 * The 'list_tables' element is only a shortcut though, and this function takes
	 * the values provided in it and adds the appropriate entries to the 'user_meta'
	 * to uninstall.
	 *
	 * List tables have two main configuration options, which are both saves as user
	 * metadata:
	 * - Hidden Columns
	 * - Screen Options
	 *
	 * The hidden columns metadata is removed by default, as well as the 'per_page'
	 * screen options.
	 *
	 * A note on screen options: they are retrieved with get_user_option(), however,
	 * they are saved by update_user_option() with the $global argument set to true.
	 * Because of this, even on multisite, they are saved like regular user metadata,
	 * which is network wide, *not* prefixed for each site.
	 *
	 * @since 2.0.0
	 */
	protected function prepare_uninstall_list_tables() {

		if ( ! isset( $this->uninstall['list_tables'] ) ) {
			return;
		}

		// We define the default args outside the loop, for micro-optimization.
		$defaults = array(
			'parent' => 'wordpoints_page',
			'options' => array( 'per_page' ),
		);

		// Loop through all of the list table screens.
		foreach ( $this->uninstall['list_tables'] as $screen_id => $args ) {

			$args = array_merge( $defaults, $args );

			// The parent page is usually the same on a multisite site...
			$site_parent = $args['parent'];

			// ...But we need to handle the special case of the modules screen.
			if ( 'wordpoints_modules' === $screen_id ) {
				$site_parent = 'toplevel_page';
			}

			// Each user can hide specific columns of the table.
			$this->uninstall['single']['user_meta'][]  = "manage{$args['parent']}_{$screen_id}columnshidden";
			$this->uninstall['network']['user_meta'][] = "manage{$site_parent}_{$screen_id}columnshidden";
			$this->uninstall['network']['user_meta'][] = "manage{$args['parent']}_{$screen_id}-networkcolumnshidden";

			// Loop through each of the other options provided by this list table.
			foreach ( $args['options'] as $option ) {

				// Each user gets to set the options to their liking.
				$this->uninstall['single']['user_meta'][]  = "{$args['parent']}_{$screen_id}_{$option}";
				$this->uninstall['network']['user_meta'][] = "{$site_parent}_{$screen_id}_{$option}";
				$this->uninstall['network']['user_meta'][] = "{$args['parent']}_{$screen_id}_network_{$option}";
			}
		}
	}

	/**
	 * Map an uninstall shortcut to its actual storage location.
	 *
	 * For an explanation of what this function does, let's look at an example:
	 *
	 * Points hooks settings are currently stored in the options table. The settings
	 * for each type of points hook is stored in a separate option. The option name
	 * is the class name (all lowercase'd) prefixed with 'wordpoints_hook-'. Within
	 * the plugin, the storage and retrieval of hook settings is handled by core
	 * functions, so how they are stored is not important to extensions. It is thus
	 * possible that the method of storage could change in the future. To avoid
	 * breakage if this happens, the hooks to uninstall are just specified by slug,
	 * and the uninstaller's bootstrap should handle the rest. We also provide a
	 * uninstall_points_hook() method, which can be used if needed. However, it is
	 * really just a wrapper for uninstall_option(), and in interest of performance
	 * we don't use it in uninstall_(). Instead, we currently treat the list of
	 * 'points_hooks' as a shortcut to the prefixed options. That way they'll be
	 * handled automatically with the other options in uninstall_().
	 *
	 * This function is used to take a list like the list of points hooks, and
	 * translate it into a list of, e.g., options, before uninstallation.
	 *
	 * @since 2.0.0
	 *
	 * @param string $shortcut The shortcut key in 'single', 'site', etc.
	 * @param string $canonical The canonical key.
	 * @param array  $args {
	 *        Other arguments.
	 *
	 *        @type string $prefix The prefix to prepend the elements with before
	 *                             adding to them to the canonical array.
	 * }
	 */
	protected function map_uninstall_shortcut( $shortcut, $canonical, $args ) {

		$args = array_merge( array( 'prefix' => '' ), $args );

		$types = array( 'single', 'site', 'network', 'local', 'global', 'universal' );

		foreach ( $types as $type ) {

			if ( ! isset( $this->uninstall[ $type ][ $shortcut ] ) ) {
				continue;
			}

			foreach ( $this->uninstall[ $type ][ $shortcut ] as $slug ) {
				$this->uninstall[ $type ][ $canonical ][] = $args['prefix'] . $slug;
			}
		}
	}

	/**
	 * Map the uninstall shortcuts to their canonical elements.
	 *
	 * For the list of {@see self::$unisntall} configuration arguments, some
	 * shortcuts are provided. These reduce duplication across the canonical
	 * elements, 'single', 'site', and 'network'. These shortcuts make it possible
	 * to define, e.g., an option to be uninstalled on a single site and as a network
	 * option on multisite installs in just a single location, using the 'global'
	 * shortcut, rather than having to add it to both the 'single' and 'network'
	 * arrays.
	 *
	 * @since 2.0.0
	 */
	protected function map_uninstall_shortcuts() {

		// shortcut => canonicals
		$map = array(
			'local'     => array( 'single', 'site', /*  -  */ ),
			'global'    => array( 'single', /* - */ 'network' ),
			'universal' => array( 'single', 'site', 'network' ),
		);

		$this->uninstall = array_merge(
			array_fill_keys(
				array( 'single', 'site', 'network', 'local', 'global', 'universal' )
				, array()
			)
			, $this->uninstall
		);

		foreach ( $map as $shortcut => $canonicals ) {
			foreach ( $canonicals as $canonical ) {
				$this->uninstall[ $canonical ] = array_merge_recursive(
					$this->uninstall[ $canonical ]
					, $this->uninstall[ $shortcut ]
				);
			}
		}
	}

	/**
	 * Run before updating.
	 *
	 * @since 1.8.0
	 */
	protected function before_update() {
		$this->maybe_load_custom_caps();
	}

	/**
	 * Get the versions that request a given type of update.
	 *
	 * @since 1.8.0
	 *
	 * @param string $type The type of update.
	 *
	 * @return array The versions that request this type of update.
	 */
	protected function get_updates_for( $type ) {

		return array_keys( wp_list_filter( $this->updates, array( $type => true ) ) );
	}

	/**
	 * Run an update.
	 *
	 * @since 1.8.0
	 *
	 * @param string $type     The type of update to run.
	 * @param array  $versions The versions to run this type of update for.
	 */
	protected function update_( $type, $versions ) {

		foreach ( $versions as $version ) {
			$this->{"update_{$type}_to_{$version}"}();
		}
	}

	/**
	 * Run the default uninstall routine for a given context.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of uninstallation to perform.
	 */
	protected function uninstall_( $type ) {

		if ( empty( $this->uninstall[ $type ] ) ) {
			return;
		}

		$uninstall = array_merge(
			array( 'user_meta' => array(), 'options' => array(), 'tables' => array(), 'comment_meta' => array() )
			, $this->uninstall[ $type ]
		);

		if ( ! empty( $uninstall['custom_caps'] ) ) {
			$this->uninstall_custom_caps( $this->custom_caps_keys );
		}

		foreach ( $uninstall['user_meta'] as $meta_key ) {
			$this->uninstall_metadata( 'user', $meta_key );
		}

		foreach ( $uninstall['options'] as $option ) {
			$this->uninstall_option( $option );
		}

		foreach ( $uninstall['tables'] as $table ) {
			$this->uninstall_table( $table );
		}

		foreach ( $uninstall['comment_meta'] as $meta_key ) {
			$this->uninstall_metadata( 'comment', $meta_key );
		}
	}

	/**
	 * Uninstall a list of capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @param string[] $caps The capabilities to uninstall.
	 */
	protected function uninstall_custom_caps( $caps ) {

		wordpoints_remove_custom_caps( $caps );
	}

	/**
	 * Uninstall metadata for all objects by key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of metadata to uninstall, e.g., 'user', 'post'.
	 * @param string $key  The metadata key to delete.
	 */
	protected function uninstall_metadata( $type, $key ) {

		if ( 'user' === $type && 'site' === $this->context ) {
			$key = $GLOBALS['wpdb']->get_blog_prefix() . $key;
		}

		delete_metadata( $type, 0, $key, '', true );
	}

	/**
	 * Uninstall an option.
	 *
	 * If the $option contains a % wildcard, all matching options will be retrieved
	 * and deleted. Note that currently this doesn't apply to network options, which
	 * will ignore wildcards.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option The option to uninstall.
	 */
	protected function uninstall_option( $option ) {

		if ( 'network' === $this->context ) {
			delete_site_option( $option );
			return;
		}

		if ( false !== strpos( $option, '%' ) ) {

			global $wpdb;

			$options = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT `option_name`
						FROM `{$wpdb->options}`
						WHERE `option_name` LIKE %s
					"
					, $option
				)
			);

		} else {
			$options = array( $option );
		}

		array_map( 'delete_option', $options );
	}

	/**
	 * Uninstall a widget.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id_base The base ID of the widget to uninstall (class name).
	 */
	protected function uninstall_widget( $id_base ) {

		$this->uninstall_option( "widget_{$id_base}" );
	}

	/**
	 * Uninstall a points hook.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id_base The base ID (class) of the points hook to uninstall.
	 */
	protected function uninstall_points_hook( $id_base ) {

		$this->uninstall_option( "wordpoints_hook-{$id_base}" );
	}

	/**
	 * Uninstall a table from the database.
	 *
	 * @since 2.0.0
	 *
	 * @param string $table The name of the table to uninstall, sans DB prefix.
	 */
	protected function uninstall_table( $table ) {

		global $wpdb;

		// Null will use the current blog's prefix, 0 the base prefix.
		if ( 'site' === $this->context ) {
			$site_id = null;
		} else {
			$site_id = 0;
		}

		$table = str_replace( '`', '``', $table );

		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->get_blog_prefix( $site_id ) . $table . '`' );
	}

	//
	// [Previously] Abstract Methods.
	//

	/**
	 * Install on the network.
	 *
	 * This runs on multisite to install only the things that are common to the
	 * whole network. For example, it would add any "site" (network-wide) options.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function install_network() {}

	/**
	 * Install on a single site on the network.
	 *
	 * This runs on multisite to install on a single site on the network, which
	 * will be the current site when this method is called.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function install_site() {
		$this->install_custom_caps();
	}

	/**
	 * Install on a single site.
	 *
	 * This runs when the WordPress site is not a multisite. It should completely
	 * install the entity.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function install_single() {
		$this->install_custom_caps();
	}

	/**
	 * Load any dependencies of the uninstall code.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function load_dependencies() {}

	/**
	 * Uninstall from the network.
	 *
	 * This runs on multisite to uninstall only the things that are common to the
	 * whole network. For example, it would delete any "site" (network-wide) options.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function uninstall_network() {

		if ( ! empty( $this->uninstall['network'] ) ) {
			$this->uninstall_( 'network' );
		}
	}

	/**
	 * Uninstall from a single site on the network.
	 *
	 * This runs on multisite to uninstall from a single site on the network, which
	 * will be the current site when this method is called.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function uninstall_site() {

		if ( ! empty( $this->uninstall['site'] ) ) {
			$this->uninstall_( 'site' );
		}
	}

	/**
	 * Uninstall from a single site.
	 *
	 * This runs when the WordPress site is not a multisite. It should completely
	 * uninstall the entity.
	 *
	 * @since 1.8.0
	 * @since 2.0.0 No longer abstract.
	 */
	protected function uninstall_single() {

		if ( ! empty( $this->uninstall['single'] ) ) {
			$this->uninstall_( 'single' );
		}
	}
}

// EOF
