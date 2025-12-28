<?php
/**
 * Plugin Name: ✅ 01 UDEMY BASIC PHP AGENT
 * Plugin URI: https://example.com/wp-basicphp-agent
 * Description: A WordPress plugin that integrates OpenAI API using PHP only. Features API key management with show/hide toggle, query input, and AI response display with ChatGPT-style dark mode.
 * Version: 1.0.0
 * Author: Craig West
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-basicphp-agent
 */

// Exit if accessed directly - security measure to prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Include shortcode functionality
require_once plugin_dir_path(__FILE__) . 'shortcodes/basic-php-agent-shortcode.php';

/**
 * Main Plugin Class
 * Handles all plugin functionality including settings, API calls, and admin pages
 */
class WP_BasicPHP_Agent {
    
    /**
     * Constructor - Initializes the plugin
     * Sets up WordPress hooks for admin menu and settings
     */
    public function __construct() {
        // Add admin menu item
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings for storing API key
        add_action('admin_init', array($this, 'register_settings'));
        
        // Process form submissions
        add_action('admin_init', array($this, 'process_forms'));
    }
    
    #region MENU
    /**
     * Add menu item to WordPress admin sidebar
     * Creates a top-level menu item for the plugin
     */
    public function add_admin_menu() {
        add_menu_page(
            '01 Basic PHP AI Agent',           // Page title
            '01 PHP AGENT',                     // Menu title
            'manage_options',               // Capability required
            'wp-basicphp-agent',           // Menu slug
            array($this, 'render_admin_page'), // Callback function
            'dashicons-admin-generic',     // Icon
            3                         // Position
        );
    }
    #endregion
    /**
     * Register plugin settings with WordPress
     * Allows us to save the API key securely in the database
     */
    public function register_settings() {
        register_setting('wp_basicphp_agent_settings', 'wp_basicphp_agent_api_key');
    }
    
    /**
     * Process all form submissions
     * Handles API key visibility toggle and OpenAI query submissions
     */
    public function process_forms() {
        // Check if user has permission to manage options
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle API key visibility toggle (PHP-only show/hide functionality)
        if (isset($_POST['toggle_api_key_visibility']) && check_admin_referer('toggle_visibility_nonce')) {
            // Get current visibility state from session/transient
            $is_visible = get_transient('wp_basicphp_agent_show_key_' . get_current_user_id());
            
            // Toggle the visibility state
            if ($is_visible) {
                delete_transient('wp_basicphp_agent_show_key_' . get_current_user_id());
            } else {
                set_transient('wp_basicphp_agent_show_key_' . get_current_user_id(), true, 3600); // 1 hour
            }
            
            // Redirect to prevent form resubmission
            wp_redirect(admin_url('admin.php?page=wp-basicphp-agent'));
            exit;
        }
        
        // Handle API key save
        if (isset($_POST['save_api_key']) && check_admin_referer('save_api_key_nonce')) {
            $api_key = sanitize_text_field($_POST['openai_api_key']);
            update_option('wp_basicphp_agent_api_key', $api_key);
            
            // Store success message in transient
            set_transient('wp_basicphp_agent_message', 'API Key saved successfully!', 30);
            
            wp_redirect(admin_url('admin.php?page=wp-basicphp-agent'));
            exit;
        }
    }
    #region API
  
    /**
     * Make API request to OpenAI
     * Sends the user query to OpenAI's gpt-4o-mini model and returns the response
     * 
     * @param string $api_key The OpenAI API key
     * @param string $query The user's query/prompt
     * @return array Response array with 'success' boolean and 'data' or 'error' message
     */
    private function call_openai_api($api_key, $query) {
        // OpenAI API endpoint for chat completions
        $url = 'https://api.openai.com/v1/chat/completions';
        
        // Prepare the request body according to OpenAI API specifications
        $data = array(
            'model' => 'gpt-4o-mini',  // Using the specified model
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $query
                )
            ),
            'temperature' => 0.7,  // Controls randomness (0-2, lower is more focused)
            'max_tokens' => 1000   // Maximum tokens in the response
        );
        
        // Set up HTTP request arguments
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode($data),
            'timeout' => 60  // 60 seconds timeout for API response
        );
    #endregion
    #region AI BIT
        // ********** AI BIT **********

        $response = wp_remote_post($url, $args); // WordPress HTTP API

        // ********** AI BIT **********
    #endregion
    
        // Check for WordPress HTTP errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => 'Request failed: ' . $response->get_error_message()
            );
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
            
            return array(
                'success' => false,
                'error' => 'API Error (Code ' . $response_code . '): ' . $error_message
            );
        }
        
        // Extract the content from the assistant's message
        if (isset($result['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'data' => $result['choices'][0]['message']['content']
            );
        }
        
        // If we get here, the response format was unexpected
        return array(
            'success' => false,
            'error' => 'Unexpected response format from API'
        );
    }
    
    /**
     * Render the admin page HTML
     * Displays the complete interface with API key management and query functionality
     */
    public function render_admin_page() {
        // Get the saved API key from database
        $api_key = get_option('wp_basicphp_agent_api_key', '');
        
        // Check if API key should be visible (PHP-only toggle state)
        $show_key = get_transient('wp_basicphp_agent_show_key_' . get_current_user_id());
        
        // Check for success message
        $message = get_transient('wp_basicphp_agent_message');
        if ($message) {
            delete_transient('wp_basicphp_agent_message');
        }
        
        // Initialize variables for query and response
        $query = '';
        $ai_response = '';
        $error_message = '';
        
        // Process OpenAI query if form submitted
        if (isset($_POST['submit_query']) && check_admin_referer('submit_query_nonce')) {
            $query = sanitize_textarea_field($_POST['ai_query']);
            
            // Validate inputs
            if (empty($api_key)) {
                $error_message = 'Please save your OpenAI API key first.';
            } elseif (empty($query)) {
                $error_message = 'Please enter a query.';
            } else {
                // Make API call
                $result = $this->call_openai_api($api_key, $query);
                
                if ($result['success']) {
                    $ai_response = $result['data'];
                } else {
                    $error_message = $result['error'];
                }
            }
        }
        
        ?>
        <!-- ChatGPT-style Dark Mode Styling -->
        <style>
            /* Main container with dark background */
            .wp-basicphp-agent-container {
                background-color: #343541;
                color: #ececf1;
                padding: 30px;
                border-radius: 8px;
                max-width: 1200px;
                margin: 20px 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            }
            
            /* Section headers */
            .wp-basicphp-agent-container h1 {
                color: #ececf1;
                font-size: 28px;
                margin-bottom: 10px;
                font-weight: 600;
            }
            
            .wp-basicphp-agent-container h2 {
                color: #ececf1;
                font-size: 20px;
                margin-top: 30px;
                margin-bottom: 15px;
                font-weight: 600;
                border-bottom: 1px solid #565869;
                padding-bottom: 10px;
            }
            
            /* Section containers */
            .settings-section,
            .query-section {
                background-color: #444654;
                padding: 25px;
                border-radius: 8px;
                margin-bottom: 25px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }
            
            /* Form labels */
            .wp-basicphp-agent-container label {
                display: block;
                color: #ececf1;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 8px;
            }
            
            /* Input fields */
            .wp-basicphp-agent-container input[type="text"],
            .wp-basicphp-agent-container input[type="password"],
            .wp-basicphp-agent-container textarea {
                width: 100%;
                padding: 12px;
                background-color: #40414f;
                border: 1px solid #565869;
                border-radius: 6px;
                color: #ececf1;
                font-size: 14px;
                transition: border-color 0.2s;
                box-sizing: border-box;
            }
            
            .wp-basicphp-agent-container input[type="text"]:focus,
            .wp-basicphp-agent-container input[type="password"]:focus,
            .wp-basicphp-agent-container textarea:focus {
                outline: none;
                border-color: #10a37f;
            }
            
            .wp-basicphp-agent-container textarea {
                min-height: 120px;
                resize: vertical;
                font-family: inherit;
            }
            
            /* Input group for API key with toggle button */
            .input-group {
                display: flex;
                gap: 10px;
                align-items: flex-start;
            }
            
            .input-group input {
                flex: 1;
            }
            
            /* Buttons */
            .wp-basicphp-agent-container button,
            .wp-basicphp-agent-container input[type="submit"] {
                background-color: #10a37f;
                color: #fff;
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.2s;
            }
            
            .wp-basicphp-agent-container button:hover,
            .wp-basicphp-agent-container input[type="submit"]:hover {
                background-color: #0d8c6c;
            }
            
            /* Secondary button (toggle visibility) */
            .btn-secondary {
                background-color: #565869 !important;
                padding: 12px 16px !important;
                min-width: 80px;
            }
            
            .btn-secondary:hover {
                background-color: #6e6f80 !important;
            }
            
            /* Output area */
            .output-area {
                background-color: #40414f;
                border: 1px solid #565869;
                border-radius: 6px;
                padding: 20px;
                margin-top: 3px;
                min-height: 90px;
                color: #ececf1;
                font-size: 1.5rem;
                line-height: 1.6;
                /* white-space: pre-wrap; */
                word-wrap: break-word;
            }
            
            .output-area.empty {
                color: #8e8ea0;
                font-style: italic;
            }
            
            /* Success message */
            .notice-success {
                background-color: #10a37f;
                color: #fff;
                padding: 12px 20px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid #0d8c6c;
            }
            
            /* Error message */
            .notice-error {
                background-color: #ef4444;
                color: #fff;
                padding: 12px 20px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid #dc2626;
            }
            
            /* Form spacing */
            .form-field {
                margin-bottom: 20px;
            }
            
            /* Response header */
            .output-header {
                font-weight: 600;
                margin: 10px;
                color: #ececf1;
                font-size: 16px;
            }
        </style>
        
        <div class="wrap">
            <div class="wp-basicphp-agent-container">
                <h1>Basic PHP AI Agent</h1>
                <p style="color: #8e8ea0; margin-bottom: 30px;">Connect to OpenAI and interact with AI using PHP only.</p>
                
                <?php
                // Display success message if exists
                if ($message): ?>
                    <div class="notice-success">
                        <?php echo esc_html($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php
                // Display error message if exists
                if ($error_message): ?>
                    <div class="notice-error">
                        <?php echo esc_html($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- API Key Management Section -->
                <div class="settings-section">
                    <h2>OpenAI API Key Settings</h2>
                    
                    <div class="form-field">
                        <label for="openai_api_key">API Key:</label>
                        <div class="input-group">
                            <!-- Form for API Key Input and Visibility Toggle -->
                            <form method="post" action="" style="flex: 1; display: flex; gap: 10px;">
                                <?php wp_nonce_field('save_api_key_nonce'); ?>
                                
                                <!-- API Key Input Field - Type changes based on visibility toggle -->
                                <input 
                                    type="<?php echo $show_key ? 'text' : 'password'; ?>" 
                                    id="openai_api_key" 
                                    name="openai_api_key" 
                                    value="<?php echo esc_attr($api_key); ?>" 
                                    placeholder="sk-..." 
                                    style="flex: 1;"
                                    required
                                />
                                
                                <input type="submit" name="save_api_key" value="Save API Key" />
                            </form>
                            
                            <!-- Separate Form for Show/Hide Toggle - PHP-only functionality -->
                            <form method="post" action="">
                                <?php wp_nonce_field('toggle_visibility_nonce'); ?>
                                <button type="submit" name="toggle_api_key_visibility" class="btn-secondary">
                                    <?php echo $show_key ? 'Hide' : 'Show'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- AI Query Section -->
                <div class="query-section">
                    <h2>Ask AI a Question</h2>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('submit_query_nonce'); ?>
                        
                        <div class="form-field">
                            <label for="ai_query">Your Query:</label>
                            <textarea style="font-size: 1.5rem;min-height:50px; padding:15px;"
                                id="ai_query" 
                                name="ai_query" 
                                placeholder="Enter your question or prompt here..."
                                required
                            ><?php echo esc_textarea($query); ?></textarea>
                        </div>
                        
                        <input type="submit" name="submit_query" value="Send to AI" />
                    </form>
                    
                    <!-- Output Area for AI Response -->
                    <?php if ($ai_response || isset($_POST['submit_query'])): ?>
                        <div class="output-header">AI Response:</div>
                        <div class="output-area <?php echo empty($ai_response) ? 'empty' : ''; ?>" style="font-size: 1.5rem;height:60px;padding:10px;">
                            <?php 
                            if ($ai_response) {
                                echo esc_html($ai_response);
                            } else {
                                echo 'No response received. Please check your query and API key.';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Information Section -->
                <div style="background-color: #444654; padding: 20px; border-radius: 8px; font-size: 13px; color: #8e8ea0;">
                    <strong style="color: #ececf1;">Note:</strong> This plugin uses OpenAI's gpt-4o-mini model. 
                    Make sure you have a valid API key from OpenAI. 
                    All functionality is implemented using PHP only - no JavaScript required.
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin - Create instance of our class
new WP_BasicPHP_Agent();
