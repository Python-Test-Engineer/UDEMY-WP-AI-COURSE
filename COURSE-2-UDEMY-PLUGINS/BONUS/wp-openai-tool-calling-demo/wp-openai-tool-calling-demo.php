<?php
/**
 * Plugin Name: OpenAI Tool Calling Demo
 * Plugin URI: https://example.com/wp-openai-tool-demo
 * Description: A simple WordPress plugin that demonstrates OpenAI tool calling functionality. It adds an admin menu item to display an interactive demo where users can enter prompts and see tool calls in action.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-openai-tool-demo
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu item for the OpenAI Tool Demo
 *
 * This function registers the admin menu page at position 4.95
 * with the required capability 'manage_options'.
 */
function wp_openai_demo_add_admin_menu() {
    add_menu_page(
        'OpenAI Tool Demo',          // Page title
        'OpenAI Tool Demo',          // Menu title
        'manage_options',            // Capability required
        'wp-openai-demo',            // Menu slug
        'wp_openai_demo_admin_page', // Callback function
        'dashicons-admin-tools',     // Icon
        4.95                        // Position
    );
}
add_action('admin_menu', 'wp_openai_demo_add_admin_menu');

/**
 * Enqueue CSS and JavaScript files for the admin page
 *
 * This function conditionally enqueues the styles and scripts
 * only when the user is on the specific admin page.
 *
 * @param string $hook The current admin page hook
 */
function wp_openai_demo_admin_enqueue_scripts($hook) {
    // Only enqueue on our specific admin page
    if ($hook !== 'toplevel_page_wp-openai-demo') {
        return;
    }

    // Enqueue the CSS file
    wp_enqueue_style(
        'wp-openai-demo-css',
        plugin_dir_url(__FILE__) . 'css/frontend.css',
        array(),
        '1.0.0'
    );

    // Enqueue the JavaScript file
    wp_enqueue_script(
        'wp-openai-demo-js',
        plugin_dir_url(__FILE__) . 'js/frontend.js',
        array('jquery'), // Dependency on jQuery
        '1.0.0',
        true // Load in footer
    );
}
add_action('admin_enqueue_scripts', 'wp_openai_demo_admin_enqueue_scripts');

/**
 * Admin page callback function
 *
 * This function outputs the HTML content for the admin page,
 * displaying the OpenAI tool calling demo interface.
 */
function wp_openai_demo_admin_page() {
    ?>
    <div class="wrap">
        <!-- Load Google Fonts as required by the original demo -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <div class="container">
            <h1>🛠️ OpenAI Tool Calling Demo</h1>
            <h2>Demonstrates tool calling with get_weather() and get_sum() functions</h2>

            <div class="input-group">
                <label for="apiKey">OpenAI API Key:</label>
                <input type="text" id="apiKey" placeholder="Enter your OpenAI API key (sk-...)">
            </div>

            <div class="input-group">
                <label for="prompt">Your Prompt:</label>
                <input type="text" id="prompt" placeholder="e.g., What's the weather? or What is 5 + 3?"
                    value="What's the weather in England?" autocomplete="off">
            </div>

            <button id="sendBtn">Send Request</button>

            <div id="result"></div>
        </div>
    </div>
    <?php
}

/**
 * Plugin activation hook
 *
 * This function runs when the plugin is activated.
 * Currently, it doesn't perform any specific actions, but it's here for future enhancements.
 */
function wp_openai_demo_activate() {
    // Flush rewrite rules if needed (not required for this plugin)
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wp_openai_demo_activate');

/**
 * Plugin deactivation hook
 *
 * This function runs when the plugin is deactivated.
 * It cleans up by flushing rewrite rules.
 */
function wp_openai_demo_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_openai_demo_deactivate');

/**
 * Plugin uninstall hook
 *
 * This function runs when the plugin is uninstalled.
 * It can be used to clean up any plugin-specific data if needed.
 */
function wp_openai_demo_uninstall() {
    // No specific uninstall actions required for this plugin
}
register_uninstall_hook(__FILE__, 'wp_openai_demo_uninstall');
?>
