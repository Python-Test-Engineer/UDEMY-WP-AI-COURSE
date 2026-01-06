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
            'Posts RAG Manager',           // Page title
            '06 RAG',                   // Menu title
            'manage_options',              // Capability
            'posts-rag-manager',           // Menu slug
            array($this, 'admin_page'),    // Callback function
           'dashicons-admin-tools',          // Icon
            4                              // Position (level 4)
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
            if (isset($_POST['sync_posts'])) {
                check_admin_referer('sync_posts_action');
                $synced = $this->sync_posts_to_table();
                echo '<div class="notice notice-success"><p>Synced ' . $synced . ' posts to RAG table.</p></div>';
            }
            ?>
            
            <div class="card">
                <h2>Sync Posts to RAG Table</h2>
                <p>Click the button below to sync all published posts to the RAG table.</p>
                <form method="post">
                    <?php wp_nonce_field('sync_posts_action'); ?>
                    <button type="submit" name="sync_posts" class="button button-primary">Sync Posts</button>
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
