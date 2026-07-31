<?php
/**
 * Example search form with spam mitigation
 * 
 * This is a reference implementation showing how to integrate
 * Cloudflare Turnstile or Google reCAPTCHA Enterprise with your search form.
 * 
 * To use this template, copy it to your theme's sitka-insights folder
 * and customize as needed.
 */

$mitigation_type = get_option('sitka_mitigation_type');
$post = $data['post'] ?? $GLOBALS['post'] ?? null;
?>

<div class="sitka-search-form-container">
  <h2>Search</h2>
  
  <form role="search" method="get" id="searchform" class="searchform sitka-search-form" action="<?= get_permalink($post) ?>">
    <label for="sitka-search-input" class="screen-reader-text">Search for:</label>
    
    <input
      type="text"
      value="<?= esc_attr($_GET['sitka_search'] ?? '') ?>"
      name="sitka_search"
      id="sitka-search-input"
      placeholder="Enter search term..."
      required
    />
    
    <?php if ($mitigation_type): ?>
      <!-- Spam mitigation widget -->
      <div class="spam-mitigation-wrapper">
        <?php echo do_shortcode('[sitka_spam_mitigation theme="light" size="normal"]'); ?>
      </div>
    <?php endif; ?>
    
    <button id="searchsubmit" type="submit" class="btn btn-primary">
      <span>Search</span>
    </button>
  </form>
</div>

<style>
  .sitka-search-form-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
  }
  
  .sitka-search-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .sitka-search-form input[type="text"] {
    padding: 0.75rem;
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  .spam-mitigation-wrapper {
    display: flex;
    justify-content: center;
    margin: 1rem 0;
  }
  
  .sitka-search-form button {
    padding: 0.75rem 2rem;
    font-size: 1rem;
    background-color: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .sitka-search-form button:hover {
    background-color: #005a87;
  }
  
  .screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    white-space: nowrap;
    border-width: 0;
  }
</style>
