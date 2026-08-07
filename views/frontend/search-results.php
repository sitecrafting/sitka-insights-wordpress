<?php

/*
 * Generic search results page content
 */

$response    = $data['response'] ?? [];
$post        = $data['post'] ?? $GLOBALS['post'] ?? null;
$searchQuery = stripslashes($data['query']) ?? '';
$originalQuery = $response['originalQueryPhrase'] ?? '';
$supersedingSuggestion = $response['supersedingSuggestion'] ?? '';
$didYouMeanOption = get_option('sitka_search_instead_enabled') ?? 'disabled';
$curatedResultsOption = get_option('sitka_search_curated_results_enabled') ?? 'disabled';
$curatedResultsEnabled = $response['curatedResultsEnabled'] ?? false;
$spamError = $data['spam_error'] ?? null;
$mitigation_type = $data['mitigation_type'] ?? null;
?>
<section class="sitka-search-form-container">
  <div class="container">
    <div class="global-search">
      <h5>Search</h5>
      <form role="search" method="get" id="searchform" class="searchform sitka-search-form" action="<?= get_permalink($post) ?>">
        <input
          type="text"
          value="<?= esc_attr($searchQuery) ?>"
          name="sitka_search"
          id="search-term"
          placeholder="Enter keyword or phrase"
          title="Enter keyword or phrase"
        />
        <?php if (isset($response['suggestionSupersededQuery']) && $response['suggestionSupersededQuery']  && $didYouMeanOption == "enabled") : ?>
          <div class="superseding-suggestion-container">
            <p>
              Showing results for <?= $supersedingSuggestion ?> </br> 
              Search instead for <a href="<?= get_permalink($post) . "?sitka_search=" . $originalQuery . "&sitka_literal_query=1" ?>"><?= $originalQuery ?></a>
            </p> 
          </div>
        <?php endif; ?>
        <?php if ($mitigation_type): ?>
          <!-- Spam mitigation widget -->
          <?php echo do_shortcode('[sitka_spam_mitigation theme="light" size="normal"]'); ?>
        <?php endif; ?>
        <button id="searchsubmit" type="submit" class="btn"><span>Search</span></button>
      </form>
    </div><!-- global-search -->
  </div><!-- container -->
</section>

<?php if ($spamError) : ?>
  <section class="sitka-search-error-container">
    <div class="container">
      <div class="sitka-spam-error alert alert-danger" role="alert">
        <strong>Security Verification Failed:</strong> <?= esc_html($spamError) ?>
        <p>Please try your search again. If this problem persists, contact the site administrator.</p>
      </div>
    </div>
  </section>
  <style>
    .sitka-spam-error {
      background-color: #f8d7da;
      border: 1px solid #f5c2c7;
      color: #842029;
      padding: 1rem;
      margin: 1rem 0;
      border-radius: 4px;
    }
    .sitka-spam-error strong {
      display: block;
      margin-bottom: 0.5rem;
    }
    .sitka-spam-error p {
      margin: 0.5rem 0 0 0;
      font-size: 0.9em;
    }
  </style>
<?php endif; ?>

<?php if ($curatedResultsOption == "enabled" && $curatedResultsEnabled && isset($response['curatedResults']) && !empty($response['curatedResults'])) { ?>
  <section class="sitka-search-results-container curated-results">
  
    <h2 class="sitka-curated-results-section-headline"> Recommended results</h2>
    <?php if (!empty($response['curatedResults'])) : ?>
      <?php foreach ($response['curatedResults'] as $result) : ?>

        <?= apply_filters('sitka/render', 'curated-result.php', array_merge($data, [
          'curated_result' => $result,
        ])) ?>

      <?php endforeach; ?>
    <?php endif; ?>

  </section>
<?php } ?>

<section class="sitka-search-results-container">
  <div class="container">

    <?php if (!empty($response['results'])) : ?>
      <?php foreach ($response['results'] as $result) : ?>
      
        <?= apply_filters('sitka/render', 'search-result.php', array_merge($data, [
          'result' => $result,
        ])) ?>

      <?php endforeach; ?>
    <?php elseif (!empty($searchQuery)) : ?>

      <p><?= apply_filters(
        'sitka/search/no_results_message',
        sprintf('%s <b>%s</b>', __('No results for'), esc_attr($searchQuery)),
        $searchQuery
      ) ?></p>

    <?php endif; ?>

    <div class="post-navigation">
      <?= Sitka\paginate_links($response) ?>
    </div>
  </div>
</section>
