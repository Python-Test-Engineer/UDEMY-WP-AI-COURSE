/**
 * WP Basic Agent - Frontend JavaScript
 * Handles AJAX submissions and UI interactions
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Toggle API key visibility
        $('#wp-basic-agent-toggle-key').on('click', function() {
            const $input = $('#wp-basic-agent-api-key');
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
        $('#wp-basic-agent-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get query value
            const query = $('#wp-basic-agent-query').val().trim();
            
            // Get API key value (if input exists)
            const apiKey = $('#wp-basic-agent-api-key').length 
                ? $('#wp-basic-agent-api-key').val().trim() 
                : '';
            
            // Validate query
            if (!query) {
                showError('Please enter a question.');
                return;
            }
            
            // Validate API key (if input exists and is empty)
            if ($('#wp-basic-agent-api-key').length && !apiKey) {
                showError('Please enter your OpenAI API key.');
                return;
            }
            
            // Hide previous response and error
            $('#wp-basic-agent-response-container').hide();
            $('#wp-basic-agent-error-container').hide();
            
            // Disable form and show loading state
            setLoadingState(true);
            
            // Prepare AJAX data
            const ajaxData = {
                action: 'wp_basic_agent_frontend_query',
                nonce: wpBasicAgentFrontend.nonce,
                query: query
            };
            
            // Add API key if provided
            if (apiKey) {
                ajaxData.api_key = apiKey;
            }
            
            // Make AJAX request
            $.ajax({
                url: wpBasicAgentFrontend.ajaxurl,
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
            // Convert line breaks and format response
            const formattedText = text.replace(/\n/g, '<br>');
            $('#wp-basic-agent-response').html(formattedText);
            $('#wp-basic-agent-response-container').fadeIn(300);
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            $('#wp-basic-agent-error-message').text(message);
            $('#wp-basic-agent-error-container').fadeIn(300);
        }
        
        /**
         * Set loading state
         */
        function setLoadingState(isLoading) {
            const $button = $('#wp-basic-agent-form button[type="submit"]');
            const $textarea = $('#wp-basic-agent-query');
            const $apiKeyInput = $('#wp-basic-agent-api-key');
            
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
        
    });
    
})(jQuery);
