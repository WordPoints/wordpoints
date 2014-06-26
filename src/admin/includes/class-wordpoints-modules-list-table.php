<?php

/**
 * Modules List Table class.
 *
 * @package WordPoints\Administration
 * @since 1.1.0
 */

/**
 * Display a table of installed modules.
 *
 * @since 1.1.0
 */
final class WordPoints_Modules_List_Table extends WP_List_Table {

	/**
	 * Construct the class.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args {@see WP_List_Table::__construct()}
	 */
	public function __construct( $args = array() ) {

		global $status, $page;

		parent::__construct(
			array(
				'plural' => 'modules',
				'screen' => ( isset( $args['screen'] ) ) ? $args['screen'] : null,
			)
		);

		$status = 'all';

		$module_statuses = array( 'active', 'inactive', 'recently_activated', 'search' );

		/**
		 * Filter the module statuses on the modules administration panel.
		 *
		 * @since 1.1.0
		 *
		 * @param array $statuses The module statuses. The defaults are 'active',
		 *                        'inactive', 'recently_activated', and 'search'.
		 */
		$module_statuses = apply_filters( 'wordpoints_module_statuses', $module_statuses );

		if ( isset( $_REQUEST['module_status'] ) && in_array( $_REQUEST['module_status'], $module_statuses ) ) {
			$status = sanitize_key( $_REQUEST['module_status'] );
		}

		if ( isset( $_REQUEST['s'] ) ) {
			$_SERVER['REQUEST_URI'] = add_query_arg( 's', wp_unslash( esc_html( $_REQUEST['s'] ) ) );
		}

		$page = $this->get_pagenum();
	}

	/**
	 * Get the table's HTML classes.
	 *
	 * @since 1.1.0
	 *
	 * @return array The classes for the the table.
	 */
	public function get_table_classes() {

		return array( 'widefat', $this->_args['plural'], 'plugins' );
	}

	/**
	 * Check whether the user has required permissions from AJAX.
	 *
	 * @since 1.1.0
	 *
	 * @param bool Whether the current user has the required capabilities.
	 */
	public function ajax_user_can() {

		return current_user_can( 'activate_wordpoints_modules' );
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
		);

		$screen = $this->screen;

		if ( ! $screen->in_admin( 'network' ) ) {

			$recently_activated = get_option( 'wordpoints_recently_activated_modules', array() );

			foreach ( $recently_activated as $key => $time ) {

				if ( $time + WEEK_IN_SECONDS < time() ) {
					unset( $recently_activated[ $key ] );
				}
			}

			update_option( 'wordpoints_recently_activated_modules', $recently_activated );
		}

		foreach ( (array) $modules['all'] as $module_file => $module_data ) {

			// Filter into individual sections.
			if ( is_multisite() && ! $screen->in_admin( 'network' ) && is_network_only_wordpoints_module( $module_file ) ) {

				unset( $modules['all'][ $module_file ] );

			} elseif ( ! $screen->in_admin( 'network' ) && is_wordpoints_module_active_for_network( $module_file ) ) {

				unset( $modules['all'][ $module_file ] );

			} elseif (
				(
					! $screen->in_admin( 'network' )
					&& is_wordpoints_module_active( $module_file )
				) || (
					$screen->in_admin( 'network' )
					&& is_wordpoints_module_active_for_network( $module_file )
				)
			) {

				$modules['active'][ $module_file ] = $module_data;

			} else {

				// Was the module recently activated?
				if ( ! $screen->in_admin( 'network' ) && isset( $recently_activated[ $module_file ] ) ) {
					$modules['recently_activated'][ $module_file ] = $module_data;
				}

				$modules['inactive'][ $module_file ] = $module_data;
			}
		}

		if ( $s ) {

			$status = 'search';
			$modules['search'] = array_filter( $modules['all'], array( $this, '_search_callback' ) );
		}

		/**
		 * Filter the modules diplayed in the modules list table.
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
		 * }
		 */
		$modules = apply_filters( 'wordpoints_modules_list_table_items', $modules );

		// Calculate the totals.
		$totals = array();

		foreach ( $modules as $type => $list ) {
			$totals[ $type ] = count( $list );
		}

		if ( empty( $modules[ $status ] ) && ! in_array( $status, array( 'all', 'search' ) ) ) {
			$status = 'all';
		}

		$this->items = array();

		foreach ( $modules[ $status ] as $module_file => $module_data ) {

			$this->items[ $module_file ] = wordpoints_get_module_data( wordpoints_modules_dir() . '/' . $module_file, false );
		}

		$total_this_page = $totals[ $status ];

		if ( $orderby ) {

			$orderby = ucfirst( $orderby );
			$order   = strtoupper( $order );

			uasort( $this->items, array( $this, '_order_callback' ) );
		}

		$modules_per_page = $this->get_items_per_page( str_replace( '-', '_', $screen->id . '_per_page' ), 999 );

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
	 * @return bool Wether any of the module's data matches the search.
	 */
	private function _search_callback( $module_data ) {

		static $term;

		if ( is_null( $term ) && isset( $_REQUEST['s'] ) ) {
			$term = wp_unslash( esc_html( $_REQUEST['s'] ) );
		}

		foreach ( $module_data as $value ) {

			if ( stripos( $value, $term ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Callback for sorting the modules.
	 *
	 * @link http://php.net/uasort uasort()
	 *
	 * @since 1.1.0
	 *
	 * @param array $module_a The data for a module.
	 * @param array $module_b The data for another module.
	 *
	 * @return int How the modules compare.
	 */
	private function _order_callback( $module_a, $module_b ) {

		global $orderby, $order;

		$a = $module_a[ $orderby ];
		$b = $module_b[ $orderby ];

		if ( $a == $b ) {
			return 0;
		}

		if ( 'DESC' == $order ) {
			return ( $a < $b ) ? 1 : -1;
		} else {
			return ( $a < $b ) ? -1 : 1;
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
			_e( 'No modules found.', 'wordpoints' );
		} else {
			printf( __( 'You do not appear to have any modules available at this time. <a href="%s">Install some</a>.', 'wordpoints' ), esc_url( self_admin_url( 'admin.php?page=wordpoints_install_modules' ) ) );
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
			'name'        => __( 'Module', 'wordpoints' ),
			'description' => __( 'Description', 'wordpoints' ),
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
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'modules', 'wordpoints' );
				break;

				case 'active':
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				case 'recently_activated':
					$text = _n( 'Recently Active <span class="count">(%s)</span>', 'Recently Active <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				case 'inactive':
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count, 'wordpoints' );
				break;

				default:
					$text = $type;
			}

			if ( 'search' != $type ) {

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
					, add_query_arg( 'module_status', $type, 'admin.php?page=wordpoints_modules' )
					, ( $type == $status ) ? ' class="current"' : ''
					, sprintf( $text, number_format_i18n( $count ) )
				);
			}
		}

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

		if ( 'active' != $status ) {
			$actions['activate-selected'] = ( $this->screen->in_admin( 'network' ) ) ? __( 'Network Activate', 'wordpoints' ) : __( 'Activate', 'wordpoints' );
		}

		if ( 'inactive' != $status && 'recent' != $status ) {
			$actions['deactivate-selected'] = ( $this->screen->in_admin( 'network' ) ) ? __( 'Network Deactivate', 'wordpoints' ) : __( 'Deactivate', 'wordpoints' );
		}

		if (
			( ! is_multisite() || $this->screen->in_admin( 'network' ) )
			&& current_user_can( 'delete_wordpoints_modules' )
			&& 'active' != $status
		) {
			$actions['delete-selected'] = __( 'Delete', 'wordpoints' );
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
	 * Display extra table navigation links.
	 *
	 * @since 1.1.0
	 *
	 * @param unknown $which Not used.
	 *
	 * @return void
	 */
	public function extra_tablenav( $which ) {

		global $status;

		if ( $status != 'recently_activated' ) {
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

		global $status, $page, $s, $totals;

		list( $module_file, $module_data ) = $item;
		$context = $status;
		$screen = $this->screen;

		// Preorder.
		$actions = array(
			'deactivate' => '',
			'activate'   => '',
			'delete'     => '',
		);

		if ( $screen->in_admin( 'network' ) ) {
			$is_active = is_wordpoints_module_active_for_network( $module_file );
		} else {
			$is_active = is_wordpoints_module_active( $module_file );
		}

		$url = 'admin.php?page=wordpoints_modules&module=' . $module_file . '&module_status=' . $context . '&paged=' . $page . '&s=' . $s;

		if ( $screen->in_admin( 'network' ) ) {

			if ( $is_active ) {

				if ( current_user_can( 'manage_network_wordpoints_modules' ) ) {
					$actions['deactivate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'deactivate', $url ), "deactivate-module_{$module_file}" ) . '">' . __( 'Network Deactivate', 'wordpoints' ) . '</a>';
				}

			} else {

				if ( current_user_can( 'manage_network_wordpoints_modules' ) ) {
					$actions['activate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'activate', $url ), "activate-module_{$module_file}" ) . '" class="edit">' . __( 'Network Activate', 'wordpoints' ) . '</a>';
				}

				if ( current_user_can( 'delete_wordpoints_modules' ) && ! is_wordpoints_module_active( $module_file ) ) {
					$actions['delete'] = '<a href="' . wp_nonce_url( 'admin.php?page=wordpoints_modules&action=delete-selected&amp;checked[]=' . $module_file . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'bulk-modules' ) . '" class="delete">' . __( 'Delete', 'wordpoints' ) . '</a>';
				}
			}

		} else {

			if ( $is_active ) {

				$actions['deactivate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'deactivate', $url ), "deactivate-module_{$module_file}" ) . '">' . __( 'Deactivate', 'wordpoints' ) . '</a>';

			} else {

				$actions['activate'] = '<a href="' . wp_nonce_url( add_query_arg( 'action', 'activate', $url ), "activate-module_{$module_file}" ) . '" class="edit">' . __( 'Activate', 'wordpoints' ) . '</a>';

				if ( ! is_multisite() && current_user_can( 'delete_wordpoints_modules' ) ) {
					$actions['delete'] = '<a href="' . wp_nonce_url( 'admin.php?page=wordpoints_modules&action=delete-selected&amp;checked[]=' . $module_file . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'bulk-modules' ) . '" class="delete">' . __( 'Delete', 'wordpoints' ) . '</a>';
				}
			}
		}

		$prefix = $screen->in_admin( 'network' ) ? 'network_admin_' : '';

		/**
		 * Filter the module action links.
		 *
		 * Each action link is an HTML anchor attribute. The avaiability of each link
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
		$actions = apply_filters( $prefix . "wordpoints_module_action_links_{$module_file}", $actions, $module_file, $module_data, $context );

		$class = ( $is_active ) ? 'active' : 'inactive';

		$checkbox_id = 'checkbox_' . md5( $module_data['name'] );

		$checkbox = "<label class='screen-reader-text' for='" . $checkbox_id . "' >" . sprintf( __( 'Select %s', 'wordpoints' ), $module_data['name'] ) . '</label>'
			. "<input type='checkbox' name='checked[]' value='" . esc_attr( $module_file ) . "' id='" . $checkbox_id . "' />";

		$description = '<p>' . ( $module_data['description'] ? $module_data['description'] : '&nbsp;' ) . '</p>';

		$module_name = $module_data['name'];

		$id = sanitize_title( $module_name );

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

		echo "<tr id='{$id}' class='{$class}'>";

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {

			$style = '';

			if ( in_array( $column_name, $hidden ) ) {
				$style = ' style="display:none;"';
			}

			switch ( $column_name ) {

				case 'cb':
					echo "<th scope='row' class='check-column'>{$checkbox}</th>";
				break;

				case 'name':
					echo "<td class='module-title'{$style}><strong>{$module_name}</strong>";
					echo $this->row_actions( $actions, true );
					echo '</td>';
				break;

				case 'description':
					echo "<td class='column-description desc'{$style}>
						<div class='module-description'>{$description}</div>
						<div class='{$class} second module-version-author-uri'>";

					$module_meta = array();

					if ( ! empty( $module_data['version'] ) ) {
						$module_meta[] = sprintf( __( 'Version %s', 'wordpoints' ), $module_data['version'] );
					}

					if ( ! empty( $module_data['author'] ) ) {

						$author = $module_data['author'];

						if ( ! empty( $module_data['author_uri'] ) ) {
							$author = '<a href="' . $module_data['author_uri'] . '" title="' . esc_attr__( 'Visit author homepage', 'wordpoints' ) . '">' . $module_data['author'] . '</a>';
						}

						$module_meta[] = sprintf( __( 'By %s', 'wordpoints' ), $author );
					}

					if ( ! empty( $module_data['module_uri'] ) ) {
						$module_meta[] = '<a href="' . $module_data['module_uri'] . '">' . __( 'Visit module site', 'wordpoints' ) . '</a>';
					}

					/**
					 * Filter meta data and links for the module row in the module list table.
					 *
					 * These include the module version, and links to the module
					 * URI and author URI if provided.
					 * @since 1.1.0
					 *
					 * @param array  $module_meta The meta links for the module.
					 * @param string $module_file The main file of the module.
					 * @param array  $module_data The info about the module.
					 * @param string $status      The module stati being displayed.
					 */
					$module_meta = apply_filters( 'wordpoints_module_row_meta', $module_meta, $module_file, $module_data, $status );

					echo implode( ' | ', $module_meta );

					echo '</div></td>';
				break;

				default:
					echo "<td class='$column_name column-$column_name'$style>";
					/**
					 * Display the row contents for a custom column in the module list table.
					 *
					 * @since 1.1.0
					 *
					 * @param string $column_name The name of the column being displayed.
					 * @param string $module_file The main file of the current module.
					 * @param array  $module_data The module's info.
					 */
					do_action( 'wordpoints_manage_modules_custom_column', $column_name, $module_file, $module_data );
					echo '</td>';

			} // switch ( $column_name )

		} // foreach ( $columns )

		echo '</tr>';

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

	} // function single_row()

} // class WordPoints_Modules_List_Table
