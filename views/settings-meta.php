<form name="gearlab_tools_settings" method="post">
  <div class="glt-label">
    <label for="gearlab_api_key"><b>API Key</b></label>
  </div>
  <div class="glt-field">
    <input type="text" id="gearlab_api_key" name="gearlab_api_key" value="<?= $data['gearlab_api_key'] ?>">
  </div>

  <div class="glt-label">
    <label for="gearlab_collection_id"><b>Collection ID</b></label>
  </div>
  <div class="glt-field">
    <input type="text" id="gearlab_collection_id" name="gearlab_collection_id" value="<?= $data['gearlab_collection_id'] ?>">
  </div>

  <?php // TODO: configure a radio toggle between Staging/Live environments ?>
  <div class="glt-label">
    <label for="gearlab_base_uri"><b>Base URI</b></label>
  </div>
  <div class="glt-field">
    <input type="text" id="gearlab_base_uri" name="gearlab_base_uri" value="<?= $data['gearlab_base_uri'] ?>">
  </div>

  <div class="glt-field">
    <h3>Override WP Search</h3>
    <p>Unless you have custom-built an integration, to get site-wide search you must enable one of the following options:</p>
    <p>
      <input
        type="radio"
        id="gearlab-search-override-disabled"
        name="gearlab_search_enabled"
        value=""
        <?= empty($data['gearlab_search_enabled']) ? 'checked' : '' ?>
      />
      <label for="gearlab-search-override-disabled"><b>Disable overrides</b></label>
    </p>
    <?php $searchRedirectEnabled = $data['gearlab_search_enabled'] === GEARLAB_OVERRIDE_METHOD_SHORTCODE; ?>
    <p>
      <input
        type="radio"
        id="gearlab-search-shortcode"
        name="gearlab_search_enabled"
        value="<?= GEARLAB_OVERRIDE_METHOD_SHORTCODE ?>"
        <?= $searchRedirectEnabled ? 'checked' : '' ?>
      />
      <label for="gearlab-search-shortcode"><b>Redirect searches to a specific page (Recommended)</b></label>
    </p>
    <p class="glt-redirect-url-field" <?= $searchRedirectEnabled ? '' : 'style="display:none"' ?>>
      <label for="gearlab-search-page-redirect"><b>Redirect searches to:</b></label>
      <input
        type="text"
        id="gearlab-search-page-redirect"
        name="gearlab_search_redirect"
        value="<?= $data['gearlab_search_redirect'] ?>"
        placeholder="/search"
      />
      <em>Do not include "?" or else redirects may not work properly.</em>
    </p>
    <p>
      <input
        type="radio"
        id="gearlab-search-timber"
        name="gearlab_search_enabled"
        value="<?= GEARLAB_OVERRIDE_METHOD_TIMBER ?>"
        <?= $data['gearlab_search_enabled'] === GEARLAB_OVERRIDE_METHOD_TIMBER ? 'checked' : '' ?>
      />
      <label for="gearlab-search-timber"><b>Override default WordPress search template (Advanced - requires Timber plugin, or custom coding)</b></label>
    </p>
  </div>
  <script>
    jQuery(function($){

      $('[name=gearlab_search_enabled]').click(function() {
        if ($('[name=gearlab_search_enabled][value=shortcode]:checked').length) {
          $('.glt-redirect-url-field').show();
        } else {
          $('.glt-redirect-url-field').hide();
        }
      });

    });
  </script>

  <div class="gtl-form-footer">
    <button type="submit" value="update_gearlab_settings" class="button button-primary">Save settings</button>
  </div>

  <?php wp_nonce_field('gearlab_tools'); ?>
</form>
