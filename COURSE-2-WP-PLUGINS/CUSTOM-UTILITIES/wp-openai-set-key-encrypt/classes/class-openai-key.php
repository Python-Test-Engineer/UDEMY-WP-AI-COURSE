<?php
/**
 * Secure OpenAI Key Manager Class
 * 
 * This class can be used by other WordPress plugins to securely store and retrieve OpenAI API keys.
 * 
 * @package Secure_OpenAI_Key
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Secure_OpenAI_Key_Manager {
    
    private static $instance = null;
    private $option_name = 'secure_openai_api_key';
    private $nonce_action = 'save_openai_key';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_save_openai_key', array($this, 'save_api_key'));
        add_action('wp_ajax_get_masked_openai_key', array($this, 'ajax_get_masked_key'));
    }
    
    /**
     * Add top-level admin menu with Claude Sonnet 4 icon
     */
    public function add_admin_menu() {
        add_menu_page(
            'OPENAI KEY',           // Page title
            'OPENAI KEY',                     // Menu title
            'manage_options',                     // Capability
            'openai-api-key',                     // Menu slug
            array($this, 'settings_page'),        // Callback function
            'dashicons-admin-network',            // Icon (network/AI-like)
            4                                      // Position (level 4 - after Dashboard)
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('openai_key_settings', $this->option_name);
    }
    
    /**
     * Encrypt API key using WordPress salts
     */
    private function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY);
        $iv = substr(hash('sha256', AUTH_SALT . SECURE_AUTH_SALT), 0, 16);
        
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($encrypted);
    }
    
    /**
     * Decrypt API key
     */
    private function decrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY);
        $iv = substr(hash('sha256', AUTH_SALT . SECURE_AUTH_SALT), 0, 16);
        
        $decrypted = openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, 0, $iv);
        return $decrypted;
    }
    
    /**
     * Save API key securely
     */
    public function save_api_key() {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], $this->nonce_action)) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }
        
        $api_key = isset($_POST['openai_api_key']) ? sanitize_text_field($_POST['openai_api_key']) : '';
        
        if (!empty($api_key)) {
            // Encrypt and save
            $encrypted_key = $this->encrypt($api_key);
            update_option($this->option_name, $encrypted_key, false);
            
            $redirect = add_query_arg(array(
                'page' => 'openai-api-key',
                'updated' => 'true'
            ), admin_url('admin.php'));
        } else {
            // Delete if empty
            delete_option($this->option_name);
            $redirect = add_query_arg(array(
                'page' => 'openai-api-key',
                'deleted' => 'true'
            ), admin_url('admin.php'));
        }
        
        wp_redirect($redirect);
        exit;
    }
    
    /**
     * AJAX handler to get masked API key
     */
    public function ajax_get_masked_key() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'display_openai_key_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access'));
        }
        
        // Get masked API key
        $masked_key = self::get_masked_api_key();
        
        if ($masked_key === false) {
            wp_send_json_error(array('message' => 'No API key configured. Please set one first.'));
        }
        
        wp_send_json_success(array('masked_key' => $masked_key));
    }
    
    /**
     * Settings page HTML
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current encrypted key (just to check if exists)
        $has_key = !empty(get_option($this->option_name));
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (isset($_GET['updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>API key saved successfully!</strong></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>API key deleted successfully!</strong></p>
                </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>OpenAI API Key Configuration</h2>
                <p>Store your OpenAI API key securely. The key is encrypted using WordPress security salts and stored in the database.</p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="save_openai_key">
                    <?php wp_nonce_field($this->nonce_action); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="openai_api_key">OpenAI API Key</label>
                            </th>
                            <td>
                                <input 
                                    type="text" 
                                    id="openai_api_key" 
                                    name="openai_api_key" 
                                    class="regular-text"
                                    placeholder="sk-proj-..." 
                                    autocomplete="off"
                                />
                                <p class="description">
                                    <?php if ($has_key): ?>
                                        <span style="color: green;">✓ API key is currently set</span><br>
                                    <?php endif; ?>
                                    Enter your OpenAI API key (starts with "sk-"). Leave empty to delete.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save API Key'); ?>
                </form>
                
                <hr>
                
                <h3>Display Masked API Key</h3>
                <p>View your masked API key securely. The middle 10 characters are hidden with asterisks.</p>
                
                <?php if (!$has_key): ?>
                    <div class="notice notice-warning inline">
                        <p><strong>No API key configured!</strong> Please save an API key above first.</p>
                    </div>
                <?php endif; ?>
                
                <div style="margin: 20px 0;">
                    <button id="display-key-btn" class="button button-primary" <?php echo !$has_key ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span>
                        Display Masked API Key
                    </button>
                </div>
                
                <div id="key-display-area" style="display: none; margin-top: 20px;">
                    <div style="background: #f0f0f1; padding: 20px; border-radius: 5px; border-left: 4px solid #2271b1;">
                        <h4 style="margin-top: 0;">Masked API Key:</h4>
                        <div style="font-family: monospace; font-size: 16px; padding: 10px; background: white; border-radius: 3px; word-break: break-all;">
                            <code id="masked-key-output"></code>
                        </div>
                        <p style="margin-bottom: 0; margin-top: 10px; font-size: 13px; color: #646970;">
                            <span class="dashicons dashicons-info" style="font-size: 16px; vertical-align: middle;"></span>
                            The middle 10 characters are hidden with asterisks (*) for security.
                        </p>
                    </div>
                </div>
                
                <div id="key-error" style="display: none; margin-top: 20px;">
                    <div class="notice notice-error inline">
                        <p id="error-message"></p>
                    </div>
                </div>
                
                <hr>
                
                <h3>For Developers</h3>
                <p>Other plugins can retrieve the API key using this function:</p>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px;"><code>$api_key = Secure_OpenAI_Key_Manager::get_api_key();

if (!empty($api_key)) {
    // Use the API key
} else {
    // No API key configured
}</code></pre>
                
                <p><strong>Security Notes:</strong></p>
                <ul>
                    <li>The API key is encrypted using AES-256-CBC encryption</li>
                    <li>Encryption uses WordPress AUTH and SECURE_AUTH salts as keys</li>
                    <li>The key is never stored in plain text</li>
                    <li>Only administrators can view or modify this setting</li>
                    <li>All requests are protected with WordPress nonces</li>
                </ul>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#display-key-btn').on('click', function() {
                var button = $(this);
                var keyDisplay = $('#key-display-area');
                var errorDisplay = $('#key-error');
                
                // Reset displays
                keyDisplay.hide();
                errorDisplay.hide();
                
                // Disable button and show loading
                button.prop('disabled', true).text('Loading...');
                
                // Make AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_masked_openai_key',
                        nonce: '<?php echo wp_create_nonce('display_openai_key_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#masked-key-output').text(response.data.masked_key);
                            keyDisplay.slideDown();
                        } else {
                            $('#error-message').text(response.data.message);
                            errorDisplay.slideDown();
                        }
                    },
                    error: function() {
                        $('#error-message').text('An error occurred while fetching the API key.');
                        errorDisplay.slideDown();
                    },
                    complete: function() {
                        // Re-enable button
                        button.prop('disabled', false).html('<span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span> Display Masked API Key');
                    }
                });
            });
        });
        </script>
        
        <style>
        #display-key-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        </style>
        <?php
    }
    
    /**
     * PUBLIC API: Get decrypted API key
     * This is the function other plugins should use
     * 
     * @return string|false The API key or false if not set
     */
    public static function get_api_key() {
        // First check if defined in wp-config.php (highest priority)
        if (defined('OPENAI_API_KEY')) {
            return OPENAI_API_KEY;
        }
        
        // Then check environment variable
        $env_key = getenv('OPENAI_API_KEY');
        if (!empty($env_key)) {
            return $env_key;
        }
        
        // Finally check encrypted database option
        $instance = self::get_instance();
        $encrypted = get_option($instance->option_name);
        
        if (empty($encrypted)) {
            return false;
        }
        
        return $instance->decrypt($encrypted);
    }
    
    /**
     * PUBLIC API: Check if API key is configured
     * 
     * @return bool
     */
    public static function has_api_key() {
        $key = self::get_api_key();
        return !empty($key);
    }
    
    /**
     * PUBLIC API: Get masked API key (for display purposes)
     * Hides middle 10 characters with asterisks
     * 
     * @return string|false The masked API key or false if not set
     */
    public static function get_masked_api_key() {
        $api_key = self::get_api_key();
        
        if (empty($api_key)) {
            return false;
        }
        
        $length = strlen($api_key);
        
        // If key is too short to mask properly, just return asterisks
        if ($length <= 10) {
            return str_repeat('*', $length);
        }
        
        // Show first few chars, hide middle 10, show last few chars
        $visible_start = max(3, ($length - 10) / 2);
        $visible_end = max(3, ($length - 10) / 2);
        
        $start = substr($api_key, 0, $visible_start);
        $end = substr($api_key, -$visible_end);
        
        return $start . str_repeat('*', 10) . $end;
    }
}
