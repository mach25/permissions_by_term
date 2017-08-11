<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Core\Template\TwigEnvironment;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class Info
 *
 * @package Drupal\permissions_by_term\Service
 */
class Info {

  /**
   * @var AccessStorage
   */
  private $accessStorage;

  /**
   * @var Term
   */
  private $term;

  /**
   * @var TwigEnvironment
   */
  private $twig;

  /**
   * Info constructor.
   *
   * @param AccessStorage   $accessStorage
   * @param Term            $term
   * @param TwigEnvironment $twig
   */
  public function __construct(
    AccessStorage $accessStorage,
    Term $term,
    TwigEnvironment $twig
  ) {
    $this->accessStorage = $accessStorage;
    $this->term = $term;
    $this->twig = $twig;
  }

  /**
   * @param int $nid
   * @return string
   */
  public function renderNodeDetails($nid, $viewFilePath) {

    $tids = $this->term->getTidsByNid($nid);
    $uids = $this->accessStorage->getUserTermPermissionsByTids($tids);
    $rids = $this->accessStorage->getRoleTermPermissionsByTids($tids);

    $roles = Role::loadMultiple($rids);
    $users = User::loadMultiple($uids);

    $template = $this->twig->load($viewFilePath);

    return $template->render(['roles' => $roles, 'users' => $users]);

  }

}