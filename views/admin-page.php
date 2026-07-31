<?php
// Display success or error messages
$message = $data['page']->get_message();
if ($message):
  $class = $message['type'] === 'success' ? 'notice-success' : 'notice-error';
  // Delete the transient after displaying
  delete_transient('sitka_admin_message');
?>
  <div class="notice <?= $class ?> is-dismissible">
    <p><?= esc_html($message['text']) ?></p>
  </div>
<?php endif; ?>

<div id="poststuff">  
  <div id="post-body" class="columns-2">
    <div class="postbox-container">
      <?php do_meta_boxes('sitka-insights', 'normal', []); ?>
    </div>
  </div>
</div>
