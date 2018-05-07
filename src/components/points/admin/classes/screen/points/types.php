<?php

/**
 * The points types admin screen class.
 *
 * @package WordPoints\Points\Administration
 * @since 2.1.0
 */

/**
 * Displays the Points Types administration screen.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Admin_Screen_Points_Types extends WordPoints_Admin_Screen {

	/**
	 * The slug of the points type currently being viewed/edited.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $current_points_type;

	/**
	 * The hooks app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hooks
	 */
	protected $hooks;

	/**
	 * The entities app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_App_Registry
	 */
	protected $entities;

	/**
	 * The points hook reaction store.
	 *
	 * Set by {@see self::add_event_meta_boxes()}.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_StoreI
	 */
	protected $reaction_store;

	/**
	 * The event args registry.
	 *
	 * Set by {@see self::add_event_meta_boxes()}.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Class_Registry_ChildrenI
	 */
	protected $event_args;

	/**
	 * Whether there were any reactions for this points type.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $had_reactions = false;

	/**
	 * @since 2.1.0
	 */
	public function __construct() {

		parent::__construct();

		$this->hooks    = wordpoints_hooks();
		$this->entities = wordpoints_entities();
	}

	/**
	 * @since 2.1.0
	 */
	protected function get_title() {
		return _x( 'Points Types', 'page title', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function hooks() {

		parent::hooks();

		add_action( 'add_meta_boxes', array( $this, 'add_points_settings_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_points_logs_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_shortcodes_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_event_meta_boxes' ) );

		add_action( 'wordpoints_admin_points_events_head', array( $this, 'create_demo_reactions' ) );
		add_action( 'wordpoints_admin_points_events_foot', array( $this, 'show_demo_reactions_message' ) );
	}

	/**
	 * @since 2.1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_style( 'wordpoints-hooks-admin' );

		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'wordpoints-admin-points-types' );

		wordpoints_hooks_ui_setup_script_data();
	}

	/**
	 * @since 2.1.0
	 */
	public function footer_scripts() {

		?>

		<script type="text/javascript">
			jQuery( document ).ready( function ( $ ) {

				$( '.if-js-closed' )
					.removeClass( 'if-js-closed' )
					.addClass( 'closed' );

				postboxes.add_postbox_toggles(
					<?php echo wp_json_encode( $this->id ); ?>
				);
			} );
		</script>

		<?php
	}

	/**
	 * Add a meta-box for the settings of the current points type.
	 *
	 * @since 2.1.0
	 */
	public function add_points_settings_meta_box() {

		if ( ! current_user_can( 'manage_wordpoints_points_types' ) ) {
			return;
		}

		add_meta_box(
			'settings'
			, __( 'Settings', 'wordpoints' )
			, array( $this, 'display_points_settings_meta_box' )
			, $this->id
			, 'side'
			, 'default'
		);
	}

	/**
	 * Add a meta-box for the logs of the current points type.
	 *
	 * @since 2.2.0
	 */
	public function add_points_logs_meta_box() {

		if ( ! $this->current_points_type ) {
			return;
		}

		add_meta_box(
			'logs'
			, __( 'Logs', 'wordpoints' )
			, array( $this, 'display_points_logs_meta_box' )
			, $this->id
			, 'side'
			, 'default'
		);
	}

	/**
	 * Add a meta-box for example shortcodes for the current points type.
	 *
	 * @since 2.3.0
	 */
	public function add_shortcodes_meta_box() {

		if ( ! $this->current_points_type ) {
			return;
		}

		add_meta_box(
			'shortcodes'
			, __( 'Shortcodes', 'wordpoints' )
			, array( $this, 'display_shortcodes_meta_box' )
			, $this->id
			, 'side'
			, 'low'
		);
	}

	/**
	 * Display the contents of the meta-box for the points settings.
	 *
	 * @since 2.1.0
	 */
	public function display_points_settings_meta_box() {

		if ( ! current_user_can( 'manage_wordpoints_points_types' ) ) {
			return;
		}

		$slug = $this->current_points_type;

		$add_new = 0;

		$points_type = wordpoints_get_points_type( $slug );

		if ( ! $points_type ) {

			$points_type = array();
			$add_new     = wp_create_nonce( 'wordpoints_add_new_points_type' );
		}

		$points_type = array_merge(
			array(
				'name'   => '',
				'prefix' => '',
				'suffix' => '',
			)
			, $points_type
		);

		?>

		<form method="post">
			<?php if ( is_wordpoints_network_active() && ! is_network_admin() ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php if ( $slug ) : ?>
							<?php esc_html_e( 'Changes to this points type&#8217;s settings will affect all sites on this network.', 'wordpoints' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'The new points type will be global across all sites on this network.', 'wordpoints' ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $slug ) : ?>
				<p>
					<span class="wordpoints-points-slug">
						<em>
							<?php

							// translators: Points type slug.
							echo esc_html( sprintf( __( 'Slug: %s', 'wordpoints' ), $slug ) );

							?>
						</em>
					</span>
				</p>
				<?php wp_nonce_field( "wordpoints_update_points_type-$slug", 'update_points_type' ); ?>
			<?php endif; ?>

			<?php

			/**
			 * At the top of the points type settings form.
			 *
			 * Called before the default inputs are displayed.
			 *
			 * @since 2.1.0
			 *
			 * @param string $points_type The slug of the points type.
			 */
			do_action( 'wordpoints_points_type_form_top', $slug );

			if ( $add_new ) {

				// Mark the prefix and suffix optional on the add new form.
				$prefix = _x( 'Prefix (optional):', 'points type', 'wordpoints' );
				$suffix = _x( 'Suffix (optional):', 'points type', 'wordpoints' );

			} else {

				$prefix = _x( 'Prefix:', 'points type', 'wordpoints' );
				$suffix = _x( 'Suffix:', 'points type', 'wordpoints' );
			}

			?>

			<p>
				<label for="points-name-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html_x( 'Name:', 'points type', 'wordpoints' ); ?></label>
				<input
					class="widefat"
					type="text"
					id="points-name-<?php echo esc_attr( $slug ); ?>"
					name="points-name"
					value="<?php echo esc_attr( $points_type['name'] ); ?>"
					<?php if ( ! $slug ) : ?>
					autofocus
					<?php endif; ?>
				/>
			</p>
			<p>
				<label for="points-prefix-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $prefix ); ?></label>
				<input
					class="widefat"
					type="text"
					id="points-prefix-<?php echo esc_attr( $slug ); ?>"
					name="points-prefix"
					value="<?php echo esc_attr( $points_type['prefix'] ); ?>"
				/>
			</p>
			<p>
				<label for="points-suffix-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $suffix ); ?></label>
				<input
					class="widefat"
					type="text"
					id="points-suffix-<?php echo esc_attr( $slug ); ?>"
					name="points-suffix"
					value="<?php echo esc_attr( $points_type['suffix'] ); ?>"
				/>
			</p>

			<?php

			/**
			 * At the bottom of the points type settings form.
			 *
			 * Called below the default inputs, but above the submit buttons.
			 *
			 * @since 2.1.0
			 *
			 * @param string $points_type The slug of the points type.
			 */
			do_action( 'wordpoints_points_type_form_bottom', $slug );

			?>

			<input type="hidden" name="points-slug" value="<?php echo esc_attr( $slug ); ?>" />
			<input type="hidden" name="add_new" class="add_new" value="<?php echo esc_attr( $add_new ); ?>" />

			<div class="hook-control-actions">
				<div class="alignleft">
					<?php

					if ( ! $add_new ) {
						wp_nonce_field( "wordpoints_delete_points_type-{$slug}", 'delete-points-type-nonce' );
						submit_button( _x( 'Delete', 'points type', 'wordpoints' ), 'delete', 'delete-points-type', false, array( 'id' => "delete_points_type-{$slug}" ) );
					}

					?>
				</div>
				<div class="alignright">
					<?php submit_button( _x( 'Save', 'points type', 'wordpoints' ), 'button-primary hook-control-save right', 'save-points-type', false, array( 'id' => "points-{$slug}-save" ) ); ?>
					<span class="spinner"></span>
				</div>
				<br class="clear"/>
			</div>
		</form>

		<?php
	}

	/**
	 * Display the contents of the meta-box for the points logs.
	 *
	 * @since 2.2.0
	 */
	public function display_points_logs_meta_box() {

		?>

		<a href="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_points_logs&tab=' . $this->current_points_type ) ); ?>">
			<?php esc_html_e( 'Go to the logs for this points type.', 'wordpoints' ); ?>
		</a>

		<?php
	}

	/**
	 * Display the contents of the meta-box for the shortcode tips.
	 *
	 * @since 2.3.0
	 */
	public function display_shortcodes_meta_box() {

		?>

		<p>
			<label><?php esc_html_e( 'Display the user&#8217;s points:', 'wordpoints' ); ?>
				<input
					type="text"
					class="widefat"
					onfocus="this.select()"
					readonly
					value="[wordpoints_points points_type=&quot;<?php echo esc_html( $this->current_points_type ); ?>&quot;]"
				/>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Display a table of the top 5 users:', 'wordpoints' ); ?>
				<input
					type="text"
					class="widefat"
					onfocus="this.select()"
					readonly
					value="[wordpoints_points_top users=&quot;5&quot; points_type=&quot;<?php echo esc_html( $this->current_points_type ); ?>&quot;]"
				/>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Display the points logs:', 'wordpoints' ); ?>
				<input
					type="text"
					class="widefat"
					onfocus="this.select()"
					readonly
					value="[wordpoints_points_logs points_type=&quot;<?php echo esc_html( $this->current_points_type ); ?>&quot;]"
				/>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Display a list of ways to earn points:', 'wordpoints' ); ?>
				<input
					type="text"
					class="widefat"
					onfocus="this.select()"
					readonly
					value="[wordpoints_how_to_get_points points_type=&quot;<?php echo esc_html( $this->current_points_type ); ?>&quot;]"
				/>
			</label>
		</p>

		<p>
			<a href="https://wordpoints.org/user-guide/shortcodes/">
				<?php esc_html_e( 'Shortcode docs on WordPoints.org.', 'wordpoints' ); ?>
			</a>
		</p>

		<?php

		/**
		 * Fires at the bottom of the Shortcodes meta box on the Points Types screen.
		 *
		 * @since 2.3.0
		 *
		 * @param string $points_type The slug of the points type being displayed.
		 */
		do_action(
			'wordpoints_points_shortcodes_meta_box_bottom'
			, $this->current_points_type
		);
	}

	/**
	 * Add a meta-box for each hook event.
	 *
	 * @since 2.1.0
	 */
	public function add_event_meta_boxes() {

		if ( ! $this->current_points_type ) {
			return;
		}

		$events_app       = $this->hooks->get_sub_app( 'events' );
		$this->event_args = $events_app->get_sub_app( 'args' );

		$this->reaction_store = wordpoints_hooks()->get_reaction_store( 'points' );

		if ( ! $this->reaction_store ) {
			return;
		}

		/** @var WordPoints_Hook_ReactorI $reactor */
		$reactor              = $this->hooks->get_sub_app( 'reactors' )->get( 'points' );
		$reactor_action_types = array_fill_keys( $reactor->get_action_types(), true );

		$event_action_types = wordpoints_hooks_ui_get_script_data_event_action_types();

		/** @var WordPoints_Hook_EventI[] $events */
		$events = $events_app->get_all();

		/**
		 * Filter the events to show meta boxes for on the Points Types admin screen.
		 *
		 * Events which don't have any action types supported by this reactor will
		 * automatically be removed subsequently.
		 *
		 * @since 2.1.0
		 *
		 * @param WordPoints_Hook_EventI[] $events      The event objects.
		 * @param string                   $points_type Slug of the points type
		 *                                              being displayed.
		 */
		$events = apply_filters(
			'wordpoints_points_types_screen_events'
			, $events
			, $this->current_points_type
		);

		foreach ( $events as $slug => $event ) {

			if (
				! array_intersect_key(
					$event_action_types[ $slug ]
					, $reactor_action_types
				)
			) {
				continue;
			}

			add_meta_box(
				"{$this->current_points_type}-{$slug}"
				, $event->get_title()
				, array( $this, 'display_event_meta_box' )
				, $this->id
				, 'events'
				, 'default'
				, array(
					'points_type' => $this->current_points_type,
					'slug'        => $slug,
					'event'       => $event,
				)
			);
		}
	}

	/**
	 * Display the meta box for a hook event.
	 *
	 * @since 2.1.0
	 *
	 * @param array $points_type The points type this meta-box relates to.
	 * @param array $meta_box    The data the meta-box was created with.
	 */
	public function display_event_meta_box( $points_type, $meta_box ) {

		$event_slug = $meta_box['args']['slug'];

		$data = array();

		foreach ( $this->reaction_store->get_reactions_to_event( $event_slug ) as $id => $reaction ) {
			if ( $reaction->get_meta( 'points_type' ) === $this->current_points_type ) {
				$data[] = WordPoints_Admin_Ajax_Hooks::prepare_hook_reaction(
					$reaction
				);
			}
		}

		if ( ! empty( $data ) ) {
			$this->had_reactions = true;
		}

		$event_data = array( 'args' => array() );

		foreach ( $this->event_args->get_children( $event_slug ) as $slug => $arg ) {

			$event_data['args'][ $slug ] = array(
				'slug' => $slug,
			);

			if ( $arg instanceof WordPoints_Hook_ArgI ) {
				$event_data['args'][ $slug ]['title']       = $arg->get_title();
				$event_data['args'][ $slug ]['is_stateful'] = $arg->is_stateful();
			}
		}

		?>

		<script>
			WordPointsHooksAdminData.events[<?php echo wp_json_encode( $event_slug ); ?>] = <?php echo wp_json_encode( $event_data ); ?>;
			WordPointsHooksAdminData.reactions[<?php echo wp_json_encode( $event_slug ); ?>] = <?php echo wp_json_encode( $data ); ?>;
		</script>

		<div class="wordpoints-hook-reaction-group-container">
			<p class="description wordpoints-hook-reaction-group-description">
				<?php echo esc_html( $meta_box['args']['event']->get_description() ); ?>
			</p>

			<div class="wordpoints-hook-reaction-group"
				data-wordpoints-hooks-hook-event="<?php echo esc_attr( $event_slug ); ?>"
				data-wordpoints-hooks-points-type="<?php echo esc_attr( $this->current_points_type ); ?>"
				data-wordpoints-hooks-create-nonce="<?php echo esc_attr( WordPoints_Admin_Ajax_Hooks::get_create_nonce( $this->reaction_store ) ); ?>"
				data-wordpoints-hooks-reaction-store="points"
				data-wordpoints-hooks-reactor="points">
			</div>

			<div class="spinner-overlay" style="display: block;">
				<span class="spinner is-active"></span>
			</div>

			<div class="error hidden">
				<p></p>
			</div>

			<div class="controls">
				<button type="button" class="button-primary add-reaction">
					<?php esc_html_e( 'Add New Reaction', 'wordpoints' ); ?>
				</button>
			</div>
		</div>

		<?php
	}

	/**
	 * @since 2.1.0
	 */
	public function load() {

		$this->save_points_type();

		$points_types = wordpoints_get_points_types();

		// Show a tab for each points type.
		$tabs = array();

		foreach ( $points_types as $slug => $settings ) {
			$tabs[ $slug ] = $settings['name'];
		}

		$tabs['add-new'] = __( 'Add New', 'wordpoints' );

		$tab = wordpoints_admin_get_current_tab( $tabs );

		if ( 'add-new' !== $tab ) {
			$this->current_points_type = $tab;
		}

		do_action( 'add_meta_boxes', $this->id, $this->current_points_type );

		$this->tabs = $tabs;
	}

	/**
	 * Add, update, or delete a points type based on submitted data.
	 *
	 * @since 2.1.0
	 */
	public function save_points_type() {

		if ( ! current_user_can( 'manage_wordpoints_points_types' ) ) {
			return;
		}

		if ( isset( $_POST['save-points-type'] ) ) { // WPCS: CSRF OK

			if ( ! empty( $_POST['add_new'] ) ) { // WPCS: CSRF OK
				$this->add_points_type();
			} else {
				$this->update_points_type();
			}

		} elseif ( ! empty( $_POST['delete-points-type'] ) ) { // WPCS: CSRF OK

			$this->delete_points_type();
		}
	}

	/**
	 * Get the settings for a points type from the submitted form.
	 *
	 * @since 2.1.0
	 *
	 * @return array The settings for a points type.
	 */
	protected function get_points_type_settings() {

		$settings = array();

		if ( isset( $_POST['points-name'] ) ) { // WPCS: CSRF OK
			$settings['name'] = trim(
				sanitize_text_field( wp_unslash( $_POST['points-name'] ) ) // WPCS: CSRF OK
			);
		}

		if ( isset( $_POST['points-prefix'] ) ) { // WPCS: CSRF OK
			$settings['prefix'] = ltrim(
				sanitize_text_field( wp_unslash( $_POST['points-prefix'] ) ) // WPCS: CSRF OK
			);
		}

		if ( isset( $_POST['points-suffix'] ) ) { // WPCS: CSRF OK
			$settings['suffix'] = rtrim(
				sanitize_text_field( wp_unslash( $_POST['points-suffix'] ) ) // WPCS: CSRF OK
			);
		}

		return $settings;
	}

	/**
	 * Update an existing points type.
	 *
	 * @since 2.1.0
	 */
	protected function update_points_type() {

		if (
			! wordpoints_verify_nonce(
				'update_points_type'
				, 'wordpoints_update_points_type-%s'
				, array( 'points-slug' )
				, 'post'
			)
			|| ! isset( $_POST['points-slug'] )
		) {
			return;
		}

		$settings = $this->get_points_type_settings();

		if ( empty( $settings['name'] ) ) {

			add_settings_error(
				'points-name'
				, 'wordpoints_points_type_update'
				, __( 'Error: points type name cannot be empty.', 'wordpoints' )
			);

			return;
		}

		$points_type = sanitize_key( $_POST['points-slug'] );

		$old_settings = wordpoints_get_points_type( $points_type );

		if ( false === $old_settings ) {

			add_settings_error(
				''
				, 'wordpoints_points_type_update'
				, __( 'Error: failed updating points type.', 'wordpoints' )
			);

			return;
		}

		if ( is_array( $old_settings ) ) {
			$settings = array_merge( $old_settings, $settings );
		}

		if ( ! wordpoints_update_points_type( $points_type, $settings ) ) {

			add_settings_error(
				''
				, 'wordpoints_points_type_update'
				, __( 'Error: failed updating points type.', 'wordpoints' )
			);

		} else {

			add_settings_error(
				''
				, 'wordpoints_points_type_update'
				, __( 'Points type updated.', 'wordpoints' )
				, 'updated'
			);
		}
	}

	/**
	 * Create a new points type.
	 *
	 * @since 2.1.0
	 */
	protected function add_points_type() {

		if (
			! wordpoints_verify_nonce(
				'add_new'
				, 'wordpoints_add_new_points_type'
				, null
				, 'post'
			)
		) {
			return;
		}

		$settings = $this->get_points_type_settings();

		$slug = wordpoints_add_points_type( $settings );

		if ( ! $slug ) {

			add_settings_error(
				''
				, 'wordpoints_points_type_create'
				, __( 'Please choose a unique name for this points type.', 'wordpoints' )
			);

		} else {

			$_GET['tab'] = $slug;

			add_settings_error(
				''
				, 'wordpoints_points_type_create'
				, __( 'Points type created.', 'wordpoints' )
				, 'updated'
			);
		}
	}

	/**
	 * Delete a points type.
	 *
	 * @since 2.1.0
	 */
	protected function delete_points_type() {

		if (
			wordpoints_verify_nonce(
				'delete-points-type-nonce'
				, 'wordpoints_delete_points_type-%s'
				, array( 'points-slug' )
				, 'post'
			)
			&& isset( $_POST['points-slug'] )
		) {

			if (
				wordpoints_delete_points_type(
					sanitize_key( $_POST['points-slug'] )
				)
			) {

				add_settings_error(
					''
					, 'wordpoints_points_type_delete'
					, __( 'Points type deleted.', 'wordpoints' )
					, 'updated'
				);

			} else {

				add_settings_error(
					''
					, 'wordpoints_points_type_delete'
					, __( 'Error while deleting.', 'wordpoints' )
				);
			}
		}
	}

	/**
	 * Shows a message to create demo reactions.
	 *
	 * @since 2.4.0
	 */
	public function show_demo_reactions_message() {

		if ( $this->had_reactions || ! $this->current_points_type ) {
			return;
		}

		?>

		<div class="notice notice-info is-dismissible">
			<p>
				<?php esc_html_e( 'Let us help you get started by creating some example reactions. (Don&#8217;t worry, each reaction will only award points once it has been enabled.)', 'wordpoints' ); ?>
			</p>
			<form method="post" action="<?php echo esc_attr( self_admin_url( 'admin.php?page=wordpoints_points_types&tab=' . $this->current_points_type ) ); ?>">
				<p>
					<button class="button-primary">
						<?php esc_html_e( 'Create Reactions', 'wordpoints' ); ?>
					</button>
				</p>
				<?php wp_nonce_field( 'create_demo_reactions', 'create_demo_reactions' ); ?>
			</form>
		</div>

		<?php
	}

	/**
	 * Creates demo reactions for this points type.
	 *
	 * @since 2.4.0
	 */
	public function create_demo_reactions() {

		if ( wordpoints_verify_nonce( 'create_demo_reactions', 'create_demo_reactions', null, 'post' ) ) {

			wordpoints_points_create_demo_reactions( $this->current_points_type );

			wordpoints_show_admin_message(
				__( 'Example reactions created successfully! You can now edit them to your liking, and then enable them to start awarding points. Feel free to delete any that you don&#8217;t want, and to create additional ones as needed.', 'wordpoints' )
				, 'success'
				, array( 'dismissible' => true )
			);
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function display_content() {

		/**
		 * Top of points hooks admin screen.
		 *
		 * @since 2.1.0
		 */
		do_action( 'wordpoints_admin_points_events_head' );

		if ( is_network_admin() ) {
			$title       = __( 'Network Events', 'wordpoints' );
			$description = __( 'Add reactions to these events to award points whenever they take place on this network.', 'wordpoints' );
		} else {
			$title       = __( 'Events', 'wordpoints' );
			$description = __( 'Add reactions to these events to award points whenever they take place on this site.', 'wordpoints' );
		}

		if ( isset( $this->current_points_type ) ) {
			$points_type         = wordpoints_get_points_type( $this->current_points_type );
			$points_type['slug'] = $this->current_points_type;
		} else {
			$points_type = false;
		}

		?>

		<div class="wordpoints-points-type-meta-box-wrap">

				<form>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				</form>

				<div id="poststuff">

					<div id="post-body" class="metabox-holder columns-<?php echo 1 === (int) get_current_screen()->get_columns() ? '1' : '2'; ?>">

						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( $this->id, 'side', $points_type ); ?>
						</div>

						<?php if ( isset( $this->current_points_type ) ) : ?>
							<div class="wordpoints-hook-events-heading">
								<h2><?php echo esc_html( $title ); ?></h2>
								<p class="description">
									<?php echo esc_html( $description ); ?>
									<?php

									echo wp_kses(
										sprintf(
											// translators: URL of points reactions user docs on WordPoints.org.
											__( 'You can learn more about how they work from <a href="%s">the user guide on WordPoints.org</a>.', 'wordpoints' )
											, 'https://wordpoints.org/user-guide/points-reactions/'
										)
										, array( 'a' => array( 'href' => true, 'target' => true ) )
									);

									?>
								</p>
							</div>

							<div id="postbox-container-2" class="postbox-container">
								<?php do_meta_boxes( $this->id, 'events', $points_type ); ?>
							</div>
						<?php endif; ?>

					</div>

					<br class="clear">

				</div>

		</div>

		<?php

		/**
		 * Bottom of points hooks admin screen.
		 *
		 * @since 2.1.0
		 */
		do_action( 'wordpoints_admin_points_events_foot' );
	}
}

// EOF
