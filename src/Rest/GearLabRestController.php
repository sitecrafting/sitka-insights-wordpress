<?php

/**
 * GearLab\Rest\GearLabRestController class
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace GearLab\Plugin\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use GearLab;

/**
 * Base controller for Sitka Insights REST API integration
 */
class GearLabRestController {
  const API_NAMESPACE = 'gearlab/v2';

  public function register_routes() {
    register_rest_route(static::API_NAMESPACE, '/completions', [
      'methods' => 'GET',
      'callback' => [$this, 'completions_action'],
    ]);
  }

  public function completions_action(WP_REST_Request $request) : WP_REST_Response {
    if (empty($_GET['prefix']) && empty($_GET['term'])) {
      return new WP_REST_Response([
        'success' => false,
        'data'    => [],
        'error'   => 'The `prefix` or `term` GET parameter is required',
      ], 400);
    }

    $response = GearLab\completions([
      // honor either prefix or term, the jquery-ui-autocomplete key
      'prefix' => $_GET['prefix'] ?? $_GET['term'],
    ]);

    if (!isset($response['results'])) {
      // support taking action from theme code when results are empty
      do_action('gearlab/api/completions/empty_results', $response);

      return new WP_REST_Response([
        'success' => false,
        'data'    => [],
        'error'   => 'Bad response from GearLab API',
      ], 500);
    }

    // Map each result to just a string that the client-side code expects
    $completions = array_map(function(array $result) : string {
      return $result['title'] ?? '';
    }, $response['results'] ?? []);

    // Return completions
    return new WP_REST_Response($completions);
  }
}
