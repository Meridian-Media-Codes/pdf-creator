<?php
if (!defined('ABSPATH')) exit;

class MMIM_CPT {
  public static function register() {
    register_post_type('mm_invoice', [
      'labels' => [
        'name' => 'Invoices',
        'singular_name' => 'Invoice',
        'add_new_item' => 'Add new invoice',
        'edit_item' => 'Edit invoice',
      ],
      'public' => false,
      'show_ui' => true,
      'menu_icon' => 'dashicons-media-spreadsheet',
      'supports' => ['title'],
      'has_archive' => false,
      'capability_type' => 'post',
      'map_meta_cap' => true,
    ]);
  }
}
