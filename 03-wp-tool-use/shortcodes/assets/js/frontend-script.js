/**
 * WP Tool Use - Frontend JavaScript
 * Handles AJAX submissions and UI interactions for tool use demo
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Toggle API key visibility
        $('#wp-tool-use-toggle-key').on('click', function() {
            const $input = $('#wp-tool-use-api-key');
            const $button = $(this);
            
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $button.text('Hide');
            } else {
                $input.attr('type', 'password');
                $button.text('Show');
            }
        });
        
        // Submit form handler
        $('#wp-tool-use-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get query value
            const query = $('#wp-tool-use-query').val().trim();
            
            // Get API key value (if input exists)
            const apiKey = $('#wp-tool-use-api-key').length 
                ? $('#wp-tool-use-api-key').val().trim() 
                : '';
            
            // Validate query
            if (!query) {
                showError('Please enter a question.');
                return;
            }
            
            // Validate API key (if input exists and is empty)
            if ($('#wp-tool-use-api-key').length && !apiKey) {
                showError('Please enter your OpenAI API key.');
                return;
            }
            
            // Hide previous response and error
            $('#wp-tool-use-response-container').hide();
            $('#wp-tool-use-error-container').hide();
            $('#wp-tool-use-tools-executed').hide();
            
            // Disable form and show loading state
            setLoadingState(true);
            
            // Prepare AJAX data
            const ajaxData = {
                action: 'wp_tool_use_frontend_query',
                nonce: wpToolUseFrontend.nonce,
                query: query
            };
            
            // Add API key if provided
            if (apiKey) {
                ajaxData.api_key = apiKey;
            }
            
            // Make AJAX request
            $.ajax({
                url: wpToolUseFrontend.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    setLoadingState(false);
                    
                    if (response.success) {
                        // Show response
                        const data = response.data;
                        
                        // Display AI message
                        showResponse(data.message || 'AI response received');
                        
                        // Display tool executions if any
                        if (data.tool_calls && data.tool_calls.length > 0) {
                            showToolExecutions(data.tool_calls);
                        }
                    } else {
                        // Show error
                        showError(response.data.message || 'An error occurred. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    setLoadingState(false);
                    showError('Network error: ' + error);
                }
            });
        });
        
        /**
         * Show AI response
         */
        function showResponse(text) {
            // Convert line breaks and format response
            const formattedText = text.replace(/\n/g, '<br>');
            $('#wp-tool-use-response').html(formattedText);
            $('#wp-tool-use-response-container').fadeIn(300);
        }
        
        /**
         * Show tool executions
         */
        function showToolExecutions(toolCalls) {
            const $toolsList = $('#wp-tool-use-tools-list');
            $toolsList.empty();
            
            toolCalls.forEach(function(tool) {
                const toolHtml = '<li>' +
                    '<strong>' + escapeHtml(tool.function) + ':</strong> ' +
                    escapeHtml(tool.response) +
                    '</li>';
                $toolsList.append(toolHtml);
            });
            
            $('#wp-tool-use-tools-executed').fadeIn(300);
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            $('#wp-tool-use-error-message').text(message);
            $('#wp-tool-use-error-container').fadeIn(300);
        }
        
        /**
         * Set loading state
         */
        function setLoadingState(isLoading) {
            const $button = $('#wp-tool-use-form button[type="submit"]');
            const $textarea = $('#wp-tool-use-query');
            const $apiKeyInput = $('#wp-tool-use-api-key');
            
            if (isLoading) {
                $button.prop('disabled', true);
                $button.find('.button-text').hide();
                $button.find('.button-loading').show();
                $textarea.prop('disabled', true);
                $apiKeyInput.prop('disabled', true);
            } else {
                $button.prop('disabled', false);
                $button.find('.button-text').show();
                $button.find('.button-loading').hide();
                $textarea.prop('disabled', false);
                $apiKeyInput.prop('disabled', false);
            }
        }
        
        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
    });
    
})(jQuery);
