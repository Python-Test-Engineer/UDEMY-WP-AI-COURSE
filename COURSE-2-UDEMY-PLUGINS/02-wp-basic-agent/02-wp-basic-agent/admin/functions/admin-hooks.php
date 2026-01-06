<?php
// Admin hooks for OpenAI settings

// Add admin menu
add_action('admin_menu', 'wp_basic_agent_settings_menu');

function wp_basic_agent_settings_menu() {
    add_menu_page(
        '02 AGENT JS',
        '02 AGENT JS',
        'manage_options',
        'basic-agent-settings',
        'wp_basic_agent_render_settings_page',
        'dashicons-admin-tools',
        3.2
    );
}
