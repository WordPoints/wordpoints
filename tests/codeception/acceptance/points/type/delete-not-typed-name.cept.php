<?php

/**
 * Tests deleting a points type when not typing the name to confirm.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Delete a points type, but did not type the name to confirm' );
$I->hadCreatedAPointsType();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Points Types' );
$I->see( 'Points', '.nav-tab-active' );
$I->see( 'Slug: points' );
$I->canSeeInFormFields(
	'#settings form'
	, array(
		'points-name' => 'Points',
		'points-prefix' => '',
		'points-suffix' => '',
	)
);
$I->click( 'Delete' );
$I->seeJQueryDialog( 'Are you sure?' );
$I->click( 'Delete', '.wordpoints-delete-type-dialog' );
$I->see( 'Points', '.nav-tab-active' );
$I->canSeePointsTypeInDB( 'points' );

// EOF
