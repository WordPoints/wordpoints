<?php

/**
 * Admin screen class.
 *
 * @package WordPoints\Administration
 * @since 2.1.0
 */

/**
 * Bootstrap for displaying an administration screen.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Admin_Screen {

	/**
	 * The screen's ID.
	 *
	 * Defaults to the ID of the current screen.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The WP Screen object for this screen.
	 *
	 * @since 2.1.0
	 *
	 * @var WP_Screen
	 */
	protected $wp_screen;

	/**
	 * The tabs to display on this screen.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	protected $tabs;

	/**
	 * @since 2.1.0
	 */
	public function __construct() {

		$this->wp_screen = get_current_screen();
		$this->id        = $this->wp_screen->id;

		$this->hooks();
	}

	/**
	 * Hook to actions and filters.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		/* Load the JavaScript needed for the settings screen. */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'footer_scripts' ) );
	}

	/**
	 * Enqueue needed scripts and styles.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_scripts() {}

	/**
	 * Enqueue or print footer scripts and styles.
	 *
	 * @since 2.1.0
	 */
	public function footer_scripts() {}

	/**
	 * Perform actions while the screen is being loaded, before it is displayed.
	 *
	 * @since 2.1.0
	 */
	public function load() {}

	/**
	 * Display the screen.
	 *
	 * @since 2.1.0
	 */
	public function display() {

		?>

		<div class="wrap">

			<h1><?php echo esc_html( $this->get_title() ); ?></h1>

			<?php settings_errors(); ?>

			<?php if ( ! empty( $this->tabs ) ) : ?>
				<?php wordpoints_admin_show_tabs( $this->tabs, false ); ?>
			<?php endif; ?>

			<?php $this->display_content(); ?>

		</div><!-- .wrap -->

		<?php
	}

	/**
	 * Get the screen's title heading.
	 *
	 * @since 2.1.0
	 *
	 * @return string The screen title.
	 */
	abstract protected function get_title();

	/**
	 * Display the screen's contents.
	 *
	 * @since 2.1.0
	 */
	abstract protected function display_content();
}

// EOF
