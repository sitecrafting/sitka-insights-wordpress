<?php

/**
 * Paginator class. Implements default pagination logic for Sitka search
 * results. Adequate for most use-cases.
 *
 * @copyright 2020 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace Sitka\Plugin;

class Paginator {
  use Paginated;

  public static function params_from_response(array $response) : array {
    $count     = apply_filters('sitka/search/result_count', 10);
    $pageCount = ceil((int) ($response['total'] ?? 0) / $count);
    $page      = apply_filters('sitka/search/page_num', 1);
    $pageParam = apply_filters('sitka/search/page_num_param', 'page_num');

    return [
      'page_count'          => $pageCount,
      'current_page_number' => $page,
      'param_name'          => $pageParam,
    ];
  }
}
