<?php
/**
 * Plugin Name: ✅ 20.1 UDEMY CAPSTONE 07 TEST RAG SEARCH ASSISTANT
 * Plugin URI: https://mydigitalagent.co.uk
 * Description: AI-powered search assistant using Full Text Search and Vector Search APIs with OpenAI
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://mydigitalagent.co.uk
 * License: GPL v2 or later
 * Text Domain: rag-search-assistant
 */

if (!defined('ABSPATH')) {
    exit;
}

class RAG_Search_Assistant {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_rag_search_query', array($this, 'handle_search_query'));
    }
    
    /**
     * Get the REST API base URL
     */
    private function get_api_base_url() {
        // Use rest_url() which automatically gets the correct URL
        return rest_url('posts-rag/v1/');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            '20.1 CAPSTONE RAG SEARCH',
            '20.1 SEARCH',
            'edit_posts',
            'rag-search-assistant',
            array($this, 'render_admin_page'),
            'dashicons-search',
            4.8
        );
    }
    
    public function render_admin_page() {
        ?>
        <style>
        .rag-search-wrap {
            max-width: 1200px;
            margin: 20px 0;
        }
        .rag-search-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .rag-search-input-section {
            margin-bottom: 30px;
        }
        .rag-search-input-section label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 16px;
        }
        .rag-query-input {
            width: 70%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        .rag-query-input:focus {
            outline: none;
            border-color: #0073aa;
        }
        #rag-search-btn {
            padding: 12px 30px;
            font-size: 16px;
        }
        .rag-loading {
            text-align: center;
            padding: 20px;
            font-size: 16px;
            color: #666;
        }
        .rag-answer-box {
            background: #f0f6fc;
            border-left: 4px solid #0073aa;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        .rag-answer-box h2 {
            margin-top: 0;
            color: #0073aa;
        }
        .rag-answer {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
        }
        .rag-search-results h3 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .rag-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .rag-result-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            transition: box-shadow 0.3s ease;
        }
        .rag-result-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .rag-result-card h4 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #0073aa;
            font-size: 18px;
        }
        .rag-result-meta {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.8;
        }
        .rag-result-excerpt {
            font-size: 14px;
            line-height: 1.6;
            color: #444;
        }
        .rag-context-section {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .rag-context-section h2 {
            margin-top: 0;
            color: #333;
        }
        .rag-context {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
        }
        .rag-context pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            font-family: "Courier New", monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .rag-metadata {
            background: #fff9e6;
            border: 1px solid #f0e68c;
            border-radius: 6px;
            padding: 20px;
        }
        .rag-metadata h3 {
            margin-top: 0;
            color: #856404;
        }
        .rag-meta-info p {
            margin: 8px 0;
            font-size: 14px;
        }
        .rag-meta-info strong {
            color: #333;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        </style>
        
        <div class="wrap rag-search-wrap">
            <h1>RAG Search Assistant</h1>
            
            <div class="rag-search-container">
                <div class="rag-search-input-section">
                    <label for="rag-query">Ask a question:</label>
                    <input type="text" id="rag-query" class="rag-query-input" value="What foam products do you have">
                    <button id="rag-search-btn" class="button button-primary">Search</button>
                </div>
                
                <div id="rag-loading" class="rag-loading" style="display: none;">
                    <span class="spinner is-active"></span> Searching...
                </div>
                
                <div id="rag-results" class="rag-results"></div>
                
                <div id="rag-context-section" class="rag-context-section" style="display: none;">
                    <h2>Retrieved Context</h2>
                    <div id="rag-context" class="rag-context"></div>
                </div>
                
                <div id="rag-metadata" class="rag-metadata" style="display: none;">
                    <h3>Search Metadata</h3>
                    <div id="rag-metadata-content"></div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('RAG Search initialized');
            
            $('#rag-search-btn').on('click', function() {
                console.log('Button clicked');
                performSearch();
            });
            
            $('#rag-query').on('keypress', function(e) {
                if (e.which === 13) {
                    performSearch();
                }
            });
            
            function performSearch() {
                const query = $('#rag-query').val().trim();
                console.log('Performing search for:', query);
                
                if (!query) {
                    alert('Please enter a search query');
                    return;
                }
                
                $('#rag-loading').show();
                $('#rag-results').empty();
                $('#rag-context-section').hide();
                $('#rag-metadata').hide();
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'rag_search_query',
                        nonce: '<?php echo wp_create_nonce('rag_search_nonce'); ?>',
                        query: query,
                        limit: 5
                    },
                    success: function(response) {
                        console.log('✅ AJAX Response:', response);
                        
                        // Log debug info if available
                        if (response.data && response.data.debug) {
                            console.log('🔍 Debug Info:', response.data.debug);
                        }
                        
                        $('#rag-loading').hide();
                        
                        if (response.success) {
                            console.log('✅ Search successful!');
                            displayResults(response.data);
                        } else {
                            console.error('❌ Search failed:', response.data);
                            $('#rag-results').html('<div class="error"><p>' + (response.data ? response.data.message : 'Unknown error') + '</p></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', error);
                        console.error('Response:', xhr.responseText);
                        $('#rag-loading').hide();
                        $('#rag-results').html('<div class="error"><p>An error occurred: ' + error + '</p></div>');
                    }
                });
            }
            
            function displayResults(data) {
                console.log('Displaying results:', data);
                
                let html = '<div class="rag-answer-box">';
                html += '<h2>Answer</h2>';
                html += '<div class="rag-answer">' + escapeHtml(data.answer) + '</div>';
                html += '</div>';
                
                html += '<div class="rag-search-results">';
                html += '<h3>Full Text Search Results</h3>';
                
                if (data.fts_results && data.fts_results.length > 0) {
                    html += '<div class="rag-results-grid">';
                    data.fts_results.forEach(function(result) {
                        html += '<div class="rag-result-card">';
                        html += '<h4>' + escapeHtml(result.post_title) + '</h4>';
                        html += '<p class="rag-result-meta">';
                        html += '<strong>Relevance Score:</strong> ' + result.relevance_score.toFixed(2) + '<br>';
                        html += '<strong>Categories:</strong> ' + escapeHtml(result.categories) + '<br>';
                        if (result.tags) {
                            html += '<strong>Tags:</strong> ' + escapeHtml(result.tags) + '<br>';
                        }
                        html += '</p>';
                        html += '<p class="rag-result-excerpt">' + escapeHtml(result.excerpt) + '</p>';
                        html += '</div>';
                    });
                    html += '</div>';
                } else {
                    html += '<p>No full text search results found.</p>';
                }
                
                html += '<h3>Vector Search Results</h3>';
                
                if (data.vector_results && data.vector_results.length > 0) {
                    html += '<div class="rag-results-grid">';
                    data.vector_results.forEach(function(result) {
                        html += '<div class="rag-result-card">';
                        html += '<h4>' + escapeHtml(result.post_title) + '</h4>';
                        html += '<p class="rag-result-meta">';
                        html += '<strong>Similarity Score:</strong> ' + result.similarity_score.toFixed(4) + '<br>';
                        html += '<strong>Categories:</strong> ' + escapeHtml(result.categories) + '<br>';
                        if (result.tags) {
                            html += '<strong>Tags:</strong> ' + escapeHtml(result.tags) + '<br>';
                        }
                        html += '</p>';
                        html += '<p class="rag-result-excerpt">' + escapeHtml(result.excerpt) + '</p>';
                        html += '</div>';
                    });
                    html += '</div>';
                } else {
                    html += '<p>No vector search results found.</p>';
                }
                
                html += '</div>';
                
                $('#rag-results').html(html);
                
                $('#rag-context').html('<pre>' + escapeHtml(data.context) + '</pre>');
                $('#rag-context-section').show();
                
                let metaHtml = '<div class="rag-meta-info">';
                metaHtml += '<p><strong>Query:</strong> ' + escapeHtml(data.query) + '</p>';
                metaHtml += '<p><strong>FTS Post IDs:</strong> [' + data.fts_ids.join(', ') + ']</p>';
                metaHtml += '<p><strong>Vector Post IDs:</strong> [' + data.vector_ids.join(', ') + ']</p>';
                metaHtml += '<p><strong>Total Unique Posts:</strong> ' + new Set([...data.fts_ids, ...data.vector_ids]).size + '</p>';
                metaHtml += '</div>';
                
                $('#rag-metadata-content').html(metaHtml);
                $('#rag-metadata').show();
            }
            
            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
        </script>
        <?php
    }
    
    public function handle_search_query() {
        // Verify nonce
        check_ajax_referer('rag_search_nonce', 'nonce');
        
        // Debug info array to send to console
        $debug_info = array();
        
        // Validate POST data exists
        if (!isset($_POST['query'])) {
            wp_send_json_error(array(
                'message' => 'Query parameter is missing',
                'debug' => 'POST query parameter not set'
            ));
            return;
        }
        
        $query = sanitize_text_field($_POST['query']);
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
        
        $debug_info['query'] = $query;
        $debug_info['limit'] = $limit;
        
        if (empty($query)) {
            wp_send_json_error(array(
                'message' => 'Query cannot be empty',
                'debug' => $debug_info
            ));
            return;
        }
        
        // Fetch from both APIs
        $fts_results = $this->fetch_fulltext_search($query, $limit, $debug_info);
        $vector_results = $this->fetch_vector_search($query, $limit, $debug_info);
        
        // Validate API responses
        if (!$fts_results || !isset($fts_results['results']) || !is_array($fts_results['results'])) {
            wp_send_json_error(array(
                'message' => 'Failed to fetch full text search results. API may be unavailable.',
                'debug' => $debug_info
            ));
            return;
        }
        
        if (!$vector_results || !isset($vector_results['results']) || !is_array($vector_results['results'])) {
            wp_send_json_error(array(
                'message' => 'Failed to fetch vector search results. API may be unavailable.',
                'debug' => $debug_info
            ));
            return;
        }
        
        // Extract post IDs with validation
        $fts_ids = array();
        foreach ($fts_results['results'] as $item) {
            if (isset($item['post_id'])) {
                $fts_ids[] = $item['post_id'];
            }
        }
        
        $vector_ids = array();
        foreach ($vector_results['results'] as $item) {
            if (isset($item['post_id'])) {
                $vector_ids[] = $item['post_id'];
            }
        }
        
        // Build context from all found posts
        $context = $this->build_context($fts_results['results'], $vector_results['results']);
        
        // Generate answer using OpenAI API
        $answer = $this->generate_answer($query, $context);
        
        wp_send_json_success(array(
            'query' => $query,
            'fts_ids' => $fts_ids,
            'vector_ids' => $vector_ids,
            'context' => $context,
            'answer' => $answer,
            'fts_results' => $fts_results['results'],
            'vector_results' => $vector_results['results'],
            'debug' => $debug_info
        ));
    }
    
    private function fetch_fulltext_search($query, $limit, &$debug_info) {
        $url = $this->get_api_base_url() . 'search?query=' . urlencode($query) . '&limit=' . $limit;
        
        $debug_info['fts_url'] = $url;
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            $debug_info['fts_error'] = $response->get_error_message();
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $debug_info['fts_status_code'] = $status_code;
        $debug_info['fts_response_length'] = strlen($body);
        
        if ($status_code !== 200) {
            $debug_info['fts_error'] = 'Non-200 status code: ' . $status_code;
            $debug_info['fts_response_body'] = substr($body, 0, 500);
            return false;
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $debug_info['fts_json_error'] = json_last_error_msg();
            $debug_info['fts_response_preview'] = substr($body, 0, 200);
            return false;
        }
        
        $debug_info['fts_success'] = true;
        $debug_info['fts_results_count'] = isset($decoded['results']) ? count($decoded['results']) : 0;
        
        return $decoded;
    }
    
    private function fetch_vector_search($query, $limit, &$debug_info) {
        $url = $this->get_api_base_url() . 'vector-search?query=' . urlencode($query) . '&limit=' . $limit;
        
        $debug_info['vector_url'] = $url;
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            $debug_info['vector_error'] = $response->get_error_message();
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $debug_info['vector_status_code'] = $status_code;
        $debug_info['vector_response_length'] = strlen($body);
        
        if ($status_code !== 200) {
            $debug_info['vector_error'] = 'Non-200 status code: ' . $status_code;
            $debug_info['vector_response_body'] = substr($body, 0, 500);
            return false;
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $debug_info['vector_json_error'] = json_last_error_msg();
            $debug_info['vector_response_preview'] = substr($body, 0, 200);
            return false;
        }
        
        $debug_info['vector_success'] = true;
        $debug_info['vector_results_count'] = isset($decoded['results']) ? count($decoded['results']) : 0;
        
        return $decoded;
    }
    
    private function build_context($fts_results, $vector_results) {
        $context_parts = array();
        $seen_ids = array();
        
        // Prioritize FTS results
        foreach ($fts_results as $result) {
            if (!in_array($result['post_id'], $seen_ids)) {
                $context_parts[] = "Title: {$result['post_title']}\n" .
                                   "Categories: {$result['categories']}\n" .
                                   "Tags: {$result['tags']}\n" .
                                   "Content: {$result['excerpt']}\n";
                $seen_ids[] = $result['post_id'];
            }
        }
        
        // Add vector results
        foreach ($vector_results as $result) {
            if (!in_array($result['post_id'], $seen_ids)) {
                $context_parts[] = "Title: {$result['post_title']}\n" .
                                   "Categories: {$result['categories']}\n" .
                                   "Tags: {$result['tags']}\n" .
                                   "Content: {$result['excerpt']}\n";
                $seen_ids[] = $result['post_id'];
            }
        }
        
        return implode("\n---\n\n", $context_parts);
    }
    
    private function generate_answer($query, $context) {
        // Check if context is empty
        if (empty(trim($context))) {
            return "My RAG does not have the answer.";
        }
        
        // Use the OpenAI key already stored in the database
        $api_key = get_option('posts_rag_openai_key', '');
        
        if (empty($api_key)) {
            return "Please configure your OpenAI API key in the 20 CAPSTONE plugin settings.";
        }
        
        // Call OpenAI API
        $api_response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => "You are a helpful assistant that answers questions based on the provided context. If the context doesn't contain enough information to answer the question, respond with 'My RAG does not have the answer.'"
                    ),
                    array(
                        'role' => 'user',
                        'content' => "Context:\n{$context}\n\nQuestion: {$query}\n\nPlease provide a helpful answer based on the context above."
                    )
                ),
                'max_tokens' => 500,
                'temperature' => 0.7
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($api_response)) {
            return "Error generating answer: " . $api_response->get_error_message();
        }
        
        $response_body = json_decode(wp_remote_retrieve_body($api_response), true);
        
        if (isset($response_body['choices'][0]['message']['content'])) {
            return $response_body['choices'][0]['message']['content'];
        }
        
        if (isset($response_body['error'])) {
            return "API Error: " . $response_body['error']['message'];
        }
        
        return "My RAG does not have the answer.";
    }
}

// Initialize the plugin
new RAG_Search_Assistant();
