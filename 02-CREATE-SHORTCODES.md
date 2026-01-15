# How to Create a WordPress Shortcode

## What is a Shortcode?

A shortcode is a small piece of code that you can insert into WordPress posts, pages, or widgets using square brackets. For example, `[gallery]` is a built-in WordPress shortcode. When WordPress processes your content, it replaces the shortcode with whatever output you've programmed it to display.

## Why Use Shortcodes?

Shortcodes let you add complex functionality to your content without writing HTML or PHP directly in your posts. They're reusable, easy to remember, and keep your content clean.

## Basic Steps to Create a Shortcode

### Step 1: Access Your Theme's Functions File

You'll need to add code to your theme's `functions.php` file. You can access this through:

- **WordPress Dashboard**: Appearance → Theme File Editor → functions.php
- **FTP/File Manager**: Navigate to `/wp-content/themes/your-theme-name/functions.php`

**Important**: Always use a child theme or a custom plugin to avoid losing your changes when the theme updates.

### Step 2: Write Your Shortcode Function

Here's a simple example that displays a welcome message:

```php
function welcome_message_shortcode() {
    return '<p style="color: blue; font-weight: bold;">Welcome to our website!</p>';
}
add_shortcode('welcome', 'welcome_message_shortcode');
```

**What's happening here:**
- `function welcome_message_shortcode()` creates a new function
- `return` sends back the HTML you want to display
- `add_shortcode('welcome', 'welcome_message_shortcode')` registers the shortcode with WordPress
- The first parameter `'welcome'` is what users will type: `[welcome]`
- The second parameter is the name of your function

### Step 3: Use Your Shortcode

Now you can use `[welcome]` anywhere in your posts, pages, or widgets, and WordPress will replace it with your welcome message.

## Two Ways to Output HTML

### Method 1: Using Return (Simple and Recommended)

For simple HTML, just return a string:

```php
function simple_button_shortcode() {
    return '<button style="padding: 10px 20px; background: blue; color: white;">Click Me</button>';
}
add_shortcode('button', 'simple_button_shortcode');
```

### Method 2: Using Output Buffering (For Complex HTML)

When you have lots of HTML or need to mix PHP and HTML, output buffering is cleaner:

```php
function card_shortcode() {
    ob_start(); ?>
    
    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
        <h3>Special Offer</h3>
        <p>Get 20% off your first order!</p>
        <button style="background: green; color: white; padding: 10px;">Shop Now</button>
    </div>
    
    <?php
    return ob_get_clean();
}
add_shortcode('card', 'card_shortcode');
```

**How output buffering works:**
- `ob_start()` starts capturing all output
- You write normal HTML (even switching in and out of PHP with `<?php ?>`)
- `ob_get_clean()` grabs everything that was captured and clears the buffer
- You return that captured content

This is especially useful when you have complex HTML layouts and don't want to deal with lots of concatenation and escaping quotes.

## Adding Parameters to Shortcodes

You can make shortcodes more flexible by accepting parameters:

```php
function custom_greeting_shortcode($atts) {
    // Set default values
    $atts = shortcode_atts(array(
        'name' => 'Friend',
        'color' => 'blue'
    ), $atts);
    
    return '<p style="color: ' . esc_attr($atts['color']) . ';">Hello, ' . esc_html($atts['name']) . '!</p>';
}
add_shortcode('greeting', 'custom_greeting_shortcode');
```

**Usage examples:**
- `[greeting]` displays: "Hello, Friend!" in blue
- `[greeting name="Sarah"]` displays: "Hello, Sarah!" in blue
- `[greeting name="John" color="red"]` displays: "Hello, John!" in red

**Security note**: Always use `esc_html()` and `esc_attr()` to sanitize user input and prevent security vulnerabilities.

## Shortcode with Content Between Tags

You can create shortcodes that wrap around content:

```php
function highlight_box_shortcode($atts, $content = null) {
    return '<div style="background-color: yellow; padding: 10px; border: 2px solid orange;">' . 
           do_shortcode($content) . 
           '</div>';
}
add_shortcode('highlight', 'highlight_box_shortcode');
```

**Usage:**
```
[highlight]
This text will appear in a yellow box!
[/highlight]
```

The `do_shortcode($content)` function processes any shortcodes that might be inside your shortcode.

## Best Practices

1. **Use unique names**: Prefix your shortcodes to avoid conflicts (e.g., `[mysite_welcome]` instead of just `[welcome]`)
2. **Always sanitize output**: Use WordPress escaping functions like `esc_html()`, `esc_attr()`, and `esc_url()`
3. **Test thoroughly**: Check your shortcode works in posts, pages, and widgets
4. **Use a child theme or plugin**: Don't edit the main theme files directly
5. **Document your shortcodes**: Keep notes on what parameters each shortcode accepts

## Troubleshooting

**Shortcode displays as text instead of rendering:**
- Check that you've spelled the shortcode name correctly
- Verify your code was added to `functions.php` and saved properly
- Clear your cache if you're using a caching plugin

**Nothing displays:**
- Make sure your function uses `return`, not `echo`
- Check for PHP errors in your code

**Formatting looks wrong:**
- Remember that WordPress may auto-format your output with `<p>` tags
- Test in different contexts (posts, widgets, etc.)

## Next Steps

Once you're comfortable with basic shortcodes, you can explore:
- Adding CSS files to style your shortcode output
- Using JavaScript with shortcodes
- Creating shortcodes that query the database
- Building admin interfaces for your shortcodes

Shortcodes are a powerful way to extend WordPress functionality without modifying core files. Start simple, test often, and gradually add more complexity as you learn!