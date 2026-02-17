<?php
if (!defined('ABSPATH')) exit;

class MMIM_Admin_Assets {
  public static function init() {
    add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue']);
  }

  public static function enqueue($hook) {
    global $post;
    if (!$post || $post->post_type !== 'mm_invoice') return;

    wp_enqueue_style('mmim-admin', MMIM_PLUGIN_URL . 'assets/admin.css', [], MMIM_VERSION);
    wp_enqueue_script('mmim-admin', MMIM_PLUGIN_URL . 'assets/admin.js', ['jquery'], MMIM_VERSION, true);
  }
}
