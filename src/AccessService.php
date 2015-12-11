<?php

/**
 * @file
 * Contains Drupal\permissions_by_term\AccessService.
 */

namespace Drupal\permissions_by_term;

use Drupal\Core\Database\Driver\mysql\Connection;
use \Drupal\Component\Utility\Tags;
use Drupal\Core\Form\FormState;

/**
 * Class AccessService.
 *
 * @package Drupal\permissions_by_term
 */
class AccessService implements AccessServiceInterface {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $oDatabase;
  protected $oFormState;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, FormState $oFormState) {
    $this->oDatabase = $database;
    $this->oFormState = $oFormState;
  }

  /**
   * Checks if the submitted users are existing. If an user isn't existing,
   * set an error message.
   *
   * @param array $aAllowedUsers An array with usernames.
   *
   * @return null
   */
  public function checkIfUsersExists() {

    $sAllowedUsers = $this->oFormState->getValue('access')['user'];
    $aAllowedUsers = Tags::explode($sAllowedUsers);

    foreach ($aAllowedUsers as $sUserName) {

      $aUserId = \Drupal::entityQuery('user')
        ->condition('name', $sUserName)
        ->execute();

      if (empty($aUserId)) {
        $this->oFormState->setErrorByName('access][user', t('The user %user_name does not exist.',
          array('%user_name' => $sUserName)));
      }

    }

  }


  public function saveTermPermissions() {

    //$iTid = $this->oFormState->getValue('access')

    $this->getUserPermissionsByTid(1);

  }

  private function getUserPermissionsByTid($iTid) {

    $result = $this->oDatabase->select('permissions_by_term_user')
      ->condition('tid', $iTid)
      ->fields(['tid', 'uid'])
      ->execute();

    $debug = true;

  }


}
