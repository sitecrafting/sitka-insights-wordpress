<?php

$params    = $data['url_params'];
$paginator = $data['paginator'];
$markers   = $paginator->page_markers($params);

/* START MARKUP */
if ($paginator->page_count() > 1) : ?>
  <div class="pagination">
    <?php foreach ($markers as $marker) : ?>
      <?php if (!empty($marker['previous'])) : ?>
        <a class="page-numbers prev" href="<?= $paginator->previous_page_url($params) ?>" rel="prev">Previous</a>
      <?php endif; ?>

      <?php if (!empty($marker['current'])) : ?>
        <span aria-current="page" class="page-numbers current"><?= $marker['page_num'] ?></span>
      <?php elseif (!empty($marker['filler'])) : ?>
        <span class="page-numbers dots">…</span>
      <?php elseif (!empty($marker['page_num'])) : ?>
        <a class="page-numbers" href="<?= $marker['url'] ?>"><?= $marker['page_num'] ?></a>
      <?php endif ?>

      <?php if (!empty($marker['next'])) : ?>
        <a class="page-numbers next" href="<?= $paginator->next_page_url($params) ?>" rel="next">Next</a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
