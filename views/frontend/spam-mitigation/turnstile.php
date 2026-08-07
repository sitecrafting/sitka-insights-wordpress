<?php
$atts = $data['atts'] ?? [];
$site_key = get_option('sitka_turnstile_site_key');
?>

<div class="<?= esc_attr($atts['class'] ?? '') ?>">
  <div 
    class="cf-turnstile" 
    data-sitekey="<?= esc_attr($site_key) ?>"
    data-theme="<?= esc_attr($atts['theme']?? 'light') ?>"
  ></div>
</div>