<?php

namespace Drupal\Tests\permissions_by_term\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\Driver\DrupalDriver;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class PermissionsByTermContext
 *
 * @package PermissionsByTerm
 */
class PermissionsByTermContext extends RawDrupalContext {

  public function __construct() {
    $driver = new DrupalDriver(DRUPAL_ROOT, '');
    $driver->setCoreFromVersion();

    // Bootstrap Drupal.
    $driver->bootstrap();
  }

  /**
   * Creates one or more terms on an existing vocabulary.
   *
   * Provide term data in the following format:
   *
   * | name  | parent | description | weight | taxonomy_field_image | access_user | access_role |
   * | Snook | Fish   | Marine fish | 10     | snook-123.jpg        | Bob         | editor      |
   * | ...   | ...    | ...         | ...    | ...                  | ...         | ...         |
   *
   * Only the 'name' field is required.
   *
   * @Given restricted :vocabulary terms:
   */
  public function createTerms($vocabulary, TableNode $termsTable) {
    foreach ($termsTable->getHash() as $termsHash) {
      $term = (object) $termsHash;
      $term->vocabulary_machine_name = $vocabulary;
      $this->termCreate($term);

      $accessStorage = \Drupal::Service('permissions_by_term.access_storage');
      if (!empty($termsHash['access_user'])) {
        $accessStorage->addTermPermissionsByUserIds([$accessStorage->getUserIdByName($termsHash['access_user'])['uid']], $term->tid);
      }
      if (!empty($termsHash['access_role'])) {
        $accessStorage->addTermPermissionsByRoleIds([$termsHash['access_role']], $term->tid);
      }
    }
  }

  /**
   * @Then I open open Permissions By Term advanced info
   */
  public function iOpenOpenPermissionsByTermAdvancedInfo()
  {
    $this->getSession()->evaluateScript("jQuery('#edit-permissions-by-term-info').attr('open', true);");
  }

  /**
   * @Given /^I create (\d+) nodes of type "([^"]*)"$/
   */
  public function iCreateNodesOfType($number, $type)
  {
    for ($i = 0; $i <= $number; $i++) {
      $node = new \stdClass();
      $node->type = $type;
      $node->title = $this->createRandomString();
      $node->body = $this->createRandomString();
      $this->nodeCreate($node);
    }
  }

  private function createRandomString($length = 10) {
    return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", $length)), 0, $length);
  }

  /**
   * @Given Node access records are rebuild.
   */
  public function nodeAccessRecordsAreRebuild()
  {
    node_access_rebuild();
  }

  /**
   * @Then /^wait (\d+) seconds$/
   */
  public function waitSeconds($secondsNumber)
  {
    $this->getSession()->wait($secondsNumber * 1000);
  }

  /**
   * @Then /^I select index (\d+) in dropdown named "([^"]*)"$/
   */
  public function selectIndexInDropdown($index, $name)
  {
    $this->getSession()->evaluateScript('document.getElementsByName("' . $name . '")[0].selectedIndex = ' . $index . ';');
  }

  /**
   * @Then /^I open node edit form by node title "([^"]*)"$/
   * @param string $title
   */
  public function openNodeEditFormByTitle($title)
  {
    $query = \Drupal::service('database')->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.title', $title);

    $this->visitPath('/node/' . $query->execute()->fetchField() . '/edit');
  }

  /**
   * @Then /^I open node view by node title "([^"]*)"$/
   * @param string $title
   */
  public function openNodeViewByTitle($title)
  {
    $query = \Drupal::service('database')->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.title', $title);

    $this->visitPath('/node/' . $query->execute()->fetchField());
  }

}