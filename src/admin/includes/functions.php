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
 * 'wordpoints_extensions' instead.
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
		$slug = 'wordpoints_extensions';
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
			, esc_html( $wordpoints )
			, 'manage_options'
			, 'wordpoints_configure'
			, 'wordpoints_admin_screen_configure'
		);

		// Settings page.
		add_submenu_page(
			'wordpoints_configure'
			, __( 'WordPoints — Settings', 'wordpoints' )
			, esc_html__( 'Settings', 'wordpoints' )
			, 'manage_options'
			, 'wordpoints_configure'
			, 'wordpoints_admin_screen_configure'
		);

	} else {

		/*
		 * When network-active and displaying the admin menu, we don't display the
		 * settings page, instead we display the modules page as the main page.
		 */

		// Main page.
		add_menu_page(
			$wordpoints
			, esc_html( $wordpoints )
			, 'activate_wordpoints_extensions'
			, 'wordpoints_extensions'
			, 'wordpoints_admin_screen_modules'
		);

	} // End if ( configure is main menu ) else.

	// Extensions page.
	add_submenu_page(
		$main_menu
		, __( 'WordPoints — Extensions', 'wordpoints' )
		, esc_html__( 'Extensions', 'wordpoints' )
		, 'activate_wordpoints_extensions'
		, 'wordpoints_extensions'
		, 'wordpoints_admin_screen_modules'
	);

	// Back-compat for extensions page when the slug was "modules".
	add_menu_page(
		__( 'WordPoints — Extensions', 'wordpoints' )
		, esc_html__( 'Extensions', 'wordpoints' )
		, 'activate_wordpoints_extensions'
		, 'wordpoints_modules'
		, 'wordpoints_admin_screen_modules'
	);

	// Hack so that this page isn't displayed in the menu.
	remove_menu_page( 'wordpoints_modules' );

	// Extensions install page.
	add_submenu_page(
		'_wordpoints_extensions' // Fake menu.
		, __( 'WordPoints — Install Extensions', 'wordpoints' )
		, esc_html__( 'Install Extensions', 'wordpoints' )
		, 'install_wordpoints_extensions'
		, 'wordpoints_install_extensions'
		, 'wordpoints_admin_screen_install_modules'
	);

	// Back-compat for extensions install page when the slug was "modules".
	add_submenu_page(
		'_wordpoints_extensions' // Fake menu.
		, __( 'WordPoints — Install Extensions', 'wordpoints' )
		, esc_html__( 'Install Extensions', 'wordpoints' )
		, 'install_wordpoints_extensions'
		, 'wordpoints_install_modules'
		, 'wordpoints_admin_screen_install_modules'
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
 * @WordPress\action load-wordpoints_page_wordpoints_extensions
 * @WordPress\action load-toplevel_page_wordpoints_extensions
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

	$assets_url        = WORDPOINTS_URL . '/admin/assets';
	$suffix            = SCRIPT_DEBUG ? '' : '.min';
	$manifested_suffix = SCRIPT_DEBUG ? '.manifested' : '.min';

	// CSS

	wp_register_style(
		'wordpoints-admin-extensions-list-table'
		, "{$assets_url}/css/extensions-list-table{$suffix}.css"
		, array()
		, WORDPOINTS_VERSION
	);

	wp_register_style(
		'wordpoints-admin-extension-updates-table'
		, "{$assets_url}/css/extension-updates-table{$suffix}.css"
		, array()
		, WORDPOINTS_VERSION
	);

	wp_register_style(
		'wordpoints-hooks-admin'
		, "{$assets_url}/css/hooks{$suffix}.css"
		, array( 'dashicons', 'wp-jquery-ui-dialog' )
		, WORDPOINTS_VERSION
	);

	$styles = wp_styles();
	$styles->add_data( 'wordpoints-admin-extensions-list-table', 'rtl', 'replace' );
	$styles->add_data( 'wordpoints-hooks-admin', 'rtl', 'replace' );

	if ( $suffix ) {
		$styles->add_data( 'wordpoints-admin-extensions-list-table', 'suffix', $suffix );
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
			'unexpectedError'   => __( 'There was an unexpected error. Try reloading the page.', 'wordpoints' ),
			'changesSaved'      => __( 'Your changes have been saved.', 'wordpoints' ),
			// translators: Form field name.
			'emptyField'        => sprintf( __( '%s cannot be empty.', 'wordpoints' ), '{{ data.label }}' ),
			'confirmAboutTo'    => __( 'You are about to delete the following reaction:', 'wordpoints' ),
			'confirmDelete'     => __( 'Are you sure that you want to delete this reaction? This action cannot be undone.', 'wordpoints' ),
			'confirmTitle'      => __( 'Are you sure?', 'wordpoints' ),
			'deleteText'        => __( 'Delete', 'wordpoints' ),
			'cancelText'        => __( 'Cancel', 'wordpoints' ),
			'separator'         => is_rtl() ? ' « ' : ' » ',
			'target_label'      => __( 'Target', 'wordpoints' ),
			// translators: Form field.
			'cannotBeChanged'   => __( '(cannot be changed)', 'wordpoints' ),
			'fieldsInvalid'     => __( 'Error: the values of some fields are invalid. Please correct these and then try again.', 'wordpoints' ),
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
					<button type="button" class="add-new button-secondary wordpoints-hooks-icon-button">
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
					<button type="button" class="delete button-secondary wordpoints-hooks-icon-button">
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
	$entities_data      = wordpoints_hooks_ui_get_script_data_entities();

	$data = array(
		'fields'             => (object) array(),
		'reactions'          => (object) array(),
		'events'             => (object) array(),
		'extensions'         => $extensions_data,
		'entities'           => $entities_data,
		'reactors'           => $reactor_data,
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
 * @WordPress\action wordpoints_install_extensions-upload
 */
function wordpoints_install_modules_upload() {

	?>

	<style type="text/css">
		.wordpoints-upload-module {
			display: block;
		}
	</style>

	<div class="upload-plugin wordpoints-upload-module">
		<p class="install-help"><?php esc_html_e( 'If you have an extension in a .zip format, you may install it by uploading it here.', 'wordpoints' ); ?></p>
		<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo esc_url( self_admin_url( 'update.php?action=upload-wordpoints-module' ) ); ?>">
			<?php wp_nonce_field( 'wordpoints-module-upload' ); ?>
			<label class="screen-reader-text" for="modulezip"><?php esc_html_e( 'Extension zip file', 'wordpoints' ); ?></label>
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

	if ( ! current_user_can( 'install_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to install WordPoints extensions on this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'wordpoints-module-upload' );

	$file_upload = new File_Upload_Upgrader( 'modulezip', 'package' );

	$title        = esc_html__( 'Upload WordPoints Extension', 'wordpoints' );
	$parent_file  = 'admin.php';
	$submenu_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$upgrader = new WordPoints_Module_Installer(
		new WordPoints_Module_Installer_Skin(
			array(
				// translators: File name.
				'title' => sprintf( esc_html__( 'Installing Extension from uploaded file: %s', 'wordpoints' ), esc_html( basename( $file_upload->filename ) ) ),
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

	require ABSPATH . 'wp-admin/admin-footer.php';
}

/**
 * Handles a request to upgrade an extension, displaying the extension upgrade screen.
 *
 * @since 2.4.0
 *
 * @WordPress\action update-custom_wordpoints-upgrade-extension
 */
function wordpoints_admin_screen_upgrade_extension() {

	global $title, $parent_file;

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	$extension = ( isset( $_REQUEST['extension'] ) )
		? sanitize_text_field( wp_unslash( $_REQUEST['extension'] ) ) // WPCS: CSRF OK.
		: '';

	check_admin_referer( 'upgrade-extension_' . $extension );

	$title       = __( 'Update WordPoints Extension', 'wordpoints' );
	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$upgrader = new WordPoints_Extension_Upgrader(
		new WordPoints_Extension_Upgrader_Skin(
			array(
				'title'     => $title,
				'nonce'     => 'upgrade-extension_' . $extension,
				'url'       => 'update.php?action=wordpoints-upgrade-extension&extension=' . rawurlencode( $extension ),
				'extension' => $extension,
			)
		)
	);

	$upgrader->upgrade( $extension );

	require ABSPATH . 'wp-admin/admin-footer.php';
}

/**
 * Reactivates an extension in an iframe after it was updated.
 *
 * @since 2.4.0
 *
 * @WordPress\action update-custom_wordpoints-reactivate-extension
 */
function wordpoints_admin_iframe_reactivate_extension() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	$extension = ( isset( $_REQUEST['extension'] ) )
		? sanitize_text_field( wp_unslash( $_REQUEST['extension'] ) ) // WPCS: CSRF OK.
		: '';

	check_admin_referer( 'reactivate-extension_' . $extension );

	// First, activate the extension.
	if ( ! isset( $_GET['failure'] ) && ! isset( $_GET['success'] ) ) {

		$nonce = sanitize_key( $_GET['_wpnonce'] ); // @codingStandardsIgnoreLine
		$url   = admin_url( 'update.php?action=wordpoints-reactivate-extension&extension=' . rawurlencode( $extension ) . '&_wpnonce=' . $nonce );

		wp_safe_redirect( $url . '&failure=true' );
		wordpoints_activate_module( $extension, '', ! empty( $_GET['network_wide'] ), true );
		wp_safe_redirect( $url . '&success=true' );

		die();
	}

	// Then we redirect back here to display the success or error message.
	iframe_header( __( 'WordPoints Extension Reactivation', 'wordpoints' ) );

	if ( isset( $_GET['success'] ) ) {

		echo '<p>' . esc_html__( 'Extension reactivated successfully.', 'wordpoints' ) . '</p>';

	} elseif ( isset( $_GET['failure'] ) ) {

		echo '<p>' . esc_html__( 'Extension failed to reactivate due to a fatal error.', 'wordpoints' ) . '</p>';

		// Ensure that Fatal errors are displayed.
		// @codingStandardsIgnoreStart
		error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
		@ini_set( 'display_errors', true );
		// @codingStandardsIgnoreEnd

		$file = wordpoints_extensions_dir() . '/' . $extension;
		WordPoints_Module_Paths::register( $file );
		include $file;
	}

	iframe_footer();
}

/**
 * Handle updating multiple extensions on the extensions administration screen.
 *
 * @since 2.4.0
 */
function wordpoints_admin_screen_update_selected_extensions() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	global $parent_file;

	check_admin_referer( 'bulk-wordpoints-extensions', 'nonce' );

	if ( isset( $_GET['extensions'] ) ) {
		$extensions = explode( ',', sanitize_text_field( wp_unslash( $_GET['extensions'] ) ) );
	} elseif ( isset( $_POST['checked'] ) ) {
		$extensions = array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['checked'] ) );
	} else {
		$extensions = array();
	}

	$url = self_admin_url( 'update.php?action=update-selected-wordpoints-extensions&amp;extensions=' . rawurlencode( implode( ',', $extensions ) ) );
	$url = wp_nonce_url( $url, 'bulk-update-extensions' );

	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Update WordPoints Extensions', 'wordpoints' ); ?></h1>

		<iframe name="wordpoints_extension_updates" src="<?php echo esc_url( $url ); ?>" style="width: 100%; height:100%; min-height:850px;"></iframe>
	</div>

	<?php

	require_once ABSPATH . 'wp-admin/admin-footer.php';

	exit;
}

/**
 * Handle bulk extension update requests from within an iframe.
 *
 * @since 2.4.0
 */
function wordpoints_iframe_update_extensions() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	check_admin_referer( 'bulk-update-extensions' );

	$extensions = array();

	if ( isset( $_GET['extensions'] ) ) {
		$extensions = explode( ',', sanitize_text_field( wp_unslash( $_GET['extensions'] ) ) );
	}

	$extensions = array_map( 'rawurldecode', $extensions );

	wp_enqueue_script( 'jquery' );
	iframe_header();

	$upgrader = new WordPoints_Extension_Upgrader(
		new WordPoints_Extension_Upgrader_Skin_Bulk(
			array(
				'nonce' => 'bulk-update-extensions',
				'url'   => 'update.php?action=update-selected-wordpoints-extensions&amp;extensions=' . rawurlencode( implode( ',', $extensions ) ),
			)
		)
	);

	$upgrader->bulk_upgrade( $extensions );

	iframe_footer();
}

/**
 * Sets up the action hooks to display the extension update rows.
 *
 * @since 2.4.0
 */
function wordpoints_extension_update_rows() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return;
	}

	$updates = wordpoints_get_extension_updates();

	foreach ( $updates->get_new_versions() as $extension_file => $version ) {
		add_action( "wordpoints_after_module_row_{$extension_file}", 'wordpoints_extension_update_row', 10, 2 );
	}
}

/**
 * Displays the update message for an extension in the extensions list table.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_after_module_row_{$extension_file} Added by
 *                   wordpoints_extension_update_rows().
 */
function wordpoints_extension_update_row( $file, $extension_data ) {

	$updates = wordpoints_get_extension_updates();

	if ( ! $updates->has_update( $file ) ) {
		return;
	}

	$server = wordpoints_get_server_for_extension( $extension_data );

	if ( ! $server ) {
		return;
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_UpdatesI ) {
		return;
	}

	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	$new_version = $updates->get_new_version( $file );

	$extension_name = wp_kses(
		$extension_data['name']
		, array(
			'a'       => array( 'href' => array(), 'title' => array() ),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'em'      => array(),
			'strong'  => array(),
		)
	);

	if ( is_network_admin() ) {
		$is_active = is_wordpoints_module_active_for_network( $file );
	} else {
		$is_active = is_wordpoints_module_active( $file );
	}

	?>

	<tr class="plugin-update-tr wordpoints-extension-update-tr <?php echo ( $is_active ) ? 'active' : 'inactive'; ?>">
		<td colspan="<?php echo (int) WordPoints_Admin_List_Table_Extensions::instance()->get_column_count(); ?>" class="plugin-update wordpoints-extension-update colspanchange">
			<div class="update-message notice inline notice-warning notice-alt">
				<p>
					<?php

					printf( // WPCS: XSS OK.
						// translators: Extension name.
						esc_html__( 'There is a new version of %1$s available.', 'wordpoints' )
						, $extension_name
					);

					?>

					<?php if ( $api instanceof WordPoints_Extension_Server_API_Updates_ChangelogI ) : ?>
						<?php

						// translators: 1. Extension name; 2. Version.
						$message = __( 'View %1$s version %2$s details', 'wordpoints' );

						?>
						<a
							href="<?php echo esc_url( admin_url( 'update.php?action=wordpoints-iframe-extension-changelog&extension=' . rawurlencode( $file ) ) ); ?>"
							class="thickbox wordpoints-open-extension-details-modal"
							aria-label="<?php echo esc_attr( sprintf( $message, $extension_name, $new_version ) ); ?>"
						>
							<?php

							printf(
								// translators: Version number.
								esc_html__( 'View version %1$s details', 'wordpoints' )
								, esc_html( $new_version )
							);

							?>
						</a>
					<?php endif; ?>

					<?php if ( current_user_can( 'update_wordpoints_extensions' ) ) : ?>
						<span class="wordpoints-update-action-separator">|</span>
						<?php if ( $api instanceof WordPoints_Extension_Server_API_Updates_InstallableI ) : ?>
							<?php

							// translators: Extension name.
							$message = sprintf( __( 'Update %s now', 'wordpoints' ), $extension_name );

							?>
							<a
								href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=wordpoints-upgrade-extension&extension=' ) . $file, 'upgrade-extension_' . $file ) ); ?>"
								aria-label="<?php echo esc_attr( $message ); ?>"
							>
								<?php esc_html_e( 'Update now', 'wordpoints' ); ?>
							</a>
						<?php else : ?>
							<em>
								<?php esc_html_e( 'Automatic update is unavailable for this extension.', 'wordpoints' ); ?>
							</em>
						<?php endif; ?>
					<?php endif; ?>

					<?php

					/**
					 * Fires at the end of the update message container in each row
					 * of the extensions list table.
					 *
					 * The dynamic portion of the hook name, `$file`, refers to the
					 * path of the extension's primary file relative to the
					 * extensions directory.
					 *
					 * @since 2.4.0
					 *
					 * @param array  $extension_data The extension's data.
					 * @param string $new_version    The new version of the extension.
					 */
					do_action( "wordpoints_in_extension_update_message-{$file}", $extension_data, $new_version );

					?>
				</p>
			</div>
		</td>
	</tr>

	<?php
}

/**
 * Save extension license forms on submit.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_modules_list_table_items
 */
function wordpoints_admin_save_extension_licenses( $extensions ) {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return $extensions;
	}

	foreach ( $extensions['all'] as $extension ) {

		if ( empty( $extension['ID'] ) ) {
			continue;
		}

		$server = wordpoints_get_server_for_extension( $extension );

		if ( ! $server ) {
			continue;
		}

		$api = $server->get_api();

		if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
			continue;
		}

		$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
			$extension['ID']
			, $server
		);

		$url = sanitize_title_with_dashes( $server->get_slug() );

		if ( ! isset( $_POST[ "license_key-{$url}-{$extension['ID']}" ] ) ) {
			continue;
		}

		$license_key = sanitize_key(
			$_POST[ "license_key-{$url}-{$extension['ID']}" ]
		);

		$license = $api->get_extension_license_object( $extension_data, $license_key );

		if (
			isset(
				$_POST[ "activate-license-{$extension['ID']}" ]
				, $_POST[ "wordpoints_activate_license_key-{$extension['ID']}" ]
			)
			&& wordpoints_verify_nonce(
				"wordpoints_activate_license_key-{$extension['ID']}"
				, "wordpoints_activate_license_key-{$extension['ID']}"
				, null
				, 'post'
			)
		) {

			if ( ! $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) {
				continue;
			}

			$result = $license->activate();

			if ( true === $result ) {
				wordpoints_show_admin_message( esc_html__( 'License activated.', 'wordpoints' ) );
				$extension_data->set( 'license_key', $license_key );
				wordpoints_check_for_extension_updates_now();
			} elseif ( is_wp_error( $result ) ) {
				// translators: Error message.
				wordpoints_show_admin_error( sprintf( esc_html__( 'Sorry, there was an error while trying to activate the license: %s', 'wordpoints' ), $result->get_error_message() ) );
			} elseif ( ! $license->is_valid() ) {
				wordpoints_show_admin_error( esc_html__( 'That license key is invalid.', 'wordpoints' ) );
			} elseif ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI && $license->is_expired() ) {
				if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI && $license->is_renewable() ) {
					if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_Renewable_URLI ) {
						wordpoints_show_admin_error(
							esc_html__( 'Sorry, that license key is expired, and must be renewed.', 'wordpoints' )
							. ' <a href="' . esc_url( $license->get_renewal_url() ) . '">' . esc_html__( 'Renew License', 'wordpoints' ) . '</a>'
						);
					} else {
						wordpoints_show_admin_error( esc_html__( 'Sorry, that license key is expired, and must be renewed.', 'wordpoints' ) );
					}
				} else {
					wordpoints_show_admin_error( esc_html__( 'Sorry, that license key is expired.', 'wordpoints' ) );
				}

				$extension_data->set( 'license_key', $license_key );
			} else {
				wordpoints_show_admin_error( esc_html__( 'Sorry, that license key cannot be activated.', 'wordpoints' ) );
			}

		} elseif (
			isset(
				$_POST[ "deactivate-license-{$extension['ID']}" ]
				, $_POST[ "wordpoints_deactivate_license_key-{$extension['ID']}" ]
			)
			&& wordpoints_verify_nonce(
				"wordpoints_deactivate_license_key-{$extension['ID']}"
				, "wordpoints_deactivate_license_key-{$extension['ID']}"
				, null
				, 'post'
			)
		) {

			if ( ! $license instanceof WordPoints_Extension_Server_API_Extension_License_DeactivatableI ) {
				continue;
			}

			$result = $license->deactivate();

			if ( true === $result ) {
				wordpoints_show_admin_message( esc_html__( 'License deactivated.', 'wordpoints' ) );
			} elseif ( is_wp_error( $result ) ) {
				// translators: Error message.
				wordpoints_show_admin_error( sprintf( esc_html__( 'Sorry, there was an error while trying to deactivate the license: %s', 'wordpoints' ), $result->get_error_message() ) );
			} else {
				wordpoints_show_admin_error( esc_html__( 'Sorry, there was an unknown error while trying to deactivate that license key.', 'wordpoints' ) );
			}

		} // End if ( activating license ) elseif ( deactivating license ).

	} // End foreach ( extension ).

	return $extensions;
}

/**
 * Filter the classes for a row in the WordPoints extensions list table.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wordpoints_module_list_row_class
 *
 * @param string $classes        The HTML classes for this extension row.
 * @param string $extension_file The extension file.
 * @param array  $extension_data The extension data.
 *
 * @return string The filtered classes.
 */
function wordpoints_extension_list_row_license_classes( $classes, $extension_file, $extension_data ) {

	// Add license information if this user is allowed to see it.
	if ( empty( $extension_data['ID'] ) || ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return $classes;
	}

	$server = wordpoints_get_server_for_extension( $extension_data );

	if ( ! $server ) {
		return $classes;
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
		return $classes;
	}

	$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
		$extension_data['ID'],
		$server
	);

	if ( ! $api->extension_requires_license( $extension_data ) ) {
		return $classes;
	}

	$classes .= ' wordpoints-extension-has-license';

	$license = $api->get_extension_license_object(
		$extension_data,
		$extension_data->get( 'license_key' )
	);

	if ( $license->is_valid() ) {
		$classes .= ' wordpoints-extension-license-valid';
	} else {
		$classes .= ' wordpoints-extension-license-invalid';
	}

	if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) {
		if ( $license->is_active() ) {
			$classes .= ' wordpoints-extension-license-active';
		} else {
			$classes .= ' wordpoints-extension-license-inactive';
		}
	}

	if (
		$license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI
		&& $license->is_expired()
	) {
		$classes .= ' wordpoints-extension-license-expired';
	}

	return $classes;
}

/**
 * Add the license key rows to the extensions list table.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_after_module_row
 */
function wordpoints_extension_license_row( $extension_file, $extension ) {

	if ( empty( $extension['ID'] ) || ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return;
	}

	$server = wordpoints_get_server_for_extension( $extension );

	if ( ! $server ) {
		return;
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
		return;
	}

	$extension_id = $extension['ID'];

	$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
		$extension_id
		, $server
	);

	if ( ! $api->extension_requires_license( $extension_data ) ) {
		return;
	}

	$license_key = $extension_data->get( 'license_key' );
	$license     = $api->get_extension_license_object( $extension_data, $license_key );
	$server_url  = sanitize_title_with_dashes( $server->get_slug() );

	$notice_type = 'error';

	if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) {
		if ( ! empty( $license_key ) && $license->is_active() ) {
			$notice_type = 'success';
		} elseif ( empty( $license_key ) || $license->is_activatable() ) {
			$notice_type = 'error';
		}
	}

	if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI ) {
		if ( ! empty( $license_key ) && $license->is_expired() ) {
			if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI ) {
				if ( $license->is_renewable() ) {
					$notice_type = 'warning';
				}
			}
		}
	}

	// translators: Extension name.
	$aria_label = __( 'License key for %s', 'wordpoints' );

	?>
	<tr class="wordpoints-extension-license-tr plugin-update-tr <?php echo ( is_wordpoints_module_active( $extension_file ) ) ? 'active' : 'inactive'; ?>">
		<td colspan="<?php echo (int) WordPoints_Admin_List_Table_Extensions::instance()->get_column_count(); ?>" class="colspanchange">
			<div class="wordpoints-license-box notice inline notice-alt notice-<?php echo esc_attr( $notice_type ); ?>">
				<p>
				<label class="description" for="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>">
					<?php esc_html_e( 'License key:', 'wordpoints' ); ?>
				</label>
				<input
					id="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
					name="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
					type="password"
					class="regular-text"
					autocomplete="off"
					value="<?php echo esc_attr( $license_key ); ?>"
				/>
				<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) : ?>
					<?php if ( ! empty( $license_key ) && $license->is_active() ) : ?>
						<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_DeactivatableI && $license->is_deactivatable() ) : ?>
							<?php

							wp_nonce_field( "wordpoints_deactivate_license_key-{$extension_id}", "wordpoints_deactivate_license_key-{$extension_id}" );

							// translators: Extension name.
							$aria_label = __( 'Deactivate License for %s', 'wordpoints' );

							?>
							<input type="submit" name="deactivate-license-<?php echo esc_attr( $extension_id ); ?>" class="button-secondary" value="<?php esc_attr_e( 'Deactivate License', 'wordpoints' ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>" />
						<?php endif; ?>
					<?php elseif ( empty( $license_key ) || $license->is_activatable() ) : ?>
						<?php

						wp_nonce_field( "wordpoints_activate_license_key-{$extension_id}", "wordpoints_activate_license_key-{$extension_id}" );

						// translators: Extension name.
						$aria_label = __( 'Activate License for %s', 'wordpoints' );

						?>
						<input type="submit" name="activate-license-<?php echo esc_attr( $extension_id ); ?>" class="button-secondary" value="<?php esc_attr_e( 'Activate License', 'wordpoints' ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>" />
					<?php endif; ?>
				<?php endif; ?>
					<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI ) : ?>
						<?php if ( ! empty( $license_key ) && $license->is_expired() ) : ?>
							<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI && $license->is_renewable() ) : ?>
								<?php esc_html_e( 'This license key is expired and must be renewed.', 'wordpoints' ); ?>
								<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_Renewable_URLI ) : ?>
									<?php

									// translators: Extension name.
									$aria_label = __( 'Renew License for %s', 'wordpoints' );

									?>
									<a href="<?php echo esc_url( $license->get_renewal_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>"><?php esc_html_e( 'Renew License', 'wordpoints' ); ?></a>
								<?php endif; ?>
							<?php else : ?>
								<?php esc_html_e( 'This license key is expired.', 'wordpoints' ); ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</p>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Displays the changelog for an extension.
 *
 * @since 2.4.0
 */
function wordpoints_iframe_extension_changelog() {

	if ( ! defined( 'IFRAME_REQUEST' ) ) {
		define( 'IFRAME_REQUEST', true );
	}

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	if ( empty( $_GET['extension'] ) ) { // WPCS: CSRF OK.
		wp_die( esc_html__( 'No extension supplied.', 'wordpoints' ), 200 );
	}

	$extension_file = sanitize_text_field( rawurldecode( wp_unslash( $_GET['extension'] ) ) ); // WPCS: CSRF, sanitization OK.

	$extensions = wordpoints_get_modules();

	if ( ! isset( $extensions[ $extension_file ] ) ) {
		wp_die( esc_html__( 'That extension does not exist.', 'wordpoints' ), 200 );
	}

	$server = wordpoints_get_server_for_extension( $extensions[ $extension_file ] );

	if ( ! $server ) {
		wp_die( esc_html__( 'There is no server specified for this extension.', 'wordpoints' ), 200 );
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_Updates_ChangelogI ) {
		wp_die( esc_html__( 'The server for this extension uses an unsupported API.', 'wordpoints' ), 200 );
	}

	$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
		$extensions[ $extension_file ]['ID']
		, $server
	);

	iframe_header();

	echo '<div style="margin-left: 10px;">';
	echo wp_kses(
		$api->get_extension_changelog( $extension_data )
		, 'wordpoints_extension_changelog'
	);
	echo '</div>';

	iframe_footer();
}

/**
 * Supply the list of HTML tags allowed in an extension changelog.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wp_kses_allowed_html
 */
function wordpoints_extension_changelog_allowed_html( $allowed_tags, $context ) {

	if ( 'wordpoints_extension_changelog' !== $context ) {
		return $allowed_tags;
	}

	return array(
		'a'       => array( 'href' => array(), 'title' => array(), 'target' => array() ),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'pre'     => array(),
		'em'      => array(),
		'strong'  => array(),
		'div'     => array( 'class' => array() ),
		'span'    => array( 'class' => array() ),
		'p'       => array(),
		'ul'      => array(),
		'ol'      => array(),
		'li'      => array(),
		'h1'      => array(),
		'h2'      => array(),
		'h3'      => array(),
		'h4'      => array(),
		'h5'      => array(),
		'h6'      => array(),
		'img'     => array( 'src' => array(), 'class' => array(), 'alt' => array() ),
	);
}

/**
 * List the available extension updates on the Updates screen.
 *
 * @since 2.4.0
 */
function wordpoints_list_extension_updates() {

	wp_enqueue_style( 'wordpoints-admin-extension-updates-table' );

	$updates      = wordpoints_get_extension_updates();
	$new_versions = $updates->get_new_versions();

	?>

	<h2><?php esc_html_e( 'WordPoints Extensions', 'wordpoints' ); ?></h2>

	<?php if ( empty( $new_versions ) ) : ?>
		<p><?php esc_html_e( 'Your extensions are all up to date.', 'wordpoints' ); ?></p>
		<?php return; // @codingStandardsIgnoreLine ?>
	<?php endif; ?>

	<p><?php esc_html_e( 'The following extensions have new versions available. Check the ones you want to update and then click &#8220;Update Extensions&#8221;.', 'wordpoints' ); ?></p>

	<form method="post" action="update-core.php?action=do-wordpoints-extension-upgrade" name="upgrade-wordpoints-extensions" class="upgrade">
		<?php wp_nonce_field( 'bulk-wordpoints-extensions', 'nonce' ); ?>

		<p><input id="upgrade-wordpoints-extensions" class="button" type="submit" value="<?php esc_attr_e( 'Update Extensions', 'wordpoints' ); ?>" name="upgrade" /></p>

		<table class="widefat" id="update-wordpoints-extensions-table">
			<thead>
			<tr>
				<td scope="col" class="manage-column check-column">
					<input type="checkbox" id="wordpoints-extensions-select-all" />
				</td>
				<th scope="col" class="manage-column">
					<label for="wordpoints-extensions-select-all"><?php esc_html_e( 'Select All', 'wordpoints' ); ?></label>
				</th>
			</tr>
			</thead>

			<tbody class="wordpoints-extensions">
			<?php foreach ( $new_versions as $extension_file => $new_version ) : ?>
				<?php $extension_data = wordpoints_get_module_data( wordpoints_extensions_dir() . $extension_file ); ?>
				<tr>
					<th scope="row" class="check-column">
						<input id="checkbox_<?php echo esc_attr( sanitize_key( $extension_file ) ); ?>" type="checkbox" name="checked[]" value="<?php echo esc_attr( $extension_file ); ?>" />
						<label for="checkbox_<?php echo esc_attr( sanitize_key( $extension_file ) ); ?>" class="screen-reader-text">
							<?php

							echo esc_html(
								sprintf(
									// translators: Extension name.
									__( 'Select %s', 'wordpoints' )
									, $extension_data['name']
								)
							);

							?>
						</label>
					</th>
					<td>
						<p>
							<strong><?php echo esc_html( $extension_data['name'] ); ?></strong>
							<br />
							<?php

							echo esc_html(
								sprintf(
									// translators: 1. Installed version number; 2. Update version number.
									__( 'You have version %1$s installed. Update to %2$s.', 'wordpoints' )
									, $extension_data['version']
									, $new_version
								)
							);

							?>
							<a href="<?php echo esc_url( self_admin_url( 'update.php?action=wordpoints-iframe-extension-changelog&extension=' . rawurlencode( $extension_file ) . '&TB_iframe=true&width=640&height=662' ) ); ?>" class="thickbox" title="<?php echo esc_attr( $extension_data['name'] ); ?>">
								<?php

								echo esc_html(
									sprintf(
										// translators: Version number.
										__( 'View version %1$s details.', 'wordpoints' )
										, $new_version
									)
								);

								?>
							</a>
						</p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>

			<tfoot>
			<tr>
				<td scope="col" class="manage-column check-column">
					<input type="checkbox" id="wordpoints-extensions-select-all-2" />
				</td>
				<th scope="col" class="manage-column">
					<label for="wordpoints-extensions-select-all-2"><?php esc_html_e( 'Select All', 'wordpoints' ); ?></label>
				</th>
			</tr>
			</tfoot>
		</table>
		<p><input id="upgrade-wordpoints-extensions-2" class="button" type="submit" value="<?php esc_attr_e( 'Update Extensions', 'wordpoints' ); ?>" name="upgrade" /></p>
	</form>

	<?php
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
								, __( 'This appears to be a WordPoints extension archive. Try installing it on the WordPoints extension install screen instead.', 'wordpoints' )
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

	<div class="notice notice-info inline" style="height: 120px; margin-top: 50px;">
		<div style="width:48%;float:left;">
			<h3><?php esc_html_e( 'Like this plugin?', 'wordpoints' ); ?></h3>
			<p>
				<?php

				echo wp_kses(
					sprintf(
						// translators: URL for leaving a review of WordPoints on WordPress.org.
						__( 'If you think WordPoints is great, let everyone know by giving it a <a href="%s">5 star rating</a>.', 'wordpoints' )
						, 'https://wordpress.org/support/view/plugin-reviews/wordpoints?rate=5#postform'
					)
					, array( 'a' => array( 'href' => true ) )
				);

				?>
			</p>
			<p><?php esc_html_e( 'If you don&#8217;t think this plugin deserves 5 stars, please let us know how we can improve it.', 'wordpoints' ); ?></p>
		</div>
		<div style="width:48%;float:left;">
			<h3><?php esc_html_e( 'Need help?', 'wordpoints' ); ?></h3>
			<p>
				<?php

				echo wp_kses(
					sprintf(
						// translators: URL of WordPoints plugin support forums WordPress.org.
						__( 'Post your feature request or support question in the <a href="%s">support forums</a>', 'wordpoints' )
						, 'https://wordpress.org/support/plugin/wordpoints'
					)
					, array( 'a' => array( 'href' => true ) )
				);

				?>
			</p>
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

	if ( current_user_can( 'activate_wordpoints_extensions' ) ) {

		if ( is_network_admin() ) {

			$deactivated_modules = get_site_option( 'wordpoints_breaking_deactivated_modules' );

			if ( is_array( $deactivated_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of extensions.
						__( 'WordPoints has deactivated the following extensions because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $deactivated_modules )
					)
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_breaking_deactivated_modules',
					)
				);
			}

			$incompatible_modules = get_site_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of extensions.
						__( 'WordPoints has deactivated the following network-active extensions because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_incompatible_modules',
					)
				);
			}

		} else {

			$incompatible_modules = get_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of extensions.
						__( 'WordPoints has deactivated the following extensions on this site because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_incompatible_modules',
					)
				);
			}

		} // End if ( is_network_admin() ) else.

	} // End if ( user can activate modules ).

	if (
		current_user_can( 'delete_wordpoints_extensions' )
		&& (
			! isset( $_REQUEST['action'] ) // WPCS: CSRF OK.
			|| 'delete-selected' !== $_REQUEST['action'] // WPCS: CSRF OK.
		)
	) {

		$merged_extensions = get_site_option( 'wordpoints_merged_extensions' );

		if ( is_array( $merged_extensions ) && ! empty( $merged_extensions ) ) {

			foreach ( $merged_extensions as $i => $extension ) {
				if ( true !== wordpoints_validate_module( $extension ) ) {
					unset( $merged_extensions[ $i ] );
				}
			}

			update_site_option( 'wordpoints_merged_extensions', $merged_extensions );

			if ( ! empty( $merged_extensions ) ) {

				$message = sprintf(
					// translators: 1. Plugin version; 2. List of extensions.
					__( 'WordPoints has deactivated the following extensions because their features have now been merged into WordPoints %1$s: %2$s.', 'wordpoints' )
					, WORDPOINTS_VERSION
					, implode( ', ', $merged_extensions )
				);

				$message .= ' ';
				$message .= __( 'You can now safely delete these extensions.', 'wordpoints' );
				$message .= ' ';

				$url = admin_url(
					'admin.php?page=wordpoints_extensions&action=delete-selected'
				);

				foreach ( $merged_extensions as $extension ) {
					$url .= '&checked[]=' . rawurlencode( $extension );
				}

				$url = wp_nonce_url( $url, 'bulk-modules' );

				$message .= '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Delete Unneeded Extensions', 'wordpoints' ) . '</a>';

				wordpoints_show_admin_message(
					$message
					, 'warning'
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_merged_extensions',
					)
				);
			}

		} // End if ( merged extensions ).

	} // End if ( user can delete and aren't deleting ).

	if ( is_wordpoints_network_active() ) {
		wordpoints_admin_show_update_skipped_notices( 'install' );
		wordpoints_admin_show_update_skipped_notices( 'update' );
	}
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

		case 'wordpoints_page_wordpoints_extensions_per_page':
		case 'wordpoints_page_wordpoints_extensions_network_per_page':
		case 'toplevel_page_wordpoints_extensions_per_page':
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
		new WordPoints_Admin_Ajax_Hooks();
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
			<?php echo wp_json_encode( $checkbox_id ); ?>
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

/**
 * Displays notices to admins when extension licenses are invalid, expired, etc.
 *
 * @since 2.4.0
 *
 * @WordPress\action admin_notices
 */
function wordpoints_admin_show_extension_license_notices() {

	// Don't show them on the extensions screen, because they would be shown before
	// license activation notices, etc.
	if ( isset( $_GET['page'] ) && 'wordpoints_extensions' === $_GET['page'] ) { // WPCS: CSRF OK.
		return;
	}

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return;
	}

	foreach ( wordpoints_get_modules() as $extension ) {

		if ( empty( $extension['ID'] ) ) {
			continue;
		}

		$server = wordpoints_get_server_for_extension( $extension );

		if ( ! $server ) {
			continue;
		}

		$api = $server->get_api();

		if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
			continue;
		}

		$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
			$extension['ID']
			, $server
		);

		if ( ! $api->extension_requires_license( $extension_data ) ) {
			continue;
		}

		$license_key = $extension_data->get( 'license_key' );

		if ( empty( $license_key ) ) {

			wordpoints_show_admin_error(
				sprintf(
					// translators: Extension name.
					esc_html__( 'Please fill in your license key for the %s extension for WordPoints, so that you can receive updates.', 'wordpoints' )
					, $extension['name']
				)
				. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
			);

			continue;
		}

		$license = $api->get_extension_license_object( $extension_data, $license_key );

		if ( ! $license->is_valid() ) {

			wordpoints_show_admin_error(
				sprintf(
					// translators: Extension name.
					esc_html__( 'Your license key for the %s extension for WordPoints appears to be invalid. Please enter a valid license key so that you can receive updates.', 'wordpoints' )
					, $extension['name']
				)
				. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
			);

		} elseif ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI && $license->is_expired() ) {

			if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI && $license->is_renewable() ) {

				if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_Renewable_URLI ) {

					wordpoints_show_admin_error(
						sprintf(
							// translators: Extension name.
							esc_html__( 'Your license key for the %s extension for WordPoints is expired. Please renew your license key so that you can receive updates.', 'wordpoints' )
							, $extension['name']
						)
						. ' <a href="' . esc_url( $license->get_renewal_url() ) . '">' . esc_html__( 'Renew License', 'wordpoints' ) . '</a>'
					);

				} else {

					wordpoints_show_admin_error(
						sprintf(
							// translators: Extension name.
							esc_html__( 'Your license key for the %s extension for WordPoints is expired. Please renew your license key so that you can receive updates.', 'wordpoints' )
							, $extension['name']
						)
						. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
					);
				}

			} else {

				wordpoints_show_admin_error(
					sprintf(
						// translators: Extension name.
						esc_html__( 'Your license key for the %s extension for WordPoints is expired. Please enter a valid license key so that you can receive updates.', 'wordpoints' )
						, $extension['name']
					)
					. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
				);
			}

		} elseif ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI && $license->is_activatable() && ! $license->is_active() ) {

			$extension_id = $extension['ID'];
			$server_url   = sanitize_title_with_dashes( $server->get_slug() );

			// translators: Extension name.
			$aria_label = __( 'Activate License for %s WordPoints Extension', 'wordpoints' );

			?>
			<div class="notice notice-error">
				<p>
					<?php

					echo esc_html(
						sprintf(
							// translators: Extension name.
							__( 'Your license key for the %s extension for WordPoints is not active. Please activate it so that you can receive updates.', 'wordpoints' )
							, $extension['name']
						)
					);

					?>
				</p>
				<form method="post" action="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ); ?>">
					<input
						id="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
						name="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
						type="hidden"
						class="regular-text"
						autocomplete="off"
						value="<?php echo esc_attr( $license_key ); ?>"
					/>
					<?php wp_nonce_field( "wordpoints_activate_license_key-{$extension_id}", "wordpoints_activate_license_key-{$extension_id}" ); ?>
					<p>
						<input
							type="submit"
							name="activate-license-<?php echo esc_attr( $extension_id ); ?>"
							class="button-secondary"
							value="<?php esc_attr_e( 'Activate License', 'wordpoints' ); ?>"
							aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension_data['name'] ) ); ?>"
						/>
					</p>
				</form>
			</div>
			<?php
		}
	}
}

/**
 * Shows the admin a notice if the update/install for an installable was skipped.
 *
 * @since 2.4.0
 *
 * @param string $notice_type The type of notices to display, 'update', or 'install'.
 */
function wordpoints_admin_show_update_skipped_notices( $notice_type = 'update' ) {

	$all_skipped = array_filter(
		wordpoints_get_array_option( "wordpoints_network_{$notice_type}_skipped", 'site' )
	);

	if ( empty( $all_skipped ) ) {
		return;
	}

	$messages = array();

	if ( 'install' === $notice_type ) {
		// translators: 1. Extension/plugin name; 2. "extension", "plugin", or "component".
		$message_template = __( 'WordPoints detected a large network and has skipped part of the installation process for &#8220;%1$s&#8221; %2$s.', 'wordpoints' );
	} else {
		// translators: 1. Extension/plugin name; 2. "extension", "plugin", or "component"; 3. Version number.
		$message_template = __( 'WordPoints detected a large network and has skipped part of the update process for &#8220;%1$s&#8221; %2$s for version %3$s (and possibly later versions).', 'wordpoints' );
	}

	foreach ( $all_skipped as $type => $skipped ) {

		switch ( $type ) {

			case 'module':
				$capability = 'wordpoints_manage_network_modules';
			break;

			default:
				$capability = 'manage_network_plugins';
		}

		if ( ! current_user_can( $capability ) ) {
			continue;
		}

		switch ( $type ) {

			case 'module':
				$type_name = __( '(extension)', 'wordpoints' );
			break;

			case 'component':
				$type_name = __( '(component)', 'wordpoints' );
			break;

			default:
				$type_name = __( '(plugin)', 'wordpoints' );
		}

		foreach ( $skipped as $slug => $version ) {

			// Normally we might have used the installable's fancy name instead
			// of the slug, but this is such an edge case to start with that I
			// decided not to. Also of note: the version is only used in the
			// update message.
			$messages[] = esc_html(
				sprintf(
					$message_template
					, $slug
					, $type_name
					, $version
				)
			);
		}

	} // End foreach ( $all_skipped ).

	if ( ! empty( $messages ) ) {

		$message  = '<p>' . implode( '</p><p>', $messages ) . '</p>';
		$message .= '<p>' . esc_html__( 'The rest of the process needs to be completed manually. If this has not been done already, some features may not function properly.', 'wordpoints' );
		$message .= ' <a href="https://wordpoints.org/user-guide/multisite/">' . esc_html__( 'Learn more.', 'wordpoints' ) . '</a></p>';

		$args = array(
			'dismissible' => true,
			'option'      => "wordpoints_network_{$notice_type}_skipped",
		);

		wordpoints_show_admin_error( $message, $args );
	}
}

// EOF
