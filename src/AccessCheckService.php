<?php

namespace Drupal\permissions_by_term;

class AccessCheckService
{

  public function __construct() {
    $debug = true;
  }

  /**
   * Checks if a user can access a node by given node id.
   *
   * @param $iNid
   */
  public function canUserAccessByNodeId($iNid)
  {
    $oNode = \Drupal::entityManager()->getStorage('node')->load($iNid);

    if ($oNode->hasField('field_secured_areas')) {
      // @TODO: replace hard coded field name to add flexibility.
      $oField = $oNode->get('field_secured_areas');
      $aReferencedTaxonomyTerms = $oField->getValue();

      if (!empty($aReferencedTaxonomyTerms)) {
        foreach ($aReferencedTaxonomyTerms as $aReferencedTerm) {

          // @TODO: Move permissions_by_term_allowed() in here.

          if (isset($aReferencedTerm['target_id']) &&
            permissions_by_term_allowed($aReferencedTerm['target_id'], \Drupal::currentUser()) === TRUE
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

}