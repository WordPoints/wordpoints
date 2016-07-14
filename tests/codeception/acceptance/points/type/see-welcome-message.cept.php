<?php

/**
 * Tests that a welcome message is displayed if no points types have been created.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( "See a welcome message if I haven't created any points types yet" );
$I->amLoggedInAsAdminOnPage( 'wp-admin/' );
$I->see( 'Welcome to WordPoints! Get started by creating a points type.', '.notice-info' );
$I->seeLink( 'creating a points type' );
$I->click( 'creating a points type' );
$I->see( 'Points Types' );
$I->see( 'Add New' );
$I->see( 'Settings' );
$I->canSeeInFormFields(
	'#settings form'
	, array(
		'points-name' => '',
		'points-prefix' => '',
		'points-suffix' => '',
	)
);

// EOF
