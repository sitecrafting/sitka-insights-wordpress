<?php

$field = $data['curated_result_field'] ?? [];

?>

<?php foreach ($field['value'] as $field_link) : ?>
  
  <?php if ($field_value['headline'] ?? false && !empty($field_value['headline'])) : ?>
    <h3 class="curated-result-<?= $field['type'] ?>"><?= $field_link['headline'] ?></h3>
  <?php endif; ?>

<?php endforeach; ?>