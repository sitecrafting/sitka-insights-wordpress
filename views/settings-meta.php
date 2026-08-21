<form name="sitka-insights-settings" method="post" autofill="false">
  <p><span class="dashicons dashicons-editor-help"></span>Not sure where to find your Site ID, API Key, or Engine ID? <a href="https://dashboard.sitkainsights.com/embed/install">Get step-by-step instructions</a>.</p>

  <h3>General Settings</h3>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_site_id"><b>Site ID <span class="required">*</span></b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_site_id" name="sitka_site_id" value="<?= $data['sitka_site_id'] ?>" required>
      <p>Enter your site ID to enable additional features likes polls and alerts.</p>
    </div>
  </div>

  <h3>Search Settings</h3>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_api_key"><b>API Key <span class="required">*</span></b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_api_key" name="sitka_api_key" value="<?= $data['sitka_api_key'] ?>" required>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_collection_id"><b>Engine ID <span class="required">*</span></b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_collection_id" name="sitka_collection_id" value="<?= $data['sitka_collection_id'] ?>" required>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label><b>Environment</b></label>
    </div>
    <div class="sitka-field__input">
      <div class="sitka-field__env-option">
        <input type="radio" id="env-prd" name="sitka_environment" value="production" <?= $data['sitka_environment'] === 'production' ? 'checked' : '' ?>>
        <label for="env-prd">Production</label>
      </div>
      <?php
      /* Option removed per request from Phil Price
      * If a developer needs to test someting against the staging instance of the Sitka, simply remove the comments around this block of code to begin the staging option back in. 
      * This should be temporary change will be reverted when the plugin is reinstalled using composer.
      <div class="sitka-field__env-option">
         <input type="radio" id="env-stg" name="sitka_environment" value="staging" <?= $data['sitka_environment'] === 'staging' ? 'checked' : '' ?>>
         <label for="env-stg">Staging</label>
      </div>
      */
      ?>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label><b>Enable “Search instead” for search term</b></label>
    </div>
    <div class="sitka-field__input">
      <div class="sitka-field__dym-option">
        <input type="radio" id="sitka_search_instead_enabled" name="sitka_search_instead_enabled" value="enabled" <?= $data['sitka_search_instead_enabled'] === 'enabled' ? 'checked' : '' ?>>
        <label for="sitka_search_instead_enabled">Enable</label>
      </div>
      <div class="sitka-field__dym-option">
        <input type="radio" id="sitka_search_instead_disabled" name="sitka_search_instead_enabled" value="disabled" <?= $data['sitka_search_instead_enabled'] !== 'enabled' ? 'checked' : '' ?>>
        <label for="sitka_search_instead_disabled">Disable</label>
      </div>
      <p>When enabled, Sitka will present a link to override automatic typo correction.</p>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label><b>Include Curated Results on the search page</b></label>
    </div>
    <div class="sitka-field__input">
      <div class="sitka-field__dym-option">
        <input type="radio" id="sitka_search_curated_results_enabled" name="sitka_search_curated_results_enabled" value="enabled" <?= $data['sitka_search_curated_results_enabled'] === 'enabled' ? 'checked' : '' ?>>
        <label for="sitka_search_curated_results_enabled">Enable</label>
      </div>
      <div class="sitka-field__dym-option">
        <input type="radio" id="sitka_search_curated_results_disabled" name="sitka_search_curated_results_enabled" value="disabled" <?= $data['sitka_search_curated_results_enabled'] !== 'enabled' ? 'checked' : '' ?>>
        <label for="sitka_search_curated_results_disabled">Disable</label>
      </div>
      <p>When enabled, curated result will appear in search results. Please note: This feature must first be enabled in the Sitka Dashboard.</p>
    </div>
  </div>
  <h3>Add Shortcode To Page</h3>
  <div class="sitka-field">
    <p>Please insert this shortcode on the page where you would like search results to appear.</p>
    <textarea id="sitka-shortcode" class="sitka-shortcode-text" disabled>[sitka_search]</textarea>
    <button type="button" class="button button-secondary sitka-shortcode-copy">Copy to clipboard</button>
    <br>
    <br>
    <p>
      <input
        type="radio"
        id="sitka-search-override-disabled"
        name="sitka_search_enabled"
        value=""
        <?= empty($data['sitka_search_enabled']) ? 'checked' : '' ?> />
      <label for="sitka-search-override-disabled"><b>Disable overrides</b></label>
    </p>
    <?php $searchRedirectEnabled = $data['sitka_search_enabled'] === SITKA_OVERRIDE_METHOD_SHORTCODE; ?>
    <p>
      <input
        type="radio"
        id="sitka-search-shortcode"
        name="sitka_search_enabled"
        value="<?= SITKA_OVERRIDE_METHOD_SHORTCODE ?>"
        <?= $searchRedirectEnabled ? 'checked' : '' ?> />
      <label for="sitka-search-shortcode"><b>Enable Sitka Search and redirect searches to the page where you added the shortcode above. (Recommended)</b></label>
    </p>
    <p class="sitka-redirect-url-field" <?= $searchRedirectEnabled ? '' : 'style="display:none"' ?>>
      <label for="sitka-search-page-redirect"><b>Redirect searches to: </b></label>
      <input
        type="text"
        id="sitka-search-page-redirect"
        name="sitka_search_redirect"
        value="<?= $data['sitka_search_redirect'] ?>"
        placeholder="/search"
        />
    </p>
  </div>

  <h3>Spam Mitigation</h3>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_mitigation_type"><b>Mitigation Type</b></label>
    </div>
    <div class="sitka-field__input">
      <select id="sitka_mitigation_type" name="sitka_mitigation_type">
        <option value="turnstile" <?= isset($data['sitka_mitigation_type']) && $data['sitka_mitigation_type'] === 'turnstile' ? 'selected' : '' ?>>Turnstile</option>
        <option value="recaptcha" <?= isset($data['sitka_mitigation_type']) && $data['sitka_mitigation_type'] === 'recaptcha' ? 'selected' : '' ?>>reCAPTCHA Enterprise</option>
      </select>
      <p>Select the spam mitigation service to use for form protection.</p>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex sitka-mitigation-description" style="display: none;">
    <div class="sitka-field__label">
      <label><b>Instructions</b></label>
    </div>
    <div class="sitka-field__input">
      <div class="sitka-mitigation-description__turnstile" style="display: none;">
        <p><strong>Cloudflare Turnstile Setup:</strong></p>
        <ol>
          <li>Sign in to your <a href="https://dash.cloudflare.com/" target="_blank">Cloudflare dashboard</a></li>
          <li>Navigate to Turnstile in the sidebar</li>
          <li>Create a new site or select an existing one</li>
          <li>Copy the Site Key and Secret Key</li>
          <li>Paste them into the fields above</li>
        </ol>
        <p>For more information, visit the <a href="https://developers.cloudflare.com/turnstile/" target="_blank">Turnstile documentation</a>.</p>
      </div>
      <div class="sitka-mitigation-description__recaptcha" style="display: none;">
        <p><strong>Google reCAPTCHA Enterprise Setup:</strong></p>
        <p style="background: #fff3cd; padding: 10px; border-left: 3px solid #ffc107;"><strong>⚠️ Important:</strong> You MUST use <strong>reCAPTCHA Enterprise</strong> (not the old reCAPTCHA v2/v3 from <code>google.com/recaptcha/admin</code>). Keys from the old admin console will NOT work and will show "Invalid key type" errors.</p>
        <ol>
          <li>Go to the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> (NOT the old reCAPTCHA admin console)</li>
          <li>Create a new project or select an existing one from the dropdown at the top of the page</li>
          <li><strong>Find your Project ID:</strong> Click on the project dropdown at the top. You'll see your project name and below it the <strong>Project ID</strong> (e.g., "my-project-12345"). Copy this ID - you'll need it later.</li>
          <li>Navigate to <a href="https://console.cloud.google.com/security/recaptcha" target="_blank">reCAPTCHA Enterprise</a> in the left sidebar menu (under "Security")</li>
          <li>Click <strong>"Enable API"</strong> if not already enabled - this is required!</li>
          <li>Click <strong>"Create Key"</strong> and select:</li>
          <ul style="margin-top: 5px; margin-bottom: 5px;">
            <li><strong>Display name:</strong> Give it a name (e.g., "My Website")</li>
            <li><strong>Platform type:</strong> Choose "Website"</li>
            <li><strong>Domains:</strong> Add your domain(s) without http:// (e.g., "example.com")</li>
            <li><strong>Integration type:</strong> Select <strong>"Score-based"</strong> - this is the only type that works with this plugin</li>
            <li><strong>Important:</strong> Do NOT select "Checkbox" - it will cause "Invalid key type" errors</li>
          </ul>
          <li>After creating the key, copy the <strong>Site Key</strong> displayed</li>
          <li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">APIs & Services > Credentials</a></li>
          <li>Click "Create Credentials" > "API Key"</li>
          <li>Restrict the API key to only allow "reCAPTCHA Enterprise API" (recommended for security)</li>
          <li>Copy the <strong>API Key</strong></li>
        </ol>
        <p><strong>Summary of what to enter:</strong></p>
        <ul>
          <li><strong>Site Key:</strong> From the reCAPTCHA Enterprise key you created (step 7)</li>
          <li><strong>Project ID:</strong> Your Google Cloud Project ID (found in top navigation, looks like "my-project-12345")</li>
          <li><strong>API Key:</strong> From the credentials page (step 11)</li>
        </ul>
        <p><strong>Common Errors:</strong></p>
        <ul style="color: #d63384;">
          <li><strong>"Invalid key type"</strong> - Two possible causes:
            <ol>
              <li>You're using a key from the old <code>google.com/recaptcha/admin</code> console. You must create a new key in the Google Cloud Console's reCAPTCHA Enterprise section.</li>
              <li>You created a "Checkbox" key instead of "Score-based" key. Delete it and create a new "Score-based" key.</li>
            </ol>
          </li>
          <li><strong>"API not enabled"</strong> - Make sure you enabled the reCAPTCHA Enterprise API in step 5.</li>
        </ul>
        <p>For more information, visit the <a href="https://cloud.google.com/recaptcha-enterprise/docs" target="_blank">reCAPTCHA Enterprise documentation</a>.</p>
      </div>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_mitigation_site_key"><b>Site Key <span class="required">*</span></b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_mitigation_site_key" name="sitka_mitigation_site_key" value="<?= isset($data['sitka_mitigation_site_key']) ? $data['sitka_mitigation_site_key'] : '' ?>" required>
      <p>For Turnstile: Site Key. For reCAPTCHA: Site Key from reCAPTCHA Enterprise.</p>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex sitka-field-recaptcha-project" style="display: none;">
    <div class="sitka-field__label">
      <label for="sitka_mitigation_project_id"><b>Project ID <span class="required">*</span></b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_mitigation_project_id" name="sitka_mitigation_project_id" value="<?= isset($data['sitka_mitigation_project_id']) ? $data['sitka_mitigation_project_id'] : '' ?>" placeholder="my-project-12345">
      <p>Your Google Cloud Project ID. Find it by clicking the project dropdown at the top of the Google Cloud Console. It's shown below the project name (e.g., "my-project-12345").</p>
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_mitigation_secret_key"><b>API Key / Secret <span class="required">*</span></b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_mitigation_secret_key" name="sitka_mitigation_secret_key" value="<?= isset($data['sitka_mitigation_secret_key']) ? $data['sitka_mitigation_secret_key'] : '' ?>" required>
      <p>For Turnstile: Secret Key. For reCAPTCHA: API Key from Google Cloud Console.</p>
    </div>
  </div>

  <h3>Developer Settings</h3>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label><b>Debug Mode</b></label>
    </div>
    <div class="sitka-field__input">
      <div class="sitka-field__debug-option">
        <input type="radio" id="sitka_debug_enabled" name="sitka_debug_mode" value="1" <?= isset($data['sitka_debug_mode']) && $data['sitka_debug_mode'] == '1' ? 'checked' : '' ?>>
        <label for="sitka_debug_enabled">Enable</label>
      </div>
      <div class="sitka-field__debug-option">
        <input type="radio" id="sitka_debug_disabled" name="sitka_debug_mode" value="0" <?= !isset($data['sitka_debug_mode']) || $data['sitka_debug_mode'] == '0' ? 'checked' : '' ?>>
        <label for="sitka_debug_disabled">Disable</label>
      </div>
      <p>When enabled, additional debug information will be displayed on the search results page to help troubleshoot issues. Make sure to disable on production sites.</p>
    </div>
  </div>

  <script>
    jQuery(function($) {

      $('[name=sitka_search_enabled]').click(function() {
        if ($('[name=sitka_search_enabled][value=shortcode]:checked').length) {
          $('.sitka-redirect-url-field').show();
        } else {
          $('.sitka-redirect-url-field').hide();
        }
      });

      $('.sitka-shortcode-copy').click(function(e) {
        e.preventDefault();

        // copy shortcode text to clipboard
        $textarea = $('.sitka-shortcode-text');
        $textarea.attr('disabled', false);
        $textarea.get(0).select();
        document.execCommand('copy');
        $textarea.attr('disabled', true);

        var $btn = $(this);
        var updatedText = $btn.text() === 'Copied!' ? 'Copied again!' : 'Copied!';
        $(this).text(updatedText);
      });

      // Handle spam mitigation type change
      $('#sitka_mitigation_type').on('change', function() {
        var selectedType = $(this).val();

        // Hide all descriptions
        $('.sitka-mitigation-description__turnstile').hide();
        $('.sitka-mitigation-description__recaptcha').hide();

        // Show/hide project ID field for reCAPTCHA
        if (selectedType === 'recaptcha') {
          $('.sitka-field-recaptcha-project').show();
          $('#sitka_mitigation_project_id').attr('required', true);
        } else {
          $('.sitka-field-recaptcha-project').hide();
          $('#sitka_mitigation_project_id').attr('required', false);
        }

        // Show the appropriate description
        if (selectedType === 'turnstile') {
          $('.sitka-mitigation-description').show();
          $('.sitka-mitigation-description__turnstile').show();
        } else if (selectedType === 'recaptcha') {
          $('.sitka-mitigation-description').show();
          $('.sitka-mitigation-description__recaptcha').show();
        } else {
          $('.sitka-mitigation-description').hide();
        }
      });

      // Trigger change on page load to show the correct description
      $('#sitka_mitigation_type').trigger('change');

    });
  </script>

  <div class="sitka-form-footer">
    <button type="submit" value="update_sitka_settings" class="button button-primary">Save settings</button>
  </div>

  <?php wp_nonce_field('sitka-insights'); ?>
</form>