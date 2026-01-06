# Basic Agent Plugin - Technical Explanation

## Overview
This WordPress plugin provides an OpenAI integration with a modular architecture. It features API key management with PHP-only show/hide toggle, an AJAX-based query interface with JavaScript, and custom styling. The plugin uses a structured folder organization separating concerns into functions, templates, and assets.

---

## Plugin Architecture

### File Structure
```
02-wp-basic-agent/
├── wp-basic-agent.php              # Main plugin file
├── index.php                       # Security file
├── README.md                       # Documentation
│
├── admin/
│   ├── functions/                  # PHP functionality
│   │   ├── admin-hooks.php         # Admin menu registration
│   │   ├── agent.php               # OpenAI API handler (AJAX)
│   │   ├── render-admin-page.php   # Page renderer
│   │   ├── enqueue_assets.php      # Asset loading
│   │   ├── basic_page_activate.php # Activation hook
│   │   └── basic_page_deactivate.php # Deactivation hook
│   │
│   ├── templates/                  # HTML templates
│   │   └── openai-settings-template.php
│   │
│   ├── includes/                   # Reusable includes
│   │   └── inc-system-prompt.php   # AI system prompt
│   │
│   └── assets/                     # Frontend assets
│       ├── css/
│       │   └── admin-styles.css    # Custom styling
│       └── js/
│           └── openai.js           # AJAX form handling
│
└── classes/                        # Reserved for future OOP
    └── .gitkeep
```

---

## Main Plugin File: `wp-basic-agent.php`

### Plugin Header
```php
/*
Plugin Name: ✅ BASIC AGENT
Description: A basic Agent plugin for WordPress that integrates with OpenAI's API.
Version: 1.1.0
Author: Craig West
*/
```

### Security Check
```php
if (!defined('ABSPATH')) {
    exit;
}
```
- Prevents direct file access
- WordPress security standard

### API Key Management Functions

#### 1. Settings Registration
```php
function wp_basic_agent_register_settings() {
    register_setting('wp_basic_agent_settings', 'wp_basic_agent_api_key');
}
add_action('admin_init', 'wp_basic_agent_register_settings');
```

**Purpose:** Registers the API key setting with WordPress
**Storage:** WordPress options table
**Option name:** `wp_basic_agent_api_key`

#### 2. Form Processing (PHP-Only Show/Hide)
```php
function wp_basic_agent_process_forms()
```

**Handles Two Form Submissions:**

##### A. Show/Hide Toggle (Pure PHP)
```php
if (isset($_POST['toggle_api_key_visibility']) && 
    check_admin_referer('wp_basic_agent_toggle_nonce'))
```

**How It Works:**
1. User clicks "Show" or "Hide" button
2. Form submits to server (POST request)
3. PHP checks nonce for security
4. Reads current visibility state from transient
5. Toggles state (visible ↔ hidden)
6. Redirects back to page
7. Input field type changes on reload

**Key Technology:**
- **Transients:** `wp_basic_agent_show_key_` + user ID
- **Expiration:** 1 hour
- **User-specific:** Each user has their own visibility state
- **No JavaScript:** Pure server-side logic

##### B. API Key Save
```php
if (isset($_POST['save_basic_agent_api_key']) && 
    check_admin_referer('wp_basic_agent_save_key_nonce'))
```

**Process:**
1. Verifies nonce (security)
2. Sanitizes API key input
3. Saves to WordPress options
4. Stores success message in 30-second transient
5. Redirects to prevent resubmission

---

## Admin Functions

### 1. Admin Hooks (`admin/functions/admin-hooks.php`)

```php
function wp_basic_agent_settings_menu() {
    add_menu_page(
        'BASIC AGENT',              // Page title
        'BASIC AGENT',              // Menu title
        'manage_options',           // Capability
        'basic-agent-settings',     // Menu slug
        'wp_basic_agent_render_settings_page', // Callback
        'dashicons-admin-tools',    // Icon
        4                           // Position
    );
}
```

**Creates admin menu item:**
- Appears in WordPress sidebar
- Position 4 (near top)
- Tool icon
- Requires admin permissions

---

### 2. Agent (AJAX Handler) (`admin/functions/agent.php`)

```php
function wp_basic_agent_openai_proxy()
```

**Purpose:** Handles AJAX requests from JavaScript to OpenAI API

#### Security Checks
```php
// 1. Verify nonce
if (!wp_verify_nonce($_POST['nonce'], 'openai_key_nonce')) {
    wp_send_json_error(array('message' => 'Security check failed'));
}

// 2. Check user capabilities
if (!current_user_can('manage_options')) {
    wp_send_json_error(array('message' => 'Unauthorized access'));
}
```

#### Get API Key
```php
$api_key = get_option('wp_basic_agent_api_key', '');
```
- Retrieves from WordPress options
- No longer depends on external plugin

#### System Prompt Integration
```php
include plugin_dir_path(__FILE__) . '../includes/inc-system-prompt.php';
```
- Loads custom system prompt
- Sets AI behavior and response format

#### OpenAI API Request
```php
$body = array(
    'model' => 'gpt-4o-mini',
    'messages' => array(
        array('role' => 'system', 'content' => $system_prompt),
        array('role' => 'user', 'content' => $prompt)
    ),
    'max_tokens' => 1024
);

$response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    ),
    'body' => json_encode($body),
    'timeout' => 30
));
```

**Key Features:**
- **Two messages:** System (instructions) + User (query)
- **Max tokens:** 1024 (response length limit)
- **Timeout:** 30 seconds
- **WordPress HTTP API:** Uses `wp_remote_post()`

#### Response Handling
```php
if ($response_code !== 200) {
    $error_message = isset($data['error']['message']) 
        ? $data['error']['message'] 
        : 'Unknown error';
    wp_send_json_error(array('message' => $error_message));
}

wp_send_json_success($data);
```

**Returns:**
- **Success:** Full OpenAI response (JavaScript extracts message)
- **Error:** Error message with details

#### AJAX Action Hook
```php
add_action('wp_ajax_openai_proxy', 'wp_basic_agent_openai_proxy');
```
- Registers AJAX endpoint
- Only available to logged-in users
- Action name: `openai_proxy`

---

### 3. Render Admin Page (`admin/functions/render-admin-page.php`)

```php
function wp_basic_agent_render_settings_page() {
    settings_errors('wp_basic_agent_messages');
    include plugin_dir_path(__FILE__) . '../templates/openai-settings-template.php';
}
```

**Simple and clean:**
- Displays WordPress settings errors
- Includes template file
- Separates logic from presentation

---

### 4. Enqueue Assets (`admin/functions/enqueue_assets.php`)

Let me read this file first:

```php
// Loads CSS and JavaScript files
// Passes PHP variables to JavaScript
```

---

## Settings Template (`admin/templates/openai-settings-template.php`)

### State Management
```php
$api_key = get_option('wp_basic_agent_api_key', '');
$show_key = get_transient('wp_basic_agent_show_key_' . get_current_user_id());
$message = get_transient('wp_basic_agent_message');
```

**Variables:**
- **$api_key:** Saved API key from options
- **$show_key:** Visibility state (transient, 1 hour)
- **$message:** Success message (transient, 30 seconds)

### API Configuration Card

#### Success Message Display
```php
<?php if ($message): ?>
    <div class="notice notice-success inline">
        <p><?php echo esc_html($message); ?></p>
    </div>
<?php endif; ?>
```

#### Two Separate Forms

**Form 1: API Key Input & Save**
```php
<form method="post" action="" style="flex: 1; display: flex; gap: 10px;">
    <?php wp_nonce_field('wp_basic_agent_save_key_nonce'); ?>
    
    <input 
        type="<?php echo $show_key ? 'text' : 'password'; ?>" 
        id="wp_basic_agent_api_key" 
        name="wp_basic_agent_api_key" 
        value="<?php echo esc_attr($api_key); ?>" 
        class="regular-text" 
        placeholder="sk-..."
        required
    />
    
    <input type="submit" name="save_basic_agent_api_key" 
           class="button button-primary" 
           value="Save API Key" />
</form>
```

**Form 2: Show/Hide Toggle**
```php
<form method="post" action="">
    <?php wp_nonce_field('wp_basic_agent_toggle_nonce'); ?>
    <button 
        type="submit" 
        name="toggle_api_key_visibility" 
        class="button"
    >
        <?php echo $show_key ? 'Hide' : 'Show'; ?>
    </button>
</form>
```

**Why Two Forms?**
- Each has its own nonce
- Independent security validation
- Prevents nonce expiration errors
- Cleaner separation of concerns

#### Dynamic Input Type
```php
type="<?php echo $show_key ? 'text' : 'password'; ?>"
```
- **Hidden:** `password` type (shows dots)
- **Visible:** `text` type (shows actual key)
- Changes after page reload

#### Status Indicator
```php
<?php if (!empty($api_key)): ?>
    <span style="color: green;">✓ API key is configured and ready to use.</span>
<?php else: ?>
    <span style="color: #d63638;">⚠ Please enter and save your OpenAI API key.</span>
<?php endif; ?>
```

### Test OpenAI API Card

#### AJAX Form
```php
<form id="openai-form">
    <input type="hidden" name="action" value="openai_proxy">
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('openai_key_nonce'); ?>">
    
    <div class="openai-test-interface">
        <input 
            type="text" 
            name="prompt" 
            id="prompt" 
            placeholder="Enter your prompt..." 
            required
        >
        <button type="submit">Send Request</button>
        <span id="loading" class="htmx-indicator" style="display:none;">
            ⏳ Loading...
        </span>
    </div>
</form>
<div id="result"></div>
```

**Key Elements:**
- **Hidden fields:** Action name and nonce
- **Prompt input:** User query
- **Submit button:** Triggers JavaScript
- **Loading indicator:** Shows during request
- **Result div:** Displays AI response

---

## JavaScript (`admin/assets/js/openai.js`)

### Form Submission Handler

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('openai-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
```

**Prevents default form submission** - handles via AJAX instead

### Loading State
```javascript
loading.style.display = 'inline-block';
submitBtn.disabled = true;
resultDiv.innerHTML = '';
```

**User Feedback:**
- Shows loading spinner
- Disables button (prevents double-submit)
- Clears previous results

### AJAX Request
```javascript
const response = await fetch(ajaxurl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action: 'openai_proxy',
        nonce: wpBasicAgent.nonce,
        prompt: prompt
    })
});

const data = await response.json();
```

**Technologies:**
- **Fetch API:** Modern AJAX
- **ajaxurl:** WordPress global variable
- **URLSearchParams:** Proper encoding
- **async/await:** Clean asynchronous code

### Response Handling
```javascript
if (data.success && data.data && data.data.choices && data.data.choices[0]) {
    const message = data.data.choices[0].message.content;
    resultDiv.innerHTML = '<div style="margin-top: 15px; font-size: 1.1em; line-height: 1.8; color: #333;">' + message + '</div>';
} else {
    resultDiv.innerHTML = '<div style="padding: 15px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; color: #721c24; margin-top: 15px;">Error: Unable to get response</div>';
}
```

**Extracts message content:**
- Navigates: `data.data.choices[0].message.content`
- Displays with custom styling
- Shows error if unsuccessful

### Cleanup
```javascript
finally {
    loading.style.display = 'none';
    submitBtn.disabled = false;
}
```
- Always executed
- Hides spinner
- Re-enables button

---

## System Prompt (`admin/includes/inc-system-prompt.php`)

```php
$system_prompt = '
You are a helpful assistant. You always answer in a concise manner. 
You never refuse to answer but do not make things up - just say "I don\'t know". 
You always try to help the user as much as possible. 
Convert markdown to HTML when needed so that output is properly formatted. 
Ensure there is a new line after each sentence.';
```

**Purpose:** Defines AI behavior

**Instructions:**
1. Be helpful and concise
2. Don't make up information
3. Convert markdown to HTML
4. Format with line breaks

**Used in:** `agent.php` as system message

---

## Custom Styling (`admin/assets/css/admin-styles.css`)

### Typography
```css
@import url('https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap');

h1.title {
   color: green;
   font-size: 2.5rem;
   font-weight: 600;
}

h2 {
   color: #667eea;
   font-size: 2rem !important;
}
```

**Custom font:** Raleway
**Color scheme:** Green and purple accents

### Container Overrides
```css
#wpwrap, #wpcontent, #wpbody-content, .wrap {
   max-width: none !important;
   width: 100% !important;
}

.dashboard-container {
   width: 70% !important;
   padding: 30px;
   background: white !important;
   border: 3px solid #bd460f !important;
   border-radius: 16px !important;
}
```

**Overrides WordPress defaults:**
- Removes max-width constraints
- 70% width for main container
- Bold orange border
- Rounded corners

### Dashboard Cards
```css
.dashboard-card {
   background: #fff;
   border-radius: 8px;
   border: 2px solid #099709;
   box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
   padding: 20px;
   margin-bottom: 20px;
}
```

**Green bordered cards:**
- White background
- Subtle shadow
- Consistent spacing

### Result Display
```css
#result {
   font-size: 1.50rem;
   margin-top: 20px;
   padding: 15px;
   padding-top: 25px;
   background: antiquewhite;
   border-radius: 15px;
   border: 3px solid blue;
   min-height: 100px;
   line-height: 1.25;
}
```

**Distinctive result area:**
- Antique white background
- Blue border
- Large font
- Minimum height

### Loading Animation
```css
@keyframes rotation {
   from { transform: rotate(0deg); }
   to { transform: rotate(359deg); }
}

.htmx-request button[type="submit"] {
   opacity: 0.6;
   cursor: wait;
}
```

**Visual feedback:**
- Rotating animation
- Faded button during load
- Wait cursor

---

## Data Flow Diagrams

### Show/Hide Toggle Flow (Pure PHP)
```
User clicks "Show" or "Hide" button
    ↓
Form submits (POST) with wp_basic_agent_toggle_nonce
    ↓
wp_basic_agent_process_forms() in wp-basic-agent.php
    ↓
Verifies nonce and user capability
    ↓
Reads transient: wp_basic_agent_show_key_{user_id}
    ↓
Toggles state (true ↔ false/deleted)
    ↓
Redirects to admin.php?page=basic-agent-settings
    ↓
render_settings_page() executes
    ↓
Template reads new transient value
    ↓
Input type changes (password ↔ text)
    ↓
User sees result
```

### API Query Flow (AJAX)
```
User enters query and clicks "Send Request"
    ↓
JavaScript prevents default form submission
    ↓
Shows loading indicator, disables button
    ↓
JavaScript sends AJAX POST to admin-ajax.php
    ↓
WordPress routes to wp_basic_agent_openai_proxy()
    ↓
Verifies nonce and capabilities
    ↓
Gets API key from options
    ↓
Loads system prompt
    ↓
Sends request to OpenAI API
    ↓
Receives JSON response
    ↓
Sends JSON back to JavaScript
    ↓
JavaScript extracts message content
    ↓
Displays in #result div
    ↓
Hides loading, re-enables button
```

---

## Security Features

### 1. Nonce Verification

**Multiple nonces for different actions:**
```php
// Save API key
check_admin_referer('wp_basic_agent_save_key_nonce')

// Toggle visibility
check_admin_referer('wp_basic_agent_toggle_nonce')

// AJAX request
wp_verify_nonce($_POST['nonce'], 'openai_key_nonce')
```

**Benefits:**
- Prevents CSRF attacks
- Verifies form submissions
- Each action has unique token

### 2. Capability Checks
```php
if (!current_user_can('manage_options')) {
    return; // or wp_send_json_error()
}
```
**Ensures:**
- Only administrators can access
- Prevents unauthorized API usage
- Protects sensitive operations

### 3. Data Sanitization
```php
sanitize_text_field($_POST['wp_basic_agent_api_key'])
sanitize_textarea_field($_POST['prompt'])
esc_html($message)
esc_attr($api_key)
```

**Different functions for different contexts:**
- Input sanitization (save)
- Output escaping (display)
- Prevents XSS attacks

### 4. Direct File Access Prevention
```php
if (!defined('ABSPATH')) {
    exit;
}
```
**In every PHP file:**
- Prevents direct URL access
- WordPress constant check

---

## WordPress Integration Points

### Actions (Hooks)
```php
// Main file
add_action('admin_init', 'wp_basic_agent_register_settings');
add_action('admin_init', 'wp_basic_agent_process_forms');
add_action('admin_enqueue_scripts', 'wp_basic_agent_enqueue_admin_assets');

// Admin hooks
add_action('admin_menu', 'wp_basic_agent_settings_menu');

// AJAX
add_action('wp_ajax_openai_proxy', 'wp_basic_agent_openai_proxy');
```

### WordPress Functions Used

**Settings:**
- `register_setting()`
- `get_option()` / `update_option()`

**Transients:**
- `get_transient()` / `set_transient()`
- `delete_transient()`

**Admin:**
- `add_menu_page()`
- `settings_errors()`

**HTTP:**
- `wp_remote_post()`
- `wp_remote_retrieve_response_code()`
- `wp_remote_retrieve_body()`

**Security:**
- `wp_nonce_field()` / `check_admin_referer()`
- `wp_create_nonce()` / `wp_verify_nonce()`
- `current_user_can()`

**Output:**
- `wp_send_json_success()` / `wp_send_json_error()`
- `wp_redirect()`

---

## Activation & Deactivation Hooks

### Activation (`admin/functions/basic_page_activate.php`)
```php
function wp_basic_agent_activate() {
    // Initialize default settings
    // Create database tables if needed
    // Set up initial configuration
}
register_activation_hook(__FILE__, 'wp_basic_agent_activate');
```

### Deactivation (`admin/functions/basic_page_deactivate.php`)
```php
function wp_basic_agent_deactivate() {
    // Clean up temporary data
    // Remove transients
    // Optional: preserve settings
}
register_deactivation_hook(__FILE__, 'wp_basic_agent_deactivate');
```

---

## Comparison: PHP-Only vs AJAX Approach

### PHP-Only (Show/Hide Toggle)
**Advantages:**
- No JavaScript required
- Works with JS disabled
- Server-side control
- Simple implementation

**Disadvantages:**
- Requires page reload
- Slower user experience
- Can't update dynamically

### AJAX (Query Interface)
**Advantages:**
- No page reload
- Instant feedback
- Better UX
- Loading indicators

**Disadvantages:**
- Requires JavaScript
- More complex code
- Potential for errors
- Needs error handling

**This plugin uses both:**
- PHP-only for show/hide (demonstration)
- AJAX for queries (better UX)

---

## Installation & Usage

### Installation
1. Upload `02-wp-basic-agent` folder to `wp-content/plugins/`
2. Activate plugin in WordPress admin
3. Find "BASIC AGENT" in admin sidebar

### Configuration
1. Click on "BASIC AGENT" menu
2. Enter your OpenAI API key
3. Click "Save API Key"
4. Use "Show/Hide" to toggle visibility

### Testing
1. Enter a prompt in "Test OpenAI API" section
2. Click "Send Request"
3. Watch loading indicator
4. View AI response below

### Requirements
- WordPress 5.0+
- PHP 7.4+
- Valid OpenAI API key
- Active internet connection
- JavaScript enabled (for AJAX features)

---

## Plugin Naming Conventions

### Function Prefixes
All functions use `wp_basic_agent_` prefix:
- Prevents naming conflicts
- WordPress best practice
- Easy to identify

### Option Names
- `wp_basic_agent_api_key` - Stored API key
- `wp_basic_agent_settings` - Settings group

### Transient Names
- `wp_basic_agent_show_key_{user_id}` - Visibility state
- `wp_basic_agent_message` - Success messages

### Action Names
- `openai_proxy` - AJAX endpoint
- `get_full_openai_key` - Key retrieval (removed in latest version)

---

## Architecture Benefits

### 1. Modular Structure
- Separated into functions, templates, assets
- Easy to locate specific functionality
- Better code organization
- Easier maintenance

### 2. Reusability
- System prompt in separate file
- Template includes can be reused
- Functions can be called from anywhere

### 3. Scalability
- Easy to add new features
- Classes folder reserved for OOP
- Can extend without breaking existing code

### 4. Security
- Multiple layers of validation
- Nonce for every action
- Capability checks everywhere
- Proper sanitization

---

## Key Differences from 01-basic-php-ai-agent

### Architecture
- **01:** Single file plugin
- **02:** Modular folder structure

### Query Interface
- **01:** PHP form submission (reload)
- **02:** AJAX with JavaScript (no reload)

### Show/Hide
- **01:** PHP-only with page reload
- **02:** PHP-only with page reload (same approach)

### Styling
- **01:** Inline CSS (ChatGPT dark mode)
- **02:** External CSS file (custom colors)

### System Prompt
- **01:** None (basic query)
- **02:** Customizable system prompt file

### Response Display
- **01:** Shows immediately after submit
- **02:** AJAX updates DOM dynamically

---

## Future Enhancement Ideas

### Possible Additions
1. **Conversation History**
   - Store queries and responses
   - Display chat-like interface
   - Export conversations

2. **Multiple Agents**
   - Different system prompts
   - Agent profiles
   - Specialized assistants

3. **API Usage Tracking**
   - Token count
   - Cost calculation
   - Usage statistics

4. **Advanced Settings**
   - Temperature control
   - Model selection
   - Max tokens slider

5. **File Uploads**
   - Image analysis
   - Document processing
   - Multi-modal AI

6. **Shortcodes**
   - Embed AI chat on frontend
   - Public-facing interface
   - Customizable design

7. **Rate Limiting**
   - Prevent abuse
   - User quotas
   - Time-based limits

---

## Troubleshooting Guide

### API Key Not Saving
- Check user permissions (must be admin)
- Verify nonce is valid
- Check for PHP errors in debug.log

### AJAX Not Working
- Ensure JavaScript is enabled
- Check console for errors
- Verify `ajaxurl` is defined
- Check nonce validity

### Show/Hide Not Toggling
- Clear transients: `delete_transient('wp_basic_agent_show_key_' . get_current_user_id())`
- Check nonce names match
- Verify redirect is working

### OpenAI Errors
- Verify API key is correct
- Check OpenAI account has credits
- Review error messages
- Test API key directly with OpenAI

---

## Code Standards

### WordPress Coding Standards
- Function naming: `prefix_function_name()`
- Hook priority: Default (10)
- Capability checks: `manage_options`
- Nonce naming: Descriptive and unique

### Security Standards
- Always verify nonces
- Check capabilities
- Sanitize input
- Escape output
- Validate data types

### File Organization
- Functions in `/admin/functions/`
- Templates in `/admin/templates/`
- Assets in `/admin/assets/`
- Includes in `/admin/includes/`

---

## Performance Considerations

### Transients
- Auto-expire (reduces database)
- User-specific (prevents conflicts)
- Appropriate timeouts (1 hour, 30 seconds)

### AJAX
- 30-second timeout
- Error handling
- Loading indicators
- Prevents double submissions

### Asset Loading
- Only on plugin pages
- Minified in production
- Conditional enqueuing

---

## Conclusion

This plugin demonstrates a **modular WordPress architecture** with:

- **Organized structure:** Separation of concerns
- **Mixed approach:** PHP-only for settings, AJAX for queries
- **Security first:** Multiple validation layers
- **User experience:** Loading indicators, error handling
- **Flexibility:** Easy to extend and customize
- **Standards:** WordPress best practices

The modular design makes it ideal for **learning and expansion**, while maintaining **security and performance** throughout.
