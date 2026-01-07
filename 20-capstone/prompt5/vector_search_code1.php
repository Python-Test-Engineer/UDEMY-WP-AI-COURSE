<?php
/**
 * Plugin Name: ✅ 06 UDEMY CAPSTONE
 * Description: Manages a custom table for RAG processing of WordPress posts with Full-Text Search and Vector Search
 * Version: 1.4
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Posts_RAG_Manager {
    
    private $table_name;
    private $option_name = 'posts_rag_openai_key';
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'posts_rag';
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // AJAX handlers
        add_action('wp_ajax_save_openai_key', array($this, 'ajax_save_openai_key'));
        add_action('wp_ajax_sync_posts', array($this, 'ajax_sync_posts'));
        add_action('wp_ajax_generate_embeddings', array($this, 'ajax_generate_embeddings'));
        add_action('wp_ajax_create_fulltext_index', array($this, 'ajax_create_fulltext_index'));
        
        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Full-text search endpoint
        register_rest_route('posts-rag/v1', '/search', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_search_posts'),
            'permission_callback' => '__return_true',
            'args' => array(
                'query' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Search query string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 3,
                    'description' => 'Number of results to return',
                    'sanitize_callback' => 'absint'
                )
            )
        ));
        
        // Vector search endpoint
        register_rest_route('posts-rag/v1', '/vector-search', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_vector_search'),
            'permission_callback' => '__return_true',
            'args' => array(
                'query' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Search query string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 3,
                    'description' => 'Number of results to return (1-20)',
                    'sanitize_callback' => 'absint'
                )
            )
        ));
    }
    
    /**
     * REST API endpoint: Search posts
     */
    public function rest_search_posts($request) {
        global $wpdb;
        
        $query = $request->get_param('query');
        $limit = $request->get_param('limit');
        
        if (empty($query)) {
            return new WP_Error('invalid_query', 'Query parameter is required', array('status' => 400));
        }
        
        // Limit between 1 and 20
        $limit = max(1, min(20, $limit));
        
        // Check if fulltext index exists
        $index_exists = $this->check_fulltext_index();
        
        if (!$index_exists) {
            return new WP_Error('no_index', 'Full-text index not created. Please create it from the admin panel.', array('status' => 500));
        }
        
        // Perform full-text search
        $results = $this->fulltext_search($query, $limit);
        
        if (empty($results)) {
            return array(
                'success' => true,
                'query' => $query,
                'method' => 'fulltext_search',
                'results' => array(),
                'count' => 0
            );
        }
        
        // Format results
        $formatted_results = array();
        foreach ($results as $row) {
            $formatted_results[] = array(
                'post_id' => intval($row->post_id),
                'post_title' => $row->post_title,
                'relevance_score' => floatval($row->relevance_score),
                'categories' => $row->categories,
                'tags' => $row->tags,
                'excerpt' => wp_trim_words($row->post_content, 30)
            );
        }
        
        return array(
            'success' => true,
            'query' => $query,
            'method' => 'fulltext_search',
            'results' => $formatted_results,
            'count' => count($formatted_results)
        );
    }
    
    /**
     * REST API endpoint: Vector search using cosine similarity
     */
    public function rest_vector_search($request) {
        $query = $request->get_param('query');
        $limit = $request->get_param('limit');
        
        if (empty($query)) {
            return new WP_Error('invalid_query', 'Query parameter is required', array('status' => 400));
        }
        
        // Limit between 1 and 20
        $limit = max(1, min(20, $limit));
        
        // Perform vector search
        $result = $this->vector_search($query, $limit);
        
        if (!$result['success']) {
            return new WP_Error('search_failed', $result['message'], array('status' => 500));
        }
        
        return array(
            'success' => true,
            'query' => $query,
            'method' => 'vector_search',
            'results' => $result['results'],
            'count' => count($result['results'])
        );
    }
    
    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosine_similarity($vec1, $vec2) {
        if (count($vec1) !== count($vec2)) {
            return 0;
        }
        
        $dot_product = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        for ($i = 0; $i < count($vec1); $i++) {
            $dot_product += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] * $vec1[$i];
            $magnitude2 += $vec2[$i] * $vec2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dot_product / ($magnitude1 * $magnitude2);
    }
    
    /**
     * Perform vector search using cosine similarity
     */
    private function vector_search($query, $limit = 3) {
        global $wpdb;
        
        $api_key = get_option($this->option_name);
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'OpenAI API key is not configured.'
            );
        }
        
        // Generate embedding for the query
        $query_embedding = $this->get_openai_embedding($query, $api_key);
        
        if ($query_embedding === false) {
            return array(
                'success' => false,
                'message' => 'Failed to generate embedding for query.'
            );
        }
        
        // Get all posts with embeddings
        $posts = $wpdb->get_results(
            "SELECT id, post_id, post_title, post_content, categories, tags, embedding 
            FROM {$this->table_name} 
            WHERE embedding IS NOT NULL"
        );
        
        if (empty($posts)) {
            return array(
                'success' => false,
                'message' => 'No posts with embeddings found. Please generate embeddings first.'
            );
        }
        
        // Calculate cosine similarity for each post
        $similarities = array();
        
        foreach ($posts as $post) {
            $post_embedding = json_decode($post->embedding, true);
            
            if (is_array($post_embedding)) {
                $similarity = $this->cosine_similarity($query_embedding, $post_embedding);
                
                $similarities[] = array(
                    'post_id' => intval($post->post_id),
                    'post_title' => $post->post_title,
                    'similarity_score' => $similarity,
                    'categories' => $post->categories,
                    'tags' => $post->tags,
                    'excerpt' => wp_trim_words($post->post_content, 30)
                );
            }
        }
        
        // Sort by similarity score (highest first)
        usort($similarities, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });
        
        // Return top N results
        $top_results = array_slice($similarities, 0, $limit);
        
        return array(
            'success' => true,
            'results' => $top_results
        );
    }
    
    /**
     * Perform full-text search on the RAG table
     */
    private function fulltext_search($query, $limit = 3) {
        global $wpdb;
        
        // Escape the query for use in MATCH AGAINST
        $search_query = $wpdb->esc_like($query);
        
        $sql = $wpdb->prepare(
            "SELECT 
                post_id,
                post_title,
                post_content,
                categories,
                tags,
                MATCH(post_title, post_content, categories, tags, custom_meta_data) 
                AGAINST (%s IN NATURAL LANGUAGE MODE) as relevance_score
            FROM {$this->table_name}
            WHERE MATCH(post_title, post_content, categories, tags, custom_meta_data) 
                AGAINST (%s IN NATURAL LANGUAGE MODE)
            ORDER BY relevance_score DESC
            LIMIT %d",
            $query,
            $query,
            $limit
        );
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Check if full-text index exists
     */
    private function check_fulltext_index() {
        global $wpdb;
        
        $index_check = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW INDEX FROM {$this->table_name} WHERE Key_name = %s",
                'fulltext_search_idx'
            )
        );
        
        return !empty($index_check);
    }
    
    /**
     * Create full-text index on the table
     */
    public function create_fulltext_index() {
        global $wpdb;
        
        // Check if index already exists
        if ($this->check_fulltext_index()) {
            return array(
                'success' => true,
                'message' => 'Full-text index already exists.'
            );
        }
        
        // Create the full-text index
        $sql = "ALTER TABLE {$this->table_name} 
                ADD FULLTEXT INDEX fulltext_search_idx (post_title, post_content, categories, tags, custom_meta_data)";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'Failed to create full-text index: ' . $wpdb->last_error
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Full-text index created successfully.'
        );
    }
    
    /**
     * AJAX: Create Full-Text Index
     */
    public function ajax_create_fulltext_index() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $result = $this->create_fulltext_index();
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_posts-rag-manager') {
            return;
        }
        
        wp_enqueue_script('jquery');
    }
    
    /**
     * AJAX: Save OpenAI API Key
     */
    public function ajax_save_openai_key() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        update_option($this->option_name, $api_key);
        
        wp_send_json_success('API Key saved successfully.');
    }
    
    /**
     * AJAX: Sync Posts
     */
    public function ajax_sync_posts() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $synced = $this->sync_posts_to_table();
        wp_send_json_success("Synced {$synced} posts to RAG table.");
    }
    
    /**
     * AJAX: Generate Embeddings
     */
    public function ajax_generate_embeddings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $result = $this->generate_embeddings();
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Create the custom table on plugin activation
     */
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_title text NOT NULL,
            post_content longtext NOT NULL,
            categories text,
            tags text,
            custom_meta_data longtext,
            embedding longtext,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_embedded datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY post_id (post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add admin menu item at level 4
     */
    public function add_admin_menu() {
        add_menu_page(
            'Posts RAG Manager',
            '06 RAG',
            'manage_options',
            'posts-rag-manager',
            array($this, 'admin_page'),
            'dashicons-admin-tools',
            4
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }
        
        $index_exists = $this->check_fulltext_index();
        ?>
        <div class="wrap">
            <h1>Posts RAG Manager</h1>
            
            <div id="rag-message" style="display:none;" class="notice">
                <p></p>
            </div>
            
            <div class="card">
                <h2>OpenAI API Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="openai_api_key">OpenAI API Key</label>
                        </th>
                        <td>
                            <input type="password" 
                                   name="openai_api_key" 
                                   id="openai_api_key" 
                                   value="<?php echo esc_attr(get_option($this->option_name)); ?>" 
                                   class="regular-text" 
                                   placeholder="sk-...">
                            <p class="description">Enter your OpenAI API key to enable embeddings generation.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" id="save-api-key-btn" class="button button-primary">Save API Key</button>
                </p>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Sync Posts to RAG Table</h2>
                <p>Click the button below to sync all published posts to the RAG table.</p>
                <button type="button" id="sync-posts-btn" class="button button-primary">Sync Posts</button>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Full-Text Search Index</h2>
                <p>
                    Status: <strong><?php echo $index_exists ? '✅ Created' : '❌ Not Created'; ?></strong>
                </p>
                <p>Create a full-text index to enable natural language search on post titles, content, categories, tags, and custom metadata.</p>
                <button type="button" id="create-fulltext-btn" class="button button-primary" 
                        <?php echo $index_exists ? 'disabled' : ''; ?>>
                    <?php echo $index_exists ? 'Index Already Created' : 'Create Full-Text Index'; ?>
                </button>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Generate Embeddings</h2>
                <p>Generate OpenAI embeddings for post titles. This will process all posts that don't have embeddings yet.</p>
                <button type="button" id="generate-embeddings-btn" class="button button-primary">Generate Embeddings</button>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>REST API Endpoints</h2>
                
                <h3>Full-Text Search</h3>
                <p>Search using MySQL full-text index (keyword matching):</p>
                <code><?php echo esc_url(rest_url('posts-rag/v1/search')); ?>?query=YOUR_SEARCH_QUERY&limit=3</code>
                
                <h3 style="margin-top: 15px;">Vector Search</h3>
                <p>Search using semantic similarity (requires embeddings):</p>
                <code><?php echo esc_url(rest_url('posts-rag/v1/vector-search')); ?>?query=YOUR_SEARCH_QUERY&limit=3</code>
                
                <p class="description" style="margin-top: 10px;">
                    <strong>Parameters:</strong> <strong>query</strong> (required), <strong>limit</strong> (optional, default: 3, max: 20)
                </p>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Table Statistics</h2>
                <div id="stats-container">
                    <?php $this->display_stats(); ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            
            function showMessage(message, type) {
                var $msg = $('#rag-message');
                $msg.removeClass('notice-success notice-error notice-info')
                    .addClass('notice-' + type)
                    .find('p').text(message);
                $msg.show();
                
                setTimeout(function() {
                    $msg.fadeOut();
                }, 5000);
            }
            
            // Save API Key
            $('#save-api-key-btn').on('click', function() {
                var $btn = $(this);
                var apiKey = $('#openai_api_key').val();
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_openai_key',
                        api_key: apiKey
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data, 'success');
                        } else {
                            showMessage(response.data, 'error');
                        }
                    },
                    error: function() {
                        showMessage('An error occurred while saving the API key.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
            
            // Sync Posts
            $('#sync-posts-btn').on('click', function() {
                var $btn = $(this);
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'sync_posts'
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showMessage(response.data, 'error');
                        }
                    },
                    error: function() {
                        showMessage('An error occurred while syncing posts.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
            
            // Create Full-Text Index
            $('#create-fulltext-btn').on('click', function() {
                var $btn = $(this);
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'create_fulltext_index'
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showMessage(response.data, 'error');
                        }
                    },
                    error: function() {
                        showMessage('An error occurred while creating the index.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
            
            // Generate Embeddings
            $('#generate-embeddings-btn').on('click', function() {
                var $btn = $(this);
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'generate_embeddings'
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showMessage(response.data, 'error');
                        }
                    },
                    error: function() {
                        showMessage('An error occurred while generating embeddings.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Sync posts to the RAG table
     */
    public function sync_posts_to_table() {
        global $wpdb;
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        
        $posts = get_posts($args);
        $synced_count = 0;
        
        foreach ($posts as $post) {
            // Get categories
            $categories = get_the_category($post->ID);
            $cat_names = array();
            foreach ($categories as $cat) {
                $cat_names[] = $cat->name;
            }
            $categories_str = implode(', ', $cat_names);
            
            // Get tags
            $tags = get_the_tags($post->ID);
            $tag_names = array();
            if ($tags) {
                foreach ($tags as $tag) {
                    $tag_names[] = $tag->name;
                }
            }
            $tags_str = implode(', ', $tag_names);
            
            // Get all custom field values as CSV
            $custom_values = array();
            
            // First try ACF fields if available
            if (function_exists('get_field_objects')) {
                $acf_fields = get_field_objects($post->ID);
                if ($acf_fields) {
                    foreach ($acf_fields as $field) {
                        $value = $field['value'];
                        if (is_array($value)) {
                            $value = implode('|', $value);
                        }
                        if (!empty($value)) {
                            $custom_values[] = $value;
                        }
                    }
                }
            }
            
            // Also get regular custom fields (non-ACF)
            $all_meta = get_post_meta($post->ID);
            foreach ($all_meta as $key => $values) {
                // Skip WordPress internal meta keys and ACF internal keys
                if (substr($key, 0, 1) !== '_') {
                    foreach ($values as $value) {
                        $value = maybe_unserialize($value);
                        if (is_array($value)) {
                            $value = implode('|', $value);
                        }
                        if (!empty($value) && is_scalar($value)) {
                            $custom_values[] = $value;
                        }
                    }
                }
            }
            
            // Remove duplicates and create CSV
            $custom_values = array_unique($custom_values);
            $custom_meta_csv = implode(', ', $custom_values);
            
            // Insert or update
            $wpdb->replace(
                $this->table_name,
                array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_content' => $post->post_content,
                    'categories' => $categories_str,
                    'tags' => $tags_str,
                    'custom_meta_data' => $custom_meta_csv
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );
            
            $synced_count++;
        }
        
        return $synced_count;
    }
    
    /**
     * Generate embeddings for posts using OpenAI API
     */
    public function generate_embeddings() {
        global $wpdb;
        
        $api_key = get_option($this->option_name);
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'OpenAI API key is not configured. Please add your API key first.'
            );
        }
        
        // Get posts without embeddings
        $posts = $wpdb->get_results(
            "SELECT id, post_id, post_title FROM {$this->table_name} WHERE last_embedded IS NULL"
        );
        
        if (empty($posts)) {
            return array(
                'success' => true,
                'message' => 'All posts already have embeddings.'
            );
        }
        
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        
        foreach ($posts as $post) {
            $embedding = $this->get_openai_embedding($post->post_title, $api_key);
            
            if ($embedding !== false) {
                // Store embedding as JSON
                $embedding_json = json_encode($embedding);
                
                $updated = $wpdb->update(
                    $this->table_name,
                    array(
                        'embedding' => $embedding_json,
                        'last_embedded' => current_time('mysql')
                    ),
                    array('id' => $post->id),
                    array('%s', '%s'),
                    array('%d')
                );
                
                if ($updated !== false) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Failed to update database for post ID {$post->post_id}";
                }
            } else {
                $error_count++;
                $errors[] = "Failed to generate embedding for post ID {$post->post_id}";
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }
        
        $message = "Generated embeddings for {$success_count} posts.";
        if ($error_count > 0) {
            $message .= " {$error_count} errors occurred.";
        }
        
        return array(
            'success' => $success_count > 0,
            'message' => $message,
            'errors' => $errors
        );
    }
    
    /**
     * Call OpenAI API to get embedding for text
     */
    private function get_openai_embedding($text, $api_key) {
        $url = 'https://api.openai.com/v1/embeddings';
        
        $data = array(
            'input' => $text,
            'model' => 'text-embedding-3-small'
        );
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode($data),
            'timeout' => 30
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('OpenAI API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['data'][0]['embedding'])) {
            return $result['data'][0]['embedding'];
        }
        
        if (isset($result['error'])) {
            error_log('OpenAI API Error: ' . $result['error']['message']);
        }
        
        return false;
    }
    
    /**
     * Display table statistics
     */
    private function display_stats() {
        global $wpdb;
        
        $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $embedded_rows = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE last_embedded IS NOT NULL");
        $index_exists = $this->check_fulltext_index();
        
        echo '<p><strong>Total Posts in RAG Table:</strong> ' . $total_rows . '</p>';
        echo '<p><strong>Posts with Embeddings:</strong> ' . $embedded_rows . '</p>';
        echo '<p><strong>Full-Text Index:</strong> ' . ($index_exists ? '✅ Active' : '❌ Not Created') . '</p>';
    }
}

// Initialize the plugin
$posts_rag_manager = new Posts_RAG_Manager();

// Activation hook must be outside the class
register_activation_hook(__FILE__, array($posts_rag_manager, 'activate'));
    