<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that the access will be removed only for one term.');
$I->signInWithAdmin();
$I->comment('I am adding access for admin to "Everybody has access" term.');
$I->amOnPage('taxonomy/term/2/edit');
$I->doNotSeeErrors();
$I->fillField('access[user]', 'admin');
$I->checkOption('#edit-access-role-administrator');
$I->makeScreenshot('debug');
$I->click('Save');
$I->comment('Remove the permission again.');
$I->amOnPage('taxonomy/term/2/edit');
$I->fillField('access[user]', '');
$I->uncheckOption('#edit-access-role-administrator');
$I->click('Save');
$I->amOnPage('taxonomy/term/1/edit');
$I->seeInField('access[user]', 'admin (1)');
$I->seeCheckboxIsChecked('#edit-access-role-administrator');
