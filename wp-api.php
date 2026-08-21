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
    'query'        => apply_filters('sitka/search/query', $_GET['s'] ?? ''),
    'resLength'    => $count,
    'resOffset'    => $offset,
    'metaTag'      => apply_filters('sitka/search/meta_tag', ''),
    'literalQuery' => apply_filters('sitka/search/literal_query', ''),
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

/**
 * Verify spam mitigation token (reCAPTCHA Enterprise or Turnstile)
 *
 * @param string $token The token received from the client
 * @param string $remote_ip Optional: The user's IP address
 * @return array Response with 'success' boolean and optional 'error' message
 */
function verify_spam_mitigation($token, $remote_ip = null) : array {
  $mitigation_type = get_option('sitka_mitigation_type');
  $secret_key = get_option('sitka_mitigation_secret_key');

  if (empty($mitigation_type) || empty($secret_key)) {
    return [
      'success' => false,
      'error' => 'Spam mitigation not configured'
    ];
  }

  if (empty($token)) {
    return [
      'success' => false,
      'error' => 'No token provided'
    ];
  }

  $remote_ip = $remote_ip ?: $_SERVER['REMOTE_ADDR'];

  if ($mitigation_type === 'turnstile') {
    // Verify Cloudflare Turnstile token
    $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
      'body' => [
        'secret' => $secret_key,
        'response' => $token,
        'remoteip' => $remote_ip,
      ],
    ]);

    if (is_wp_error($response)) {
      return [
        'success' => false,
        'error' => 'Failed to verify token: ' . $response->get_error_message()
      ];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    return [
      'success' => $body['success'] ?? false,
      'error' => isset($body['error-codes']) ? implode(', ', $body['error-codes']) : null,
      'results-expired' => isset($body['error-codes']) && in_array('timeout-or-duplicate', $body['error-codes']),
    ];

  } elseif ($mitigation_type === 'recaptcha') {
    // Verify Google reCAPTCHA Enterprise token
    $project_id = get_option('sitka_mitigation_project_id');
    $api_key = $secret_key; // API key stored in secret_key field
    
    if (empty($project_id)) {
      return [
        'success' => false,
        'error' => 'reCAPTCHA Enterprise Project ID not configured'
      ];
    }

    $site_key = get_option('sitka_mitigation_site_key');
    
    // Create assessment using reCAPTCHA Enterprise API
    $assessment_data = [
      'event' => [
        'token' => $token,
        'siteKey' => $site_key,
        'userIpAddress' => $remote_ip,
      ]
    ];

    $response = wp_remote_post(
      "https://recaptchaenterprise.googleapis.com/v1/projects/{$project_id}/assessments?key={$api_key}",
      [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => json_encode($assessment_data),
      ]
    );

    if (is_wp_error($response)) {
      return [
        'success' => false,
        'error' => 'Failed to verify token: ' . $response->get_error_message()
      ];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Verify token validity
    $token_properties = $body['tokenProperties'] ?? [];
    $is_valid = ($token_properties['valid'] ?? false) === true;
    
    if (!$is_valid) {
      return [
        'success' => false,
        'error' => $token_properties['invalidReason'] ?? 'Invalid token',
        'results-expired' => in_array($token_properties['invalidReason'] ?? '', ['EXPIRED', 'DUPE']),
      ];
    }

    // Get risk analysis
    $risk_analysis = $body['riskAnalysis'] ?? [];
    $score = $risk_analysis['score'] ?? 0; // 0.0 to 1.0 (higher is more likely human)
    
    // Consider score >= 0.5 as success (adjust threshold as needed)
    $success = $score >= 0.5;
    
    return [
      'success' => $success,
      'score' => $score,
      'reasons' => $risk_analysis['reasons'] ?? [],
      'error' => !$success ? 'Score too low (possible bot)' : null
    ];
  }

  return [
    'success' => false,
    'error' => 'Unknown mitigation type'
  ];
}
