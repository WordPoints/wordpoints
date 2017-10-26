<?php

/**
 * Term entity class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * Represents a Taxonomy Term.
 *
 * @since 2.4.0
 */
class WordPoints_Entity_Term extends WordPoints_Entity_Stored_DB_Table {

	/**
	 * @since 2.4.0
	 */
	protected $wpdb_table_name = 'terms';

	/**
	 * @since 2.4.0
	 */
	protected $id_field = 'term_id';

	/**
	 * @since 2.4.0
	 */
	protected $id_is_int = true;

	/**
	 * @since 2.4.0
	 */
	protected $getter = 'get_term';

	/**
	 * @since 2.4.0
	 */
	protected $human_id_field = 'name';

	/**
	 * The slug of the taxonomy this entity object is for.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * @since 2.4.0
	 */
	public function __construct( $slug ) {

		parent::__construct( $slug );

		if ( ! isset( $this->taxonomy ) ) {
			$this->taxonomy = substr( $this->slug, 5 /* term\ */ );
		}
	}

	/**
	 * @since 2.4.0
	 */
	public function get_title() {

		$taxonomy = get_taxonomy( $this->taxonomy );

		if ( $taxonomy ) {
			return $taxonomy->labels->singular_name;
		} else {
			return $this->slug;
		}
	}

	/**
	 * @since 2.4.0
	 */
	public function get_storage_info() {
		return array(
			'type' => 'db',
			'info' => array(
				'type'       => 'table',
				'table_name' => $GLOBALS['wpdb']->{$this->wpdb_table_name},
				'conditions' => array(
					array(
						'field' => array(
							'table_name' => $GLOBALS['wpdb']->term_taxonomy,
							'on'         => array(
								'primary_field'   => 'term_id',
								'join_field'      => 'term_id',
								'condition_field' => 'taxonomy',
							),
						),
						'value' => $this->taxonomy,
					),
				),
			),
		);
	}
}

// EOF
