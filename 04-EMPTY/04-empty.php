<?php
/**
 * Plugin Name: ✅ 04 UDEMY EMPTY 
 * Plugin URI: https://example.com/04-empty-plugin
 * Description: An empty placeholder plugin for WordPress admin menu.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 04-empty
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class for 04 EMPTY functionality
 */
class Empty_Plugin {

    /**
     * Constructor - Initialize the plugin
     */
    public function __construct() {
        // Hook into WordPress admin menu to add our page
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Add admin menu item at position 3.4
     */
    public function add_admin_menu() {
        add_menu_page(
            "04 BLANK", // Page title
            "04 BLANK", // Menu title
            'manage_options', // Capability required
            '04-blank', // Menu slug
            array($this, 'admin_page_callback'), // Callback function
            'dashicons-minus', // Icon (minus icon for empty/placeholder)
            3.33 // Position (level 3.4)
        );
    }

    /**
     * Callback function to render the admin page
     * Outputs a simple placeholder message
     */
    public function admin_page_callback() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Output the page content
        echo '<div class="wrap">';
        echo '<h1>04 BLANK - Placeholder Plugin</h1>';
        echo '<p>This is an empty placeholder plugin.</p>';
        echo '</div>';
    }
}

// Initialize the plugin
new Empty_Plugin();