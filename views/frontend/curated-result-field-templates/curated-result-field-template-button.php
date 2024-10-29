<?php

$field = $data['curated_result_field'] ?? [];

?>

<?php foreach ($field['value'] as $field_link) : ?>
  
  <?php $render = true; ?>
  <?php if ( ($field_link['link_text'] ?? false) === false || empty($field_link['link_text'])) : $render = false; endif; ?>
  <?php if ( ($field_link['url'] ?? false) === false || empty($field_link['url'])) : $render = false; endif; ?>

  <?php if ($render) : ?>
    <a href="<?= $field_link['url'] ?>" target="<?= (isset($field_link['new_window']) && $field_link['new_window'] ? '_blank' : '_self') ?>" class="curated-result-<?= $field['type'] ?>"><?= $field_link['link_text'] ?></a>
  <?php endif; ?>

<?php endforeach; ?>