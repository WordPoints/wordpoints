<?php

/**
 * Tests cancelling creating a condition for a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.3
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Cancel creating a condition for a points reaction' );
$the_reaction = $I->hadCreatedAPointsReaction();
$reaction     = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( (string) $reaction );
$condition = $reaction->addCondition( array( 'User » Roles', 'Contains' ) );
$reaction->cancel();
$I->cantSeeElement( (string) $condition );

// EOF
