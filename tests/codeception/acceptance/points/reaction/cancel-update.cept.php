<?php

/**
 * Tests canceling updating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update a points reaction, but change my mind' );
$I->hadCreatedAPointsReaction();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Test description.', '#points-user_register .wordpoints-hook-reaction .title' );
$I->click( 'Edit', '#points-user_register .wordpoints-hook-reaction' );
$I->canSeeInFormFields(
	'#points-user_register .wordpoints-hook-reaction form'
	, array(
		'description' => 'Test description.',
		'log_text' => 'Test log text.',
		'points' => '10',
	)
);
$I->cantSee( 'Edit', '#points-user_register .wordpoints-hook-reaction .view' );
$I->canSee( 'Close', '#points-user_register .wordpoints-hook-reaction .view' );
$I->canSee( 'Close', '#points-user_register .wordpoints-hook-reaction .actions' );
$I->canSee( 'Save', '#points-user_register .wordpoints-hook-reaction .actions :disabled' );
$I->cantSee( 'Cancel', '#points-user_register .wordpoints-hook-reaction .actions' );
$I->fillField( 'description', 'Registering.' );
$I->fillField( 'log_text', 'Registration.' );
$I->fillField( 'points', '50' );
$I->cantSee( 'Edit', '#points-user_register .wordpoints-hook-reaction .view' );
$I->cantSee( 'Close', '#points-user_register .wordpoints-hook-reaction .view' );
$I->cantSee( 'Close', '#points-user_register .wordpoints-hook-reaction .actions' );
$I->canSee( 'Save', '#points-user_register .wordpoints-hook-reaction .actions :not(:disabled)' );
$I->click( 'Cancel', '#points-user_register .wordpoints-hook-reaction .actions' );
$I->canSeeInFormFields(
	'#points-user_register .wordpoints-hook-reaction form'
	, array(
		'description' => 'Test description.',
		'log_text' => 'Test log text.',
		'points' => '10',
	)
);
$I->canSee( 'Close', '#points-user_register .wordpoints-hook-reaction .view' );
$I->canSee( 'Close', '#points-user_register .wordpoints-hook-reaction .actions' );
$I->canSee( 'Save', '#points-user_register .wordpoints-hook-reaction .actions :disabled' );
$I->cantSee( 'Cancel', '#points-user_register .wordpoints-hook-reaction .actions' );

// EOF
