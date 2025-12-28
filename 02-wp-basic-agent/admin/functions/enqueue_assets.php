<?php
// Enqueue admin assets for OpenAI settings page
function wp_basic_agent_enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_basic-agent-settings') {
        return;
    }

    $plugin_url = plugin_dir_url(dirname(__FILE__, 2));
    
    // Enqueue HTMX from CDN
    wp_enqueue_script('htmx', 'https://unpkg.com/htmx.org@1.9.10', array(), '1.9.10', true);
    
    // Enqueue styles and minimal JS (version bump to clear cache)
    wp_enqueue_style('wp-basic-agent-admin-styles', $plugin_url . 'admin/assets/css/admin-styles.css', array(), '3.1.0');
    wp_enqueue_script('wp-basic-agent-openai-js', $plugin_url . 'admin/assets/js/openai.js', array(), '3.1.0', true);
    
    // Pass config to JavaScript
    wp_localize_script('wp-basic-agent-openai-js', 'wpBasicAgent', array(
        'nonce' => wp_create_nonce('openai_key_nonce'),
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
}
