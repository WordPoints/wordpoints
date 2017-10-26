<?php

/**
 * Base un/installer class.
 *
 * @package WordPoints
 * @since 2.3.0
 */

/**
 * Base class to be extended for un/installing a plugin/component/module.
 *
 * @since 1.8.0
 * @deprecated 2.4.0 Use the new installables API instead.
 */
abstract class WordPoints_Un_Installer_Base {

	/**
	 * Bitmask value directing an install to be performed.
	 *
	 * @since 2.1.0
	 *
	 * @const int
	 */
	const DO_INSTALL = 0;

	/**
	 * Bitmask value directing an install to be skipped.
	 *
	 * @since 2.1.0
	 *
	 * @const int
	 */
	const SKIP_INSTALL = 1;

	/**
	 * Bitmask value indicating that manual installation is required.
	 *
	 * @since 2.1.0
	 *
	 * @const int
	 */
	const REQUIRES_MANUAL_INSTALL = 2;

	//
	// Protected Vars.
	//

	/**
	 * The type of entity.
	 *
	 * For example, 'module' or 'component'.
	 *
	 * Note that this is singular, even though in the 'wordpoints_data' option the
	 * plural forms are used for legacy reasons.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The slug of this entity.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The code version of the entity.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * The prefix to use for the name of the options the un/installer uses.
	 *
	 * @since 1.8.0
	 * @deprecated 2.0.0 The $slug and $type properties are used instead.
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
	 * Database schema for this entity.
	 *
	 * @since 1.0.0
	 *
	 * @var array[] $schema {
	 *      @type array[] $single {
	 *            Schema for a single site (non-multisite) install.
	 *
	 *             @type string[] $tables DB field schema for tables (i.e., the part of the
	 *                                    CREATE TABLE query within the main parenthesis)
	 *                                    indexed by table name (base DB prefix will be
	 *                                    prepended).
	 *      }
	 *      @type array[] $site Schema for each site in a multisite network. See
	 *                          $single for list of keys. Note that 'tables' will be
	 *                          prepended with blog prefix instead.
	 *      @type array[] $network Schema for a multisite network. See $single for
	 *                             list of keys.
	 *      @type array[] $local Schema for each site in a multisite network, and on
	 *                           a single site install. See $single for list of keys.
	 *      @type array[] $global Schema for a multisite network and on a single site
	 *                            install. See $single for list of keys.
	 *      @type array[] $universal Schema for $single, $site, and $network. See
	 *                               $single for list of keys.
	 * }
	 */
	protected $schema = array();

	/**
	 * List of things to uninstall.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Added support for uninstalling meta boxes.
	 * @since 2.1.0 Deprecated $list_tables in favor of $*['list_tables'].
	 * @since 2.4.0 Added support for uninstalling transients.
	 *
	 * @type array[] $uninstall {
	 *       Different kinds of things to uninstall.
	 *
	 *       @type array[] $list_tables Deprecated. Use the list tables key in one of
	 *                                  the below options instead.
	 *       @type array[] $single {
	 *             Things to be uninstalled on a single site (non-multisite) install.
	 *
	 *             @type string[] $user_meta    A list of keys for user metadata to delete.
	 *             @type string[] $options      A list of options to delete.
	 *             @type string[] $transients   A list of transients to delete.
	 *             @type string[] $widgets      A list of widget slugs to uninstall.
	 *             @type string[] $points_hooks A list of points hooks to uninstall.
	 *             @type string[] $tables       A list of tables to uninstall. Base
	 *                                          DB prefix will be prepended.
	 *             @type string[] $comment_meta A list of keys for comment metadata
	 *                                          to delete.
	 *             @type array[]  $meta_boxes {
	 *                   Admin screens with meta boxes to uninstall, keyed by screen slug.
	 *
	 *                   @type string   $parent  The slug of the parent screen.
	 *                                           Defaults to 'wordpoints'. Use
	 *                                           'toplevel' if no parent.
	 *                   @type string[] $options Extra meta box options provided by
	 *                                           this screen.
	 *             }
	 *             @type array[] $list_tables {
	 *                   Admin screens with list tables to uninstall, keyed by screen slug.
	 *
	 *                   @type string   $parent  The slug of the parent screen.
	 *                   @type string[] $options The options provided by this screen.
	 *                                           Defaults to [ 'per_page' ].
	 *             }
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

	/**
	 * Installable object for the entity.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Installable
	 */
	protected $installable;

	//
	// Public Methods.
	//

	/**
	 * Constructs the un/installer with the entity's slug and version.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug    The slug of the entity.
	 * @param string $version The current code version of the entity.
	 */
	public function __construct( $slug = null, $version = null ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		if ( ! isset( $slug ) ) {
			_doing_it_wrong( __METHOD__, 'The $slug parameter is required.', '2.0.0' );
		}

		if ( ! isset( $version ) ) {
			_doing_it_wrong( __METHOD__, 'The $version parameter is required.', '2.0.0' );
		}

		$this->slug        = $slug;
		$this->version     = $version;
		$this->installable = new WordPoints_Installable_Basic(
			$this->type
			, $this->slug
			, $this->version
		);

		if ( isset( $this->option_prefix ) ) {
			_deprecated_argument( __METHOD__, '2.0.0', 'The $option_prefix property is deprecated.' );
		}
	}

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

		$this->no_interruptions();

		$hooks      = wordpoints_hooks();
		$hooks_mode = $hooks->get_current_mode();
		$hooks->set_current_mode( 'standard' );

		$this->before_install();

		/**
		 * Include the upgrade script so that we can use dbDelta() to create DBs.
		 *
		 * @since 1.8.0
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( is_multisite() ) {

			$this->context = 'network';
			$hooks->set_current_mode( 'network' );

			$this->install_network();

			$this->context = 'site';
			$hooks->set_current_mode( 'standard' );

			if ( $network ) {

				$this->set_network_installed();

				$skip_per_site_install = $this->skip_per_site_install();

				if ( ! ( $skip_per_site_install & self::SKIP_INSTALL ) ) {

					$ms_switched_state = new WordPoints_Multisite_Switched_State();
					$ms_switched_state->backup();

					foreach ( $this->get_all_site_ids() as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->install_site();
					}

					$ms_switched_state->restore();
				}

				if ( $skip_per_site_install & self::REQUIRES_MANUAL_INSTALL ) {

					// We'll check this later and let the user know that per-site
					// install was skipped.
					$this->set_network_install_skipped();
				}

			} else {

				$this->install_site();
				$this->add_installed_site_id();
			}

		} else {

			$this->context = 'single';
			$this->install_single();

		} // End if ( is_multisite() ) else.

		$hooks->set_current_mode( $hooks_mode );
	}

	/**
	 * Run the install routine on a certain site on the network.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The ID of the site to install on.
	 */
	public function install_on_site( $site_id ) {

		$this->action       = 'install';
		$this->network_wide = true;

		$this->no_interruptions();

		$hooks      = wordpoints_hooks();
		$hooks_mode = $hooks->get_current_mode();
		$hooks->set_current_mode( 'standard' );

		$this->before_install();

		/**
		 * Include the upgrade script so that we can use dbDelta() to create DBs.
		 *
		 * @since 1.8.0
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$this->context = 'site';

		switch_to_blog( $site_id );
		$this->install_site();
		restore_current_blog();

		$hooks->set_current_mode( $hooks_mode );
	}

	/**
	 * Run the uninstallation routine.
	 *
	 * @since 1.8.0
	 */
	public function uninstall() {

		$this->action = 'uninstall';

		$this->load_base_dependencies();
		$this->load_dependencies();

		$this->no_interruptions();

		$hooks      = wordpoints_hooks();
		$hooks_mode = $hooks->get_current_mode();
		$hooks->set_current_mode( 'standard' );

		$this->before_uninstall();

		if ( is_multisite() ) {

			if ( $this->do_per_site_uninstall() ) {

				$this->context = 'site';

				$ms_switched_state = new WordPoints_Multisite_Switched_State();
				$ms_switched_state->backup();

				$site_ids = $this->get_installed_site_ids();

				foreach ( $site_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->uninstall_site();
				}

				$ms_switched_state->restore();

				unset( $ms_switched_state, $site_ids );
			}

			$this->context = 'network';
			$hooks->set_current_mode( 'network' );

			$this->uninstall_network();

			$this->delete_installed_site_ids();

			// If WordPoints is being uninstalled, the options will already have been
			// deleted, and calling these methods will actually create them again.
			if ( 'wordpoints' !== $this->slug ) {
				$this->unset_network_installed();
				$this->unset_network_install_skipped();
				$this->unset_network_update_skipped();
			}

		} else {

			$this->context = 'single';
			$this->uninstall_single();

		} // End if ( is_multisite() ) else.

		$hooks->set_current_mode( $hooks_mode );
	}

	/**
	 * Prepares to update the entity.
	 *
	 * @since 2.0.0
	 *
	 * @param string $from    The version to update from.
	 * @param string $to      The version to update to.
	 * @param bool   $network Whether the entity is network active. Defaults to the
	 *                        state of WordPoints itself.
	 */
	protected function prepare_to_update( $from, $to, $network ) {

		$this->action = 'update';

		if ( null === $network ) {
			$network = is_wordpoints_network_active();
		}

		$this->network_wide  = $network;
		$this->updating_from = ( null === $from ) ? $this->get_db_version() : $from;
		$this->updating_to   = ( null === $to ) ? $this->version : $to;

		$updates = array();

		foreach ( $this->updates as $version => $types ) {

			if (
				version_compare( $this->updating_from, $version, '<' )
				&& version_compare( $this->updating_to, $version, '>=' )
			) {
				$updates[ str_replace( array( '.', '-' ), '_', $version ) ] = $types;
			}
		}

		$this->updates = $updates;
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
	public function update( $from = null, $to = null, $network = null ) {

		$this->prepare_to_update( $from, $to, $network );

		if ( empty( $this->updates ) ) {
			return;
		}

		$this->no_interruptions();

		$hooks      = wordpoints_hooks();
		$hooks_mode = $hooks->get_current_mode();
		$hooks->set_current_mode( 'standard' );

		$this->before_update();

		/**
		 * Include the upgrade script so that we can use dbDelta() to create DBs.
		 *
		 * @since 1.8.0
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( is_multisite() ) {

			$this->context = 'network';
			$hooks->set_current_mode( 'network' );

			$this->update_( 'network', $this->get_updates_for( 'network' ) );

			$this->context = 'site';
			$hooks->set_current_mode( 'standard' );

			if ( $this->network_wide ) {

				$updates = $this->get_updates_for( 'site' );

				if ( $updates ) {

					if ( $this->do_per_site_update() ) {

						$ms_switched_state = new WordPoints_Multisite_Switched_State();
						$ms_switched_state->backup();

						foreach ( $this->get_installed_site_ids() as $blog_id ) {
							switch_to_blog( $blog_id );
							$this->update_( 'site', $updates );
						}

						$ms_switched_state->restore();

					} else {

						// We'll check this later and let the user know that per-site
						// update was skipped.
						$this->set_network_update_skipped();
					}
				}

			} else {

				$this->update_( 'site', $this->get_updates_for( 'site' ) );
			}

		} else {

			$this->context = 'single';
			$this->update_( 'single', $this->get_updates_for( 'single' ) );

		} // End if ( is_multisite() ) else.

		$this->after_update();

		$hooks->set_current_mode( $hooks_mode );
	}

	//
	// Protected Methods.
	//

	/**
	 * Prevent any interruptions from occurring during the update.
	 *
	 * @since 2.2.0
	 */
	protected function no_interruptions() {
		wordpoints_prevent_interruptions();
	}

	/**
	 * Check whether we should run the install for each site in the network.
	 *
	 * On large networks we don't attempt the per-site install.
	 *
	 * @since 2.1.0
	 *
	 * @return int Whether to skip the per-site installation.
	 */
	protected function skip_per_site_install() {

		return ( wp_is_large_network() )
			? self::SKIP_INSTALL | self::REQUIRES_MANUAL_INSTALL
			: self::DO_INSTALL;
	}

	/**
	 * Check whether we should run the install for each site in the network.
	 *
	 * On large networks we don't attempt the per-site install.
	 *
	 * @since 1.8.0
	 * @deprecated 2.1.0 Use self::skip_per_site_install() instead.
	 *
	 * @return bool Whether to do the per-site installation.
	 */
	protected function do_per_site_install() {

		_deprecated_function(
			__METHOD__
			, '2.1.0'
			, __CLASS__ . '::skip_per_site_install'
		);

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

		return get_sites(
			array(
				'fields'     => 'ids',
				'network_id' => get_current_network_id(),
				'number'     => 0,
			)
		);
	}

	/**
	 * Set an option in the database for this entity.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option The name of the option to set.
	 * @param mixed  $value  The value of the option.
	 */
	private function set_option( $option, $value = true ) {

		if ( isset( $this->option_prefix ) ) {
			update_site_option( "{$this->option_prefix}{$option}", $value );
		} else {
			$this->installable->{"set_{$option}"}( $value );
		}
	}

	/**
	 * Delete an option in the database for this entity.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option The name of the option to delete.
	 */
	private function unset_option( $option ) {

		if ( isset( $this->option_prefix ) ) {
			delete_site_option( "{$this->option_prefix}{$option}" );
		} else {
			$this->installable->{"unset_{$option}"}();
		}
	}

	/**
	 * Check if this entity is network installed.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether the code is network installed.
	 */
	protected function is_network_installed() {

		if ( isset( $this->option_prefix ) ) {
			return (bool) get_site_option( "{$this->option_prefix}network_installed" );
		} else {
			return $this->installable->is_network_installed();
		}
	}

	/**
	 * Set this entity's status as network-installed in the database.
	 *
	 * @since 2.0.0
	 */
	protected function set_network_installed() {
		$this->set_option( 'network_installed' );
	}

	/**
	 * Delete this entity's status as network-installed.
	 *
	 * @since 2.0.0
	 */
	protected function unset_network_installed() {
		$this->unset_option( 'network_installed' );
	}

	/**
	 * Set that the per-site network installation has been skipped in the database.
	 *
	 * @since 2.0.0
	 */
	protected function set_network_install_skipped() {
		$this->set_option( 'network_install_skipped' );
	}

	/**
	 * Delete the network-install skipped flag for this entity from the database.
	 *
	 * @since 2.0.0
	 */
	protected function unset_network_install_skipped() {
		$this->unset_option( 'network_install_skipped' );
	}

	/**
	 * Set that per-site network-updating has been skipped in the database.
	 *
	 * @since 2.0.0
	 */
	protected function set_network_update_skipped() {
		$this->set_option( 'network_update_skipped', $this->updating_from );
	}

	/**
	 * Delete the network-update skipped flag for this entity from the database.
	 *
	 * @since 2.0.0
	 */
	protected function unset_network_update_skipped() {
		$this->unset_option( 'network_update_skipped' );
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

			if ( isset( $this->option_prefix ) ) {

				$sites = wordpoints_get_array_option(
					"{$this->option_prefix}installed_sites"
					, 'site'
				);

				$sites = $this->validate_site_ids( $sites );

			} else {

				$sites = $this->installable->get_installed_site_ids();
			}
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

		if ( ! isset( $this->option_prefix ) ) {
			$this->installable->add_installed_site_id( $id );
			return;
		}

		if ( empty( $id ) ) {
			$id = get_current_blog_id();
		}

		$option_name = "{$this->option_prefix}installed_sites";

		$sites   = wordpoints_get_array_option( $option_name, 'site' );
		$sites[] = $id;

		update_site_option( $option_name, $sites );
	}

	/**
	 * Delete the list of installed sites.
	 *
	 * @since 2.0.0
	 */
	protected function delete_installed_site_ids() {

		if ( ! isset( $this->option_prefix ) ) {
			$this->installable->delete_installed_site_ids();
		} else {
			delete_site_option( "{$this->option_prefix}installed_sites" );
		}
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

		if ( empty( $site_ids ) || ! is_array( $site_ids ) ) {
			return array();
		}

		$site_ids = get_sites(
			array(
				'fields'     => 'ids',
				'network_id' => get_current_network_id(),
				'number'     => 0,
				'site__in'   => $site_ids,
			)
		);

		return $site_ids;
	}

	/**
	 * Get the database version of the entity.
	 *
	 * Note that this should be used with care during uninstall, since
	 * `$this->network_wide` isn't set by default during uninstall.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Now based on `$this->network_wide` instead of `$this->context`.
	 *
	 * @return string|false The database version of the entity, or false if not set.
	 */
	protected function get_db_version() {

		return $this->installable->get_db_version( $this->network_wide );
	}

	/**
	 * Set the version of the entity in the database.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Now based on `$this->network_wide` instead of `$this->context`.
	 *
	 * @param string $version The version of the entity.
	 */
	protected function set_db_version( $version = null ) {

		$this->installable->set_db_version( $version, $this->network_wide );
	}

	/**
	 * Remove the version of the entity from the database.
	 *
	 * @since 2.0.0
	 */
	protected function unset_db_version() {

		$this->installable->unset_db_version( 'network' === $this->context );
	}

	/**
	 * Set a component's version.
	 *
	 * For use when installing a component.
	 *
	 * @since 1.8.0
	 * @deprecated 2.0.0 Use set_db_version() method instead.
	 *
	 * @param string $component The component's slug.
	 * @param string $version   The installed component version.
	 */
	protected function set_component_version( $component, $version ) {

		_deprecated_function( __METHOD__, '2.0.0', '::set_db_version()' );

		$wordpoints_data = wordpoints_get_maybe_network_array_option(
			'wordpoints_data'
		);

		if ( empty( $wordpoints_data['components'][ $component ]['version'] ) ) {
			$wordpoints_data['components'][ $component ]['version'] = $version;
		}

		wordpoints_update_maybe_network_option( 'wordpoints_data', $wordpoints_data );
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

		$this->custom_caps      = call_user_func( $this->custom_caps_getter );
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
		$this->map_shortcuts( 'schema' );
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
	 * Install the database schema for the current site.
	 *
	 * @since 2.0.0
	 */
	protected function install_db_schema() {

		$schema = $this->get_db_schema();

		if ( ! empty( $schema ) ) {
			dbDelta( $schema );
		}
	}

	/**
	 * Get the database schema for this entity.
	 *
	 * @since 2.0.0
	 *
	 * @return string The database schema for this entity.
	 */
	public function get_db_schema() {

		if ( ! isset( $this->schema[ $this->context ]['tables'] ) ) {
			return '';
		}

		global $wpdb;

		$schema = '';

		$charset_collate = $wpdb->get_charset_collate();

		if ( 'site' === $this->context ) {
			$prefix = $wpdb->prefix;
		} else {
			$prefix = $wpdb->base_prefix;
		}

		foreach ( $this->schema[ $this->context ]['tables'] as $table_name => $table_schema ) {

			$table_name   = str_replace( '`', '``', $table_name );
			$table_schema = trim( $table_schema );

			$schema .= "CREATE TABLE {$prefix}{$table_name} (
				{$table_schema}
			) {$charset_collate};";
		}

		return $schema;
	}

	/**
	 * Run before uninstalling, but after loading dependencies.
	 *
	 * @since 1.8.0
	 */
	protected function before_uninstall() {

		$this->maybe_load_custom_caps();

		$this->prepare_uninstall_list_tables();
		$this->prepare_uninstall_non_per_site_items( 'meta_boxes' );
		$this->map_uninstall_shortcut( 'widgets', 'options', array( 'prefix' => 'widget_' ) );
		$this->map_uninstall_shortcut( 'points_hooks', 'options', array( 'prefix' => 'wordpoints_hook-' ) );

		// Add any tables to uninstall based on the db schema.
		foreach ( $this->schema as $context => $schema ) {

			if ( ! isset( $schema['tables'] ) ) {
				continue;
			}

			if ( ! isset( $this->uninstall[ $context ]['tables'] ) ) {
				$this->uninstall[ $context ]['tables'] = array();
			}

			$this->uninstall[ $context ]['tables'] = array_unique(
				array_merge(
					$this->uninstall[ $context ]['tables']
					, array_keys( $schema['tables'] )
				)
			);
		}

		// This *must* happen *after* the schema and list tables args are parsed.
		$this->map_shortcuts( 'uninstall' );
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

		_deprecated_argument(
			__METHOD__
			, '2.1.0'
			, '$this->uninstall["list_tables"] is deprecated, use $this->uninstall[*]["list_tables"] instead.'
		);

		// Loop through all of the list table screens.
		foreach ( $this->uninstall['list_tables'] as $screen_id => $args ) {

			// Back-compat for pre-2.1.0.
			if ( isset( $args['parent'] ) && '_page' === substr( $args['parent'], -5 /* _page */ ) ) {
				$args['parent'] = substr( $args['parent'], 0, -5 );
			}

			$this->uninstall['universal']['list_tables'][ $screen_id ] = $args;
		}

		$this->prepare_uninstall_non_per_site_items( 'list_tables' );
	}

	/**
	 * Prepare to uninstall items which don't need to be uninstalled per-site.
	 *
	 * Screen options are retrieved with get_user_option(), however, they are saved
	 * by update_user_option() with the $global argument set to true. Because of
	 * this, even on multisite, they are saved like regular user metadata, which is
	 * network wide, *not* prefixed for each site.
	 *
	 * So we only need to run the meta box and list table uninstall for network and
	 * single (i.e.,  "global"), even if the screen is "universal".
	 *
	 * @since 2.1.0
	 *
	 * @param string $items_key The key name of the items in the uninstall[*] arrays.
	 */
	protected function prepare_uninstall_non_per_site_items( $items_key ) {

		$map = array(
			'universal' => array( 'global' ),
			'site'      => array( 'network' ),
			'local'     => array( 'network', 'single' ),
		);

		foreach ( $map as $from => $to ) {

			if ( isset( $this->uninstall[ $from ][ $items_key ] ) ) {

				foreach ( $to as $context ) {

					if ( ! isset( $this->uninstall[ $context ][ $items_key ] ) ) {
						$this->uninstall[ $context ][ $items_key ] = array();
					}

					$this->uninstall[ $context ][ $items_key ] = array_merge(
						$this->uninstall[ $context ][ $items_key ]
						, $this->uninstall[ $from ][ $items_key ]
					);
				}

				unset( $this->uninstall[ $from ][ $items_key ] );
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
	 *
	 * @param string $type The type of shortcuts to map. Corresponds to a member var.
	 */
	protected function map_shortcuts( $type ) {

		// shortcut => canonicals
		$map = array(
			'local'     => array( 'single', 'site'  /*  -  */ ),
			'global'    => array( 'single', /* - */ 'network' ),
			'universal' => array( 'single', 'site', 'network' ),
		);

		$this->$type = array_merge(
			array_fill_keys(
				array( 'single', 'site', 'network', 'local', 'global', 'universal' )
				, array()
			)
			, $this->$type
		);

		foreach ( $map as $shortcut => $canonicals ) {
			foreach ( $canonicals as $canonical ) {
				$this->{$type}[ $canonical ] = array_merge_recursive(
					$this->{$type}[ $canonical ]
					, $this->{$type}[ $shortcut ]
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
	 * Run after updating.
	 *
	 * @since 2.0.0
	 */
	protected function after_update() {}

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
	 * Maybe update some database tables to the utf8mb4 character set.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of tables to update.
	 */
	protected function maybe_update_tables_to_utf8mb4( $type ) {

		global $wpdb;

		if ( 'utf8mb4' !== $wpdb->charset ) {
			return;
		}

		if ( 'global' === $type || 'network' === $type ) {
			$prefix = $wpdb->base_prefix;
		} else {
			$prefix = $wpdb->prefix;
		}

		foreach ( $this->schema[ $type ]['tables'] as $table_name => $schema ) {
			maybe_convert_table_to_utf8mb4( $prefix . $table_name );
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
			array(
				'user_meta'    => array(),
				'options'      => array(),
				'transients'   => array(),
				'tables'       => array(),
				'comment_meta' => array(),
				'meta_boxes'   => array(),
				'list_tables'  => array(),
			)
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

		foreach ( $uninstall['transients'] as $transient ) {
			$this->uninstall_transient( $transient );
		}

		foreach ( $uninstall['tables'] as $table ) {
			$this->uninstall_table( $table );
		}

		foreach ( $uninstall['comment_meta'] as $meta_key ) {
			$this->uninstall_metadata( 'comment', $meta_key );
		}

		foreach ( $uninstall['meta_boxes'] as $screen_id => $args ) {
			$this->uninstall_meta_boxes( $screen_id, $args );
		}

		foreach ( $uninstall['list_tables'] as $screen_id => $args ) {
			$this->uninstall_list_table( $screen_id, $args );
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

		if ( false !== strpos( $key, '%' ) ) {

			global $wpdb;

			$table = wordpoints_escape_mysql_identifier( _get_meta_table( $type ) );

			$keys = $wpdb->get_col( // WPCS: unprepared SQL OK.
				$wpdb->prepare( // WPCS: unprepared SQL OK.
					"
						SELECT `meta_key`
						FROM {$table}
						WHERE `meta_key` LIKE %s
					"
					, $key
				)
			); // WPCS: cache pass.

		} else {
			$keys = array( $key );
		}

		foreach ( $keys as $key ) {
			delete_metadata( $type, 0, wp_slash( $key ), '', true );
		}
	}

	/**
	 * Uninstall a screen with meta boxes.
	 *
	 * Any admin screen that uses meta boxes also will automatically have certain
	 * screen options associated with it. Each user is able to configure these
	 * options for themselves, and this is saved as user metadata.
	 *
	 * The default options currently are:
	 * - Hidden meta boxes.
	 * - Closed meta boxes.
	 * - Reordered meta boxes.
	 *
	 * This function will automatically remove all of these.
	 *
	 * A note on screen options: they are retrieved with get_user_option(), however,
	 * they are saved by update_user_option() with the $global argument set to true.
	 * Because of this, even on multisite, they are saved like regular user metadata,
	 * which is network wide, *not* prefixed for each site.
	 *
	 * @param string $screen_id The screen ID.
	 * @param array  $args {
	 *        Other args.
	 *
	 *        @type string   $parent  The parent screen slug, or 'toplevel' if none.
	 *                                Defaults to 'wordpoints'.
	 *        @type string[] $options The list of options to delete. Defaults to
	 *                                'closedpostboxes', 'metaboxhidden',
	 *                                'meta-box-order'.
	 * }
	 */
	protected function uninstall_meta_boxes( $screen_id, $args ) {

		$defaults = array(
			'parent'  => 'wordpoints',
			'options' => array( 'closedpostboxes', 'metaboxhidden', 'meta-box-order' ),
		);

		$args            = array_merge( $defaults, $args );
		$args['options'] = array_merge( $defaults['options'], $args['options'] );

		// Each user gets to set the options to their liking.
		foreach ( $args['options'] as $option ) {

			$this->uninstall_metadata(
				'user'
				, "{$option}_{$args['parent']}_page_{$screen_id}"
			);

			if ( 'network' === $this->context ) {
				$this->uninstall_metadata(
					'user'
					, "{$option}_{$args['parent']}_page_{$screen_id}-network"
				);
			}
		}
	}

	/**
	 * Uninstall list tables.
	 *
	 * Any admin screen that uses list tables also will automatically have certain
	 * screen options associated with it. Each user is able to configure these
	 * options for themselves, and this is saved as user metadata.
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
	 * @since 2.1.0
	 *
	 * @param string $screen_id The screen ID.
	 * @param array  $args {
	 *        Other args.
	 *
	 *        @type string   $parent  The parent screen slug, or 'toplevel' if none.
	 *                                Defaults to 'wordpoints'.
	 *        @type string[] $options The list of options to delete. Defaults to
	 *                                'per_page'.
	 * }
	 */
	protected function uninstall_list_table( $screen_id, $args ) {

		$defaults = array(
			'parent'  => 'wordpoints',
			'options' => array( 'per_page' ),
		);

		$args = array_merge( $defaults, $args );

		$network_parent = $args['parent'];
		$parent         = $network_parent;

		// The parent page is usually the same on a multisite site, but we need to
		// handle the special case of the extensions screen.
		if (
			(
				'wordpoints_extensions' === $screen_id
				|| 'wordpoints_modules' === $screen_id
			)
			&& is_multisite()
		) {
			$parent = 'toplevel';
		}

		$meta_keys = array();

		// Each user can hide specific columns of the table.
		$meta_keys[] = "manage{$parent}_page_{$screen_id}columnshidden";

		if ( 'network' === $this->context ) {
			$meta_keys[] = "manage{$network_parent}_page_{$screen_id}-networkcolumnshidden";
		}

		// Loop through each of the other options provided by this list table.
		foreach ( $args['options'] as $option ) {

			// Each user gets to set the options to their liking.
			$meta_keys[] = "{$parent}_page_{$screen_id}_{$option}";

			if ( 'network' === $this->context ) {
				$meta_keys[] = "{$network_parent}_page_{$screen_id}_network_{$option}";
			}
		}

		foreach ( $meta_keys as $meta_key ) {
			$this->uninstall_metadata( 'user', $meta_key );
		}
	}

	/**
	 * Uninstall an option.
	 *
	 * If the $option contains a % wildcard, all matching options will be retrieved
	 * and deleted.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Added support for wildcards for network options.
	 *
	 * @param string $option The option to uninstall.
	 */
	protected function uninstall_option( $option ) {

		if ( 'network' === $this->context ) {
			$this->uninstall_network_option( $option );
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
			); // WPCS: cache pass.

		} else {
			$options = array( $option );
		}

		array_map( 'delete_option', $options );
	}

	/**
	 * Uninstall a network option.
	 *
	 * @since 2.1.0
	 *
	 * @see WordPoints_Un_Installer_Base::uninstall_option()
	 *
	 * @param string $option The network option to uninstall.
	 */
	protected function uninstall_network_option( $option ) {

		if ( false !== strpos( $option, '%' ) ) {

			global $wpdb;

			$options = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT `meta_key`
						FROM `{$wpdb->sitemeta}`
						WHERE `meta_key` LIKE %s
							AND `site_id` = %d
					"
					, $option
					, $wpdb->siteid
				)
			); // WPCS: cache pass.

		} else {
			$options = array( $option );
		}

		array_map( 'delete_site_option', $options );
	}

	/**
	 * Uninstall a transient.
	 *
	 * @since 2.4.0
	 *
	 * @param string $transient The transient to uninstall.
	 */
	protected function uninstall_transient( $transient ) {

		if ( 'network' === $this->context ) {
			delete_site_transient( $transient );
		} else {
			delete_transient( $transient );
		}
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

		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->get_blog_prefix( $site_id ) . $table . '`' ); // WPCS: unprepared SQL, cache pass.
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
	protected function install_network() {

		$this->install_db_schema();

		if ( $this->network_wide ) {
			$this->set_db_version();
		}
	}

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

		if ( isset( $this->schema['site'] ) ) {
			$this->install_db_schema();
		}

		if ( ! $this->network_wide ) {
			$this->set_db_version();
		}

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

		$this->install_db_schema();
		$this->install_custom_caps();
		$this->set_db_version();
	}

	/**
	 * Load the dependencies of the main un/install routine.
	 *
	 * @since 2.1.0
	 */
	protected function load_base_dependencies() {

		require_once dirname( __FILE__ ) . '/../../../includes/constants.php';
		require_once WORDPOINTS_DIR . '/classes/class/autoloader.php';

		WordPoints_Class_Autoloader::register_dir( WORDPOINTS_DIR . '/classes' );

		require_once WORDPOINTS_DIR . '/includes/functions.php';
		require_once WORDPOINTS_DIR . '/includes/apps.php';
		require_once WORDPOINTS_DIR . '/includes/hooks.php';
		require_once WORDPOINTS_DIR . '/includes/filters.php';
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

		$this->unset_db_version();
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

		$this->unset_db_version();
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

		$this->unset_db_version();
	}
}

// EOF
