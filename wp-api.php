<?php

/**
 * Public API for consuming the GearLab Tools API from WordPress code
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace GearLab;

use WP_Query;

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

function disable_default_wp_search() {
  add_action('parse_query', function(WP_Query $query) {
    if ($query->is_search()) {
      $query->is_search = false;
      $query->set('gearlab_search', true);
    }
  });

  add_filter('template_include', function(string $template) {
    $searchTpl = get_template_directory() . '/search.php';
    if (isset($_GET['s']) && file_exists($searchTpl)) {
      // Force a 200 OK response
      global $wp_query;
      $wp_query->is_404 = false;
      status_header(200);

      return $searchTpl;
    }
    return $template;
  });

  add_filter('wp_title', function(string $title, $separator) {
    global $wp_query;
    if ($wp_query->get('gearlab_search')) {
      $searchTerms = $wp_query->get('search_terms') ?: [];

      $title = sprintf(
        'Search: %s %s %s',
        implode(' ', $searchTerms),
        trim($separator) ?: '|',
        get_bloginfo('name')
      );
    }
    return $title;
  }, 10, 2);
}

function enqueue_scripts() {
  // enqueue dependencies
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-autocomplete');

  // enqueue our AJAX autocomplete script
  wp_enqueue_script(
    'gearlab-js',
    GEARLAB_PLUGIN_JS_ROOT . '/search.js',
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

  wp_enqueue_style('gearlab-tools-search', GEARLAB_PLUGIN_WEB_PATH . 'css/gearlab-tools-search.css');
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
  return (int) ($_GET['page_num'] ?? 1);
}, 1);

add_filter('gearlab/search/meta_tag', function() : string {
  return '';
}, 1);
