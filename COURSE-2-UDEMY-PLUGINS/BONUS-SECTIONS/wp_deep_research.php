<?php
/**
 * Plugin Name: ✅ 50 UDEMY DEEP RESEARCH
 * Plugin URI: https://example.com
 * Description: Performs deep research on WordPress posts in a category using LangChain.js and OpenAI
 * Version: 1.0.0
 * Author: Craig West
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DeepResearchAgent {
    
    private $plugin_slug = 'deep-research-agent';
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_dra_get_posts', [$this, 'ajax_get_posts']);
        add_action('wp_ajax_dra_perform_research', [$this, 'ajax_perform_research']);
        add_action('wp_ajax_dra_save_report', [$this, 'ajax_save_report']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Deep Research Agent',
            '50 UDEMY DEEP RESEARCH',
            'manage_options',
            $this->plugin_slug,
            [$this, 'render_admin_page'],
            'dashicons-search',
            4.97
        );
    }
    
    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_' . $this->plugin_slug) {
            return;
        }
        
        // Enqueue LangChain.js from CDN
        wp_enqueue_script(
            'langchain-core',
            'https://cdn.jsdelivr.net/npm/@langchain/core@0.2.0/dist/index.js',
            [],
            '0.2.0',
            true
        );
        
        wp_enqueue_script(
            'langchain-openai',
            'https://cdn.jsdelivr.net/npm/@langchain/openai@0.2.0/dist/index.js',
            ['langchain-core'],
            '0.2.0',
            true
        );
        
        wp_enqueue_script(
            'dra-admin-script',
            plugin_dir_url(__FILE__) . 'assets/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
        
        wp_enqueue_style(
            'dra-admin-style',
            plugin_dir_url(__FILE__) . 'assets/admin.css',
            [],
            '1.0.0'
        );
        
        wp_localize_script('dra-admin-script', 'draAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }
    
    public function ajax_get_posts() {
        console_log('AJAX: Getting posts for category');
        
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        if (!$category_id) {
            wp_send_json_error(['message' => 'Invalid category ID']);
            return;
        }
        
        $posts = get_posts([
            'category' => $category_id,
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        
        $formatted_posts = array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => wp_strip_all_tags($post->post_content),
                'excerpt' => $post->post_excerpt,
                'date' => $post->post_date,
                'author' => get_the_author_meta('display_name', $post->post_author)
            ];
        }, $posts);
        
        console_log('Found ' . count($formatted_posts) . ' posts');
        
        wp_send_json_success([
            'posts' => $formatted_posts,
            'count' => count($formatted_posts)
        ]);
    }
    
    public function ajax_perform_research() {
        console_log('AJAX: Starting research process');
        
        // This endpoint will be called from the frontend
        // The actual research happens in JavaScript using LangChain
        wp_send_json_success(['message' => 'Research initiated']);
    }
    
    public function ajax_save_report() {
        console_log('AJAX: Saving markdown report');
        
        $markdown = isset($_POST['markdown']) ? $_POST['markdown'] : '';
        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : 'research-report.md';
        
        if (empty($markdown)) {
            wp_send_json_error(['message' => 'No content to save']);
            return;
        }
        
        // Save to uploads directory
        $upload_dir = wp_upload_dir();
        $reports_dir = $upload_dir['basedir'] . '/research-reports';
        
        if (!file_exists($reports_dir)) {
            wp_mkdir_p($reports_dir);
        }
        
        $file_path = $reports_dir . '/' . $filename;
        $result = file_put_contents($file_path, $markdown);
        
        if ($result === false) {
            console_log('Failed to save report to: ' . $file_path);
            wp_send_json_error(['message' => 'Failed to save report']);
            return;
        }
        
        console_log('Report saved successfully to: ' . $file_path);
        
        wp_send_json_success([
            'message' => 'Report saved successfully',
            'file_path' => $file_path,
            'file_url' => $upload_dir['baseurl'] . '/research-reports/' . $filename
        ]);
    }
    
    public function render_admin_page() {
        $categories = get_categories(['hide_empty' => false]);
        ?>
        <div class="wrap dra-container">
            <h1 class="dra-title">
                <span class="dashicons dashicons-search"></span>
                Deep Research Agent
            </h1>
            
            <div class="dra-card">
                <div class="dra-section">
                    <h2>Configuration</h2>
                    
                    <div class="dra-form-group">
                        <label for="openai-key">OpenAI API Key</label>
                        <input 
                            type="password" 
                            id="openai-key" 
                            class="dra-input" 
                            placeholder="sk-..." 
                        />
                        <small class="dra-help">Your API key will not be stored on the server</small>
                    </div>
                    
                    <div class="dra-form-row">
                        <div class="dra-form-group">
                            <label for="model-select">Model</label>
                            <select id="model-select" class="dra-select">
                                <option value="gpt-4o-mini" selected>GPT-4o Mini (Recommended)</option>
                                <option value="gpt-4o">GPT-4o</option>
                                <option value="gpt-4-turbo">GPT-4 Turbo</option>
                                <option value="gpt-4">GPT-4</option>
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                            </select>
                        </div>
                        
                        <div class="dra-form-group">
                            <label for="category-select">Category</label>
                            <select id="category-select" class="dra-select">
                                <option value="">Select a category...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>">
                                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button id="start-research-btn" class="dra-button dra-button-primary">
                        <span class="dashicons dashicons-search"></span>
                        Start Deep Research
                    </button>
                </div>
                
                <div id="progress-section" class="dra-section" style="display: none;">
                    <h2>Research Progress</h2>
                    <div class="dra-progress-bar">
                        <div id="progress-fill" class="dra-progress-fill"></div>
                    </div>
                    <div id="progress-text" class="dra-progress-text">Initializing...</div>
                    <div id="logs-container" class="dra-logs"></div>
                </div>
                
                <div id="report-section" class="dra-section" style="display: none;">
                    <div class="dra-report-header">
                        <h2>Research Report</h2>
                        <button id="save-report-btn" class="dra-button dra-button-success">
                            <span class="dashicons dashicons-download"></span>
                            Save as Markdown
                        </button>
                    </div>
                    <div id="report-preview" class="dra-report-preview"></div>
                    <textarea id="report-markdown" style="display: none;"></textarea>
                </div>
            </div>
        </div>
        <?php
    }
}

// Helper function for console logging
function console_log($data) {
    if (is_array($data) || is_object($data)) {
        error_log(print_r($data, true));
    } else {
        error_log($data);
    }
}

// Initialize plugin
new DeepResearchAgent();

// Create assets directory structure
register_activation_hook(__FILE__, function() {
    $plugin_dir = plugin_dir_path(__FILE__);
    $assets_dir = $plugin_dir . 'assets';
    
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    // Create admin.js
    $admin_js = <<<'JS'
(function($) {
    'use strict';
    
    console.log('Deep Research Agent: Script loaded');
    
    let currentPosts = [];
    let researchReport = '';
    
    $(document).ready(function() {
        console.log('Deep Research Agent: DOM ready');
        
        $('#start-research-btn').on('click', startResearch);
        $('#save-report-btn').on('click', saveReport);
    });
    
    async function startResearch() {
        console.log('Starting research process...');
        
        const apiKey = $('#openai-key').val().trim();
        const model = $('#model-select').val();
        const categoryId = $('#category-select').val();
        
        if (!apiKey) {
            alert('Please enter your OpenAI API key');
            console.error('No API key provided');
            return;
        }
        
        if (!categoryId) {
            alert('Please select a category');
            console.error('No category selected');
            return;
        }
        
        console.log(`Configuration: Model=${model}, Category=${categoryId}`);
        
        // Show progress section
        $('#progress-section').slideDown();
        $('#report-section').slideUp();
        $('#start-research-btn').prop('disabled', true);
        
        updateProgress(10, 'Fetching posts from category...');
        
        try {
            // Get posts from selected category
            const posts = await getPosts(categoryId);
            console.log(`Retrieved ${posts.length} posts:`, posts);
            
            if (posts.length === 0) {
                throw new Error('No posts found in selected category');
            }
            
            currentPosts = posts;
            updateProgress(25, `Found ${posts.length} posts. Analyzing content...`);
            
            // Perform deep research using LangChain
            await performDeepResearch(posts, apiKey, model);
            
        } catch (error) {
            console.error('Research error:', error);
            addLog(`Error: ${error.message}`, 'error');
            updateProgress(0, 'Research failed');
            $('#start-research-btn').prop('disabled', false);
        }
    }
    
    function getPosts(categoryId) {
        console.log(`Fetching posts for category: ${categoryId}`);
        
        return new Promise((resolve, reject) => {
            $.ajax({
                url: draAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dra_get_posts',
                    category_id: categoryId
                },
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        resolve(response.data.posts);
                    } else {
                        reject(new Error(response.data.message || 'Failed to fetch posts'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {xhr, status, error});
                    reject(new Error('Network error: ' + error));
                }
            });
        });
    }
    
    async function performDeepResearch(posts, apiKey, model) {
        console.log('Starting deep research with LangChain...');
        addLog('Initializing AI research agent...', 'info');
        
        updateProgress(35, 'Initializing AI agent...');
        
        try {
            // Create summary of all posts
            const postsContent = posts.map(post => {
                return `Title: ${post.title}\nDate: ${post.date}\nAuthor: ${post.author}\nContent: ${post.content.substring(0, 500)}...\n`;
            }).join('\n---\n\n');
            
            console.log('Prepared content for analysis');
            addLog(`Analyzing ${posts.length} posts with ${model}...`, 'info');
            
            updateProgress(50, 'Performing deep analysis...');
            
            // Simulate LangChain research with direct OpenAI call
            // In a real implementation, you would use LangChain.js here
            const research = await performOpenAIResearch(postsContent, posts, apiKey, model);
            
            updateProgress(90, 'Generating final report...');
            console.log('Research completed, generating report...');
            
            researchReport = research;
            displayReport(research);
            
            updateProgress(100, 'Research complete!');
            addLog('Research completed successfully!', 'success');
            
            $('#start-research-btn').prop('disabled', false);
            $('#report-section').slideDown();
            
        } catch (error) {
            console.error('Deep research error:', error);
            throw error;
        }
    }
    
    async function performOpenAIResearch(content, posts, apiKey, model) {
        console.log(`Calling OpenAI API with model: ${model}`);
        
        const prompt = `You are a deep research analyst. Analyze the following ${posts.length} blog posts and create a comprehensive research report.

Posts to analyze:
${content}

Create a detailed markdown report with the following sections:
1. Executive Summary
2. Key Themes and Topics
3. Content Analysis
4. Author Insights
5. Trends and Patterns
6. Recommendations
7. Conclusion

Make the report thorough, insightful, and well-structured in markdown format.`;

        const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: model,
                messages: [
                    {
                        role: 'system',
                        content: 'You are an expert research analyst who creates comprehensive, insightful reports in markdown format.'
                    },
                    {
                        role: 'user',
                        content: prompt
                    }
                ],
                temperature: 0.7,
                max_tokens: 3000
            })
        });
        
        if (!response.ok) {
            const error = await response.json();
            console.error('OpenAI API error:', error);
            throw new Error(`OpenAI API error: ${error.error?.message || 'Unknown error'}`);
        }
        
        const data = await response.json();
        console.log('OpenAI response received');
        
        return data.choices[0].message.content;
    }
    
    function displayReport(markdown) {
        console.log('Displaying report preview');
        
        $('#report-markdown').val(markdown);
        
        // Convert markdown to HTML for preview (basic conversion)
        const html = markdownToHTML(markdown);
        $('#report-preview').html(html);
    }
    
    function markdownToHTML(markdown) {
        // Basic markdown to HTML conversion
        let html = markdown
            .replace(/#{6}\s(.+)/g, '<h6>$1</h6>')
            .replace(/#{5}\s(.+)/g, '<h5>$1</h5>')
            .replace(/#{4}\s(.+)/g, '<h4>$1</h4>')
            .replace(/#{3}\s(.+)/g, '<h3>$1</h3>')
            .replace(/#{2}\s(.+)/g, '<h2>$1</h2>')
            .replace(/#{1}\s(.+)/g, '<h1>$1</h1>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/^\-\s(.+)/gm, '<li>$1</li>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/^(?!<[h|l])/gm, '<p>')
            .replace(/(?![h|l]>)$/gm, '</p>');
        
        return html;
    }
    
    function saveReport() {
        console.log('Saving report as markdown...');
        
        const markdown = $('#report-markdown').val();
        
        if (!markdown) {
            alert('No report to save');
            return;
        }
        
        const filename = 'research-report-' + Date.now() + '.md';
        
        $.ajax({
            url: draAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dra_save_report',
                markdown: markdown,
                filename: filename
            },
            success: function(response) {
                console.log('Save response:', response);
                
                if (response.success) {
                    alert(`Report saved successfully!\n\nLocation: ${response.data.file_path}`);
                    console.log('Report saved to:', response.data.file_path);
                    
                    // Also trigger browser download
                    downloadMarkdown(markdown, filename);
                } else {
                    alert('Failed to save report: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Save error:', {xhr, status, error});
                alert('Error saving report: ' + error);
            }
        });
    }
    
    function downloadMarkdown(content, filename) {
        console.log('Triggering browser download...');
        
        const blob = new Blob([content], { type: 'text/markdown' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        console.log('Download triggered for:', filename);
    }
    
    function updateProgress(percent, message) {
        console.log(`Progress: ${percent}% - ${message}`);
        
        $('#progress-fill').css('width', percent + '%');
        $('#progress-text').text(message);
        
        addLog(message, 'info');
    }
    
    function addLog(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logClass = `dra-log dra-log-${type}`;
        const logHtml = `<div class="${logClass}">[${timestamp}] ${message}</div>`;
        
        $('#logs-container').append(logHtml);
        $('#logs-container').scrollTop($('#logs-container')[0].scrollHeight);
    }
    
})(jQuery);
JS;
    
    file_put_contents($assets_dir . '/admin.js', $admin_js);
    
    // Create admin.css
    $admin_css = <<<'CSS'
.dra-container {
    max-width: 1200px;
    margin: 20px auto;
}

.dra-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 28px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 24px;
}

.dra-title .dashicons {
    color: #3b82f6;
    font-size: 32px;
    width: 32px;
    height: 32px;
}

.dra-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.dra-section {
    padding: 32px;
    border-bottom: 1px solid #e5e7eb;
}

.dra-section:last-child {
    border-bottom: none;
}

.dra-section h2 {
    margin: 0 0 24px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
}

.dra-form-group {
    margin-bottom: 20px;
}

.dra-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.dra-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.dra-input,
.dra-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
    background: #ffffff;
}

.dra-input:focus,
.dra-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dra-help {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #6b7280;
}

.dra-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.dra-button .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.dra-button-primary {
    background: #3b82f6;
    color: #ffffff;
}

.dra-button-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.dra-button-primary:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.dra-button-success {
    background: #10b981;
    color: #ffffff;
}

.dra-button-success:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

.dra-progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 12px;
}

.dra-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    border-radius: 4px;
    transition: width 0.3s ease;
    width: 0%;
}

.dra-progress-text {
    font-size: 14px;
    color: #4b5563;
    margin-bottom: 16px;
    font-weight: 500;
}

.dra-logs {
    max-height: 300px;
    overflow-y: auto;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 16px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
}

.dra-log {
    padding: 6px 0;
    border-bottom: 1px solid #e5e7eb;
}

.dra-log:last-child {
    border-bottom: none;
}

.dra-log-info {
    color: #3b82f6;
}

.dra-log-success {
    color: #10b981;
    font-weight: 500;
}

.dra-log-error {
    color: #ef4444;
    font-weight: 500;
}

.dra-report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.dra-report-header h2 {
    margin: 0;
}

.dra-report-preview {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 24px;
    max-height: 600px;
    overflow-y: auto;
}

.dra-report-preview h1 {
    font-size: 24px;
    margin: 24px 0 16px 0;
    color: #1e293b;
    border-bottom: 2px solid #3b82f6;
    padding-bottom: 8px;
}

.dra-report-preview h1:first-child {
    margin-top: 0;
}

.dra-report-preview h2 {
    font-size: 20px;
    margin: 20px 0 12px 0;
    color: #334155;
}

.dra-report-preview h3 {
    font-size: 18px;
    margin: 16px 0 10px 0;
    color: #475569;
}

.dra-report-preview p {
    line-height: 1.6;
    color: #4b5563;
    margin: 12px 0;
}

.dra-report-preview ul,
.dra-report-preview ol {
    margin: 12px 0;
    padding-left: 24px;
}

.dra-report-preview li {
    margin: 6px 0;
    line-height: 1.6;
    color: #4b5563;
}

.dra-report-preview strong {
    color: #1e293b;
    font-weight: 600;
}

.dra-report-preview em {
    color: #6b7280;
}

/* Scrollbar styling */
.dra-logs::-webkit-scrollbar,
.dra-report-preview::-webkit-scrollbar {
    width: 8px;
}

.dra-logs::-webkit-scrollbar-track,
.dra-report-preview::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.dra-logs::-webkit-scrollbar-thumb,
.dra-report-preview::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.dra-logs::-webkit-scrollbar-thumb:hover,
.dra-report-preview::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
CSS;
    
    file_put_contents($assets_dir . '/admin.css', $admin_css);
    
    console_log('Deep Research Agent plugin activated and assets created');
});
