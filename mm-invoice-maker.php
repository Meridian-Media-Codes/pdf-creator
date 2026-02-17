<?php
/**
 * Plugin Name: MM Invoice Maker
 * Description: Simple invoice creator with line items, VAT, PDF download, and storage.
 * Version: 1.0.0
 * Author: Meridian Media
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) exit;

define('MMIM_VERSION', '1.0.0');
define('MMIM_PLUGIN_FILE', __FILE__);
define('MMIM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MMIM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once MMIM_PLUGIN_DIR . 'includes/class-mmim-cpt.php';
require_once MMIM_PLUGIN_DIR . 'includes/class-mmim-settings.php';
require_once MMIM_PLUGIN_DIR . 'includes/class-mmim-metaboxes.php';
require_once MMIM_PLUGIN_DIR . 'includes/class-mmim-admin-assets.php';
require_once MMIM_PLUGIN_DIR . 'includes/class-mmim-pdf.php';

register_activation_hook(__FILE__, function () {
  MMIM_CPT::register();
  flush_rewrite_rules();
});

add_action('init', function () {
  MMIM_CPT::register();
});

add_action('plugins_loaded', function () {
  MMIM_Settings::init();
  MMIM_Metaboxes::init();
  MMIM_Admin_Assets::init();
  MMIM_PDF::init();
});
