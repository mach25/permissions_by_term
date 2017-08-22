<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Core\Database\Connection;
use Drupal\user\Entity\User;

/**
 * AccessCheckService class.
 */
class AccessCheck {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs AccessCheck object.
   *
   * @param Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database  = $database;
  }

  /**
   * @return bool
   */
  public function canUserAccessByNodeId($nid, $uid = FALSE) {
    $access_allowed = TRUE;

    $terms = $this->database
      ->query("SELECT tid FROM {taxonomy_index} WHERE nid = :nid",
      [':nid' => $nid])->fetchAll();

    foreach ($terms as $term) {
      if (!$this->isAccessAllowedByDatabase($term->tid, $uid)) {
        $access_allowed = FALSE;

        return $access_allowed;
      }
    }

    return $access_allowed;
  }

  /**
   * Returns a boolean if the view is containing nodes.
   * @return bool
   */
  public function viewContainsNode($view) {
    $bViewContainsNodes = FALSE;

    foreach ($view->result as $view_result) {
      if (array_key_exists('nid', $view_result) === TRUE) {
        $bViewContainsNodes = TRUE;
        break;
      }
    }
    return $bViewContainsNodes;
  }

  /**
   * @return void
   */
  public function removeForbiddenNodesFromView(&$view) {
    $aNodesToHideInView = [];

    // Iterate over all nodes in view.
    foreach ($view->result as $v) {

      if ($this->canUserAccessByNodeId($v->nid) === FALSE) {
        $aNodesToHideInView[] = $v->nid;
      }

    }

    $counter = 0;

    foreach ($view->result as $v) {
      if (in_array($v->nid, $aNodesToHideInView)) {
        unset($view->result[$counter]);
      }
      $counter++;
    }
  }

  /**
   * @param int      $tid
   * @param bool|int $uid
   * @return array
   */
  public function isAccessAllowedByDatabase($tid, $uid = FALSE) {

    if ($uid === FALSE) {
      $user = \Drupal::currentUser();
    } elseif (is_numeric($uid)) {
      $user = User::load($uid);
    }

    // Admin can access everything (user id "1").
    if ($user->id() == 1) {
      return TRUE;
    }

    $tid = intval($tid);

    if (!$this->isAnyPermissionSetForTerm($tid)) {
      return TRUE;
    }

    /* At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    $aUserRoles = $user->getRoles();

    foreach ($aUserRoles as $sUserRole) {

      if ($this->isTermAllowedByUserRole($tid, $sUserRole)) {
        return TRUE;
      }

    }

    $iUid = intval($user->id());

    if ($this->isTermAllowedByUserId($tid, $iUid)) {
      return TRUE;
    }

    return FALSE;

  }

  /**
   * @param int $tid
   * @param int $iUid
   *
   * @return bool
   */
  public function isTermAllowedByUserId($tid, $iUid) {

    $query_result = $this->database->query("SELECT uid FROM {permissions_by_term_user} WHERE tid = :tid AND uid = :uid",
      [':tid' => $tid, ':uid' => $iUid])->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * @param int    $tid
   * @param string $sUserRole
   *
   * @return bool
   */
  public function isTermAllowedByUserRole($tid, $sUserRole) {
    $query_result = $this->database->query("SELECT rid FROM {permissions_by_term_role} WHERE tid = :tid AND rid IN (:user_roles)",
      [':tid' => $tid, ':user_roles' => $sUserRole])->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * @param int $tid
   *
   * @return bool
   */
  public function isAnyPermissionSetForTerm($tid) {

    $iUserTableResults = intval($this->database->query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid",
      [':tid' => $tid])->fetchField());

    $iRoleTableResults = intval($this->database->query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid",
      [':tid' => $tid])->fetchField());

    if ($iUserTableResults > 0 ||
      $iRoleTableResults > 0) {
      return TRUE;
    }

  }

}
