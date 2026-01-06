<?php
// ############### AGENT ###############
// AJAX handler to proxy OpenAI API calls (returns JSON, JavaScript extracts the message)
function wp_basic_agent_openai_proxy() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'openai_key_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized access'));
    }
    
    // Get API key from WordPress options
    $api_key = get_option('wp_basic_agent_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'No API key configured. Please save your OpenAI API key in the settings.'));
    }
    
    // Get prompt from request
    $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';
    
    if (empty($prompt)) {
        wp_send_json_error(array('message' => 'No prompt provided'));
    }
    #region PROMPT
    // System prompt
    include plugin_dir_path(__FILE__) . '../includes/inc-system-prompt.php';
    
    // Prepare request to OpenAI
    $body = array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $prompt)
        ),
        'max_tokens' => 1024
    );
    #endregion PROMPT

    // ********** AI BIT **********
    #region AI BIT
    // Make request to OpenAI
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'body' => json_encode($body),
        'timeout' => 30
    ));
    
    // ********** AI BIT **********
    #endregion AI BIT
    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);
    
    if ($response_code !== 200) {
        $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
        wp_send_json_error(array('message' => $error_message));
    }
    
    // Return the full OpenAI response as JSON
    // JavaScript will extract just the message content
    wp_send_json_success($data);
}
add_action('wp_ajax_openai_proxy', 'wp_basic_agent_openai_proxy');
