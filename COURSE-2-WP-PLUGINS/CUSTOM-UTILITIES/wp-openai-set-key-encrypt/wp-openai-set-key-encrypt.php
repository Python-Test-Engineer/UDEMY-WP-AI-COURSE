<?php
/**
 * Plugin Name: ✅ SET OPENAI API KEY
 * Plugin URI: https://example.com
 * Description: Securely store and retrieve OpenAI API keys for use across all plugins
 * Version: w1.0.0
 * Author: Craig West
 * License: GPL v2 or later
 * Text Domain: secure-openai-key
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Require the class file
require_once plugin_dir_path(__FILE__) . 'classes/class-openai-key.php';

// Initialize the plugin
Secure_OpenAI_Key_Manager::get_instance();

/**
 * CONVENIENCE FUNCTIONS FOR OTHER PLUGINS
 * ========================================
 */

/**
 * Get the OpenAI API key
 * 
 * @return string|false The API key or false if not set
 */
function get_openai_api_key() {
    return Secure_OpenAI_Key_Manager::get_api_key();
}

/**
 * Check if OpenAI API key is configured
 * 
 * @return bool
 */
function has_openai_api_key() {
    return Secure_OpenAI_Key_Manager::has_api_key();
}

/**
 * Get masked OpenAI API key (for display)
 * 
 * @return string|false The masked API key or false if not set
 */
function get_masked_openai_api_key() {
    return Secure_OpenAI_Key_Manager::get_masked_api_key();
}
