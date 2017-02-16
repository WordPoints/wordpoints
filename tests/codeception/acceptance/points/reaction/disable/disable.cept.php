<?php

/**
 * Tests using disabling a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.3.0
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Disable a points reaction' );
$the_reaction = $I->hadCreatedAPointsReaction();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );

$reaction = new Reaction( $I, $the_reaction );

$I->waitForElement( (string) $reaction );
$reaction->edit();

$I->see( 'Disable', (string) $reaction );
$I->cantSeeCheckboxIsChecked( $reaction . '[name=disable]' );
$I->checkOption( $reaction . '[name=disable]' );
$reaction->save();

$I->see( '(Disabled)', $reaction . '.title' );

// EOF
