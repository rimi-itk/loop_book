<?php
/**
 * @file
 * loop_book.features.filter.inc
 */

/**
 * Implements hook_filter_default_formats().
 */
function loop_book_filter_default_formats() {
  $formats = array();

  // Exported format: loop_book.
  $formats['loop_book'] = array(
    'format' => 'loop_book',
    'name' => 'loop_book',
    'cache' => 1,
    'status' => 1,
    'weight' => 0,
    'filters' => array(),
  );

  return $formats;
}
