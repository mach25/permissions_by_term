<?php /**
 * @file
 * Contains \Drupal\permissions_by_term\Controller\DefaultController.
 */

namespace Drupal\permissions_by_term\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the permissions_by_term module.
 */
class DefaultController extends ControllerBase {

  public function permissions_by_term_autocomplete_multiple($string) {
    // The user enters a comma-separated list of users.
  // We only autocomplete the last user.
    $array = drupal_explode_tags($string);

    // Fetch last user.
    $last_string = trim(array_pop($array));

    $matches = [];
    $result = db_select('users')->fields('users', ['name'])->condition('name', db_like($last_string) . '%', 'LIKE')->range(0, 10)->execute();

    $prefix = count($array) ? implode(', ', $array) . ', ' : '';

    foreach ($result as $user) {
      $matches[$prefix . $user->name] = \Drupal\Component\Utility\SafeMarkup::checkPlain($user->name);
    }
    drupal_json_output($matches);
  }

}
