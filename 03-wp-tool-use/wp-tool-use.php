<?php
/*
Plugin Name: ✅ 03 UDEMY TOOL USE 
Description: Demonstration of AI tool use in WordPress. Includes weather and math tools for educational purposes.
Version: 1.0.0
Author: Craig West
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register settings for API key storage
 * This stores the OpenAI API key in WordPress options table
 */
function wp_tool_use_register_settings() {
    register_setting('wp_tool_use_settings', 'wp_tool_use_api_key');
}
add_action('admin_init', 'wp_tool_use_register_settings');

/**
 * Process form submissions for API key and AJAX requests
 */
function wp_tool_use_process_forms() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle API key save
    if (isset($_POST['save_tool_use_api_key']) && check_admin_referer('wp_tool_use_save_key_nonce')) {
        $api_key = sanitize_text_field($_POST['wp_tool_use_api_key']);
        update_option('wp_tool_use_api_key', $api_key);
        set_transient('wp_tool_use_message', 'API Key saved successfully!', 30);

        wp_redirect(admin_url('admin.php?page=tool-use-demo'));
        exit;
    }
}
add_action('admin_init', 'wp_tool_use_process_forms');

/**
 * Add menu item to WordPress admin
 */
function wp_tool_use_admin_menu() {
    add_menu_page(
        '03 UDEMY Tool Use Demo',           // Page title
        '03 TOOL USE',           // Menu title
        'manage_options',          // Capability
        'tool-use-demo',           // Menu slug
        'wp_tool_use_admin_page',  // Function to display page
        'dashicons-admin-generic',         // Icon
        3.3                        // Position
    );
}
add_action('admin_menu', 'wp_tool_use_admin_menu');

/**
 * Enqueue admin scripts and styles
 */
function wp_tool_use_enqueue_admin_assets($hook) {
    // Only load on our plugin page
    if ($hook !== 'toplevel_page_tool-use-demo') {
        return;
    }

    wp_enqueue_script(
        'tool-use-js',
        plugins_url('tool-use.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script('tool-use-js', 'toolUseAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tool_use_nonce')
    ));

    wp_enqueue_style(
        'tool-use-css',
        plugins_url('tool-use.css', __FILE__),
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'wp_tool_use_enqueue_admin_assets');

/**
 * Define the weather tool function
 * This is a fake weather API that returns a random temperature in Celsius
 *
 * @return int Random temperature between -10°C and 40°C
 */
if (!function_exists('wp_tool_use_get_weather')) {
    function wp_tool_use_get_weather() {
        // Generate a random temperature between -10 and 40 Celsius
        $temp = rand(-10, 40);
        return $temp;
    }
}

/**
 * Backward compatibility: Keep old function name
 */
if (!function_exists('get_weather')) {
    function get_weather() {
        return wp_tool_use_get_weather();
    }
}

/**
 * Define the add two numbers tool function
 * Simple mathematical function to add two numbers
 *
 * @param float $a First number to add
 * @param float $b Second number to add
 * @return float Sum of the two numbers
 */
if (!function_exists('wp_tool_use_add_two_numbers')) {
    function wp_tool_use_add_two_numbers($a, $b) {
        return $a + $b;
    }
}

/**
 * Backward compatibility: Keep old function name
 */
if (!function_exists('add_two_numbers')) {
    function add_two_numbers($a, $b) {
        return wp_tool_use_add_two_numbers($a, $b);
    }
}

/**
 * AJAX handler for AI tool use proxy
 * This function handles requests to OpenAI API with tool calling enabled
 */
function wp_tool_use_ai_proxy() {
    // Verify nonce and permissions
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tool_use_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized access'));
    }

    // Get API key
    $api_key = get_option('wp_tool_use_api_key', '');
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'No API key configured'));
    }

    // Get user prompt
    $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';
    if (empty($prompt)) {
        wp_send_json_error(array('message' => 'No prompt provided'));
    }
    #region TOOLS
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
    #endregion TOOLS
    #region PROMPT
    // System prompt explaining tool use
    $system_prompt = "You are an AI assistant with access to tools. When a user asks about weather or math operations, use the appropriate tool to get accurate information. For weather, call get_weather(). For adding numbers, call add_two_numbers() with the two numbers as parameters. Always provide helpful responses and explain what you're doing when using tools.";

    // Prepare OpenAI request with tools
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
    #endregion PROMPT
    // *****************************************
    #region OPENAI
    // Make request to OpenAI
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'body' => json_encode($body),
        'timeout' => 30
    ));
    // ******************************

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
    #region TOOLS?
    // Check if the AI wants to use a tool
    $message = $data['choices'][0]['message'];
    $tool_calls = isset($message['tool_calls']) ? $message['tool_calls'] : array();

    $response_data = array(
        'message' => $message['content'] ?: 'AI is processing...',
        'tool_calls' => array()
    );

    // Process any tool calls
    if (!empty($tool_calls)) {
        foreach ($tool_calls as $tool_call) {
            $function_name = $tool_call['function']['name'];
            $arguments = json_decode($tool_call['function']['arguments'], true);

            $result = null;

            // Execute the appropriate tool function
            switch ($function_name) {
                case 'get_weather':
                    $result = get_weather();
                    $tool_response = "Weather tool executed: Current temperature is {$result}°C";
                    break;

                case 'add_two_numbers':
                    $a = isset($arguments['a']) ? floatval($arguments['a']) : 0;
                    $b = isset($arguments['b']) ? floatval($arguments['b']) : 0;
                    $result = add_two_numbers($a, $b);
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
}
add_action('wp_ajax_tool_use_ai', 'wp_tool_use_ai_proxy');

/**
 * Display the admin page
 */
function wp_tool_use_admin_page() {
    // Handle messages
    if ($message = get_transient('wp_tool_use_message')) {
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
        delete_transient('wp_tool_use_message');
    }

    // Get API key (show/hide functionality)
    $api_key = get_option('wp_tool_use_api_key', '');
    $show_key = isset($_GET['show_key']) && $_GET['show_key'] === '1';

    ?>
    <div class="wrap">
        <h1>Tool Use Demo</h1>

        <!-- API Key Settings -->
        <div class="card">
            <h2>OpenAI API Settings</h2>
            <form method="post" action="">
                <?php wp_nonce_field('wp_tool_use_save_key_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="<?php echo $show_key ? 'text' : 'password'; ?>"
                                   name="wp_tool_use_api_key"
                                   value="<?php echo esc_attr($api_key); ?>"
                                   class="regular-text" />
                            <p class="description">
                                <a href="<?php echo esc_url(add_query_arg('show_key', $show_key ? '0' : '1')); ?>">
                                    <?php echo $show_key ? 'Hide' : 'Show'; ?> API Key
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save API Key', 'primary', 'save_tool_use_api_key'); ?>
            </form>
        </div>

        <!-- Tool Use Demo -->
        <div class="card">
            <h2>AI Tool Use Demonstration</h2>
            <p>This demo shows how AI can use tools to perform specific functions. Try asking about weather or doing math calculations.</p>

            <div id="tool-demo-container">
                <textarea id="tool-prompt" rows="3" placeholder="Ask about weather or do math calculations (e.g., 'What's the weather today?' or 'Add 5 and 7')" style="width: 100%;"></textarea>
                <button id="send-tool-prompt" class="button button-primary">Send to AI</button>

                <div id="tool-response" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #007cba; display: none;">
                    <h3>AI Response:</h3>
                    <div id="ai-message"></div>
                    <div id="tool-executions" style="margin-top: 15px;"></div>
                </div>
            </div>
        </div>

        <!-- Tool Documentation -->
        <div class="card">
            <h2>Available Tools</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <h3>🌤️ Weather Tool</h3>
                    <p><strong>Function:</strong> get_weather()</p>
                    <p><strong>Description:</strong> Returns a random temperature in Celsius (-10°C to 40°C)</p>
                    <p><strong>Example prompt:</strong> "What's the current temperature?"</p>
                </div>

                <div>
                    <h3>➕ Math Tool</h3>
                    <p><strong>Function:</strong> add_two_numbers(a, b)</p>
                    <p><strong>Description:</strong> Adds two numbers together</p>
                    <p><strong>Example prompt:</strong> "Add 15 and 23"</p>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#send-tool-prompt').on('click', function() {
            var prompt = $('#tool-prompt').val().trim();
            if (!prompt) {
                alert('Please enter a prompt');
                return;
            }

            $(this).prop('disabled', true).text('Processing...');

            $.ajax({
                url: toolUseAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tool_use_ai',
                    prompt: prompt,
                    nonce: toolUseAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        // Display AI message
                        $('#ai-message').html('<p>' + (data.message || 'No response') + '</p>');

                        // Display tool executions
                        var toolHtml = '';
                        if (data.tool_calls && data.tool_calls.length > 0) {
                            toolHtml = '<h4>Tool Executions:</h4><ul>';
                            data.tool_calls.forEach(function(tool) {
                                toolHtml += '<li><strong>' + tool.function + '</strong>: ' + tool.response + '</li>';
                            });
                            toolHtml += '</ul>';
                        }

                        $('#tool-executions').html(toolHtml);
                        $('#tool-response').show();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('AJAX request failed');
                },
                complete: function() {
                    $('#send-tool-prompt').prop('disabled', false).text('Send to AI');
                }
            });
        });
    });
    </script>
    <?php
}

// Include shortcode functionality - must be after tool functions are defined
$shortcode_file = plugin_dir_path(__FILE__) . 'shortcodes/wp-tool-use-shortcode.php';
if (file_exists($shortcode_file)) {
    require_once $shortcode_file;
} else {
    error_log('WP Tool Use: Shortcode file not found at: ' . $shortcode_file);
}

/**
 * Plugin activation hook
 */
function wp_tool_use_activate() {
    // Create any necessary database tables or options here if needed
}
register_activation_hook(__FILE__, 'wp_tool_use_activate');

/**
 * Plugin deactivation hook
 */
function wp_tool_use_deactivate() {
    // Clean up if necessary
}
register_deactivation_hook(__FILE__, 'wp_tool_use_deactivate');
