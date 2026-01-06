<?php
/**
 * Plugin Name: ✅ 06 UDEMY CAPSTONE
 * Description: Manages a custom table for RAG processing of WordPress posts
 * Version: 1.0
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
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
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
        ?>
        <div class="wrap">
            <h1>Posts RAG Manager</h1>
            
            <?php
            // Handle API key save
            if (isset($_POST['save_api_key'])) {
                check_admin_referer('save_api_key_action');
                $api_key = sanitize_text_field($_POST['openai_api_key']);
                update_option($this->option_name, $api_key);
                echo '<div class="notice notice-success"><p>API Key saved successfully.</p></div>';
            }
            
            // Handle sync posts
            if (isset($_POST['sync_posts'])) {
                check_admin_referer('sync_posts_action');
                $synced = $this->sync_posts_to_table();
                echo '<div class="notice notice-success"><p>Synced ' . $synced . ' posts to RAG table.</p></div>';
            }
            
            // Handle generate embeddings
            if (isset($_POST['generate_embeddings'])) {
                check_admin_referer('generate_embeddings_action');
                $result = $this->generate_embeddings();
                if ($result['success']) {
                    echo '<div class="notice notice-success"><p>' . $result['message'] . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>' . $result['message'] . '</p></div>';
                }
            }
            ?>
            
            <div class="card">
                <h2>OpenAI API Configuration</h2>
                <form method="post">
                    <?php wp_nonce_field('save_api_key_action'); ?>
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
                        <button type="submit" name="save_api_key" class="button button-primary">Save API Key</button>
                    </p>
                </form>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Sync Posts to RAG Table</h2>
                <p>Click the button below to sync all published posts to the RAG table.</p>
                <form method="post">
                    <?php wp_nonce_field('sync_posts_action'); ?>
                    <button type="submit" name="sync_posts" class="button button-primary">Sync Posts</button>
                </form>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Generate Embeddings</h2>
                <p>Generate OpenAI embeddings for post titles. This will process all posts that don't have embeddings yet.</p>
                <form method="post">
                    <?php wp_nonce_field('generate_embeddings_action'); ?>
                    <button type="submit" name="generate_embeddings" class="button button-primary">Generate Embeddings</button>
                </form>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Table Statistics</h2>
                <?php $this->display_stats(); ?>
            </div>
        </div>
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
        
        echo '<p><strong>Total Posts in RAG Table:</strong> ' . $total_rows . '</p>';
        echo '<p><strong>Posts with Embeddings:</strong> ' . $embedded_rows . '</p>';
    }
}

// Initialize the plugin
new Posts_RAG_Manager();