# 08 WordPress Scripts Plugin

## What is @wordpress/scripts

@wordpress/scripts (or wp-scripts) is a collection of pre-configured build tools (like Webpack, Babel, ESLint) and scripts for modern WordPress development, especially for plugins and themes using the Block Editor (Gutenberg), simplifying the process of compiling modern JS/JSX/Sass into browser-ready code, linting, and managing assets without complex manual setup. It handles tasks like transforming ESNext/JSX, bundling, minifying, and provides commands (like build, start) in your package.json for a streamlined workflow, acting similarly to react-scripts. 

See `EXPLANATION-JS-BUNDLE-INECTION.md` for how JS Frameworks are rendered.

## Table of Contents

- [Overview](#overview)
- [What is @wordpress/scripts?](#what-is-wordpressscripts)
- [How This Plugin Works](#how-this-plugin-works)
- [Architecture Deep Dive](#architecture-deep-dive)
- [Installation](#installation)
- [Development Workflow](#development-workflow)
- [File Structure](#file-structure)
- [Usage](#usage)
- [REST API](#rest-api)

---

## Overview

This plugin demonstrates a complete WordPress plugin architecture that uses modern JavaScript tooling through `@wordpress/scripts`. It creates a counter application in the WordPress admin that can save and load data via the WordPress REST API.

**Key Features:**
- React-based admin interface
- WordPress REST API integration
- Modern build system with webpack
- Hot Module Replacement (HMR) during development
- Internationalization (i18n) ready
- WordPress coding standards compliance

---

## What is @wordpress/scripts?

`@wordpress/scripts` is a collection of reusable scripts for WordPress development, maintained by the WordPress core team. It provides:

### 1. **Zero Configuration Build System**
- Pre-configured webpack setup
- Babel transpilation for modern JavaScript
- SCSS/CSS processing
- Production optimization (minification, tree-shaking)
- Development server with hot reload

### 2. **WordPress Integration**
- Automatic dependency extraction
- Generates `index.asset.php` with dependencies and version hash
- Built-in WordPress package support (@wordpress/element, @wordpress/i18n, etc.)

### 3. **Development Tools**
- ESLint for JavaScript linting
- Stylelint for CSS/SCSS linting
- Prettier for code formatting
- Jest for unit testing
- Puppeteer for end-to-end testing

### 4. **Why Use @wordpress/scripts?**
- **Consistency:** Same tooling used by WordPress core and Gutenberg
- **Maintenance:** Automatically updated by the WordPress team
- **Best Practices:** Enforces WordPress coding standards
- **No Configuration:** Works out of the box, no webpack config needed

---

## How This Plugin Works

### The Big Picture

```
┌─────────────────────────────────────────────────────────────┐
│                    WordPress Admin                          │
│  ┌───────────────────────────────────────────────────────┐  │
│  │           Admin Menu: "08 @WP SCRIPTS"                │  │
│  │  ┌─────────────────────────────────────────────────┐  │  │
│  │  │         wp-admin-page.php Template             │  │  │
│  │  │  <div id="my-custom-app-root"></div>           │  │  │
│  │  └─────────────────────────────────────────────────┘  │  │
│  │                         ↓                             │  │
│  │  ┌─────────────────────────────────────────────────┐  │  │
│  │  │      React App (compiled from src/index.js)     │  │  │
│  │  │  • Counter functionality                        │  │  │
│  │  │  • State management with hooks                  │  │  │
│  │  │  • REST API communication                       │  │  │
│  │  └─────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↕ (AJAX/REST API)
┌─────────────────────────────────────────────────────────────┐
│                    REST API Endpoints                       │
│  • POST /wp-json/my-plugin/v1/save                         │
│  • GET  /wp-json/my-plugin/v1/get                          │
│                            ↕                                │
│                 WordPress Options Table                     │
│            (stores 'my_plugin_count')                       │
└─────────────────────────────────────────────────────────────┘
```

---

## Architecture Deep Dive

### 1. **Plugin Entry Point: `wp-wordpress-scripts.php`**

This is the main plugin file that WordPress loads. It handles:

#### a. Plugin Registration
```php
/**
 * Plugin Name: ✅ 08 UDEMY @wordpress/scripts
 * Description: A custom plugin with JS app built using @wordpress/scripts
 * Version: 1.0.0
 * Author: Craig West
 * Text Domain: my-@wp-plugin
 */
```

#### b. Admin Menu Creation
```php
add_action('admin_menu', 'my_plugin_add_admin_menu');

function my_plugin_add_admin_menu() {
    add_menu_page(
        '@wordpress/scripts App',      // Page title
        '08 @WP SCRIPTS',              // Menu title
        'manage_options',              // Capability required
        'my-custom-app',               // Menu slug
        'my_plugin_render_admin_page', // Callback function
        'dashicons-admin-generic',     // Icon
        3.8                            // Position
    );
}
```

**What happens:** Creates a new menu item in the WordPress admin sidebar. When clicked, it renders the admin page template.

#### c. Script and Style Enqueueing
```php
add_action('admin_enqueue_scripts', 'my_plugin_enqueue_scripts');

function my_plugin_enqueue_scripts($hook) {
    // Only load on our plugin page
    if ($hook !== 'toplevel_page_my-custom-app') {
        return;
    }

    // Load the asset file (generated by @wordpress/scripts)
    $asset_file = include MY_PLUGIN_PATH . 'build/index.asset.php';

    // Enqueue JavaScript
    wp_enqueue_script(
        'my-custom-app',
        MY_PLUGIN_URL . 'build/index.js',
        $asset_file['dependencies'],  // Auto-detected dependencies
        $asset_file['version'],        // Cache-busting hash
        true
    );

    // Enqueue styles
    wp_enqueue_style(
        'my-custom-app-style',
        MY_PLUGIN_URL . 'build/style-index.css',
        [],
        $asset_file['version']
    );

    // Pass data to JavaScript
    wp_localize_script('my-custom-app', 'myPluginData', [
        'apiUrl' => rest_url('my-plugin/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'currentCount' => get_option('my_plugin_count', 0),
    ]);
}
```

**Key Points:**
- **Conditional Loading:** Scripts only load on the plugin's admin page (performance optimization)
- **Asset File:** `build/index.asset.php` is auto-generated by @wordpress/scripts and contains:
  - Dependencies array: `['react', 'wp-element', 'wp-i18n']`
  - Version hash for cache-busting
- **wp_localize_script:** Makes PHP data available to JavaScript via `window.myPluginData`

#### d. REST API Registration
```php
add_action('rest_api_init', 'my_plugin_register_routes');

function my_plugin_register_routes() {
    // Save endpoint
    register_rest_route('my-plugin/v1', '/save', [
        'methods' => 'POST',
        'callback' => 'my_plugin_save_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);

    // Get endpoint
    register_rest_route('my-plugin/v1', '/get', [
        'methods' => 'GET',
        'callback' => 'my_plugin_get_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);
}
```

**What happens:** Creates custom REST API endpoints at:
- `https://yoursite.com/wp-json/my-plugin/v1/save`
- `https://yoursite.com/wp-json/my-plugin/v1/get`

**Security:** Permission callback ensures only administrators can access these endpoints.

### 2. **Admin Page Template: `includes/wp-admin-page.php`**

Simple PHP template that provides the mounting point for the React app:

```php
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description">
        This is a custom JavaScript application built with @wordpress/scripts
    </p>
    <div id="my-custom-app-root"></div>  <!-- React app mounts here -->
</div>
```

### 3. **React Application: `src/index.js`**

The heart of the plugin's functionality. Built with React and WordPress components.

#### a. Imports
```javascript
import { createRoot } from '@wordpress/element';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './style.scss';
```

**What's happening:**
- `@wordpress/element`: WordPress's version of React (uses actual React under the hood)
- `@wordpress/i18n`: Internationalization functions for translation
- `./style.scss`: SCSS file that gets compiled to CSS by @wordpress/scripts

#### b. State Management
```javascript
const App = () => {
    const [count, setCount] = useState(0);           // Counter value
    const [message, setMessage] = useState('');       // User feedback messages
    const [messageType, setMessageType] = useState('success'); // Message styling
    const [isLoading, setIsLoading] = useState(false); // Loading state

    // Load initial count from WordPress on mount
    useEffect(() => {
        if (window.myPluginData && window.myPluginData.currentCount) {
            setCount(parseInt(window.myPluginData.currentCount));
        }
    }, []);
```

**Key Points:**
- Uses React hooks for state management
- `useEffect` loads initial data from `window.myPluginData` (passed via `wp_localize_script`)
- Loading state prevents duplicate requests

#### c. Counter Functions
```javascript
const handleIncrement = () => {
    setCount(count + 1);
    setMessage('');
};

const handleDecrement = () => {
    setCount(Math.max(0, count - 1)); // Prevents negative numbers
    setMessage('');
};

const handleReset = () => {
    setCount(0);
    setMessage('');
};
```

#### d. API Communication
```javascript
const handleSave = async () => {
    setIsLoading(true);
    setMessage('');

    try {
        const response = await fetch(`${window.myPluginData.apiUrl}save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.myPluginData.nonce, // WordPress nonce for security
            },
            body: JSON.stringify({ count }),
        });

        const data = await response.json();

        if (response.ok) {
            setMessage(__('Saved successfully!', 'my-custom-plugin'));
            setMessageType('success');
        } else {
            setMessage(__('Error saving data', 'my-custom-plugin'));
            setMessageType('error');
        }
    } catch (error) {
        setMessage(__('Network error occurred', 'my-custom-plugin'));
        setMessageType('error');
        console.error('Save error:', error);
    } finally {
        setIsLoading(false);
    }
};
```

**Security Notes:**
- Uses WordPress nonce (`X-WP-Nonce` header) for CSRF protection
- All strings wrapped in `__()` for internationalization
- Error handling for network failures

#### e. React Mount
```javascript
document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('my-custom-app-root');
    
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(<App />);
    }
});
```

**What happens:** 
1. Waits for DOM to load
2. Finds the root element created in `wp-admin-page.php`
3. Creates a React root and renders the app

### 4. **Build Process**

When you run `npm run build` or `npm start`:

#### Input: `src/index.js`
```javascript
import { createRoot } from '@wordpress/element';
import './style.scss';
// ... React component code
```

#### @wordpress/scripts Processing:
1. **Webpack Bundling:** Combines all imports into a single file
2. **Babel Transpilation:** Converts modern JS (ES6+, JSX) to browser-compatible code
3. **Dependency Extraction:** Identifies WordPress dependencies (wp-element, wp-i18n)
4. **SCSS Compilation:** Converts SCSS to CSS
5. **Minification:** Compresses code for production (build only)
6. **Source Maps:** Generates source maps for debugging (start only)

#### Output: `build/` directory
```
build/
├── index.js              # Compiled JavaScript
├── index.asset.php       # Dependencies and version
└── style-index.css       # Compiled styles
```

#### `build/index.asset.php` Example:
```php
<?php return array(
    'dependencies' => array('react', 'wp-element', 'wp-i18n'),
    'version' => '808aab6c2602e702e863'
);
```

**Why this matters:**
- WordPress automatically loads dependencies (React, wp-element, etc.)
- Version hash changes on every build (cache-busting)
- No manual dependency management needed

### 5. **Data Flow**

#### Saving Data Flow:
```
User clicks "Save to Database"
    ↓
handleSave() function executed
    ↓
Fetch POST to /wp-json/my-plugin/v1/save
    ↓
my_plugin_save_data() in PHP receives request
    ↓
update_option('my_plugin_count', $count) saves to database
    ↓
Returns JSON response { success: true, count: X }
    ↓
React updates UI with success message
```

#### Loading Data Flow:
```
User clicks "Load from Database"
    ↓
handleLoad() function executed
    ↓
Fetch GET to /wp-json/my-plugin/v1/get
    ↓
my_plugin_get_data() in PHP receives request
    ↓
get_option('my_plugin_count', 0) retrieves from database
    ↓
Returns JSON response { success: true, count: X }
    ↓
React updates state with loaded count
```

---

## Installation

### Prerequisites
- WordPress 5.8 or higher
- Node.js 14 or higher
- npm or yarn

### Steps

1. **Clone or download** the plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone <your-repo-url> 08-wp-wordpress-scripts
   ```

2. **Navigate** to the plugin directory:
   ```bash
   cd 08-wp-wordpress-scripts
   ```

3. **Install dependencies:**
   ```bash
   npm install
   ```
   This installs `@wordpress/scripts` and all WordPress packages.

4. **Build the plugin:**
   ```bash
   npm run build
   ```
   This compiles `src/index.js` into production-ready files in `build/`.

5. **Activate** the plugin in WordPress admin panel:
   - Go to Plugins → Installed Plugins
   - Find "✅ 08 UDEMY @wordpress/scripts"
   - Click "Activate"

---

## Development Workflow

### Start Development Server (Recommended)
```bash
npm start
```

**What happens:**
- Starts webpack dev server with hot module replacement
- Watches for changes in `src/` files
- Automatically recompiles when you save files
- Provides faster builds (no minification)
- Includes source maps for debugging

**Your workflow:**
1. Run `npm start`
2. Edit files in `src/`
3. Refresh WordPress admin page to see changes
4. Leave `npm start` running while developing

### Build for Production
```bash
npm run build
```

**What happens:**
- Creates optimized, minified production build
- Enables tree-shaking (removes unused code)
- Smaller file sizes
- No source maps

**When to use:** Before deploying to production or committing.

### Other Commands

#### Lint JavaScript
```bash
npm run lint:js
```
Checks JavaScript against WordPress coding standards.

#### Lint CSS/SCSS
```bash
npm run lint:css
```
Checks styles against WordPress CSS standards.

#### Format Code (Auto-fix)
```bash
npm run format
```
Automatically formats code with Prettier.

#### Check Node Version Compatibility
```bash
npm run check-engines
```

#### Update WordPress Packages
```bash
npm run packages-update
```

---

## File Structure

```
08-wp-wordpress-scripts/
│
├── wp-wordpress-scripts.php    # Main plugin file (entry point)
│   • Plugin header metadata
│   • Admin menu registration
│   • Script/style enqueueing
│   • REST API routes
│   • Activation/deactivation hooks
│
├── package.json                 # Node.js configuration
│   • Dependencies (@wordpress/scripts, @wordpress/element)
│   • NPM scripts (build, start, lint, etc.)
│   • Plugin metadata
│
├── README.md                    # This file
│
├── includes/
│   └── wp-admin-page.php       # Admin page template
│       • HTML wrapper for React app
│       • Provides mounting point (#my-custom-app-root)
│
├── src/                         # Source files (you edit these)
│   ├── index.js                # React application entry point
│   │   • React components
│   │   • State management
│   │   • API calls
│   │   • App rendering
│   │
│   └── style.scss              # SCSS styles
│       • Component styling
│       • Admin interface customization
│
└── build/                       # Compiled files (auto-generated)
    ├── index.js                # Compiled JavaScript (don't edit)
    ├── index.asset.php         # Dependencies array (don't edit)
    └── style-index.css         # Compiled CSS (don't edit)
```

**Important:** Never edit files in `build/` directory - they are regenerated on every build.

---

## Usage

### For End Users

After activating the plugin:

1. **Access the app:**
   - Go to WordPress admin dashboard
   - Find "08 @WP SCRIPTS" in the sidebar menu
   - Click to open the counter application

2. **Use the counter:**
   - **+ Increment:** Adds 1 to the counter
   - **- Decrement:** Subtracts 1 (minimum 0)
   - **Reset:** Sets counter back to 0

3. **Persist data:**
   - **Save to Database:** Stores current count in WordPress database
   - **Load from Database:** Retrieves saved count

4. **Features:**
   - Counter state persists during page session
   - Saved data loads automatically on page load
   - Visual feedback for all actions
   - Loading states prevent duplicate actions

### For Developers

This plugin serves as a template for building WordPress admin interfaces with React:

1. **Modify the React app** in `src/index.js`
2. **Add styles** in `src/style.scss`
3. **Create new REST endpoints** in `wp-wordpress-scripts.php`
4. **Extend functionality** by adding new WordPress hooks

**Example customizations:**
- Add more complex state management
- Integrate with third-party APIs
- Create multiple admin pages
- Add user settings and preferences
- Build custom post type interfaces

---

## REST API

### Endpoints

#### 1. Save Data
**Endpoint:** `/wp-json/my-plugin/v1/save`

**Method:** `POST`

**Headers:**
```
Content-Type: application/json
X-WP-Nonce: [nonce from wp_create_nonce('wp_rest')]
```

**Request Body:**
```json
{
  "count": 42
}
```

**Response (Success):**
```json
{
  "success": true,
  "count": 42,
  "message": "Data saved successfully"
}
```

**Response (Error):**
```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to do that.",
  "data": { "status": 401 }
}
```

#### 2. Get Data
**Endpoint:** `/wp-json/my-plugin/v1/get`

**Method:** `GET`

**Headers:**
```
X-WP-Nonce: [nonce from wp_create_nonce('wp_rest')]
```

**Response (Success):**
```json
{
  "success": true,
  "count": 42
}
```

### Security

Both endpoints require:
1. **User Capability:** `manage_options` (admin only)
2. **Nonce Verification:** WordPress automatically validates `X-WP-Nonce` header
3. **REST API Authentication:** Uses WordPress's built-in REST API authentication

---

## Key Concepts Explained

### 1. Why @wordpress/scripts?

**Traditional Approach:**
- Manually configure webpack
- Install and configure Babel
- Set up ESLint, Stylelint
- Configure SCSS processing
- Handle WordPress dependencies manually
- Maintain complex build configuration

**With @wordpress/scripts:**
- Zero configuration needed
- All tools pre-configured
- WordPress best practices enforced
- Regular updates from WordPress team
- One command to start: `npm start`

### 2. How WordPress Handles React

WordPress includes React as `wp-element`:
```javascript
// You write:
import { useState } from '@wordpress/element';

// WordPress translates this to:
// Uses the React library bundled with WordPress core
```

**Benefits:**
- No need to bundle React in your plugin
- Smaller file sizes
- Consistent React version across plugins
- Multiple plugins can share the same React instance

### 3. Asset File Generation

The `build/index.asset.php` file is crucial:

```php
<?php return array(
    'dependencies' => array('react', 'wp-element', 'wp-i18n'),
    'version' => '808aab6c2602e702e863'
);
```

**How it's used:**
```php
$asset_file = include 'build/index.asset.php';
wp_enqueue_script(
    'my-app',
    'build/index.js',
    $asset_file['dependencies'], // WordPress loads these automatically
    $asset_file['version']        // Cache-busting
);
```

### 4. WordPress Localization

**PHP Side:**
```php
wp_localize_script('my-custom-app', 'myPluginData', [
    'apiUrl' => rest_url('my-plugin/v1/'),
    'nonce' => wp_create_nonce('wp_rest'),
]);
```

**JavaScript Side:**
```javascript
console.log(window.myPluginData.apiUrl); // "https://site.com/wp-json/my-plugin/v1/"
```

This is how PHP data reaches JavaScript without using inline scripts.

---

## Troubleshooting

### Build files not found
**Error:** "Build files not found. Please run `npm run build`"

**Solution:**
```bash
cd wp-content/plugins/08-wp-wordpress-scripts
npm install
npm run build
```

### Changes not appearing
**Solution:**
1. If using `npm start`: Ensure it's running
2. Clear browser cache
3. Rebuild: `npm run build`

### Permission errors with REST API
**Error:** "Sorry, you are not allowed to do that"

**Causes:**
- Not logged in as administrator
- Nonce expired (refresh page)
- Custom user roles without `manage_options` capability

### Hot reload not working
**Solution:**
1. Stop `npm start`
2. Delete `node_modules/` and `build/`
3. Run `npm install` and `npm start` again

---

## Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **Node.js:** 14 or higher
- **npm:** 6 or higher

---

## Learning Resources

- [@wordpress/scripts Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [@wordpress/element (React) Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-element/)

---

## License

GPL-2.0-or-later

---

## Summary

This plugin demonstrates:
✅ Modern WordPress development with React
✅ Zero-configuration build system
✅ REST API integration
✅ Proper script/style enqueueing
✅ Security best practices (nonces, capability checks)
✅ Internationalization support
✅ Hot module replacement for fast development
✅ WordPress coding standards compliance

Perfect as a starting point for building complex WordPress admin interfaces with modern JavaScript!
