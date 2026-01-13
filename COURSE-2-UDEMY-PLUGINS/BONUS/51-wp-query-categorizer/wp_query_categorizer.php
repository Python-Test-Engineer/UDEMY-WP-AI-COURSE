<?php
/**
 * Plugin Name: ✅ 51 UDEMY QUERY CATEGROIZER
 * Plugin URI: https://example.com
 * Description: Uses OpenAI GPT-4o-mini to analyze WordPress categories and find the best match for user queries
 * Version: 4.9
 * Author: Your Name
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class QueryCategorizerAI {
    
    private $option_name = 'query_categorizer_openai_key';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_categorize_query', array($this, 'ajax_categorize_query'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            '51 Query Categorizer',
            '51 QUERY ANALYZER',
            'manage_options',
            'query-categorizer',
            array($this, 'admin_page'),
            'dashicons-category',
           4.99       );
    }
    
    public function register_settings() {
        register_setting('query_categorizer_settings', $this->option_name);
    }
    
    public function admin_page() {
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        ?>
        <div class="wrap">
            <h1>Query Categorizer AI</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('query_categorizer_settings');
                do_settings_sections('query_categorizer_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">OpenAI API Key</th>
                        <td>
                            <input type="text" 
                                   name="<?php echo $this->option_name; ?>" 
                                   value="<?php echo esc_attr(get_option($this->option_name)); ?>" 
                                   class="regular-text" />
                            <p class="description">Enter your OpenAI API key</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save API Key'); ?>
            </form>
            
            <hr>
            
            <h2>Available Categories</h2>
            <?php if (empty($categories)): ?>
                <p><strong>No categories found.</strong> Please create some categories first in Posts > Categories.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Slug</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><strong><?php echo esc_html($cat->name); ?></strong></td>
                                <td><?php echo esc_html($cat->description ?: '(No description)'); ?></td>
                                <td><?php echo esc_html($cat->slug); ?></td>
                                <td><?php echo esc_html($cat->count); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <hr>
            
            <h2>Categorize Query</h2>
            <div id="categorizer-form">
                <label for="user-query"><strong>Enter Query:</strong></label><br>
                <textarea id="user-query" rows="4" cols="50" class="large-text" value="I am interested in foam pillows" placeholder="I am interested in foam pillows"></textarea><br><br>
                
                <button type="button" id="categorize-btn" class="button button-primary" <?php echo empty($categories) ? 'disabled' : ''; ?>>
                    Categorize Query
                </button>
                
                <?php if (empty($categories)): ?>
                    <p class="description">Create categories first to use this feature.</p>
                <?php endif; ?>
                
                <div id="loading" style="display:none; margin-top:10px;">
                    <span class="spinner is-active" style="float:none;"></span> Analyzing with GPT-4o-mini...
                </div>
                
                <div id="result" style="margin-top:20px;"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#categorize-btn').on('click', function() {
                var query = $('#user-query').val().trim();
                
                if (!query) {
                    alert('Please enter a query');
                    return;
                }
                
                $('#loading').show();
                $('#result').html('');
                $(this).prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'categorize_query',
                        query: query,
                        nonce: '<?php echo wp_create_nonce('categorize_query_nonce'); ?>'
                    },
                    success: function(response) {
                        $('#loading').hide();
                        $('#categorize-btn').prop('disabled', false);
                        
                        if (response.success) {
                            var data = response.data;
                            var resultHtml = '<div style="background:#fff; border:1px solid #ccc; padding:15px; border-radius:4px;">';
                            resultHtml += '<h3>Result:</h3>';
                            resultHtml += '<pre style="background:#f5f5f5; padding:10px; overflow:auto; white-space: pre-wrap;">' + 
                                         JSON.stringify(data, null, 2) + '</pre>';
                            resultHtml += '</div>';
                            $('#result').html(resultHtml);
                        } else {
                            $('#result').html('<div class="error"><p>' + response.data + '</p></div>');
                        }
                    },
                    error: function() {
                        $('#loading').hide();
                        $('#categorize-btn').prop('disabled', false);
                        $('#result').html('<div class="error"><p>An error occurred. Please try again.</p></div>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function ajax_categorize_query() {
        check_ajax_referer('categorize_query_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $query = sanitize_text_field($_POST['query']);
        
        $api_key = get_option($this->option_name);
        if (empty($api_key)) {
            wp_send_json_error('OpenAI API key not configured');
            return;
        }
        
        // Get all WordPress categories
        $categories = get_categories(array(
            'hide_empty' => false
        ));
        
        if (empty($categories)) {
            wp_send_json_error('No categories found in WordPress');
            return;
        }
        
        // Call OpenAI API
        $result = $this->categorize_with_openai($query, $categories, $api_key);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }
    
    private function categorize_with_openai($query, $categories, $api_key) {
        // Build category information for the AI
        $categories_info = array();
        foreach ($categories as $cat) {
            $categories_info[] = array(
                'name' => $cat->name,
                'description' => $cat->description ?: 'No description provided',
                'slug' => $cat->slug
            );
        }
        
        $categories_json = json_encode($categories_info, JSON_PRETTY_PRINT);
        
        $prompt = "You are analyzing a user query to find the most appropriate WordPress category that would likely contain the answer.\n\n";
        $prompt .= "User Query: \"$query\"\n\n";
        $prompt .= "Available WordPress Categories (with descriptions):\n";
        $prompt .= $categories_json . "\n\n";
        $prompt .= "Analyze the query and all category names and descriptions. Determine which category is most likely to contain content that would answer this query.\n\n";
        $prompt .= "Respond with ONLY a JSON object in this exact format:\n";
        $prompt .= "{\n";
        $prompt .= '  "query": "the original user query",'."\n";
        $prompt .= '  "category_name": "the most appropriate category name",'."\n";
        $prompt .= '  "category_description": "explanation of why this category is best suited to contain the answer to the query"'."\n";
        $prompt .= "}";
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are an expert at analyzing user queries and matching them to the most relevant content categories. Always respond with valid JSON only, no additional text.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'temperature' => 0.3
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('openai_error', $body['error']['message']);
        }
        
        if (!isset($body['choices'][0]['message']['content'])) {
            return new WP_Error('openai_error', 'Invalid response from OpenAI');
        }
        
        $content = trim($body['choices'][0]['message']['content']);
        
        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        
        $result = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Failed to parse OpenAI response: ' . json_last_error_msg());
        }
        
        // Validate that the selected category exists
        $category_exists = false;
        foreach ($categories as $cat) {
            if ($cat->name === $result['category_name']) {
                $category_exists = true;
                break;
            }
        }
        
        if (!$category_exists) {
            return new WP_Error('validation_error', 'AI selected a category that does not exist');
        }
        
        return $result;
    }
}

// Initialize the plugin
new QueryCategorizerAI();
