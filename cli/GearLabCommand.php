<?php

/**
 * GearLabCommand class
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace GearLab\WpCli;

use WP_CLI;
use WP_CLI\Utils;

use GearLab;

/**
 * Perform a query against the Sitka Insights API using the settings from the
 * Sitka Insights settings admin page (/wp-admin/admin.php?page=gearlab-tools)
 */
class GearLabCommand {
  /**
   * Perform a search
   *
   * ## OPTIONS
   *
   * <query>
   * : The search term
   *
   * [--count=<count>]
   * : The total number of results to return for this query. Defaults to the
   * value of the posts_per_page WP setting.
   *
   * [--offset=<offset>]
   * : The number of results to skip over, i.e. for pagination. Defaults to zero.
   * ---
   * default: 0
   * ---
   *
   * [--literal]
   * : Whether to interpret matches literally, i.e. strict string matching vs.
   * smarter term matching. Defaults to false.
   *
   * [--meta-tag=<meta_tag>]
   * : The metaTag to pass filter by (e.g. for searching only WP pages, custom
   * post types, PDFs, etc.)
   *
   * [--format=<format>]
   * : The format to print the response data in. NOTE: in table format, only
   * the search results will be printed, without metadata about the query
   * itself (e.g. resOffset).
   * ---
   * default: json
   * options:
   *   - table
   *   - json
   * ---
   *
   * ## EXAMPLES
   *
   *     wp gearlab search tacoma
   *     wp gearlab s tacoma
   *     wp gearlab search --meta-tag=document tacoma
   *     wp gearlab search --meta-tag=page tacoma
   *     wp gearlab search --meta-tag=page tacoma
   *     wp gearlab search --meta-tag=page --format=table tacoma
   *
   * @subcommand search
   * @alias s
   * @when after_wp_load
   */
  public function search(array $args, array $opts = []) {
    $response = GearLab\search([
      'query'        => $args[0],
      'resLength'    => $opts['count'] ?? '',
      'resOffset'    => $opts['offset'] ?? '',
      'literalQuery' => $opts['literal'] ?? false,
      'metaTag'      => $opts['meta-tag'] ?? '',
    ]);

    $format = $opts['format'] ?? 'json';

    if ($format === 'table') {
      Utils\format_items(
        'table',
        $response['results'],
        ['title', 'url', 'snippet', 'meta']
      );
    } else {
      echo json_encode($response);
    }
  }

  /**
   * Perform a completions query
   *
   * ## OPTIONS
   *
   * <prefix>
   * : The partial search term to offer completion suggestions for
   *
   * [--meta-tag=<meta_tag>]
   * : The metaTag to pass filter by (e.g. for searching only WP pages, custom
   * post types, PDFs, etc.)
   *
   * [--format=<format>]
   * : The format to print the response data in. NOTE: in table format, only
   * the search results will be printed, without metadata about the query
   * itself (e.g. resOffset).
   * ---
   * default: json
   * options:
   *   - table
   *   - json
   * ---
   *
   * ## EXAMPLES
   *
   *     wp gearlab completions tac
   *     wp gearlab c tac
   *
   * @subcommand completions
   * @alias c
   * @when after_wp_load
   */
  public function completions(array $args, array $opts = []) {
    $response = GearLab\completions([
      'prefix'  => $args[0],
      'metaTag' => $opts['meta-tag'] ?? '',
    ]);

    $format = $opts['format'] ?? 'json';

    if ($format === 'table') {
      Utils\format_items(
        'table',
        $response['results'],
        ['title']
      );
    } else {
      echo json_encode($response);
    }
  }
}
