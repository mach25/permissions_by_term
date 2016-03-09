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
            $user_is_allowed_to_view = FALSE;
            $arr__nodes_to_hide_in_view[] = $oNode->id();
          }
        }
      }
    }
  }

}