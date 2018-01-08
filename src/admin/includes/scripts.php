<?php

/**
 * Admin-side script- and style-related functions.
 *
 * @package WordPoints\Admin
 * @since   2.5.0
 */

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
		'wordpoints-admin-general'
		, "{$assets_url}/css/admin{$suffix}.css"
		, array()
		, WORDPOINTS_VERSION
	);

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

	$rtl_styles = array(
		'wordpoints-admin-general',
		'wordpoints-admin-extensions-list-table',
		'wordpoints-hooks-admin',
	);

	foreach ( $rtl_styles as $handle ) {

		$styles->add_data( $handle, 'rtl', 'replace' );

		if ( $suffix ) {
			$styles->add_data( $handle, 'suffix', $suffix );
		}
	}

	// JS

	wp_register_script(
		'wordpoints-admin-utils'
		, "{$assets_url}/js/utils{$suffix}.js"
		, array( 'wp-util' )
		, WORDPOINTS_VERSION
	);

	wp_register_script(
		'wordpoints-admin-dismiss-notice'
		, "{$assets_url}/js/dismiss-notice{$suffix}.js"
		, array( 'jquery', 'wp-util' )
		, WORDPOINTS_VERSION
	);

	wp_register_script(
		'wordpoints-hooks-models'
		, "{$assets_url}/js/hooks/models{$manifested_suffix}.js"
		, array( 'backbone', 'jquery-ui-dialog', 'wordpoints-admin-utils' )
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
				<button type="button" class="edit button">
					' . esc_html__( 'Edit', 'wordpoints' ) . '
				</button>
				<button type="button" class="close button">
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
						<div class="spinner-overlay hidden">
							<span class="spinner is-active"></span>
						</div>
						<div class="action-buttons">
							<button type="button" class="save button button-primary" disabled>
								' . esc_html__( 'Save', 'wordpoints' ) . '
							</button>
							<button type="button" class="cancel button">
								' . esc_html__( 'Cancel', 'wordpoints' ) . '
							</button>
							<button type="button" class="close button">
								' . esc_html__( 'Close', 'wordpoints' ) . '
							</button>
							<button type="button" class="delete button">
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
					<button type="button" class="add-new button wordpoints-hooks-icon-button">
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
					<button type="button" class="confirm-add-new button" disabled aria-label="' . esc_attr__( 'Add Condition', 'wordpoints' ) . '">
						' . esc_html_x( 'Add', 'reaction condition', 'wordpoints' ) . '
					</button>
					<button type="button" class="cancel-add-new button" aria-label="' . esc_attr__( 'Cancel Adding New Condition', 'wordpoints' ) . '">
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
					<button type="button" class="delete button wordpoints-hooks-icon-button">
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
 * @since 2.5.0 The $reactions_data and $events_data args were added.
 *
 * @param array $reactions_data Reaction data to send to the script.
 * @param array $events_data    Event data to send to the script.
 */
function wordpoints_hooks_ui_setup_script_data(
	array $reactions_data = array(),
	array $events_data = array()
) {

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
		'reactions'          => (object) $reactions_data,
		'events'             => (object) $events_data,
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

// EOF
