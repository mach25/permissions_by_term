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
use Drupal\user\Entity\User;

/**
 * Class AccessService.
 *
 * @package Drupal\permissions_by_term
 */
class AccessStorageService implements AccessStorageServiceInterface {

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
  private $iTermId;

  /**
   * The roles with granted access.
   *
   * @var array
   */
  protected $aSubmittedRolesGrantedAccess;

  /**
   * AccessService constructor.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   * @param \Drupal\Core\Form\FormState $oFormState
   */
  public function __construct(Connection $database, FormState $oFormState, $iTermId = null) {
    $this->oDatabase  = $database;
    $this->oFormState = $oFormState;

    if (isset($this->oFormState->getValues()['access'])) {
      $sValuesUserAccess       = $this->oFormState->getValues()['access']['user'];
      $aUsernamesGrantedAccess = Tags::explode($sValuesUserAccess);

      $this->aUserIdsGrantedAccess = $this->getUserIdsByNames($aUsernamesGrantedAccess);
    }

    $this->iTermId = $this->getTermId($iTermId);
  }

  /**
   * Gets submitted roles with granted access from form.
   *
   * @return array
   */
  protected function getSubmittedRolesGrantedAccess() {
    $aRoles       = $this->oFormState->getValue('access')['role'];
    $aChosenRoles = array();
    foreach ($aRoles as $sRole) {
      if ($sRole !== 0) {
        $aChosenRoles[] = $sRole;
      }
    }
    return $aChosenRoles;
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
   * Gets user term permissions by tid.
   *
   * @param $iTermId
   *
   * @return mixed
   */
  protected function getExistingUserTermPermissionsByTid() {
    return $this->oDatabase->select('permissions_by_term_user', 'pu')
      ->condition('tid', $this->iTermId)
      ->fields('pu', ['uid'])
      ->execute()
      ->fetchCol();
  }

  /**
   * Gets role term permissions by tid.
   *
   * @param $iTermId
   *
   * @return mixed
   */
  public function getExistingRoleTermPermissionsByTid() {
    return $this->oDatabase->select('permissions_by_term_role', 'pr')
      ->condition('tid', $this->iTermId)
      ->fields('pr', ['rid'])
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
   * Gets the user names from users which have granted access
   * for a taxonomy term.
   *
   * @return mixed
   */
  public function getAllowedUserNames() {
    $query = $this->oDatabase->select('permissions_by_term_user', 'p')
      ->fields('ufd', ['name'])
      ->condition('p.tid', $this->iTermId);

    // Join is not fluent on Drupal database object.
    $query->join('users_field_data', 'ufd', 'p.uid = %alias.uid');

    // fetchCol() returns all results, fetchAssoc() only "one" result.
    return $query->execute()
      ->fetchCol();
  }

  /**
   * Deletes term permissions by user id.
   *
   * @param $iUserId
   *
   * @return null
   */
  private function deleteTermPermissionsByUserIds($aUserIdsAccessRemove) {
    foreach ($aUserIdsAccessRemove as $iUserId) {
      $this->oDatabase->delete('permissions_by_term_user')
        ->condition('uid', $iUserId, '=')
        ->execute();
    }
  }

  /**
   * Deletes term permissions by role ids.
   *
   * @param $aRoleIdsAccessRemove
   *
   * @return null
   */
  private function deleteTermPermissionsByRoleIds($aRoleIdsAccessRemove) {
    foreach ($aRoleIdsAccessRemove as $sRoleId) {
      $this->oDatabase->delete('permissions_by_term_role')
        ->condition('rid', $sRoleId, '=')
        ->execute();
    }
  }

  /**
   * Adds term permissions by user ids.
   *
   * @param $iUserIdGrantedAccess
   * @param $iTermId
   *
   * @return null
   * @throws \Exception
   */
  private function addTermPermissionsByUserIds($aUserIdsGrantedAccess) {
    foreach ($aUserIdsGrantedAccess as $iUserIdGrantedAccess) {
      $this->oDatabase->insert('permissions_by_term_user')
        ->fields(['tid', 'uid'], [$this->iTermId, $iUserIdGrantedAccess])
        ->execute();
    }
  }

  /**
   * Adds term permissions by role ids.
   *
   * @param $aRoleIdsGrantedAccess
   *
   * @return null
   * @throws \Exception
   */
  private function addTermPermissionsByRoleIds($aRoleIdsGrantedAccess) {
    foreach ($aRoleIdsGrantedAccess as $sRoleIdGrantedAccess) {
      $this->oDatabase->insert('permissions_by_term_role')
        ->fields(['tid', 'rid'], [$this->iTermId, $sRoleIdGrantedAccess])
        ->execute();
    }
  }

  /**
   * Gets the term id by term name.
   *
   * @return null
   */
  private function getTermId($iTermId = null) {
    if ($iTermId == null){
      $this->sTermName  = $this->oFormState->getValue('name')['0']['value'];
      $aTermId = \Drupal::entityQuery('taxonomy_term')
        ->condition('name', $this->sTermName)
        ->execute();
      return key($aTermId);
    } else {
      return $iTermId;
    }
  }

  protected function getSubmittedUserIdsGrantedAccess() {
    return $this->aUserIdsGrantedAccess;
  }

  /**
   * Saves term permissions by users. Oposite to save term permission
   * by roles.
   *
   * @return null
   */
  public function saveTermPermissions() {

    $aExistingUserPermissions       = $this->getExistingUserTermPermissionsByTid();
    $aSubmittedUserIdsGrantedAccess = $this->getSubmittedUserIdsGrantedAccess();

    $aExistingRoleIdsGrantedAccess = $this->getExistingRoleTermPermissionsByTid();
    $aSubmittedRolesGrantedAccess  = $this->getSubmittedRolesGrantedAccess();

    $aRet = $this->getPreparedDataForDatabaseQueries($aExistingUserPermissions,
      $aSubmittedUserIdsGrantedAccess, $aExistingRoleIdsGrantedAccess,
      $aSubmittedRolesGrantedAccess);

    // Run the database queries.
    $this->deleteTermPermissionsByUserIds($aRet['UserIdPermissionsToRemove']);
    $this->addTermPermissionsByUserIds($aRet['UserIdPermissionsToAdd']);

    $this->deleteTermPermissionsByRoleIds($aRet['UserRolePermissionsToRemove']);
    $this->addTermPermissionsByRoleIds($aRet['aRoleIdPermissionsToAdd']);

    return $aRet;

  }

  /**
   * Get array items to remove. The array items which aren't in the new items
   * array, but are in old items array, will be returned.
   *
   * @param $aExistingItems
   * @param $aNewItems
   *
   * @return array
   */
  private function getArrayItemsToRemove($aExistingItems, $aNewItems) {
    foreach ($aExistingItems as $existingItem) {
      if (!in_array($existingItem, $aNewItems)) {
        $aRet[] = $existingItem;
      }
    }
    return $aRet;
  }

  /**
   * Get the array items to add. The items in the new items array, which aren't
   * in the existing items array, will be returned.
   *
   * @param $aNewItems
   * @param $aExistingItems
   *
   * @return array
   */
  private function getArrayItemsToAdd($aNewItems, $aExistingItems) {
    foreach ($aNewItems as $newItem) {
      if (!in_array($newItem, $aExistingItems)) {
        $aRet[] = $newItem;
      }
    }
    return $aRet;
  }

  /**
   * Prepares the data which has to be applied to the database.
   *
   * @param array $aExistingUserPermissions
   * @param array $aSubmittedUserIdsGrantedAccess
   * @param array $aExistingRoleIdsGrantedAccess
   * @param array $aSubmittedRolesGrantedAccess
   *
   * @return mixed
   */
  public function getPreparedDataForDatabaseQueries($aExistingUserPermissions,
                                                    $aSubmittedUserIdsGrantedAccess,
                                                    $aExistingRoleIdsGrantedAccess,
                                                    $aSubmittedRolesGrantedAccess) {
    /**
     * Fill array with user ids to remove permission.
     */
    $aRet['UserIdPermissionsToRemove'] =
      $this->getArrayItemsToRemove($aExistingUserPermissions,
        $aSubmittedUserIdsGrantedAccess);

    /**
     * Fill array with user ids to add permission.
     */
    $aRet['UserIdPermissionsToAdd'] =
      $this->getArrayItemsToAdd($aSubmittedUserIdsGrantedAccess,
        $aExistingUserPermissions);

    /**
     * Fill array with user roles to remove permission.
     */
    $aRet['UserRolePermissionsToRemove'] =
      $this->getArrayItemsToRemove($aExistingRoleIdsGrantedAccess,
        $aSubmittedRolesGrantedAccess);

    /**
     * Fill array with user roles to add permission.
     */
    $aRet['aRoleIdPermissionsToAdd'] =
      $this->getArrayItemsToAdd($aSubmittedRolesGrantedAccess,
        $aExistingRoleIdsGrantedAccess);

    return $aRet;
  }

}
