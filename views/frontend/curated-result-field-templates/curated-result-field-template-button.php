<?php

$field = $data['curated_result_field'] ?? [];

?>

<?php foreach ($field['value'] as $field_link) : ?>
  
  <a href="<?= $field_link['url'] ?>" target="<?= (isset($field_link['new_window']) && $field_link['new_window'] ? '_blank' : '_self') ?>" class="curated-result-<?= $field['type'] ?>"><?= $field_link['link_text'] ?></a>

<?php endforeach; ?>