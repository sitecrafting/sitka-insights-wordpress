<?php

$field = $data['curated_result_field'] ?? [];

?>

<?php foreach ($field['value'] as $field_link) : ?>
  
  <div class="curated-result-<?= $field['type'] ?>"><?= $field_link['text'] ?></div>

<?php endforeach; ?>
