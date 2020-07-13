<form name="sitka-insights-settings" method="post">
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
      <label for="sitka_collection_id"><b>Collection ID</b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_collection_id" name="sitka_collection_id" value="<?= $data['sitka_collection_id'] ?>">
    </div>
  </div>

  <?php // TODO: configure a radio toggle between Staging/Live environments ?>
  <div class="sitka-field sitka-field--flex">
    <div class="sitka-field__label">
      <label for="sitka_base_uri"><b>Base URI</b></label>
    </div>
    <div class="sitka-field__input">
      <input type="text" id="sitka_base_uri" name="sitka_base_uri" value="<?= $data['sitka_base_uri'] ?>">
    </div>
  </div>

  <div class="sitka-field">
    <h3>Override WP Search</h3>
    <p>Use this shortcode on any page:</p>
    <textarea class="sitka-shortcode-text" disabled>[sitka_search]</textarea>
    <button type="button" class="button button-secondary sitka-shortcode-copy">Copy to clipboard</button>
    <p>Unless you have custom-built an integration, for your theme's normal search form to work, you must enable one of the following options:</p>
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
      <label for="sitka-search-shortcode"><b>Redirect searches to a specific page (Recommended)</b></label>
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
      <em>Do not include "?" or else redirects may not work properly. The page you specify here must contain the shortcode above.</em>
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
