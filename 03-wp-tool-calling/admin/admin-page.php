<?php
/**
 * Admin Page for Tool Calling Demo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
add_action('admin_menu', 'wptc_add_admin_menu');

function wptc_add_admin_menu() {
    add_menu_page(
        'Tool Calling Demo',
        '03 TOOL CALLING',
        'manage_options',
        'wp-tool-calling',
        'wptc_admin_page',
        'dashicons-admin-tools',
        3.4
    );
}

// Enqueue admin assets
add_action('admin_enqueue_scripts', 'wptc_admin_enqueue_assets');

function wptc_admin_enqueue_assets($hook) {
    // Only load on our plugin page
    if ($hook !== 'toplevel_page_wp-tool-calling') {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'wptc-admin-styles',
        WP_TOOL_CALLING_URL . 'admin/assets/css/admin-styles.css',
        array(),
        '1.0.0'
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'wptc-admin-script',
        WP_TOOL_CALLING_URL . 'admin/assets/js/admin-script.js',
        array(),
        '1.0.0',
        true
    );
}

// Render admin page
function wptc_admin_page() {
    ?>
    <div class="wrap">
        <div class="wptc-container">
            <h1>🛠️ OpenAI Tool Calling Demo</h1>
            <h2>Demonstrates tool calling with get_weather() and get_sum() functions</h2>

            <div class="wptc-input-group">
                <label for="wptc-apiKey">OpenAI API Key:</label>
                <input type="text" id="wptc-apiKey" placeholder="Enter your OpenAI API key (sk-...)">
            </div>

            <div class="wptc-input-group">
                <label for="wptc-prompt">Your Prompt:</label>
                <input type="text" id="wptc-prompt" placeholder="e.g., What's the weather? or What is 5 + 3?"
                    value="What's the weather in England?" autocomplete="off">
            </div>

            <button id="wptc-sendBtn" class="button button-primary">Send Request</button>

            <div id="wptc-result"></div>
        </div>
    </div>
    <?php
}
