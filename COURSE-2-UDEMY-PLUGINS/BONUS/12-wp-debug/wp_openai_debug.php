<?php
/**
 * Plugin Name: ✅ 012 UDEMY  OpenAI Debug Logger
 * Description: Demonstrates debugging techniques for OpenAI API requests with comprehensive logging
 * Version: 1.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
add_action('admin_menu', 'openai_debug_menu');

function openai_debug_menu() {
    add_menu_page(
        'OpenAI Debug Logger',
        '12 DEBUG AI',
        'manage_options',
        'openai-debug-logger',
        'openai_debug_page',
        'dashicons-admin-generic', // Icon
        4.8
    );
}

// Main plugin page
function openai_debug_page() {
    ?>
    <div class="wrap">
        <h1>OpenAI Debug Logger</h1>
        <p>This plugin demonstrates comprehensive logging techniques for debugging API requests.</p>
        
        <div style="max-width: 800px;">
            <div style="margin-bottom: 20px;">
                <label for="api-key" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    OpenAI API Key:
                </label>
                <input 
                    type="password" 
                    id="api-key" 
                    style="width: 100%; padding: 8px; font-family: monospace;"
                    placeholder="sk-..."
                />
                <small style="color: #666;">Enter your OpenAI API key. It's stored in browser memory only.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="user-question" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Ask a Question:
                </label>
                <input 
                    type="text" 
                    id="user-question" 
                    style="width: 100%; padding: 8px;"
                    placeholder="What is the capital of France?"
                />
            </div>

            <button id="submit-btn" class="button button-primary" style="margin-bottom: 20px;">
                Send Request
            </button>

            <div id="response-area" style="margin-bottom: 20px; padding: 15px; background: #f0f0f1; border-radius: 4px; display: none;">
                <h3 style="margin-top: 0;">Response:</h3>
                <div id="response-content" style="white-space: pre-wrap;"></div>
            </div>

            <div style="margin-bottom: 20px;">
                <h3>Debug Log:</h3>
                <div id="log-output" style="
                    background: #1e1e1e; 
                    color: #d4d4d4; 
                    padding: 15px; 
                    border-radius: 4px; 
                    font-family: 'Courier New', monospace; 
                    font-size: 12px; 
                    max-height: 400px; 
                    overflow-y: auto;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    line-spacing: 1.0;
                ">
                    <span style="color: #888;">Logs will appear here...</span>
                </div>
            </div>

            <button id="download-btn" class="button" disabled>
                Download Log as Markdown
            </button>
            <button id="clear-btn" class="button" style="margin-left: 10px;">
                Clear Log
            </button>
        </div>
    </div>

    <style>
        .log-entry {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #333;
        }
        .log-timestamp {
            color: #888;
            font-size: 11px;
        }
        .log-level-info { color: #4fc3f7; }
        .log-level-success { color: #81c784; }
        .log-level-error { color: #e57373; }
        .log-level-warning { color: #ffb74d; }
    </style>

    <script>
        class DebugLogger {
            constructor() {
                this.logs = [];
                this.logOutput = document.getElementById('log-output');
                this.downloadBtn = document.getElementById('download-btn');
            }

            log(message, level = 'info', data = null) {
                const timestamp = new Date().toISOString();
                const logEntry = {
                    timestamp,
                    level,
                    message,
                    data: data ? JSON.stringify(data, null, 2) : null
                };

                this.logs.push(logEntry);
                console.log(`[${level.toUpperCase()}] ${message}`, data || '');

                this.displayLog(logEntry);
                this.downloadBtn.disabled = false;
            }

            displayLog(entry) {
                const logDiv = document.createElement('div');
                logDiv.className = 'log-entry';
                
                const levelColor = {
                    'info': 'log-level-info',
                    'success': 'log-level-success',
                    'error': 'log-level-error',
                    'warning': 'log-level-warning'
                }[entry.level] || 'log-level-info';

                logDiv.innerHTML = `
                    <div class="log-timestamp">${entry.timestamp}</div>
                    <div class="${levelColor}">[${entry.level.toUpperCase()}] ${entry.message}</div>
                    ${entry.data ? `<div style="color: #ce9178; margin-top: 4px; margin-left: 15px;">${entry.data}</div>` : ''}
                `;

                if (this.logOutput.querySelector('span')) {
                    this.logOutput.innerHTML = '';
                }

                this.logOutput.appendChild(logDiv);
                this.logOutput.scrollTop = this.logOutput.scrollHeight;
            }

            clear() {
                this.logs = [];
                this.logOutput.innerHTML = '<span style="color: #888;">Logs will appear here...</span>';
                this.downloadBtn.disabled = true;
                console.clear();
                console.log('Debug log cleared');
            }

            generateMarkdown() {
                let markdown = '# OpenAI API Debug Log\n\n';
                markdown += `Generated: ${new Date().toISOString()}\n\n`;
                markdown += '---\n\n';

                this.logs.forEach((entry, index) => {
                    markdown += `## Log Entry ${index + 1}\n\n`;
                    markdown += `**Timestamp:** ${entry.timestamp}\n\n`;
                    markdown += `**Level:** ${entry.level.toUpperCase()}\n\n`;
                    markdown += `**Message:** ${entry.message}\n\n`;
                    
                    if (entry.data) {
                        markdown += '**Data:**\n\n```json\n' + entry.data + '\n```\n\n';
                    }
                    
                    markdown += '---\n\n';
                });

                return markdown;
            }

            downloadMarkdown() {
                const markdown = this.generateMarkdown();
                const blob = new Blob([markdown], { type: 'text/markdown' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `openai-debug-log-${Date.now()}.md`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                this.log('Debug log downloaded successfully', 'success');
            }
        }

        // Initialize logger
        const logger = new DebugLogger();

        // Event listeners
        document.getElementById('submit-btn').addEventListener('click', async () => {
            const apiKey = document.getElementById('api-key').value.trim();
            const question = document.getElementById('user-question').value.trim();
            const submitBtn = document.getElementById('submit-btn');
            const responseArea = document.getElementById('response-area');
            const responseContent = document.getElementById('response-content');

            logger.log('=== New Request Started ===', 'info');

            // Validation
            if (!apiKey) {
                logger.log('API Key validation failed: empty key', 'error');
                alert('Please enter your OpenAI API key');
                return;
            }

            if (!question) {
                logger.log('Question validation failed: empty question', 'error');
                alert('Please enter a question');
                return;
            }

            logger.log('Validation passed', 'success');
            logger.log('Question received', 'info', { question });

            // Disable button during request
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            responseArea.style.display = 'none';

            try {
                logger.log('Preparing API request payload', 'info');
                
                const requestBody = {
                    model: 'gpt-3.5-turbo',
                    messages: [
                        { role: 'user', content: question }
                    ],
                    max_tokens: 150
                };

                logger.log('Request payload prepared', 'info', requestBody);
                logger.log('Initiating fetch to OpenAI API', 'info');

                const startTime = performance.now();

                const response = await fetch('https://api.openai.com/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${apiKey}`
                    },
                    body: JSON.stringify(requestBody)
                });

                const endTime = performance.now();
                const duration = (endTime - startTime).toFixed(2);

                logger.log(`API request completed in ${duration}ms`, 'success');
                logger.log('Response status', 'info', { 
                    status: response.status, 
                    statusText: response.statusText 
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    logger.log('API returned error response', 'error', errorData);
                    throw new Error(`API Error: ${errorData.error?.message || response.statusText}`);
                }

                logger.log('Parsing response JSON', 'info');
                const data = await response.json();
                
                logger.log('Response parsed successfully', 'success');
                logger.log('Full API response', 'info', data);

                const answer = data.choices?.[0]?.message?.content || 'No response content';
                
                logger.log('Extracted answer from response', 'info', { answer });
                logger.log('Token usage', 'info', data.usage);

                // Display response
                responseContent.textContent = answer;
                responseArea.style.display = 'block';

                logger.log('=== Request Completed Successfully ===', 'success');

            } catch (error) {
                logger.log('Request failed with exception', 'error', {
                    message: error.message,
                    stack: error.stack
                });
                
                responseContent.textContent = `Error: ${error.message}`;
                responseArea.style.display = 'block';
                
                logger.log('=== Request Failed ===', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Request';
            }
        });

        document.getElementById('download-btn').addEventListener('click', () => {
            logger.downloadMarkdown();
        });

        document.getElementById('clear-btn').addEventListener('click', () => {
            if (confirm('Clear all logs?')) {
                logger.clear();
            }
        });

        // Initial log
        logger.log('OpenAI Debug Logger initialized', 'success');
        logger.log('Ready to process requests', 'info');
    </script>
    <?php
}
