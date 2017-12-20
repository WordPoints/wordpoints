<?php

/**
 * User Points List Table class.
 *
 * @package WordPoints
 * @since 2.5.0
 */

// Include the users list table dependency.
require_once ABSPATH . '/wp-admin/includes/class-wp-users-list-table.php';

/**
 * Displays a table of users and their points.
 *
 * @since 2.5.0
 */
class WordPoints_Points_Admin_List_Table_User_Points extends WP_Users_List_Table {

	/**
	 * The current points type being displayed.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $points_type;

	/**
	 * @since 2.5.0
	 */
	public function __construct( $args = array() ) {

		if (
			! isset( $args['points_type'] )
			|| ! wordpoints_is_points_type( $args['points_type'] )
		) {
			$args['points_type'] = wordpoints_get_default_points_type();
		}

		$this->points_type = $args['points_type'];

		WP_List_Table::__construct(
			array(
				'singular' => 'wordpoints-user-points',
				'plural'   => 'wordpoints-users-points',
				'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
			)
		);
	}

	/**
	 * @since 2.5.0
	 */
	public function ajax_user_can() {

		if ( ! current_user_can( 'set_wordpoints_points' ) ) {
			return false;
		}

		return parent::ajax_user_can();
	}

	/**
	 * @since 2.5.0
	 */
	public function prepare_items() {

		global $usersearch;

		$usersearch = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // WPCS: CSRF OK.

		$users_per_page = $this->get_items_per_page(
			str_replace( '-', '_', $this->screen->id . '_per_page' )
		);

		$paged = $this->get_pagenum();

		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged - 1 ) * $users_per_page,
			'search' => $usersearch,
			'fields' => 'all_with_meta',
		);

		if ( '' !== $args['search'] ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( isset( $_REQUEST['order'] ) ) { // WPCS: CSRF OK.
			$args['order'] = sanitize_key( $_REQUEST['order'] );
		}

		if ( isset( $_REQUEST['orderby'] ) ) { // WPCS: CSRF OK.

			$args['orderby'] = sanitize_key( $_REQUEST['orderby'] );

			if ( 'meta_value_num' === $args['orderby'] ) {

				$order = isset( $args['order'] ) ? strtoupper( $args['order'] ) : 'DESC';

				$args['orderby'] = array(
					'meta_value_num' => $order,
					'user_login'     => 'DESC' === $order ? 'ASC' : 'DESC',
				);

				$meta_key = wordpoints_get_points_user_meta_key( $this->points_type );

				$args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key'     => $meta_key,
						'compare' => 'EXISTS',
					),
					array(
						'key'     => $meta_key,
						'compare' => 'NOT EXISTS',
					),
				);
			}
		}

		/**
		 * Filters the query args used to retrieve users for the users points list table.
		 *
		 * @since 2.5.0
		 *
		 * @param array $args Arguments passed to WP_User_Query to retrieve items for the
		 *                    users points list table.
		 */
		$args = apply_filters( 'wordpoints_users_points_list_table_query_args', $args );

		$query = new WP_User_Query( $args );

		$this->items = $query->get_results();

		$this->set_pagination_args(
			array(
				'total_items' => $query->get_total(),
				'per_page'    => $users_per_page,
			)
		);
	}

	/**
	 * @since 2.5.0
	 */
	public function get_views() {
		return array();
	}

	/**
	 * @since 2.5.0
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * @since 2.5.0
	 */
	public function extra_tablenav( $which ) {}

	/**
	 * @since 2.5.0
	 */
	public function get_columns() {

		return array(
			'username' => _x( 'Username', 'user points table heading', 'wordpoints' ),
			'name'     => _x( 'Name', 'user points table heading', 'wordpoints' ),
			'points'   => _x( 'Points', 'user points table heading', 'wordpoints' ),
			'action'   => _x( 'Action', 'user points table heading', 'wordpoints' ),
		);
	}

	/**
	 * @since 2.5.0
	 */
	public function get_sortable_columns() {

		return array(
			'username' => 'login',
			'name'     => 'name',
			'points'   => 'meta_value_num',
		);
	}

	/**
	 * @since 2.5.0
	 */
	public function display_rows() {

		foreach ( $this->items as $user_object ) {

			if ( is_multisite() && empty( $user_object->allcaps ) ) {
				continue;
			}

			echo "\n\t" . $this->single_row( $user_object ); // WPCS: XSS OK.
		}
	}

	/**
	 * @since 2.5.0
	 */
	public function single_row( $user, $style = '', $role = '', $numposts = 0 ) {

		if ( ! ( $user instanceof WP_User ) ) {
			$user = get_userdata( (int) $user );
		}

		$user->filter = 'display';

		$avatar = get_avatar( $user->ID, 32 );

		$row = '<tr id="user-' . (int) $user->ID . '" data-wordpoints-points-user-id="' . (int) $user->ID . '">';

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {

			$classes = "{$column_name} column-{$column_name}";

			if ( $primary === $column_name ) {
				$classes .= ' column-primary';
			}

			if ( in_array( $column_name, $hidden, true ) ) {
				$classes .= ' hidden';
			}

			$row .= '<td class="' . esc_attr( $classes ) . '" data-colname="' . esc_attr( wp_strip_all_tags( $column_display_name ) ) . '">';

			switch ( $column_name ) {

				case 'username':
					$row .= $avatar . ' <strong>' . esc_html( $user->user_login ) . '</strong>';
				break;

				case 'name':
					if ( $user->first_name || $user->last_name ) {
						$row .= esc_html( "{$user->first_name} {$user->last_name}" );
					} else {
						$row .= '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . _x( 'Unknown', 'name', 'wordpoints' ) . '</span>';
					}
				break;

				case 'points':
					$row .= (int) wordpoints_get_points( $user->ID, $this->points_type );
				break;

				case 'action':
					$row .= '<label for="wordpoints_update_user_points-' . (int) $user->ID . '" class="screen-reader-text">' . esc_html__( 'Points to add or subtract:', 'wordpoints' ) . '</label>'
						. '<input type="number" name="wordpoints_update_user_points-' . (int) $user->ID . '" id="wordpoints_update_user_points-' . (int) $user->ID . '" />'
						. get_submit_button( esc_html__( 'Add', 'wordpoints' ), 'wordpoints-add-points', 'wordpoints_add_user_points-' . $user->ID, false )
						. get_submit_button( esc_html__( 'Subtract', 'wordpoints' ), 'wordpoints-subtract-points', 'wordpoints_subtract_user_points-' . $user->ID, false );
				break;

				default:
					/**
					 * Filters the HTML for a custom column in the user points table.
					 *
					 * @since 2.5.0
					 *
					 * @param string $column      The column HTML.
					 * @param string $column_name The name of the column.
					 * @param int    $user_id     The ID of the user the row is for.
					 */
					$row .= apply_filters( 'wordpoints_manage_user_points_custom_column', '', $column_name, $user->ID );
			}

			$row .= '</td>';
		}

		$row .= '</tr>';

		return $row;
	}
}

// EOF
