<?php
/**
 * Plugin Name: ✅ 05 UDEMY Structured Output Plugin
 * Plugin URI: https://example.com/openai-structured-output-plugin
 * Description: A WordPress plugin that demonstrates OpenAI's structured output API for city information retrieval.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: openai-structured-output
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class for OpenAI Structured Output functionality
 */
class OpenAI_Structured_Output_Plugin {

    /**
     * Constructor - Initialize the plugin
     */
    public function __construct() {
        // Hook into WordPress admin menu to add our page
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue scripts and styles for the admin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Register AJAX handler for OpenAI API calls
        add_action('wp_ajax_get_city_info', array($this, 'handle_openai_request'));
    }

    /**
     * Add admin menu item at position 3.5
     * Position 3.5 places it between Posts (5) and Media (10), often under Tools or custom location
     */
    public function add_admin_menu() {
        add_menu_page(
            "05 Streucture Output", // Page title
            "05 STRUCTURED",       // Menu title
            'manage_options',                                          // Capability required
            'openai-structured-output',                               // Menu slug
            array($this, 'admin_page_callback'),                      // Callback function
            'dashicons-location-alt',                                 // Icon (location/map icon)
           3.95                                                     // Position (level 3.5)
        );
    }

    /**
     * Enqueue CSS and JavaScript for the admin page
     *
     * @param string $hook The current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin's admin page
        if ($hook !== 'toplevel_page_openai-structured-output') {
            return;
        }

        // Enqueue Google Fonts for styling consistency
        wp_enqueue_style(
            'openai-structured-google-fonts',
            'https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap',
            array(),
            null
        );

        // Add inline CSS for the plugin's styling
        wp_add_inline_style('openai-structured-google-fonts', $this->get_admin_styles());

        // Enqueue jQuery (WordPress includes it by default)
        wp_enqueue_script('jquery');

        // Add inline JavaScript for the plugin's functionality
        wp_add_inline_script('jquery', $this->get_admin_scripts());
    }

    /**
     * Get CSS styles for the admin page
     *
     * @return string Inline CSS
     */
    private function get_admin_styles() {
        return "
            .openai-structured-container {
                font-family: 'Raleway', sans-serif;
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background: #f5f5f5;
                border-radius: 8px;
            }

            .openai-structured-container h1 {
                color: #333;
                font-size: 2em;
                margin-bottom: 30px;
            }

            .openai-structured-container input[type='text'] {
                width: 100%;
                padding: 12px;
                margin: 10px 0;
                border: 2px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                font-family: 'Fira Code', monospace;
                box-sizing: border-box;
            }

            .openai-structured-container button {
                background: #007bff;
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                margin: 10px 0;
            }

            .openai-structured-container button:hover {
                background: #0056b3;
            }

            #openai-result {
                background: white;
                padding: 20px;
                border-radius: 5px;
                margin-top: 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            #openai-json-output {
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 20px;
                border-radius: 5px;
                margin-top: 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                font-family: 'Fira Code', monospace;
                overflow-x: auto;
            }

            #openai-json-output h3 {
                color: #4ec9b0;
                margin-top: 0;
            }

            #openai-json-output pre {
                margin: 0;
                white-space: pre-wrap;
                word-wrap: break-word;
            }

            .city-card {
                border-left: 4px solid #007bff;
                padding-left: 15px;
            }

            .city-card h2 {
                margin: 0 0 10px 0;
                color: #007bff;
            }

            .city-info {
                margin: 10px 0;
            }

            .landmarks {
                margin-top: 15px;
            }

            .landmarks ul {
                list-style-type: none;
                padding: 0;
            }

            .landmarks li {
                background: #f8f9fa;
                padding: 8px;
                margin: 5px 0;
                border-radius: 3px;
            }

            .error {
                color: red;
            }

            .loading {
                color: #666;
                font-style: italic;
            }
        ";
    }

    /**
     * Get JavaScript for the admin page
     *
     * @return string Inline JavaScript
     */
    private function get_admin_scripts() {
        return <<<'EOT'
            jQuery(document).ready(function($) {
                $('#openai-send-btn').on('click', function() {
                    const apiKey = $('#openai-api-key').val();
                    const prompt = $('#openai-prompt').val();
                    const resultDiv = $('#openai-result');
                    const nonce = $('#openai_nonce').val();

                    if (!apiKey) {
                        resultDiv.html('<p class="error">Please enter your OpenAI API key</p>');
                        return;
                    }

                    resultDiv.html('<p class="loading">Loading...</p>');
                    $('#openai-json-output').html('');

                    // Send AJAX request to WordPress backend
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_city_info',
                            api_key: apiKey,
                            prompt: prompt,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                const cityInfo = response.data;

                                // Display formatted result
                                resultDiv.html(`
                                    <div class="city-card">
                                        <h1>JSON Schema Structured Output formatted</h1>
                                        <h2>${cityInfo.city_name}, ${cityInfo.country}</h2>
                                        <div class="city-info">
                                            <strong>Population:</strong> ${cityInfo.population.toLocaleString()}
                                        </div>
                                        <div class="city-info">
                                            <strong>Fun Fact:</strong> ${cityInfo.fun_fact}
                                        </div>
                                        <div class="landmarks">
                                            <strong>Famous Landmarks:</strong>
                                            <ul>
                                                ${cityInfo.famous_landmarks.map(landmark => `<li>${landmark}</li>`).join('')}
                                            </ul>
                                        </div>
                                    </div>
                                `);

                                // Display raw JSON
                                $('#openai-json-output').html(`
                                    <h3>Raw Structured JSON Output Returned:</h3>
                                    <pre>${JSON.stringify(cityInfo, null, 2)}</pre>
                                `);
                            } else {
                                resultDiv.html(`<p class="error">Error: ${response.data}</p>`);
                            }
                        },
                        error: function(xhr, status, error) {
                            resultDiv.html(`<p class="error">AJAX Error: ${error}</p>`);
                        }
                    });
                });
            });
EOT;
    }

    /**
     * AJAX handler for OpenAI API requests
     * Handles the server-side call to OpenAI to maintain security
     */
    public function handle_openai_request() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'openai_structured_nonce')) {
            wp_die('Security check failed');
        }

        // Sanitize inputs
        $api_key = sanitize_text_field($_POST['api_key']);
        $prompt = sanitize_text_field($_POST['prompt']);

        if (empty($api_key) || empty($prompt)) {
            wp_send_json_error('Missing API key or prompt');
        }

        // Define the JSON schema for structured output (same as original HTML)
        $response_schema = array(
            'type' => 'object',
            'properties' => array(
                'city_name' => array(
                    'type' => 'string',
                    'description' => 'The name of the city'
                ),
                'country' => array(
                    'type' => 'string',
                    'description' => 'The country where the city is located'
                ),
                'population' => array(
                    'type' => 'number',
                    'description' => 'The approximate population of the city'
                ),
                'famous_landmarks' => array(
                    'type' => 'array',
                    'description' => 'List of famous landmarks in the city',
                    'items' => array(
                        'type' => 'string'
                    )
                ),
                'fun_fact' => array(
                    'type' => 'string',
                    'description' => 'An interesting fact about the city'
                )
            ),
            'required' => array('city_name', 'country', 'population', 'famous_landmarks', 'fun_fact'),
            'additionalProperties' => false
        );

        // Prepare the API request payload
        $payload = array(
            'model' => 'gpt-4o-mini',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that provides structured information about cities.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'response_format' => array(
                'type' => 'json_schema',
                'json_schema' => array(
                    'name' => 'city_information',
                    'strict' => true,
                    'schema' => $response_schema
                )
            ),
            'temperature' => 0.7
        );

        // Make the API call to OpenAI
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode($payload),
            'timeout' => 30 // 30 seconds timeout
        ));

        // Handle potential errors
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to connect to OpenAI API: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON response from OpenAI');
        }

        if (isset($data['error'])) {
            wp_send_json_error($data['error']['message']);
        }

        // Parse the structured response
        $city_info_json = $data['choices'][0]['message']['content'];
        $city_info = json_decode($city_info_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Failed to parse structured JSON from OpenAI');
        }

        // Send success response with the city information
        wp_send_json_success($city_info);
    }

    /**
     * Callback function to render the admin page
     * Outputs the HTML form and result containers
     */
    public function admin_page_callback() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Generate nonce for security
        $nonce = wp_create_nonce('openai_structured_nonce');

        // Output the page content
        echo '<div class="wrap">';
        echo '<div class="openai-structured-container">';
        echo '<h1>' . __('OpenAI Structured Output Example', 'openai-structured-output') . '</h1>';

        echo '<input type="text" id="openai-api-key" placeholder="' . __('Enter your OpenAI API key', 'openai-structured-output') . '" value="" autocomplete="off" data-form-type="other">';
        echo '<input type="text" id="openai-prompt" placeholder="' . __('Ask about a city (e.g., \'Tell me about Paris\')', 'openai-structured-output') . '" value="Tell me about London">';

        echo '<button id="openai-send-btn" type="submit">' . __('Get City Information', 'openai-structured-output') . '</button>';

        // Hidden nonce field for AJAX security
        echo '<input type="hidden" id="openai_nonce" value="' . esc_attr($nonce) . '">';

        // Result display areas
        echo '<div id="openai-json-output"></div>';
        echo '<div id="openai-result"></div>';

        echo '</div>';
        echo '</div>';
    }
}

// Initialize the plugin
new OpenAI_Structured_Output_Plugin();