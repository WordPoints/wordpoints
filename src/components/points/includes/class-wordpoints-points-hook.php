<?php

/**
 * The points hook class.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.0.0
 */

/**
 * Points hook class.
 *
 * This is an abstract class that is extended to create different typs of points
 * hooks. It has several methods which may be optionally extended. These methods
 * display the form for an instance of a hook [form()], update the instance's
 * settings when the form is submitted [update()], generate a description of the hook
 * [generate_description()], and retrieve the number of points an instance awards
 * [get_points()], respectively. These methods each have a default behaviour, so
 * extending each them is optional. (This changed in version 1.5.0, as previously the
 * form() and update() methods were abstract, and so had to be extended.)
 *
 * The rest of the methods are final, and therefore  cannot be extended. A few of
 * these are noteworthy, as you must use them for your hook to work. The first of
 * these, and the only method which it is absolutely required for every hook to call,
 * is the init() method, which your hook must call in it's constructor to initialize
 * the hook type. If you extend the form() method, you will also need to use the
 * methods to get the correct name [get_form_name() or the_form_name()] and id
 * [get_form_id() or the_form_id()] attribute values for your form elements. There is
 * one other method, get_instances(), which you will need to use to get a list of the
 * instances of the hook, when you are awarding points for example.
 *
 * The rest of the methods do other interesting acrobatics, most of which you don't
 * need to worry about.
 *
 * The entire Points Hooks API is based heavily on the Widgets API in WordPress Core.
 * The main difference of course, is that points hooks aren't displayed in the side-
 * bar of your site.
 *
 * @since 1.0.0
 */
abstract class WordPoints_Points_Hook {

	//
	// Private Vars.
	//

	/**
	 * Root id for all hooks of this type.
	 *
	 * @since 1.0.0
	 *
	 * @type string $id_base
	 */
	private $id_base;

	/**
	 * Name for this hook type.
	 *
	 * @since 1.0.0
	 *
	 * @type string $name
	 */
	private $name;

	/**
	 * The name for this hooks option field.
	 *
	 * @since 1.0.0
	 *
	 * @type string $option_name
	 */
	private $option_name;

	/**
	 * Option array.
	 *
	 * @since 1.0.0
	 *
	 * @type array $options
	 */
	private $options;

	/**
	 * Unique ID number of the current instance.
	 *
	 * The number will be false when none is set. When set, it will usually be an
	 * integer, or numeric string. For network hooks, it is prefixed with 'network_'.
	 *
	 * @since 1.0.0
	 *
	 * @type int|string|false $number
	 */
	private $number = false;

	//
	// Public Non-final Methods.
	//

	/**
	 * Get the number of points for a particular instance.
	 *
	 * @since 1.4.0
	 *
	 * @param int|string $number The hook number or ID to get the points for.
	 *
	 * @return int|false The number of points for this instance, or false.
	 */
	public function get_points( $number = null ) {

		if ( isset( $number ) ) {
			$number = $this->get_number_by_id( $number );
		} else {
			$number = $this->number;
		}

		$instances = $this->get_instances();

		if ( isset( $instances[ $number ]['points'] ) ) {
			return $instances[ $number ]['points'];
		}

		return false;
	}

	//
	// Protected Non-final Methods.
	//

	/**
	 * Update a particular instance.
	 *
	 * This function will verify that the points setting is an integer. If you have
	 * other settings that need to be sanitized, you should override this function.
	 * Your function should check that $new_instance is set correctly. The newly
	 * calculated value of $new_instance should be returned. If false is returned,
	 * the instance won't be saved/updated.
	 *
	 * If the instance does not have the 'points' setting set already, the default
	 * will be retrieved from the class's $defaults property, if available.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 No longer abstract.
	 *
	 * @param array $new_instance New settings for this instance as input by the user
	 *                            via form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array|bool Settings to save, or false to cancel saving.
	 */
	protected function update( $new_instance, $old_instance ) {

		$new_instance = array_merge( array( 'points' => 0 ), $old_instance, $new_instance );

		if ( false === wordpoints_posint( $new_instance['points'] ) ) {
			if ( isset( $this->defaults['points'] ) ) {
				$new_instance['points'] = $this->defaults['points'];
			} else {
				return false;
			}
		}

		return $new_instance;
	}

	/**
	 * Display the settings update form.
	 *
	 * This function will output the test input for the points field. If you have
	 * other fields to output, you should override this function.
	 *
	 * If the instance does not have the 'points' setting set already, the default
	 * will be retrieved from the class's $defaults property, if available.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 No longer abstract.
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool Whether the hook has a form.
	 */
	protected function form( $instance ) {

		if ( ! isset( $instance['points'] ) ) {

			if ( isset( $this->defaults['points'] ) ) {
				$instance['points'] = $this->defaults['points'];
			} else {
				$instance['points'] = 0;
			}
		}

		?>

		<p>
			<label for="<?php $this->the_field_id( 'points' ); ?>"><?php echo $this->options['points_label']; ?></label>
			<input class="widefat" name="<?php $this->the_field_name( 'points' ); ?>" id="<?php $this->the_field_id( 'points' ); ?>" type="text" value="<?php echo wordpoints_posint( $instance['points'] ); ?>" />
		</p>

		<?php

		return true;
	}

	/**
	 * Generate the description for an instance of a points hook.
	 *
	 * @since 1.4.0
	 *
	 * @param array $instance The settings for the hook instance, or an emtpy array.
	 *
	 * @return string The hook instance's description.
	 */
	protected function generate_description( $instance = array() ) {

		return $this->options['description'];
	}

	//
	// Public Final Methods.
	//

	/**
	 * Get the id_base.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	final public function get_id_base() {

		return $this->id_base;
	}

	/**
	 * Get the ID for the current instance.
	 *
	 * This function must be used when the instance number is set up, unless the
	 * $number parameter is set.
	 *
	 * @since 1.0.0
	 *
	 * @param int $number The number of the instance to get the ID for.
	 *
	 * @return string The unique ID for the instance.
	 */
	final public function get_id( $number = null ) {

		if ( ! isset( $number ) ) {
			$number = $this->number;
		}

		return $this->id_base . '-' . (int) $number;
	}

	/**
	 * Get the number for the current hook instance.
	 *
	 * The current instance number is only set properly at certain times, such as
	 * just after saving the instance. So use with care.
	 *
	 * @since 1.4.0
	 *
	 * @return int|false The current hook number, or false.
	 */
	final public function get_number() {

		return $this->number;
	}

	/**
	 * Set the current instance by number or ID.
	 *
	 * @since 1.4.0
	 *
	 * @param int|string $instance_id The number or ID of an instance.
	 */
	final public function set_number( $instance_id ) {

		$this->number = $this->get_number_by_id( $instance_id );
	}

	/**
	 * Get the number for a hook by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The id of a hook instance.
	 *
	 * @return string The number for the hook instance. Prefixed with 'network_' for
	 *                network hooks.
	 */
	final public function get_number_by_id( $id ) {

		return str_replace( $this->id_base . '-', '', $id );
	}

	/**
	 * Get the name of the hook.
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the hook.
	 */
	final public function get_name() {

		return $this->name;
	}

	/**
	 * Get the options.
	 *
	 * The options are the arguments for display of this instance.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	final public function get_options() {

		return $this->options;
	}

	/**
	 * Get a particular option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The index for the option.
	 *
	 * @return mixed The option, or null if it doesn't exist.
	 */
	final public function get_option( $option ) {

		if ( isset( $this->options[ $option ] ) ) {

			return $this->options[ $option ];
		}
	}

	/**
	 * Set the hook options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The options for the hook.
	 */
	final public function set_options( $options ) {

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$this->options = $options;
	}

	/**
	 * Calculate the ID number of the next instance of a hook.
	 *
	 * Each hook can have multiple instances, and to tell them apart each is assigned
	 * a consecutive ID number. This function calculates what the next ID number
	 * would be.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	final public function next_hook_id_number() {

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$type = 'network';
		} else {
			$type = 'standard';
		}

		return 1 + max( array_keys( $this->get_instances( $type ) ) + array( 0 ) );
	}

	/**
	 * Get all saved instances of this hook.
	 *
	 * Returns an array of hook instances indexed by ID number. You will need to use
	 * this to get the settings for each instance of your hook in your hook() method.
	 *
	 * By default it returns all instances of a hook, standard and, on multisite
	 * installs, network-wide. To get only network-wide hooks, set $type to
	 * 'network'. For only standard hooks, 'standard'. For both, 'all' (default).
	 * When all instances are being returned, the network instance's ID numbers (the
	 * keys) are prefixed with 'network_'.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 The $type parameter was added.
	 *
	 * @param string $type The type of hooks to retrieve.
	 *
	 * @return array The saved instances of this hook.
	 */
	final public function get_instances( $type = 'all' ) {

		switch ( $type ) {

			case 'standard':
				$instances = wordpoints_get_array_option( $this->option_name );
			break;

			case 'network':
				if ( is_multisite() ) {
					$instances = wordpoints_get_array_option( $this->option_name, 'site' );
				} else {
					$instances = array();
				}
			break;

			case 'all':
				$instances = wordpoints_get_array_option( $this->option_name );

				if ( is_multisite() ) {
					foreach ( wordpoints_get_array_option( $this->option_name, 'site' ) as $number => $instance ) {
						$instances[ 'network_' . $number ] = $instance;
					}
				}
			break;
		}

		unset( $instances['__i__'] );

		return $instances;
	}

	/**
	 * Update an instance's settings.
	 *
	 * Will also create a new instance if no old instance with the ID $number exists.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance The new instance of this hooks settings.
	 * @param int   $number       The ID number for this hook.
	 */
	final public function update_callback( $new_instance, $number ) {

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$type = 'network';
		} else {
			$type = 'standard';
		}

		// Get all saved instances of this points hook.
		$all_instances = $this->get_instances( $type );

		$this->set_number( $number );

		$old_instance = isset( $all_instances[ $this->number ] ) ? $all_instances[ $this->number ] : array();

		$instance = $this->update( $new_instance, $old_instance );

		/**
		 * Filter a points hook's settings before saving.
		 *
		 * You can return false to cancel saving (keep the old settings if updating).
		 *
		 * @since 1.0.0
		 *
		 * @param array                  $instance     The updated instance of the
		 *        hook as returned by its update() method.
		 * @param array                  $new_instance The unfiltered instance of the
		 *        hook as input by the user.
		 * @param array                  $old_instance The old instance of the hook.
		 * @param WordPoints_Points_Hook $hook         The hook object.
		 */
		$instance = apply_filters( 'wordpoints_points_hook_update_callback', $instance, $new_instance, $old_instance, $this );

		if ( false !== $instance ) {

			// If this is a new instance, register it.
			// This is deprecated, but is here for back-compat.
			if ( ! isset( $all_instances[ $this->number ] ) ) {

				if ( 'network' === $type ) {
					WordPoints_Points_Hooks::_register_network_hook( $this );
				} else {
					WordPoints_Points_Hooks::_register_hook( $this );
				}
			}

			$all_instances[ $this->number ] = $instance;
		}

		$this->_save_instances( $all_instances );
	}

	/**
	 * Delete an instance of a hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_id The ID of the instance to delete.
	 */
	final public function delete_callback( $hook_id ) {

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$type = 'network';
		} else {
			$type = 'standard';
		}

		// Get all saved instances of this points hook.
		$all_instances = $this->get_instances( $type );

		$number = $this->get_number_by_id( $hook_id );

		if ( isset( $all_instances[ $number ] ) && $hook_id = $this->get_id( $number ) ) {

			unset( $all_instances[ $number ] );

			$this->_save_instances( $all_instances );

			// This is deprecated, but is here for back-compat.
			WordPoints_Points_Hooks::_unregister_hook( $hook_id );
		}
	}

	/**
	 * Generate the control form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $number
	 *
	 * @return bool|void Whether the form was displayed.
	 */
	final public function form_callback( $number ) {

		$this->set_number( $number );

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$type = 'network';
		} else {
			$type = 'standard';
		}

		$all_instances = $this->get_instances( $type );

		if ( 0 == $this->number ) {

			// We echo out a form where 'number' can be set later.
			$this->set_number( '__i__' );
			$instance = array();

		} else {

			$instance = $all_instances[ $this->number ];
		}

		/**
		 * Filter the points hook instance before display by form().
		 *
		 * Returning false will cancel display of the form.
		 *
		 * @since 1.0.0
		 *
		 * @param array                  $instance The settings for this instance.
		 * @param WordPoints_Points_Hook $hook     The hook object.
		 */
		$instance = apply_filters( 'wordpoints_points_hook_form_callback', $instance, $this );

		if ( false !== $instance ) {

			$has_form = $this->form( $instance );

			/**
			 * Inside the points hook form.
			 *
			 * You can use this to add extra fields in the hook form. The hook fires
			 * after the form() method has been called.
			 *
			 * @param bool                   $has_form Whether the hook has a form.
			 * @param array                  $instance Settings for this instance.
			 * @param WordPoints_Points_Hook $hook     The hook object.
			 */
			do_action( 'wordpoints_in_points_hook_form', $has_form, $instance, $this );

			return $has_form;
		}
	}

	/**
	 * Constructs name attributes for use in form() fields.
	 *
	 * This function should be used in form() methods to create name attributes for
	 * fields to be saved by update(). Note that the returned value is escaped with
	 * esc_attr().
	 *
	 * @since 1.0.0
	 *
	 * @see WordPoints_Points_Hook::the_field_name()
	 *
	 * @param string $field_name Field name.
	 *
	 * @return string Name attribute for $field_name.
	 */
	final public function get_field_name( $field_name ) {

		return esc_attr( 'hook-' . $this->id_base . '[' . $this->number . '][' . $field_name . ']' );
	}

	/**
	 * Echo a name attribute for use in form() fields.
	 *
	 * @since 1.0.0
	 *
	 * @uses WordPoints_Points_Hook::get_field_name()
	 *
	 * @param string $field_name The field name.
	 */
	final public function the_field_name( $field_name ) {

		echo $this->get_field_name( $field_name );
	}

	/**
	 * Constructs id attributes for use in form() fields.
	 *
	 * This function should be used in form() methods to create id attributes for
	 * fields to be saved by update(). Note that the returned value is escaped with
	 * esc_attr().
	 *
	 * @since 1.0.0
	 *
	 * @seee WordPoints_Points_Hook::the_field_id()
	 *
	 * @param string $field_name Field name.
	 *
	 * @return string ID attribute for $field_name.
	 */
	final public function get_field_id( $field_name ) {

		return esc_attr( 'hook-' . $this->id_base . '-' . $this->number . '-' . $field_name );
	}

	/**
	 * Echo an id attribute for use in form() fields.
	 *
	 * @since 1.0.0
	 *
	 * @uses WordPoints_Points_Hook::get_field_id()
	 *
	 * @param string $field_name The field name.
	 */
	final public function the_field_id( $field_name ) {

		echo $this->get_field_id( $field_name );
	}

	/**
	 * Retrieve the description for the hook.
	 *
	 * @since 1.4.0
	 *
	 * @param string $type The type of description to return. If 'generated', a user-
	 *                     defined description will not be returned.
	 *
	 * @return string The hook's description.
	 */
	final public function get_description( $type = 'any' ) {

		$instances = $this->get_instances();

		if ( $type !== 'generated' && ! empty( $instances[ $this->number ]['_description'] ) ) {
			return $instances[ $this->number ]['_description'];
		}

		$instance = ( isset( $instances[ $this->number ] ) ) ? $instances[ $this->number ] : array();

		/**
		 * Filter the description for a points hook.
		 *
		 * @since 1.4.0
		 *
		 * @param string                 $description The description.
		 * @param WordPoints_Points_Hook $hook        The points hook object.
		 * @param array                  $instance    The settings for this instance.
		 */
		return apply_filters( 'wordpoints_points_hook_description', $this->generate_description( $instance ), $this, $instance );
	}

	/**
	 * Get the points type for an instance.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $number The instance number. Prefixed by 'network_' for
	 *                           network-wide hooks.
	 *
	 * @return string|false The type of points, or false if none found.
	 */
	final public function points_type( $number = null ) {

		$network_mode = false;

		if ( ! isset( $number ) ) {

			$number = $this->number;

		} elseif ( is_string( $number ) && substr( $number, 0, 8 ) === 'network_' ) {

			$network_mode = true;
			$number = (int) substr( $number, 8 );
		}

		$current_mode = WordPoints_Points_Hooks::get_network_mode();

		if ( $current_mode !== $network_mode ) {
			WordPoints_Points_Hooks::set_network_mode( $network_mode );
		}

		$points_type = WordPoints_Points_Hooks::get_points_type( $this->get_id( $number ) );

		// Reset network mode if it was changed.
		if ( $current_mode !== $network_mode ) {
			WordPoints_Points_Hooks::set_network_mode( $current_mode );
		}

		return $points_type;
	}

	//
	// Protected Final Methods.
	//

	/**
	 * Initializer.
	 *
	 * You need to call this in your constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Name for the hook displayed on the configuration page.
	 * @param array  $options {
	 *        Optional arguments for the hooks' display
	 *
	 *        @type string $description  Shown on the configuration page.
	 *        @type int    $width        The width of your hook form. Required if
	 *              more than 250px, but you should stay within that if possible.
	 *        @type string $points_label The label for the points field. Used by the
	 *              default form function. The default is 'Points:'. If you are
	 *              overriding that function, you can ignore this option.
	 * }
	 */
	final protected function init( $name, array $options = array() ) {

		$this->id_base     = strtolower( get_class( $this ) );
		$this->name        = $name;
		$this->option_name = 'wordpoints_hook-' . $this->id_base;

		// Option names can only be 64 characters long.
		if ( isset( $this->option_name{65} ) ) {
			_doing_it_wrong( __METHOD__, sprintf( 'Points hook class names cannot be longer than 48 characters, %s is %s charachter(s) too long.', $this->id_base, strlen( $this->id_base ) - 48 ), '1.5.0' );
			return;
		}

		$this->options = array_merge(
			array(
				'width'        => 250,
				'description'  => '',
				'points_label' => __( 'Points:', 'wordpoints' ),
			)
			, $options
		);

		$this->options['_classname']   = $this->option_name;
		$this->options['_before_hook'] = '';
		$this->options['_after_hook']  = '';

		/*
		 * The below registration is not longer necessary, and is deprecated.
		 * It is here only for back-compat.
		 */

		// Register all standard instances of this hook.
		foreach ( array_keys( $this->get_instances( 'standard' ) ) as $number ) {

			$this->set_number( $number );

			WordPoints_Points_Hooks::_register_hook( $this );
		}

		// Register all network instances of this hook.
		foreach ( array_keys( $this->get_instances( 'network' ) ) as $number ) {

			$this->set_number( $number );

			WordPoints_Points_Hooks::_register_network_hook( $this );
		}
	}

	//
	// Private Final Methods.
	//

	/**
	 * Save the settings of all the hook's instances.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instances All settings, indexed by instance number.
	 */
	final private function _save_instances( $instances ) {

		// This needs to start at 1.
		unset( $instances[0] );

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			update_site_option( $this->option_name, $instances );
		} else {
			update_option( $this->option_name, $instances );
		}
	}
}

// end of file /components/points/includes/class-WordPoints_Points_Hook.php
