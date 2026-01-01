<?php
/**
 * Plugin Name: ✅ 03 UDEMY TOOL USE
 * Plugin URI: https://example.com/wp-tool-use-demo
 * Description: A WordPress plugin that demonstrates OpenAI tool calling functionality with admin interface and shortcode support.
 * Version: 1.0.0
 * Author: Craig West
 * License: GPL v2 or later
 * Text Domain: wp-tool-use-demo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_TOOL_USE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_TOOL_USE_PLUGIN_PATH', plugin_dir_path(__FILE__));

class WPToolUsePlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('wp_tool_use', array($this, 'render_tool_use_shortcode'));
        add_action('wp_ajax_wp_tool_use_request', array($this, 'handle_ajax_request'));
        add_action('wp_ajax_nopriv_wp_tool_use_request', array($this, 'handle_ajax_request'));
    }
    
    public function init() {
        // Plugin initialization
    }
    
    /**
     * Add top-level admin menu with position 3.3
     */
    public function add_admin_menu() {
        add_menu_page(
           '03 UDEMY TOOL USE',
           '03 TOOL USE',
            'manage_options',
            'wp-tool-use-demo',
            array($this, 'admin_page'),
            'dashicons-admin-tools',
            3.3
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="wp-tool-use-admin-container">
                <div class="wp-tool-use-admin-header">
                    <h2><?php _e('🛠️ OpenAI Tool Calling Demo', 'wp-tool-use-demo'); ?></h2>
                    <p><?php _e('Demonstrates tool calling with get_weather() and get_sum() functions', 'wp-tool-use-demo'); ?></p>
                </div>
                
                <div class="wp-tool-use-admin-form">
                    <div class="input-group">
                        <label for="admin_apiKey"><?php _e('OpenAI API Key:', 'wp-tool-use-demo'); ?></label>
                        <input type="text" id="admin_apiKey" class="api-key-input" 
                               placeholder="<?php _e('Enter your OpenAI API key (sk-...)', 'wp-tool-use-demo'); ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="admin_prompt"><?php _e('Your Prompt:', 'wp-tool-use-demo'); ?></label>
                        <input type="text" id="admin_prompt" class="prompt-input" 
                               placeholder="<?php _e('e.g., What\'s the weather? or What is 5 + 3?', 'wp-tool-use-demo'); ?>"
                               value="<?php echo esc_attr('What\'s the weather in England?'); ?>" autocomplete="off">
                    </div>
                    
                    <button id="admin_sendBtn" class="send-button"><?php _e('Send Request', 'wp-tool-use-demo'); ?></button>
                    
                    <div id="admin_result" class="result-container"></div>
                </div>
                
                <div class="wp-tool-use-admin-help">
                    <h3><?php _e('How to Use', 'wp-tool-use-demo'); ?></h3>
                    <ul>
                        <li><?php _e('Enter your OpenAI API key above', 'wp-tool-use-demo'); ?></li>
                        <li><?php _e('Ask about weather (e.g., "What\'s the weather?") to trigger get_weather() tool', 'wp-tool-use-demo'); ?></li>
                        <li><?php _e('Ask math questions (e.g., "What is 5 + 3?") to trigger get_sum() tool', 'wp-tool-use-demo'); ?></li>
                        <li><?php _e('Watch the conversation flow showing tool calls and results', 'wp-tool-use-demo'); ?></li>
                    </ul>
                </div>
                
                <div class="wp-tool-use-admin-shortcode">
                    <h3><?php _e('Shortcode Usage', 'wp-tool-use-demo'); ?></h3>
                    <p><?php _e('You can also use this tool on the frontend with the shortcode:', 'wp-tool-use-demo'); ?></p>
                    <code>[wp_tool_use]</code>
                    <p><?php _e('Or with custom attributes:', 'wp-tool-use-demo'); ?></p>
                    <code>[wp_tool_use title="My Demo" default_prompt="What is 10 + 5?"]</code>
                </div>
            </div>
        </div>
        
        <style>
        .wp-tool-use-admin-container {
            max-width: 800px;
            margin: 20px 0;
        }
        
        .wp-tool-use-admin-header {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .wp-tool-use-admin-header h2 {
            color: #667eea;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .wp-tool-use-admin-header p {
            color: #099709;
            margin: 0;
            font-weight: 500;
        }
        
        .wp-tool-use-admin-form {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .wp-tool-use-admin-help,
        .wp-tool-use-admin-shortcode {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        .wp-tool-use-admin-help h3,
        .wp-tool-use-admin-shortcode h3 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        
        .wp-tool-use-admin-help ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .wp-tool-use-admin-help li {
            margin-bottom: 8px;
            color: #666;
        }
        
        .wp-tool-use-admin-shortcode code {
            background: #f0f0f1;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            display: block;
            margin: 10px 0;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .api-key-input,
        .prompt-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .send-button {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .send-button:hover {
            background: #5568d3;
        }
        
        .send-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .result-container {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
            min-height: 100px;
        }
        </style>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle admin button clicks
            $('#admin_sendBtn').on('click', function() {
                var apiKey = $('#admin_apiKey').val();
                var prompt = $('#admin_prompt').val();
                var $button = $(this);

                if (!apiKey) {
                    alert('<?php _e('Please enter your OpenAI API key', 'wp-tool-use-demo'); ?>');
                    return;
                }

                if (!prompt) {
                    alert('<?php _e('Please enter a prompt', 'wp-tool-use-demo'); ?>');
                    return;
                }

                $button.prop('disabled', true).text('<?php _e('Processing...', 'wp-tool-use-demo'); ?>');
                
                // Simple AJAX call for admin
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wp_tool_use_request',
                        api_key: apiKey,
                        prompt: prompt,
                        nonce: '<?php echo wp_create_nonce('wp_tool_use_nonce'); ?>'
                    },
                    success: function(response) {
                        $button.prop('disabled', false).text('<?php _e('Send Request', 'wp-tool-use-demo'); ?>');
                        if (response.success) {
                            $('#admin_result').html('<p>Request processed successfully!</p>');
                        } else {
                            $('#admin_result').html('<p>Error: ' + response.data.message + '</p>');
                        }
                    },
                    error: function() {
                        $button.prop('disabled', false).text('<?php _e('Send Request', 'wp-tool-use-demo'); ?>');
                        $('#admin_result').html('<p>Network error occurred</p>');
                    }
                });
            });

            // Allow Enter key to submit
            $('#admin_prompt').on('keypress', function(e) {
                if (e.key === 'Enter') {
                    $('#admin_sendBtn').click();
                }
            });
        });
        </script>
        <?php
    }
    
    public function enqueue_scripts() {
        // Only enqueue on pages that have the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wp_tool_use')) {
            wp_enqueue_style('wp-tool-use-style', WP_TOOL_USE_PLUGIN_URL . 'shortcodes/assets/css/frontend-styles.css', array(), '1.0.0');
            wp_enqueue_script('wp-tool-use-script', WP_TOOL_USE_PLUGIN_URL . 'shortcodes/assets/js/frontend-script.js', array('jquery'), '1.0.0', true);
            
            // Localize script for AJAX
            wp_localize_script('wp-tool-use-script', 'wp_tool_use_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_tool_use_nonce')
            ));
        }
    }
    
    public function handle_ajax_request() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wp_tool_use_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sanitize input data
        $api_key = sanitize_text_field($_POST['api_key']);
        $prompt = sanitize_text_field($_POST['prompt']);
        
        // For now, return success response
        wp_send_json_success(array(
            'message' => 'Request processed successfully',
            'data_received' => array(
                'api_key_length' => strlen($api_key),
                'prompt_length' => strlen($prompt)
            )
        ));
    }
    
    public function render_tool_use_shortcode($atts) {
        // Include the shortcode handler
        include_once WP_TOOL_USE_PLUGIN_PATH . 'shortcodes/wp-tool-use-shortcode.php';
        
        // Call the shortcode function from the included file
        return wp_tool_use_shortcode($atts);
    }
}

// Initialize the plugin
new WPToolUsePlugin();

// Activation hook
register_activation_hook(__FILE__, 'wp_tool_use_activation');
function wp_tool_use_activation() {
    // Plugin activation code
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_tool_use_deactivation');
function wp_tool_use_deactivation() {
    // Plugin deactivation code
}
