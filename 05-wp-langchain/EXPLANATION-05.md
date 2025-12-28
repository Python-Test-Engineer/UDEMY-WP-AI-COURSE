# Integrating LangChain.js into WordPress Plugins Using @wordpress/scripts

This explainer demonstrates how to create a basic WordPress plugin that integrates LangChain.js, a framework for building applications with large language models (LLMs), using WordPress's official `@wordpress/scripts` package for development and build processes.

## Prerequisites

Before starting, ensure you have:
- Node.js (version 14 or higher) installed
- npm or yarn package manager
- A local WordPress development environment (e.g., Local by Flywheel, MAMP, or Docker)
- Basic knowledge of WordPress plugin development and JavaScript
- An API key for an LLM provider (e.g., OpenAI, as LangChain supports multiple providers)

## Overview

`@wordpress/scripts` is a collection of reusable scripts tailored for WordPress development, providing build tools for modern JavaScript and CSS. We'll use it to bundle our LangChain.js code for use in a WordPress plugin.

LangChain.js allows us to create chains of LLM operations, making it easy to build conversational AI features in WordPress.

## Step 1: Set Up Plugin Structure

Create a basic WordPress plugin structure in your `06-wp-langchain` directory:

```
wp-langchain-integration/
├── wp-langchain-integration.php
├── src/
│   ├── index.js
│   ├── langchain-utils.js
│   └── style.css
├── package.json
└── README.md
```

Create the main plugin file first:

**wp-langchain-integration.php**
```php
<?php
/**
 * Plugin Name: WP LangChain Integration
 * Description: A WordPress plugin demonstrating LangChain.js integration
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue our bundled JavaScript
function wpli_enqueue_scripts() {
    wp_enqueue_script(
        'wp-langchain-integration',
        plugin_dir_url(__FILE__) . 'build/index.js',
        array(),
        '1.0.0',
        true
    );

    // Enqueue our bundled CSS
    wp_enqueue_style(
        'wp-langchain-integration',
        plugin_dir_url(__FILE__) . 'build/index.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'wpli_enqueue_scripts');
add_action('admin_enqueue_scripts', 'wpli_enqueue_scripts');

// Add admin menu for demonstration
function wpli_add_admin_menu() {
    add_menu_page(
        'LangChain Demo',
        'LangChain Demo',
        'manage_options',
        'wp-langchain-demo',
        'wpli_admin_page'
    );
}
add_action('admin_menu', 'wpli_add_admin_menu');

function wpli_admin_page() {
    ?>
    <div class="wrap">
        <h1>LangChain.js Integration Demo</h1>
        <div id="langchain-demo-app"></div>
    </div>
    <?php
}
```

## Step 2: Initialize Package.json and Install Dependencies

Create a `package.json` file with the necessary dependencies:

**package.json**
```json
{
  "name": "wp-langchain-integration",
  "version": "1.0.0",
  "description": "WordPress plugin with LangChain.js integration using @wordpress/scripts",
  "main": "src/index.js",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "check-engines": "wp-scripts check-engines",
    "check-licenses": "wp-scripts check-licenses",
    "format": "wp-scripts format",
    "lint:css": "wp-scripts lint-style",
    "lint:js": "wp-scripts lint-js",
    "lint:pkg-json": "wp-scripts lint-pkg-json",
    "packages-update": "wp-scripts packages-update",
    "plugin-zip": "wp-scripts plugin-zip"
  },
  "dependencies": {
    "@langchain/openai": "^0.0.14",
    "langchain": "^0.1.25"
  },
  "devDependencies": {
    "@wordpress/scripts": "^26.18.0"
  },
  "engines": {
    "node": ">=14.0.0",
    "npm": ">=6.14.4"
  }
}
```

Install the dependencies:

```bash
npm install
```

## Step 3: Create LangChain.js Utilities

Create `src/langchain-utils.js` to encapsulate our LangChain logic:

**src/langchain-utils.js**
```javascript
import { OpenAI } from '@langchain/openai';
import { PromptTemplate } from 'langchain/prompts';
import { LLMChain } from 'langchain/chains';

/**
 * Initialize OpenAI LLM with API key
 * @param {string} apiKey - OpenAI API key
 * @returns {OpenAI} Configured OpenAI instance
 */
export function initOpenAI(apiKey) {
    return new OpenAI({
        openAIApiKey: apiKey,
        modelName: 'gpt-3.5-turbo',
        temperature: 0.7,
    });
}

/**
 * Create a simple prompt template for content generation
 * @returns {PromptTemplate} Configured prompt template
 */
export function createPromptTemplate() {
    return PromptTemplate.fromTemplate(`
Generate a {length} response about {topic} in the style of {style}.

Topic: {topic}
Length: {length}
Style: {style}

Response:`);
}

/**
 * Create a chain for content generation
 * @param {OpenAI} llm - The language model instance
 * @param {PromptTemplate} prompt - The prompt template
 * @returns {LLMChain} Configured LLM chain
 */
export function createContentChain(llm, prompt) {
    return new LLMChain({
        llm: llm,
        prompt: prompt,
    });
}

/**
 * Generate content using the LangChain
 * @param {LLMChain} chain - The LLM chain
 * @param {Object} inputs - Input parameters for the chain
 * @returns {Promise<string>} Generated content
 */
export async function generateContent(chain, inputs) {
    try {
        const result = await chain.call(inputs);
        return result.text;
    } catch (error) {
        console.error('Error generating content:', error);
        throw error;
    }
}
```

## Step 4: Implement Main Application Logic

Create the main JavaScript entry point:

## Step 4a: Create CSS Stylesheet

Create `src/style.css` to style our demo interface:

**src/style.css**
```css
/* Demo App Container Styles */
.langchain-demo-container {
  max-width: 600px;
  margin: 0 auto;
}

.langchain-demo-container h2 {
  margin-bottom: 1.5rem;
}

/* Form Elements */
.demo-form-group {
  margin-bottom: 1rem;
}

.demo-form-group label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: bold;
}

.demo-input,
.demo-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.demo-input:focus,
.demo-select:focus {
  outline: none;
  border-color: #007cba;
  box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
}

/* Button Styles */
.demo-button {
  background: #007cba;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.2s;
}

.demo-button:hover {
  background: #006ba1;
}

.demo-button:disabled {
  background: #cccccc;
  cursor: not-allowed;
}

/* Result Area */
.demo-result {
  margin-top: 1rem;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  min-height: 200px;
  white-space: pre-wrap;
  background: #f9f9f9;
  font-family: monospace;
  line-height: 1.5;
}

.demo-result.error {
  border-color: #dc3232;
  background: #ffe6e6;
}
```

## Step 4b: Implement Main Application Logic

Create the main JavaScript entry point that imports the CSS and JavaScript utilities:

**src/index.js**
```javascript
import { initOpenAI, createPromptTemplate, createContentChain, generateContent } from './langchain-utils';
import './style.css';

document.addEventListener('DOMContentLoaded', () => {
    const demoApp = document.getElementById('langchain-demo-app');

    if (!demoApp) {
        return; // Not on the demo page
    }

    // Create the demo UI using CSS classes
    demoApp.innerHTML = `
        <div class="langchain-demo-container">
            <h2>LangChain.js Content Generator Demo</h2>

            <div class="demo-form-group">
                <label for="api-key">OpenAI API Key:</label>
                <input type="password" id="api-key" class="demo-input" placeholder="sk-...">
            </div>

            <div class="demo-form-group">
                <label for="topic">Topic:</label>
                <input type="text" id="topic" class="demo-input" value="WordPress development">
            </div>

            <div class="demo-form-group">
                <label for="length">Length:</label>
                <select id="length" class="demo-select">
                    <option value="short paragraph">Short paragraph</option>
                    <option value="medium article">Medium article</option>
                    <option value="detailed explanation">Detailed explanation</option>
                </select>
            </div>

            <div class="demo-form-group">
                <label for="style">Style:</label>
                <select id="style" class="demo-select">
                    <option value="professional">Professional</option>
                    <option value="conversational">Conversational</option>
                    <option value="technical">Technical</option>
                </select>
            </div>

            <button id="generate-btn" class="demo-button">
                Generate Content
            </button>

            <div id="result" class="demo-result"></div>
        </div>
    `;

    const apiKeyInput = document.getElementById('api-key');
    const topicInput = document.getElementById('topic');
    const lengthSelect = document.getElementById('length');
    const styleSelect = document.getElementById('style');
    const generateBtn = document.getElementById('generate-btn');
    const resultDiv = document.getElementById('result');

    // Initialize LangChain components (but we'll do this on button click to use the provided API key)
    let llm, prompt, chain;

    generateBtn.addEventListener('click', async () => {
        const apiKey = apiKeyInput.value.trim();
        const topic = topicInput.value.trim();

        if (!apiKey) {
            resultDiv.textContent = 'Please provide an OpenAI API key.';
            return;
        }

        if (!topic) {
            resultDiv.textContent = 'Please provide a topic.';
            return;
        }

        generateBtn.disabled = true;
        generateBtn.textContent = 'Generating...';
        resultDiv.textContent = 'Generating content...';

        try {
            // Initialize components with the provided API key
            llm = initOpenAI(apiKey);
            prompt = createPromptTemplate();
            chain = createContentChain(llm, prompt);

            const inputs = {
                topic: topic,
                length: lengthSelect.value,
                style: styleSelect.value,
            };

            const generatedContent = await generateContent(chain, inputs);
            resultDiv.textContent = generatedContent;
        } catch (error) {
            resultDiv.textContent = `Error: ${error.message}`;
        } finally {
            generateBtn.disabled = false;
            generateBtn.textContent = 'Generate Content';
        }
    });
});
```

## Step 5: Build the Plugin

Use `@wordpress/scripts` to build the JavaScript and CSS bundles:

```bash
npm run build
```

This will create a `build/` directory with the bundled JavaScript file (`build/index.js`) and the processed CSS file (`build/index.css`) that can be enqueued by WordPress. The CSS is automatically processed through Webpack, allowing you to use modern CSS features and ensuring optimal performance.

The `src/style.css` file is imported into `src/index.js` using ES6 modules:

```javascript
import './style.css';
```

This import statement tells `@wordpress/scripts` to include the CSS in the build process. When the bundled JavaScript is loaded in WordPress, the CSS is automatically included, providing styles for your interface elements without requiring separate enqueueing for development builds.

During development, use:

```bash
npm start
```

This starts the development server with hot reloading for both JavaScript and CSS files.

## Step 6: Testing the Plugin

1. Upload the plugin to your WordPress installation:
   - Create a zip file of the plugin directory (excluding `node_modules` and other development files)
   - Or copy the directory directly to `wp-content/plugins/`

2. Activate the plugin in the WordPress admin

3. Navigate to the "LangChain Demo" page in the admin menu

4. Enter your OpenAI API key and generate content

## Important Security Considerations

- Never hardcode API keys in your plugin code
- Store sensitive configuration using WordPress options or environment variables
- Implement proper input validation and sanitization
- Consider rate limiting for API calls to avoid abuse

## Extending the Example

This basic example can be extended in several ways:

1. **Add more chain types**: Use different LangChain abstractions like `SequentialChain` or `RouterChain`
2. **Integrate with WordPress content**: Generate content based on existing posts or pages
3. **Add memory**: Implement conversation memory for multi-turn interactions
4. **Support multiple models**: Allow users to choose between different LLM providers
5. **Create custom tools**: Implement WordPress-specific tools that can be used by LangChain agents

## Troubleshooting

1. **Build errors**: Ensure all dependencies are installed and you're using a compatible Node.js version
2. **API errors**: Verify your OpenAI API key and check your usage quota
3. **WordPress errors**: Check that the plugin is activated and there are no PHP errors in the main plugin file
4. **Console errors**: Use browser developer tools to inspect JavaScript errors

## Resources

- [LangChain.js Documentation](https://js.langchain.com)
- [WordPress Scripts Documentation](https://developer.wordpress.org/block-editor/packages/packages-scripts/)
- [OpenAI API Documentation](https://platform.openai.com/docs)
- [WordPress Plugin Development Handbook](https://developer.wordpress.org/plugins/)

This setup provides a solid foundation for integrating LangChain.js into WordPress plugins, enabling you to leverage the power of large language models within your WordPress applications.
