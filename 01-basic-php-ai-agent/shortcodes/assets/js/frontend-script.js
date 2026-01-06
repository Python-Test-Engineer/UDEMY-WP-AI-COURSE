/**
 * Basic PHP AI Agent - Frontend JavaScript
 * Handles AJAX submissions and UI interactions
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Toggle API key visibility
        $('#basic-php-ai-agent-toggle-key').on('click', function() {
            const $input = $('#basic-php-ai-agent-api-key');
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
        $('#basic-php-ai-agent-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get query value
            const query = $('#basic-php-ai-agent-query').val().trim();
            
            // Get API key value (if input exists)
            const apiKey = $('#basic-php-ai-agent-api-key').length 
                ? $('#basic-php-ai-agent-api-key').val().trim() 
                : '';
            
            // Validate query
            if (!query) {
                showError('Please enter a question.');
                return;
            }
            
            // Validate API key (if input exists and is empty)
            if ($('#basic-php-ai-agent-api-key').length && !apiKey) {
                showError('Please enter your OpenAI API key.');
                return;
            }
            
            // Hide previous response and error
            $('#basic-php-ai-agent-response-container').hide();
            $('#basic-php-ai-agent-error-container').hide();
            
            // Disable form and show loading state
            setLoadingState(true);
            
            // Prepare AJAX data
            const ajaxData = {
                action: 'basic_php_ai_agent_query',
                nonce: basicPhpAiAgent.nonce,
                query: query
            };
            
            // Add API key if provided
            if (apiKey) {
                ajaxData.api_key = apiKey;
            }
            
            // Make AJAX request
            $.ajax({
                url: basicPhpAiAgent.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    setLoadingState(false);
                    
                    if (response.success) {
                        // Show response
                        showResponse(response.data.response);
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
            $('#basic-php-ai-agent-response').text(text);
            $('#basic-php-ai-agent-response-container').fadeIn(300);
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            $('#basic-php-ai-agent-error-message').text(message);
            $('#basic-php-ai-agent-error-container').fadeIn(300);
        }
        
        /**
         * Set loading state
         */
        function setLoadingState(isLoading) {
            const $button = $('#basic-php-ai-agent-form button[type="submit"]');
            const $textarea = $('#basic-php-ai-agent-query');
            
            if (isLoading) {
                $button.prop('disabled', true);
                $button.find('.button-text').hide();
                $button.find('.button-loading').show();
                $textarea.prop('disabled', true);
            } else {
                $button.prop('disabled', false);
                $button.find('.button-text').show();
                $button.find('.button-loading').hide();
                $textarea.prop('disabled', false);
            }
        }
        
    });
    
})(jQuery);
