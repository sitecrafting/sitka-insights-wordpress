<?php 

$result = $data['curated_result'] ?? [];

?>

<?php foreach ($result['fields'] as $field) : ?>
  
  <?= apply_filters('sitka/render', 'curated-result-field-templates/curated-result-field-template-'.$field['type'].'.php', array_merge($data, [
		'curated_result_field' => $field,
	])) ?>
  
<?php endforeach; ?>
