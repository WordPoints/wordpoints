<?php

/**
 * Base hook reaction storage class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Bootstrap for hook reaction storage methods.
 *
 * This class provides a common bootstrap for creating, updated, and deleting
 * reactions. It also provides a bootstrap for retrieving a single hook reaction.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Reaction_Store implements WordPoints_Hook_Reaction_StoreI {

	/**
	 * The slug of this store.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The slug of the mode that this store relates to.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $mode_slug;

	/**
	 * The slug of the contexts in which the reactions are stored.
	 *
	 * @since 2.1.0
	 *
	 * @see wordpoints_entities_get_current_context_id()
	 *
	 * @var string
	 */
	protected $context = 'site';

	/**
	 * The name of the class to use for reaction objects.
	 *
	 * The class must implement the WordPoints_Hook_ReactionI interface.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $reaction_class;

	/**
	 * @since 2.1.0
	 *
	 * @param string $slug      The slug of this store.
	 * @param string $mode_slug The slug of the mode this store relates to.
	 */
	public function __construct( $slug, $mode_slug ) {
		$this->slug      = $slug;
		$this->mode_slug = $mode_slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_mode_slug() {
		return $this->mode_slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_context_id() {
		return wordpoints_entities_get_current_context_id( $this->context );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reaction( $id ) {

		if ( ! $this->reaction_exists( $id ) ) {
			return false;
		}

		return new $this->reaction_class( $id, $this );
	}

	/**
	 * @since 2.1.0
	 */
	public function create_reaction( array $settings ) {
		return $this->create_or_update_reaction( $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function update_reaction( $id, array $settings ) {
		return $this->create_or_update_reaction( $settings, $id );
	}

	/**
	 * Create or update a reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The settings for the reaction.
	 * @param int   $id       The ID of the reaction to update, if updating.
	 *
	 * @return WordPoints_Hook_ReactionI|false|WordPoints_Hook_Reaction_Validator
	 *         The reaction object if created/updated successfully. False or a
	 *         validator instance if not.
	 */
	protected function create_or_update_reaction( array $settings, $id = null ) {

		$is_new = ! isset( $id );

		if ( ! $is_new && ! $this->reaction_exists( $id ) ) {
			return false;
		}

		$validator = new WordPoints_Hook_Reaction_Validator( $settings );
		$settings  = $validator->validate();

		if ( $validator->had_errors() ) {
			return $validator;
		}

		if ( $is_new ) {

			$id = $this->_create_reaction( $settings['event'] );

			if ( ! $id ) {
				return false;
			}
		}

		$reaction = $this->get_reaction( $id );

		$reaction->update_event_slug( $settings['event'] );

		unset( $settings['event'] );

		$reaction->update_meta( 'reactor', $settings['reactor'] );

		/** @var WordPoints_Hook_ReactorI $reactor */
		$reactor = wordpoints_hooks()->get_sub_app( 'reactors' )->get( $settings['reactor'] );
		$reactor->update_settings( $reaction, $settings );

		/** @var WordPoints_Hook_ExtensionI $extension */
		foreach ( wordpoints_hooks()->get_sub_app( 'extensions' )->get_all() as $extension ) {
			$extension->update_settings( $reaction, $settings );
		}

		/**
		 * A hook reaction is being saved.
		 *
		 * @param WordPoints_Hook_ReactionI $reaction The reaction object.
		 * @param array                     $settings The new settings for the reaction.
		 * @param bool                      $is_new   Whether the reaction was just now created.
		 */
		do_action( 'wordpoints_hook_reaction_save', $reaction, $settings, $is_new );

		return $reaction;
	}

	/**
	 * Create a reaction.
	 *
	 * The event slug is provided in case it is needed (for some storage methods it
	 * is).
	 *
	 * @since 2.1.0
	 *
	 * @param string $event_slug The slug of the event this reaction is for.
	 *
	 * @return int|false The reaction ID, or false if not created.
	 */
	abstract protected function _create_reaction( $event_slug );
}

// EOF
