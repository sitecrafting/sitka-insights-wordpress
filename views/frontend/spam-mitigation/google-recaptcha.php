<?php
$atts = $data['atts'] ?? [];
?>

<span class="<?= esc_attr($atts['class'] ?? '') ?>">
  <input type="text" name="g-recaptcha-response">
</span>