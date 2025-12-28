# Beginner's Guide to WordPress Plugin Development

## Getting Started

A WordPress plugin is essentially PHP code that extends WordPress functionality. Let's start simple and then refactor into a professional structure.

## Phase 1: Your First Plugin (Single File)

Create a file called `my-first-plugin.php` in `wp-content/plugins/`:

```php
<?php
/**
 * Plugin Name: My First Plugin
 * Plugin URI: https://example.com/my-first-plugin
 * Description: A beginner's plugin to learn WordPress development
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: my-first-plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('MY_PLUGIN_VERSION', '1.0.0');
define('MY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Add a simple admin notice
function my_plugin_admin_notice() {
    echo '<div class="notice notice-success"><p>My First Plugin is active!</p></div>';
}
add_action('admin_notices', 'my_plugin_admin_notice');
```

Activate this in WordPress admin under Plugins, and you'll see your notice.

## Understanding Plugin Constants

These constants make your code portable and maintainable:

```php
// Version tracking for updates and cache busting
define('MY_PLUGIN_VERSION', '1.0.0');

// Absolute file system path to plugin directory
// Example: /var/www/html/wp-content/plugins/my-plugin/
define('MY_PLUGIN_DIR', plugin_dir_path(__FILE__));

// URL to plugin directory (for enqueueing assets)
// Example: https://example.com/wp-content/plugins/my-plugin/
define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Optional: Path to plugin file itself
define('MY_PLUGIN_FILE', __FILE__);

// Optional: Plugin basename (for activation/deactivation hooks)
define('MY_PLUGIN_BASENAME', plugin_basename(__FILE__));
```

**Usage examples:**

```php
// Load a PHP file
require_once MY_PLUGIN_DIR . 'includes/class-helper.php';

// Enqueue a CSS file
wp_enqueue_style('my-plugin-css', MY_PLUGIN_URL . 'assets/css/style.css', [], MY_PLUGIN_VERSION);

// Enqueue a JavaScript file
wp_enqueue_script('my-plugin-js', MY_PLUGIN_URL . 'assets/js/script.js', ['jquery'], MY_PLUGIN_VERSION, true);
```

## Include vs Require: What's the Difference?

PHP offers four ways to load files, and understanding them is crucial:

### `include` and `include_once`

- **include**: Loads a file. If the file is missing, shows a warning but continues execution.
- **include_once**: Same as `include`, but ensures the file is loaded only once even if called multiple times.

```php
// Use for optional files (like templates or non-critical components)
include MY_PLUGIN_DIR . 'templates/optional-feature.php';

// Prevents loading the same file twice
include_once MY_PLUGIN_DIR . 'templates/header.php';
```

### `require` and `require_once`

- **require**: Loads a file. If the file is missing, throws a fatal error and stops execution.
- **require_once**: Same as `require`, but ensures the file is loaded only once.

```php
// Use for critical files (classes, functions your plugin depends on)
require MY_PLUGIN_DIR . 'includes/class-core.php';

// Prevents loading the same class definition twice (which causes errors)
require_once MY_PLUGIN_DIR . 'includes/functions.php';
```

### When to Use Which?

| Use Case | Directive | Reason |
|----------|-----------|--------|
| Loading critical classes | `require_once` | Fatal error if missing; no duplicate loading |
| Loading helper functions | `require_once` | Fatal error if missing; no duplicate loading |
| Loading configuration | `require_once` | Fatal error if missing; load once |
| Loading optional templates | `include` | Continue if missing; may need multiple times |
| Loading optional features | `include_once` | Continue if missing; load once |

**Best Practice:** Use `require_once` for 95% of your plugin files. It's safer and prevents duplicate loading issues.

## Phase 2: Recommended Folder Structure

As your plugin grows, organize it properly:

```
my-first-plugin/
├── my-first-plugin.php          # Main plugin file
├── uninstall.php                # Cleanup when plugin is deleted
├── readme.txt                   # WordPress.org readme
├── LICENSE.txt
├── includes/                    # PHP classes and functions
│   ├── class-activator.php      # Runs on plugin activation
│   ├── class-deactivator.php    # Runs on plugin deactivation
│   ├── class-core.php           # Main plugin class
│   ├── class-admin.php          # Admin-specific functionality
│   ├── class-public.php         # Public-facing functionality
│   └── functions.php            # Helper functions
├── admin/                       # Admin area files
│   ├── css/
│   │   └── admin-style.css
│   ├── js/
│   │   └── admin-script.js
│   └── partials/                # Admin HTML templates
│       └── admin-display.php
├── public/                      # Public-facing files
│   ├── css/
│   │   └── public-style.css
│   ├── js/
│   │   └── public-script.js
│   └── partials/                # Public HTML templates
│       └── public-display.php
├── languages/                   # Translation files
│   └── my-first-plugin.pot
└── assets/                      # WordPress.org assets
    ├── banner-772x250.png
    └── icon-256x256.png
```

## Phase 3: Refactored Main Plugin File

Here's how your main file looks after refactoring:

```php
<?php
/**
 * Plugin Name: My First Plugin
 * Description: A properly structured WordPress plugin
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: my-first-plugin
 */

// Security: Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MY_PLUGIN_VERSION', '1.0.0');
define('MY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MY_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Activation hook
 */
function activate_my_plugin() {
    require_once MY_PLUGIN_DIR . 'includes/class-activator.php';
    My_Plugin_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_my_plugin');

/**
 * Deactivation hook
 */
function deactivate_my_plugin() {
    require_once MY_PLUGIN_DIR . 'includes/class-deactivator.php';
    My_Plugin_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_my_plugin');

/**
 * Load core plugin class
 */
require_once MY_PLUGIN_DIR . 'includes/class-core.php';

/**
 * Initialize the plugin
 */
function run_my_plugin() {
    $plugin = new My_Plugin_Core();
    $plugin->run();
}
run_my_plugin();
```

## Phase 4: Example Core Class

Create `includes/class-core.php`:

```php
<?php
/**
 * Core plugin class
 */
class My_Plugin_Core {
    
    protected $version;
    
    public function __construct() {
        $this->version = MY_PLUGIN_VERSION;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load helper functions
        require_once MY_PLUGIN_DIR . 'includes/functions.php';
        
        // Load admin class
        require_once MY_PLUGIN_DIR . 'includes/class-admin.php';
        
        // Load public class
        require_once MY_PLUGIN_DIR . 'includes/class-public.php';
    }
    
    /**
     * Register admin hooks
     */
    private function define_admin_hooks() {
        $admin = new My_Plugin_Admin($this->version);
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_scripts'));
        add_action('admin_menu', array($admin, 'add_admin_menu'));
    }
    
    /**
     * Register public hooks
     */
    private function define_public_hooks() {
        $public = new My_Plugin_Public($this->version);
        add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));
    }
    
    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is now running
    }
}
```

## Phase 5: Example Admin Class

Create `includes/class-admin.php`:

```php
<?php
/**
 * Admin-specific functionality
 */
class My_Plugin_Admin {
    
    private $version;
    
    public function __construct($version) {
        $this->version = $version;
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'my-plugin-admin',
            MY_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'my-plugin-admin',
            MY_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery'),
            $this->version,
            true
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'My Plugin Settings',           // Page title
            'My Plugin',                    // Menu title
            'manage_options',               // Capability
            'my-plugin',                    // Menu slug
            array($this, 'display_admin_page'), // Callback
            'dashicons-admin-generic',      // Icon
            30                              // Position
        );
    }
    
    /**
     * Display admin page
     */
    public function display_admin_page() {
        include_once MY_PLUGIN_DIR . 'admin/partials/admin-display.php';
    }
}
```

## Key WordPress Coding Standards

1. **Prefix everything**: Use a unique prefix for all functions, classes, and constants to avoid conflicts.

2. **Security first**: 
   - Check `ABSPATH` or `WPINC` at file start
   - Sanitize inputs with `sanitize_text_field()`, `esc_html()`, etc.
   - Escape outputs with `esc_html()`, `esc_attr()`, `esc_url()`
   - Use nonces for forms

3. **Use WordPress functions**: Don't reinvent the wheel. WordPress has functions for database queries, HTTP requests, file operations, etc.

4. **Hooks over direct modification**: Never modify core WordPress files. Always use hooks (actions and filters).

## Common Patterns

### Loading a configuration file once:
```php
require_once MY_PLUGIN_DIR . 'includes/config.php';
```

### Loading multiple class files:
```php
$classes = ['class-helper', 'class-validator', 'class-formatter'];
foreach ($classes as $class) {
    require_once MY_PLUGIN_DIR . 'includes/' . $class . '.php';
}
```

### Loading a template with fallback:
```php
$template = locate_template('my-plugin/custom-template.php');
if (!$template) {
    $template = MY_PLUGIN_DIR . 'templates/default-template.php';
}
include $template;
```

## Activation, Deactivation, and Uninstall Hooks

WordPress provides three lifecycle hooks for plugins. Understanding when and how to use each is crucial.

### Activation Hook

The activation hook runs **once** when the plugin is activated. Use it for:
- Creating custom database tables
- Adding default options
- Setting up initial data
- Scheduling cron jobs
- Flushing rewrite rules

**In your main plugin file:**

```php
/**
 * Register activation hook
 */
function activate_my_plugin() {
    require_once MY_PLUGIN_DIR . 'includes/class-activator.php';
    My_Plugin_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_my_plugin');
```

**Create `includes/class-activator.php`:**

```php
<?php
/**
 * Fired during plugin activation
 */
class My_Plugin_Activator {
    
    /**
     * Activation tasks
     */
    public static function activate() {
        global $wpdb;
        
        // Check minimum WordPress version
        if (version_compare(get_bloginfo('version'), '5.8', '<')) {
            wp_die('This plugin requires WordPress 5.8 or higher.');
        }
        
        // Create custom database table
        $table_name = $wpdb->prefix . 'my_plugin_data';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            name tinytext NOT NULL,
            text text NOT NULL,
            url varchar(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add default options
        add_option('my_plugin_version', MY_PLUGIN_VERSION);
        add_option('my_plugin_settings', array(
            'enabled' => true,
            'api_key' => '',
            'items_per_page' => 10
        ));
        
        // Schedule cron job
        if (!wp_next_scheduled('my_plugin_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'my_plugin_daily_cleanup');
        }
        
        // Flush rewrite rules (if you added custom post types or taxonomies)
        flush_rewrite_rules();
        
        // Set activation flag (useful for showing welcome message)
        set_transient('my_plugin_activated', true, 60);
    }
}
```

### Deactivation Hook

The deactivation hook runs when the plugin is deactivated. Use it for:
- Clearing scheduled cron jobs
- Flushing rewrite rules
- Clearing temporary data

**Important:** Do NOT delete user data on deactivation! Users might reactivate later.

**In your main plugin file:**

```php
/**
 * Register deactivation hook
 */
function deactivate_my_plugin() {
    require_once MY_PLUGIN_DIR . 'includes/class-deactivator.php';
    My_Plugin_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_my_plugin');
```

**Create `includes/class-deactivator.php`:**

```php
<?php
/**
 * Fired during plugin deactivation
 */
class My_Plugin_Deactivator {
    
    /**
     * Deactivation tasks
     */
    public static function deactivate() {
        
        // Clear scheduled cron jobs
        $timestamp = wp_next_scheduled('my_plugin_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'my_plugin_daily_cleanup');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any temporary transients
        delete_transient('my_plugin_cache');
        
        // Log deactivation (optional)
        error_log('My Plugin deactivated at ' . current_time('mysql'));
        
        // DO NOT delete options, database tables, or user data here!
        // That should only happen on uninstall
    }
}
```

### Uninstall: Two Methods

WordPress offers two ways to handle plugin uninstall. **Choose one method only.**

#### Method 1: Uninstall Hook (Less Common)

Register an uninstall hook in your main plugin file:

```php
/**
 * Register uninstall hook
 */
register_uninstall_hook(__FILE__, 'uninstall_my_plugin');

function uninstall_my_plugin() {
    require_once MY_PLUGIN_DIR . 'includes/class-uninstaller.php';
    My_Plugin_Uninstaller::uninstall();
}
```

**Limitations of this method:**
- Cannot use static class methods directly in `register_uninstall_hook()`
- Less flexible than `uninstall.php`
- Not recommended by WordPress documentation

#### Method 2: uninstall.php (Recommended)

Create `uninstall.php` in your plugin root directory:

```php
<?php
/**
 * Fired when the plugin is uninstalled
 * 
 * This file must be in the plugin root directory
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define plugin path (we can't use constants here)
$plugin_dir = plugin_dir_path(__FILE__);

// Load any dependencies if needed
// require_once $plugin_dir . 'includes/class-uninstaller.php';

/**
 * Delete plugin options
 */
delete_option('my_plugin_version');
delete_option('my_plugin_settings');

// For multisite installations
delete_site_option('my_plugin_version');
delete_site_option('my_plugin_settings');

/**
 * Delete all transients
 */
delete_transient('my_plugin_cache');
delete_transient('my_plugin_data');

/**
 * Drop custom database tables
 */
global $wpdb;

$table_name = $wpdb->prefix . 'my_plugin_data';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// For multisite, loop through all sites
if (is_multisite()) {
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        // Delete options for this site
        delete_option('my_plugin_version');
        delete_option('my_plugin_settings');
        
        // Drop table for this site
        $table_name = $wpdb->prefix . 'my_plugin_data';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        restore_current_blog();
    }
}

/**
 * Delete custom post types (optional)
 */
$posts = get_posts(array(
    'post_type' => 'my_custom_post_type',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($posts as $post) {
    wp_delete_post($post->ID, true); // true = force delete, skip trash
}

/**
 * Delete custom taxonomies (optional)
 */
$terms = get_terms(array(
    'taxonomy' => 'my_custom_taxonomy',
    'hide_empty' => false,
));

foreach ($terms as $term) {
    wp_delete_term($term->term_id, 'my_custom_taxonomy');
}

/**
 * Delete user meta (if you stored any)
 */
$users = get_users();
foreach ($users as $user) {
    delete_user_meta($user->ID, 'my_plugin_user_setting');
}

/**
 * Clear scheduled cron jobs
 */
wp_clear_scheduled_hook('my_plugin_daily_cleanup');

/**
 * Remove custom capabilities (if you added any)
 */
$role = get_role('administrator');
if ($role) {
    $role->remove_cap('my_plugin_manage');
}

/**
 * Delete uploaded files (be careful!)
 */
$upload_dir = wp_upload_dir();
$plugin_upload_dir = $upload_dir['basedir'] . '/my-plugin-uploads';

if (is_dir($plugin_upload_dir)) {
    // Recursively delete directory
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($plugin_upload_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    
    rmdir($plugin_upload_dir);
}
```

### Comparison: Uninstall Hook vs uninstall.php

| Feature | Uninstall Hook | uninstall.php |
|---------|---------------|---------------|
| **Location** | Registered in main file | Root directory file |
| **WordPress Recommendation** | Not recommended | ✅ Recommended |
| **Can use static methods** | ❌ No | ✅ Yes |
| **Flexibility** | Limited | Full |
| **Multisite support** | Harder | Easier |
| **Best Practice** | Avoid | ✅ Use this |

### Best Practices for Plugin Lifecycle

**Activation:**
- ✅ Create database tables
- ✅ Set default options
- ✅ Check system requirements
- ✅ Schedule cron jobs
- ✅ Flush rewrite rules
- ❌ Don't send emails (user didn't consent)
- ❌ Don't make external API calls

**Deactivation:**
- ✅ Clear scheduled events
- ✅ Flush rewrite rules
- ✅ Clear temporary caches
- ❌ Don't delete user data
- ❌ Don't delete options
- ❌ Don't delete database tables

**Uninstall:**
- ✅ Delete all options
- ✅ Drop database tables
- ✅ Delete custom post types/taxonomies
- ✅ Clear all scheduled events
- ✅ Remove user meta
- ✅ Delete uploaded files (if safe)
- ⚠️ Be absolutely certain before deleting data!

### Testing Uninstall Safely

Create a test function to preview what will be deleted:

```php
// Add this to your admin page for testing
function my_plugin_preview_uninstall() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<h3>Data that will be deleted on uninstall:</h3>';
    echo '<ul>';
    
    // Check options
    echo '<li>Options: my_plugin_version, my_plugin_settings</li>';
    
    // Check database tables
    global $wpdb;
    $table_name = $wpdb->prefix . 'my_plugin_data';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "<li>Database table: $table_name ($count rows)</li>";
    
    // Check custom post types
    $posts = get_posts(array(
        'post_type' => 'my_custom_post_type',
        'numberposts' => -1,
        'post_status' => 'any'
    ));
    echo '<li>Custom posts: ' . count($posts) . '</li>';
    
    echo '</ul>';
}
```

### Complete Lifecycle Example

Here's how all three hooks work together:

```php
<?php
/**
 * Main plugin file with all lifecycle hooks
 */

// Activation
register_activation_hook(__FILE__, function() {
    // Setup: Create tables, add options, schedule events
    require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
    My_Plugin_Activator::activate();
});

// Deactivation
register_deactivation_hook(__FILE__, function() {
    // Cleanup: Clear caches, unschedule events (keep user data!)
    require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';
    My_Plugin_Deactivator::deactivate();
});

// Uninstall is handled by uninstall.php in root directory
// (removes all data when user deletes the plugin)
```

## Next Steps

1. Read the [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
2. Study existing plugins in the WordPress repository
3. Learn about WordPress hooks, filters, and actions
4. Understand the WordPress database structure (`$wpdb`)
5. Practice writing secure, sanitized code
6. Test your activation/deactivation/uninstall hooks thoroughly!

Remember: Start simple, test often, and refactor as your plugin grows!