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
            <h2 style="margin-top: 0;">
                <?php _e('What is LangGraph?', 'wp-langgraph'); ?>
            </h2>
            <p>
                <strong>LangGraph</strong> is a library for building stateful, multi-actor applications with LLMs. 
                It allows you to create workflows where AI agents can:
            </p>
            <ul style="margin-left: 20px;">
                <li>Process input through multiple sequential steps (nodes)</li>
                <li>Make decisions and route to different paths (conditional edges)</li>
                <li>Maintain state across the entire workflow</li>
                <li>Coordinate multiple AI agents working together</li>
            </ul>
            
            <h3><?php _e('This Demo:', 'wp-langgraph'); ?></h3>
            <p>
                This simple demonstration shows a <strong>3-step sequential graph</strong>:
            </p>
            <ol style="margin-left: 20px;">
                <li><strong>Analyzer:</strong> Analyzes the user's input text</li>
                <li><strong>Processor:</strong> Processes the analysis results</li>
                <li><strong>Responder:</strong> Generates a final response</li>
            </ol>
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
