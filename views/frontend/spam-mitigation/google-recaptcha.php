<?php
$atts = $data['atts'] ?? [];
?>

<span class="<?= esc_attr($atts['class'] ?? '') ?>">
  <input type="hidden" name="g-recaptcha-response">
</span>