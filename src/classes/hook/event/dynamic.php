<?php

/**
 * Dynamic hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook event that is attached to a dynamic entity.
 *
 * Dynamic events, like dynamic entities, have slugs that are prefixed with a generic
 * identifier (like 'post'). After this comes a backslash (\) and then the dynamic
 * part of the name.
 *
 * This class offers a helper method to let dynamic events build better titles and
 * descriptions by using a dynamic entity title (like "Page" or "Order"), instead of
 * hard-coding something generic (like "Post").
 *
 * To retrieve the entity title, we just rip off the generic part of the event slug
 * and replace it with the generic part of the entity slug. Then we just retrieve
 * the entity and return its title.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Event_Dynamic extends WordPoints_Hook_Event {

	/**
	 * The generic portion of the entity slug.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $generic_entity_slug;

	/**
	 * Get the title of the entity.
	 *
	 * This is useful to interpolate into your event title and description.
	 *
	 * @since 2.1.0
	 *
	 * @return string The title of the entity.
	 */
	protected function get_entity_title() {

		$parts = wordpoints_parse_dynamic_slug( $this->slug );

		if ( $parts['dynamic'] ) {
			$entity_slug = "{$this->generic_entity_slug}\\{$parts['dynamic']}";
		} else {
			$entity_slug = $this->generic_entity_slug;
		}

		$entity = wordpoints_entities()->get( $entity_slug );

		if ( ! $entity instanceof WordPoints_Entity ) {
			return $this->slug;
		}

		return $entity->get_title();
	}
}

// EOF
