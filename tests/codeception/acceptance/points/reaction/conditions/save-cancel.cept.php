<?php

/**
 * Tests cancelling editing a points reaction after saving a new condition.
 *
 * @package WordPoints\Codeception
 * @since 2.1.3
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Cancel editing a reaction after saving a new condition' );
$the_reaction = $I->hadCreatedAPointsReaction();
$reaction     = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( (string) $reaction );
$reaction->edit();
$condition = $reaction->addCondition( array( 'User » Roles', 'Contains' ) );
$reaction->save();
$I->canSeePointsReactionConditionInDB( $the_reaction );
$I->fillField( 'points', '100' );
$reaction->cancel();
$I->canSeeElement( (string) $condition );

// EOF
