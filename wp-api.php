<?php

/**
 * Public API for consuming the Sitka Insights API from WordPress code
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace Sitka;

use WP_Query;

use Sitka\Plugin\Paginator;

function client() {
  return apply_filters('sitka/api/client', null);
}

function search(array $params = []) : array {
  return client()->search(apply_filters('sitka/search/params', $params));
}

function completions(array $params) : array {
  return client()->completions($params);
}

function shortcode_redirect_enabled() : bool {
  return apply_filters(
    'sitka/search_shortcode/enabled',
    get_option('sitka_search_enabled') === SITKA_OVERRIDE_METHOD_SHORTCODE
  );
}

function paginate_links(array $response) : string {
  $params = apply_filters(
    'sitka/pagination/construct',
    Paginator::params_from_response($response)
  );

  $paginator = new Paginator();
  $paginator->set_pagination($params);

  return apply_filters('sitka/render', 'pagination.php', [
    'paginator'  => $paginator,
    'url_params' => $_GET,
  ]);
}

/**
 * Given a Sitka response array, return a human-readable string of the form:
 * "X-Y of Z results" where:
 *
 * * X is resStart
 * * Y is resEnd
 * * Z is total
 *
 * The given array MUST contain the above keys.
 * @param array $response the Sitka response array
 * @return string the human-readable string
 */
function enumerate_page(array $response) : string {
  return sprintf(
    '%s-%s of %s',
    number_format($response['resStart'] ?? 0),
    number_format($response['resEnd'] ?? 0),
    number_format($response['total'] ?? 0)
  );
}

function enqueue_scripts() {
  // enqueue dependencies
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-autocomplete');

  // enqueue our AJAX autocomplete script
  wp_enqueue_script(
    'sitka-js',
    SITKA_PLUGIN_JS_ROOT . '/search.js',
    ['jquery', 'jquery-ui-core', 'jquery-ui-autocomplete'],
    /** @version v0.0.2 */
    'v0.0.2',
    $footer = true
  );

  // Provide basic styles for the search form
  wp_register_style(
    'jquery-ui-styles',
    'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css'
  );
  wp_enqueue_style('jquery-ui-styles');

  wp_enqueue_style('sitka-insights-search', SITKA_PLUGIN_WEB_PATH . 'css/sitka-insights-search.css');
}


add_filter('sitka/search/params', function(array $params) : array {
  if ($params) {
    // we already have params; no need to set up defaults
    return $params;
  }

  $pageNum = apply_filters('sitka/search/page_num', 1);
  $count   = apply_filters('sitka/search/result_count', 10);
  $offset  = ($pageNum - 1) * $count;

  return [
    'query'     => apply_filters('sitka/search/query', $_GET['s'] ?? ''),
    'resLength' => $count,
    'resOffset' => $offset,
    'metaTag'   => apply_filters('sitka/search/meta_tag', ''),
  ];
}, 10);

add_filter('sitka/search/result_count', function() : int {
  static $count;
  if (!isset($count)) {
    $count = (int) get_option('posts_per_page');
  }
  return $count;
}, 1);

add_filter('sitka/search/page_num', function() : int {
  return (int) ($_GET['page_num'] ?? 1);
}, 1);

add_filter('sitka/search/meta_tag', function() : string {
  return '';
}, 1);

add_filter('sitka/search/result/meta_tag_label_map', function($map = []) : array {
  return array_merge($map, [
    '_document' => 'Document',
    'post'      => 'Post',
    'page'      => 'Page',
  ]);
});

add_filter('sitka/search/result/meta_tag_label', function($tag, $_result) : string {
  $map = apply_filters('sitka/search/result/meta_tag_label_map', []);

  return $map[$tag] ?? ucfirst($tag);
}, 10, 2);
