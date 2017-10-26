<?php

/**
 * Class for WordPoints apps.
 *
 * @package WordPoints\Apps
 * @since 2.1.0
 */

/**
 * An app for WordPoints.
 *
 * Apps are self-contained APIs that can include sub-apps.
 *
 * The sub-apps are not required to be instances of WordPoints_App themselves, they
 * can be any sort of object.
 *
 * @since 2.1.0
 */
class WordPoints_App {

	/**
	 * The main app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_App
	 */
	public static $main;

	/**
	 * The slug of this app.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The full slug of this app, prefixed with the slug of the parent app.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $full_slug;

	/**
	 * A registry for child apps.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Class_Registry_Persistent
	 */
	protected $sub_apps;

	/**
	 * The parent of this app, if this is a sub-app.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_App
	 */
	protected $parent;

	/**
	 * Whether to skip calling an action when each registry is initialized.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $silent = false;

	/**
	 * Keeps track of which registries have been initialized.
	 *
	 * @since 2.1.0
	 *
	 * @var bool[]
	 */
	protected $did_init = array();

	/**
	 * Whether properties are allowed to be set.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected $allow_set = false;

	/**
	 * @since 2.1.0
	 */
	public function __construct( $slug, $parent = null ) {

		$this->slug      = $slug;
		$this->full_slug = $slug;

		if ( $parent instanceof WordPoints_App ) {
			$this->parent = $parent;

			if ( 'apps' !== $this->parent->full_slug ) {
				$this->full_slug = $this->parent->full_slug . '-' . $this->full_slug;
			}
		}

		$this->sub_apps = new WordPoints_Class_Registry_Persistent();

		$this->init();
	}

	/**
	 * Get a sub app.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the sub-app to get.
	 *
	 * @return null|object|WordPoints_App|WordPoints_Class_RegistryI|WordPoints_Class_Registry_ChildrenI|WordPoints_Class_Registry_DeepI
	 *         The sub app, or null if not found.
	 */
	public function get_sub_app( $slug ) {

		$sub_app = $this->sub_apps->get( $slug, array( $this ) );

		if ( ! $sub_app ) {
			return null;
		}

		if (
			empty( $this->did_init[ $slug ] )
			&& ! self::$main->silent
			&& $this->should_do_registry_init( $sub_app )
		) {

			$this->did_init[ $slug ] = true;

			/**
			 * Initialization of an app registry.
			 *
			 * @since 2.1.0
			 *
			 * @param WordPoints_Class_RegistryI|WordPoints_Class_Registry_ChildrenI
			 *        $registry The registry object.
			 */
			do_action( "wordpoints_init_app_registry-{$this->full_slug}-{$slug}", $sub_app );
		}

		return $sub_app;
	}

	/**
	 * Get the sub apps registry.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Class_Registry_Persistent The sub apps registry.
	 */
	public function sub_apps() {
		return $this->sub_apps;
	}

	/**
	 * Check whether to call the init action for a registry sub-app.
	 *
	 * @since 2.1.0
	 *
	 * @param object $registry The sub-app object.
	 *
	 * @return bool Whether to call the init action or not.
	 */
	protected function should_do_registry_init( $registry ) {
		return (
			$registry instanceof WordPoints_Class_RegistryI
				|| $registry instanceof WordPoints_Class_Registry_ChildrenI
				|| $registry instanceof WordPoints_Class_Registry_DeepI
		);
	}

	/**
	 * Initialize this app.
	 *
	 * @since 2.1.0
	 */
	protected function init() {

		/**
		 * WordPoints app initialized.
		 *
		 * The dynamic portion of the action is the slug of the app being
		 * initialized.
		 *
		 * @since 2.1.0
		 *
		 * @param WordPoints_App $app The app object.
		 */
		do_action( "wordpoints_init_app-{$this->full_slug}", $this );
	}
}

// EOF
