<?php
/**
 * Plugin Name: ✅ 10 METAPHONE SEARCH
 * Description: Converts post titles to metaphone text for phonetic searching
 * Version: 1.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class for metaphone-based searching
 */
class Metaphone_Search_Plugin {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        
        // Set the custom table name using proper prefix
        $this->table_name = $wpdb->prefix . 'rag_metaphone';
        
        // Hook into WordPress admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register AJAX handlers
        add_action('wp_ajax_metaphone_search', array($this, 'handle_search_ajax'));
        add_action('wp_ajax_nopriv_metaphone_search', array($this, 'handle_search_ajax'));
        
        // Hook for plugin activation to create table
        register_activation_hook(__FILE__, array($this, 'create_metaphone_table'));
    }
    
    /**
     * Create the metaphone table on plugin activation
     */
    public function create_metaphone_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // SQL to create the metaphone table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_title text NOT NULL,
            metaphone_text text NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY metaphone_text (metaphone_text(191))
        ) $charset_collate;";
        
        // Execute the SQL using dbDelta for safe table creation
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_menu_page(
            '🔍 1Metaphone Search',               // Page title
            '10 METAPHONE SEARCH',            // Menu title
            'manage_options',                  // Capability required
            'metaphone-search',                // Menu slug
            array($this, 'render_admin_page'), // Callback function
            'dashicons-search',                // Icon
            4.5                             // Position
        );
    }
    
    /**
     * Render the admin page interface
     */
    public function render_admin_page() {
        global $wpdb;
        
        // Get count of records in the metaphone table
        $record_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Get total WordPress posts
        $total_posts = wp_count_posts('post')->publish;
        
        ?>
        <div class="wrap">
            <h1>🔍 Metaphone Search Plugin</h1>
            
            <!-- Database Status Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Database Status</h2>
                <p><strong>Metaphone Table:</strong> <?php echo esc_html($this->table_name); ?></p>
                <p><strong>Records in Metaphone Table:</strong> <?php echo esc_html($record_count); ?></p>
                <p><strong>Total Published Posts:</strong> <?php echo esc_html($total_posts); ?></p>
                
                <?php if ($record_count < $total_posts): ?>
                    <p style="color: #d63638;">⚠️ Some posts haven't been indexed yet.</p>
                <?php endif; ?>
            </div>
            
            <!-- Index Posts Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Index Posts</h2>
                <p>Click the button below to index all published post titles with metaphone text:</p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('metaphone_index_posts', 'metaphone_index_nonce'); ?>
                    <input type="submit" name="index_posts" class="button button-primary" value="Index All Posts">
                    <input type="submit" name="clear_index" class="button" value="Clear Index" 
                           onclick="return confirm('Are you sure you want to clear all indexed data?');">
                </form>
                
                <?php
                // Handle index posts action
                if (isset($_POST['index_posts']) && check_admin_referer('metaphone_index_posts', 'metaphone_index_nonce')) {
                    $indexed = $this->index_all_posts();
                    echo '<div class="notice notice-success" style="margin-top: 15px;"><p>Successfully indexed ' . $indexed . ' posts!</p></div>';
                }
                
                // Handle clear index action
                if (isset($_POST['clear_index']) && check_admin_referer('metaphone_index_posts', 'metaphone_index_nonce')) {
                    $this->clear_index();
                    echo '<div class="notice notice-success" style="margin-top: 15px;"><p>Index cleared successfully!</p></div>';
                }
                ?>
            </div>
            
            <!-- Phonetic Search Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Phonetic Search</h2>
                <p>Search for posts using phonetic matching. This will find posts that sound similar even if spelled differently:</p>
                
                <div style="margin: 20px 0;">
                    <input type="text" 
                           id="search-query" 
                           placeholder="e.g., filosofy (finds 'philosophy')" 
                           style="width: 100%; max-width: 500px; padding: 10px; font-size: 16px; border: 2px solid #ddd; border-radius: 4px;">
                </div>
                
                <button id="search-btn" class="button button-primary" style="padding: 10px 30px; height: auto;">
                    <span class="dashicons dashicons-search" style="vertical-align: middle;"></span> Search
                </button>
                <button id="clear-search" class="button" style="padding: 10px 30px; height: auto;">
                    <span class="dashicons dashicons-dismiss" style="vertical-align: middle;"></span> Clear
                </button>
                
                <!-- Loading indicator -->
                <div id="search-loading" style="display: none; margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
                    <span class="spinner is-active" style="float: none; margin: 0 10px 0 0;"></span>
                    <strong>Searching...</strong>
                </div>
                
                <!-- Search results -->
                <div id="search-results" style="display: none; margin-top: 20px;">
                    <h3>Search Results</h3>
                    <div id="results-container"></div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Handle search button click
            $('#search-btn').on('click', function() {
                var query = $('#search-query').val().trim();
                
                if (!query) {
                    alert('Please enter a search query');
                    return;
                }
                
                // Show loading indicator
                $('#search-loading').show();
                $('#search-results').hide();
                
                // Make AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'metaphone_search',
                        query: query,
                        nonce: '<?php echo wp_create_nonce('metaphone_search_nonce'); ?>'
                    },
                    success: function(response) {
                        $('#search-loading').hide();
                        
                        if (response.success) {
                            var data = response.data;
                            var html = '';
                            
                            // Display query metaphone
                            html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #dee2e6;">';
                            html += '<p><strong>Query:</strong> ' + data.query + '</p>';
                            html += '<p><strong>Metaphone:</strong> ' + data.query_metaphone + '</p>';
                            html += '<p><strong>Matches Found:</strong> ' + data.results.length + '</p>';
                            html += '</div>';
                            
                            // Display results
                            if (data.results.length > 0) {
                                html += '<div style="background: white; border: 1px solid #ddd; border-radius: 4px;">';
                                
                                $.each(data.results, function(index, result) {
                                    html += '<div style="padding: 15px; border-bottom: 1px solid #eee;">';
                                    html += '<h4 style="margin: 0 0 10px 0;">' + result.post_title + '</h4>';
                                    html += '<p style="margin: 0; color: #666; font-size: 13px;">';
                                    html += '<strong>Post ID:</strong> ' + result.post_id + ' | ';
                                    html += '<strong>Metaphone:</strong> ' + result.metaphone_text;
                                    html += '</p>';
                                    html += '</div>';
                                });
                                
                                html += '</div>';
                            } else {
                                html += '<p style="color: #666; font-style: italic;">No matching posts found.</p>';
                            }
                            
                            $('#results-container').html(html);
                            $('#search-results').show();
                        } else {
                            alert('Error: ' + (response.data.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#search-loading').hide();
                        alert('AJAX Error: ' + error);
                    }
                });
            });
            
            // Handle clear button
            $('#clear-search').on('click', function() {
                $('#search-query').val('');
                $('#search-results').hide();
            });
            
            // Allow Enter key to submit search
            $('#search-query').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#search-btn').click();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Index all published posts with metaphone text
     */
    private function index_all_posts() {
        global $wpdb;
        
        // Get all published posts
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ));
        
        // Clear existing records first
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        $indexed_count = 0;
        
        // Process each post
        foreach ($posts as $post) {
            $post_title = $post->post_title;
            
            // Generate metaphone text from the title
            $metaphone_text = $this->generate_metaphone($post_title);
            
            // Insert into database
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'post_id' => $post->ID,
                    'post_title' => $post_title,
                    'metaphone_text' => $metaphone_text
                ),
                array('%d', '%s', '%s')
            );
            
            if ($result) {
                $indexed_count++;
            }
        }
        
        return $indexed_count;
    }
    
    /**
     * Clear all records from the metaphone index
     */
    private function clear_index() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }
    
    /**
     * Generate metaphone text from a string
     * Processes each word separately and concatenates results
     */
    private function generate_metaphone($text) {
        // Remove special characters and convert to lowercase
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Split into words
        $words = explode(' ', $text);
        
        // Generate metaphone for each word
        $metaphone_parts = array();
        foreach ($words as $word) {
            if (!empty($word)) {
                // Use PHP's metaphone function
                $metaphone = metaphone($word);
                if (!empty($metaphone)) {
                    $metaphone_parts[] = $metaphone;
                }
            }
        }
        
        // Join with spaces
        return implode(' ', $metaphone_parts);
    }
    
    /**
     * Handle AJAX search request
     */
    public function handle_search_ajax() {
        // Verify nonce for security
        check_ajax_referer('metaphone_search_nonce', 'nonce');
        
        // Get the search query
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (empty($query)) {
            wp_send_json_error(array('message' => 'Search query is required'));
        }
        
        // Perform the search
        $results = $this->search_by_metaphone($query);
        
        wp_send_json_success($results);
    }
    
    /**
     * Search for posts by converting query to metaphone and matching
     */
    private function search_by_metaphone($query) {
        global $wpdb;
        
        // Generate metaphone text for the query
        $query_metaphone = $this->generate_metaphone($query);
        
        // Split query metaphone into individual words
        $query_words = explode(' ', $query_metaphone);
        
        // Build WHERE clause to match any of the metaphone words
        $where_clauses = array();
        foreach ($query_words as $word) {
            if (!empty($word)) {
                $where_clauses[] = $wpdb->prepare(
                    "metaphone_text LIKE %s",
                    '%' . $wpdb->esc_like($word) . '%'
                );
            }
        }
        
        // If no valid words, return empty results
        if (empty($where_clauses)) {
            return array(
                'query' => $query,
                'query_metaphone' => $query_metaphone,
                'results' => array()
            );
        }
        
        // Combine WHERE clauses with OR
        $where_sql = implode(' OR ', $where_clauses);
        
        // Execute the query
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_sql} ORDER BY post_id DESC";
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        // Return structured response
        return array(
            'query' => $query,
            'query_metaphone' => $query_metaphone,
            'results' => $results ? $results : array()
        );
    }
}

// Initialize the plugin
new Metaphone_Search_Plugin();
