<?php

/**
 * Paginator class. Implements default pagination logic for GearLab search
 * results. Adequate for most use-cases.
 *
 * @copyright 2020 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace GearLab\Plugin;

class Paginator {
  use Paginated;

  public static function from_search_response(array $response) : self {
    $paginator = new static();

    $count     = apply_filters('gearlab/search/result_count', 10);
    $pageCount = ceil((int) ($response['total'] ?? 0) / $count);
    $page      = apply_filters('gearlab/search/page_num', 1);
    $pageParam = apply_filters('gearlab/search/page_num_param', 'page_num');

    $paginator->set_pagination([
      'page_count'          => $pageCount,
      'current_page_number' => $page,
      'param_name'          => $pageParam,
    ]);

    return $paginator;
  }
}
