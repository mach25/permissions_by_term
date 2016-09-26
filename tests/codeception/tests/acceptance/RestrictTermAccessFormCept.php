<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Check term access restriction on a node edit form.');
$I->signInWithEditor();
$I->doNotSeeErrors();
$I->comment('Check if I am signed in.');
$I->see('Edit');
$I->see('View');
$I->see('Member for');
$I->comment('I check if I can use a restricted taxonomy term.');
$I->amOnPage('node/add/article');
$I->fillField('field_tags[target_id]', 'No');
$I->wait(1);
$I->dontSee('No access');
$I->comment('I check if I can use an not restricted taxonomy term.');
$I->fillField('field_tags[target_id]', 'Everybody');
$I->wait(1);
$I->see('Everybody has access');
$I->doNotSeeErrors();
