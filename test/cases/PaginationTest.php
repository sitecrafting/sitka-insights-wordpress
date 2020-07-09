<?php

/**
 * Pagination tests
 *
 * @copyright 2020 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace Sitka\Plugin;

use PHPUnit\Framework\TestCase;

/**
 * Test all the pagination things
 */
class PaginationTests extends TestCase {
  public function setUp() : void {
    parent::setUp();

    $this->paginator = new Paginator();
  }

  public function test_page_count() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 5,
      'display_adjacent'    => 2,
    ]);

    $this->assertEquals(9, $this->paginator->page_count());
  }

  public function test_current_page_number() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 5,
      'display_adjacent'    => 2,
    ]);

    $this->assertEquals(5, $this->paginator->current_page_number());
  }

  public function test_display_adjacent() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 5,
      // display_adjacent should default to 2
    ]);

    $this->assertEquals(2, $this->paginator->display_adjacent());

    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 5,
      'display_adjacent'    => 3,
    ]);

    $this->assertEquals(3, $this->paginator->display_adjacent());
  }

  public function test_show_previous_on_first() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 1,
    ]);

    $this->assertFalse($this->paginator->show_previous());
  }

  public function test_show_previous_on_subsequent() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 2,
    ]);

    $this->assertTrue($this->paginator->show_previous());
  }

  public function test_show_next_on_last() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 9,
    ]);

    $this->assertFalse($this->paginator->show_next());
  }

  public function test_show_next_on_previous() {
    $this->paginator->set_pagination([
      'page_count'          => 9,
      'current_page_number' => 8,
    ]);

    $this->assertTrue($this->paginator->show_next());
  }

  public function test_results_page_url() {
    $this->assertEquals(
      '?greeting=hello&name=Billie+Holiday&page_num=4',
      $this->paginator->results_page_url([
        'greeting' => 'hello',
        'name'     => 'Billie Holiday',
        'page_num' => 4,
      ])
    );
  }

  public function test_previous_page_url() {
    $this->paginator->set_pagination([
      'current_page_number' => 5,
      'param_name'          => 'page_num',
    ]);

    $this->assertEquals(
      '?greeting=hello&name=Billie+Holiday&page_num=4',
      $this->paginator->previous_page_url([
        'greeting' => 'hello',
        'name'     => 'Billie Holiday',
        'page_num' => 'This should get overridden!',
      ])
    );
  }

  public function test_next_page_url() {
    $this->paginator->set_pagination([
      'current_page_number' => 5,
      'param_name'          => 'page_num',
    ]);

    $this->assertEquals(
      '?greeting=hello&name=Billie+Holiday&page_num=6',
      $this->paginator->next_page_url([
        'greeting' => 'hello',
        'name'     => 'Billie Holiday',
        'page_num' => 'This should get overridden!',
      ])
    );
  }

  public function test_page_markers_blank() {
    $this->paginator->set_pagination([
      'page_count'          => 1,
      'current_page_number' => 99,
    ]);
    $this->assertEquals([], $this->paginator->page_markers());
  }

  public function test_page_markers() {
    $cases = [
      [
        'pagination'            => [
          'page_count'          => 2,
          'current_page_number' => 1,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'page_num'          => 1,
            'current'           => true,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=2',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 3,
          'current_page_number' => 1,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'page_num'          => 1,
            'current'           => true,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=2',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 4,
          'current_page_number' => 1,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'page_num'          => 1,
            'current'           => true,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'page_num'          => 4,
            'current'           => false,
            'url'               => '?x=123&page_num=4',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=2',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 5,
          'current_page_number' => 1,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'page_num'          => 1,
            'current'           => true,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 5,
            'current'           => false,
            'url'               => '?x=123&page_num=5',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=2',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 6,
          'current_page_number' => 1,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'page_num'          => 1,
            'current'           => true,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 6,
            'current'           => false,
            'url'               => '?x=123&page_num=6',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=2',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 7,
          'current_page_number' => 2,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => true,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'page_num'          => 4,
            'current'           => false,
            'url'               => '?x=123&page_num=4',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 7,
            'current'           => false,
            'url'               => '?x=123&page_num=7',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=3',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 8,
          'current_page_number' => 3,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => true,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'page_num'          => 4,
            'current'           => false,
            'url'               => '?x=123&page_num=4',
          ],
          [
            'page_num'          => 5,
            'current'           => false,
            'url'               => '?x=123&page_num=5',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 8,
            'current'           => false,
            'url'               => '?x=123&page_num=8',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=4',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 9,
          'current_page_number' => 4,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=3',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'page_num'          => 2,
            'current'           => false,
            'url'               => '?x=123&page_num=2',
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'page_num'          => 4,
            'current'           => true,
            'url'               => '?x=123&page_num=4',
          ],
          [
            'page_num'          => 5,
            'current'           => false,
            'url'               => '?x=123&page_num=5',
          ],
          [
            'page_num'          => 6,
            'current'           => false,
            'url'               => '?x=123&page_num=6',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 9,
            'current'           => false,
            'url'               => '?x=123&page_num=9',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=5',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 9,
          'current_page_number' => 5,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=4',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 3,
            'current'           => false,
            'url'               => '?x=123&page_num=3',
          ],
          [
            'page_num'          => 4,
            'current'           => false,
            'url'               => '?x=123&page_num=4',
          ],
          [
            'page_num'          => 5,
            'current'           => true,
            'url'               => '?x=123&page_num=5',
          ],
          [
            'page_num'          => 6,
            'current'           => false,
            'url'               => '?x=123&page_num=6',
          ],
          [
            'page_num'          => 7,
            'current'           => false,
            'url'               => '?x=123&page_num=7',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 9,
            'current'           => false,
            'url'               => '?x=123&page_num=9',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=6',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 9,
          'current_page_number' => 6,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=5',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 4,
            'current'           => false,
            'url'               => '?x=123&page_num=4',
          ],
          [
            'page_num'          => 5,
            'current'           => false,
            'url'               => '?x=123&page_num=5',
          ],
          [
            'page_num'          => 6,
            'current'           => true,
            'url'               => '?x=123&page_num=6',
          ],
          [
            'page_num'          => 7,
            'current'           => false,
            'url'               => '?x=123&page_num=7',
          ],
          [
            'page_num'          => 8,
            'current'           => false,
            'url'               => '?x=123&page_num=8',
          ],
          [
            'page_num'          => 9,
            'current'           => false,
            'url'               => '?x=123&page_num=9',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=7',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 9,
          'current_page_number' => 7,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=6',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 5,
            'current'           => false,
            'url'               => '?x=123&page_num=5',
          ],
          [
            'page_num'          => 6,
            'current'           => false,
            'url'               => '?x=123&page_num=6',
          ],
          [
            'page_num'          => 7,
            'current'           => true,
            'url'               => '?x=123&page_num=7',
          ],
          [
            'page_num'          => 8,
            'current'           => false,
            'url'               => '?x=123&page_num=8',
          ],
          [
            'page_num'          => 9,
            'current'           => false,
            'url'               => '?x=123&page_num=9',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=8',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 9,
          'current_page_number' => 8,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=7',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 6,
            'current'           => false,
            'url'               => '?x=123&page_num=6',
          ],
          [
            'page_num'          => 7,
            'current'           => false,
            'url'               => '?x=123&page_num=7',
          ],
          [
            'page_num'          => 8,
            'current'           => true,
            'url'               => '?x=123&page_num=8',
          ],
          [
            'page_num'          => 9,
            'current'           => false,
            'url'               => '?x=123&page_num=9',
          ],
          [
            'next'              => true,
            'text'              => 'Next',
            'url'               => '?x=123&page_num=9',
          ],
        ],
      ],
      [
        'pagination'            => [
          'page_count'          => 9,
          'current_page_number' => 9,
          'param_name'          => 'page_num',
          'display_adjacent'    => 2,
        ],
        'result'                => [
          [
            'previous'          => true,
            'text'              => 'Previous',
            'url'               => '?x=123&page_num=8',
          ],
          [
            'page_num'          => 1,
            'current'           => false,
            'url'               => '?x=123&page_num=1',
          ],
          [
            'text'              => '...',
            'filler'            => true,
          ],
          [
            'page_num'          => 7,
            'current'           => false,
            'url'               => '?x=123&page_num=7',
          ],
          [
            'page_num'          => 8,
            'current'           => false,
            'url'               => '?x=123&page_num=8',
          ],
          [
            'page_num'          => 9,
            'current'           => true,
            'url'               => '?x=123&page_num=9',
          ],
        ],
      ],
    ];

    /*
     *
     * <span aria-current="page" class="page-numbers current">1</span>
     * <a class="page-numbers" href="">2</a>
     * <a class="page-numbers" href="">3</a>
     * <span class="page-numbers dots">…</span>
     * <a class="page-numbers" href="">9</a>
     * <a class="next page-numbers" href="">Next</a>
     */
    foreach ($cases as $case) {
      $this->paginator->set_pagination($case['pagination']);

      $this->assertEquals(
        $case['result'],
        $this->paginator->page_markers(['x' => 123]),
        sprintf(
          'Failed at current=%d, count=%d',
          $case['pagination']['current_page_number'],
          $case['pagination']['page_count']
        )
      );
    }
  }
}
