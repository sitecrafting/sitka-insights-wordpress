<?php

namespace Sitka\Plugin;

class AdminPage {
  protected $config;
  protected $settings;

  /**
   * Top-level static function for creating an admin page
   * responsible for managing various Sitka Insights settings.
   *
   * @param array $config the configuration for this page, which includes
   * the settings this page is in charge of managing and the view it renders.
   * @return AdminPage the newly created AdminPage instance
   */
  public static function add_options_page(array $config) : self {
    $page = new static($config);

    // Add this page to the main WP Admin
    add_options_page(
      'Sitka Insights',
      'Sitka Insights',
      'manage_options',
      'sitka-insights',
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
      'sitka-insights-settings',
      'Sitka Insights Settings',
      [$this, 'render_settings_meta_box'],
      'sitka-insights',
      'normal'
    );
  }

  /**
   * Generic callback for rendering this settings page
   */
  public function render() {
    wp_create_nonce('sitka-insights');
    echo $this->render_view('admin-page.php', ['page' => $this]);
  }

  /**
   * Get any message to display to the user
   */
  public function get_message() {
    return get_transient('sitka_admin_message');
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
    if (!wp_verify_nonce($nonce, 'sitka-insights')) {
      $this->set_error_message('Security verification failed. Please try again.');
      return;
    }

    // Validate required fields
    $required_fields = [
      'sitka_site_id' => 'Site ID',
      'sitka_api_key' => 'API Key',
      'sitka_collection_id' => 'Engine ID',
      'sitka_mitigation_site_key' => 'Mitigation Site Key',
      'sitka_mitigation_secret_key' => 'Mitigation API Key / Secret',
    ];

    // Check if redirect is enabled and validate redirect URL
    if (isset($request['sitka_search_enabled']) && $request['sitka_search_enabled'] === 'shortcode') {
      $required_fields['sitka_search_redirect'] = 'Redirect searches to';
    }

    // Check if reCAPTCHA is selected and validate project ID
    if (isset($request['sitka_mitigation_type']) && $request['sitka_mitigation_type'] === 'recaptcha') {
      $required_fields['sitka_mitigation_project_id'] = 'reCAPTCHA Project ID';
    }

    $errors = [];
    foreach ($required_fields as $field => $label) {
      if (empty($request[$field])) {
        $errors[] = $label;
      }
    }

    if (!empty($errors)) {
      $this->set_error_message('Please fill in all required fields: ' . implode(', ', $errors));
      return;
    }

    // update each option that this page is responsible for managing
    try {
      foreach ($this->config['option_keys'] as $key) {
        update_option($key, $request[$key] ?? false);
      }
      $this->set_success_message('Settings saved successfully.');
    } catch (\Exception $e) {
      $this->set_error_message('An error occurred while saving settings. Please try again.');
    }
  }

  /**
   * Set a success message to display to the user
   */
  private function set_success_message($message) {
    set_transient('sitka_admin_message', [
      'type' => 'success',
      'text' => $message,
    ], 30);
  }

  /**
   * Set an error message to display to the user
   */
  private function set_error_message($message) {
    set_transient('sitka_admin_message', [
      'type' => 'error',
      'text' => $message,
    ], 30);
  }

  private function render_view(string $view, array $data = []) : string {
    ob_start();
    include __DIR__ . '/../views/' . $view;
    return ob_get_clean();
  }
}

?>
