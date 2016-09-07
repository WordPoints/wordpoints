<?php

/**
 * Tests altering a user's points on their Profile screen in the admin.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( "Alter a user's points." );
$I->hadCreatedAPointsType();
$I->hadCreatedAPointsType(
	array(
		'name'   => 'Credits',
		'prefix' => '',
		'suffix' => 'c',
	)
);
$I->amLoggedInAsAdminOnPage( 'wp-admin/profile.php' );
$I->see( 'WordPoints' );
$I->canSeeInFormFields(
	'form#your-profile'
	, array(
		'wordpoints_points-points' => '0',
		'wordpoints_points-credits' => '0',
	)
);
$I->fillField( 'wordpoints_points-points', 5 );
$I->checkOption( '[name="wordpoints_points_set-points"]' );
$I->fillField( 'wordpoints_points-credits', 10 );
// We decide not to change the credits, so we don't check that box.
$I->fillField( 'wordpoints_set_reason', 'Testing.' );
$I->click( 'Update Profile' );
$I->canSeeInFormFields(
	'form#your-profile'
	, array(
		'wordpoints_points-points' => '5',
		'wordpoints_points-credits' => '0',
	)
);

// EOF
