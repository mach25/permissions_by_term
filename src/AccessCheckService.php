<?php

namespace Drupal\permissions_by_term;


class AccessCheckService
{

  /**
   * AccessCheckService constructor.
   *
   * @param $iNid Can be NULL in case of views.
   */
  public function __construct($iNid) {
    $this->oUser = \Drupal::currentUser();
    if ($iNid !== NULL) {
      $this->oNode = \Drupal::entityManager()->getStorage('node')->load($iNid);
    }
  }

  /**
   * Checks if a user can access a node by given node id.
   *
   * @param $iNid
   */
  public function canUserAccessByNodeId($iNid = NULL)
  {
    // In case of access checking on a view.
    if ($iNid !== NULL) {
      $this->oNode = \Drupal::entityManager()->getStorage('node')->load($iNid);
    }

    // @TODO: check if there's any permission setting + check access for anonymous users. There seems to be a bug.

    $debug = true;

    if ($this->oNode->hasField('field_secured_areas')) {
      // @TODO: replace hard coded field name to add flexibility.
      $oField = $this->oNode->get('field_secured_areas');
      $aReferencedTaxonomyTerms = $oField->getValue();

      if (!empty($aReferencedTaxonomyTerms)) {
        foreach ($aReferencedTaxonomyTerms as $aReferencedTerm) {

          if (isset($aReferencedTerm['target_id']) &&
            $this->isAccessAllowedByDatabase($aReferencedTerm['target_id']) === TRUE
          ) {
            return TRUE;
          }

          if (!isset($user_is_allowed_to_view)) {
           return FALSE;
          }

        }
      }

    } else {
      // No taxonomy field reference for permissions. User can access.
      return TRUE;
    }

  }

  /**
   * @param $view
   * @return bool
   */
  public function viewContainsNode($view)
  {
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
   * @param $view
   * @param $aNodesToHideInView
   */
  public function removeForbiddenNodesFromView(&$view)
  {
    $aNodesToHideInView = array();

    // Iterate over all nodes in view.
    foreach ($view->result as $v) {

      if ($this->canUserAccessByNodeId($v->nid) === FALSE) {
        $aNodesToHideInView[] = $v->nid;
      }

    }

    $iCounter = 0;

    foreach ($view->result as $v) {
      if (in_array($v->nid, $aNodesToHideInView)) {
        unset($view->result[$iCounter]);
      }
      $iCounter++;
    }
  }

  /**
   * Implements permissions_by_term_allowed().
   *
   * This hook-function checks if a user is either allowed or not allowed to
   * access a given node by the referenced taxonomy term.
   *
   * @param int $tid The taxonomy term id.
   *
   * @return bool
   */
  private function isAccessAllowedByDatabase($tid) {

    // Admin can access everything (user id "1").
    if ($this->oUser->id() == 1) {
      return TRUE;
    }

    $iTid = intval($tid);

    if (!$this->isAnyPermissionSetForTerm($iTid)) {
      return TRUE;
    }

    /**
     * At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    $aUserRoles = $this->oUser->getRoles();

    foreach ($aUserRoles as $sUserRole) {

      if ($this->isTermAllowedByUserRole($iTid, $sUserRole)) {
        return TRUE;
      }

    }

    $iUid = intval($this->oUser->id());

    if ($this->isTermAllowedByUserId($iTid, $iUid)) {
      return TRUE;
    }

    return FALSE;

  }

  /**
   * Returns a boolean if the term is allowed by given user id.
   *
   * @param $iTid
   * @param $iUid
   *
   * @return bool
   */
  private function isTermAllowedByUserId ($iTid, $iUid) {

    $query_result = db_query("SELECT uid FROM {permissions_by_term_user} WHERE tid = :tid AND uid = :uid",
      array(':tid' => $iTid, ':uid' => $iUid))->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    } else {
      return FALSE;
    }

  }

  /**
   * Returns a boolean if the term is allowed by given user role id.
   *
   * @param $iTid
   * @param $sUserRole
   *
   * @return bool
   */
  private function isTermAllowedByUserRole ($iTid, $sUserRole) {
    $query_result = db_query("SELECT rid FROM {permissions_by_term_role} WHERE tid = :tid AND rid IN (:user_roles)",
      array(':tid' => $iTid, ':user_roles' => $sUserRole))->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    } else {
      return FALSE;
    }

  }

  /**
   * Returns a boolean is any permission is set on term. Either by roles or
   * user accounts.
   *
   * @param $iTid
   *
   * @return bool
   */
  private function isAnyPermissionSetForTerm ($iTid) {

    $iUserTableResults = intval(db_query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid",
      array(':tid' => $iTid))->fetchField());

    $iRoleTableResults = intval(db_query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid",
      array(':tid' => $iTid))->fetchField());

    if ($iUserTableResults > 0 ||
      $iRoleTableResults > 0) {
      return TRUE;
    }

  }


}
