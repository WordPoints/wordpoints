<?php

/**
 * Abstract class to extend for points hooks that implement a post type setting.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.5.0
 */

/**
 * A base class for points hooks that have a post type settings.
 *
 * Intended to reduce code (and bug) duplication by providing helper functions.
 *
 * @since 1.5.0
 */
abstract class WordPoints_Post_Type_Points_Hook_Base extends WordPoints_Points_Hook {

	/**
	 * Check if the post type setting matches a certian post type.
	 *
	 * @since 1.5.0
	 *
	 * @param string $post_type          The post type to check against the setting.
	 * @param string $instance_post_type The post type setting from an instance.
	 *
	 * @return bool Whether this post type matches and points should be awarded.
	 */
	public function is_matching_post_type( $post_type, $instance_post_type ) {

		return (
			$instance_post_type === $post_type
			|| (
				$instance_post_type === 'ALL'
				&& post_type_exists( $post_type )
				&& get_post_type_object( $post_type )->public
			)
		);
	}

	/**
	 * Generate a description for an instance of this hook.
	 *
	 * @since 1.5.0
	 *
	 * @param array $instance The settings for the instance the description is for.
	 *
	 * @return string A description for the hook instance.
	 */
	protected function generate_description( $instance = array() ) {

		if ( ! empty( $instance['post_type'] ) && $instance['post_type'] !== 'ALL' ) {
			$post_type = get_post_type_object( $instance['post_type'] );

			if ( $post_type ) {
				return sprintf( $this->get_option( 'post_type_description' ), $post_type->labels->singular_name );
			}
		}

		return parent::generate_description( $instance );
	}

	/**
	 * Display the settings update form.
	 *
	 * @since 1.5.0
	 *
	 * @param array $instance Current settings.
	 *
	 * @return bool True.
	 */
	protected function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );

		?>

		<p>
			<label for="<?php $this->the_field_id( 'post_type' ); ?>"><?php _e( 'Select post type:', 'wordpoints' ); ?></label>
			<?php

			wordpoints_list_post_types(
				array(
					'selected' => $instance['post_type'],
					'id'       => $this->get_field_id( 'post_type' ),
					'name'     => $this->get_field_name( 'post_type' ),
					'class'    => 'widefat',
				)
				, array( 'public' => true )
			);

			?>
		</p>

		<?php parent::form( $instance ); ?>

		<?php

		return true;
	}
}

// EOF
