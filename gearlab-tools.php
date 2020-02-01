<?php

/**
 * Plugin Name: GearLab Tools
 * Description: Integrate your WordPress site with the GearLab Tools platform
 * Plugin URI: https://gearlab.tools
 * Author: Coby Tamayo <ctamayo@sitecrafting.com>
 * Author URI: https://www.sitecrafting.com/
 * Version: 0.2.3
 */

// no script kiddiez
if (!defined('ABSPATH')) {
  return;
}

// require the composer autoloader, making educated guesses as to where it is
if (file_exists(ABSPATH . 'vendor/autoload.php')) {
  require_once ABSPATH . 'vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/wp-api.php';

use GearLab\Api\Client;
use GearLab\Plugin\AdminPage;
use GearLab\Plugin\Rest\GearLabRestController;
use GearLab\Plugin\TimberTwigHelper;

use Timber\Timber;


define('GEARLAB_PLUGIN_WEB_PATH', plugin_dir_url(__FILE__));
define('GEARLAB_PLUGIN_JS_ROOT', GEARLAB_PLUGIN_WEB_PATH . 'js');

/*
 * Add the main hook for getting an API client instance
 */
add_filter('gearlab/api/client', function() {
  // avoid instantiating the same object twice
  static $client;

  // instantiate a Client instance if we don't have one already
  return $client ?: new Client([
    'key'        => get_option('gearlab_api_key'),
    'collection' => get_option('gearlab_collection_id'),
    'baseUri'    => get_option('gearlab_base_uri'),
  ]);
});


/*
 * Disable default search
 */
add_action('init', GearLab\disable_default_wp_search::class);


/*
 * Add WP Admin pages (just the one page, actually 🐦)
 */
add_action('admin_menu', function() {
  // Create an admin page responsible for managing the main
  // GearLab Tools credentials
  $page = AdminPage::add_menu_page([
    'option_keys' => [
      'gearlab_api_key',
      'gearlab_collection_id',
      'gearlab_base_uri'
    ],
  ]);
  // Process any user updates
  if ($_POST) {
    $page->save_settings($_POST);
  }
  // Render the page
  $page->init()->add_meta_boxes();
});

/*
 * Add REST Routes
 */
add_action('rest_api_init', function() {
  $controller = new GearLabRestController();
  $controller->register_routes();
});

/*
 * Add JS for
 */
add_action('wp_enqueue_scripts', function() {
  $enqueue = apply_filters('gearlab/search/enqueue_js', !is_admin());
  if ($enqueue) {
    GearLab\enqueue_scripts();
  }
});

if (class_exists(WP_CLI::class)) {
  $command = new GearLab\WpCli\GearLabCommand();
  WP_CLI::add_command('gearlab', $command);
}

// Inject Timber-specific specializations
add_action('plugins_loaded', function() {
  if (class_exists(Timber::class)) {
    // Timber is running. Extend it!
    add_filter('get_twig', function(Twig_Environment $twig) {
      $twig->addFunction(new Twig_SimpleFunction(
        'gearlab_paginate_links',
        GearLab\paginate_links::class
      ));

      return $twig;
    });
  }
});
