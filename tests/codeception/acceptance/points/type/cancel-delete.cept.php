<?php

/**
 * Tests canceling deleting a points type.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Delete a points type, but change my mind' );
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
$I->click( 'Delete' );
$I->seeJQueryDialog( 'Are you sure?' );
$I->click( 'Cancel', '.wordpoints-delete-type-dialog' );
$I->see( 'Points', '.nav-tab-active' );
$I->canSeePointsTypeInDB( 'points' );

// EOF
