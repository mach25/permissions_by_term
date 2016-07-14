<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the ERR composite relationship upgrade path.
 *
 * @group permissions_by_term
 */
class SavePermissionsTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'permissions_by_term',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('permissions_by_term');
  }

  /**
   * Tests if an editor has access to a term on node edit form.
   */
  public function testTermFormAccess() {

  }

}
