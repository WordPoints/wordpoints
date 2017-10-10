<?php

/**
 * Tests canceling creating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

use WordPoints\Tests\Codeception\Element\ReactionGroup;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Create a points reaction, but change my mind' );
$I->hadCreatedAPointsType();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );

$reactions = new ReactionGroup( $I, 'points', 'user_register' );
$reaction  = $reactions->addNew();

$I->seeElement( "{$reactions} .add-reaction[disabled]" );
$I->fillField( "{$reaction} [name=description]", 'Registering.' );
$I->fillField( "{$reaction} [name=log_text]", 'Registration.' );
$I->fillField( "{$reaction} [name=points]", '10' );

$reaction->cancel();

$I->cantSeeElement( "{$reaction}" );
$I->canSeeElement( "{$reactions} .add-reaction:not([disabled])" );

// EOF
