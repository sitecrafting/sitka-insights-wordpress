<?php

/*
 * Generic search results page content
 */

$response    = $data['response'] ?? [];
$post        = $data['post'] ?? $GLOBALS['post'] ?? null;
$searchQuery = $data['query'] ?? '';

?>
<section class="glt-search-form-container">
  <div class="container">
    <div class="global-search">
      <h5>Search</h5>
      <form role="search" method="get" id="searchform" class="searchform" action="<?= get_permalink($post) ?>">
        <input
          type="text"
          value="<?= $searchQuery ?>"
          name="glt_search"
          id="search-term"
          placeholder="Enter keyword or phrase"
          title="Enter keyword or phrase"
        />
        <button id="searchsubmit" type="submit" class="btn"><span>Search</span></button>
      </form>
    </div><!-- global-search -->
  </div><!-- container -->
</section>
<section class="gtl-search-results-container">
  <div class="container">

    <?php if (!empty($response['results'])) : ?>
      <?php foreach ($response['results'] as $result) : ?>

        <?= apply_filters('gearlab/render', 'search-result.php', array_merge($data, [
          'result' => $result,
        ])) ?>

      <?php endforeach; ?>
    <?php endif; ?>

    <div class="post-navigation">
      <?= GearLab\paginate_links($response) ?>
    </div>
  </div>
</section>