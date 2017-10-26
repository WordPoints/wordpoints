<?php

/**
 * Extensions admin list table class.
 *
 * @package WordPoints\Administration
 * @since 2.4.0
 */

/**
 * Display a table of installed extensions.
 *
 * @since 1.1.0 As WordPoints_Modules_List_Table.
 * @since 2.4.0
 */
class WordPoints_Admin_List_Table_Extensions extends WP_List_Table {

	/**
	 * The current instance of the class.
	 *
	 * @since 1.9.0
	 *
	 * @var WordPoints_Admin_List_Table_Extensions
	 */
	protected static $instance;

	/**
	 * Get the current instance of the list table.
	 *
	 * @since 1.9.0
	 *
	 * @return WordPoints_Admin_List_Table_Extensions The current instance.
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Construct the class.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args {@see WP_List_Table::__construct()}.
	 */
	public function __construct( $args = array() ) {

		global $status, $page;

		parent::__construct(
			array(
				'plural' => 'wordpoints-extensions',
				'screen' => ( isset( $args['screen'] ) ) ? $args['screen'] : null,
			)
		);

		self::$instance = $this;

		$status = 'all';

		$module_statuses = array(
			'active',
			'inactive',
			'recently_activated',
			'search',
			'upgrade',
		);

		/**
		 * Filter the module statuses on the modules administration panel.
		 *
		 * @since 1.1.0
		 *
		 * @param array $statuses The module statuses. The defaults are 'active',
		 *                        'inactive', 'recently_activated', 'upgrade', and
		 *                        'search'.
		 */
		$module_statuses = apply_filters( 'wordpoints_module_statuses', $module_statuses );

		if ( isset( $_REQUEST['module_status'] ) && in_array( wp_unslash( $_REQUEST['module_status'] ), $module_statuses, true ) ) { // WPCS: CSRF OK.
			$status = sanitize_key( $_REQUEST['module_status'] ); // WPCS: CSRF OK.
		}

		if ( isset( $_REQUEST['s'] ) ) { // WPCS: CSRF OK.
			$_SERVER['REQUEST_URI'] = add_query_arg( 's', sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ); // WPCS: CSRF OK.
		}

		$page = $this->get_pagenum();
	}

	/**
	 * Get the table's HTML classes.
	 *
	 * @since 1.1.0
	 *
	 * @return array The classes for the table.
	 */
	public function get_table_classes() {

		return array( 'widefat', $this->_args['plural'], 'modules', 'plugins' );
	}

	/**
	 * @since 2.3.0
	 */
	protected function get_default_primary_column_name() {
		return 'name';
	}

	/**
	 * Check whether the user has required permissions from AJAX.
	 *
	 * @since 1.1.0
	 *
	 * @return bool Whether the current user has the required capabilities.
	 */
	public function ajax_user_can() {

		return current_user_can( 'activate_wordpoints_extensions' );
	}

	/**
	 * Prepare the modules for display in the table.
	 *
	 * @since 1.1.0
	 */
	public function prepare_items() {

		global $status, $modules, $totals, $page, $orderby, $order, $s;

		wp_reset_vars( array( 'orderby', 'order', 's' ) );

		$modules = array(
			/**
			 * All of the modules to be displayed in the list table.
			 *
			 * @since 1.1.0
			 *
			 * @param array $modules All of the installed modules.
			 */
			'all'                => apply_filters( 'all_wordpoints_modules', wordpoints_get_modules() ),
			'search'             => array(),
			'active'             => array(),
			'inactive'           => array(),
			'recently_activated' => array(),
			'upgrade'            => array(),
		);

		$updates = wordpoints_get_extension_updates();

		$user_can_update = (
			current_user_can( 'update_wordpoints_extensions' )
			&& (
				! is_multisite()
				|| (
					is_network_admin()
					&& current_user_can( 'manage_network_wordpoints_extensions' )
				)
			)
		);

		$show_network_active = false;

		if ( ! $this->screen->in_admin( 'network' ) ) {

			// Recently active modules.
			$recently_activated = get_option( 'wordpoints_recently_activated_modules' );

			if ( ! is_array( $recently_activated ) ) {
				add_option( 'wordpoints_recently_activated_modules', array(), '', 'no' );
				$recently_activated = array();
			}

			foreach ( $recently_activated as $key => $time ) {

				if ( $time + WEEK_IN_SECONDS < time() ) {
					unset( $recently_activated[ $key ] );
				}
			}

			update_option( 'wordpoints_recently_activated_modules', $recently_activated );

			// Showing network-active/network-only modules.
			$show = current_user_can( 'wordpoints_manage_network_modules' );

			/**
			 * Filters whether to display network-active modules alongside
			 * modules active for the current site.
			 *
			 * This also controls the display of inactive network-only modules
			 * (modules with "Network: true" in the module header).
			 *
			 * Modules cannot be network-activated or network-deactivated from
			 * this screen.
			 *
			 * @since 2.3.0
			 *
			 * @param bool $show Whether to show network-active modules. Default
			 *                   is whether the current user can manage network
			 *                   modules (ie. a Network Admin).
			 */
			$show_network_active = apply_filters( 'wordpoints_show_network_active_modules', $show );

		} // End if ( not network admin ).

		foreach ( (array) $modules['all'] as $module_file => $module_data ) {

			// Filter into individual sections.
			if (
				is_multisite()
				&& ! $this->screen->in_admin( 'network' )
				&& is_network_only_wordpoints_module( $module_file )
			) {

				if ( $show_network_active ) {
					$modules['inactive'][ $module_file ] = $module_data;
				} else {
					unset( $modules['all'][ $module_file ] );
				}

			} elseif (
				! $this->screen->in_admin( 'network' )
				&& is_wordpoints_module_active_for_network( $module_file )
			) {

				if ( $show_network_active ) {
					$modules['active'][ $module_file ] = $module_data;
				} else {
					unset( $modules['all'][ $module_file ] );
				}

			} elseif (
				(
					! $this->screen->in_admin( 'network' )
					&& is_wordpoints_module_active( $module_file )
				) || (
					$this->screen->in_admin( 'network' )
					&& is_wordpoints_module_active_for_network( $module_file )
				)
			) {

				$modules['active'][ $module_file ] = $module_data;

			} else {

				// Was the module recently activated?
				if ( ! $this->screen->in_admin( 'network' ) && isset( $recently_activated[ $module_file ] ) ) {
					$modules['recently_activated'][ $module_file ] = $module_data;
				}

				$modules['inactive'][ $module_file ] = $module_data;

			} // End if ().

			/*
			 * If the user can update modules, add an 'update' key to the module data
			 * for modules that have an available update.
			 */
			if ( $user_can_update && $updates->has_update( $module_file ) ) {
				$modules['upgrade'][ $module_file ] = $modules['all'][ $module_file ];
			}

		} // End foreach ( modules ).

		if ( $s ) {

			$status            = 'search';
			$modules['search'] = array_filter( $modules['all'], array( $this, 'search_callback' ) );
		}

		/**
		 * Filter the modules displayed in the modules list table.
		 *
		 * @since 1.1.0
		 *
		 * @param array $modules {
		 *        The modules to display in the list table, grouped by status.
		 *
		 *        @type array $all                All of the modules.
		 *        @type array $search             The modules matching the current search.
		 *        @type array $active             The active modules.
		 *        @type array $inactive           The modules that aren't active.
		 *        @type array $recently_activated Modules that were recently active.
		 *        @type array $upgrade            Modules that have an update available.
		 * }
		 */
		$modules = apply_filters( 'wordpoints_modules_list_table_items', $modules );

		// Calculate the totals.
		$totals = array();

		foreach ( $modules as $type => $list ) {
			$totals[ $type ] = count( $list );
		}

		if ( empty( $modules[ $status ] ) && ! in_array( $status, array( 'all', 'search' ), true ) ) {
			$status = 'all';
		}

		$this->items = array();

		foreach ( $modules[ $status ] as $module_file => $module_data ) {

			$this->items[ $module_file ] = wordpoints_get_module_data( wordpoints_extensions_dir() . '/' . $module_file, false );
		}

		$total_this_page = $totals[ $status ];

		if ( ! $orderby ) {
			$orderby = 'name';
		}

		$order = strtoupper( $order );

		uasort( $this->items, array( $this, 'order_callback' ) );

		$modules_per_page = $this->get_items_per_page( str_replace( '-', '_', $this->screen->id . '_per_page' ), 999 );

		$start = ( $page - 1 ) * $modules_per_page;

		if ( $total_this_page > $modules_per_page ) {
			$this->items = array_slice( $this->items, $start, $modules_per_page );
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_this_page,
				'per_page'    => $modules_per_page,
			)
		);
	}

	/**
	 * Callback for filtering modules based on a search.
	 *
	 * @since 1.1.0
	 *
	 * @param array $module_data The data for a module.
	 *
	 * @return bool Whether any of the module's data matches the search.
	 */
	private function search_callback( $module_data ) {

		static $term;

		if ( is_null( $term ) && isset( $_REQUEST['s'] ) ) { // WPCS: CSRF OK.
			$term = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // WPCS: CSRF OK.
		}

		foreach ( $module_data as $value ) {

			if ( false !== stripos( strip_tags( $value ), $term ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Callback for sorting the modules.
	 *
	 * @link https://php.net/uasort uasort()
	 *
	 * @since 1.1.0
	 *
	 * @param array $module_a The data for a module.
	 * @param array $module_b The data for another module.
	 *
	 * @return int How the modules compare.
	 */
	private function order_callback( $module_a, $module_b ) {

		global $orderby, $order;

		$a = $module_a[ $orderby ];
		$b = $module_b[ $orderby ];

		if ( $a === $b ) {
			return 0;
		}

		if ( 'DESC' === $order ) {
			return strcasecmp( $b, $a );
		} else {
			return strcasecmp( $a, $b );
		}
	}

	/**
	 * Display a message if no modules are found.
	 *
	 * @since 1.1.0
	 */
	public function no_items() {

		global $modules;

		if ( ! empty( $modules['all'] ) ) {
			esc_html_e( 'No extensions found.', 'wordpoints' );
		} else {
			esc_html_e( 'There are not any extensions installed.', 'wordpoints' );
		}
	}

	/**
	 * Get the names of the columns of the table.
	 *
	 * @since 1.1.0
	 *
	 * @return array The names of the table columns, indexed by slug.
	 */
	public function get_columns() {

		return array(
			'cb'          => '<input type="checkbox" />',
			'name'        => esc_html__( 'Extension', 'wordpoints' ),
			'description' => esc_html__( 'Description', 'wordpoints' ),
		);
	}

	/**
	 * Get an array of links to different table views.
	 *
	 * @since 1.1.0
	 *
	 * @return array Table view links.
	 */
	public function get_views() {

		global $totals, $status;

		$status_links = array();

		foreach ( $totals as $type => $count ) {

			if ( ! $count ) {
				continue;
			}

			switch ( $type ) {

				case 'all':
					// translators: Count.
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'extensions', 'wordpoints' );
				break;

				case 'active':
					// translators: Count.
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				case 'recently_activated':
					// translators: Count.
					$text = _n( 'Recently Active <span class="count">(%s)</span>', 'Recently Active <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				case 'inactive':
					// translators: Count.
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				case 'upgrade':
					// translators: Count.
					$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				default:
					$text = $type;
			}

			if ( 'search' !== $type ) {

				/**
				 * Filter the text for a module status link for the module list table.
				 *
				 * @since 1.1.0
				 *
				 * @param string $text  The link text.
				 * @param int    $count The number of modules matching this filter.
				 */
				$text = apply_filters( "wordpoints_modules_status_link_text-{$type}", $text, $count );

				$status_links[ $type ] = sprintf(
					"<a href='%s' %s>%s</a>"
					, esc_url( add_query_arg( 'module_status', $type, self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) )
					, ( $type === $status ) ? ' class="current"' : ''
					, sprintf( $text, number_format_i18n( $count ) )
				);
			}

		} // End foreach ( $totals ).

		return $status_links;
	}

	/**
	 * Get actions on the modules that may be performed in bulk.
	 *
	 * @since 1.1.0
	 *
	 * @return array {
	 *         The actions that may be performed in bulk.
	 *
	 *         @type string $activate-selected   Activate/network activate.
	 *         @type string $deactivate-selected Deactivate/network deactivate.
	 *         @type string $update-selected     Update.
	 *         @type string $delete-selected     Delete.
	 * }
	 */
	public function get_bulk_actions() {

		global $status;

		$actions = array();

		if ( 'active' !== $status ) {
			$actions['activate-selected'] = ( $this->screen->in_admin( 'network' ) ) ? esc_html__( 'Network Activate', 'wordpoints' ) : esc_html__( 'Activate', 'wordpoints' );
		}

		if ( 'inactive' !== $status && 'recent' !== $status ) {
			$actions['deactivate-selected'] = ( $this->screen->in_admin( 'network' ) ) ? esc_html__( 'Network Deactivate', 'wordpoints' ) : esc_html__( 'Deactivate', 'wordpoints' );
		}

		if (
			( ! is_multisite() || $this->screen->in_admin( 'network' ) )
			&& current_user_can( 'delete_wordpoints_extensions' )
			&& 'active' !== $status
		) {
			$actions['delete-selected'] = esc_html__( 'Delete', 'wordpoints' );
		}

		if (
			( ! is_multisite() || is_network_admin() )
			&& current_user_can( 'update_wordpoints_extensions' )
		) {
			$actions['update-selected'] = esc_html__( 'Update', 'wordpoints' );
		}

		/**
		 * Filter the bulk module action links for the modules table.
		 *
		 * @since 1.1.0
		 *
		 * @param array  $actions The bulk action links.
		 * @param string $status  The current module status being displayed.
		 */
		$actions = apply_filters( 'wordpoints_module_bulk_actions', $actions, $status );

		return $actions;
	}

	/**
	 * @since 2.4.0
	 */
	protected function display_tablenav( $which ) {

		// Back-compat for the old nonce name.
		$this->_args['plural'] = 'modules';

		parent::display_tablenav( $which );

		$this->_args['plural'] = 'wordpoints-extensions';
	}

	/**
	 * Display extra table navigation links.
	 *
	 * @since 1.1.0
	 *
	 * @param string $which Which table navigation is being displayed, top or bottom.
	 */
	public function extra_tablenav( $which ) {

		global $status;

		// Forward-compat.
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-wordpoints-extensions', 'nonce' );
		}

		if ( 'recently_activated' !== $status ) {
			return;
		}

		echo '<div class="alignleft actions">';

		if ( ! $this->screen->in_admin( 'network' ) ) {
			submit_button( __( 'Clear List', 'wordpoints' ), 'button', 'clear-recent-list', false );
		}

		echo '</div>';
	}

	/**
	 * Display the table's rows.
	 *
	 * @since 1.1.0
	 */
	public function display_rows() {

		foreach ( $this->items as $module_file => $module_data ) {

			$this->single_row( array( $module_file, $module_data ) );
		}
	}

	/**
	 * Display a single table row.
	 *
	 * @since 1.1.0
	 *
	 * @param array $item The module file and the module data.
	 */
	public function single_row( $item ) {

		global $status;

		list( $module_file, $module_data ) = $item;
		$context = $status;

		if ( $this->screen->in_admin( 'network' ) ) {
			$is_active = is_wordpoints_module_active_for_network( $module_file );
		} else {
			$is_active = is_wordpoints_module_active( $module_file );
		}

		$class = ( $is_active ) ? 'active' : 'inactive';

		if ( wordpoints_get_extension_updates()->has_update( $module_file ) ) {
			$class .= ' update';
		}

		/**
		 * Filter the class of a row of the module's list table.
		 *
		 * @since 1.1.0
		 *
		 * @param string $class The current classes.
		 * @param string $module_file The module that the row is for.
		 * @param string $module_data The module's data.
		 * @param string $context     The current status context in which the modules are being displayed.
		 */
		$class = apply_filters( 'wordpoints_module_list_row_class', $class, $module_file, $module_data, $context );

		?>
		<tr id="<?php echo esc_attr( sanitize_title( $module_data['name'] ) ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php $this->single_row_columns( $item, $class, $is_active ); ?>
		</tr>
		<?php

		/**
		 * After each row in the module list table.
		 *
		 * @since 1.1.0
		 *
		 * @param string $module_file The main file of the module this row was for.
		 * @param array  $module_data The module's info.
		 * @param string $status      The status of the module's being displayed.
		 */
		do_action( 'wordpoints_after_module_row', $module_file, $module_data, $status );

		/**
		 * After row for a module is displayed in the list table.
		 *
		 * @see wordpoints_after_module_row
		 *
		 * @since 1.1.0
		 */
		do_action( "wordpoints_after_module_row_{$module_file}", $module_file, $module_data, $status );

	} // End function single_row().

	/**
	 * Display the columns for a single row.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $item      The module data.
	 * @param string $class     The class for the module row.
	 * @param bool   $is_active Whether the module is active.
	 */
	public function single_row_columns( $item, $class = '', $is_active = true ) {

		list( $module_file, $module_data ) = $item;

		$module_data['extra']['module_file'] = $module_file;

		if ( $this->screen->in_admin( 'network' ) ) {

			$restricted_network_active = false;
			$restricted_network_only   = false;

		} else {

			$restricted_network_active = ( is_multisite() && is_wordpoints_module_active_for_network( $module_file ) );
			$restricted_network_only   = ( is_multisite() && is_network_only_wordpoints_module( $module_file ) && ! $is_active );
		}

		$module_data['extra']['is_active'] = $is_active;
		$module_data['extra']['restricted_network_active'] = $restricted_network_active;
		$module_data['extra']['restricted_network_only']   = $restricted_network_only;

		list( $columns, $hidden, , $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {

			$is_hidden  = in_array( $column_name, $hidden, true );
			$is_primary = $primary === $column_name;

			switch ( $column_name ) {

				case 'cb':
					$this->column_cb( $module_data );
				break;

				case 'name':
					$this->column_name( $module_data, $is_hidden, $is_primary );
				break;

				case 'description':
					$this->column_description( $module_data, $is_hidden, $is_primary, $class );
				break;

				default:
					$this->column_default( $module_data, $column_name, $is_hidden, $is_primary );
			}
		}

	} // End function single_row_columns().

	/**
	 * Display the checkbox row.
	 *
	 * @since 2.3.0
	 *
	 * @param array $module_data The module data.
	 */
	protected function column_cb( $module_data ) {

		$checkbox_id = 'checkbox_' . sanitize_key( $module_data['extra']['module_file'] );

		?>
		<th scope="row" class="check-column">
			<?php if ( ! $module_data['extra']['restricted_network_active'] && ! $module_data['extra']['restricted_network_only'] ) : ?>
				<label class="screen-reader-text" for="<?php echo esc_attr( $checkbox_id ); ?>">
					<?php

					echo esc_html(
						sprintf(
							// translators: Extension name.
							__( 'Select %s', 'wordpoints' )
							, $module_data['name']
						)
					);

					?>
				</label>
				<input type="checkbox" name="checked[]" value="<?php echo esc_attr( $module_data['extra']['module_file'] ); ?>" id="<?php echo esc_attr( $checkbox_id ); ?>" />
			<?php endif; ?>
		</th>
		<?php
	}

	/**
	 * Display the module name column.
	 *
	 * @since 2.3.0
	 *
	 * @param array $module_data The module data.
	 * @param bool  $is_hidden   Whether this column is hidden.
	 * @param bool  $is_primary  Whether this is the primary column.
	 */
	protected function column_name( $module_data, $is_hidden, $is_primary ) {

		?>
		<td class="module-title<?php echo ( $is_hidden ) ? ' hidden' : ''; ?><?php echo ( $is_primary ) ? ' has-row-actions column-primary' : ''; ?>">
			<strong><?php echo esc_html( $module_data['name'] ); ?></strong>
			<?php if ( $is_primary ) : ?>
				<?php echo $this->row_actions( $this->get_module_row_actions( $module_data ), true ); /* WPCS XSS OK. */ ?>
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Display the description column.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $module_data The module data.
	 * @param bool   $is_hidden   Whether the column is hidden.
	 * @param bool   $is_primary  Whether this is the primary column.
	 * @param string $class       The class for this module row.
	 */
	protected function column_description( $module_data, $is_hidden, $is_primary, $class ) {

		global $status;

		?>
		<td class="column-description desc<?php echo ( $is_hidden ) ? ' hidden' : ''; ?><?php echo ( $is_primary ) ? ' has-row-actions column-primary' : ''; ?>">
			<div class="module-description">
				<p>
					<?php if ( ! empty( $module_data['description'] ) ) : ?>
						<?php echo wp_kses( $module_data['description'], 'wordpoints_module_description' ); ?>
					<?php else : ?>
						&nbsp;
					<?php endif; ?>
				</p>
			</div>
			<div class="<?php echo esc_attr( $class ); ?> second module-version-author-uri">
				<?php

				$module_meta = array();

				if ( ! empty( $module_data['version'] ) ) {
					// translators: Extension version.
					$module_meta[] = sprintf( esc_html__( 'Version %s', 'wordpoints' ), $module_data['version'] );
				}

				if ( ! empty( $module_data['author'] ) ) {

					$author = $module_data['author'];

					if ( ! empty( $module_data['author_uri'] ) ) {
						$author = '<a href="' . esc_url( $module_data['author_uri'] ) . '">' . esc_html( $module_data['author'] ) . '</a>';
					}

					// translators: Author name.
					$module_meta[] = sprintf( __( 'By %s', 'wordpoints' ), $author );
				}

				if ( ! empty( $module_data['uri'] ) ) {
					$module_meta[] = '<a href="' . esc_url( $module_data['uri'] ) . '">' . esc_html__( 'Visit extension site', 'wordpoints' ) . '</a>';
				}

				/**
				 * Filter meta data and links for the module row in the module list table.
				 *
				 * These include the module version, and links to the module
				 * URI and author URI if provided.
				 *
				 * @since 1.1.0
				 *
				 * @param array  $module_meta The meta links for the module.
				 * @param string $module_file The main file of the module.
				 * @param array  $module_data The info about the module.
				 * @param string $status      The module status being displayed.
				 */
				$module_meta = apply_filters( 'wordpoints_module_row_meta', $module_meta, $module_data['extra']['module_file'], $module_data, $status );

				echo wp_kses( implode( ' | ', $module_meta ), 'data' );

				?>
			</div>
			<?php if ( $is_primary ) : ?>
				<?php echo $this->row_actions( $this->get_module_row_actions( $module_data ), true ); /* WPCS XSS OK. */ ?>
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Display a custom column with default markup.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $module_data The module data.
	 * @param string $column_name The column name.
	 * @param bool   $is_hidden   Whether this column is hidden.
	 * @param bool   $is_primary  Whether this is the primary column.
	 */
	protected function column_default( $module_data, $column_name, $is_hidden = false, $is_primary = false ) {

		?>
		<td class="<?php echo sanitize_html_class( $column_name ); ?> column-<?php echo sanitize_html_class( $column_name ); ?><?php echo ( $is_hidden ) ? ' hidden' : ''; ?><?php echo ( $is_primary ) ? ' has-row-actions column-primary' : ''; ?>">
			<?php
			/**
			 * Display the row contents for a custom column in the module list table.
			 *
			 * @since 1.1.0
			 *
			 * @param string $column_name The name of the column being displayed.
			 * @param string $module_file The main file of the current module.
			 * @param array  $module_data The module's info.
			 */
			do_action( 'wordpoints_manage_modules_custom_column', $column_name, $module_data['module_file'], $module_data );
			?>
			<?php if ( $is_primary ) : ?>
				<?php echo $this->row_actions( $this->get_module_row_actions( $module_data ), true ); /* WPCS XSS OK. */ ?>
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Get the row actions for a module.
	 *
	 * @since 1.8.0
	 * @since 2.3.0 Just one argument is now expected, $module_data.
	 *
	 * @param array $module_data The module's file header data, and some extra data
	 *                           in the 'extra' array, including the 'module_file',
	 *                           and whether the module 'is_active', is
	 *                           'restricted_network_active', and is
	 *                           'restricted_network_only'.
	 *
	 * @return string[] The action links for this module.
	 */
	private function get_module_row_actions( $module_data ) {

		global $page, $s, $status;

		$context = $status;

		$module_file = $module_data['extra']['module_file'];
		$args        = $module_data['extra'];

		// Pre-order.
		$actions = array(
			'deactivate' => '',
			'activate'   => '',
			'delete'     => '',
		);

		$url = self_admin_url( 'admin.php?page=wordpoints_extensions&module=' . $module_file . '&module_status=' . $context . '&paged=' . $page . '&s=' . $s );

		if ( $this->screen->in_admin( 'network' ) ) {

			if ( $args['is_active'] ) {

				if ( current_user_can( 'manage_network_wordpoints_extensions' ) ) {
					// translators: Extension name.
					$actions['deactivate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'deactivate', $url ), "deactivate-module_{$module_file}" ) . '" aria-label="' . esc_attr( sprintf( __( 'Network deactivate %s', 'wordpoints' ), $module_data['name'] ) ) . '">' . esc_html__( 'Network Deactivate', 'wordpoints' ) . '</a>';
				}

			} else {

				if ( current_user_can( 'manage_network_wordpoints_extensions' ) ) {
					// translators: Extension name.
					$actions['activate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'activate', $url ), "activate-module_{$module_file}" ) . '" aria-label="' . esc_attr( sprintf( __( 'Network activate %s', 'wordpoints' ), $module_data['name'] ) ) . '" class="edit">' . esc_html__( 'Network Activate', 'wordpoints' ) . '</a>';
				}

				if ( current_user_can( 'delete_wordpoints_extensions' ) && ! is_wordpoints_module_active( $module_file ) ) {
					// translators: Extension name.
					$actions['delete'] = '<a href="' . wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_extensions&action=delete-selected&amp;checked[]=' . $module_file . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s ), 'bulk-modules' ) . '" aria-label="' . esc_attr( sprintf( __( 'Delete %s', 'wordpoints' ), $module_data['name'] ) ) . '" class="delete">' . esc_html__( 'Delete', 'wordpoints' ) . '</a>';
				}
			}

		} else {

			if ( $args['restricted_network_active'] ) {

				$actions = array( 'network_active' => __( 'Network Active', 'wordpoints' ) );

			} elseif ( $args['restricted_network_only'] ) {

				$actions = array( 'network_only' => __( 'Network Only', 'wordpoints' ) );

			} elseif ( $args['is_active'] ) {

				// translators: Extension name.
				$actions['deactivate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'deactivate', $url ), "deactivate-module_{$module_file}" ) . '" aria-label="' . esc_attr( sprintf( __( 'Deactivate %s', 'wordpoints' ), $module_data['name'] ) ) . '">' . esc_html__( 'Deactivate', 'wordpoints' ) . '</a>';

			} else {

				// translators: Extension name.
				$actions['activate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'activate', $url ), "activate-module_{$module_file}" ) . '" aria-label="' . esc_attr( sprintf( __( 'Activate %s', 'wordpoints' ), $module_data['name'] ) ) . '" class="edit">' . esc_html__( 'Activate', 'wordpoints' ) . '</a>';

				if ( ! is_multisite() && current_user_can( 'delete_wordpoints_extensions' ) ) {
					// translators: Extension name.
					$actions['delete'] = '<a href="' . wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_extensions&action=delete-selected&amp;checked[]=' . $module_file . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s ), 'bulk-modules' ) . '" aria-label="' . esc_attr( sprintf( __( 'Delete %s', 'wordpoints' ), $module_data['name'] ) ) . '" class="delete">' . esc_html__( 'Delete', 'wordpoints' ) . '</a>';
				}
			}

		} // End if ( network admin ) {} else {}.

		$prefix = $this->screen->in_admin( 'network' ) ? 'network_admin_' : '';

		/**
		 * Filter the module action links.
		 *
		 * Each action link is an HTML anchor attribute. The availability of each link
		 * is subject to the capabilities of the user, and whether the module is
		 * active or not.
		 *
		 * @since 1.1.0
		 *
		 * @param array $actions {
		 *        The action links for the module.
		 *
		 *        @type string $deactivate Deactivate/network deactivate the module.
		 *        @type string $activate   Activate/network activate the module.
		 *        @type string $delete     Delete the module. Only available if the module is deactivated.
		 * }
		 * @param string $module_file The main file of the module the action links are for.
		 * @param array  $module_data The module's info.
		 * @param string $context     The status of the modules being displayed.
		 */
		$actions = apply_filters( $prefix . 'wordpoints_module_action_links', array_filter( $actions ), $module_file, $module_data, $context );

		/**
		 * Filter the action links for a specific module.
		 *
		 * @see module_action_links The module action links filter.
		 *
		 * @since 1.1.0
		 */
		return apply_filters( $prefix . "wordpoints_module_action_links_{$module_file}", $actions, $module_file, $module_data, $context );
	}

} // class WordPoints_Admin_List_Table_Extensions

// EOF
