<?php
/**
 * Sample Page: Display Masked OpenAI API Key
 * 
 * This is a demonstration page showing how to securely display a masked API key
 * with a button click. The middle 10 characters are hidden with asterisks.
 * 
 * This page can be accessed via the admin menu or used as a reference for other plugins.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// This class handles the sample display functionality
class Sample_Display_Key_Page {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_submenu_page'));
        add_action('wp_ajax_get_masked_openai_key', array($this, 'ajax_get_masked_key'));
    }
    
    /**
     * Add submenu page under OPENAI KEY menu
     */
    public function add_submenu_page() {
        add_submenu_page(
            'openai-api-key',                    // Parent slug
            'Display Key Sample',                // Page title
            'Display Key Sample',                // Menu title
            'manage_options',                    // Capability
            'openai-key-display-sample',         // Menu slug
            array($this, 'render_page')          // Callback
        );
    }
    
    /**
     * AJAX handler to get masked API key
     */
    public function ajax_get_masked_key() {
        if (function_exists('openai_key_debug_log')) {
            openai_key_debug_log('AJAX: ajax_get_masked_key called');
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'display_openai_key_nonce')) {
            if (function_exists('openai_key_debug_log')) {
                openai_key_debug_log('AJAX ERROR: Nonce verification failed');
            }
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            if (function_exists('openai_key_debug_log')) {
                openai_key_debug_log('AJAX ERROR: User lacks manage_options capability');
            }
            wp_send_json_error(array('message' => 'Unauthorized access'));
        }
        
        // Get masked API key
        if (function_exists('openai_key_debug_log')) {
            openai_key_debug_log('AJAX: Attempting to get masked API key');
        }
        $masked_key = Secure_OpenAI_Key_Manager::get_masked_api_key();
        
        if ($masked_key === false) {
            if (function_exists('openai_key_debug_log')) {
                openai_key_debug_log('AJAX ERROR: No API key available');
            }
            wp_send_json_error(array('message' => 'No API key configured. Please set one first.'));
        }
        
        if (function_exists('openai_key_debug_log')) {
            openai_key_debug_log('AJAX SUCCESS: Returning masked key (length: ' . strlen($masked_key) . ')');
        }
        wp_send_json_success(array('masked_key' => $masked_key));
    }
    
    /**
     * Render the sample page
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if key exists
        $has_key = Secure_OpenAI_Key_Manager::has_api_key();
        
        ?>
        <div class="wrap">
            <h1>Display Masked OpenAI API Key - Sample</h1>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Secure Key Display Demo</h2>
                <p>This page demonstrates how to securely display the OpenAI API key with the middle 10 characters masked.</p>
                
                <?php if (!$has_key): ?>
                    <div class="notice notice-warning inline">
                        <p><strong>No API key configured!</strong> Please <a href="<?php echo admin_url('admin.php?page=openai-api-key'); ?>">set an API key</a> first.</p>
                    </div>
                <?php endif; ?>
                
                <div style="margin: 20px 0;">
                    <button id="display-key-btn" class="button button-primary button-hero" <?php echo !$has_key ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span>
                        Display Masked API Key
                    </button>
                </div>
                
                <div id="key-display-area" style="display: none; margin-top: 20px;">
                    <div style="background: #f0f0f1; padding: 20px; border-radius: 5px; border-left: 4px solid #2271b1;">
                        <h3 style="margin-top: 0;">Masked API Key:</h3>
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
                
                <hr style="margin: 30px 0;">
                
                <h3>How to Use This in Your Plugin</h3>
                <p>You can retrieve the masked API key in your own plugins using:</p>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px;"><code>// Get the masked key (middle 10 chars hidden)
$masked_key = Secure_OpenAI_Key_Manager::get_masked_api_key();

if ($masked_key !== false) {
    echo 'Masked Key: ' . esc_html($masked_key);
} else {
    echo 'No API key configured.';
}

// Or use the convenience function
$masked_key = get_masked_openai_api_key();</code></pre>
                
                <h3>Available Functions</h3>
                <ul>
                    <li><code>Secure_OpenAI_Key_Manager::get_api_key()</code> - Get full API key (use carefully!)</li>
                    <li><code>Secure_OpenAI_Key_Manager::get_masked_api_key()</code> - Get masked API key for display</li>
                    <li><code>Secure_OpenAI_Key_Manager::has_api_key()</code> - Check if key is configured</li>
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
}

// Initialize the sample page
Sample_Display_Key_Page::get_instance();
