<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\permissions_by_term\Service\NodeEntityBundleInfo;


class MultilingualTest extends PBTKernelTestBase {

  use TaxonomyTestTrait;

  /**
   * @var NodeEntityBundleInfo
   */
  private $nodeEntityBundleInfo;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->nodeEntityBundleInfo = $this->container->get('permissions_by_term.node_entity_bundle_info');
  }

  public function testGetPermissionsByTids() {
    Vocabulary::create([
      'name'     => 'test2',
      'vid'      => 'test2',
      'langcode' => 'de',
    ])->save();
  }

}