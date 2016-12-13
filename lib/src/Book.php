<?php

/**
 * @file
 * Contains \Drupal\loop_book\Book.
 */

namespace Drupal\loop_book;

class Book {
  const COLOR_WHITE = 0;
  const COLOR_GRAY  = 1;
  const COLOR_BLACK = 2;

  // Parent-child relations.
  protected $children;

  // Child-parent relations.
  protected $parents;

  // node id ⟼ tree.
  protected $trees;

  // node id ⟼ root id.
  protected $roots;

  public function getTrees(array $roots = null) {
    $data = $this->getData();

    if (!isset($data['trees'])) {
      return null;
    }

    $trees = [];
    foreach ($data['trees'] as $root => $tree) {
      if ($roots === null || in_array($root, $roots)) {
        $trees[$root] = $tree;
      }
    }

    return $trees;
  }

  public function getTree($root) {
    $trees = $this->getTrees([$root]);

    return isset($trees[$root]) ? $trees[$root] : null;
  }

  public function getRoots($node = null) {
    $data = $this->getData();

    if (!isset($data['roots'])) {
      return null;
    }

    if ($node === null) {
      return $data['roots'];
    }

    return isset($data['roots'][$node]) ? $data['roots'][$node] : null;
  }

  public function rebuild() {
    $this->getData(true);
  }

  protected function getData($rebuild = false) {
    $data = &drupal_static(__METHOD__);
    if ($rebuild || !isset($data)) {
      if (!$rebuild && $cache = cache_get(__METHOD__)) {
        $data = $cache->data;
      }
      else {
        $data = $this->build();
        cache_set(__METHOD__, $data, 'cache');
      }
    }

    return $data;
  }

  protected function build() {
    $this->children = [];
    $this->parents = [];

    $relations = $this->getRelations();
    foreach ($relations as $relation) {
      if (!isset($this->children[$relation->id])) {
        $this->children[$relation->id] = [];
      }
      $this->children[$relation->id][] = $relation->child_id;
      if (!isset($this->parents[$relation->child_id])) {
        $this->parents[$relation->child_id] = [];
      }
      $this->parents[$relation->child_id][] = $relation->id;
    }

    $this->trees = [];
    $this->roots = [];

    // Build a tree for each node that has no parents.
    $nodes = array_diff(array_keys($this->children), array_keys($this->parents));
    foreach ($nodes as $node) {
      $tree = $this->buildTree($node);
      if ($tree) {
        $this->trees[$node] = $tree;
      }
    }

    return [
      'trees' => $this->trees,
      'roots' => $this->roots,
    ];
  }

  protected function buildTree($node, $root = null, array &$visited = []) {
    if (empty($root)) {
      $root = $node;
    }

    if (isset($visited[$node]) && $visited[$node] === static::COLOR_GRAY) {
      // Cycle detected.
      return null;
    }

    if ($node !== $root) {
      if (!isset($this->roots[$node])) {
        $this->roots[$node] = [];
      }
      if (!in_array($root, $this->roots[$node])) {
        $this->roots[$node][] = $root;
      }
    }

    $visited[$node] = static::COLOR_GRAY;
    $children = [];
    if (isset($this->children[$node])) {
      foreach ($this->children[$node] as $child) {
        if ($child) {
          $children[$child] = $this->buildTree($child, $root, $visited);
        }
      }
    }
    $visited[$node] = static::COLOR_BLACK;

    $tree = [
      'id' => $node,
    ];
    if (!empty($children)) {
      $tree['children'] = $children;
    }
    if ($node === $root) {
      $tree['nodes'] = array_keys($visited);
    }

    return $tree;
  }

  protected function climbTrees() {
    foreach (array_keys($this->children) as $root) {
      $this->climbTree($root);
    }
  }

  protected function climbTree($root) {
    if (empty($root)) {
      $root = $node;
    }

    $children = [];
    if (!empty($node->field_loop_book_children)) {
      foreach ($node->field_loop_book_children[$lang] as $info) {
        $child = node_load($info['target_id']);
        if ($child) {
          $children[] = loop_book_theme_climb_tree($child, $lang, $root);
        }
      }
    }

    $url = $root->nid != $node->nid
         ? url('node/' . $node->nid, ['query' => array('context' => $root->nid)])
         : url('node/' . $node->nid);

    return [
      'nid' => $node->nid,
      'title' => $node->title,
      'url' => $url,
      'canonical_url' => url('node/' . $node->nid),
      'children' => $children,
    ];
  }

  protected function getRelations() {
    $result = db_query('select entity_id id, field_loop_book_children_target_id child_id from {field_data_field_loop_book_children} order by delta');

    return $result;
  }
}
