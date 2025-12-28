<?php
/**
 * Plugin Name: ✅ 05 UDEMY LANGCHAIN 
 * Description: A WordPress plugin demonstrating LangChain.js integration
 * Version: 1.0.0
 * Author: Craig West
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue our bundled JavaScript and CSS
function wpli_enqueue_scripts() {
    // Enqueue the CSS file
    wp_enqueue_style(
        'wp-langchain-integration',
        plugin_dir_url(__FILE__) . 'build/style-index.css',
        array(),
        '1.0.0'
    );

    // Enqueue the JavaScript file
    wp_enqueue_script(
        'wp-langchain-integration',
        plugin_dir_url(__FILE__) . 'build/index.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'wpli_enqueue_scripts');
add_action('admin_enqueue_scripts', 'wpli_enqueue_scripts');

// Add admin menu for demonstration
function wpli_add_admin_menu() {
    add_menu_page(
        '05 UDEMY LangChain Demo',
        '05 LANGCHAIN',
        'manage_options',
        'wp-langchain-demo',
        'wpli_admin_page',        // call back function below
        'dashicons-admin-site',
        4.2
    );
}
add_action('admin_menu', 'wpli_add_admin_menu');

function wpli_admin_page() {
    ?>
    <div class="wrap">
        <h1>LangChain.js Integration Demo</h1>
        <div id="langchain-demo-app"></div>
    </div>
    <?php
}
