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
$aiSearchOptionEnabled = get_option('sitka_search_ai_results_enabled') ?? 'disabled';
$aiSearchHeading = get_option('sitka_ai_results_heading') ?? 'AI-Powered Search Results';
$aiSearchGeneratedTextEnabled = get_option('sitka_search_ai_results_gen_text_enabled') ?? 'disabled';

?>
<script>

/**
 * Get the data needed from the API response into JS variables so we can send AI Feedback via JS functions.
 */
  <?php
  $ai_message = "";
  $context = "";
  $searchQuery = $originalQuery;
  $queryLogId = 1;//$response['queryLogId']; TODO fix this


  // AI generated message
  // TODO put back success true check 
  // if($aiSearchOptionEnabled == "enabled" && isset($response['aiResult']) && !empty($response['aiResult']) && $response['aiResult']['success'] == true)
  if($aiSearchOptionEnabled == "enabled" && isset($response['aiResult']) && !empty($response['aiResult']))
  {
      if ($aiSearchGeneratedTextEnabled === 'enabled') {
        // set the variable if it's enabled and shown to the user
        // -> this will help distinguish feedback between feedback on AI-suggested links and AI text with AI-suggested links.
        $ai_message = $response['aiResult']['ai_text']; 
      }
      else {
        $ai_message = "";
      }

      // context (links found by AI)
      foreach ($response['aiResult']['links'] as $resultLink)
      {
        $context .= ($resultLink["url"]." ");
      }

      if (isset($response['suggestionSupersededQuery']) && $response['suggestionSupersededQuery']  && $didYouMeanOption == "enabled")
      {
        $searchQuery = $response['suggestionSupersededQuery'];
      } 
  }
    $dashboard_uri = apply_filters('sitka/dashboard_uri',[]);
    $dashboard_url = $dashboard_uri."/aisearchfeedback/post";
    $siteId = get_option('sitka_site_id');
?>
  const sitka_ai_feedback_dashboard_url = "<?= $dashboard_url ?>";
  const sitka_aiText = "<?= $ai_message ?>";
  const sitka_ai_links_context = "<?= $context ?>";
  const sitka_searchQuery = "<?= $searchQuery ?>";
  const sitka_queryLogId = "<?= $queryLogId ?>";
  const sitka_siteId = "<?= $siteId ?>";
</script>

<section class="sitka-search-form-container">
  <div class="container">
    <div class="global-search">
      <h5>Search</h5>
      <form role="search" method="get" id="searchform" class="searchform sitka-search-form" action="<?= get_permalink($post) ?>">
        <input
          type="text"
          value="<?= esc_attr_e($searchQuery) ?>"
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
        <button id="searchsubmit" type="submit" class="btn"><span>Search</span></button>
      </form>
    </div><!-- global-search -->
  </div><!-- container -->
</section>

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

<!-- && $response['aiResult']['success'] == true -->
<?php if ($aiSearchOptionEnabled == "enabled" && isset($response['aiResult']) && !empty($response['aiResult'])) { ?>
  <section class="sitka-search-results-container sitka-ai-results">
    <span class="sitka-beta-badge">BETA</span>
    <h2 class="sitka-ai-results-section-headline">
    <?= $aiSearchHeading ?>
    </h2>
    
    <?php if (!empty($response['aiResult'])) { ?>
      <?php if ($aiSearchGeneratedTextEnabled === 'enabled') {?>
        <p class="sitka-ai-search-generated-text"> <?= $response['aiResult']['ai_text']?></p>
      <?php }?>
      <fieldset class="sitka-ai-search-sources">
        <legend>Sources</legend>
          <ul class="sitka-ai-search-links">
          <?php foreach ($response['aiResult']['links'] as $resultLink) { ?>
            <li>
              <a href="<?= $resultLink["url"] ?>"><?= $resultLink["title"] ?></a>
            </li>
          <?php } ?>
          </ul>
      </fieldset>
      
       <?= apply_filters('sitka/render', 'ai-feedback-buttons.php', array_merge($data, [
          'labels' => $response['aiResult']['feedback_labels'],
          'queryLogId' => $queryLogId
        ])) ?>

    <?php } ?>
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