<?php
/**
 * Plugin Name: ✅ 09 UDEMY UDEMY EXTRACT
 * Description: Uses OpenAI to match user queries with relevant WordPress categories and tags, then retrieves associated title/content from a custom RAG posts table.
 * Version: 1.0
 * Author: Craig West
 */


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
class RAG_Category_Tag_Matcher {
    
    private $openai_api_key;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        
        // Set the custom table name using proper prefix
        $this->table_name = $wpdb->prefix . 'posts_fts';
        
        // Hook into WordPress admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register AJAX handlers for frontend and backend
        add_action('wp_ajax_rag_match_query', array($this, 'handle_ajax_request'));
        add_action('wp_ajax_nopriv_rag_match_query', array($this, 'handle_ajax_request'));
        
        // Get OpenAI API key from WordPress options
        $this->openai_api_key = get_option('rag_openai_api_key', '');
    }
    
    /**
     * Add admin menu item at level 4
     */
    public function add_admin_menu() {
        add_menu_page(
            'RAG Extract',                    // Page title
            '09 UDEMY EXTRACT',      // Menu title
            'manage_options',                  // Capability required
            'rag-extract',                     // Menu slug
            array($this, 'render_admin_page'), // Callback function
            'dashicons-search',                // Icon
            4.4                            // Position (level 4)
        );
    }
    
    /**
     * Render the admin page interface
     */
    public function render_admin_page() {
        global $wpdb;
        
        // Get count of records in the custom table for display
        $record_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        ?>
        <div class="wrap">
            <h1>✅ RAG Category/Tag Matcher</h1>
            
            <!-- Settings Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>OpenAI Settings</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('rag_save_settings', 'rag_settings_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="openai_api_key">OpenAI API Key</label></th>
                            <td>
                                <input type="password" 
                                       id="openai_api_key" 
                                       name="openai_api_key" 
                                       value="<?php echo esc_attr($this->openai_api_key); ?>" 
                                       class="regular-text">
                                <p class="description">Enter your OpenAI API key to enable query matching</p>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" name="save_settings" class="button button-primary" value="Save Settings">
                </form>
            </div>
            
            <!-- Database Status Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Database Status</h2>
                <p><strong>Custom Table:</strong> <?php echo esc_html($this->table_name); ?></p>
                <p><strong>Total Records:</strong> <?php echo esc_html($record_count); ?></p>
                <?php if ($record_count == 0): ?>
                    <p style="color: #d63638;">⚠️ No records found in the RAG posts table. Please populate it before testing queries.</p>
                <?php endif; ?>
                
                <?php 
                // Check if output.json exists
                $output_file = plugin_dir_path(__FILE__) . 'output.json';
                if (file_exists($output_file)): 
                    $file_time = filemtime($output_file);
                    $file_size = filesize($output_file);
                ?>
                    <hr style="margin: 15px 0;">
                    <p><strong>Output File:</strong> output.json</p>
                    <p><strong>Last Updated:</strong> <?php echo date('Y-m-d H:i:s', $file_time); ?></p>
                    <p><strong>File Size:</strong> <?php echo number_format($file_size / 1024, 2); ?> KB</p>
                    <p><em>The latest query results are saved to output.json in the plugin folder.</em></p>
                <?php else: ?>
                    <hr style="margin: 15px 0;">
                    <p style="color: #666;"><em>No output.json file yet. Run a query to generate it.</em></p>
                <?php endif; ?>
            </div>
            
            <!-- Query Testing Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Test Query Matching</h2>
                <p>Enter a user query to see which categories and tags match, along with relevant post IDs and content:</p>
                
                <textarea id="user-query" 
                          rows="3" 
                          style="width: 100%; max-width: 600px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" 
                          placeholder="e.g., Tell me about kitchen utensils you stock, particularly cordless ones"></textarea>
                <br><br>
                <button id="test-query" class="button button-primary" style="margin-right: 10px;">
                    <span class="dashicons dashicons-search" style="vertical-align: middle;"></span> Match Query
                </button>
                <button id="clear-results" class="button">
                    <span class="dashicons dashicons-dismiss" style="vertical-align: middle;"></span> Clear Results
                </button>
                
                <!-- Loading indicator -->
                <div id="loading" style="display: none; margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
                    <span class="spinner is-active" style="float: none; margin: 0 10px 0 0;"></span>
                    <strong>Processing query with OpenAI...</strong> This may take a few seconds.
                </div>
                
                <!-- Results display -->
                <div id="results" style="margin-top: 20px; display: none;">
                    <h3>Results</h3>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6;">
                        <h4>Matched Taxonomies & Post IDs</h4>
                        <pre id="results-json" style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto; border: 1px solid #ddd; font-size: 13px; line-height: 1.6;"></pre>
                        
                        <h4 style="margin-top: 20px;">Context Preview</h4>
                        <div id="context-preview" style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto; font-size: 13px; line-height: 1.8; white-space: pre-wrap;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Handle test query button click
            $('#test-query').on('click', function() {
                var query = $('#user-query').val().trim();
                
                if (!query) {
                    alert('Please enter a query');
                    return;
                }
                
                // Show loading indicator
                $('#loading').show();
                $('#results').hide();
                
                // Make AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rag_match_query',
                        query: query,
                        nonce: '<?php echo wp_create_nonce('rag_match_query_nonce'); ?>'
                    },
                    success: function(response) {
                        $('#loading').hide();
                        
                        if (response.success) {
                            // Extract context for separate display
                            var data = response.data;
                            var context = data.context;
                            
                            // Create a copy without context for cleaner JSON display
                            var jsonDisplay = {
                                categories: data.categories,
                                tags: data.tags,
                                post_id: data.post_id
                            };
                            
                            // Display formatted JSON results (without context)
                            $('#results-json').text(JSON.stringify(jsonDisplay, null, 2));
                            
                            // Display context in separate area
                            var contextPreview = context;
                            if (contextPreview.length > 2000) {
                                contextPreview = contextPreview.substring(0, 2000) + '\n\n... (truncated for display, full context available in API response)';
                            }
                            $('#context-preview').text(contextPreview);
                            
                            $('#results').show();
                        } else {
                            alert('Error: ' + (response.data.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading').hide();
                        alert('AJAX Error: ' + error);
                    }
                });
            });
            
            // Handle clear results button
            $('#clear-results').on('click', function() {
                $('#user-query').val('');
                $('#results').hide();
            });
            
            // Allow Enter key to submit
            $('#user-query').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    $('#test-query').click();
                }
            });
        });
        </script>
        <?php
        
        // Handle settings save
        if (isset($_POST['save_settings']) && check_admin_referer('rag_save_settings', 'rag_settings_nonce')) {
            update_option('rag_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
            $this->openai_api_key = get_option('rag_openai_api_key');
        }
    }
    
    /**
     * Handle AJAX request for query matching
     */
    public function handle_ajax_request() {
        // Verify nonce for security
        check_ajax_referer('rag_match_query_nonce', 'nonce');
        
        // Get the query from the request
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (empty($query)) {
            wp_send_json_error(array('message' => 'Query is required'));
        }
        
        // Process the query and get matching categories/tags/content
        $result = $this->match_query_to_taxonomies($query);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Save the result to output.json file in the plugin directory
        $this->save_output_json($result);
        
        wp_send_json_success($result);
    }
    
    /**
     * Main function to match query to categories and tags using OpenAI
     */
    private function match_query_to_taxonomies($query) {
        // Check if API key is set
        if (empty($this->openai_api_key)) {
            return new WP_Error('no_api_key', 'OpenAI API key not configured. Please add your API key in the settings.');
        }
        
        // Get all available categories and tags from the custom table
        $categories = $this->get_all_categories_from_rag_table();
        $tags = $this->get_all_tags_from_rag_table();
        
        // Check if we have any data to work with
        if (empty($categories) && empty($tags)) {
            return new WP_Error('no_data', 'No categories or tags found in the RAG posts table. Please populate the table first.');
        }
        
        // Use OpenAI to determine which categories and tags are relevant
        $matched_taxonomies = $this->call_openai_for_matching($query, $categories, $tags);
        
        if (is_wp_error($matched_taxonomies)) {
            return $matched_taxonomies;
        }
        
        // Get post_ids that match the selected categories and tags
        // Note: This returns the 'post_id' column values from wp_posts_fts table
        $post_ids = $this->get_post_ids_from_matched_taxonomies(
            $matched_taxonomies['categories'],
            $matched_taxonomies['tags']
        );
        
        // Get concatenated content from matched posts using their post_ids
        $context = $this->get_context_from_post_ids($post_ids);
        
        // Return the final result with all required keys
        return array(
            'categories' => $matched_taxonomies['categories'],
            'tags' => $matched_taxonomies['tags'],
            'post_id' => $post_ids,
            'context' => $context
        );
    }
    
    /**
     * Get all unique categories from the wp_posts_rag table
     */
    private function get_all_categories_from_rag_table() {
        global $wpdb;
        
        // Get all non-empty categories from the custom table
        $results = $wpdb->get_col("
            SELECT DISTINCT categories 
            FROM {$this->table_name} 
            WHERE categories IS NOT NULL 
            AND categories != ''
        ");
        
        // Parse categories (they might be stored as comma-separated values)
        $all_categories = array();
        foreach ($results as $category_string) {
            // Split by comma and trim whitespace
            $cats = array_map('trim', explode(',', $category_string));
            $all_categories = array_merge($all_categories, $cats);
        }
        
        // Remove duplicates and empty values
        $all_categories = array_unique(array_filter($all_categories));
        
        // Return as indexed array
        return array_values($all_categories);
    }
    
    /**
     * Get all unique tags from the wp_posts_rag table
     */
    private function get_all_tags_from_rag_table() {
        global $wpdb;
        
        // Get all non-empty tags from the custom table
        $results = $wpdb->get_col("
            SELECT DISTINCT tags 
            FROM {$this->table_name} 
            WHERE tags IS NOT NULL 
            AND tags != ''
        ");
        
        // Parse tags (they might be stored as comma-separated values)
        $all_tags = array();
        foreach ($results as $tag_string) {
            // Split by comma and trim whitespace
            $tag_items = array_map('trim', explode(',', $tag_string));
            $all_tags = array_merge($all_tags, $tag_items);
        }
        
        // Remove duplicates and empty values
        $all_tags = array_unique(array_filter($all_tags));
        
        // Return as indexed array
        return array_values($all_tags);
    }
    
    /**
     * Call OpenAI API to determine which categories and tags match the query
     */
    private function call_openai_for_matching($query, $categories, $tags) {
        // Construct the system prompt for OpenAI
        $system_prompt = "You are a helpful assistant that analyzes user queries and determines which categories and tags from a content database would contain relevant information to answer that query. You must respond ONLY with valid JSON in the exact format specified, with no additional text, markdown formatting, or explanations.";
        
        // Construct the user prompt with available categories and tags
        $user_prompt = sprintf(
            "User Query: \"%s\"\n\nAvailable Categories:\n%s\n\nAvailable Tags:\n%s\n\nTask: Analyze the user's query and determine which categories and tags would contain content relevant to answering this query.\n\nResponse Format: Return ONLY a JSON object (no markdown code blocks, no explanations) with exactly two keys:\n- \"categories\": an array of relevant category names from the available categories\n- \"tags\": an array of relevant tag names from the available tags\n\nIf no categories or tags are relevant, return empty arrays. Be selective and only include categories/tags that would genuinely help answer the query.",
            $query,
            !empty($categories) ? implode(", ", $categories) : "None available",
            !empty($tags) ? implode(", ", $tags) : "None available"
        );
        
        // Make API call to OpenAI Chat Completions endpoint
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->openai_api_key
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',  // Using GPT-4o-mini for cost-effectiveness
                'messages' => array(
                    array('role' => 'system', 'content' => $system_prompt),
                    array('role' => 'user', 'content' => $user_prompt)
                ),
                'temperature' => 0.3,  // Lower temperature for more consistent results
                'max_tokens' => 500
            )),
            'timeout' => 30  // 30 second timeout
        ));
        
        // Check for errors in the HTTP request
        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Failed to connect to OpenAI API: ' . $response->get_error_message());
        }
        
        // Get the response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error('api_error', 'OpenAI API returned error code: ' . $response_code);
        }
        
        // Parse the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Check if the API returned an error message
        if (isset($data['error'])) {
            return new WP_Error('openai_error', 'OpenAI API Error: ' . $data['error']['message']);
        }
        
        // Extract the content from the response
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'Invalid response structure from OpenAI API');
        }
        
        $content = $data['choices'][0]['message']['content'];
        
        // Clean up the response - remove markdown code blocks if present
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $content = trim($content);
        
        // Parse the JSON response
        $parsed = json_decode($content, true);
        
        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Failed to parse OpenAI response as JSON: ' . json_last_error_msg() . ' | Response: ' . $content);
        }
        
        // Validate the response structure
        if (!isset($parsed['categories']) || !isset($parsed['tags'])) {
            return new WP_Error('invalid_structure', 'OpenAI response missing required keys (categories, tags)');
        }
        
        // Ensure arrays are returned
        return array(
            'categories' => is_array($parsed['categories']) ? $parsed['categories'] : array(),
            'tags' => is_array($parsed['tags']) ? $parsed['tags'] : array()
        );
    }
    
    /**
     * Get post_ids from the custom table that match the specified categories and tags
     * Returns the 'post_id' column values from wp_posts_rag where categories/tags match
     */
    private function get_post_ids_from_matched_taxonomies($category_names, $tag_names) {
        global $wpdb;
        
        // If no categories or tags matched, return empty array
        if (empty($category_names) && empty($tag_names)) {
            return array();
        }
        
        // Build the WHERE clause for SQL query
        $where_clauses = array();
        
        // Add category matching conditions
        if (!empty($category_names)) {
            foreach ($category_names as $category) {
                // Use LIKE to find the category in the comma-separated list
                $where_clauses[] = $wpdb->prepare(
                    "categories LIKE %s",
                    '%' . $wpdb->esc_like($category) . '%'
                );
            }
        }
        
        // Add tag matching conditions
        if (!empty($tag_names)) {
            foreach ($tag_names as $tag) {
                // Use LIKE to find the tag in the comma-separated list
                $where_clauses[] = $wpdb->prepare(
                    "tags LIKE %s",
                    '%' . $wpdb->esc_like($tag) . '%'
                );
            }
        }
        
        // Combine with OR since a post can match either categories OR tags
        $where_sql = implode(' OR ', $where_clauses);
        
        // Execute the query to get matching post_ids from the wp_posts_rag table
        $query = "SELECT DISTINCT post_id FROM {$this->table_name} WHERE {$where_sql}";
        $post_ids = $wpdb->get_col($query);
        
        // Convert to integers and return
        return array_map('intval', $post_ids);
    }
    
    /**
     * Get concatenated post titles and content for the specified post_ids
     * Note: post_ids refer to the 'post_id' column in wp_posts_rag
     */
    private function get_context_from_post_ids($post_ids) {
        global $wpdb;
        
        // If no post_ids, return empty message
        if (empty($post_ids)) {
            return "No matching content found for this query.";
        }
        
        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));
        
        // Query to get post titles and content using the 'post_id' column
        $query = $wpdb->prepare(
            "SELECT post_title, post_content 
             FROM {$this->table_name} 
             WHERE post_id IN ($placeholders)
             ORDER BY post_id",
            $post_ids
        );
        
        // Execute query
        $results = $wpdb->get_results($query);
        
        // If no results found
        if (empty($results)) {
            return "No content found for the matched post_ids.";
        }
        
        // Build concatenated context string
        $context_parts = array();
        
        foreach ($results as $row) {
            // Format each post as: Title followed by content
            $context_parts[] = sprintf(
                "Title: %s\n\nContent: %s\n\n---\n",
                $row->post_title,
                $row->post_content
            );
        }
        
        // Return the complete concatenated context
        return implode("\n", $context_parts);
    }
    
    /**
     * Save the result JSON to output.json file in the plugin directory
     */
    private function save_output_json($result) {
        // Get the plugin directory path
        $plugin_dir = plugin_dir_path(__FILE__);
        
        // Define the output file path
        $output_file = $plugin_dir . 'output.json';
        
        // Convert result to pretty-printed JSON
        $json_content = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Write to file
        $write_result = file_put_contents($output_file, $json_content);
        
        // Log any errors (optional - for debugging)
        if ($write_result === false) {
            error_log('RAG Plugin: Failed to write output.json file');
        }
        
        return $write_result !== false;
    }
}

// Initialize the plugin
new RAG_Category_Tag_Matcher();