<?php

/**
 * Generic search results page, powered by Timber
 */

use Swagger\Client\ApiException;

try {
  $response = Sitka\search();
} catch (ApiException $e) {
  do_action('sitka/api/error/api_exception', sprintf(
    'Sitka API error: %s',
    $e->getMessage()
  ));
  $response = [];
} catch (InvalidArgumentException $e) {
  do_action('sitka/api/error/invalid_client_args', sprintf(
    'Error setting up Sitka client: %s',
    $e->getMessage()
  ));
  $response = [];
}

$data = Timber::get_context();
$data['response'] = $response;
$data['layout_template'] = 'layouts/main.twig';

do_action(
  'sitka/timber/render_search',
  apply_filters('sitka/timber/render_search_view', 'sitka-insights/search.twig'),
  apply_filters('sitka/timber/render_search_context', $data),
  false
);
