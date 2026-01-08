<?php
/**
 * Plugin Name: ✅ 20.9 UDEMY CAPSTONE 09 CHATGPT UI WITH MEMORY
 * Plugin URI: https://mydigitalagent.co.uk
 * Description: ChatGPT-style AI search assistant with RAG context, citations, and conversation memory for follow-up questions
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://mydigitalagent.co.uk
 * License: GPL v2 or later
 * Text Domain: rag-chatgpt-memory
 */

if (!defined('ABSPATH')) {
    exit;
}

class RAG_ChatGPT_Memory_Assistant {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_rag_chatgpt_memory_query', array($this, 'handle_search_query'));
    }
    
    private function get_api_base_url() {
        return rest_url('posts-rag/v1/');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            '20.9 CAPSTONE MEMORY',
            '20.9 MEMORY',
            'edit_posts',
            'rag-chatgpt-memory',
            array($this, 'render_admin_page'),
            'dashicons-format-chat',
            4.91
        );
    }
    
    public function render_admin_page() {
        ?>
        <style>
        * {
            box-sizing: border-box;
        }
        
        .chatgpt-wrap {
            max-width: 900px;
            margin: 20px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .chatgpt-container {
            background: #f7f7f8;
            min-height: 600px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .chatgpt-header {
            background: #ffffff;
            padding: 20px 30px;
            border-bottom: 1px solid #e5e5e5;
            border-radius: 12px 12px 0 0;
        }
        
        .chatgpt-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #2d333a;
        }
        
        .chatgpt-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #6b7280;
        }
        
        .chatgpt-messages {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            max-height: 70vh;
        }
        
        .chatgpt-message {
            margin-bottom: 30px;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .chatgpt-message.user {
            display: flex;
            justify-content: flex-end;
        }
        
        .chatgpt-message.assistant {
            display: flex;
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 80%;
            padding: 16px 20px;
            border-radius: 18px;
            line-height: 1.6;
            font-size: 15px;
        }
        
        .chatgpt-message.user .message-content {
            background: #10a37f;
            color: #ffffff;
        }
        
        .chatgpt-message.assistant .message-content {
            background: #ffffff;
            color: #2d333a;
            border: 1px solid #e5e5e5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .rag-section {
            background: #f0f9ff;
            border-left: 3px solid #0ea5e9;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .rag-section h4 {
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: 600;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .rag-content {
            color: #334155;
            line-height: 1.5;
            white-space: pre-wrap;
            font-size: 13px;
        }
        
        .citations-section {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .citations-section h4 {
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: 600;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .citation-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .citation-badge {
            background: #ffffff;
            border: 1px solid #fbbf24;
            color: #92400e;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .citation-badge:hover {
            background: #fef3c7;
            border-color: #f59e0b;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
        }
        
        .chatgpt-input-container {
            background: #ffffff;
            padding: 20px 30px;
            border-top: 1px solid #e5e5e5;
            border-radius: 0 0 12px 12px;
        }
        
        .chatgpt-input-wrapper {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .chatgpt-query-input {
            flex: 1;
            padding: 14px 18px;
            font-size: 15px;
            border: 2px solid #e5e5e5;
            border-radius: 24px;
            background: #f7f7f8;
            transition: all 0.2s;
        }
        
        .chatgpt-query-input:focus {
            outline: none;
            border-color: #10a37f;
            background: #ffffff;
        }
        
        .chatgpt-send-btn {
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 600;
            background: #10a37f;
            color: #ffffff;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chatgpt-send-btn:hover {
            background: #0d8c6d;
            transform: translateY(-1px);
        }
        
        .chatgpt-send-btn:active {
            transform: translateY(0);
        }
        
        .chatgpt-send-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
        }
        
        .chatgpt-clear-btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            background: #ef4444;
            color: #ffffff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chatgpt-clear-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        .chatgpt-loading {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6b7280;
            font-size: 14px;
            padding: 10px 0;
        }
        
        .typing-indicator {
            display: flex;
            gap: 4px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.7;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 16px;
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #374151;
        }
        
        .empty-state p {
            font-size: 14px;
        }
        </style>
        
        <div class="wrap chatgpt-wrap">
            <div class="chatgpt-container">
                <div class="chatgpt-header">
                    <h1>💬 ChatGPT-Style RAG Search with Memory</h1>
                    <p>🧠 This assistant remembers your conversation - try asking follow-up questions!</p>
                </div>
                
                <div class="chatgpt-messages" id="chatgpt-messages">
                    <div class="empty-state">
                        <h3>Welcome to RAG Search Assistant with Memory</h3>
                        <p>Ask me anything about your content. I'll remember our conversation so you can ask follow-up questions!</p>
                    </div>
                </div>
                
                <div class="chatgpt-input-container">
                    <div class="chatgpt-input-wrapper">
                        <input type="text" 
                               id="chatgpt-query" 
                               class="chatgpt-query-input" 
                               placeholder="Ask a question or follow-up..."
                               value="What foam products do you have">
                        <button id="chatgpt-send-btn" class="chatgpt-send-btn">Send</button>
                        <button id="chatgpt-clear-btn" class="chatgpt-clear-btn">Clear Chat</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('🚀 ChatGPT Memory UI Plugin Initialized');
            let isLoading = false;
            let conversationHistory = []; // Store conversation for follow-up questions
            
            $('#chatgpt-send-btn').on('click', function() {
                console.log('🖱️ Send button clicked');
                if (!isLoading) {
                    performSearch();
                }
            });
            
            $('#chatgpt-clear-btn').on('click', function() {
                console.log('🗑️ Clear chat button clicked');
                conversationHistory = [];
                $('#chatgpt-messages').html(`
                    <div class="empty-state">
                        <h3>Welcome to RAG Search Assistant with Memory</h3>
                        <p>Ask me anything about your content. I'll remember our conversation so you can ask follow-up questions!</p>
                    </div>
                `);
                console.log('✅ Conversation history cleared');
            });
            
            $('#chatgpt-query').on('keypress', function(e) {
                if (e.which === 13 && !isLoading) {
                    console.log('⌨️ Enter key pressed');
                    performSearch();
                }
            });
            
            function performSearch() {
                const query = $('#chatgpt-query').val().trim();
                console.log('🔍 Starting search with query:', query);
                
                if (!query) {
                    console.warn('⚠️ Empty query - aborting');
                    alert('Please enter a search query');
                    return;
                }
                
                $('.empty-state').remove();
                console.log('💬 Adding user message to chat');
                addMessage('user', query);
                $('#chatgpt-query').val('');
                
                isLoading = true;
                $('#chatgpt-send-btn').prop('disabled', true);
                console.log('⏳ Loading state activated');
                addLoadingIndicator();
                
                const ajaxData = {
                    action: 'rag_chatgpt_memory_query',
                    nonce: '<?php echo wp_create_nonce('rag_chatgpt_memory_nonce'); ?>',
                    query: query,
                    limit: 5,
                    conversation_history: JSON.stringify(conversationHistory)
                };
                console.log('📤 Sending AJAX request with data:', ajaxData);
                console.log('📜 Current conversation history:', conversationHistory);
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: ajaxData,
                    success: function(response) {
                        console.log('📥 AJAX SUCCESS - Full response:', JSON.stringify(response, null, 2));
                        
                        removeLoadingIndicator();
                        isLoading = false;
                        $('#chatgpt-send-btn').prop('disabled', false);
                        
                        if (response.success) {
                            console.log('✅ Response successful - processing data');
                            
                            // Add to conversation history
                            conversationHistory.push({
                                role: 'user',
                                content: query
                            });
                            conversationHistory.push({
                                role: 'assistant',
                                content: response.data.answer
                            });
                            console.log('💾 Updated conversation history:', conversationHistory);
                            
                            addAssistantMessage(response.data);
                        } else {
                            console.error('❌ Response failed:', response.data);
                            addMessage('assistant', 'Error: ' + (response.data ? response.data.message : 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX ERROR - Status:', status, 'Error:', error);
                        console.error('❌ Response Text:', xhr.responseText);
                        
                        removeLoadingIndicator();
                        isLoading = false;
                        $('#chatgpt-send-btn').prop('disabled', false);
                        addMessage('assistant', 'An error occurred: ' + error);
                    }
                });
            }
            
            function addMessage(role, content) {
                console.log('💬 addMessage() - role:', role, 'content:', content);
                const html = `
                    <div class="chatgpt-message ${role}">
                        <div class="message-content">${escapeHtml(content)}</div>
                    </div>
                `;
                $('#chatgpt-messages').append(html);
                scrollToBottom();
            }
            
            function addAssistantMessage(data) {
                console.log('🤖 ========== addAssistantMessage() START ==========');
                console.log('🤖 Full data:', JSON.stringify(data, null, 2));
                console.log('🤖 Citations:', data.citations);
                
                let answer = data.answer || '';
                let ragContext = data.rag_summary || '';
                let citations = data.citations || [];
                
                console.log('✅ Citations is array:', Array.isArray(citations), 'Length:', citations.length);
                
                let messageHtml = `
                    <div class="chatgpt-message assistant">
                        <div class="message-content">
                            ${escapeHtml(answer)}
                `;
                
                if (ragContext) {
                    console.log('📚 Adding RAG section');
                    messageHtml += `
                        <div class="rag-section">
                            <h4>📚 RAG Context Used</h4>
                            <div class="rag-content">${escapeHtml(ragContext)}</div>
                        </div>
                    `;
                }
                
                if (citations && citations.length > 0) {
                    console.log('✅ Adding citations section with', citations.length, 'citations');
                    messageHtml += `
                        <div class="citations-section">
                            <h4>📎 Citations</h4>
                            <div class="citation-list">
                    `;
                    
                    citations.forEach(function(citation, index) {
                        console.log(`📎 Citation ${index}:`, citation);
                        if (citation && typeof citation === 'object') {
                            // Citation with permalink and title
                            messageHtml += `<a href="${escapeHtml(citation.permalink)}" target="_blank" class="citation-badge" title="Post ID: ${citation.post_id}">${escapeHtml(citation.title)}</a>`;
                        } else {
                            // Just post ID
                            messageHtml += `<span class="citation-badge">Post ${citation}</span>`;
                        }
                    });
                    
                    messageHtml += `
                            </div>
                        </div>
                    `;
                    console.log('✅ Citations section complete');
                } else {
                    console.log('❌ No citations to display');
                }
                
                messageHtml += `
                        </div>
                    </div>
                `;
                
                $('#chatgpt-messages').append(messageHtml);
                console.log('✅ Message appended to DOM');
                scrollToBottom();
                console.log('🤖 ========== addAssistantMessage() END ==========');
            }
            
            function addLoadingIndicator() {
                const html = `
                    <div class="chatgpt-message assistant" id="loading-indicator">
                        <div class="message-content">
                            <div class="chatgpt-loading">
                                <div class="typing-indicator">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                                <span>Thinking...</span>
                            </div>
                        </div>
                    </div>
                `;
                $('#chatgpt-messages').append(html);
                scrollToBottom();
            }
            
            function removeLoadingIndicator() {
                $('#loading-indicator').remove();
            }
            
            function scrollToBottom() {
                const messages = $('#chatgpt-messages');
                messages.scrollTop(messages[0].scrollHeight);
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
        check_ajax_referer('rag_chatgpt_memory_nonce', 'nonce');
        
        $debug_info = array();
        
        if (!isset($_POST['query'])) {
            wp_send_json_error(array(
                'message' => 'Query parameter is missing',
                'debug' => 'POST query parameter not set'
            ));
            return;
        }
        
        $query = sanitize_text_field($_POST['query']);
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
        
        // Get conversation history from request
        $conversation_history = array();
        if (isset($_POST['conversation_history'])) {
            $history_json = stripslashes($_POST['conversation_history']);
            $conversation_history = json_decode($history_json, true);
            if (!is_array($conversation_history)) {
                $conversation_history = array();
            }
        }
        
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
        
        // Extract post IDs
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
        
        // Get all unique post IDs
        $all_post_ids = array_values(array_unique(array_merge($fts_ids, $vector_ids)));
        
        // Build citations with permalinks
        $citations_with_links = array();
        foreach ($all_post_ids as $post_id) {
            $citations_with_links[] = array(
                'post_id' => $post_id,
                'permalink' => get_permalink($post_id),
                'title' => get_the_title($post_id)
            );
        }
        
        // Build context
        $context = $this->build_context($fts_results['results'], $vector_results['results']);
        
        // Build RAG summary
        $rag_summary = $this->build_rag_summary($fts_results['results'], $vector_results['results']);
        
        // Generate answer with conversation history
        $answer_data = $this->generate_answer($query, $context, $conversation_history);
        
        wp_send_json_success(array(
            'answer' => $answer_data,
            'rag_summary' => $rag_summary,
            'citations' => $citations_with_links,
            'fts_ids' => $fts_ids,
            'vector_ids' => $vector_ids,
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
        
        if ($status_code !== 200) {
            return false;
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
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
        
        if ($status_code !== 200) {
            return false;
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        return $decoded;
    }
    
    private function build_context($fts_results, $vector_results) {
        $context_parts = array();
        $seen_ids = array();
        
        foreach ($fts_results as $result) {
            if (!in_array($result['post_id'], $seen_ids)) {
                $context_parts[] = "Title: {$result['post_title']}\n" .
                                   "Categories: {$result['categories']}\n" .
                                   "Tags: {$result['tags']}\n" .
                                   "Content: {$result['excerpt']}\n";
                $seen_ids[] = $result['post_id'];
            }
        }
        
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
    
    private function build_rag_summary($fts_results, $vector_results) {
        $summary_parts = array();
        $seen_ids = array();
        
        $all_results = array_merge($fts_results, $vector_results);
        
        foreach ($all_results as $result) {
            if (!in_array($result['post_id'], $seen_ids)) {
                // Display full excerpt without truncation
                $summary_parts[] = "• {$result['post_title']}: {$result['excerpt']}";
                $seen_ids[] = $result['post_id'];
            }
        }
        
        return implode("\n\n", $summary_parts);
    }
    
    private function generate_answer($query, $context, $conversation_history = array()) {
        if (empty(trim($context))) {
            return "My RAG does not have the answer.";
        }
        
        $api_key = get_option('posts_rag_openai_key', '');
        
        if (empty($api_key)) {
            return "Please configure your OpenAI API key in the 20 CAPSTONE plugin settings.";
        }
        
        // Build messages array with conversation history
        $messages = array(
            array(
                'role' => 'system',
                'content' => "You are a helpful assistant that answers questions based on the provided RAG context. If the context doesn't contain enough information to answer the question, respond with 'My RAG does not have the answer.' Be conversational and friendly. You can refer to previous parts of the conversation when answering follow-up questions."
            )
        );
        
        // Add conversation history (up to last 10 messages to avoid token limits)
        if (!empty($conversation_history) && is_array($conversation_history)) {
            $recent_history = array_slice($conversation_history, -10);
            foreach ($recent_history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = array(
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    );
                }
            }
        }
        
        // Add current query with context
        $messages[] = array(
            'role' => 'user',
            'content' => "RAG Context:\n{$context}\n\nQuestion: {$query}\n\nPlease provide a helpful answer based on the RAG context above. If this is a follow-up question, use our conversation history for context."
        );
        
        $api_response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
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
new RAG_ChatGPT_Memory_Assistant();
