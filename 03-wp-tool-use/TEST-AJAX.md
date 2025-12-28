# Testing the AJAX 500 Error

## How to See the Actual Error

### Method 1: Browser Developer Tools

1. Open the page with the shortcode `[wp_tool_use]`
2. Press F12 to open Developer Tools
3. Go to the **Network** tab
4. Clear the network log
5. Submit a query in the form
6. Look for the request to `admin-ajax.php`
7. Click on it
8. Go to the **Response** tab
9. **Copy the entire error message and share it with me**

### Method 2: Enable WordPress Debug Mode

Add this to your `wp-config.php` file (before "That's all, stop editing!"):

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Then check the file: `wp-content/debug.log` for error messages.

### Method 3: Simple Test

Create a test page and add just this shortcode:
```
[wp_tool_use]
```

Then try asking: "what is 2 plus 2"

## Common Causes of 500 Errors

1. **PHP Syntax Error** - Missing semicolon, bracket, etc.
2. **Function not found** - The tool functions aren't loaded when AJAX runs
3. **Memory limit** - PHP runs out of memory
4. **Plugin conflict** - Another plugin interfering

## Quick Fix to Try

Try deactivating ALL other plugins except this one, then test again.

If it works with other plugins off, re-activate them one by one to find the conflicting plugin.
