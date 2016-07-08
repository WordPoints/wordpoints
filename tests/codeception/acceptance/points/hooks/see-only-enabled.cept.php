<?php

/**
 * Tests that disabled hooks aren't shown on the Points Hooks admins screen.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'See only enabled hooks on the Points Hooks screen' );
$I->haveSiteWithDisabledLegacyPointsHooks(
	array( 'wordpoints_post_points_hook' => true )
);
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_hooks' );
$I->dontSee( 'Post Publish', '.hook-title' );

// EOF
