<?php

/**
 * Tests manually decreasing a user's points on the User Points screen in the admin.
 *
 * @package WordPoints\Codeception
 * @since 2.5.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( "Manually subtract from a user's points." );
$I->hadCreatedAPointsType();
wordpoints_add_points( 1, 10, 'points', 'test' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_user_points' );
$I->see( 'User Points' );
$I->see( '10', '#user-1 .column-points' );
$I->fillField( 'wordpoints_update_user_points-1', 3 );
$I->click( 'Subtract' );
$I->seeJQueryDialog();
$I->see( 'User: admin' );
$I->see( 'Total: 10 - 3 = 7' );
$I->fillField( 'Reason:', 'Testing.' );
$I->click( 'Subtract Points' );
$I->waitForJqueryAjax();
$I->see( '7', '#user-1 .column-points' );

// EOF
