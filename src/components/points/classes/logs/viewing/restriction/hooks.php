<?php

/**
 * Hooks API points logs viewing restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restricts logs from the Hooks API based on restrictions for related entities.
 *
 * The IDs of the entities relating to a particular fire of a hook event are saved as
 * metadata for the points log for that event. In this restriction we pull up those
 * entity IDs and pass them to the Entity Restrictions API, and if we find that any
 * of those entities is restricted, then we restrict the log as well.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Logs_Viewing_Restriction_Hooks
	implements WordPoints_Points_Logs_Viewing_RestrictionI {

	/**
	 * The restrictions that apply to this entity.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Entity_RestrictionI[]
	 */
	protected $restrictions = array();

	/**
	 * @since 2.2.0
	 */
	public function __construct( $log ) {

		$events = wordpoints_hooks()->get_sub_app( 'events' );

		$log_id      = $log->id;
		$event_slug  = $log->log_type;
		$is_reversal = ( 'reverse-' === substr( $log->log_type, 0, 8 ) );

		if ( $is_reversal ) {
			$event_slug = substr( $log->log_type, 8 );
		}

		// If this is not a log from an event, this doesn't apply.
		if ( ! $events->is_registered( $event_slug ) ) {
			return;
		}

		if ( $is_reversal ) {
			$log_id = wordpoints_get_points_log_meta(
				$log_id
				, 'original_log_id'
				, true
			);
		}

		/** @var WordPoints_Entity_Restrictions $entity_restrictions */
		$entity_restrictions = wordpoints_entities()->get_sub_app( 'restrictions' );

		/** @var WordPoints_Hook_ArgI $arg */
		foreach ( $events->get_sub_app( 'args' )->get_children( $event_slug ) as $slug => $arg ) {

			$value = wordpoints_get_points_log_meta( $log_id, "{$slug}_guid", true );

			if ( ! $value ) {
				$value = wordpoints_get_points_log_meta( $log_id, $slug, true );

				// If we don't find the value it may mean that a new arg has been
				// registered or something. Just skip over it.
				if ( ! $value ) {
					continue;
				}
			}

			$entity_slug = $arg->get_entity_slug();
			$restriction = $entity_restrictions->get( $value, $entity_slug, 'view' );

			if ( ! $restriction->applies() ) {
				continue;
			}

			$this->restrictions[ $entity_slug ] = $restriction;
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function get_description() {

		$entities = wordpoints_entities();

		// translators: Entity title (Post, Comment, etc.).
		$string = __(
			'This log entry is only visible to users who can view the %s.'
			, 'wordpoints'
		);

		$descriptions = array();

		foreach ( $this->restrictions as $entity_slug => $restriction ) {

			$entity = $entities->get( $entity_slug );

			if ( ! $entity instanceof WordPoints_Entity ) {
				continue;
			}

			$descriptions[] = sprintf( $string, $entity->get_title() );
		}

		return $descriptions;
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		foreach ( $this->restrictions as $restriction ) {
			if ( ! $restriction->user_can( $user_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return ! empty( $this->restrictions );
	}
}

// EOF
