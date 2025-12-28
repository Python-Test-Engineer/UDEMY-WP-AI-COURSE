<?php
// ############### ADMIN PAGE ###############
// Settings functions for OpenAI

function wp_basic_agent_render_settings_page() {
    // Display any settings errors
    settings_errors('wp_basic_agent_messages');
    
    // Include the settings template
    include plugin_dir_path(__FILE__) . '../templates/openai-settings-template.php';
}
