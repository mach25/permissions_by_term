<?php

namespace Drupal\permissions_by_term;

interface AccessCheckInterface {

  /**
   * Checks if a user can access a node by given node id.
   */
  public function canUserAccessByNodeId($iNid);

  /**
   * Returns a boolean if the view is containing nodes.
   */
  public function viewContainsNode($view);

  /**
   * Removes forbidden nodes from view listing.
   */
  public function removeForbiddenNodesFromView(&$view);

  /**
   * Implements permissions_by_term_allowed().
   *
   * This hook-function checks if a user is either allowed or not allowed to
   * access a given term.
   *
   * @param int $tid
   *   The taxonomy term id.
   *
   * @return bool
   *   Determines by boolean if access is allowed by given tid and the signed
   *   in user.
   */
  public function isAccessAllowedByDatabase($tid);

  /**
   * Returns a boolean if the term is allowed by given user id.
   *
   * @param int $iTid
   *   The taxonomy term id.
   * @param int $iUid
   *   The user id.
   *
   * @return bool
   *   Determines by boolean if the given term id is allowed by given user id.
   */
  public function isTermAllowedByUserId($iTid, $iUid);

  /**
   * Returns a boolean if the term is allowed by given user role id.
   *
   * @param int $iTid
   *   The term id.
   * @param string $sUserRole
   *   The user role.
   *
   * @return bool
   *   Determines if the term is allowed by the given user role.
   */
  public function isTermAllowedByUserRole($iTid, $sUserRole);

  /**
   * Gets boolean for set permission on a term.
   *
   * @param int $iTid
   *   The taxonomy term id.
   *
   * @return bool
   *   Returns either TRUE or FALSE if there is any permission set for the term.
   */
  public function isAnyPermissionSetForTerm($iTid);

}
