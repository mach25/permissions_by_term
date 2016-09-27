<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

  /**
   * Sign-in an user which inherits the editor role.
   */
    public function signInWithEditor() {
     $I = $this;
     $I->comment('Firstly I am going to sign in.');
     $I->amOnPage('user/login');
     $I->fillField('name', 'editor');
     $I->fillField('pass', 'eri4t4z');
     $I->see('Log in');
     $I->click('#edit-submit');
    }

    public function doNotSeeErrors() {
      $I = $this;
      $I->dontSee('Warning');
      $I->dontSee('Error');
    }

    public function signInWithAdmin() {
      $I = $this;
      $I->comment('Firstly I am going to sign in.');
      $I->amOnPage('user/login');
      $I->fillField('name', 'admin');
      $I->fillField('pass', 'eri4t4z');
      $I->see('Log in');
      $I->click('#edit-submit');
    }

}
