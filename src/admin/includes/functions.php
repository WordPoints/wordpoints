<?php

/**
 * Admin-side functions.
 *
 * @package WordPoints\Admin
 * @since 2.1.0
 */

/**
 * Register the admin apps when the main app is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app-apps
 *
 * @param WordPoints_App $app The main WordPoints app.
 */
function wordpoints_hooks_register_admin_apps( $app ) {

	$apps = $app->sub_apps();

	$apps->register( 'admin', 'WordPoints_App' );

	/** @var WordPoints_App $admin */
	$admin = $apps->get( 'admin' );

	$admin->sub_apps()->register( 'screen', 'WordPoints_Admin_Screens' );
}

/**
 * Get the slug of the main administration menu item for the plugin.
 *
 * The main item changes in multisite when the plugin is network activated. In the
 * network admin it is the usual 'wordpoints_configure', while everywhere else it is
 * 'wordpoints_modules' instead.
 *
 * @since 1.2.0
 *
 * @return string The slug for the plugin's main top level admin menu item.
 */
function wordpoints_get_main_admin_menu() {

	$slug = 'wordpoints_configure';

	/*
	 * If the plugin is network active and we are displaying the regular admin menu,
	 * the modules screen should be the main one (the configure menu is only for the
	 * network admin when network active).
	 */
	if ( is_wordpoints_network_active() && 'admin_menu' === current_filter() ) {
		$slug = 'wordpoints_modules';
	}

	return $slug;
}

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @WordPress\action admin_menu
 * @WordPress\action network_admin_menu
 */
function wordpoints_admin_menu() {

	$main_menu  = wordpoints_get_main_admin_menu();
	$wordpoints = __( 'WordPoints', 'wordpoints' );

	/*
	 * The settings page is always the main menu, except when the plugin is network
	 * active on multisite. Then it is only the main menu when in the network admin.
	 */
	if ( 'wordpoints_configure' === $main_menu ) {

		// Main page.
		add_menu_page(
			$wordpoints
			,esc_html( $wordpoints )
			,'manage_options'
			,'wordpoints_configure'
			,'wordpoints_admin_screen_configure'
		);

		// Settings page.
		add_submenu_page(
			'wordpoints_configure'
			,__( 'WordPoints — Settings', 'wordpoints' )
			,esc_html__( 'Settings', 'wordpoints' )
			,'manage_options'
			,'wordpoints_configure'
			,'wordpoints_admin_screen_configure'
		);

	} else {

		/*
		 * When network-active and displaying the admin menu, we don't display the
		 * settings page, instead we display the modules page as the main page.
		 */

		// Main page.
		add_menu_page(
			$wordpoints
			,esc_html( $wordpoints )
			,'activate_wordpoints_modules'
			,'wordpoints_modules'
			,'wordpoints_admin_screen_modules'
		);

	} // End if ( configure is main menu ) else.

	// Modules page.
	add_submenu_page(
		$main_menu
		,__( 'WordPoints — Modules', 'wordpoints' )
		,esc_html__( 'Modules', 'wordpoints' )
		,'activate_wordpoints_modules'
		,'wordpoints_modules'
		,'wordpoints_admin_screen_modules'
	);

	// Module install page.
	add_submenu_page(
		'_wordpoints_modules' // Fake menu.
		,__( 'WordPoints — Install Modules', 'wordpoints' )
		,esc_html__( 'Install Modules', 'wordpoints' )
		,'install_wordpoints_modules'
		,'wordpoints_install_modules'
		,'wordpoints_admin_screen_install_modules'
	);
}

/**
 * Display the modules admin screen.
 *
 * @since 1.2.0
 */
function wordpoints_admin_screen_modules() {

	/**
	 * The modules administration screen.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/modules.php';
}

/**
 * Set up for the modules screen.
 *
 * @since 1.1.0
 *
 * @WordPress\action load-wordpoints_page_wordpoints_modules
 * @WordPress\action load-toplevel_page_wordpoints_modules
 */
function wordpoints_admin_screen_modules_load() {

	/**
	 * Set up for the modules page.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/modules-load.php';
}

/**
 * Display the install modules admin screen.
 *
 * @since 1.1.0
 */
function wordpoints_admin_screen_install_modules() {

	/**
	 * The WordPoints > Install Modules admin panel.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/module-install.php';
}

/**
 * Set up for the configure screen.
 *
 * @since 1.5.0 As wordpoints_admin_sreen_configure_load().
 * @since 2.3.0
 *
 * @WordPress\action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_screen_configure_load() {

	/**
	 * Set up for the WordPoints » Settings administration screen.
	 *
	 * @since 1.5.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/configure-settings-load.php';
}

/**
 * Set up for the configure screen.
 *
 * @since 1.5.0
 * @deprecated 2.3.0 Use wordpoints_admin_screen_configure_load() instead.
 */
function wordpoints_admin_sreen_configure_load() {

	_deprecated_function(
		__FUNCTION__
		, '2.3.0'
		, 'wordpoints_admin_screen_configure_load()'
	);

	wordpoints_admin_screen_configure_load();
}

/**
 * Activate/deactivate components.
 *
 * This function handles activation and deactivation of components from the
 * WordPoints » Settings » Components administration screen.
 *
 * @since 1.0.1
 *
 * @WordPress\action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_activate_components() {

	/**
	 * Set up for the WordPoints > Components administration screen.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/configure-components-load.php';
}

/**
 * Register admin scripts and styles.
 *
 * @since 2.1.0
 *
 * @WordPress\action admin_init
 */
function wordpoints_register_admin_scripts() {

	$assets_url = WORDPOINTS_URL . '/admin/assets';
	$suffix = SCRIPT_DEBUG ? '' : '.min';
	$manifested_suffix = SCRIPT_DEBUG ? '.manifested' : '.min';

	// CSS

	wp_register_style(
		'wordpoints-hooks-admin'
		, "{$assets_url}/css/hooks{$suffix}.css"
		, array( 'dashicons', 'wp-jquery-ui-dialog' )
		, WORDPOINTS_VERSION
	);

	$styles = wp_styles();
	$styles->add_data( 'wordpoints-hooks-admin', 'rtl', 'replace' );

	if ( $suffix ) {
		$styles->add_data( 'wordpoints-hooks-admin', 'suffix', $suffix );
	}

	// JS

	wp_register_script(
		'wordpoints-admin-dismiss-notice'
		, "{$assets_url}/js/dismiss-notice{$suffix}.js"
		, array( 'jquery', 'wp-util' )
		, WORDPOINTS_VERSION
	);

	wp_register_script(
		'wordpoints-hooks-models'
		, "{$assets_url}/js/hooks/models{$manifested_suffix}.js"
		, array( 'backbone', 'jquery-ui-dialog', 'wp-util' )
		, WORDPOINTS_VERSION
	);

	wp_register_script(
		'wordpoints-hooks-views'
		, "{$assets_url}/js/hooks/views{$manifested_suffix}.js"
		, array( 'wordpoints-hooks-models', 'wp-a11y' )
		, WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-hooks-views'
		, 'WordPointsHooksAdminL10n'
		, array(
			'unexpectedError' => __( 'There was an unexpected error. Try reloading the page.', 'wordpoints' ),
			'changesSaved'    => __( 'Your changes have been saved.', 'wordpoints' ),
			// translators: Form field name.
			'emptyField'      => sprintf( __( '%s cannot be empty.', 'wordpoints' ), '{{ data.label }}' ),
			'confirmDelete'   => __( 'Are you sure that you want to delete this reaction? This action cannot be undone.', 'wordpoints' ),
			'confirmTitle'    => __( 'Are you sure?', 'wordpoints' ),
			'deleteText'      => __( 'Delete', 'wordpoints' ),
			'cancelText'      => __( 'Cancel', 'wordpoints' ),
			'separator'       => is_rtl() ? ' « ' : ' » ',
			'target_label'    => __( 'Target', 'wordpoints' ),
			// translators: Form field.
			'cannotBeChanged' => __( '(cannot be changed)', 'wordpoints' ),
			'fieldsInvalid'   => __( 'Error: the values of some fields are invalid. Please correct these and then try again.', 'wordpoints' ),
			'discardedReaction' => __( 'Discarded reaction.', 'wordpoints' ),
			'discardedChanges'  => __( 'Discarded changes.', 'wordpoints' ),
			'saving'            => __( 'Saving&hellp;', 'wordpoints' ),
			'deleting'          => __( 'Deleting&hellp;', 'wordpoints' ),
			'reactionDeleted'   => __( 'Reaction deleted successfully.', 'wordpoints' ),
			'reactionSaved'     => __( 'Reaction saved successfully.', 'wordpoints' ),
		)
	);

	wp_script_add_data(
		'wordpoints-hooks-views'
		, 'wordpoints-templates'
		, '
		<script type="text/template" id="tmpl-wordpoints-hook-reaction">
			<div class="view">
				<div class="title"></div>
				<button type="button" class="edit button-secondary">
					' . esc_html__( 'Edit', 'wordpoints' ) . '
				</button>
				<button type="button" class="close button-secondary">
					' . esc_html__( 'Close', 'wordpoints' ) . '
				</button>
			</div>
			<div class="form">
				<form>
					<div class="fields">
						<div class="settings"></div>
						<div class="target"></div>
					</div>
					<div class="messages">
						<div class="success"></div>
						<div class="err"></div>
					</div>
					<div class="actions">
						<div class="spinner-overlay">
							<span class="spinner is-active"></span>
						</div>
						<div class="action-buttons">
							<button type="button" class="save button-primary" disabled>
								' . esc_html__( 'Save', 'wordpoints' ) . '
							</button>
							<button type="button" class="cancel button-secondary">
								' . esc_html__( 'Cancel', 'wordpoints' ) . '
							</button>
							<button type="button" class="close button-secondary">
								' . esc_html__( 'Close', 'wordpoints' ) . '
							</button>
							<button type="button" class="delete button-secondary">
								' . esc_html__( 'Delete', 'wordpoints' ) . '
							</button>
						</div>
					</div>
				</form>
			</div>
		</script>

		<script type="text/template" id="tmpl-wordpoints-hook-arg-selector">
			<div class="arg-selector">
				<label>
					{{ data.label }}
					<select name="{{ data.name }}"></select>
				</label>
			</div>
		</script>

		<script type="text/template" id="tmpl-wordpoints-hook-arg-option">
			<option value="{{ data.slug }}">{{ data.title }}</option>
		</script>

		<script type="text/template" id="tmpl-wordpoints-hook-reaction-field">
			<p class="description description-thin">
				<label>
					{{ data.label }}
					<input type="{{ data.type }}" class="widefat" name="{{ data.name }}"
					       value="{{ data.value }}"/>
				</label>
			</p>
		</script>

		<script type="text/template" id="tmpl-wordpoints-hook-reaction-select-field">
			<p class="description description-thin">
				<label>
					{{ data.label }}
					<select name="{{ data.name }}" class="widefat"></select>
				</label>
			</p>
		</script>

		<script type="text/template" id="tmpl-wordpoints-hook-reaction-hidden-field">
			<input type="hidden" name="{{ data.name }}" value="{{ data.value }}"/>
		</script>
		'
	);

	wp_register_script(
		'wordpoints-hooks-extension-conditions'
		, "{$assets_url}/js/hooks/extensions/conditions{$manifested_suffix}.js"
		, array( 'wordpoints-hooks-views' )
		, WORDPOINTS_VERSION
	);

	wp_script_add_data(
		'wordpoints-hooks-extension-conditions'
		, 'wordpoints-templates'
		, '
			<script type="text/template" id="tmpl-wordpoints-hook-condition-groups">
				<div class="conditions-title section-title">
					<h4>' . esc_html__( 'Conditions', 'wordpoints' ) . '</h4>
					<button type="button" class="add-new button-secondary button-link">
						<span class="screen-reader-text">' . esc_html__( 'Add New Condition', 'wordpoints' ) . '</span>
						<span class="dashicons dashicons-plus"></span>
					</button>
				</div>
				<div class="add-condition-form hidden">
					<div class="no-conditions hidden">
						' . esc_html__( 'No conditions available.', 'wordpoints' ) . '
					</div>
					<div class="condition-selectors">
						<div class="arg-selectors"></div>
						<div class="condition-selector"></div>
					</div>
					<button type="button" class="confirm-add-new button-secondary" disabled aria-label="' . esc_attr__( 'Add Condition', 'wordpoints' ) . '">
						' . esc_html_x( 'Add', 'reaction condition', 'wordpoints' ) . '
					</button>
					<button type="button" class="cancel-add-new button-secondary" aria-label="' . esc_attr__( 'Cancel Adding New Condition', 'wordpoints' ) . '">
						' . esc_html_x( 'Cancel', 'reaction condition', 'wordpoints' ) . '
					</button>
				</div>
				<div class="condition-groups section-content"></div>
			</script>

			<script type="text/template" id="tmpl-wordpoints-hook-reaction-condition-group">
				<div class="condition-group-title"></div>
			</script>

			<script type="text/template" id="tmpl-wordpoints-hook-reaction-condition">
				<div class="condition-controls">
					<div class="condition-title"></div>
					<button type="button" class="delete button-secondary button-link">
						<span class="screen-reader-text">' . esc_html__( 'Remove Condition', 'wordpoints' ) . '</span>
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
				<div class="condition-settings"></div>
			</script>

			<script type="text/template" id="tmpl-wordpoints-hook-condition-selector">
				<label>
					{{ data.label }}
					<select name="{{ data.name }}"></select>
				</label>
			</script>
		'
	);

	wp_register_script(
		'wordpoints-hooks-extension-periods'
		, "{$assets_url}/js/hooks/extensions/periods{$manifested_suffix}.js"
		, array( 'wordpoints-hooks-views' )
		, WORDPOINTS_VERSION
	);

	wp_script_add_data(
		'wordpoints-hooks-extension-periods'
		, 'wordpoints-templates'
		, '
			<script type="text/template" id="tmpl-wordpoints-hook-periods">
				<div class="wordpoints-hook-periods">
					<div class="periods-title section-title">
						<h4>' . esc_html__( 'Rate Limit', 'wordpoints' ) . '</h4>
					</div>
					<div class="periods section-content"></div>
				</div>
			</script>
			
			<script type="text/template" id="tmpl-wordpoints-hook-reaction-simple-period">
				<div class="wordpoints-period">
					<input type="hidden" name="{{ data.name }}" value="{{ data.length }}" class="widefat wordpoints-hook-period-length" />
					<fieldset>
						<p class="description description-thin">
							<legend>{{ data.length_in_units_label }}</legend>
							<label>
								<span class="screen-reader-text">' . esc_html__( 'Time Units:', 'wordpoints' ) . '</span>
								<select class="widefat wordpoints-hook-period-sync wordpoints-hook-period-units"></select>
							</label>
							<label>
								<span class="screen-reader-text">' . esc_html__( 'Number:', 'wordpoints' ) . '</span>
								<input type="number" value="{{ data.length_in_units }}" class="widefat wordpoints-hook-period-sync wordpoints-hook-period-length-in-units" />
							</label>
						</p>
					</fieldset>
				</div>
			</script>
		'
	);

	wp_register_script(
		'wordpoints-hooks-extension-disable'
		, "{$assets_url}/js/hooks/extensions/disable{$manifested_suffix}.js"
		, array( 'wordpoints-hooks-views' )
		, WORDPOINTS_VERSION
	);

	wp_script_add_data(
		'wordpoints-hooks-extension-disable'
		, 'wordpoints-templates'
		, '
			<script type="text/template" id="tmpl-wordpoints-hook-disable">
				<div class="disable">
					<div class="section-title">
						<h4>' . esc_html__( 'Disable', 'wordpoints' ) . '</h4>
					</div>
					<div class="section-content">
						<p class="description description-thin">
							<label>
								<input type="checkbox" name="disable" value="1" />
								' . esc_html__( 'Disable (make this reaction inactive without deleting it)', 'wordpoints' ) . '
							</label>
						</p>
					</div>
				</div>
			</script>
			
			<script type="text/template" id="tmpl-wordpoints-hook-disabled-text">
				<span class="wordpoints-hook-disabled-text">' . esc_html__( '(Disabled)', 'wordpoints' ) . '</span>
			</script>
		'
	);
}

/**
 * Export the data for the scripts needed to make the hooks UI work.
 *
 * @since 2.1.0
 */
function wordpoints_hooks_ui_setup_script_data() {

	$hooks = wordpoints_hooks();

	$extensions_data = wordpoints_hooks_ui_get_script_data_from_objects(
		$hooks->get_sub_app( 'extensions' )->get_all()
		, 'extension'
	);

	$reactor_data = wordpoints_hooks_ui_get_script_data_from_objects(
		$hooks->get_sub_app( 'reactors' )->get_all()
		, 'reactor'
	);

	$event_action_types = wordpoints_hooks_ui_get_script_data_event_action_types();
	$entities_data = wordpoints_hooks_ui_get_script_data_entities();

	$data = array(
		'fields'     => (object) array(),
		'reactions'  => (object) array(),
		'events'     => (object) array(),
		'extensions' => $extensions_data,
		'entities'   => $entities_data,
		'reactors'   => $reactor_data,
		'event_action_types' => $event_action_types,
	);

	/**
	 * Filter the hooks data used to provide the UI.
	 *
	 * This is currently exported as JSON to the Backbone.js powered UI. But
	 * that could change in the future. The important thing is that the data is
	 * bing exported and will be used by something somehow.
	 *
	 * @param array $data The data.
	 */
	$data = apply_filters( 'wordpoints_hooks_ui_data', $data );

	wp_localize_script(
		'wordpoints-hooks-models'
		, 'WordPointsHooksAdminData'
		, $data
	);
}

/**
 * Get the UI script data from a bunch of objects.
 *
 * @since 2.1.0
 *
 * @param object[] $objects Objects that might provide script UI data.
 * @param string   $type    The type of objects. Used to automatically enqueue
 *                          scripts for the objects.
 *
 * @return array The data extracted from the objects.
 */
function wordpoints_hooks_ui_get_script_data_from_objects( $objects, $type ) {

	$data = array();

	foreach ( $objects as $slug => $object ) {

		if ( $object instanceof WordPoints_Hook_UI_Script_Data_ProviderI ) {
			$data[ $slug ] = $object->get_ui_script_data();
		}

		if ( wp_script_is( "wordpoints-hooks-{$type}-{$slug}", 'registered' ) ) {
			wp_enqueue_script( "wordpoints-hooks-{$type}-{$slug}" );
		}
	}

	return $data;
}

/**
 * Get the entities data for use in the hooks UI.
 *
 * @since 2.1.0
 *
 * @return array The entities data for use in the hooks UI.
 */
function wordpoints_hooks_ui_get_script_data_entities() {

	$entities = wordpoints_entities();

	$entities_data = array();

	/** @var WordPoints_Class_Registry_Children $entity_children */
	$entity_children = $entities->get_sub_app( 'children' );

	/** @var WordPoints_Entity $entity */
	foreach ( $entities->get_all() as $slug => $entity ) {

		$child_data = array();

		/** @var WordPoints_EntityishI $child */
		foreach ( $entity_children->get_children( $slug ) as $child_slug => $child ) {

			$child_data[ $child_slug ] = array(
				'slug'  => $child_slug,
				'title' => $child->get_title(),
			);

			if ( $child instanceof WordPoints_Entity_Attr ) {

				$child_data[ $child_slug ]['_type']     = 'attr';
				$child_data[ $child_slug ]['data_type'] = $child->get_data_type();

			} elseif ( $child instanceof WordPoints_Entity_Relationship ) {

				$child_data[ $child_slug ]['_type']     = 'relationship';
				$child_data[ $child_slug ]['primary']   = $child->get_primary_entity_slug();
				$child_data[ $child_slug ]['secondary'] = $child->get_related_entity_slug();
			}

			/**
			 * Filter the data for an entity child.
			 *
			 * Entity children include attributes and relationships.
			 *
			 * @param array                $data  The data for the entity child.
			 * @param WordPoints_Entityish $child The child's object.
			 */
			$child_data[ $child_slug ] = apply_filters(
				'wordpoints_hooks_ui_data_entity_child'
				, $child_data[ $child_slug ]
				, $child
			);
		}

		$entities_data[ $slug ] = array(
			'slug'     => $slug,
			'title'    => $entity->get_title(),
			'children' => $child_data,
			'id_field' => $entity->get_id_field(),
			'_type'    => 'entity',
		);

		if ( $entity instanceof WordPoints_Entity_EnumerableI ) {

			$values = array();

			foreach ( $entity->get_enumerated_values() as $value ) {
				if ( $entity->set_the_value( $value ) ) {
					$values[] = array(
						'value' => $entity->get_the_id(),
						'label' => $entity->get_the_human_id(),
					);
				}
			}

			$entities_data[ $slug ]['values'] = $values;
		}

		/**
		 * Filter the data for an entity.
		 *
		 * @param array             $data   The data for the entity.
		 * @param WordPoints_Entity $entity The entity object.
		 */
		$entities_data[ $slug ] = apply_filters(
			'wordpoints_hooks_ui_data_entity'
			, $entities_data[ $slug ]
			, $entity
		);

	} // End foreach ( entities ).

	return $entities_data;
}

/**
 * Get a list of action types for each event for the hooks UI script data.
 *
 * @since 2.1.0
 *
 * @return array The event action types.
 */
function wordpoints_hooks_ui_get_script_data_event_action_types() {

	// We want a list of the action types for each event. We can start with this list
	// but it is indexed by action slug and then action type and then event slug, so
	// we ned to do some processing.
	$event_index = wordpoints_hooks()->get_sub_app( 'router' )->get_event_index();

	// We don't care about the action slugs, so first we get rid of that bottom level
	// of the array.
	$event_index = call_user_func_array( 'array_merge_recursive', $event_index );

	$event_action_types = array();

	// This leaves us the event indexed by action type. But we actually need to flip
	// this, so that we have the action types indexed by event slug.
	foreach ( $event_index as $action_type => $events ) {
		foreach ( $events as $event => $unused ) {
			$event_action_types[ $event ][ $action_type ] = true;
		}
	}

	return $event_action_types;
}

/**
 * Append templates registered in wordpoints-templates script data to scripts.
 *
 * One day templates will probably be stored in separate files instead.
 *
 * @link https://core.trac.wordpress.org/ticket/31281
 *
 * @since 2.1.0
 *
 * @WordPress\filter script_loader_tag
 *
 * @param string $html   The HTML for the script.
 * @param string $handle The handle of the script.
 *
 * @return string The HTML with templates appended.
 */
function wordpoints_script_templates_filter( $html, $handle ) {

	global $wp_scripts;

	$templates = $wp_scripts->get_data( $handle, 'wordpoints-templates' );

	if ( $templates ) {
		$html .= $templates;
	}

	return $html;
}

/**
 * Display an error message.
 *
 * @since 1.0.0
 * @since 1.8.0 The $args parameter was added.
 *
 * @uses wordpoints_show_admin_message()
 *
 * @param string $message The text for the error message.
 * @param array  $args    Other optional arguments.
 */
function wordpoints_show_admin_error( $message, array $args = array() ) {

	wordpoints_show_admin_message( $message, 'error', $args );
}

/**
 * Display an update message.
 *
 * You should use {@see wordpoints_show_admin_error()} instead for showing error
 * messages. Currently there aren't wrappers for the other types, as they aren't used
 * in WordPoints core.
 *
 * @since 1.0.0
 * @since 1.2.0  The $type parameter is now properly escaped.
 * @since 1.8.0  The $message will be passed through wp_kses().
 * @since 1.8.0  The $args parameter was added with "dismissable" and "option" args.
 * @since 1.10.0 The "dismissable" arg was renamed to "dismissible".
 * @since 2.1.0  Now supports 'warning' and 'info' message types, and 'updated' is
 *               deprecated in favor of 'success'.
 *
 * @param string $message The text for the message.
 * @param string $type    The type of message to display, 'success' (default),
 *                        'error', 'warning' or 'info'.
 * @param array  $args    {
 *        Other optional arguments.
 *
 *        @type bool   $dismissible Whether this notice can be dismissed. Default is
 *                                  false (not dismissible).
 *        @type string $option      An option to delete when if message is dismissed.
 *                                  Required when $dismissible is true.
 * }
 */
function wordpoints_show_admin_message( $message, $type = 'success', array $args = array() ) {

	$defaults = array(
		'dismissible' => false,
		'option'      => '',
	);

	$args = array_merge( $defaults, $args );

	if ( isset( $args['dismissable'] ) ) {

		$args['dismissible'] = $args['dismissable'];

		_deprecated_argument(
			__FUNCTION__
			, '1.10.0'
			, 'The "dismissable" argument has been replaced by the correct spelling, "dismissible".'
		);
	}

	if ( 'updated' === $type ) {

		$type = 'success';

		_deprecated_argument(
			__FUNCTION__
			, '2.1.0'
			, 'Use "success" instead of "updated" for the $type argument.'
		);
	}

	if ( $args['dismissible'] && $args['option'] ) {
		wp_enqueue_script( 'wordpoints-admin-dismiss-notice' );
	}

	?>

	<div
		class="notice notice-<?php echo sanitize_html_class( $type, 'success' ); ?><?php echo ( $args['dismissible'] ) ? ' is-dismissible' : ''; ?>"
		<?php if ( $args['dismissible'] && $args['option'] ) : ?>
			data-nonce="<?php echo esc_attr( wp_create_nonce( "wordpoints_dismiss_notice-{$args['option']}" ) ); ?>"
			data-option="<?php echo esc_attr( $args['option'] ); ?>"
		<?php endif; ?>
		>
		<p>
			<?php echo wp_kses( $message, 'wordpoints_admin_message' ); ?>
		</p>
		<?php if ( $args['dismissible'] && $args['option'] ) : ?>
			<form method="post" class="wordpoints-notice-dismiss-form" style="padding-bottom: 5px;">
				<input type="hidden" name="wordpoints_notice" value="<?php echo esc_html( $args['option'] ); ?>" />
				<?php wp_nonce_field( "wordpoints_dismiss_notice-{$args['option']}" ); ?>
				<?php submit_button( __( 'Hide This Notice', 'wordpoints' ), 'secondary', 'wordpoints_dismiss_notice', false ); ?>
			</form>
		<?php endif; ?>
	</div>

	<?php
}

/**
 * Get the current tab.
 *
 * @since 1.0.0
 *
 * @param array $tabs The tabs. If passed, the first key will be returned if
 *        $_GET['tab'] is not set, or not one of the values in $tabs.
 *
 * @return string
 */
function wordpoints_admin_get_current_tab( array $tabs = null ) {

	$tab = '';

	if ( isset( $_GET['tab'] ) ) { // WPCS: CSRF OK.

		$tab = sanitize_key( $_GET['tab'] ); // WPCS: CSRF OK.
	}

	if ( isset( $tabs ) && ! isset( $tabs[ $tab ] ) ) {

		reset( $tabs );
		$tab = key( $tabs );
	}

	return $tab;
}

/**
 * Display a set of tabs.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_admin_get_current_tab()
 *
 * @param string[] $tabs         The tabs. Keys are slugs, values displayed text.
 * @param bool     $show_heading Whether to show an <h1> element using the current
 *                               tab. Default is true.
 */
function wordpoints_admin_show_tabs( $tabs, $show_heading = true ) {

	$current = wordpoints_admin_get_current_tab( $tabs );

	if ( $show_heading ) {

		// translators: Current tab name.
		echo '<h1>', esc_html( sprintf( __( 'WordPoints — %s', 'wordpoints' ), $tabs[ $current ] ) ), '</h1>';
	}

	echo '<h2 class="nav-tab-wrapper">';

	$page = '';

	if ( isset( $_GET['page'] ) ) { // WPCS: CSRF OK.
		$page = sanitize_key( $_GET['page'] ); // WPCS: CSRF OK.
	}

	foreach ( $tabs as $tab => $name ) {

		$class = ( $tab === $current ) ? ' nav-tab-active' : '';

		echo '<a class="nav-tab ', sanitize_html_class( $class ), '" href="?page=', rawurlencode( $page ), '&amp;tab=', rawurlencode( $tab ), '">', esc_html( $name ), '</a>';
	}

	echo '</h2>';
}

/**
 * Display the upload module from zip form.
 *
 * @since 1.1.0
 *
 * @WordPress\action wordpoints_install_modules-upload
 */
function wordpoints_install_modules_upload() {

	?>

	<style type="text/css">
		.wordpoints-upload-module {
			display: block;
		}
	</style>

	<div class="upload-plugin wordpoints-upload-module">
		<p class="install-help"><?php esc_html_e( 'If you have a module in a .zip format, you may install it by uploading it here.', 'wordpoints' ); ?></p>
		<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo esc_url( self_admin_url( 'update.php?action=upload-wordpoints-module' ) ); ?>">
			<?php wp_nonce_field( 'wordpoints-module-upload' ); ?>
			<label class="screen-reader-text" for="modulezip"><?php esc_html_e( 'Module zip file', 'wordpoints' ); ?></label>
			<input type="file" id="modulezip" name="modulezip" />
			<?php submit_button( __( 'Install Now', 'wordpoints' ), 'button', 'install-module-submit', false ); ?>
		</form>
	</div>

	<?php
}

/**
 * Perform module upload from .zip file.
 *
 * @since 1.1.0
 *
 * @WordPress\action update-custom_upload-wordpoints-module
 */
function wordpoints_upload_module_zip() {

	global $title, $parent_file, $submenu_file;

	if ( ! current_user_can( 'install_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to install WordPoints modules on this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'wordpoints-module-upload' );

	$file_upload = new File_Upload_Upgrader( 'modulezip', 'package' );

	$title = esc_html__( 'Upload WordPoints Module', 'wordpoints' );
	$parent_file  = 'admin.php';
	$submenu_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$upgrader = new WordPoints_Module_Installer(
		new WordPoints_Module_Installer_Skin(
			array(
				// translators: File name.
				'title' => sprintf( esc_html__( 'Installing Module from uploaded file: %s', 'wordpoints' ), esc_html( basename( $file_upload->filename ) ) ),
				'nonce' => 'wordpoints-module-upload',
				'url'   => add_query_arg( array( 'package' => $file_upload->id ), self_admin_url( 'update.php?action=upload-wordpoints-module' ) ),
				'type'  => 'upload',
			)
		)
	);

	$result = $upgrader->install( $file_upload->package );

	if ( $result || is_wp_error( $result ) ) {
		$file_upload->cleanup();
	}

	include ABSPATH . 'wp-admin/admin-footer.php';
}

/**
 * Notify the user when they try to install a module on the plugins screen.
 *
 * The function is hooked to the upgrader_source_selection action twice. The first
 * time it is called, we just save a local copy of the source path. This is
 * necessary because the second time around the source will be a WP_Error if there
 * are no plugins in it, but we have to have the source location so that we can check
 * if it is a module instead of a plugin.
 *
 * @since 1.9.0
 *
 * @WordPress\action upgrader_source_selection See above for more info.
 *
 * @param string|WP_Error $source The module source.
 *
 * @return string|WP_Error The filtered module source.
 */
function wordpoints_plugin_upload_error_filter( $source ) {

	static $_source;

	if ( ! isset( $_source ) ) {

		$_source = $source;

	} else {

		global $wp_filesystem;

		if (
			! is_wp_error( $_source )
			&& is_wp_error( $source )
			&& 'incompatible_archive_no_plugins' === $source->get_error_code()
		) {

			$working_directory = str_replace(
				$wp_filesystem->wp_content_dir()
				, trailingslashit( WP_CONTENT_DIR )
				, $_source
			);

			if ( is_dir( $working_directory ) ) {

				$files = glob( $working_directory . '*.php' );

				if ( is_array( $files ) ) {

					// Check if the folder contains a module.
					foreach ( $files as $file ) {

						$info = wordpoints_get_module_data( $file, false, false );

						if ( ! empty( $info['name'] ) ) {
							$source = new WP_Error(
								'wordpoints_module_archive_not_plugin'
								, $source->get_error_message()
								, __( 'This appears to be a WordPoints module archive. Try installing it on the WordPoints module install screen instead.', 'wordpoints' )
							);

							break;
						}
					}
				}
			}
		}

		unset( $_source );

	} // End if ( ! isset( $_source ) ) else.

	return $source;
}

/**
 * Add a sidebar to the general settings page.
 *
 * @since 1.1.0
 *
 * @WordPress\action wordpoints_admin_settings_bottom 5 Before other items are added.
 */
function wordpoints_admin_settings_screen_sidebar() {

	?>

	<div style="height: 120px;border: none;padding: 1px 12px;background-color: #fff;border-left: 4px solid rgb(122, 208, 58);box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);margin-top: 50px;">
		<div style="width:48%;float:left;">
			<h3><?php esc_html_e( 'Like this plugin?', 'wordpoints' ); ?></h3>
			<?php // translators: URL for leaving a review of WordPoints on WordPress.org. ?>
			<p><?php echo wp_kses( sprintf( __( 'If you think WordPoints is great, let everyone know by giving it a <a href="%s">5 star rating</a>.', 'wordpoints' ), 'https://wordpress.org/support/view/plugin-reviews/wordpoints?rate=5#postform' ), array( 'a' => array( 'href' => true ) ) ); ?></p>
			<p><?php esc_html_e( 'If you don&#8217;t think this plugin deserves 5 stars, please let us know how we can improve it.', 'wordpoints' ); ?></p>
		</div>
		<div style="width:48%;float:left;">
			<h3><?php esc_html_e( 'Need help?', 'wordpoints' ); ?></h3>
			<?php // translators: URL of WordPoints plugin support forums WordPress.org. ?>
			<p><?php echo wp_kses( sprintf( __( 'Post your feature request or support question in the <a href="%s">support forums</a>', 'wordpoints' ), 'https://wordpress.org/support/plugin/wordpoints' ), array( 'a' => array( 'href' => true ) ) ); ?></p>
			<p><em><?php esc_html_e( 'Thank you for using WordPoints!', 'wordpoints' ); ?></em></p>
		</div>
	</div>

	<?php
}

/**
 * Display notices to the user on the administration panels.
 *
 * @since 1.8.0
 *
 * @WordPress\action admin_notices
 */
function wordpoints_admin_notices() {

	wordpoints_delete_admin_notice_option();

	if ( current_user_can( 'activate_wordpoints_modules' ) ) {

		if ( is_network_admin() ) {

			$deactivated_modules = get_site_option( 'wordpoints_breaking_deactivated_modules' );

			if ( is_array( $deactivated_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of modules.
						__( 'WordPoints has deactivated the following modules because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $deactivated_modules )
					)
					, array(
						'dismissible' => true,
						'option' => 'wordpoints_breaking_deactivated_modules',
					)
				);
			}

			$incompatible_modules = get_site_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of modules.
						__( 'WordPoints has deactivated the following network-active modules because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option' => 'wordpoints_incompatible_modules',
					)
				);
			}

		} else {

			$incompatible_modules = get_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of modules.
						__( 'WordPoints has deactivated the following modules on this site because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option' => 'wordpoints_incompatible_modules',
					)
				);
			}

		} // End if ( is_network_admin() ) else.

	} // End if ( user can activate modules ).
}

/**
 * Handle a request to delete an option tied to an admin notice.
 *
 * @since 2.1.0
 *
 * @WordPress\action wp_ajax_wordpoints-delete-admin-notice-option
 */
function wordpoints_delete_admin_notice_option() {

	// Check if any notices have been dismissed.
	$is_notice_dismissed = wordpoints_verify_nonce(
		'_wpnonce'
		, 'wordpoints_dismiss_notice-%s'
		, array( 'wordpoints_notice' )
		, 'post'
	);

	if ( $is_notice_dismissed && isset( $_POST['wordpoints_notice'] ) ) {

		$option = sanitize_key( $_POST['wordpoints_notice'] );

		if ( ! is_network_admin() && 'wordpoints_incompatible_modules' === $option ) {
			delete_option( $option );
		} else {
			wordpoints_delete_maybe_network_option( $option );
		}
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		wp_die( '', 200 );
	}
}

/**
 * Save the screen options.
 *
 * @since 2.0.0
 *
 * @WordPress\action set-screen-option
 *
 * @param mixed  $sanitized The sanitized option value, or false if not sanitized.
 * @param string $option    The option being saved.
 * @param mixed  $value     The raw value supplied by the user.
 *
 * @return mixed The option value, sanitized if it is for one of the plugin's screens.
 */
function wordpoints_admin_set_screen_option( $sanitized, $option, $value ) {

	switch ( $option ) {

		case 'wordpoints_page_wordpoints_modules_per_page':
		case 'wordpoints_page_wordpoints_modules_network_per_page':
		case 'toplevel_page_wordpoints_modules_per_page':
			$sanitized = wordpoints_posint( $value );
			break;
	}

	return $sanitized;
}

/**
 * Ajax callback to load the modules admin screen when running module compat checks.
 *
 * We run this Ajax action to check module compatibility before loading modules
 * after WordPoints is updated to a new major version. This avoids breaking the site
 * if some modules aren't compatible with the backward-incompatible changes that are
 * present in a major version.
 *
 * @since 2.0.0
 *
 * @WordPress\action wp_ajax_nopriv_wordpoints_breaking_module_check
 */
function wordpoints_admin_ajax_breaking_module_check() {

	if ( ! isset( $_GET['wordpoints_module_check'] ) ) { // WPCS: CSRF OK.
		wp_die( '', 400 );
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || ! hash_equals( $nonce, sanitize_key( $_GET['wordpoints_module_check'] ) ) ) { // WPCS: CSRF OK.
		wp_die( '', 403 );
	}

	// The list table constructor calls WP_Screen::get(), which expects this.
	$GLOBALS['hook_suffix'] = null;

	wordpoints_admin_screen_modules();

	wp_die( '', 200 );
}

/**
 * Initialize the Ajax actions.
 *
 * @since 2.1.0
 *
 * @WordPress\action admin_init
 */
function wordpoints_hooks_admin_ajax() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		new WordPoints_Admin_Ajax_Hooks;
	}
}

/**
 * Get the PHP version required for an update for the plugin.
 *
 * @since 2.3.0
 *
 * @return string|false The PHP version number, or false if no requirement could be
 *                      determined. The version may be in x.y or x.y.z format.
 */
function wordpoints_admin_get_php_version_required_for_update() {

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	// We store this as a special field on the update plugins transient. That way it
	// is cached, and we don't need to worry about keeping the cache in sync with
	// this transient.
	$updates = get_site_transient( 'update_plugins' );

	if ( ! isset( $updates->response[ $plugin_basename ] ) ) {
		return false;
	}

	if ( ! isset( $updates->response[ $plugin_basename ]->wordpoints_required_php ) ) {

		/**
		 * The plugin install functions.
		 *
		 * @since 2.3.0
		 */
		require_once ABSPATH . '/wp-admin/includes/plugin-install.php';

		$info = plugins_api(
			'plugin_information'
			, array(
				'slug'   => 'wordpoints',
				// We need to use the default locale in case the pattern we need to
				// search for would have gotten lost in translation.
				'locale' => 'en_US',
			)
		);

		if ( is_wp_error( $info ) ) {
			return false;
		}

		preg_match(
			'/requires php (\d+\.\d+(?:\.\d)?)/i'
			, $info->sections['description']
			, $matches
		);

		$version = false;

		if ( ! empty( $matches[1] ) ) {
			$version = $matches[1];
		}

		$updates->response[ $plugin_basename ]->wordpoints_required_php = $version;

		set_site_transient( 'update_plugins', $updates );

	} // End if ( PHP requirements not in cache ).

	return $updates->response[ $plugin_basename ]->wordpoints_required_php;
}

/**
 * Checks if the PHP version meets the requirements of the next WordPoints update.
 *
 * @since 2.3.0
 *
 * @return bool Whether the PHP version meets the requirements of the next update.
 */
function wordpoints_admin_is_running_php_version_required_for_update() {

	$required_version = wordpoints_admin_get_php_version_required_for_update();

	// If there is no required version, then the requirement is met.
	if ( ! $required_version ) {
		return true;
	}

	return version_compare( PHP_VERSION, $required_version, '>=' );
}

/**
 * Replaces the update notice with an error message when PHP requirements aren't met.
 *
 * Normally WordPress displays an update notice row in the plugins list table on the
 * Plugins screen. However, if the next version of WordPoints requires a greater PHP
 * version than is currently in use, we replace that row with an error message
 * informing the user of the situation instead.
 *
 * @since 2.3.0
 *
 * @WordPress\action load-plugins.php
 */
function wordpoints_admin_maybe_disable_update_row_for_php_version_requirement() {

	if ( wordpoints_admin_is_running_php_version_required_for_update() ) {
		return;
	}

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	// Remove the default update row function.
	remove_action( "after_plugin_row_{$plugin_basename}", 'wp_plugin_update_row', 10 );

	// And add a custom function of our own to output an error message.
	add_action(
		"after_plugin_row_{$plugin_basename}"
		, 'wordpoints_admin_not_running_php_version_required_for_update_plugin_row'
		, 10
		, 2
	);
}

/**
 * Outputs an error row for an update requiring a greater PHP version than is in use.
 *
 * This is used to replace the default update notice row that WordPress displays in
 * the plugins table if an update for WordPoints requires a greater version of PHP
 * than the site is currently running. This prevents the user from being able to
 * update, and informs them of the situation so that they can take action to update
 * their version of PHP.
 *
 * @since 2.3.0
 *
 * @WordPress\action after_plugin_row_wordpoints/wordpoints.php
 *
 * @param string $file        Plugin basename.
 * @param array  $plugin_data Plugin data, as returned by the plugins API.
 */
function wordpoints_admin_not_running_php_version_required_for_update_plugin_row(
	$file,
	$plugin_data
) {

	if ( is_multisite() && ! is_network_admin() ) {
		return;
	}

	// First check that there is actually an update available.
	$updates = get_site_transient( 'update_plugins' );

	if ( ! isset( $updates->response[ $file ] ) ) {
		return;
	}

	$response = $updates->response[ $file ];

	$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

	if ( is_network_admin() ) {
		$active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
	} else {
		$active_class = is_plugin_active( $file ) ? ' active' : '';
	}

	?>

	<tr
		class="plugin-update-tr <?php echo esc_attr( $active_class ); ?>"
		id="<?php echo esc_attr( $response->slug . '-update' ); ?>"
		data-slug="<?php echo esc_attr( $response->slug ); ?>"
		data-plugin="<?php echo esc_attr( $file ); ?>"
	>
		<td
			colspan="<?php echo esc_attr( $wp_list_table->get_column_count() ); ?>"
			class="plugin-update colspanchange"
		>
			<div class="update-message inline notice notice-error notice-alt">
				<p>
					<?php esc_html_e( 'A WordPoints update is available, but your system is not compatible because it is running an outdated version of PHP.', 'wordpoints' ); ?>
					<?php

					echo wp_kses(
						sprintf(
							// translators: URL of WordPoints PHP Compatibility docs.
							__( 'See <a href="%s">the WordPoints user guide</a> for more information.', 'wordpoints' )
							, 'https://wordpoints.org/user-guide/php-compatibility/'
						)
						, array( 'a' => array( 'href' => true ) )
					);

					?>
				</p>
			</div>
		</td>
	</tr>

	<?php

	// JavaScript to disable the bulk upgrade checkbox.
	// See WP_Plugins_List_Table::single_row().
	$checkbox_id = 'checkbox_' . md5( $plugin_data['Name'] );

	?>

	<script type="text/javascript">
		document.getElementById(
			<?php echo wp_json_encode( $checkbox_id ) ?>
		).disabled = true;
	</script>

	<?php
}

/**
 * Hides the plugin on the Updates screen if the PHP version requirements aren't met.
 *
 * On the Dashboard » Updates screen, WordPress displays a table of the available
 * plugin updates. This function will prevent an update for WordPoints form being
 * displayed in that table, if the PHP version requirements for that update are not
 * met by the site.
 *
 * It is also used to hide the "Install Update Now" button in the plugin information
 * dialog.
 *
 * @since 2.3.0
 *
 * @WordPress\action load-update-core.php
 * @WordPress\action install_plugins_pre_plugin-information
 */
function wordpoints_admin_maybe_remove_from_updates_screen() {

	if ( wordpoints_admin_is_running_php_version_required_for_update() ) {
		return;
	}

	// Add filter to remove WordPoints from the update plugins list.
	add_filter(
		'site_transient_update_plugins'
		, 'wordpoints_admin_remove_wordpoints_from_update_plugins_transient'
	);
}

/**
 * Filter callback to remove WordPoints from the update plugins list.
 *
 * @since 2.3.0
 *
 * @WordPress\filter site_transient_update_plugins
 *                   Added by wordpoints_admin_maybe_remove_from_updates_screen().
 *
 * @param object $data Object of plugin update data.
 *
 * @return object The filtered object.
 */
function wordpoints_admin_remove_wordpoints_from_update_plugins_transient( $data ) {

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	if ( isset( $data->response[ $plugin_basename ] ) ) {
		unset( $data->response[ $plugin_basename ] );
	}

	return $data;
}

// EOF
