<?php

namespace Drupal\permissions_by_term;

class AccessCheckService
{

  public function __construct() {
    $this->oUser = \Drupal::currentUser();
  }

  /**
   * Checks if a user can access a node by given node id.
   *
   * @param $iNid
   */
  public function canUserAccessByNodeId($iNid)
  {
    // @TODO: check if there's any permission setting + check access for anonymous users. There seems to be a bug.

    $oNode = \Drupal::entityManager()->getStorage('node')->load($iNid);

    if ($oNode->hasField('field_secured_areas')) {
      // @TODO: replace hard coded field name to add flexibility.
      $oField = $oNode->get('field_secured_areas');
      $aReferencedTaxonomyTerms = $oField->getValue();

      if (!empty($aReferencedTaxonomyTerms)) {
        foreach ($aReferencedTaxonomyTerms as $aReferencedTerm) {

          // @TODO: Move permissions_by_term_allowed() in here.

          if (isset($aReferencedTerm['target_id']) &&
            $this->getAccessFromDatabase($aReferencedTerm['target_id']) === TRUE
          ) {
            $user_is_allowed_to_view = TRUE;
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
   */
  private function getAccessFromDatabase($tid) {

    if ($this->oUser->id() == 1) {
      return TRUE;
    }

    // Are permissions enabled on this term? Check for role and user.
    if (!(db_query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid",
        array(':tid' => $tid))->fetchField() ||
      db_query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid",
        array(':tid' => $tid))->fetchField())) {
      return TRUE;
    }

    /**
     * At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    $aUserRoles = $this->oUser->getRoles();

    foreach ($aUserRoles as $sUserRole) {
      if (db_query("SELECT uid FROM {permissions_by_term_user} WHERE tid = :tid AND uid = :uid",
          array(':tid' => $tid, ':uid' => $this->oUser->id()))->fetchField() ||
        db_query("SELECT rid FROM {permissions_by_term_role} WHERE tid = :tid AND rid IN (:user_roles)",
          array(':tid' => $tid, ':user_roles' => $sUserRole))->fetchField()) {
        return TRUE;
      }
    }

    return FALSE;

  }

}
