<?php

$result = $data['curated_result'] ?? [];

?>
<div class="sitka-curated-result">
	<?= apply_filters('sitka/render', 'curated-result-templates/curated-result-template-'.$result['template'].'.php', array_merge($data, [
		'curated_result' => $result,
	])) ?>
</div>