<?php

/**
 * Plugin Name: Sitka Insights
 * Description: Integrate your WordPress site with the Sitka Insights platform
 * Plugin URI: https://www.sitkainsights.com
 * Author: SiteCrafting, Inc. <hello@sitecrafting.com>
 * Author URI: https://www.sitecrafting.com/
 * Version: 3.0.0
 * Requires PHP: 7.1
 */

// no script kiddiez
if (!defined('ABSPATH')) {
  return;
}

// Require the composer autoloader, making educated guesses as to where it is.
// If it exists, honor the project-wide autoloader first, but do not treat it
// as mutually exclusive from the plugin's autoloader, since you can't assume
// the project pulls in the Sitka Insights plugin as a dependency.
if (file_exists(ABSPATH . 'vendor/autoload.php')) {
  require_once ABSPATH . 'vendor/autoload.php';
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/wp-api.php';

use GearLab\Api\Client;
use Sitka\Plugin\AdminPage;
use Sitka\Plugin\Rest\SitkaRestController;

use Swagger\Client\ApiException;


define('SITKA_PLUGIN_WEB_PATH', plugin_dir_url(__FILE__));
define('SITKA_PLUGIN_JS_ROOT', SITKA_PLUGIN_WEB_PATH . 'js');
define('SITKA_PLUGIN_VIEW_PATH', __DIR__ . '/views');

define('SITKA_OVERRIDE_METHOD_SHORTCODE', 'shortcode');

function sitka_default_client() : Client {
  // avoid instantiating the same object twice
  static $client;

  // instantiate a Client instance if we don't have one already
  return $client ?: new Client([
    'key'        => get_option('sitka_api_key'),
    'collection' => get_option('sitka_collection_id'),
    'baseUri'    => apply_filters('sitka/api/base_uri', ''),
  ]);
}

/*
 * Add the main hook for getting an API client instance
 */
add_filter('sitka/api/client', 'sitka_default_client');

add_filter('sitka/api/base_uri', function() {
  $env  = get_option('sitka_environment');
  $uris = [
    'production' => 'https://api.sitkainsights.com',
    'staging'    => 'https://stg-api.sitkainsights.com',
  ];

  $uri  = $uris[$env] ?? $uris['production'];

  return $uri;
});

add_filter('sitka/feedback/embed_uri', function() {
  $env  = get_option('sitka_environment');
  $uris = [
    'production' => 'https://dashboard.sitkainsights.com/feedback/embed',
    'staging'    => 'https://stg-dashboard.sitkainsights.com/feedback/embed',
  ];
  $uri  = $uris[$env] ?? $uris['production'];

  return $uri;
});


/*
 * Add WP Admin pages (just the one page, actually 🐦)
 */
add_action('admin_menu', function() {
  // Create an admin page responsible for managing the main
  // Sitka Insights credentials
  $page = AdminPage::add_options_page([
    'option_keys' => [
      'sitka_api_key',
      'sitka_site_id',
      'sitka_collection_id',
      'sitka_environment',
      'sitka_search_enabled',
      'sitka_search_redirect',
      'sitka_search_instead_enabled',
      'sitka_search_curated_results_enabled',
    ],
  ]);
  // Process any user updates
  if ($_POST) {
    $page->save_settings($_POST);
  }
  // Render the page
  $page->init()->add_meta_boxes();
});

add_action('admin_enqueue_scripts', function() {
  wp_enqueue_style(
    'sitka-insights-admin-styles',
    SITKA_PLUGIN_WEB_PATH . 'css/sitka-insights-admin.css'
  );
});

/*
 * Add REST Routes
 */
add_action('rest_api_init', function() {
  $controller = new SitkaRestController();
  $controller->register_routes();
});

/*
 * Add JS for autocomplete suggestions.
 */
add_action('wp_enqueue_scripts', function() {
  $enqueue = apply_filters('sitka/search/enqueue_js', !is_admin());
  if ($enqueue) {
    Sitka\enqueue_scripts();
  }
});

if (class_exists(WP_CLI::class)) {
  $command = new Sitka\WpCli\SitkaCommand();
  WP_CLI::add_command('sitka', $command);
}


/*
 * Add support for the Search UI shortcode.
 */

add_filter('sitka/render', function($tpl, $data = []) {
  $path = get_template_directory() . '/sitka-insights/' . $tpl;

  if (!file_exists($path)) {
    $path = SITKA_PLUGIN_VIEW_PATH . '/frontend/' . $tpl;
  }

  if (file_exists($path)) {
    ob_start();
    require $path;
    return ob_get_clean();
  }
}, 10, 2);

add_action('init', function() {
  global $wp;
  $wp->add_query_var('sitka_search');
  $wp->add_query_var('sitka_meta_tag');
  $wp->add_query_var('sitka_page_num');
  $wp->add_query_var('sitka_literal_query');

  add_shortcode('sitka_search', function($atts = []) {
    global $post;

    // Override how search paramaters are set in shortcode context.
    add_filter('sitka/search/query', function() {
      return get_query_var('sitka_search');
    });
    add_filter('sitka/search/meta_tag', function() {
      return get_query_var('sitka_meta_tag');
    });
    add_filter('sitka/search/page_num', function() {
      return get_query_var('sitka_page_num') ?: 1;
    });
    add_filter('sitka/search/page_num_param', function() {
      return 'sitka_page_num';
    });
    add_filter('sitka/search/literal_query', function() {
      return  get_query_var('sitka_literal_query');
    });

    $searchQuery = apply_filters('sitka/search/query', '');

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

    return apply_filters('sitka/render', 'search-results.php', [
      'post'     => $post,
      'query'    => $searchQuery,
      'response' => $response,
    ]);
  });

  /*
   * Redirect to the configured search page
   */
  add_action('template_redirect', function() {
    if (!Sitka\shortcode_redirect_enabled()) {
      return;
    }

    global $wp_query;
    $dest = get_option('sitka_search_redirect');
    if ($dest && $wp_query->is_search()) {
      $params = array_merge($_GET, [
        'sitka_search' => get_query_var('s')
      ]);
      unset($params['s']);

      wp_redirect($dest . '?' . http_build_query($params));
      exit;
    }
  });


  /*
   * Add the Sitka Insights global embed script.
   * This handles polls, alerts, and other frontend functionality.
   */
  add_action('wp_footer', function() {
    echo apply_filters('sitka/render', 'global-embed.js.php', [
      'site_id'      => get_option('sitka_site_id'),
      'feedback_uri' => apply_filters('sitka/feedback/embed_uri', ''),
    ]);
  });
});
