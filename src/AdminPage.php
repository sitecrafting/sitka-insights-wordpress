<?php

namespace GearLab\Plugin;

class AdminPage {
  protected $config;
  protected $settings;

  /**
   * Top-level static function for creating an admin page
   * responsible for managing various GLT settings.
   *
   * @param array $config the configuration for this page, which includes
   * the settings this page is in charge of managing and the view it renders.
   * @return AdminPage the newly created AdminPage instance
   */
  public static function add_options_page(array $config) : self {
    $page = new static($config);

    // Add this page to the main WP Admin
    add_options_page(
      'GearLab Tools',
      'GearLab Tools',
      'manage_options',
      'gearlab-tools',
      [$page, 'render']
    );

    return $page;
  }

  /**
   * Constructor.
   */
  public function __construct(array $config) {
    $this->config   = $config;
    $this->settings = [];
  }

  /**
   * Load setting values from the database
   *
   * @return AdminPage returns the AdminPage instance
   */
  public function init() : self {
    foreach (($this->config['option_keys'] ?? []) as $key) {
      $this->settings[$key] = get_option($key);
    }

    return $this;
  }

  /**
   * Define meta boxes for this admin page
   */
  public function add_meta_boxes() {
    add_meta_box(
      'gearlab_tools_settings',
      'GearLab Tools Settings',
      [$this, 'render_settings_meta_box'],
      'gearlab-tools', 'normal'
    );
  }

  /**
   * Generic callback for rendering this settings page
   */
  public function render() {
    wp_create_nonce('gearlab_tools');
    echo $this->render_view('admin-page.php');
  }

  /**
   * Callback for the main settings meta box
   */
  public function render_settings_meta_box() {
    echo $this->render_view('settings-meta.php', $this->settings);
  }

  /**
   * Save the settings this page is responsible for managing
   *
   * @param array $request the current request params
   */
  public function save_settings(array $request) {
    $nonce = $request['_wpnonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'gearlab_tools')) {
      return;
    }

    // update each option that this page is responsible for managing
    foreach ($this->config['option_keys'] as $key) {
      update_option($key, $request[$key]);
    }
  }

  private function render_view(string $view, array $data = []) : string {
    ob_start();
    include __DIR__ . '/../views/' . $view;
    return ob_get_clean();
  }
}

?>
