<?php

/**
 * Tests canceling creating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Create a points reaction, but change my mind' );
$I->hadCreatedAPointsType();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->click( 'Add New', '#points-user_register .controls' );
$I->waitForNewReaction();
$I->seeElement( '#points-user_register .add-reaction[disabled]' );
$I->fillField( 'description', 'Registering.' );
$I->fillField( 'log_text', 'Registration.' );
$I->fillField( 'points', '10' );
$I->click( 'Cancel', '#points-user_register .wordpoints-hook-reaction .actions' );
$I->cantSeeElement( '#points-user_register .wordpoints-hook-reaction' );
$I->canSeeElement( '#points-user_register .add-reaction:not([disabled])' );

// EOF
