<?php
/**
 * WP Tool Use Shortcode Handler
 * 
 * Provides shortcode functionality to display the AI tool use demo on the frontend
 * Usage: [wp_tool_use]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the WP Tool Use shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output for the tool use interface
 */
function wp_tool_use_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'placeholder' => 'Ask about weather or do math calculations...', // Customizable placeholder text
            'show_api_key_input' => 'yes', // Show API key input field
            'show_tool_info' => 'yes', // Show tool information section
        ),
        $atts,
        'wp_tool_use'
    );
    
    // Check if API key is configured in admin
    $admin_api_key = get_option('wp_tool_use_api_key', '');
    $show_api_input = strtolower($atts['show_api_key_input']) === 'yes';
    $show_tool_info = strtolower($atts['show_tool_info']) === 'yes';
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="wp-tool-use-frontend-wrapper">
        <div class="wp-tool-use-container">
            <div>
                <h2>TOOL USE</h2>
                <p><?php _e('Ask about weather or do math - the AI will use tools to help!', 'wp-tool-use'); ?></p>
            </div>

            <?php if ($show_api_input): ?>
                <!-- API Key Section -->
                <div class="wp-tool-use-api-section">
                    <h2>OpenAI API KEY</h2>
                    <div class="wp-tool-use-api-input-group">
                        <input 
                            type="password" 
                            id="wp-tool-use-api-key" 
                            name="api_key" 
                            class="wp-tool-use-input" 
                            placeholder="sk-..."
                            value="<?php echo esc_attr($admin_api_key); ?>"
                        />
                        <button type="button" id="wp-tool-use-toggle-key" class="wp-tool-use-button wp-tool-use-button-secondary">
                            <?php _e('Show', 'wp-tool-use'); ?>
                        </button>
                    </div>
                    <p class="wp-tool-use-help-text">
                        <?php _e('Enter your OpenAI API key or use the one configured in admin settings.', 'wp-tool-use'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($show_tool_info): ?>
                <!-- Available Tools Info -->
                <div class="wp-tool-use-tools-section">
                    <h4><?php _e('Available Tools', 'wp-tool-use'); ?></h4>
                    <div class="wp-tool-use-tools-grid">
                        <div class="wp-tool-use-tool-card">
                            <div class="wp-tool-use-tool-icon">🌤️</div>
                            <h5>Weather Tool</h5>
                            <p class="wp-tool-use-tool-desc">Get current temperature</p>
                            <p class="wp-tool-use-tool-example">Try: "What's the weather?"</p>
                        </div>
                        <div class="wp-tool-use-tool-card">
                            <div class="wp-tool-use-tool-icon">➕</div>
                            <h5>Math Tool</h5>
                            <p class="wp-tool-use-tool-desc">Add two numbers</p>
                            <p class="wp-tool-use-tool-example">Try: "Add 15 and 27"</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="wp-tool-use-form-container">
                <form id="wp-tool-use-form" class="wp-tool-use-form">
                    <div class="wp-tool-use-input-group">
                        <textarea 
                            id="wp-tool-use-query" 
                            name="query" 
                            class="wp-tool-use-textarea" 
                            placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                            rows="3"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="wp-tool-use-button-group">
                        <button type="submit" class="wp-tool-use-button wp-tool-use-button-primary">
                            <span class="button-text"><?php _e('Send to AI', 'wp-tool-use'); ?></span>
                            <span class="button-loading" style="display: none;"><?php _e('⏳ Processing...', 'wp-tool-use'); ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <div id="wp-tool-use-response-container" class="wp-tool-use-response-container" style="display: none;">
                <div class="wp-tool-use-response-header">
                    <strong><?php _e('AI Response:', 'wp-tool-use'); ?></strong>
                </div>
                <div id="wp-tool-use-response" class="wp-tool-use-response-content"></div>
                
                <div id="wp-tool-use-tools-executed" class="wp-tool-use-tools-executed" style="display: none;">
                    <div class="wp-tool-use-tools-header">
                        <strong><?php _e('🔧 Tools Executed:', 'wp-tool-use'); ?></strong>
                    </div>
                    <ul id="wp-tool-use-tools-list" class="wp-tool-use-tools-list"></ul>
                </div>
            </div>

            <div id="wp-tool-use-error-container" class="wp-tool-use-error-container" style="display: none;">
                <div class="wp-tool-use-notice wp-tool-use-error">
                    <p id="wp-tool-use-error-message"></p>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php
    
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('wp_tool_use', 'wp_tool_use_shortcode');

/**
 * Enqueue frontend assets for the tool use shortcode
 */
function wp_tool_use_enqueue_frontend_assets() {
    // Only enqueue if we're on a page/post that has the shortcode
    global $post;
    
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wp_tool_use')) {
        // Enqueue frontend CSS
        wp_enqueue_style(
            'wp-tool-use-frontend-styles',
            plugins_url('assets/css/frontend-styles.css', __FILE__),
            array(),
            '1.0.0'
        );
        
        // Enqueue frontend JavaScript
        wp_enqueue_script(
            'wp-tool-use-frontend-script',
            plugins_url('assets/js/frontend-script.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'wp-tool-use-frontend-script',
            'wpToolUseFrontend',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_tool_use_frontend_nonce')
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'wp_tool_use_enqueue_frontend_assets');

/**
 * AJAX handler for frontend tool use queries
 */
function wp_tool_use_frontend_ajax_handler() {
    // Verify nonce - use wp_verify_nonce for better error control
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_tool_use_frontend_nonce')) {
        wp_send_json_error(array('message' => 'Security verification failed. Please refresh the page and try again.'));
        wp_die();
    }
    
    // Get the query from POST data
    $prompt = isset($_POST['query']) ? sanitize_textarea_field($_POST['query']) : '';
    
    // Validate query
    if (empty($prompt)) {
        wp_send_json_error(array('message' => 'Please enter a query.'));
        wp_die();
    }
    
    // Get API key - prioritize frontend input, fallback to admin settings
    $api_key = isset($_POST['api_key']) && !empty($_POST['api_key']) 
        ? sanitize_text_field($_POST['api_key']) 
        : get_option('wp_tool_use_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'API key is not configured. Please enter an API key or contact the administrator.'));
        wp_die();
    }
    
    // Define the tools available to the AI
    $tools = array(
        array(
            'type' => 'function',
            'function' => array(
                'name' => 'get_weather',
                'description' => 'Get the current weather temperature in Celsius. Returns a random temperature between -10°C and 40°C.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => (object)array(),
                    'required' => array()
                )
            )
        ),
        array(
            'type' => 'function',
            'function' => array(
                'name' => 'add_two_numbers',
                'description' => 'Add two numbers together and return their sum.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'a' => array(
                            'type' => 'number',
                            'description' => 'First number to add'
                        ),
                        'b' => array(
                            'type' => 'number',
                            'description' => 'Second number to add'
                        )
                    ),
                    'required' => array('a', 'b')
                )
            )
        )
    );
    
    // System prompt explaining tool use
    $system_prompt = "You are an AI assistant with access to tools. When a user asks about weather or math operations, use the appropriate tool to get accurate information. For weather, call get_weather(). For adding numbers, call add_two_numbers() with the two numbers as parameters. Always provide helpful responses and explain what you're doing when using tools.";
    
    // Prepare request body for OpenAI API with tools
    $body = array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $prompt)
        ),
        'tools' => $tools,  // Enable tool use
        'tool_choice' => 'auto', // Let AI decide when to use tools
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
        wp_die();
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
        wp_die();
    }
    
    // Check if the AI wants to use a tool
    $message = isset($data['choices'][0]['message']) ? $data['choices'][0]['message'] : array();
    $tool_calls = isset($message['tool_calls']) ? $message['tool_calls'] : array();
    
    $response_data = array(
        'message' => isset($message['content']) ? $message['content'] : 'AI is processing with tools...',
        'tool_calls' => array()
    );
    
    // Process any tool calls
    if (!empty($tool_calls)) {
        foreach ($tool_calls as $tool_call) {
            $function_name = $tool_call['function']['name'];
            $arguments = json_decode($tool_call['function']['arguments'], true);
            
            $result = null;
            $tool_response = '';
            
            // Execute the appropriate tool function
            switch ($function_name) {
                case 'get_weather':
                    // Check if function exists to prevent fatal errors
                    if (function_exists('wp_tool_use_get_weather')) {
                        $result = wp_tool_use_get_weather();
                    } elseif (function_exists('get_weather')) {
                        $result = get_weather();
                    } else {
                        $result = 'N/A';
                        $tool_response = "Weather tool not available";
                        break;
                    }
                    $tool_response = "Weather tool executed: Current temperature is {$result}°C";
                    break;
                    
                case 'add_two_numbers':
                    $a = isset($arguments['a']) ? floatval($arguments['a']) : 0;
                    $b = isset($arguments['b']) ? floatval($arguments['b']) : 0;
                    
                    // Check if function exists to prevent fatal errors
                    if (function_exists('wp_tool_use_add_two_numbers')) {
                        $result = wp_tool_use_add_two_numbers($a, $b);
                    } elseif (function_exists('add_two_numbers')) {
                        $result = add_two_numbers($a, $b);
                    } else {
                        $result = 'N/A';
                        $tool_response = "Math tool not available";
                        break;
                    }
                    $tool_response = "Math tool executed: {$a} + {$b} = {$result}";
                    break;
                    
                default:
                    $tool_response = "Unknown tool: {$function_name}";
                    break;
            }
            
            // Add tool call details to response
            $response_data['tool_calls'][] = array(
                'function' => $function_name,
                'arguments' => $arguments,
                'result' => $result,
                'response' => $tool_response
            );
        }
    }
    
    wp_send_json_success($response_data);
    wp_die();
}
add_action('wp_ajax_wp_tool_use_frontend_query', 'wp_tool_use_frontend_ajax_handler');
add_action('wp_ajax_nopriv_wp_tool_use_frontend_query', 'wp_tool_use_frontend_ajax_handler');
