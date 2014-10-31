<?php

/**
 * Ajax callbacks for the administration screens of the Ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Respond to Ajax requests from the ranks admin screen.
 *
 * This code is part of a class mainly to keep it DRY by consolidating common code
 * into the private methods.
 *
 * @since 1.7.0
 */
final class WordPoints_Ranks_Admin_Screen_Ajax {

	//
	// Private Vars.
	//

	/**
	 * The instance of the class.
	 *
	 * @since 1.7.0
	 *
	 * @type WordPoints_Ranks_Admin_Screen_Ajax $instance
	 */
	private static $instance;

	/**
	 * The object for the rank type of the current rank.
	 *
	 * @since 1.7.0
	 *
	 * @type WordPoints_Rank_Type $rank_type
	 */
	private $rank_type;

	//
	// Public Static Functions.
	//

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.7.0
	 *
	 * @return WordPoints_Ranks_Admin_Screen_Ajax The instace of the class.
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Get all of the ranks orgainized by group.
	 *
	 * @since 1.7.0
	 *
	 * @return array The ranks indexed by group.
	 */
	public static function prepare_all_ranks() {

		$rank_groups = WordPoints_Rank_Groups::get();
		$ranks = array();

		foreach ( $rank_groups as $group ) {
			$ranks[ $group->slug ] = self::prepare_group_ranks( $group );
		}

		return $ranks;
	}

	/**
	 * Prepare an array of ranks in a rank group for conversion to JSON.
	 *
	 * @since 1.7.0
	 *
	 * @param WordPoints_Rank_Group $group The group object.
	 *
	 * @return array The ranks of this group, prepared for sending to the JS.
	 */
	public static function prepare_group_ranks( $group ) {

		$rank_ids = $group->get_ranks();
		$ranks = array();

		foreach ( $rank_ids as $order => $rank_id ) {

			$rank = wordpoints_get_rank( $rank_id );

			if ( ! $rank ) {
				continue;
			}

			$ranks[] = self::_prepare_rank( $rank );
		}

		return $ranks;
	}

	//
	// Public Methods.
	//

	/**
	 * Hook up the methods to the Ajax request actions when the class is constructed.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		if ( isset( self::$instance ) ) {
			_doing_it_wrong( __METHOD__, 'Class should only be constructed once.', '1.7.0' );
		}

		self::$instance = $this;

		$this->hooks();
	}

	/**
	 * Hook the callback methods to the Ajax actions.
	 *
	 * @since 1.7.0
	 */
	public function hooks() {

		add_action( 'wp_ajax_wordpoints_admin_get_ranks', array( $this, 'get_ranks' ) );
		add_action( 'wp_ajax_wordpoints_admin_create_rank', array( $this, 'create_rank' ) );
		add_action( 'wp_ajax_wordpoints_admin_update_rank', array( $this, 'update_rank' ) );
		add_action( 'wp_ajax_wordpoints_admin_delete_rank', array( $this, 'delete_rank' ) );
	}

	/**
	 * Handle an Ajax request retrieving all of the ranks in the group.
	 *
	 * @since 1.7.0
	 */
	public function get_ranks() {

		$this->_verify_user_can();

		$group = $this->_get_group();

		$this->_verify_request( "wordpoints_get_ranks-{$group->slug}" );

		wp_send_json_success( self::prepare_group_ranks( $group ) );
	}

	/**
	 * Handle an Ajax request to create a new rank.
	 *
	 * @since 1.7.0
	 */
	public function create_rank() {

		$this->_verify_user_can();

		$group = $this->_get_group();
		$type = $this->_get_rank_type()->get_slug();

		$this->_verify_request( "wordpoints_create_rank|{$group->slug}|{$type}" );

		// Attempt to save the rank.
		$result = wordpoints_add_rank(
			$this->_get_rank_name()
			, $type
			, $group->slug
			, $this->_get_rank_position()
			, $this->_get_rank_meta()
		);

		$this->_send_json_result( $result, 'create' );
	}

	/**
	 * Handle an Ajax request to update a rank.
	 *
	 * @since 1.7.0
	 */
	public function update_rank() {

		$this->_verify_user_can();

		$group = $this->_get_group();
		$rank  = $this->_get_rank();

		$this->_verify_request(
			"wordpoints_update_rank|{$group->slug}|{$rank->ID}"
		);

		$type = $this->_get_rank_type()->get_slug();

		if ( $type !== $rank->type ) {
			wp_send_json_error( array( 'message' => __( 'This rank does not match any rank in the database, perhaps it was deleted. Refresh the page to update the list of ranks.', 'wordpoints' ) ) );
		}

		$result = wordpoints_update_rank(
			$rank->ID
			, $this->_get_rank_name()
			, $type
			, $group->slug
			, $this->_get_rank_position()
			, $this->_get_rank_meta()
		);

		$this->_send_json_result( $result, 'update' );
	}

	/**
	 * Handle Ajax requests to delete a rank.
	 *
	 * @since 1.7.0
	 */
	public function delete_rank() {

		$this->_verify_user_can();

		$group = $this->_get_group();
		$rank  = $this->_get_rank();

		$this->_verify_request(
			"wordpoints_delete_rank|{$group->slug}|{$rank->ID}"
		);

		$result = wordpoints_delete_rank( $rank->ID );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'There was an error deleting the rank. Please try again.', 'wordpoints' ) ) );
		}

		wp_send_json_success();
	}

	//
	// Private Methods.
	//

	/**
	 * Report an unexpected error.
	 *
	 * There is a common error message returned when certain hidden fields are
	 * absent. The user doesn't know these fields exist, so we give a generic error.
	 * In the real world, this should really never happen.
	 *
	 * @since 1.7.0
	 *
	 * @param string $debug_context Context sent with the message (for debugging).
	 */
	private function _unexpected_error( $debug_context ) {

		wp_send_json_error(
			array(
				'message' => __( 'There was an unexpected error. Try reloading the page.', 'wordpoints' ),
				'debug'   => $debug_context,
			)
		);
	}

	/**
	 * Verify that the current user can do this.
	 *
	 * This should be called before the request is processed. Then _verify_request()
	 * may be called later, after data needed to verify the nonce is retrieved.
	 *
	 * @since 1.7.0
	 */
	private function _verify_user_can() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action. Maybe you have been logged out?', 'wordpoints' ) ) );
		}
	}

	/**
	 * Verify the current request.
	 *
	 * Checks that the request is accompanied by a valid nonce for the action.
	 *
	 * @since 1.7.0
	 *
	 * @param string $action The action the nonce should be for.
	 */
	private function _verify_request( $action ) {

		if (
			empty( $_POST['nonce'] )
			|| ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $action )
		) {
			wp_send_json_error(
				array( 'message' => __( 'Your security token for this action has expired. Refresh the page and try again.', 'wordpoints' ) )
			);
		}
	}

	/**
	 * Get the rank group this request is made for.
	 *
	 * @since 1.7.0
	 *
	 * @return WordPoints_Rank_Group The object of the rank group being modified/retrieved.
	 */
	private function _get_group() {

		if ( ! isset( $_POST['group'] ) ) {
			$this->_unexpected_error( 'group' );
		}

		$group = WordPoints_Rank_Groups::get_group( wp_unslash( $_POST['group'] ) );

		if ( ! $group ) {
			wp_send_json_error( array( 'message' => __( 'The rank group passed to the server is invalid. Perhaps it has been deleted. Try reloading the page.', 'wordpoints' ) ) );
		}

		return $group;
	}

	/**
	 * Get the rank this request is for.
	 *
	 * @since 1.7.0
	 *
	 * @return WordPoints_Rank The rank that this request relates to.
	 */
	private function _get_rank() {

		if ( ! isset( $_POST['id'] ) ) {
			$this->_unexpected_error( 'id' );
		}

		$rank = wordpoints_get_rank( wordpoints_int( $_POST['id'] ) );

		if ( ! $rank ) {
			wp_send_json_error( array( 'message' => __( 'The rank ID passed to the server is invalid. Perhaps it has been deleted. Try reloading the page.', 'wordpoints' ) ) );
		}

		return $rank;
	}

	/**
	 * Get the new name of the rank from the request.
	 *
	 * @since 1.7.0
	 *
	 * @return string The new name for this rank.
	 */
	private function _get_rank_name() {

		$empty_name = true;

		if ( ! empty( $_POST['name'] ) ) {
			$name = sanitize_text_field( trim( wp_unslash( $_POST['name'] ) ) );

			if ( ! empty( $name ) ) {
				$empty_name = false;
			}
		}

		if ( $empty_name ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter a name for this rank.', 'wordpoints' ),
					'field'   => 'name',
				)
			);
		}

		return $name;
	}

	/**
	 * Get the slug for the rank type of this rank from the request.
	 *
	 * @since 1.7.0
	 *
	 * @reutrn string The rank type specified in the request.
	 */
	private function _get_rank_type() {

		if ( empty( $_POST['type'] ) ) {
			$this->_unexpected_error( 'type' );
		}

		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) );

		if ( ! WordPoints_Rank_Types::is_type_registered( $type ) ) {
			wp_send_json_error( array( 'message' => __( 'That rank type was not recognized. It may no longer be available. Try reloading the page.', 'wordpoints' ) ) );
		}

		$this->rank_type = WordPoints_Rank_Types::get_type( $type );

		return $this->rank_type;
	}

	/**
	 * Get the position of the rank in the group.
	 *
	 * @since 1.7.0
	 *
	 * @return int The position of the rank in the group.
	 */
	private function _get_rank_position() {

		if (
			! isset( $_POST['order'] )
			|| false === wordpoints_int( $_POST['order'] )
		) {
			$this->_unexpected_error( 'order' );
		}

		return $_POST['order'];
	}

	/**
	 * Get the metadata for the rank.
	 *
	 * @since 1.7.0
	 *
	 * @return array The metadata for the rank.
	 */
	private function _get_rank_meta() {

		return array_intersect_key(
			wp_unslash( $_POST )
			, $this->rank_type->get_meta_fields()
		);
	}

	/**
	 * Send the rank or an error back to the user based on the result.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed  $result The result of the action.
	 * @param string $action The action being performed: 'create' or 'update'.
	 */
	private function _send_json_result( $result, $action ) {

		if ( ! $result ) {

			if ( 'create' === $action ) {
				$message = __( 'There was an error adding the rank. Please try again.', 'wordpoints' );
			} else {
				$message = __( 'There was an error updating the rank. Please try again.', 'wordpoints' );
			}

			wp_send_json_error( array( 'message' => $message ) );

		} elseif ( is_wp_error( $result ) ) {

			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
					'data'    => $result->get_error_data()
				)
			);
		}

		$data = null;

		if ( 'create' === $action ) {
			$data = self::_prepare_rank( wordpoints_get_rank( $result ) );
		}

		wp_send_json_success( $data );
	}

	//
	// Private Static Functions.
	//

	/**
	 * Prepare a rank for return to the user.
	 *
	 * @since 1.7.0
	 *
	 * @param WordPoints_Rank $rank The rank object.
	 *
	 * @return array The rank data extracted into an array.
	 */
	private static function _prepare_rank( $rank ) {

		$name = $rank->name;
		if ( empty( $name ) ) {
			$name = __( '(no title)', 'wordpoints' );
		}

		$order = WordPoints_Rank_Groups::get_group( $rank->rank_group )
			->get_rank_position( $rank->ID );

		$prepared_rank = array(
			'id'    => $rank->ID,
			'order' => $order,
			'name'  => $name,
			'type'  => $rank->type,
			'nonce' => wp_create_nonce(
				"wordpoints_update_rank|{$rank->rank_group}|{$rank->ID}"
			),
			'delete_nonce' => wp_create_nonce(
				"wordpoints_delete_rank|{$rank->rank_group}|{$rank->ID}"
			),
		);

		$rank_type = WordPoints_Rank_Types::get_type( $rank->type );

		foreach ( $rank_type->get_meta_fields() as $field => $data ) {
			$prepared_rank[ $field ] = $rank->{$field};
		}

		return $prepared_rank;
	}
}
new WordPoints_Ranks_Admin_Screen_Ajax;

// EOF
