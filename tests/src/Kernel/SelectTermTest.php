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
    // Add a paragraph field to the article.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'secured_areas',
      'entity_type' => 'term_relation',
      //'type' => 'entity_reference_revisions',
    ));
    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'test_pbt',
    ));
    $field->save();

    // Add a paragraph field to the article.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'node_pbt_field',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => '-1',
    ));
    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'article',
    ));
    $field->save();
  }

}
