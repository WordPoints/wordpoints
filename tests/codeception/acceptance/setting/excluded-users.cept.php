<?php

/**
 * Tests setting the Excluded Users setting.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Exclude some users' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_configure' );
$I->see( 'General Settings', '.nav-tab-active' );
$I->fillField( 'excluded_users', '5, 45, l, 7lkj, aa  s,, ,alkj,5' );
$I->click( 'Save Changes', '#wordpoints-settings' );
$I->see( 'General Settings', '.nav-tab-active' );
$I->see( 'Settings updated.', '.notice-success' );
$I->seeInField( 'excluded_users', '5, 45' );

// EOF
