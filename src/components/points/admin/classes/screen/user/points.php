<?php

/**
 * The user points admin screen class.
 *
 * @package WordPoints\Points\Administration
 * @since 2.5.0
 */

/**
 * Displays the User Points administration screen.
 *
 * @since 2.5.0
 */
class WordPoints_Points_Admin_Screen_User_Points extends WordPoints_Admin_Screen {

	/**
	 * The slug of the points type currently being viewed.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $current_points_type;

	/**
	 * @since 2.5.0
	 */
	protected function get_title() {
		return _x( 'User Points', 'page title', 'wordpoints' );
	}

	/**
	 * @since 2.5.0
	 */
	public function footer_scripts() {

		wp_enqueue_style( 'wordpoints-admin-user-points' );

		wp_enqueue_script( 'wordpoints-admin-user-points' );

		if ( $this->current_points_type ) {

			wp_localize_script(
				'wordpoints-admin-user-points'
				, 'WordPointsUserPointsTableData'
				, array(
					'pointsType'    => $this->current_points_type,
					'pointsMinimum' => wordpoints_get_points_minimum(
						$this->current_points_type
					),
					'nonce'         => wp_create_nonce(
						'wordpoints-points-alter-user-points'
					),
				)
			);
		}
	}

	/**
	 * @since 2.5.0
	 */
	public function load() {

		add_screen_option( 'per_page' );

		$current_screen = get_current_screen();

		register_column_headers(
			$current_screen
			, array(
				'username' => _x( 'Username', 'user points table heading', 'wordpoints' ),
				'name'     => _x( 'Name', 'user points table heading', 'wordpoints' ),
				'points'   => _x( 'Points', 'user points table heading', 'wordpoints' ),
				'action'   => _x( 'Action', 'user points table heading', 'wordpoints' ),
			)
		);

		$current_screen->set_screen_reader_content(
			array(
				'heading_views'      => __( 'Filter users list', 'wordpoints' ),
				'heading_pagination' => __( 'Users list navigation', 'wordpoints' ),
				'heading_list'       => __( 'Users list', 'wordpoints' ),
			)
		);

		$this->tabs = wp_list_pluck( wordpoints_get_points_types(), 'name' );

		$this->current_points_type = wordpoints_admin_get_current_tab( $this->tabs );
	}

	/**
	 * @since 2.5.0
	 */
	public function display_content() {

		global $usersearch;

		/**
		 * Before user points on admin screen.
		 *
		 * @since 2.5.0
		 */
		do_action( 'wordpoints_admin_user_points' );

		if ( empty( $this->current_points_type ) ) {

			wordpoints_show_admin_error(
				sprintf(
					// translators: URL of Points Types admin screen.
					__( 'You need to <a href="%s">create a type of points</a> before you can use this page.', 'wordpoints' )
					, esc_url( self_admin_url( 'admin.php?page=wordpoints_points_types' ) )
				)
			);

		} else {

			$wp_list_table = new WordPoints_Points_Admin_List_Table_User_Points(
				array( 'points_type' => $this->current_points_type )
			);

			$wp_list_table->prepare_items();

			/**
			 * At the top of one of the tabs on the user points admin screen.
			 *
			 * @since 2.5.0
			 *
			 * @param string $points_type The points type the current tab is for.
			 */
			do_action( 'wordpoints_admin_user_points_tab', $this->current_points_type );

			?>

			<?php if ( strlen( $usersearch ) ) : ?>
				<p class="wordpoints-searching-for">
					<span class="dashicons dashicons-search"></span>
					<?php

					// translators: Search keywords.
					echo esc_html( sprintf( __( 'Search results for &#8220;%s&#8221;', 'wordpoints' ), $usersearch ) );

					?>
				</p>
			<?php endif; ?>

			<?php $wp_list_table->views(); ?>

			<form method="get">
				<?php $wp_list_table->search_box( __( 'Search Users', 'wordpoints' ), 'user' ); ?>
				<input type="hidden" name="page" value="wordpoints_user_points" />
				<input type="hidden" name="tab" value="<?php echo esc_attr( $this->current_points_type ); ?>" />
			</form>

			<?php

			$wp_list_table->display();

			/**
			 * At the bottom of one of the tabs on the user points admin screen.
			 *
			 * @since 2.5.0
			 *
			 * @param string $points_type The points type the current tab is for.
			 */
			do_action( 'wordpoints_admin_user_points_tab_after', $this->current_points_type );
		}

		/**
		 * After user points on admin screen.
		 *
		 * @since 2.5.0
		 */
		do_action( 'wordpoints_admin_user_points_after' );
	}
}

// EOF
