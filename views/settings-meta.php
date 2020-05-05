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
    <p>
      <input type="checkbox" id="gearlab_enabled" name="gearlab_search_enabled" value="1" <?= $data['gearlab_search_enabled'] === '1' ? 'checked' : '' ?>>
      <label for="gearlab_enabled"><b>Override default WordPress search</b></label>
    </p>
  </div>

  <div class="gtl-form-footer">
    <button type="submit" value="update_gearlab_settings" class="button button-primary">Save settings</button>
  </div>

  <?php wp_nonce_field('gearlab_tools'); ?>
</form>
