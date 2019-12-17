# GearLab Tools for WordPress

Integrate your WordPress site seamlessly with the GearLab Tools suite.

## Installation

### Installing Manually

Download the .zip archive of the latest release and place the extracted directory in `wp-content/plugins`. Activate the plugin from the WP Admin as you normally would.

### Installing via Composer

Add the requirement to your composer.json:

```sh
composer require sitecrafting/gearlab-tools-wordpress --prefer-dist
```

*NOTE:* if you are tracking your WordPress codebase as one big monorepo, the `--prefer-dist` flag is important! It tells Composer to find and download the .zip archive instead of the full Git repository. Without this flag, it will create the plugin directory as a [Git submodule](https://git-scm.com/book/en/v2/Git-Tools-Submodules) and strange things will happen.

## Usage

### Getting Started

After installing and activating the plugin, go to the *GearLab Tools* section of the WP Admin. Enter your *API Key*, *Collection ID*, and *Base URI* as provided by GearLab. All settings are required.

Once you've entered these settings, you're ready to start consuming the GearLab Tools API directly!

### Search

With GearLab Tools, you can override the default WordPress search functionality, which is extremely limited by default, with results from the ElasticSearch crawler that powers GearLab Search.

Shortcodes for a drop-in search interface are planned. For now, you must override the search results in your `search.php` template directly:

```php
// NOTE: the client may throw an exception!
use Swagger\Client\ApiException;

// Honor your site's pagination settings.
$count = get_option('posts_per_page');

// Compute the results offset based on current pagination.
// Note that we can't use $paged here, because WordPress core won't
// necessarily report the same number of pages as GearLab, leading to 404s
// in cases where GearLab has more result pages that WP would.
$pageOffset = ($_GET['page_num'] ?? 1) - 1;


// Call out to the API
try {
  $response = GearLab\search([
    // Pass the user's search term to the API.
    'query'     => $_GET['s'] ?? '',
    // Tell the API how many results we want per page.
    'resLength' => $count,
    // Tell the API which page of results we want.
    'resOffset' => $pageOffset * $count,
  ]);
} catch (ApiException $e) {
  error_log($e->getMessage());
  $response = [];
}


// Render results
foreach (($response['results'] ?? []) as $result) : ?>
  <article class="search-result">
    <h1><a href="<?= $result['url'] ?>><?= $result['title'] ?></a></h1>
    <p><?= $result['snippet'] ?></p>
  </article>
<?php endforeach; ?>
```

### Search Autocomplete

In addition to providing superior search results, GearLab Tools also adds search autocomplete to your search template out of the box. You don't need to do anything to make this work, although you may want to override the default [`jquery-ui-autocomplete`](https://api.jqueryui.com/autocomplete/) styles.

The only assumption this module makes about your HTML is that the search input can be found at the selector `form [name="s"]`, i.e. a form element whose `name` attribute is `"s"`. Because of how WordPress search is implemented, this assumption will hold true unless your search functionality is overriding WordPress core in an advanced way.

For the curious, this feature works by registering a custom WP REST route at `/wp-json/gearlab/v2/completions` and telling `jquery-ui-autocomplete` to grab its autocomplete suggestions from that route.

### WP-CLI Custom Commands

The plugin implements WP-CLI commands for major GearLab Tools REST endpoints, such as search:

```bash
wp gearlab search tacos
```

This will automatically use the credentials you've configured in the plugin settings.

Run `wp gearlab --help` to list subcommands.
