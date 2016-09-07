<?php

/**
 * Tests deactivating a component.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Deactivate a component' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_configure&tab=components' );
$I->see( 'Components', '.nav-tab-active' );
$I->click( 'Deactivate', '[name=wordpoints_components_form_points]' );
$I->see( 'Components', '.nav-tab-active' );
$I->see( 'Component “Points” deactivated!', '.notice-success' );
$I->seeElement(
	'[name=wordpoints_components_form_points] [type=submit]'
	, array( 'value' => 'Activate' )
);

// EOF
