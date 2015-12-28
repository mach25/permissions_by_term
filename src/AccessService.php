<?php

/**
 * @file
 * Contains Drupal\permissions_by_term\AccessService.
 */

namespace Drupal\permissions_by_term;

use Drupal\Core\Database\Driver\mysql\Connection;
use \Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Form\FormState;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\UserData;

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
  /**
   * @var \Drupal\Core\Form\FormState
   */
  protected $oFormState;

  /**
   * The term name for which the access is set.
   *
   * @var string
   */
  protected $sTermName;

  /**
   * The user ids which gain granted access.
   *
   * @var array
   */
  protected $aUserIdsGrantedAccess;

  /**
   * The term id.
   *
   * @var int
   */
  protected $iTermId;

  /**
   * AccessService constructor.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   * @param \Drupal\Core\Form\FormState $oFormState
   */
  public function __construct(Connection $database, FormState $oFormState) {
    $this->oDatabase  = $database;
    $this->oFormState = $oFormState;
    $this->sTermName = $this->oFormState->getValue('name')['0']['value'];

    $sValuesUserAccess = $this->oFormState->getValues()['access']['user'];
    $aUsernamesGrantedAccess = Tags::explode($sValuesUserAccess);
    $this->aUserIdsGrantedAccess = $this->getUserIdsByNames($aUsernamesGrantedAccess);

    $this->iTermId = $this->getTermId();
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

  /**
   * Adds and deletes term permissions by users and by user roles.
   *
   * @TODO: save term permissions by roles.
   * @TODO: write an unit test.
   * @return null
   */
  public function saveTermPermissions() {

    $this->saveTermPermissionsByUsers();

  }

  /**
   * Gets user term permissions by tid.
   *
   * @param $iTid
   *
   * @return mixed
   */
  public function getUserTermPermissionsByTid() {
    return $this->oDatabase->select('permissions_by_term_user', 'pu')
      ->condition('tid', $this->iTid)
      ->fields('pu', ['uid'])
      ->execute()
      ->fetchCol();
  }

  /**
   * Gets single user id by user name.
   *
   * @param $sUsername
   *
   * @return mixed
   */
  private function getUserIdByName($sUsername) {
    return $this->oDatabase->select('users_field_data', 'ufd')
      ->condition('name', $sUsername)
      ->fields('ufd', ['uid'])
      ->execute()
      ->fetchAssoc();
  }

  /**
   * Gets multiple user ids by user names.
   *
   * @param $aUserNames
   *
   * @return array
   */
  private function getUserIdsByNames($aUserNames) {
    $aUserIds = array();
    foreach ($aUserNames as $userName) {
      $iUserId    = $this->getUserIdByName($userName)['uid'];
      $aUserIds[] = $iUserId['uid'];
    }
    return $aUserIds;
  }

  /**
   * Deletes one term permission by user id.
   *
   * @param $iUserId
   *
   * @return null
   */
  private function deleteOneTermPermissionByUserId($iUserId) {
    $this->oDatabase->delete('permissions_by_term_user')
      ->condition('uid', [$iUserId])
      ->execute();
  }

  /**
   * Adds one term permission.
   *
   * @param $iUserIdGrantedAccess
   * @param $iTermId
   *
   * @return null
   * @throws \Exception
   */
  private function addOneTermPermission($iUserIdGrantedAccess){
    $this->oDatabase->insert('permissions_by_term_user')
      ->fields(['tid', 'uid'], [$this->iTermId, $iUserIdGrantedAccess])
      ->execute();
  }

  /**
   * Gets the term id by term name.
   *
   * @return null
   */
  private function getTermId() {
    $aTermId = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $this->sTermName)
      ->execute();
    return key($aTermId);
  }

  public function getUserIdsGrantedAccess(){
    return $this->aUserIdsGrantedAccess;
  }

  /**
   * Saves term permissions by users. Oposite to save term permission
   * by roles.
   *
   * @return null
   */
  public function saveTermPermissionsByUsers() {

    $aUserPermissions = $this->getUserTermPermissionsByTid();
    $aUserIdsGrantedAccess = $this->getUserIdsGrantedAccess();

    foreach ($aUserPermissions as $iPermissionUid) {
      if (!in_array($iPermissionUid, $aUserIdsGrantedAccess)) {
        $this->deleteOneTermPermissionByUserId($iPermissionUid);
      }
    }

    foreach ($aUserIdsGrantedAccess as $iUserIdGrantedAccess) {
      if (!in_array($iUserIdGrantedAccess, $aUserPermissions)) {
        $this->addOneTermPermission($iUserIdGrantedAccess);
      }
    }

    return $aUserPermissions;

  }

}
