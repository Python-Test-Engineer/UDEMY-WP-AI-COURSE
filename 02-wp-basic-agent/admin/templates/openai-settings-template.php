<?php
// Get the saved API key from WordPress options
$api_key = get_option('wp_basic_agent_api_key', '');

// Check if API key should be visible (PHP-only toggle state)
$show_key = get_transient('wp_basic_agent_show_key_' . get_current_user_id());

// Check for success message
$message = get_transient('wp_basic_agent_message');
if ($message) {
    delete_transient('wp_basic_agent_message');
}
?>

<div class="wrap">
    <h1 class="title"><?php _e('Basic Agent', 'wp-basic-agent'); ?></h1>

    <div class="dashboard-container">

        <!-- *****  OPENAI SETTINGS TEMPLATE CONTENT ***** -->

        <div class="dashboard-card">
            <h2><?php _e('API Configuration', 'wp-basic-agent'); ?></h2>
            
            <?php
            // Display success message if exists
            if ($message): ?>
                <div class="notice notice-success inline">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wp_basic_agent_api_key"><?php _e('OpenAI API Key', 'wp-basic-agent'); ?></label>
                    </th>
                    <td>
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <!-- Form for API Key Input and Save -->
                            <form method="post" action="" style="flex: 1; display: flex; gap: 10px;">
                                <?php wp_nonce_field('wp_basic_agent_save_key_nonce'); ?>
                                
                                <!-- API Key Input Field -->
                                <input 
                                    type="password" 
                                    id="wp_basic_agent_api_key" 
                                    name="wp_basic_agent_api_key" 
                                    value="<?php echo esc_attr($api_key); ?>" 
                                    class="regular-text" 
                                    placeholder="sk-..."
                                    required
                                />
                                
                                <input type="submit" name="save_basic_agent_api_key" class="button button-primary" value="<?php _e('Save API Key', 'wp-basic-agent'); ?>" />
                                
                                <!-- Show/Hide Toggle Button -->
                                <button 
                                    type="button" 
                                    id="toggle_api_key_visibility" 
                                    class="button"
                                    style="padding: 8px 12px; height: 36px; min-width: 60px; background: #f0f0f1; border: 1px solid #8c8f94; color: #2c3338;"
                                    title="<?php _e('Show API Key', 'wp-basic-agent'); ?>"
                                >
                                    <?php _e('Show', 'wp-basic-agent'); ?>
                                </button>
                            </form>
                        </div>
                        <p class="description">
                            <?php if (!empty($api_key)): ?>
                                <span style="color: green;">✓ <?php _e('API key is configured and ready to use.', 'wp-basic-agent'); ?></span>
                            <?php else: ?>
                                <span style="color: #d63638;">⚠ <?php _e('Please enter and save your OpenAI API key to use the plugin.', 'wp-basic-agent'); ?></span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- *******************  USER QUERY TESTING INTERFACE WITH HTMX *******************  -->
        
        <div class="dashboard-card">
            <h2><?php _e('Test OpenAI API', 'wp-basic-agent'); ?></h2>
            <form id="openai-form">
                <input type="hidden" name="action" value="openai_proxy">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('openai_key_nonce'); ?>">
                
                <div class="openai-test-interface">
                    <input 
                        type="text" 
                        name="prompt" 
                        id="prompt" 
                        placeholder="<?php _e('Enter your prompt...', 'wp-basic-agent'); ?>" 
                        required
                    >
                    <button type="submit"><?php _e('Send Request', 'wp-basic-agent'); ?></button>
                    <span id="loading" class="htmx-indicator" style="display:none;">⏳ Loading...</span>
                </div>
            </form>
            <div id="result"></div>
        </div>
    </div>
</div>
