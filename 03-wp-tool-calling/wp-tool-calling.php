<?php
/**
 * Plugin Name: ✅ 03 UDEMY TOOL CALLING
 * Description: OpenAI Tool Calling Demo - Available in admin and via shortcode [tool_calling]
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_TOOL_CALLING_PATH', plugin_dir_path(__FILE__));
define('WP_TOOL_CALLING_URL', plugin_dir_url(__FILE__));

// Include admin functionality
require_once WP_TOOL_CALLING_PATH . 'admin/admin-page.php';

// Include shortcode functionality
require_once WP_TOOL_CALLING_PATH . 'shortcodes/tool-calling-shortcode.php';
