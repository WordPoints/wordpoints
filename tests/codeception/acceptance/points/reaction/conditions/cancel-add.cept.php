<?php

/**
 * Tests adding a condition for a points reaction after hitting Cancel.
 *
 * @package WordPoints\Codeception
 * @since 2.1.3
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Add a condition for a points reaction after cancelling edits' );
$the_reaction = $I->hadCreatedAPointsReaction();
$reaction     = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( (string) $reaction );
$reaction->edit();
$condition = $reaction->addCondition( array( 'User » Roles', 'Contains' ) );
$reaction->cancel();
$I->cantSeeElement( (string) $condition );
$condition = $reaction->addCondition( array( 'User » Roles', 'Contains' ) );
$I->canSeeElement( (string) $condition );

// EOF
