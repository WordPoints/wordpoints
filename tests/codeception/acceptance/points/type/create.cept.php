<?php

/**
 * Tests creating a points type.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Create a points type' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Points Types' );
$I->see( 'Add New', '.nav-tab-active' );
$I->dontSee( 'Events' );
$I->see( 'Settings' );
$I->fillField( 'points-name', 'Points' );
$I->fillField( 'points-prefix', '$' );
$I->fillField( 'points-suffix', 'pts.' );
$I->click( 'Save' );
$I->seeSuccessMessage();
$I->see( 'Points Types' );
$I->see( 'Points', '.nav-tab-active' );
$I->see( 'Events' );
$I->see( 'Settings' );
$I->see( 'Slug: points' );
$I->canSeeInFormFields(
	'#settings form'
	, array(
		'points-name' => 'Points',
		'points-prefix' => '$',
		'points-suffix' => 'pts.',
	)
);
$I->click( 'Create Reactions' );
$I->see( 'Points Types' );
$I->see( 'Points', '.nav-tab-active' );
$I->see( 'Example reactions created successfully!' );
$I->see( '(Disabled) Registering with the site' );

// EOF
