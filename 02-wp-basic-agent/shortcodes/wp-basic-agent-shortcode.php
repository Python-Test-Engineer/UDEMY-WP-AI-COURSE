<?php
/**
 * WP Basic Agent Shortcode Handler
 * 
 * Provides shortcode functionality to display the AI agent on the frontend
 * Usage: [wp_basic_agent]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the WP Basic Agent shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output for the AI agent
 */
function wp_basic_agent_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'placeholder' => 'Enter your prompt...', // Customizable placeholder text
            'show_api_key_input' => 'yes', // Show API key input field
        ),
        $atts,
        'wp_basic_agent'
    );
    
    // Check if API key is configured in admin
    $admin_api_key = get_option('wp_basic_agent_api_key', '');
    $show_api_input = strtolower($atts['show_api_key_input']) === 'yes';
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="wp-basic-agent-frontend-wrapper">
        <div class="wp-basic-agent-container">
            <div class="wp-basic-agent-header">
                <h3><?php _e('AI Agent', 'wp-basic-agent'); ?></h3>
                <p><?php _e('Ask me anything! Powered by OpenAI GPT-4o-mini', 'wp-basic-agent'); ?></p>
            </div>

            <?php if ($show_api_input): ?>
                <!-- API Key Section -->
                <div class="wp-basic-agent-api-section">
                    <h4><?php _e('OpenAI API Key', 'wp-basic-agent'); ?></h4>
                    <div class="wp-basic-agent-api-input-group">
                        <input 
                            type="password" 
                            id="wp-basic-agent-api-key" 
                            name="api_key" 
                            class="wp-basic-agent-input" 
                            placeholder="sk-..."
                            value="<?php echo esc_attr($admin_api_key); ?>"
                        />
                        <button type="button" id="wp-basic-agent-toggle-key" class="wp-basic-agent-button wp-basic-agent-button-secondary">
                            <?php _e('Show', 'wp-basic-agent'); ?>
                        </button>
                    </div>
                    <p class="wp-basic-agent-help-text">
                        <?php _e('Enter your OpenAI API key or use the one configured in admin settings.', 'wp-basic-agent'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="wp-basic-agent-form-container">
                <form id="wp-basic-agent-form" class="wp-basic-agent-form">
                    <div class="wp-basic-agent-input-group">
                        <textarea 
                            id="wp-basic-agent-query" 
                            name="query" 
                            class="wp-basic-agent-textarea" 
                            placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                            rows="3"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="wp-basic-agent-button-group">
                        <button type="submit" class="wp-basic-agent-button wp-basic-agent-button-primary">
                            <span class="button-text"><?php _e('Send Request', 'wp-basic-agent'); ?></span>
                            <span class="button-loading" style="display: none;"><?php _e('⏳ Processing...', 'wp-basic-agent'); ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <div id="wp-basic-agent-response-container" class="wp-basic-agent-response-container" style="display: none;">
                <div class="wp-basic-agent-response-header">
                    <strong><?php _e('AI Response:', 'wp-basic-agent'); ?></strong>
                </div>
                <div id="wp-basic-agent-response" class="wp-basic-agent-response-content"></div>
            </div>

            <div id="wp-basic-agent-error-container" class="wp-basic-agent-error-container" style="display: none;">
                <div class="wp-basic-agent-notice wp-basic-agent-error">
                    <p id="wp-basic-agent-error-message"></p>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php
    
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('wp_basic_agent', 'wp_basic_agent_shortcode');

/**
 * Enqueue frontend assets for the AI agent shortcode
 */
function wp_basic_agent_enqueue_frontend_assets() {
    // Only enqueue if we're on a page/post that has the shortcode
    global $post;
    
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wp_basic_agent')) {
        // Enqueue frontend CSS
        wp_enqueue_style(
            'wp-basic-agent-frontend-styles',
            plugins_url('assets/css/frontend-styles.css', __FILE__),
            array(),
            '1.0.0'
        );
        
        // Enqueue frontend JavaScript
        wp_enqueue_script(
            'wp-basic-agent-frontend-script',
            plugins_url('assets/js/frontend-script.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'wp-basic-agent-frontend-script',
            'wpBasicAgentFrontend',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_basic_agent_frontend_nonce')
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'wp_basic_agent_enqueue_frontend_assets');

/**
 * AJAX handler for frontend AI queries
 */
function wp_basic_agent_frontend_ajax_handler() {
    // Verify nonce
    check_ajax_referer('wp_basic_agent_frontend_nonce', 'nonce');
    
    // Get the query from POST data
    $prompt = isset($_POST['query']) ? sanitize_textarea_field($_POST['query']) : '';
    
    // Validate query
    if (empty($prompt)) {
        wp_send_json_error(array('message' => 'Please enter a query.'));
        return;
    }
    
    // Get API key - prioritize frontend input, fallback to admin settings
    $api_key = isset($_POST['api_key']) && !empty($_POST['api_key']) 
        ? sanitize_text_field($_POST['api_key']) 
        : get_option('wp_basic_agent_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'API key is not configured. Please enter an API key or contact the administrator.'));
        return;
    }
    
    // Load system prompt
    include plugin_dir_path(dirname(__FILE__)) . 'admin/includes/inc-system-prompt.php';
    
    // Prepare request body for OpenAI API
    $body = array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $prompt)
        ),
        'max_tokens' => 1024
    );
    
    // Make OpenAI API call
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'body' => json_encode($body),
        'timeout' => 30
    ));
    
    // Check for WordPress HTTP errors
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Request failed: ' . $response->get_error_message()));
        return;
    }
    
    // Get the response code
    $response_code = wp_remote_retrieve_response_code($response);
    
    // Get the response body
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);
    
    // Check if response code indicates success
    if ($response_code !== 200) {
        $error_message = isset($data['error']['message']) 
            ? $data['error']['message'] 
            : 'Unknown error occurred';
        
        wp_send_json_error(array('message' => 'OpenAI API Error (Code ' . $response_code . '): ' . $error_message));
        return;
    }
    
    // Extract the content from the assistant's message
    if (isset($data['choices'][0]['message']['content'])) {
        wp_send_json_success(array('response' => $data['choices'][0]['message']['content']));
    } else {
        wp_send_json_error(array('message' => 'Unexpected response format from API'));
    }
}
add_action('wp_ajax_wp_basic_agent_frontend_query', 'wp_basic_agent_frontend_ajax_handler');
add_action('wp_ajax_nopriv_wp_basic_agent_frontend_query', 'wp_basic_agent_frontend_ajax_handler');
