<?php

/*
 * Debug information display
 * Only shown when debug mode is enabled in settings
 */

$response    = $data['response'] ?? [];
$spamError   = $data['spam_error'] ?? null;
$debugMode   = get_option('sitka_debug_mode') == '1';

// Only display if debug mode is enabled and there's something to show
if (!$debugMode || (!$spamError && empty($response))) {
  return;
}

?>
<section class="sitka-debug-info" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
  <h3 style="margin-top: 0; color: #856404;">🐛 Debug Information</h3>
  
  <?php if ($spamError): ?>
    <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 4px;">
      <strong>Spam Mitigation Error:</strong>
      <p style="margin: 5px 0 0 0;"><?= esc_html($spamError) ?></p>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($response)): ?>
    <details style="margin-top: 10px;">
      <summary style="cursor: pointer; font-weight: bold; color: #856404;">📋 View Full API Response</summary>
      <pre style="background: #fff; padding: 10px; overflow-x: auto; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;"><?= esc_html(print_r($response, true)) ?></pre>
    </details>
  <?php endif; ?>
</section>
