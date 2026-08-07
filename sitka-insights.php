<?php

/**
 * Plugin Name: Sitka Insights
 * Description: Integrate your WordPress site with the Sitka Insights platform
 * Plugin URI: https://www.sitkainsights.com
 * Author: SiteCrafting, Inc. <hello@sitecrafting.com>
 * Author URI: https://www.sitecrafting.com/
 * Version: 0.0.17b
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
      'sitka_mitigation_type',
      'sitka_mitigation_site_key',
      'sitka_mitigation_secret_key',
      'sitka_mitigation_project_id',
    ],
  ]);
  // Process any user updates - only if we're on the sitka-insights settings page
  if ($_POST && isset($_GET['page']) && $_GET['page'] === 'sitka-insights') {
    $page->save_settings($_POST);
    // Redirect to prevent form resubmission
    wp_safe_redirect(add_query_arg('page', 'sitka-insights', admin_url('options-general.php')));
    exit;
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

    $mitigation_type = get_option('sitka_mitigation_type');

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
    
    // Verify spam mitigation if enabled
    $mitigation_type = get_option('sitka_mitigation_type');
    $verification_error = null;
    
    if (!empty($mitigation_type) && !empty($searchQuery)) {
      // Get the token based on mitigation type
      $token = null;
      if ($mitigation_type === 'turnstile') {
        $token = $_POST['cf-turnstile-response'] ?? $_GET['cf-turnstile-response'] ?? null;
      } elseif ($mitigation_type === 'recaptcha') {
        $token = $_POST['g-recaptcha-response'] ?? $_GET['g-recaptcha-response'] ?? null;
      }
      
      // Verify the token
      if (!empty($token)) {
        $verification_result = Sitka\verify_spam_mitigation($token);
        
        if (!$verification_result['success']) {
          $verification_error = $verification_result['error'] ?? 'Spam verification failed';
          error_log('Sitka spam mitigation failed: ' . $verification_error);
        }
      } else {
        // No token provided but mitigation is enabled
        $verification_error = 'Security verification required';
        error_log('Sitka spam mitigation: No token provided');
      }
    }
    
    // If verification failed, return empty results
    if ($verification_error) {
      return apply_filters('sitka/render', 'search-results.php', [
        'post'     => $post,
        'query'    => $searchQuery,
        'response' => [],
        'spam_error' => $verification_error,
        'mitigation_type' => $mitigation_type,
      ]);
    }

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
      'mitigation_type' => $mitigation_type,
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

  /*
   * Shortcode for rendering spam mitigation (reCAPTCHA or Turnstile)
   */
  add_shortcode('sitka_spam_mitigation', function($atts = []) {
    $mitigation_type = get_option('sitka_mitigation_type');
    $site_key = get_option('sitka_mitigation_site_key');
    
    if (empty($mitigation_type) || empty($site_key)) {
      return '<!-- Spam mitigation not configured -->';
    }

    $atts = shortcode_atts([
      'class' => 'sitka-spam-mitigation',
      'size' => 'normal', // normal, compact, invisible (for reCAPTCHA)
      'theme' => 'light', // light, dark
    ], $atts);

    ob_start();
    
    if ($mitigation_type === 'turnstile') {
      // Cloudflare Turnstile
      wp_enqueue_script(
        'turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        [],
        null,
        true
      );
      echo apply_filters('sitka/render', 'spam-mitigation/turnstile.php', [
        'atts'      => $atts,
        'site_key'  => $site_key,
      ]);

    } elseif ($mitigation_type === 'recaptcha') {
      // Google reCAPTCHA Enterprise - use programmatic/invisible execution
      
      // Enqueue reCAPTCHA Enterprise API
      wp_enqueue_script(
        'recaptcha-enterprise-api',
        'https://www.google.com/recaptcha/enterprise.js?render=' . urlencode($site_key),
        [],
        null,
        true
      );
      
      // Enqueue our custom handler
      wp_enqueue_script(
        'sitka-recaptcha',
        SITKA_PLUGIN_JS_ROOT . '/recaptcha.js',
        ['recaptcha-enterprise-api'],
        '1.0.0',
        true
      );
      
      // Pass site key to JavaScript
      wp_localize_script('sitka-recaptcha', 'sitkaRecaptcha', [
        'siteKey' => $site_key,
      ]);
      
      echo apply_filters('sitka/render', 'spam-mitigation/google-recaptcha.php', [
        'atts'     => $atts,
      ]);
      
    }
    
    return ob_get_clean();
  });
});
