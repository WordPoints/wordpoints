<?php

/**
 * Abstract class for representing a rank type.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Parent rank type handler.
 *
 * The ranks provided by WordPoints may be of multiple different types. Each rank
 * type is represented by a child of this class, which may be called a "rank type
 * handler". The handler for a rank type handles adding, updating, and deleting ranks
 * of that type. It also does the same for their metadata.
 *
 * This abstract parent class provides the basic backbone of this API. It also
 * contains several abstract methods, which must be implemented by each child class
 * for each type of rank. These methods hook up the functions to perform the rank
 * change, display the form fields for the metadata for this rank type, and validate
 * the meta when the form is submitted.
 *
 * @since 1.7.0
 */
abstract class WordPoints_Rank_Type {

	//
	// Protected Vars.
	//

	/**
	 * The rank type's slug.
	 *
	 * @since 1.7.0
	 *
	 * @type string $slug
	 */
	protected $slug;

	/**
	 * The rank type's name.
	 *
	 * @since 1.7.0
	 *
	 * @type string $name
	 */
	protected $name;

	/**
	 * The meta fields used by ranks of this type.
	 *
	 * It contains an array for each meta field indexed by key. Each array may have
	 * the following attributes:
	 * - 'default' The default value for this field. Optional.
	 * - 'type'    The type of field: hidden, text, or number.
	 * - 'label'   The label text for this field. Optional (for hidden fields).
	 *
	 * @since 1.7.0
	 *
	 * @type array[] $meta_fields
	 */
	protected $meta_fields = array();

	//
	// Public Methods.
	//

	/**
	 * Construct the rank type.
	 *
	 * @param array $args {
	 *        Arguments for this rank type.
	 *
	 *        @type string $slug The slug of this rank type.
	 * }
	 */
	public function __construct( array $args ) {

		$this->slug = $args['slug'];
	}

	//
	// Abstract Methods.
	//

	/**
	 * Destroy the rank type hanlder when this rank type is deregistered.
	 *
	 * This method is called if the rank type is deregistered, so that it can revert
	 * anything done on construction. For example, it should unhook itself from any
	 * actions, etc.
	 *
	 * @since 1.7.0
	 */
	abstract public function destruct();

	/**
	 * Validate the metadata for a rank of this type.
	 *
	 * If you return a WP_Error object, the error message will be sent to the user.
	 * If the error is for a specific field, you can designate which one by giving
	 * its name as part of the error's array of data, with the key 'field'.
	 *
	 * @since 1.7.0
	 *
	 * @param array $meta The metadata to validate.
	 *
	 * @return array|WP_Error|false The validated metadata, or false or a WP_Error
	 *                              if it should't be saved.
	 */
	abstract public function validate_rank_meta( array $meta );

	/**
	 * Determine if a user meets the requirements for a rank of this type.
	 *
	 * This function is called to determine whether a user should be transitioned to
	 * the rank in question from their current rank.
	 *
	 * @since 1.7.0
	 *
	 * @param int             $user_id The ID of the user to check.
	 * @param WordPoints_Rank $rank    The object for the rank.
	 * @param array           $args    Other arguments from the function which
	 *                                 triggered the check.
	 *
	 * @return bool Whether the user meets the requirements for this rank.
	 */
	abstract protected function can_transition_user_rank( $user_id, $rank, array $args );

	//
	// Final Public Methods.
	//

	/**
	 * Get the slug of this rank type.
	 *
	 * @since 1.7.0
	 *
	 * @return string The rank type's slug.
	 */
	final public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the name of this rank type.
	 *
	 * @since 1.7.0
	 *
	 * @return string The rank type's name.
	 */
	final public function get_name() {
		return $this->name;
	}

	/**
	 * Get the meta fields used by the rank type.
	 *
	 * @since 1.7.0
	 *
	 * @return string[] The meta keys used to store this rank type's metadata.
	 */
	final public function get_meta_fields() {
		return $this->meta_fields;
	}

	/**
	 * Display form fields for the metadata of a rank of this type.
	 *
	 * @since 1.7.0
	 *
	 * @param array $meta The metadata for a rank of this type.
	 * @param array $args {
	 *        Arguments controlling the way the fields are displayed.
	 *
	 *        @type bool $placeholders Whether to just display Underscore template
	 *                                 placeholders instead of the actual field
	 *                                 values. Default is false. Does not affect
	 *                                 hidden fields.
	 * }
	 */
	final public function display_rank_meta_form_fields(
		array $meta = array(),
		array $args = array()
	) {

		$args = array_merge( array( 'placeholders' => false ), $args );

		foreach ( $this->meta_fields as $name => $field ) {

			// If we aren't using placeholders, calculate the value. Hidden fields
			// never use placeholders.
			if ( ! $args['placeholders'] || 'hidden' === $field['type'] ) {

				// Default to the default value.
				$value = $field['default'];

				// If the value is set use that instead.
				if ( isset( $meta[ $name ] ) ) {
					$value = $meta[ $name ];
				}
			}

			switch ( $field['type'] ) {

				case 'hidden':
				case 'number':
				case 'text':
					if ( isset( $field['label'] ) ) {
						?><p class="description description-thin"><label><?php
						echo esc_html( $field['label'] );
					}

					?>
					<input
						type="<?php echo esc_attr( $field['type'] ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						<?php if ( $args['placeholders'] && 'hidden' !== $field['type'] ) : ?>
						value="<% if ( typeof <?php echo sanitize_key( $name ); ?> !== "undefined" ) { print( <?php echo sanitize_key( $name ); ?> ); } %>"
						<?php else : ?>
						value="<?php echo esc_attr( $value ); ?>"
						<?php endif; ?>
						class="widefat"
					/>
					<?php

					if ( isset( $field['label'] ) ) {
						?></label></p><?php
					}
				break;

				default:
					_doing_it_wrong(
						__METHOD__
						, sprintf(
							'WordPoints Error: Unknown field type "%s".'
							, $field['type']
						)
						, '1.7.0'
					);
			}
		}
	}

	/**
	 * Check if a user's rank can be increased.
	 *
	 * @since 1.7.0
	 *
	 * @param int             $user_id The ID of the user.
	 * @param WordPoints_Rank $rank    The starting rank.
	 * @param array           $args    Other arguments.
	 *
	 * @return WordPoints_Rank The highest rank that the user can be increased to.
	 */
	final public function maybe_increase_user_rank( $user_id, $rank, array $args = array() ) {

		$next_rank = $rank->get_adjacent( 1 );

		if ( ! $next_rank ) {
			return $rank;
		}

		$rank_type = WordPoints_Rank_Types::get_type( $next_rank->type );

		if ( ! $rank_type->can_transition_user_rank( $user_id, $next_rank, $args ) ) {
			return $rank;
		}

		return $this->maybe_increase_user_rank( $user_id, $next_rank, $args );
	}

	/**
	 * Check if a user's rank should be decreased.
	 *
	 * @since 1.7.0
	 *
	 * @param int             $user_id The ID of the user.
	 * @param WordPoints_Rank $rank    The starting rank.
	 * @param array           $args    Other arguments.
	 *
	 * @return WordPoints_Rank The highest rank that the user can have.
	 */
	final public function maybe_decrease_user_rank( $user_id, $rank, array $args = array() ) {

		$rank_type = WordPoints_Rank_Types::get_type( $rank->type );

		if ( $rank_type->can_transition_user_rank( $user_id, $rank, $args ) ) {
			return $rank;
		}

		$previous_rank = $rank->get_adjacent( -1 );

		if ( ! $previous_rank ) {
			return $rank;
		}

		return $this->maybe_decrease_user_rank( $user_id, $previous_rank, $args );
	}

	//
	// Final Protected Methods.
	//

	/**
	 * Maybe transition a user's rank.
	 *
	 * @since 1.7.0
	 *
	 * @param int   $user_id  The ID of the user whose rank to maybe transition.
	 * @param bool  $increase Whether the user's rank will increase, or decrease.
	 * @param array $args     Other arguments.
	 */
	final protected function maybe_transition_user_ranks(
		$user_id,
		$increase,
		array $args = array()
	) {

		$groups = WordPoints_Rank_Groups::get();

		foreach ( $groups as $group_slug => $group ) {

			if ( ! WordPoints_Rank_Groups::is_type_registered_for_group( $this->slug, $group_slug ) ) {
				continue;
			}

			$rank_id = wordpoints_get_user_rank( $user_id, $group_slug );

			$rank = wordpoints_get_rank( $rank_id );

			if ( ! $rank ) {
				continue;
			}

			if ( $increase ) {
				$new_rank = $this->maybe_increase_user_rank( $user_id, $rank, $args );
			} else {
				$new_rank = $this->maybe_decrease_user_rank( $user_id, $rank, $args );
			}

			if ( $new_rank !== $rank ) {
				wordpoints_update_user_rank( $user_id, $new_rank->ID );
			}
		}
	}

} // abstract class WordPoints_Rank_Type

// EOF
