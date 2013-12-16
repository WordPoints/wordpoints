<?php

/**
 * Register scripts and styles.
 *
 * These are all registered here so they may be easily enqueued when needed.
 *
 * Component-specific styles/scripts are enqueued separately by their respective
 * components.
 *
 * @package WordPoints
 * @since 1.0.0
 */

/**
 * Register scripts and styles.
 *
 * It is run on both the front and back end with a priority of 5, so the scripts will
 * all be registered when we want to enqueue them, usually on the default priority of
 * 10.
 *
 * @since 1.0.0
 *
 * @action wp_enqueue_scripts    5 Front-end scripts enqueued.
 * @action admin_enqueue_scripts 5 Admin scripts enqueued.
 */
function wordpoints_register_scripts() {

	$assets_url = plugins_url( 'assets/', dirname( __FILE__ ) );

	// - JS

	wp_register_script(
		'wordpoints-datatables'
		,$assets_url . 'js/jquery.datatables.min.js'
		,array( 'jquery' )
		,'1.9.4'
	);

	wp_register_script(
		'wordpoints-datatables-init'
		,$assets_url . 'js/datatables-init.js'
		,array( 'wordpoints-datatables' )
		,WORDPOINTS_VERSION
	);

	// - CSS

	wp_register_style(
		'wordpoints-datatables'
		,$assets_url . 'css/datatables.css'
		,null
		,WORDPOINTS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'wordpoints_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_register_scripts', 5 );

/**
 * Enqueue scripts for datatables.
 *
 * It is recommended that you use this function rather than calling the enqueue
 * functions directly, for forward compatibility.
 *
 * @since 1.0.0
 * @since 1.0.1 'oLanguage' datatables argument may now be overridden.
 *
 * @param string $for  The selector for the the HTML elements to apply the JS to.
 * @param array  $args Arguments for the datatables constructor.
 */
function wordpoints_enqueue_datatables( $for = null, array $args = array() ) {

	global $wp_locale;

	wp_enqueue_style( 'wordpoints-datatables' );
	wp_enqueue_script( 'wordpoints-datatables' );
	wp_enqueue_script( 'wordpoints-datatables-init' );

	if ( $for ) {

		if ( ! $args ) {

			$args = array(
				'sPaginationType' => 'full_numbers',
				'bStateSave'      => false,
				'bSort'           => false,
				'aoColumns'       => array(
					array(),
					array(),
					array(),
					array( 'bSearchable' => false ),
				),
			);
		}

		$lang_defaults = array(
			'sEmptyTable'     => _x( 'No data available in table', 'datatable', 'wordpoints' ),
			/* translators: _START_, _END_, and _TOTAL_ will be replaced with the correct values. */
			'sInfo'           => _x( 'Showing _START_ to _END_ of _TOTAL_ entries', 'datatable', 'wordpoints' ),
			'sInfoEmpty'      => _x( 'Showing 0 to 0 of 0 entries', 'datatable', 'wordpoints' ),
			/* translators: _MAX_ will be replaced with the total. */
			'sInfoFiltered'   => _x( '(filtered from _MAX_ total entries)', 'datatable', 'wordpoints' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => $wp_locale->number_format['thousands_sep'],
			/* translators: _MENU_ will be replaced with a dropdown menu. */
			'sLengthMenu'     => _x( 'Show _MENU_ entries', 'datatable', 'wordpoints' ),
			'sLoadingRecords' => _x( 'Loading...', 'datatable', 'wordpoints' ),
			'sProcessing'     => _x( 'Processing...', 'datatable', 'wordpoints' ),
			'sSearch'         => _x( 'Search:', 'datatable', 'wordpoints' ),
			'sZeroRecords'    => _x( 'No matching records found', 'datatable', 'wordpoints' ),
			'oPaginate' => array(
				'sFirst'    => _x( 'First', 'datatable', 'wordpoints' ),
				'sLast'     => _x( 'Last', 'datatable', 'wordpoints' ),
				'sNext'     => _x( 'Next', 'datatable', 'wordpoints' ),
				'sPrevious' => _x( 'Previous', 'datatable', 'wordpoints' ),
			),
		);

		if ( isset( $args['oLanguage'] ) ) {

			$args['oLanguage'] = array_merge( $lang_defaults, $args['oLanguage'] );
			$args['oLanguage']['oPaginate'] = array_merge( $lang_defaults['oPaginate'], $args['oLanguage']['oPaginate'] );

		} else {

			$args['oLanguage'] = $lang_defaults;
		}

		wp_localize_script( 'wordpoints-datatables-init', 'WordPointsDataTable', array( 'selector' => $for, 'args' => $args ) );
	}
}

// end of file /includes/scripts.php
