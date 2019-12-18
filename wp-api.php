<?php

/**
 * Public API for consuming the GearLab Tools API from WordPress code
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace GearLab;

use GearLab\Plugin\Paginator;

function client() {
  return apply_filters('gearlab/api/client', null);
}

function search(array $params = []) : array {
  return client()->search(apply_filters('gearlab/search/params', $params));
}

function completions(array $params) : array {
  return client()->completions($params);
}

function paginate_links(array $response) : string {
  $paginator = Paginator::from_search_response($response);
  // TODO filters for pagination params
  $markers = $paginator->page_markers($_GET);
  $params = $_GET;

  ob_start();
  require __DIR__ . '/views/pagination.php';
  return ob_get_clean();
}


add_filter('gearlab/search/params', function(array $params) : array {
  if ($params) {
    // we already have params; no need to set up defaults
    return $params;
  }

  $pageNum = apply_filters('gearlab/search/page_num', 1);
  $count   = apply_filters('gearlab/search/result_count', 10);
  $offset  = ($pageNum - 1) * $count;

  return [
    'query'     => apply_filters('gearlab/search/query', $_GET['s'] ?? ''),
    'resLength' => $count,
    'resOffset' => $offset,
    'metaTag'   => apply_filters('gearlab/search/meta_tag', ''),
  ];
}, 10);

add_filter('gearlab/search/result_count', function() : int {
  static $count;
  if (!isset($count)) {
    $count = (int) get_option('posts_per_page');
  }
  return $count;
}, 1);

add_filter('gearlab/search/page_num', function() : int {
  return $_GET['page_num'] ?? 1;
}, 1);

add_filter('gearlab/search/meta_tag', function() : string {
  return '';
}, 1);
