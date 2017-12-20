<?php

/**
 * Tests manually increasing a user's points on the User Points screen in the admin.
 *
 * @package WordPoints\Codeception
 * @since 2.5.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( "Manually add to a user's points." );
$I->hadCreatedAPointsType();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_user_points' );
$I->see( 'User Points' );
$I->see( '0', '#user-1 .column-points' );
$I->fillField( 'wordpoints_update_user_points-1', 5 );
$I->click( 'Add' );
$I->seeJQueryDialog();
$I->see( 'User: admin' );
$I->see( 'Total: 0 + 5 = 5' );
$I->fillField( 'Reason:', 'Testing.' );
$I->click( 'Add Points' );
$I->waitForJqueryAjax();
$I->see( '5', '#user-1 .column-points' );

// EOF
