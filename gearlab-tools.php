<?php

/**
 * Plugin Name: GearLab Tools
 * Description: Integrate your WordPress site with the GearLab Tools platform
 * Plugin URI: https://gearlab.tools
 * Author: Coby Tamayo <ctamayo@sitecrafting.com>
 * Author URI: https://www.sitecrafting.com/
 * Version: 0.1.0
 */

// no script kiddiez
if (!defined('ABSPATH')) {
  return;
}

// require the composer autoloader, making educated guesses as to where it is
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(ABSPATH . '/vendor/autoload.php')) {
  require_once ABSPATH . '/vendor/autoload.php';
}

require_once __DIR__ . '/wp-api.php';

use GearLab\Api\Client;
use GearLab\Plugin\AdminPage;
use GearLab\Plugin\Rest\GearLabRestController;


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
  if (!is_admin() && is_search()) {
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
  }
});

if (class_exists(WP_CLI::class)) {
  $command = new GearLab\WpCli\GearLabCommand();
  WP_CLI::add_command('gearlab', $command);
}
