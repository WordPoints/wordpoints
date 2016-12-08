<?php

/**
 * Tests activating a component.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Activate a component' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_configure&tab=components' );
$I->see( 'Components', '.nav-tab-active' );
$I->click( 'Activate', '[name=wordpoints_components_form_ranks]' );
$I->see( 'Components', '.nav-tab-active' );
$I->see( 'Component “Ranks” activated!', '.notice-success' );
$I->seeElement(
	'[name=wordpoints_components_form_ranks] [type=submit]'
	, array( 'value' => 'Deactivate' )
);

// EOF
