<?php

$field = $data['curated_result_field'] ?? [];

?>

<?php foreach ($field['value'] as $field_link) : ?>
  
  <h3 class="curated-result-<?= $field['type'] ?>"><?= $field_link['headline'] ?></h3>

<?php endforeach; ?>