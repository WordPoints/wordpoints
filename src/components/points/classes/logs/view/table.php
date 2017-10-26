<?php

/**
 * Table points logs view class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Displays points logs in a table.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Logs_View_Table extends WordPoints_Points_Logs_View {

	/**
	 * The headings for the columns being displayed.
	 *
	 * @since 2.2.0
	 *
	 * @var string[]
	 */
	protected $column_headings;

	/**
	 * The unix timestamp for the current time.
	 *
	 * Stored here so that we can calculate it just once, before the logs are
	 * displayed, instead of for each log.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	protected $current_time;

	/**
	 * The size of the user avatars used in the table, in pixels.
	 *
	 * @since 2.4.0
	 *
	 * @var int
	 */
	protected $avatar_size = 32;

	/**
	 * @since 2.2.0
	 */
	protected function before() {

		wp_enqueue_style( 'wordpoints-points-logs' );

		/**
		 * Filters the size of the user avatars displayed in the points logs table.
		 *
		 * @since 2.4.0
		 *
		 * @param int $avatar_size The size of the avatars, in pixels.
		 */
		$this->avatar_size = apply_filters( 'wordpoints_points_logs_table_avatar_size', $this->avatar_size );

		$this->current_time = current_time( 'timestamp', true );

		$this->column_headings = array(
			'user'        => _x( 'User', 'points logs table heading', 'wordpoints' ),
			'points'      => _x( 'Points', 'points logs table heading', 'wordpoints' ),
			'description' => _x( 'Description', 'points logs table heading', 'wordpoints' ),
			'time'        => _x( 'Time', 'points logs table heading', 'wordpoints' ),
		);

		$points_type = $this->logs_query->get_arg( 'points_type' );

		if ( ! empty( $points_type ) ) {

			$points_type_name = wordpoints_get_points_type_setting(
				$points_type
				, 'name'
			);

			if ( ! empty( $points_type_name ) ) {
				$this->column_headings['points'] = $points_type_name;
			}
		}

		$extra_classes = array();

		foreach ( array( 'searchable', 'paginate' ) as $arg ) {
			if ( $this->args[ $arg ] ) {
				$extra_classes[] = $arg;
			}
		}

		/**
		 * Filter the extra HTML classes to give to the points logs table element.
		 *
		 * @since 1.6.0
		 *
		 * @param string[] $extra_classes Classes to add to the table beyond the defaults.
		 * @param array    $args          The arguments for displaying the table.
		 * @param object[] $logs          The logs being displayed.
		 */
		$extra_classes = apply_filters(
			'wordpoints_points_logs_table_extra_classes'
			, $extra_classes
			, $this->args
			, $this->logs
		);

		?>

		<div class="wordpoints-points-logs-wrapper">
			<?php if ( $this->args['searchable'] ) : ?>
				<?php $this->search_box(); ?>
			<?php endif; ?>

			<table class="wordpoints-points-logs widefat <?php echo esc_attr( implode( ' ', $extra_classes ) ); ?>">
				<thead>
					<tr>
						<?php if ( $this->args['show_users'] ) : ?>
							<th scope="col"><?php echo esc_html( $this->column_headings['user'] ); ?></th>
						<?php endif; ?>
						<th scope="col"><?php echo esc_html( $this->column_headings['points'] ); ?></th>
						<th scope="col"><?php echo esc_html( $this->column_headings['description'] ); ?></th>
						<th scope="col"><?php echo esc_html( $this->column_headings['time'] ); ?></th>
					</tr>
				</thead>
				<tbody>
		<?php
	}

	/**
	 * @since 2.2.0
	 */
	protected function no_logs() {

		?>
		<tr>
			<td colspan="<?php echo ( $this->args['show_users'] ) ? 4 : 3; ?>">
				<?php esc_html_e( 'No matching logs found.', 'wordpoints' ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @since 2.2.0
	 */
	protected function log( $log ) {

		$user = get_userdata( $log->user_id );

		?>

		<tr class="wordpoints-log-id-<?php echo (int) $log->id; ?> <?php echo ( $this->i % 2 ) ? 'odd' : 'even'; ?>">
			<?php if ( $this->args['show_users'] ) : ?>
				<td>
					<?php echo get_avatar( $user->ID, $this->avatar_size ); ?>
					<span class="wordpoints-points-log-user-name">
					<?php

					/**
					 * Filters the username displayed in the top users table.
					 *
					 * Note that whatever you return from this filter will be passed
					 * through wp_kses() before display, to avoid XSS.
					 *
					 * @since 2.2.0
					 *
					 * @param string  $name The name to display for the user.
					 *                      Defaults to the user's Display Name.
					 * @param WP_User $user The user object.
					 * @param object  $log  The object for the log being displayed.
					 */
					$name = apply_filters(
						'wordpoints_points_logs_table_username'
						, sanitize_user_field( 'display_name', $user->display_name, $log->user_id, 'display' )
						, $user
						, $log
					);

					echo wp_kses( $name, 'wordpoints_points_logs_username' );

					?>
					</span>
				</td>
			<?php endif; ?>
			<td><?php echo wordpoints_format_points( $log->points, $log->points_type, 'logs' ); ?></td>
			<td>
				<div class="wordpoints-log-text">
					<?php echo wp_kses( $log->text, 'wordpoints_points_log' ); ?>
				</div>
				<?php if ( $this->restriction->applies() ) : ?>
					<div class="wordpoints-log-viewing-restrictions">
						<?php foreach ( (array) $this->restriction->get_description() as $description ) : ?>
							<div class="wordpoints-log-viewing-restriction">
								<?php echo esc_html( $description ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</td>
			<td title="<?php echo esc_attr( $log->date ); ?> <?php /* translators: coordinated universal time */ esc_attr_e( 'UTC', 'wordpoints' ); ?>">
				<?php echo esc_html( human_time_diff( strtotime( $log->date ), $this->current_time ) ); ?>
			</td>
		</tr>

		<?php
	}

	/**
	 * @since 2.2.0
	 */
	protected function after() {

		?>
				</tbody>
				<tfoot>
					<tr>
						<?php if ( $this->args['show_users'] ) : ?>
							<th scope="col"><?php echo esc_html( $this->column_headings['user'] ); ?></th>
						<?php endif; ?>
						<th scope="col"><?php echo esc_html( $this->column_headings['points'] ); ?></th>
						<th scope="col"><?php echo esc_html( $this->column_headings['description'] ); ?></th>
						<th scope="col"><?php echo esc_html( $this->column_headings['time'] ); ?></th>
					</tr>
				</tfoot>
			</table>

			<?php if ( $this->args['paginate'] ) : ?>
				<?php $this->pagination(); ?>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_search_term() {

		$search_term = '';

		if ( isset( $_POST['wordpoints_points_logs_search'] ) ) { // WPCS: CSRF OK
			$search_term = trim(
				sanitize_text_field(
					wp_unslash( $_POST['wordpoints_points_logs_search'] ) // WPCS: CSRF OK
				)
			);

			return $search_term;
		}

		return $search_term;
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_page_number() {

		$page = 1;

		if (
			isset( $_GET['wordpoints_points_logs_page'] ) // WPCS: CSRF OK.
			&& wordpoints_posint( $_GET['wordpoints_points_logs_page'] ) // WPCS: CSRF OK.
		) {
			$page = (int) $_GET['wordpoints_points_logs_page'];
		}

		return $page;
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_per_page() {

		$per_page = 25;

		if (
			isset( $_GET['wordpoints_points_logs_per_page'] ) // WPCS: CSRF OK.
			&& wordpoints_posint( $_GET['wordpoints_points_logs_per_page'] ) // WPCS: CSRF OK.
		) {
			$per_page = (int) $_GET['wordpoints_points_logs_per_page'];
		}

		return $per_page;
	}

	/**
	 * @since 2.2.0
	 */
	protected function search_box() {

		$search_term = $this->get_search_term();

		?>

		<?php if ( ! empty( $search_term ) ) : ?>
			<div class="wordpoints-points-logs-searching">
				<?php

				echo esc_html(
					sprintf(
						// translators: Search term.
						__( 'Searching for &#8220;%s&#8221;', 'wordpoints' )
						, $search_term
					)
				);

				?>
			</div>
		<?php endif; ?>

		<div class="wordpoints-points-logs-search">
			<form method="POST" action="<?php echo esc_url( remove_query_arg( 'wordpoints_points_logs_page' ) ); ?>">
				<label class="screen-reader-text" for="wordpoints_points_logs_search">
					<?php esc_html_e( 'Search Logs:', 'wordpoints' ); ?>
				</label>
				<input
					type="text"
					name="wordpoints_points_logs_search"
					id="wordpoints_points_logs_search"
					value="<?php echo esc_attr( $search_term ); ?>"
				/>
				<input
					name=""
					class="button"
					value="<?php esc_attr_e( 'Search Logs', 'wordpoints' ); ?>"
					type="submit"
				/>
			</form>
		</div>

		<?php
	}

	/**
	 * @since 2.2.0
	 */
	protected function pagination() {

		$per_page = $this->get_per_page();

		echo paginate_links( // XSS pass WPCS.
			array(
				'base'     => add_query_arg( '%_%', '' ),
				'format'   => 'wordpoints_points_logs_page=%#%',
				'total'    => ceil( $this->logs_query->count() / $per_page ),
				'current'  => $this->get_page_number(),
				'add_args' => array(
					'wordpoints_points_logs_per_page' => $per_page,
				),
			)
		);
	}
}

// EOF
