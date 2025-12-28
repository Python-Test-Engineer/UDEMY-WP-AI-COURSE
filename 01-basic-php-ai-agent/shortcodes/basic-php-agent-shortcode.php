<?php
/**
 * Basic PHP AI Agent Shortcode Handler
 * 
 * Provides shortcode functionality to display the AI agent on the frontend
 * Usage: [basic_php_ai_agent]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the Basic PHP AI Agent shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output for the AI agent
 */
function basic_php_ai_agent_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'placeholder' => 'Ask me anything...', // Customizable placeholder text
            'show_api_key_input' => 'yes', // Show API key input field
        ),
        $atts,
        'basic_php_ai_agent'
    );
    
    // Check if API key is configured in admin
    $admin_api_key = get_option('wp_basicphp_agent_api_key', '');
    $show_api_input = strtolower($atts['show_api_key_input']) === 'yes';
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="basic-php-ai-agent-frontend-wrapper">
        <div class="basic-php-ai-agent-container">
            <div class="basic-php-ai-agent-header">
                <h3><?php _e('AI Agent', 'wp-basicphp-agent'); ?></h3>
                <p><?php _e('Ask me anything! Powered by OpenAI GPT-4o-mini', 'wp-basicphp-agent'); ?></p>
            </div>

            <?php if ($show_api_input): ?>
                <!-- API Key Section -->
                <div class="basic-php-ai-agent-api-section">
                    <h4><?php _e('OpenAI API Key', 'wp-basicphp-agent'); ?></h4>
                    <div class="basic-php-ai-agent-api-input-group">
                        <input 
                            type="password" 
                            id="basic-php-ai-agent-api-key" 
                            name="api_key" 
                            class="basic-php-ai-agent-input" 
                            placeholder="sk-..."
                            value="<?php echo esc_attr($admin_api_key); ?>"
                        />
                        <button type="button" id="basic-php-ai-agent-toggle-key" class="basic-php-ai-agent-button basic-php-ai-agent-button-secondary">
                            <?php _e('Show', 'wp-basicphp-agent'); ?>
                        </button>
                    </div>
                    <p class="basic-php-ai-agent-help-text">
                        <?php _e('Enter your OpenAI API key or use the one configured in admin settings.', 'wp-basicphp-agent'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="basic-php-ai-agent-form-container">
                <form id="basic-php-ai-agent-form" class="basic-php-ai-agent-form">
                    <div class="basic-php-ai-agent-input-group">
                        <textarea 
                            id="basic-php-ai-agent-query" 
                            name="query" 
                            class="basic-php-ai-agent-textarea" 
                            placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                            rows="3"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="basic-php-ai-agent-button-group">
                        <button type="submit" class="basic-php-ai-agent-button basic-php-ai-agent-button-primary">
                            <span class="button-text"><?php _e('Send', 'wp-basicphp-agent'); ?></span>
                            <span class="button-loading" style="display: none;"><?php _e('Thinking...', 'wp-basicphp-agent'); ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <div id="basic-php-ai-agent-response-container" class="basic-php-ai-agent-response-container" style="display: none;">
                <div class="basic-php-ai-agent-response-header">
                    <strong><?php _e('AI Response:', 'wp-basicphp-agent'); ?></strong>
                </div>
                <div id="basic-php-ai-agent-response" class="basic-php-ai-agent-response-content"></div>
            </div>

            <div id="basic-php-ai-agent-error-container" class="basic-php-ai-agent-error-container" style="display: none;">
                <div class="basic-php-ai-notice basic-php-ai-error">
                    <p id="basic-php-ai-agent-error-message"></p>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php
    
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('basic_php_ai_agent', 'basic_php_ai_agent_shortcode');

/**
 * Enqueue frontend assets for the AI agent shortcode
 */
function basic_php_ai_agent_enqueue_frontend_assets() {
    // Only enqueue if we're on a page/post that has the shortcode
    global $post;
    
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'basic_php_ai_agent')) {
        // Enqueue frontend CSS
        wp_enqueue_style(
            'basic-php-ai-agent-frontend-styles',
            plugins_url('shortcodes/assets/css/frontend-styles.css', dirname(__FILE__)),
            array(),
            '1.0.0'
        );
        
        // Enqueue frontend JavaScript
        wp_enqueue_script(
            'basic-php-ai-agent-frontend-script',
            plugins_url('shortcodes/assets/js/frontend-script.js', dirname(__FILE__)),
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'basic-php-ai-agent-frontend-script',
            'basicPhpAiAgent',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('basic_php_ai_agent_nonce')
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'basic_php_ai_agent_enqueue_frontend_assets');

/**
 * AJAX handler for frontend AI queries
 */
function basic_php_ai_agent_ajax_handler() {
    // Verify nonce
    check_ajax_referer('basic_php_ai_agent_nonce', 'nonce');
    
    // Get the query from POST data
    $query = isset($_POST['query']) ? sanitize_textarea_field($_POST['query']) : '';
    
    // Validate query
    if (empty($query)) {
        wp_send_json_error(array('message' => 'Please enter a query.'));
        return;
    }
    
    // Get API key - prioritize frontend input, fallback to admin settings
    $api_key = isset($_POST['api_key']) && !empty($_POST['api_key']) 
        ? sanitize_text_field($_POST['api_key']) 
        : get_option('wp_basicphp_agent_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'API key is not configured. Please enter an API key or contact the administrator.'));
        return;
    }
    
    // Make OpenAI API call
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array(
                'role' => 'user',
                'content' => $query
            )
        ),
        'temperature' => 0.7,
        'max_tokens' => 1000
    );
    
    $args = array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'body' => json_encode($data),
        'timeout' => 60
    );
    
    $response = wp_remote_post($url, $args);
    
    // Check for WordPress HTTP errors
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Request failed: ' . $response->get_error_message()));
        return;
    }
    
    // Get the response code
    $response_code = wp_remote_retrieve_response_code($response);
    
    // Get the response body
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    // Check if response code indicates success
    if ($response_code !== 200) {
        $error_message = isset($result['error']['message']) 
            ? $result['error']['message'] 
            : 'Unknown error occurred';
        
        wp_send_json_error(array('message' => 'API Error (Code ' . $response_code . '): ' . $error_message));
        return;
    }
    
    // Extract the content from the assistant's message
    if (isset($result['choices'][0]['message']['content'])) {
        wp_send_json_success(array('response' => $result['choices'][0]['message']['content']));
    } else {
        wp_send_json_error(array('message' => 'Unexpected response format from API'));
    }
}
add_action('wp_ajax_basic_php_ai_agent_query', 'basic_php_ai_agent_ajax_handler');
add_action('wp_ajax_nopriv_basic_php_ai_agent_query', 'basic_php_ai_agent_ajax_handler');
