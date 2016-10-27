<?php

/**
 * Mock entity restriction class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Mock entity restriction class for the PHPUnit tests.
 *
 * @since 2.2.0
 */
class WordPoints_PHPUnit_Mock_Entity_Restriction
	implements WordPoints_Entity_RestrictionI {

	/**
	 * Whether the user is allowed to.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	public $user_can = false;

	/**
	 * Whether this restriction applies.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	public $applies = true;

	/**
	 * A context to record the current ID of in user_can().
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	public $listen_for_context;

	/**
	 * The IDs of the context recorded in user_can().
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	public $context = array();

	/**
	 * A context to record the current ID of in user_can() and the constructor.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	public static $listen_for_contexts;

	/**
	 * The IDs of the context recorded in user_can().
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	public static $contexts = array();

	/**
	 * The IDs of the context recorded in the constructor.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	public static $contexts_construct = array();

	/**
	 * @since 2.2.0
	 *
	 * @param int|string $entity_id The entity ID.
	 * @param array      $hierarchy The entity hierarchy.
	 * @param bool       $user_can  Whether the user can.
	 * @param bool       $applies   Whether this restriction applies.
	 */
	public function __construct(
		$entity_id,
		array $hierarchy,
		$user_can = null,
		$applies = null
	) {

		if ( isset( $user_can ) ) {
			$this->user_can = $user_can;
		}

		if ( isset( $applies ) ) {
			$this->applies = $applies;
		}

		if ( self::$listen_for_contexts ) {
			self::$contexts_construct[] = array(
				'context' => wordpoints_entities()
					->get_sub_app( 'contexts' )
					->get( self::$listen_for_contexts )
					->get_current_id(),
				'entity_id' => $entity_id,
				'hierarchy' => $hierarchy,
			);
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return $this->applies;
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		if ( $this->listen_for_context ) {
			$this->context[] = wordpoints_entities()
				->get_sub_app( 'contexts' )
				->get( $this->listen_for_context )
				->get_current_id();
		}

		if ( self::$listen_for_contexts ) {
			self::$contexts[] = wordpoints_entities()
				->get_sub_app( 'contexts' )
				->get( self::$listen_for_contexts )
				->get_current_id();
		}

		return $this->user_can;
	}
}

// EOF
