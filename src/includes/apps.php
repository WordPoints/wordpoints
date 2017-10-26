<?php

/**
 * Apps functions.
 *
 * @package wordpoints
 * @since 2.1.0
 */

//
// Generic Apps functions.
//

/**
 * Get the main WordPoints app.
 *
 * @since 2.1.0
 *
 * @return WordPoints_App The main WordPoints app.
 */
function wordpoints_apps() {

	if ( ! isset( WordPoints_App::$main ) ) {
		WordPoints_App::$main = new WordPoints_App( 'apps' );
	}

	return WordPoints_App::$main;
}

/**
 * Register sub apps when the apps app is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app-apps
 *
 * @param WordPoints_App $app The main apps app.
 */
function wordpoints_apps_init( $app ) {

	$apps = $app->sub_apps();

	$apps->register( 'hooks', 'WordPoints_Hooks' );
	$apps->register( 'entities', 'WordPoints_App_Registry' );
	$apps->register( 'data_types', 'WordPoints_Class_Registry' );
	$apps->register( 'installables', 'WordPoints_Installables_App' );
	$apps->register( 'components', 'WordPoints_App' );
	$apps->register( 'extensions', 'WordPoints_App' );
	$apps->register( 'modules', 'WordPoints_App' ); // Deprecated.
	$apps->register( 'extension_server_apis', 'WordPoints_Class_Registry' );
}

/**
 * Parse a dynamic slug into the dynamic and generic components.
 *
 * In the hooks and entities APIs, we have a convention of using dynamic slugs when
 * certain elements are registered dynamically. Such slugs are of the following
 * format: <generic part>\<dynamic part>. In other words, the generic and dynamic
 * parts are separated by a backslash. This function provides a canonical method of
 * parsing a slug into its constituent parts.
 *
 * @since 2.1.0
 *
 * @param string $slug A slug (for an entity or hook event, for example).
 *
 * @return array The slug parsed into the 'generic' and 'dynamic' portions. If the
 *               slug is not dynamic, the value of each of those keys will be false.
 */
function wordpoints_parse_dynamic_slug( $slug ) {

	$parsed = array( 'dynamic' => false, 'generic' => false );

	$parts = explode( '\\', $slug, 2 );

	if ( isset( $parts[1] ) ) {
		$parsed['dynamic'] = $parts[1];
		$parsed['generic'] = $parts[0];
	}

	return $parsed;
}

/**
 * Get the slugs of the post types to automatically integrate with.
 *
 * @since 2.2.0
 *
 * @return string[] The slugs of the post types to automatically integrate with.
 */
function wordpoints_get_post_types_for_auto_integration() {

	$post_types = get_post_types( array( 'public' => true ) );

	/**
	 * Filter which post types to automatically integrate with.
	 *
	 * @since 2.2.0
	 *
	 * @param string[] $post_types The post type slugs ("names").
	 */
	return apply_filters( 'wordpoints_post_types_for_auto_integration', $post_types );
}

/**
 * Get the slugs of the taxonomies to automatically integrate with.
 *
 * @since 2.4.0
 *
 * @return string[] The slugs of the taxonomies to automatically integrate with.
 */
function wordpoints_get_taxonomies_for_auto_integration() {

	$taxonomies = get_taxonomies( array( 'public' => true ) );

	/**
	 * Filter which taxonomies to automatically integrate with.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $taxonomies The taxonomy slugs.
	 */
	return apply_filters( 'wordpoints_taxonomies_for_auto_integration', $taxonomies );
}

//
// Components API
//

/**
 * Gets the app for a component.
 *
 * @since 2.2.0
 *
 * @param string $slug The slug of the component.
 *
 * @return false|WordPoints_App The component's app.
 */
function wordpoints_component( $slug ) {

	if ( ! isset( WordPoints_App::$main ) ) {
		wordpoints_apps();
	}

	return WordPoints_App::$main->get_sub_app( 'components' )->get_sub_app( $slug );
}

//
// Extensions API
//

/**
 * Gets the app for an extension.
 *
 * @since 2.4.0
 *
 * @param string $slug The slug of the extension.
 *
 * @return false|WordPoints_App The extension's app.
 */
function wordpoints_extension( $slug ) {

	if ( ! isset( WordPoints_App::$main ) ) {
		wordpoints_apps();
	}

	return WordPoints_App::$main->get_sub_app( 'extensions' )->get_sub_app( $slug );
}

/**
 * Gets the app for a module.
 *
 * @since 2.2.0
 * @deprecated 2.4.0 Use wordpoints_extensions() instead.
 *
 * @param string $slug The slug of the module.
 *
 * @return false|WordPoints_App The module's app.
 */
function wordpoints_module( $slug ) {

	_deprecated_function( __FUNCTION__, '2.4.0', 'wordpoints_extension' );

	if ( ! isset( WordPoints_App::$main ) ) {
		wordpoints_apps();
	}

	return WordPoints_App::$main->get_sub_app( 'modules' )->get_sub_app( $slug );
}

//
// Extension Server APIs API
//

/**
 * Register extension server APIs when the Extension Server APIs app is initialized.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_init_app_registry-apps-extension_server_apis
 *
 * @param WordPoints_Class_RegistryI $server_apis The extension server APIs app.
 */
function wordpoints_extension_server_apis_init( $server_apis ) {

	$server_apis->register( 'edd_software_licensing', 'WordPoints_Extension_Server_API_EDD_SL' );
	$server_apis->register( 'edd_software_licensing_free', 'WordPoints_Extension_Server_API_EDD_SL_Free' );
}

//
// Entities API
//

/**
 * Get the entities app.
 *
 * @since 2.1.0
 *
 * @return WordPoints_App_Registry The entities app.
 */
function wordpoints_entities() {

	if ( ! isset( WordPoints_App::$main ) ) {
		wordpoints_apps();
	}

	return WordPoints_App::$main->get_sub_app( 'entities' );
}

/**
 * Register sub-apps when the Entities app is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app-entities
 *
 * @param WordPoints_App_Registry $entities The entities app.
 */
function wordpoints_entities_app_init( $entities ) {

	$sub_apps = $entities->sub_apps();
	$sub_apps->register( 'children', 'WordPoints_Class_Registry_Children' );
	$sub_apps->register( 'contexts', 'WordPoints_Entity_Contexts' );
	$sub_apps->register( 'restrictions', 'WordPoints_Entity_Restrictions' );
}

/**
 * Register entity contexts when the registry is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app_registry-entities-contexts
 *
 * @param WordPoints_Class_RegistryI $contexts The entity context registry.
 */
function wordpoints_entity_contexts_init( $contexts ) {

	$contexts->register( 'network', 'WordPoints_Entity_Context_Network' );
	$contexts->register( 'site', 'WordPoints_Entity_Context_Site' );
}

/**
 * Register sub-apps when the Entity Restrictions app is initialized.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app-entities-restrictions
 *
 * @param WordPoints_App $restrictions The entity restrictions app.
 */
function wordpoints_entities_restrictions_app_init( $restrictions ) {

	$sub_apps = $restrictions->sub_apps();
	$sub_apps->register( 'know', 'WordPoints_Class_Registry_Deep_Multilevel_Slugless' );
	$sub_apps->register( 'view', 'WordPoints_Class_Registry_Deep_Multilevel_Slugless' );
}

/**
 * Register entity restrictions when the "know" restrictions registry is initialized.
 *
 * These are entities that are totally restricted, so that when the restriction
 * applies, the user is not even allowed to know that such an object exists.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app_registry-entities-restrictions-know
 *
 * @param WordPoints_Class_Registry_Deep_Multilevel $restrictions The restrictions
 *                                                                registry.
 */
function wordpoints_entity_restrictions_know_init( $restrictions ) {

	$restrictions->register(
		'unregistered'
		, array()
		, 'WordPoints_Entity_Restriction_Unregistered'
	);

	$restrictions->register(
		'legacy'
		, array()
		, 'WordPoints_Entity_Restriction_Legacy'
	);

	foreach ( wordpoints_get_post_types_for_entities() as $slug ) {

		$restrictions->register(
			'status_nonpublic'
			, array( "post\\$slug" )
			, 'WordPoints_Entity_Restriction_Post_Status_Nonpublic'
		);

		$restrictions->register(
			'post_status_nonpublic'
			, array( "comment\\$slug" )
			, 'WordPoints_Entity_Restriction_Comment_Post_Status_Nonpublic'
		);
	}
}

/**
 * Register entity restrictions when the View restrictions registry is initialized.
 *
 * @since 2.2.0
 *
 * @WordPress\action wordpoints_init_app_registry-entities-restrictions-view
 *
 * @param WordPoints_Class_Registry_Deep_Multilevel $restrictions The restrictions
 *                                                                registry.
 */
function wordpoints_entity_restrictions_view_init( $restrictions ) {

	foreach ( wordpoints_get_post_types_for_entities() as $slug ) {

		$restrictions->register(
			'password_protected'
			, array( "post\\$slug", 'content' )
			, 'WordPoints_Entity_Restriction_View_Post_Content_Password_Protected'
		);
	}
}

/**
 * Register entities when the entities app is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app_registry-apps-entities
 *
 * @param WordPoints_App_Registry $entities The entities app.
 */
function wordpoints_entities_init( $entities ) {

	$children = $entities->get_sub_app( 'children' );

	$entities->register( 'user', 'WordPoints_Entity_User' );
	$children->register( 'user', 'roles', 'WordPoints_Entity_User_Roles' );

	$entities->register( 'user_role', 'WordPoints_Entity_User_Role' );

	foreach ( wordpoints_get_post_types_for_entities() as $slug ) {
		wordpoints_register_post_type_entities( $slug );
	}

	// Also register entities for any post types that are late to the party.
	add_action( 'registered_post_type', 'wordpoints_register_post_type_entities' );

	foreach ( wordpoints_get_taxonomies_for_entities() as $slug ) {
		wordpoints_register_taxonomy_entities( $slug );
	}

	// Also register entities for any taxonomies that are late to the party.
	add_action( 'registered_taxonomy', 'wordpoints_register_taxonomy_entities' );
}

/**
 * Get the slugs of the post types to register entities for.
 *
 * @since 2.2.0
 *
 * @return string[] The slugs of the post types to register entities for.
 */
function wordpoints_get_post_types_for_entities() {

	$post_types = wordpoints_get_post_types_for_auto_integration();

	/**
	 * Filter which post types to register entities for.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $post_types The post type slugs ("names").
	 */
	return apply_filters( 'wordpoints_register_entities_for_post_types', $post_types );
}

/**
 * Register the entities for a post type.
 *
 * @since 2.1.0
 *
 * @WordPress\action registered_post_type By {@see wordpoints_entities_init()}.
 *
 * @param string $slug The slug of the post type.
 */
function wordpoints_register_post_type_entities( $slug ) {

	$entities = wordpoints_entities();
	$children = $entities->get_sub_app( 'children' );

	$entities->register( "post\\{$slug}", 'WordPoints_Entity_Post' );
	$children->register( "post\\{$slug}", 'author', 'WordPoints_Entity_Post_Author' );
	$children->register( "post\\{$slug}", 'date_modified', 'WordPoints_Entity_Post_Date_Modified' );
	$children->register( "post\\{$slug}", 'date_published', 'WordPoints_Entity_Post_Date_Published' );

	if ( is_post_type_hierarchical( $slug ) ) {
		$children->register( "post\\{$slug}", 'parent', 'WordPoints_Entity_Post_Parent' );
	}

	$supports = get_all_post_type_supports( $slug );

	if ( isset( $supports['title'] ) ) {
		$children->register( "post\\{$slug}", 'title', 'WordPoints_Entity_Post_Title' );
	}

	if ( isset( $supports['editor'] ) ) {
		$children->register( "post\\{$slug}", 'content', 'WordPoints_Entity_Post_Content' );
	}

	if ( isset( $supports['excerpt'] ) ) {
		$children->register( "post\\{$slug}", 'excerpt', 'WordPoints_Entity_Post_Excerpt' );
	}

	if ( isset( $supports['comments'] ) ) {

		$children->register( "post\\{$slug}", 'comment_count', 'WordPoints_Entity_Post_Comment_Count' );

		$entities->register( "comment\\{$slug}", 'WordPoints_Entity_Comment' );
		$children->register( "comment\\{$slug}", "post\\{$slug}", 'WordPoints_Entity_Comment_Post' );
		$children->register( "comment\\{$slug}", 'author', 'WordPoints_Entity_Comment_Author' );
		$children->register( "comment\\{$slug}", 'content', 'WordPoints_Entity_Comment_Content' );
		$children->register( "comment\\{$slug}", 'date', 'WordPoints_Entity_Comment_Date' );
		$children->register( "comment\\{$slug}", 'parent', 'WordPoints_Entity_Comment_Parent' );
	}

	/**
	 * Fired when registering the entities for a post type.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug ("name") of the post type.
	 */
	do_action( 'wordpoints_register_post_type_entities', $slug );
}

/**
 * Get the slugs of the taxonomies to register entities for.
 *
 * @since 2.4.0
 *
 * @return string[] The slugs of the taxonomies to register entities for.
 */
function wordpoints_get_taxonomies_for_entities() {

	$taxonomies = wordpoints_get_taxonomies_for_auto_integration();

	/**
	 * Filter which taxonomies to register entities for.
	 *
	 * @since 2.4.0
	 *
	 * @param string[] $taxonomies The taxonomy slugs ("names").
	 */
	return apply_filters( 'wordpoints_register_entities_for_taxonomies', $taxonomies );
}

/**
 * Register the entities for a taxonomy.
 *
 * @since 2.4.0
 *
 * @WordPress\action registered_taxonomy By {@see wordpoints_entities_init()}.
 *
 * @param string $slug The slug of the taxonomy.
 */
function wordpoints_register_taxonomy_entities( $slug ) {

	$entities = wordpoints_entities();
	$children = $entities->get_sub_app( 'children' );

	$entities->register( "term\\{$slug}", 'WordPoints_Entity_Term' );
	$children->register( "term\\{$slug}", 'count', 'WordPoints_Entity_Term_Count' );
	$children->register( "term\\{$slug}", 'description', 'WordPoints_Entity_Term_Description' );
	$children->register( "term\\{$slug}", 'name', 'WordPoints_Entity_Term_Name' );

	if ( is_taxonomy_hierarchical( $slug ) ) {
		$children->register( "term\\{$slug}", 'parent', 'WordPoints_Entity_Term_Parent' );
	}

	/**
	 * Fires when registering the entities for a taxonomy.
	 *
	 * @since 2.4.0
	 *
	 * @param string $slug The slug of the taxonomy.
	 */
	do_action( 'wordpoints_register_taxonomy_entities', $slug );
}

/**
 * Register the taxonomy entities for a post type.
 *
 * @since 2.4.0
 *
 * @WordPoints\action wordpoints_register_post_type_entities
 *
 * @param string $slug The slug of the post type.
 */
function wordpoints_register_post_type_taxonomy_entities( $slug ) {

	$children = wordpoints_entities()->get_sub_app( 'children' );

	$object_taxonomies = get_object_taxonomies( $slug );
	$object_taxonomies = array_intersect(
		$object_taxonomies
		, wordpoints_get_taxonomies_for_entities()
	);

	foreach ( $object_taxonomies as $taxonomy_slug ) {
		$children->register( "post\\{$slug}", "terms\\{$taxonomy_slug}", 'WordPoints_Entity_Post_Terms' );
	}
}

/**
 * Check whether a user can view an entity.
 *
 * @since 2.1.0
 * @since 2.2.0 Now uses the new entity restrictions API.
 *
 * @param int    $user_id     The user ID.
 * @param string $entity_slug The slug of the entity type.
 * @param mixed  $entity_id   The entity ID.
 *
 * @return bool Whether the user can view this entity.
 */
function wordpoints_entity_user_can_view( $user_id, $entity_slug, $entity_id ) {

	/** @var WordPoints_Entity_Restrictions $restrictions */
	$restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );
	$restriction  = $restrictions->get( $entity_id, $entity_slug, 'view' );
	return $restriction->user_can( $user_id );
}

/**
 * Get the GUID of the current entity context.
 *
 * Most entities exist only in the context of a specific site on the network (in
 * multisite—when not on multisite they are just global to the install). An
 * example of this would be a Post: a post on one site with the ID 5 is different
 * than a post with that same ID on another site. To get the ID of such an entity's
 * context, you would pass 'site' as the value of the `$slug` arg, and the IDs for
 * both the 'site' and 'network' contexts would be returned.
 *
 * Some entities exist in the context of the network itself, not any particular
 * site. You can get the ID for the context of such an entity by passing 'network'
 * as the value of `$slug`.
 *
 * Still other entities are global to the install, existing across all networks even
 * on a multi-network installation. An example of this would be a User: the user with
 * the ID 3 is the same on every site on the network, and every network in the
 * install.
 *
 * Some entities might exist in other contexts entirely.
 *
 * The context IDs are returned in ascending hierarchical order.
 *
 * @since 2.1.0
 *
 * @param string $slug The slug of the context you want to get the current GUID of.
 *
 * @return array|false The ID of the context you passed in and the IDs of its parent
 *                     contexts, indexed by context slug, or false if any of the
 *                     contexts isn't current.
 */
function wordpoints_entities_get_current_context_id( $slug ) {

	$current_context = array();

	/** @var WordPoints_Class_Registry $contexts */
	$contexts = wordpoints_entities()->get_sub_app( 'contexts' );

	while ( $slug ) {

		$context = $contexts->get( $slug );

		if ( ! $context instanceof WordPoints_Entity_Context ) {
			return false;
		}

		$id = $context->get_current_id();

		if ( false === $id ) {
			return false;
		}

		$current_context[ $slug ] = $id;

		$slug = $context->get_parent_slug();
	}

	return $current_context;
}

/**
 * Checks if we are in network context.
 *
 * There are times on multisite when we are in the context of the network as a whole,
 * and not in the context of any particular site. This includes the network admin
 * screens, and Ajax requests that originate from them.
 *
 * @since 2.1.0
 *
 * @return bool Whether we are in network context.
 */
function wordpoints_is_network_context() {

	if ( ! is_multisite() ) {
		return false;
	}

	if ( is_network_admin() ) {
		return true;
	}

	// See https://core.trac.wordpress.org/ticket/22589
	if (
		defined( 'DOING_AJAX' )
		&& DOING_AJAX
		&& isset( $_SERVER['HTTP_REFERER'] )
		&& preg_match(
			'#^' . preg_quote( network_admin_url(), '#' ) . '#i'
			, esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) )
		)
	) {
		return true;
	}

	/**
	 * Filter whether we are currently in network context.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $in_network_context Whether we are in network context.
	 */
	return apply_filters( 'wordpoints_is_network_context', false );
}

//
// Data types API.
//

/**
 * Register the data types with the data types registry is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app_registry-apps-data_types
 *
 * @param WordPoints_Class_RegistryI $data_types The data types registry.
 */
function wordpoints_data_types_init( $data_types ) {

	$data_types->register( 'decimal_number', 'WordPoints_Data_Type_Decimal_Number' );
	$data_types->register( 'integer', 'WordPoints_Data_Type_Integer' );
	$data_types->register( 'text', 'WordPoints_Data_Type_Text' );
}

//
// Installables API.
//

/**
 * Runs any update routines for installables that may need to be run.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_extensions_loaded 5 Before most extension code runs.
 */
function wordpoints_installables_maybe_update() {

	/** @var WordPoints_Installables_App $installables */
	$installables = wordpoints_apps()->get_sub_app( 'installables' );
	$installables->maybe_update();
}

/**
 * Runs the install routines for any network-active installables for a new site.
 *
 * @since 2.4.0
 *
 * @WordPress\action wpmu_new_blog
 *
 * @param int $site_id The ID of the new site.
 */
function wordpoints_installables_install_on_new_site( $site_id ) {

	/** @var WordPoints_Installables_App $installables */
	$installables = wordpoints_apps()->get_sub_app( 'installables' );
	$installables->install_on_new_site( $site_id );
}

// EOF
