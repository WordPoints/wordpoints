<?php

/**
 * Ajax callbacks for hook reactions.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/**
 * Respond to Ajax requests from the hooks admin screen.
 *
 * This code is part of a class mainly to keep it DRY by consolidating common code
 * into the private methods.
 *
 * @since 2.1.0
 */
class WordPoints_Admin_Ajax_Hooks {

	//
	// Private Vars.
	//

	/**
	 * The reaction store that the reactions are being saved for.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_StoreI
	 */
	protected $reaction_store;

	/**
	 * The slug of the reaction store that the reactions are being saved for.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $reaction_store_slug;

	//
	// Public Static Functions.
	//

	/**
	 * Prepare a hook reaction for return to the user.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI $reaction The reaction object.
	 *
	 * @return array The hook reaction data extracted into an array.
	 */
	public static function prepare_hook_reaction( WordPoints_Hook_ReactionI $reaction ) {

		return array_merge(
			$reaction->get_all_meta()
			, array(
				'id'             => $reaction->get_id(),
				'event'          => $reaction->get_event_slug(),
				'reaction_store' => $reaction->get_store_slug(),
				'nonce'          => self::get_update_nonce( $reaction ),
				'delete_nonce'   => self::get_delete_nonce( $reaction ),
			)
		);
	}

	/**
	 * Get a nonce for creating new reactions.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Reaction_StoreI $reaction_store The reactor the nonce
	 *                                                        will be used to create
	 *                                                        new reactions for.
	 *
	 * @return string A nonce for creating a new reaction to this reactor.
	 */
	public static function get_create_nonce(
		WordPoints_Hook_Reaction_StoreI $reaction_store
	) {

		return wp_create_nonce( self::get_create_nonce_action( $reaction_store ) );
	}

	/**
	 * Get the action for a nonce for creating new reactions.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Reaction_StoreI $reaction_store The reactor the nonce
	 *                                                        will be used to create
	 *                                                        new reactions for.
	 *
	 * @return string The nonce action for creating a new reaction to this reactor.
	 */
	public static function get_create_nonce_action(
		WordPoints_Hook_Reaction_StoreI $reaction_store
	) {

		return 'wordpoints_create_hook_reaction|' . $reaction_store->get_slug()
			. '|' . wordpoints_hooks()->get_current_mode()
			. '|' . wp_json_encode( $reaction_store->get_context_id() );
	}

	/**
	 * Get a nonce for updating a reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI $reaction The reaction that will be updated.
	 *
	 * @return string A nonce for updating this reaction.
	 */
	public static function get_update_nonce( WordPoints_Hook_ReactionI $reaction ) {

		return wp_create_nonce( self::get_update_nonce_action( $reaction ) );
	}

	/**
	 * Get the action for a nonce for updating a reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI $reaction The reaction that will be updated.
	 *
	 * @return string The action for a nonce for updating this reaction.
	 */
	public static function get_update_nonce_action(
		WordPoints_Hook_ReactionI $reaction
	) {
		return 'wordpoints_update_hook_reaction|'
			. wp_json_encode( $reaction->get_guid() );
	}

	/**
	 * Get a nonce for deleting a reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI $reaction The reaction that will be deleted.
	 *
	 * @return string A nonce for deleting this reaction.
	 */
	public static function get_delete_nonce( WordPoints_Hook_ReactionI $reaction ) {

		return wp_create_nonce( self::get_delete_nonce_action( $reaction ) );
	}

	/**
	 * Get the action for a nonce for deleting a reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI $reaction The reaction that will be deleted.
	 *
	 * @return string The action for a nonce for deleting this reaction.
	 */
	public static function get_delete_nonce_action(
		WordPoints_Hook_ReactionI $reaction
	) {
		return 'wordpoints_delete_hook_reaction|'
			. wp_json_encode( $reaction->get_guid() );
	}

	//
	// Public Methods.
	//

	/**
	 * Hook up the methods to the Ajax request actions when the class is constructed.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hook the callback methods to the Ajax actions.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action(
			'wp_ajax_wordpoints_admin_create_hook_reaction'
			, array( $this, 'create_hook_reaction' )
		);

		add_action(
			'wp_ajax_wordpoints_admin_update_hook_reaction'
			, array( $this, 'update_hook_reaction' )
		);

		add_action(
			'wp_ajax_wordpoints_admin_delete_hook_reaction'
			, array( $this, 'delete_hook_reaction' )
		);
	}

	/**
	 * Handle an Ajax request to create a new hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function create_hook_reaction() {

		$this->verify_user_can();

		$reaction_store = $this->get_reaction_store();

		$this->verify_request( $this->get_create_nonce_action( $reaction_store ) );

		$reaction = $reaction_store->create_reaction( $this->get_data() );

		$this->send_json_result( $reaction, 'create' );
	}

	/**
	 * Handle an Ajax request to update a hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function update_hook_reaction() {

		$this->verify_user_can();

		$reaction_store = $this->get_reaction_store();
		$reaction       = $this->get_reaction();

		$this->verify_request( $this->get_update_nonce_action( $reaction ) );

		$reaction = $reaction_store->update_reaction(
			$reaction->get_id()
			, $this->get_data()
		);

		$this->send_json_result( $reaction, 'update' );
	}

	/**
	 * Handle Ajax requests to delete a hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function delete_hook_reaction() {

		$this->verify_user_can();

		$reaction_store = $this->get_reaction_store();
		$reaction       = $this->get_reaction();

		$this->verify_request( $this->get_delete_nonce_action( $reaction ) );

		$result = $reaction_store->delete_reaction( $reaction->get_id() );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'There was an error deleting the reaction. Please try again.', 'wordpoints' ) ) );
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
	 * @since 2.1.0
	 *
	 * @param string $debug_context Context sent with the message (for debugging).
	 */
	private function unexpected_error( $debug_context ) {

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
	 * This should be called before the request is processed. Then verify_request()
	 * may be called later, after data needed to verify the nonce is retrieved.
	 *
	 * @since 2.1.0
	 */
	private function verify_user_can() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, you are not allowed to perform this action. Maybe you have been logged out?', 'wordpoints' ) ) );
		}
	}

	/**
	 * Verify the current request.
	 *
	 * Checks that the request is accompanied by a valid nonce for the action.
	 *
	 * @since 2.1.0
	 *
	 * @param string $action The action the nonce should be for.
	 */
	private function verify_request( $action ) {

		if (
			empty( $_POST['nonce'] )
			|| ! wordpoints_verify_nonce( 'nonce', $action, null, 'post' )
		) {
			wp_send_json_error(
				array( 'message' => __( 'Your security token for this action has expired. Refresh the page and try again.', 'wordpoints' ) )
			);
		}
	}

	/**
	 * Get the hook reactor this request is made for.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_Reaction_StoreI The object of the reaction store.
	 */
	protected function get_reaction_store() {

		if ( ! isset( $_POST['reaction_store'] ) ) { // WPCS: CSRF OK.
			$this->unexpected_error( 'reaction_store' );
		}

		$reactor_slug = sanitize_key( $_POST['reaction_store'] ); // WPCS: CSRF OK.

		$reaction_store = wordpoints_hooks()->get_reaction_store( $reactor_slug );

		if ( ! $reaction_store instanceof WordPoints_Hook_Reaction_StoreI ) {
			$this->unexpected_error( 'reaction_store_invalid' );
		}

		$this->reaction_store_slug = $reactor_slug;
		$this->reaction_store      = $reaction_store;

		return $reaction_store;
	}

	/**
	 * Get the hook reaction this request is for.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_ReactionI The hook reaction that this request relates to.
	 */
	protected function get_reaction() {

		if ( ! isset( $_POST['id'] ) ) { // WPCS: CSRF OK.
			$this->unexpected_error( 'id' );
		}

		$reaction = $this->reaction_store->get_reaction(
			wordpoints_int( $_POST['id'] ) // WPCS: CSRF OK.
		);

		if ( ! $reaction ) {
			wp_send_json_error( array( 'message' => __( 'The reaction ID passed to the server is invalid. Perhaps it has been deleted. Try reloading the page.', 'wordpoints' ) ) );
		}

		return $reaction;
	}

	/**
	 * Get the hook reaction's settings.
	 *
	 * @since 2.1.0
	 *
	 * @return array The hook reaction's settings.
	 */
	protected function get_data() {

		$data = wp_unslash( $_POST ); // WPCS: CSRF OK.

		unset( $data['id'], $data['action'], $data['nonce'], $data['reaction_store'] );

		return $data;
	}

	/**
	 * Send the hook reaction or an error back to the user based on the result.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed  $result The result of the action.
	 * @param string $action The action being performed: 'create' or 'update'.
	 */
	private function send_json_result( $result, $action ) {

		if ( ! $result ) {

			if ( 'create' === $action ) {
				$message = __( 'There was an error adding the reaction. Please try again.', 'wordpoints' );
			} else {
				$message = __( 'There was an error updating the reaction. Please try again.', 'wordpoints' );
			}

			wp_send_json_error( array( 'message' => $message ) );

		} elseif ( $result instanceof WordPoints_Hook_Reaction_Validator ) {

			wp_send_json_error( array( 'errors' => $result->get_errors() ) );
		}

		$data = null;

		if ( 'create' === $action ) {
			$data = self::prepare_hook_reaction( $result );
		}

		wp_send_json_success( $data );
	}
}

// EOF
