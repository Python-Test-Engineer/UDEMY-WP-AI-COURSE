<?php
/**
 * Admin Page Template for LangGraph Demo
 * 
 * This template provides a simple interface for demonstrating
 * a LangGraph workflow with OpenAI.
 * 
 * The JavaScript in src/index.js will inject the interactive UI
 * into the #langgraph-app div below.
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1>
        <?php _e('30 LangGraph Demo', 'wp-langgraph'); ?>
    </h1>
    
    <div style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px; margin-top: 20px;">
        
        <!-- Information Section -->
        <div style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
           
            <h3><?php _e('This Demo:', 'wp-langgraph'); ?></h3>
            <p>
                This advanced demonstration combines two AI capabilities:
            </p>

            <h4>🛠️ Tool-Based Processing</h4>
            <p>
                The plugin intelligently detects when you need WordPress data and automatically executes tools:
            </p>
            <ul style="margin-left: 20px;">
                <li><strong>Get Categories & Tags Statistics:</strong> Lists all categories and tags with post counts</li>
                <li><strong>Get Random Post (French Translation):</strong> Retrieves a random post and translates it to French</li>
            </ul>

            <h4>🔬 Deep Research Tool</h4>
            <p>
                For category analysis, it performs <strong>comprehensive research</strong> on all posts within a category:
            </p>
            <ul style="margin-left: 20px;">
                <li><strong>Category Analysis:</strong> Extracts and analyzes all posts in the specified category</li>
                <li><strong>AI-Powered Summary:</strong> Generates insights about themes, patterns, and trends</li>
                <li><strong>Publication Timeline:</strong> Provides chronological analysis of content</li>
            </ul>

            <p>
                The plugin <strong>automatically chooses</strong> the most appropriate tool based on your query!
            </p>

            <p style="margin-top: 15px;">
                <strong>Note:</strong> This plugin uses the OpenAI API key saved in the <em>02 AGENT JS</em> settings page.
                Please ensure you have saved your API key there first.
            </p>
        </div>

        <!-- 
            The LangGraph Application UI will be injected here by JavaScript
            See src/index.js for the implementation
        -->
        <div id="langgraph-app">
            <!-- JS will inject the interactive UI here -->
            <div style="padding: 40px; text-align: center; color: #666;">
                <p>Loading LangGraph application...</p>
                <p style="font-size: 12px; margin-top: 10px;">
                    If this message persists, ensure the build folder contains the compiled JavaScript.
                    Run: <code>npm run build</code>
                </p>
            </div>
        </div>
        
    </div>
    
    <!-- Debug Information -->
    <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-top: 20px;">
        <h3 style="margin-top: 0;">
            <?php _e('Debug Information', 'wp-langgraph'); ?>
        </h3>
        <p>
            <strong>Plugin Path:</strong> <code><?php echo plugin_dir_path(dirname(__FILE__)); ?></code><br>
            <strong>Build Folder:</strong> <code><?php echo plugin_dir_url(dirname(__FILE__)) . 'build/'; ?></code><br>
            <strong>Build JS Exists:</strong> 
            <?php 
            $build_js_path = plugin_dir_path(dirname(__FILE__)) . 'build/index.js';
            if (file_exists($build_js_path)) {
                echo '<span style="color: green;">✓ Yes</span>';
            } else {
                echo '<span style="color: red;">✗ No - Run <code>npm run build</code> to create it</span>';
            }
            ?>
        </p>
        <p style="font-size: 12px; color: #666; margin-top: 10px;">
            Check the browser console (F12) for detailed debug messages from the LangGraph application.
        </p>
    </div>
</div>
