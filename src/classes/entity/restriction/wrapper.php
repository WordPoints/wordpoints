<?php

/**
 * Entity restriction wrapper class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Wraps an array of entity restrictions.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restriction_Wrapper
	implements WordPoints_Entity_RestrictionI {

	/**
	 * The ID of the entity being checked.
	 *
	 * @since 2.2.0
	 *
	 * @var int|string
	 */
	protected $entity_id;

	/**
	 * The restrictions being wrapped.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Entity_RestrictionI[]
	 */
	protected $restrictions = array();

	/**
	 * The context in which this entity exists.
	 *
	 * Default is empty, which assumes the current context.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	protected $context;

	/**
	 * @since 2.2.0
	 *
	 * @param int|string                       $entity_id    Entity ID.
	 * @param string[]                         $hierarchy    Entity hierarchy.
	 * @param WordPoints_Entity_RestrictionI[] $restrictions Restrictions to wrap.
	 * @param array                            $context      Entity context.
	 */
	public function __construct(
		$entity_id,
		array $hierarchy,
		array $restrictions = array(),
		array $context = array()
	) {

		$this->entity_id = $entity_id;
		$this->context   = $context;

		foreach ( $restrictions as $restriction ) {
			if ( $restriction->applies() ) {
				$this->restrictions[] = $restriction;
			}
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {

		return (bool) $this->restrictions;
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		if ( empty( $this->restrictions ) ) {
			return true;
		}

		if ( $this->context ) {
			/** @var WordPoints_Entity_Contexts $contexts */
			$contexts = wordpoints_entities()->get_sub_app( 'contexts' );
			$contexts->switch_to( $this->context );
		}

		$can = true;

		foreach ( $this->restrictions as $restriction ) {
			if ( ! $restriction->user_can( $user_id ) ) {
				$can = false;
				break;
			}
		}

		if ( isset( $contexts ) ) {
			$contexts->switch_back();
		}

		return $can;
	}
}

// EOF
