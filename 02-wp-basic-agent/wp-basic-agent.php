<?php
/*
Plugin Name: ✅ 02 UDEMY BASIC AGENT WITH JS
Description: A basic Agent plugin for WordPress that integrates with OpenAI's API.
Version: 1.0.0
Author: Craig West
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register settings for API key storage
function wp_basic_agent_register_settings() {
    register_setting('wp_basic_agent_settings', 'wp_basic_agent_api_key');
}
add_action('admin_init', 'wp_basic_agent_register_settings');

// Process form submissions (API key save and show/hide toggle)
function wp_basic_agent_process_forms() {
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Note: Show/hide toggle is now handled by JavaScript only
    // No PHP form processing needed for show/hide functionality
    
    // Handle API key save
    if (isset($_POST['save_basic_agent_api_key'])) {
        // Verify nonce manually to avoid "expired" error page
        if (wp_verify_nonce($_POST['_wpnonce'], 'wp_basic_agent_save_key_nonce')) {
            $api_key = sanitize_text_field($_POST['wp_basic_agent_api_key']);
            update_option('wp_basic_agent_api_key', $api_key);

            // Store success message in transient
            set_transient('wp_basic_agent_message', 'API Key saved successfully!', 30);
        }
        // Always redirect to prevent form resubmission, regardless of nonce validity
        wp_redirect(admin_url('admin.php?page=basic-agent-settings'));
        exit;
    }
}
add_action('admin_init', 'wp_basic_agent_process_forms');

// Include admin functionality
require_once plugin_dir_path(__FILE__) . 'admin/functions/admin-hooks.php'; // menu items and page rendering
require_once plugin_dir_path(__FILE__) . 'admin/functions/render-admin-page.php'; // settings page functions
require_once plugin_dir_path(__FILE__) . 'admin/functions/enqueue_assets.php'; // enqueue scripts and styles
require_once plugin_dir_path(__FILE__) . 'admin/functions/agent.php'; // OpenAI API proxy handler

// Include shortcode functionality
require_once plugin_dir_path(__FILE__) . 'shortcodes/wp-basic-agent-shortcode.php'; // Frontend shortcode [wp_basic_agent]

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'wp_basic_agent_enqueue_admin_assets');

// Activation hook
require_once plugin_dir_path(__FILE__) . 'admin/functions/basic_page_activate.php';
register_activation_hook(__FILE__, 'wp_basic_agent_activate');

// Deactivation hook
require_once plugin_dir_path(__FILE__) . 'admin/functions/basic_page_deactivate.php';
register_deactivation_hook(__FILE__, 'wp_basic_agent_deactivate');
