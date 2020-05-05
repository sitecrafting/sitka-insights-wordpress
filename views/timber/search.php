<?php

/**
 * Generic search results page, powered by Timber
 */

use Swagger\Client\ApiException;

try {
  $response = GearLab\search();
} catch (ApiException $e) {
  do_action('gearlab/api/error/api_exception', sprintf(
    'GearLab API error: %s',
    $e->getMessage()
  ));
  $response = [];
} catch (InvalidArgumentException $e) {
  do_action('gearlab/api/error/invalid_client_args', sprintf(
    'Error setting up GearLab client: %s',
    $e->getMessage()
  ));
  $response = [];
}

$data = Timber::get_context();
$data['response'] = $response;
$data['layout_template'] = 'layouts/main.twig';

do_action(
  'gearlab/timber/render_search',
  apply_filters('gearlab/timber/render_search_view', 'gearlab-tools/search.twig'),
  apply_filters('gearlab/timber/render_search_context', $data),
  false
);
