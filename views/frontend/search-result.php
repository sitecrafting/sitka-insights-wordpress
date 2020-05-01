<?php

$result = $data['result'] ?? [];

?>
<div class="glt-search-card">
	<?php // If filtering by post_type using metaTag, this is where that data will show up ?>
  <?php if (!empty($result['meta']['tags']) && is_array($result['meta']['tags'])): ?>
		<div class="glt-search-card__label"><?= implode(', ', apply_filters('gearlab/meta_tag/human_readable', $result['meta']['tags'])) ?></div>
  <?php endif; ?>
	<h3 class="glt-search-card__title"><?= $result['title'] ?></h3>
	<div class="glt-search-card__snippet"><?= $result['snippet'] ?></div>
	<a class="glt-search-card__link" href="<?= $result['url'] ?>">Learn More</a>
</div>