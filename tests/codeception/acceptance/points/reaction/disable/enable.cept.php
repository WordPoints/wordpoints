<?php

/**
 * Tests using enabling a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.3.0
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Enable a points reaction' );
$the_reaction = $I->hadCreatedAPointsReaction( array( 'disable' => true ) );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );

$reaction = new Reaction( $I, $the_reaction );

$I->waitForElement( (string) $reaction );
$I->see( '(Disabled)', $reaction . '.title' );
$reaction->edit();

$I->see( 'Disable', (string) $reaction );
$I->canSeeCheckboxIsChecked( $reaction . '[name=disable]' );
$I->uncheckOption( $reaction . '[name=disable]' );
$reaction->save();

$I->cantSee( '(Disabled)', $reaction . '.title' );

// EOF
