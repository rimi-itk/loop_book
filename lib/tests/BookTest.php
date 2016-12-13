<?php
namespace Drupal\loop_book;

include_once __DIR__ . '/../src/Book.php';
use Drupal\loop_book\Book;

use PHPUnit\Framework\TestCase;

class BookTest extends TestCase {
  /**
   * @dataProvider bookDataProvider
   */
  public function testTrees(array $relations, array $trees, array $roots)
  {
    $book = new TestableBook();
    $book->setRelations($relations);

    $actual = $book->getTrees();
    $this->assertEquals(count($trees), count($actual), 'Incorrect number of trees');
    $this->assertEquals($trees, $actual);
  }

  /**
   * @dataProvider bookDataProvider
   */
  public function testRoots(array $relations, array $trees, array $roots)
  {
    $book = new TestableBook();
    $book->setRelations($relations);

    $actual = $book->getRoots();
    $this->assertEquals($roots, $actual);
  }

  public function bookDataProvider() {
    return [
      // Relations, trees, roots

      [
        [], [], []
      ],

      [
        // Relations
        [
          [1 => 2],
        ],
        // trees
        [
          1 => [
            'id' => 1,
            'children' => [
              2 => ['id' => 2],
            ],
            'nodes' => [1, 2],
          ],
        ],
        // roots
        [
          // 1 => [1],
          2 => [1],
        ]
      ],

      [
        [
          [1 => 2],
          [2 => 3],
        ], [
          1 => [
            'id' => 1,
            'children' => [
              2 => [
                'id' => 2,
                'children' => [
                  3 => ['id' => 3],
                ],
              ],
            ],
            'nodes' => [1, 2, 3],
          ],
        ], [
          // 1 => [1],
          2 => [1],
          3 => [1],
        ]
      ],

      [
        [
          // id => $child_id
          ['book 1' => 'page 1'],
          ['page 1' => 'page 1, 1'],
          ['book 1' => 'page 2'],
          ['book 2' => 'page 2'],
          ['book 2' => 'page 3'],
        ], [
          'book 1' => [
            'id' => 'book 1',
            'children' => [
              'page 1' => [
                'id' => 'page 1',
                'children' => [
                  'page 1, 1' => ['id' => 'page 1, 1'],
                ],
              ],
              'page 2' => ['id' => 'page 2'],
            ],
            'nodes' => ['book 1', 'page 1', 'page 1, 1', 'page 2'],
          ],
          'book 2' => [
            'id' => 'book 2',
            'children' => [
              'page 2' => ['id' => 'page 2'],
              'page 3' => ['id' => 'page 3'],
            ],
            'nodes' => ['book 2', 'page 2', 'page 3'],
          ],
        ], [
          'page 1, 1' => ['book 1'],
          'page 1' => ['book 1'],
          'page 2' => ['book 1','book 2'],
          'page 3' => ['book 2'],
        ]
      ],

      [ // Simple cycle
        [
          [1 => 2],
          [2 => 1],
          [2 => 3],
        ],
        [],
        [],
      ],

      [
        [
          [1 => 2],
          [2 => 5],
          [2 => 6],
          [1 => 3],
          [3 => 6],
          [3 => 7],
          [1 => 4],
          [4 => 8],
        ],
        [
          1 => [
            'id' => 1,
            'children' => [
              2 => [
                'id' => 2,
                'children' => [
                  5 => ['id' => 5],
                  6 => ['id' => 6],
                ],
              ],
              3 => [
                'id' => 3,
                'children' => [
                  6 => ['id' => 6],
                  7 => ['id' => 7],
                ],
              ],
              4 => [
                'id' => 4,
                'children' => [
                  8 => ['id' => 8],
                ],
              ],
            ],
            'nodes' => [1,2,5,6,3,7,4,8],
          ],
        ],
        [
          5 => [1],
          6 => [1],
          7 => [1],
          8 => [1],
          2 => [1],
          3 => [1],
          4 => [1],
        ]
      ],

    ];
  }
}

class TestableBook extends Book {
  protected $relations;

  public function setRelations(array $relations) {
    $this->relations = [];

    foreach ($relations as $relation) {
      foreach ($relation as $id => $child_id) {
        $this->relations[] = (object)[
          'id' => $id,
          'child_id' => $child_id,
        ];
      }
    }
  }

  protected function getRelations() {
    return $this->relations;
  }

  protected function getData($rebuild = false) {
    return $this->build();
  }
}
