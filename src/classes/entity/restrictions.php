<?php

/**
 * Entity restrictions app class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Entity restrictions app.
 *
 * Entities can be restricted from some users in various ways, and these restrictions
 * can come in different types: 'view' restrictions, 'edit' restrictions, etc. This
 * app serves as the face of the entity restrictions API by exposing these
 * restrictions via the {@see self::get()} method. The registries for each of the
 * different types of restrictions are children of this app, and all different types
 * can be checked using it. The restrictions should only be checked in this way, not
 * by interacting with the registries directly.
 *
 * The most basic restrictions are 'know' restrictions, which restrict the user from
 * knowing any identifying information about the entity at all; they aren't allowed
 * to even know that it exists. These kind of restrictions are applied by default,
 * and in addition to whatever other type of restriction is being checked.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restrictions extends WordPoints_App {

	/**
	 * Get the restrictions for an entity.
	 *
	 * @since 2.2.0
	 *
	 * @param int|string      $entity_id The ID of the entity.
	 * @param string|string[] $hierarchy The entity slug or hierarchy leading to an
	 *                                   entity child's slug.
	 * @param string          $type      The type of restrictions to check. Default
	 *                                   is 'know'.
	 *
	 * @return WordPoints_Entity_RestrictionI A restriction object wrapping all of
	 *                                        the restrictions of this type for this
	 *                                        entity.
	 */
	public function get( $entity_id, $hierarchy, $type = 'know' ) {

		$hierarchy         = (array) $hierarchy;
		$restrictions      = array();
		$class_hierarchies = array( $hierarchy );

		$depth = count( $hierarchy );

		if ( 1 === $depth ) {
			$class_hierarchies[] = array();
		}

		if ( is_array( $entity_id ) ) {

			$context     = $entity_id;
			$entity_slug = $hierarchy[ $depth - ( $depth % 2 ? 1 : 2 ) ];
			$entity_id   = $context[ $entity_slug ];

			unset( $context[ $entity_slug ] );

			/** @var WordPoints_Entity_Contexts $contexts */
			$contexts = wordpoints_entities()->get_sub_app( 'contexts' );
			$contexts->switch_to( $context );
		}

		$types = array( $type );

		// If a user isn't allowed to even know about an entity, they can't do
		// anything else either. So we always check that too.
		if ( 'know' !== $type ) {
			$types[] = 'know';
		}

		foreach ( $types as $type ) {

			$sub_app = $this->get_sub_app( $type );

			if ( ! $sub_app instanceof WordPoints_Class_Registry_Deep_Multilevel ) {
				continue;
			}

			foreach ( $class_hierarchies as $class_hierarchy ) {

				$type_restrictions = $sub_app->get_children(
					$class_hierarchy
					, array( $entity_id, $hierarchy )
				);

				if ( ! $type_restrictions ) {
					continue;
				}

				$restrictions = array_merge(
					$restrictions
					, array_values( $type_restrictions )
				);
			}
		}

		$wrapper = new WordPoints_Entity_Restriction_Wrapper(
			$entity_id
			, $hierarchy
			, $restrictions
			, isset( $context ) ? $context : array()
		);

		if ( isset( $contexts ) ) {
			$contexts->switch_back();
		}

		return $wrapper;
	}
}

// EOF
