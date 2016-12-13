<?php
/**
 * @file
 * loop_book.features.inc
 */

/**
 * Implements hook_node_info().
 */
function loop_book_node_info() {
  $items = array(
    'loop_book' => array(
      'name' => t('Loop book'),
      'base' => 'node_content',
      'description' => t('Loop book'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
