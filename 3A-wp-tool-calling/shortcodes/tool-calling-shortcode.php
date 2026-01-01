<?php
/**
 * Shortcode for Tool Calling Demo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
add_shortcode('tool_calling', 'wptc_shortcode');

// Enqueue frontend assets
add_action('wp_enqueue_scripts', 'wptc_frontend_enqueue_assets');

function wptc_frontend_enqueue_assets() {
    // Enqueue CSS
    wp_register_style(
        'wptc-frontend-styles',
        WP_TOOL_CALLING_URL . 'shortcodes/assets/css/frontend-styles.css',
        array(),
        '1.0.0'
    );

    // Enqueue JavaScript
    wp_register_script(
        'wptc-frontend-script',
        WP_TOOL_CALLING_URL . 'shortcodes/assets/js/frontend-script.js',
        array(),
        '1.0.0',
        true
    );
}

// Shortcode function
function wptc_shortcode($atts) {
    // Enqueue assets
    wp_enqueue_style('wptc-frontend-styles');
    wp_enqueue_script('wptc-frontend-script');

    ob_start();
    ?>
    <div class="wptc-frontend-container">
        <h1>🛠️ OpenAI Tool Calling Demo</h1>
        <h2>Demonstrates tool calling with get_weather() and get_sum() functions</h2>

        <div class="wptc-frontend-input-group">
            <label for="wptc-frontend-apiKey">OpenAI API Key:</label>
            <input type="text" id="wptc-frontend-apiKey" placeholder="Enter your OpenAI API key (sk-...)">
        </div>

        <div class="wptc-frontend-input-group">
            <label for="wptc-frontend-prompt">Your Prompt:</label>
            <input type="text" id="wptc-frontend-prompt" placeholder="e.g., What's the weather? or What is 5 + 3?"
                value="What's the weather in England?" autocomplete="off">
        </div>

        <button id="wptc-frontend-sendBtn">Send Request</button>

        <div id="wptc-frontend-result"></div>
    </div>
    <?php
    return ob_get_clean();
}
