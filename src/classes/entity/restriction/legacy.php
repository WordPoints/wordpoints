<?php

/**
 * Legacy entity restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restriction rule for entities implementing the legacy interface.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restriction_Legacy
	implements WordPoints_Entity_RestrictionI {

	/**
	 * The ID of the entity this restriction relates to.
	 *
	 * @since 2.2.0
	 *
	 * @var int|string
	 */
	protected $entity_id;

	/**
	 * The object for the entity type that this restriction related to.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Entity
	 */
	protected $entity;

	/**
	 * The object for the entity type if it is restricted.
	 *
	 * Really only here for better auto-detection of property types.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Entity_Restricted_VisibilityI
	 */
	protected $restricted_entity = false;

	/**
	 * @since 2.2.0
	 */
	public function __construct( $entity_id, array $hierarchy ) {

		$this->entity_id = $entity_id;

		$entity = wordpoints_entities()->get( $hierarchy[0] );

		if ( ! $entity instanceof WordPoints_Entity ) {
			return;
		}

		$this->entity = $entity;

		if ( $this->entity instanceof WordPoints_Entity_Restricted_VisibilityI ) {

			_deprecated_argument(
				__METHOD__
				, '2.2.0'
				, esc_html( get_class( $this->entity ) )
					. ' implements the WordPoints_Entity_Restricted_VisibilityI'
					. ' interface, which has been deprecated. Use the entity'
					. ' restrictions API instead.'
			);

			$this->restricted_entity = $this->entity;
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		$can_view = true;

		if ( $this->restricted_entity ) {
			$can_view = $this->restricted_entity->user_can_view(
				$user_id
				, $this->entity_id
			);
		}

		if ( $this->entity ) {

			/**
			 * Filter whether a user can view an entity.
			 *
			 * @since      2.1.0
			 * @deprecated 2.2.0 Use the entity restrictions API instead.
			 *
			 * @param bool              $can_view  Whether the user can view the entity.
			 * @param int               $user_id   The user ID.
			 * @param int               $entity_id The entity ID.
			 * @param WordPoints_Entity $entity    The entity object.
			 */
			$can_view = apply_filters_deprecated(
				'wordpoints_entity_user_can_view'
				, array( $can_view, $user_id, $this->entity_id, $this->entity )
				, '2.2.0'
				, false
				, 'Use the entity restrictions API instead.'
			);
		}

		return $can_view;
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return (bool) $this->restricted_entity;
	}
}

// EOF
