<?php

$field = $data['curated_result_field'] ?? [];
?>

<?php foreach ($field['value'] as $field_value) : ?>
  
  <?php if ($field_value['text'] ?? false && !empty($field_value['text'])) : ?>
    <div class="curated-result-<?= $field['type'] ?>"><?= $field_value['text'] ?></div>
  <?php endif; ?>

<?php endforeach; ?>
