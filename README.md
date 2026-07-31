# Sitka Insights for WordPress

![Travis CI build status](https://api.travis-ci.org/sitecrafting/sitka-insights-wordpress.svg?branch=main)

Integrate your WordPress site seamlessly with the Sitka Insights suite.

## Installation

### Installing Manually

Go to the GitHub [releases page](https://github.com/sitecrafting/sitka-insights-wordpress/releases/) and download the .zip archive of the latest release. Make sure you download the release archive, **not** the source code archive. For example, if the latest release is called `v2.x.x`, click the download link that says **sitka-insights-v2.x.x.zip**. (You can also use the `tar.gz` archive if you want - they are the same code.)

Once downloaded and unzipped, place the extracted directory in `wp-content/plugins`. Activate the plugin from the WP Admin as you normally would.

### Installing via Composer

Add the requirement to your composer.json:

```sh
composer require sitecrafting/sitka-insights-wordpress --prefer-dist
```

*NOTE:* if you are tracking your WordPress codebase as one big monorepo, the `--prefer-dist` flag is important! It tells Composer to find and download the .zip archive instead of the full Git repository. Without this flag, it will create the plugin directory as a [Git submodule](https://git-scm.com/book/en/v2/Git-Tools-Submodules) and strange things will happen.

## Usage

### Getting Started

After installing and activating the plugin, go to the *Sitka Insights* section of the WP Admin. Enter your *API Key*, *Collection ID*, and *Base URI* as provided by Sitka. All settings are required.

### Search

With Sitka Insights, you can override the default WordPress search functionality, which is extremely limited by default, with results from the ElasticSearch crawler that powers Sitka Search. To do this you must first enter your settings as described above, in **Getting Started**.

Once you've entered your Sitka Insights settings, but before enabling overriding WordPress search globally, you can test from the command line to see if you get results. To do this, run `wp sitka search <search term>`. You should see a JSON object like this:

```json
{"results": [{"url": "https://www.example.com/example-page", "title": "Example Page", "snippet": "Some content"}, ...]}
```

Once you've entered the settings above correctly, you're ready to enable Sitka Insights Search to override the default WP search. Enable the option **Override default WordPress search** and save the settings again. You should now see a basic search results page rendered by Sitka Insights whenever you perform a search.

#### Search Shortcode

Out of the box, you can use the `[sitka_search]` shortcode in any RTE that supports shortcodes. This is the recommended approach for most cases.

However, basic searches (using WordPress's standard `s` query param), will still render your theme's default search.php template (assuming there is one). You can redirect global searches to the page your shortcode lives on in the Settings. Go to **Settings > Sitka Insights** and select **Redirect searches to a specific page**. Type the URI, e.g. `/search`, in the text box that appears.

Save your changes and you're good to go! Default searches will now redirect to your page. Note that all query string parameters will be preserved *except* `s`, which will be renamed to `sitka_search` to avoid conflicting with WordPress's default functionality.

#### Using the Sitka WordPress API directly

If you want a bit more control, you can use the provided WordPress API directly. Place something like the following in your theme at `search.php`:

```php
// NOTE: the client may throw an exception!
use Swagger\Client\ApiException;

// Call out to the API
try {
  // search with some sensible defaults
  $response = Sitka\search();
} catch (ApiException $e) {
  error_log($e->getMessage());
  $response = [];
}

wp_header();

// Render results
<?php if (empty($response['results'])) : ?>
  <p>Sorry, no results for <?= $response['originalQueryPhrase'] ?? ' that search term.' ?></p>
<?php else : ?>
  <?php foreach (($response['results'] ?? []) as $result) : ?>
    <article class="search-result">
      <h1><a href="<?= $result['url'] ?>"><?= $result['title'] ?></a></h1>
      <p><?= $result['snippet'] ?></p>
    </article>
  <?php endforeach; ?>
<?php endif; ?>

<?= Sitka\paginate_links($response) ?>

<?php wp_footer(); ?>
```

For more custom behavior, you can pass params directly to `Sitka\search()`:

```php
use Swagger\Client\ApiException;

// Override your site's pagination settings.
$count = 25;

// Note that we can't use $paged here, because WordPress core won't
// necessarily report the same number of pages as Sitka, leading to 404s
// in cases where Sitka has more result pages than WP would.
$page_offset = ($_GET['page_num'] ?? 1) - 1;


// Call out to the API
try {
  $response = Sitka\search([
    // Pass the user's search term to the API.
    'query'     => get_query_var('my_search_param'),
    // Tell the API how many results we want per page.
    'resLength' => $count,
    // Tell the API which page of results we want.
    'resOffset' => $page_offset * $count,
    // Tell the API to only return results of a certain type
    'metaKey'   => $_GET['my_content_type'],
  ]);
} catch (ApiException $e) {
  error_log($e->getMessage());
  $response = [];
}

// Render results
foreach (($response['results'] ?? []) as $result) : ?>
  <article class="search-result">
    <h1><a href="<?= $result['url'] ?>"><?= $result['title'] ?></a></h1>
    <p><?= $result['snippet'] ?></p>
  </article>
<?php endforeach; ?>

<?= Sitka\paginate_links($response) ?>
```

### Theme overrides

When rendering frontend code, such as markup for search results, Sitka checks the root directory of your theme (where your `style.css` lives) for a special `sitka-insights` folder. If a given file, for example `search-result.php`, exists within this folder, Sitka renders that version instead. Otherwise, it renders its own [default implementation](https://github.com/sitecrafting/sitka-insights-wordpress/tree/main/views/frontend).

Sitka works out of the box without any theme overrides, but if you need to customize the markup rendered, this is how to do it.

There are currently three frontend files you can override from your theme:

* `search-result.php` renders a single search result
* `search-results.php` renders all search results, wrapped in a container element.
* `pagination.php` renders the pagination 

When you do this, Sitka will `require` your theme file, setting a variable called `$data` which is an array of all the data available to you inside your override template. This will vary between templates.

### Spam Mitigation

Sitka Insights includes built-in support for spam mitigation on search forms and other forms using either **Cloudflare Turnstile** or **Google reCAPTCHA Enterprise**.

**Important:** This plugin uses reCAPTCHA Enterprise, not the deprecated reCAPTCHA v3. If you're currently using reCAPTCHA v3, you should migrate to reCAPTCHA Enterprise.

#### Configuration

1. Go to **Settings > Sitka Insights** in the WP Admin
2. Scroll to the **Spam Mitigation** section
3. Select your mitigation type (Turnstile or ReCaptcha)
4. Enter your credentials:
   - **For Turnstile**: Site Key and Secret Key
   - **For reCAPTCHA Enterprise**: Site Key, Project ID, and API Key
5. Save settings

The instructions for obtaining keys for each service are displayed in the admin panel when you select a mitigation type.

#### reCAPTCHA Enterprise Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one from the dropdown at the top of the page
3. **Find your Project ID:** Click on the project dropdown at the top. You'll see your project name and below it the **Project ID** (e.g., "my-project-12345"). This is what you'll enter in the Sitka settings - NOT the project name.
4. Navigate to [reCAPTCHA Enterprise](https://console.cloud.google.com/security/recaptcha) in the left sidebar menu (under "Security")
5. Click "Enable API" if not already enabled
6. Click "Create Key" and choose "Score-based (v3)" for invisible verification  
7. Add your domain(s) to the list of authorized domains
8. Copy the **Site Key** displayed after creating the key
9. Go to [APIs & Services > Credentials](https://console.cloud.google.com/apis/credentials)
10. Click "Create Credentials" > "API Key"
11. (Recommended) Restrict the API key to only allow "reCAPTCHA Enterprise API" for better security
12. Copy the **API Key**

**What to enter in Sitka Insights settings:**
- **Site Key:** From the reCAPTCHA key you created (step 8)
- **Project ID:** Your Google Cloud Project ID from step 3 (looks like "my-project-12345", NOT the project name)
- **API Key:** From the credentials page (step 12)

**Common issue:** Make sure to use the Project ID (the unique identifier), not the project name. The Project ID is shown in the project dropdown and is usually lowercase with hyphens.

#### Using the Shortcode

To add spam mitigation to any form, use the `[sitka_spam_mitigation]` shortcode:

```html
<form method="post" action="/search">
  <input type="text" name="sitka_search" placeholder="Search...">
  
  <!-- Add spam mitigation widget -->
  [sitka_spam_mitigation]
  
  <button type="submit">Search</button>
</form>
```

**Shortcode attributes:**

* `class` - CSS class for the container (default: `sitka-spam-mitigation`)
* `size` - Widget size: `normal`, `compact`, or `invisible` (reCAPTCHA only, default: `normal`)
* `theme` - Visual theme: `light` or `dark` (default: `light`)

Example with attributes:

```
[sitka_spam_mitigation class="my-custom-class" theme="dark" size="compact"]
```

#### Verifying Tokens

When processing form submissions, verify the spam mitigation token on the server side:

```php
// For Turnstile, the token is in $_POST['cf-turnstile-response']
// For reCAPTCHA, the token is in $_POST['g-recaptcha-response']

$mitigation_type = get_option('sitka_mitigation_type');

if ($mitigation_type === 'turnstile') {
  $token = $_POST['cf-turnstile-response'] ?? '';
} elseif ($mitigation_type === 'recaptcha') {
  $token = $_POST['g-recaptcha-response'] ?? '';
}

// Verify the token
$result = Sitka\verify_spam_mitigation($token);

if ($result['success']) {
  // Token is valid, process the form
  // ...
} else {
  // Token is invalid
  $error = $result['error'] ?? 'Verification failed';
  // Handle error...
}
```

For reCAPTCHA v3, you can also check the score:

```php
$result = Sitka\verify_spam_mitigation($token);

if ($result['success']) {
  $score = $result['score'] ?? 0; // 0.0 to 1.0 (higher is more human-like)
  
  if ($score >= 0.5) {
    // Likely a human, process the form
  } else {
    // Likely a bot, reject the submission
  }
  
  // You can also check the risk reasons
  $reasons = $result['reasons'] ?? [];
  // Reasons might include: AUTOMATION, UNEXPECTED_ENVIRONMENT, etc.
}
```

**Note on Score Threshold:** The default threshold is 0.5 (50%). You can adjust this in the `verify_spam_mitigation()` function in `wp-api.php` based on your security requirements. Higher thresholds (e.g., 0.7) are more strict but may block some legitimate users.

### Pagination

Sitka implements its own logic for paginating results that, unlike the core `paginate_links()` function, is not coupled to WordPress's internal query logic. In fact, it is much simpler to use than the built-in WordPress function.

Here is the default return value (**not** echoed output) of the function when there are ten pages of results (and the user is on page 1):

```html
<div class="pagination">
  <span aria-current="page" class="page-numbers current">1</span>    
  <a class="page-numbers" href="?s=doctor&amp;page_num=2">2</a>
  <a class="page-numbers" href="?s=doctor&amp;page_num=3">3</a>
  <span class="page-numbers dots">…</span>
  <a class="page-numbers" href="?s=doctor&amp;page_num=10">10</a>
  <a class="page-numbers next" href="?s=doctor&amp;page_num=2" rel="next">Next</a>
</div>
```

#### Customizing pagination markup

You can override this markup using a standard Theme Override (see the previous section about this).

Here is the default implementation:

```php
<?php

$url_params    = $data['url_params']; // this is just $_GET by default
$paginator = $data['paginator']; // A Sitka\Plugin\Paginator instance
$markers   = $paginator->page_markers($url_params);

/* START MARKUP */
if ($paginator->page_count() > 1) : ?>
  <div class="pagination">
    <?php foreach ($markers as $marker) : ?>
      <?php if (!empty($marker['previous'])) : ?>
        <a class="page-numbers prev" href="<?= $paginator->previous_page_url($url_params) ?>" rel="prev">Previous</a>
      <?php endif; ?>

      <?php if (!empty($marker['current'])) : ?>
        <span aria-current="page" class="page-numbers current"><?= $marker['page_num'] ?></span>
      <?php elseif (!empty($marker['filler'])) : ?>
        <span class="page-numbers dots">…</span>
      <?php elseif (!empty($marker['page_num'])) : ?>
        <a class="page-numbers" href="<?= $marker['url'] ?>"><?= $marker['page_num'] ?></a>
      <?php endif ?>

      <?php if (!empty($marker['next'])) : ?>
        <a class="page-numbers next" href="<?= $paginator->next_page_url($url_params) ?>" rel="next">Next</a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
```

`$paginator` is an object that implements most of the complex logic for which prev/next/number links, or "markers," should be displayed. Note that almost all the information you need about each marker lives inside the marker array itself.

Crucially, remember to always pass the `$url_params` array to the Paginator methods `page_markers()`, `previous_page_url()` and `next_page_url()`. Otherwise your links will be wrong.

Here's an example markers array, returned when we're on **page 4** of the results, with **25 total** pages:

```
// NOTE: Normally you are simply passed the $url_params array; you typically
// won't build it yourself. This is just for demonstration purposes.
$url_params = [
  's'        => 'doctor',
  'page_num' => 4,
];

$paginator->page_markers($url_params);

// result:
[
  [
    'text'     => 'Previous',
    'url'      => '?s=doctor&page_num=3',
    'previous' => true,
  ],
  [
    'page_num' => 1,
    'current'  => false,
    'url'      => '?s=doctor&page_num=1',
  ],
  [
    'page_num' => 2,
    'current'  => false,
    'url'      => '?s=doctor&page_num=2',
  ],
  [
    'page_num' => 3,
    'current'  => false,
    'url'      => '?s=doctor&page_num=3',
  ],
  [
    'page_num' => 4,
    'current'  => true,
    'url'      => '?s=doctor&page_num=4',
  ],
  [
    'page_num' => 5,
    'current'  => false,
    'url'      => '?s=doctor&page_num=5',
  ],
  [
    'page_num' => 6,
    'current'  => false,
    'url'      => '?s=doctor&page_num=6',
  ],
  [
    'text'     => '...',
    'filler'   => true,
  ],
  [
    'page_num' => 25,
    'current'  => false,
    'url'      => '?s=doctor&page_num=25',
  ],
  [
    'text'     => 'Next',
    'url'      => '?s=doctor&page_num=5',
    'next'     => true,
  ],
]
```

#### Customizing pagination parameters

If the template markup is fine but you need to affect the pagination *logic* itself, you probably want the `sitka/pagination/construct` filter, so named because it controls the array passed to the `Sitka\Plugin\Paginator::__construct` method. This hook can be used to customize things like:

* the number of "nearby" or "adjacent" pages next to the current page number to display (default is 2) before a "filler" marker with a `…` is displayed
* the total page count and current page number, in case you need to override those for some reason

Here's an example of overriding the number adjacent pages rendered:

```php
add_filter('sitka/pagination/construct', function(array $ctor_args) {
  return array_merge($ctor_args, [
    'display_adjacent' => 3, // override the default of 2
  ]);
});
```

### Search Autocomplete

In addition to providing superior search results, Sitka Insights also adds search autocomplete to your search template out of the box. You don't need to do anything to make this work, although you may want to override the default [`jquery-ui-autocomplete`](https://api.jqueryui.com/autocomplete/) styles.

The only assumption this module makes about your HTML is that the search input can be found at the selector `form [name="s"]`, i.e. a form element whose `name` attribute is `"s"`. Because of how WordPress search is implemented, this assumption will hold true unless your search functionality is overriding WordPress core in an advanced way.

For the curious, this feature works by registering a custom WP REST route at `/wp-json/sitka/v2/completions` and telling `jquery-ui-autocomplete` to grab its autocomplete suggestions from that route.

### WP-CLI Custom Commands

The plugin implements WP-CLI commands for major Sitka Insights REST endpoints, such as search:

```bash
wp sitka search tacos
wp sitka s tacos # `s` is an alias for `search`
wp sitka completions
wp sitka c taco # `c` is an alias for `completions`
```

This will automatically use the credentials you've configured in the plugin settings.

Run `wp sitka --help` to list subcommands.

As with other WordPress options, you can configure the plugin options with `wp option`:

```bash
wp option get sitka_api_key
wp option get sitka_collection_id
wp option get sitka_base_uri
wp option get sitka_enabled
wp option set sitka_api_key supersecure
wp option set sitka_collection_id 12345
wp option set sitka_environment production
wp option set sitka_enabled 1
```

## Development

To build a new release use the script `./bin/create-release.sh`

Make sure all your changes are committed then run this script and follow the prompts: 

```sh
./bin/create-release.sh
```

This script will: 

* Request a new version number
* Update the version in the `sitka-insights.php` file
* Update the `composer.json` file:
  * Change the `version` value
  * Update the `dist.url` value to match the download URL from the release
* Create a tag with the version number
* Create `.zip` and `.tar.gz` archives. 
* Create a GitHub release and upload the built assets

### Prerequisites

The script requires the following tools to be installed:

- **git** - Version control
- **gh** (GitHub CLI) - For creating releases. Install from https://cli.github.com/
- **jq** - JSON processor for updating composer.json
- **zip** - For creating . zip archives
- **tar** - For creating .tar.gz archives
