<?php

$result = $data['result'] ?? [];

?>
<div class="sitka-search-card">
	<?php // If filtering by post_type using metaTag, this is where that data will show up ?>
  <?php if (!empty($result['meta']['tags']) && is_array($result['meta']['tags'])): ?>
		<?php foreach ($result['meta']['tags'] as $tag) : ?>
			<div class="sitka-search-card__label"><?= apply_filters(
				'sitka/search/result/meta_tag_label',
				$tag,
				$result
			) ?></div>
		<?php endforeach; ?>
  <?php endif; ?>
	<h3 class="sitka-search-card__title"><?= $result['title'] ?></h3>
	<div class="sitka-search-card__snippet"><?= $result['snippet'] ?></div>
	<a class="sitka-search-card__link" href="<?= $result['url'] ?>">Learn More</a>
</div>