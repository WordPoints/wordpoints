<?php

/**
 * Tests updating a points type.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update a points type' );
$I->hadCreatedAPointsType();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Points Types' );
$I->see( 'Points', '.nav-tab-active' );
$I->see( 'Slug: points' );
$I->canSeeInFormFields(
	'#settings form'
	, array(
		'points-name'   => 'Points',
		'points-prefix' => '',
		'points-suffix' => '',
	)
);
$I->fillField( 'points-name', 'Credits' );
$I->fillField( 'points-prefix', '$' );
$I->fillField( 'points-suffix', 'credits' );
$I->click( 'Save' );
$I->seeSuccessMessage();
$I->see( 'Points Types' );
$I->see( 'Credits', '.nav-tab-active' );
$I->see( 'Slug: points' );
$I->canSeeInFormFields(
	'#settings form'
	, array(
		'points-name'   => 'Credits',
		'points-prefix' => '$',
		'points-suffix' => 'credits',
	)
);

// EOF
