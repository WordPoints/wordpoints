<?php

/**
 * Post type hook action class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Bootstrap for actions that are for posts across multiple post types.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Action_Post_Type extends WordPoints_Hook_Action {

	/**
	 * The arg hierarchy needed to reach the post from a main action arg.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $post_hierarchy = array( 'post\\post' );

	/**
	 * @since 2.1.0
	 */
	public function should_fire() {

		$post = $this->get_post_entity();

		if ( ! $post ) {
			return false;
		}

		return parent::should_fire();
	}

	/**
	 * Get the post entity object, ensuring that it is the correct post type.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Entity|false The post entity object, with the post type
	 *                                 matching the dynamic portion of the action
	 *                                 slug, or false.
	 */
	protected function get_post_entity() {

		$parts = wordpoints_parse_dynamic_slug( $this->slug );

		if ( ! $parts['dynamic'] ) {
			return false;
		}

		$this->post_hierarchy = str_replace(
			'\\post'
			, '\\' . $parts['dynamic']
			, $this->post_hierarchy
		);

		$entity = wordpoints_entities()->get( $this->post_hierarchy[0] );

		if ( ! $entity instanceof WordPoints_Entity ) {
			return false;
		}

		$entity->set_the_value( $this->get_arg_value( $this->post_hierarchy[0] ) );

		if ( 1 === count( $this->post_hierarchy ) ) {

			$post_entity = $entity;

		} else {

			$args = new WordPoints_Entity_Hierarchy( $entity );

			$post_entity = $args->get_from_hierarchy( $this->post_hierarchy );

			if ( ! $post_entity instanceof WordPoints_Entity ) {
				return false;
			}
		}

		if ( $parts['dynamic'] !== $post_entity->get_the_attr_value( 'post_type' ) ) {
			return false;
		}

		return $post_entity;
	}
}

// EOF
