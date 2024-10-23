<?php

$field = $data['curated_result_field'] ?? [];

?>

<?php foreach ($field['value'] as $field_link) : ?>

  <a href="<?= $field_link['url'] ?>" target="<?= ($field_link['new_window'] ? '_blank' : '_self') ?>" class="curated-result-link"><?= $field_link['link_text'] ?></a>

<?php endforeach; ?>