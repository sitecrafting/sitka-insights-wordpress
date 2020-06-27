<?php

/**
 * Paginated trait. Include this in a theme class to implement or override
 * your own pagination, e.g. in a Form class.
 *
 * @copyright 2020 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace Sitka\Plugin;

trait Paginated {
  public function set_pagination(array $pagination) {
    $this->pagination = $pagination;
  }

  public function page_count() : int {
    return $this->pagination['page_count'] ?? 0;
  }

  public function current_page_number() : int {
    return $this->pagination['current_page_number'] ?? 1;
  }

  public function display_adjacent() : int {
    return $this->pagination['display_adjacent'] ?? 2;
  }

  public function show_previous() : bool {
    return $this->page_count() > 1 && $this->current_page_number() > 1;
  }

  public function show_next() : bool {
    $count = $this->page_count();
    return $count > 1 && $this->current_page_number() < $count;
  }

  public function results_page_url(array $params) : string {
    return '?' . http_build_query($params);
  }

  public function previous_page_url(array $params = []) : string {
    $pageKey  = $this->pagination_param_name();
    $previous = $this->current_page_number() - 1;

    return $this->results_page_url(array_merge($params, [
      $pageKey => $previous,
    ]));
  }

  public function next_page_url(array $params = []) : string {
    $pageKey  = $this->pagination_param_name();
    $next     = $this->current_page_number() + 1;

    return $this->results_page_url(array_merge($params, [
      $pageKey => $next,
    ]));
  }

  public function pagination_param_name() : string {
    return '' . ($this->pagination['param_name'] ?? 'page_num');
  }

  public function page_markers(array $params = []) : array {
    if ($this->page_count() <= 1) {
      return [];
    }

    $markers = [];

    if ($this->show_previous()) {
      $markers[] = [
        'text' => 'Previous',
        'url'  => $this->previous_page_url($params),
        'previous' => true,
      ];
    }

    // TODO current
    foreach (range(1, $this->page_count()) as $page) {
      // Is this number within the acceptable range for display?
      $diff   = abs($page - $this->current_page_number());
      $nearby = $diff <= $this->display_adjacent();

      // this "page" should show up as a filler marker IFF:
      //  * its number is greater than one, AND
      //  * it is less than $page_count
      //  * it is EXACTLY $display_adjacent less than or greater than
      //    the current page number
      $justBeyond = $page > 1
        && $page < $this->page_count()
        && $diff === $this->display_adjacent() + 1;

      // Only render this page number (as opposed to a filler "...") IFF
      // it's the first, last, or $display_adjacent within the current page
      $renderThisPageNumber = $page === 1
        || $page === $this->page_count()
        || $nearby;

      $url = $this->results_page_url(array_merge(
        $params,
        [$this->pagination_param_name() => $page]
      ));

      if ($renderThisPageNumber) {
        $markers[] = [
          'page_num' => $page,
          'current'  => $page === $this->current_page_number(),
          'url'      => $url,
        ];
      } elseif ($justBeyond) {
        $markers[] = [
          'text' => '...',
          'filler' => true,
        ];
      }
    }

    if ($this->show_next()) {
      $markers[] = [
        'text' => 'Next',
        'url'  => $this->next_page_url($params),
        'next' => true,
      ];
    }

    return $markers;
  }
}
