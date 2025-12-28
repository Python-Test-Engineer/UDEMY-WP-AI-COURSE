<?php
/**
 * Plugin Name: ✅ 11 UDEMY HTMX Demo Plugin
 * Plugin URI: https://example.com
 * Description: A simple plugin demonstrating HTMX integration with WordPress posts
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// Define plugin constants
define('WP_HTMX_VERSION', '1.0.0');
define('WP_HTMX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_HTMX_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WP_HTMX_PLUGIN_DIR . 'functions/admin-menu.php';
require_once WP_HTMX_PLUGIN_DIR . 'shortcodes/htmx-demo-shortcode.php';
require_once WP_HTMX_PLUGIN_DIR . 'functions/htmx-posts.php';
