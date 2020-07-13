<form name="sitka-insights-settings" method="post" autofill="false">
  <h3>General Settings</h3>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_site_id"><b>Site ID</b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_site_id" name="sitka_site_id" value="<?= $data['sitka_site_id'] ?>">
      <p><span class="dashicons dashicons-editor-help"></span>Not sure where to find your Site ID? <a href="https://dashboard.sitkainsights.com/embed/install">Get step-by-step instructions</a>.</p>
    </div>
  </div>

  <h3>Search Settings</h3>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_api_key"><b>API Key</b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_api_key" name="sitka_api_key" value="<?= $data['sitka_api_key'] ?>">
    </div>
  </div>

  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_collection_id"><b>Engine ID</b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_collection_id" name="sitka_collection_id" value="<?= $data['sitka_collection_id'] ?>">
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
      <div class="sitka-field__env-option">
        <input type="radio" id="env-stg" name="sitka_environment" value="staging" <?= $data['sitka_environment'] === 'staging' ? 'checked' : '' ?>>
        <label for="env-stg">Staging</label>
      </div>
    </div>
  </div>

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
        <?= empty($data['sitka_search_enabled']) ? 'checked' : '' ?>
      />
      <label for="sitka-search-override-disabled"><b>Disable overrides</b></label>
    </p>
    <?php $searchRedirectEnabled = $data['sitka_search_enabled'] === SITKA_OVERRIDE_METHOD_SHORTCODE; ?>
    <p>
      <input
        type="radio"
        id="sitka-search-shortcode"
        name="sitka_search_enabled"
        value="<?= SITKA_OVERRIDE_METHOD_SHORTCODE ?>"
        <?= $searchRedirectEnabled ? 'checked' : '' ?>
      />
      <label for="sitka-search-shortcode"><b>Enable Sitka Search and redirect searches to the page where you added the shortcode above. (Recommended)</b></label>
    </p>
    <p class="sitka-redirect-url-field" <?= $searchRedirectEnabled ? '' : 'style="display:none"' ?>>
      <label for="sitka-search-page-redirect"><b>Redirect searches to:</b></label>
      <input
        type="text"
        id="sitka-search-page-redirect"
        name="sitka_search_redirect"
        value="<?= $data['sitka_search_redirect'] ?>"
        placeholder="/search"
      />
    </p>
  </div>
  <script>
    jQuery(function($){

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

    });
  </script>

  <div class="sitka-form-footer">
    <button type="submit" value="update_sitka_settings" class="button button-primary">Save settings</button>
  </div>

  <?php wp_nonce_field('sitka-insights'); ?>
</form>
