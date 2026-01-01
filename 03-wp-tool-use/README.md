# WP Tool Use Demo Plugin

A WordPress plugin that demonstrates OpenAI tool calling functionality with both admin interface and shortcode support. This plugin allows users to interact with OpenAI's GPT models that support function calling, providing a visual interface to see how AI can use tools to accomplish tasks.

## Features

- **Admin Interface**: Top-level menu item "Tool Use Demo" in WordPress admin (position 3.3)
- **Shortcode Interface**: Easy to embed anywhere in WordPress using `[wp_tool_use]`
- **Multiple Instances**: Can use multiple instances on the same page
- **Tool Calling Demo**: Demonstrates `get_weather()` and `get_sum()` functions
- **Responsive Design**: Works on desktop and mobile devices
- **Security**: Includes nonce verification for form submissions
- **WordPress Standards**: Follows WordPress coding standards and best practices

## Installation

1. Upload the `wp-tool-use` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the tool demo from WordPress admin menu → **Tool Use Demo**
4. Use the shortcode `[wp_tool_use]` in any post or page

## Usage

### Admin Interface
1. Go to **WordPress Admin → Tool Use Demo**
2. Enter your OpenAI API key
3. Enter a prompt (e.g., "What's the weather?" or "What is 5 + 3?")
4. Click "Send Request" to see basic functionality

### Basic Shortcode
```
[wp_tool_use]
```

### Shortcode with Custom Attributes
```
[wp_tool_use 
    title="My Custom Tool Demo" 
    subtitle="Try asking about weather or math" 
    default_prompt="What is 10 + 15?"
]
```

### Available Attributes

- **title**: The main heading for the tool demo (default: "🛠️ OpenAI Tool Calling Demo")
- **subtitle**: The subtitle/description (default: "Demonstrates tool calling with get_weather() and get_sum() functions")
- **default_prompt**: The default prompt in the input field (default: "What's the weather in England?")

## How It Works

### Admin Interface
The admin interface provides a simplified demonstration of the tool calling concept with basic AJAX functionality.

### Frontend Shortcode
The shortcode creates a full tool calling interface where users can:

1. **Enter OpenAI API Key**: Users provide their own OpenAI API key
2. **Enter a Prompt**: Users can ask questions like:
   - "What's the weather?" (triggers `get_weather()` tool)
   - "What is 5 + 3?" (triggers `get_sum()` tool)
3. **View Results**: The interface shows the conversation flow including:
   - API calls to OpenAI
   - Tool calls made by the AI
   - Tool execution results
   - Final AI responses

## Available Tools

### get_weather()
Returns current weather information (simulated):
```
"The current weather is 25°C, sunny with a light breeze."
```

### get_sum(a, b)
Calculates the sum of two numbers:
```
Input: a=5, b=3
Output: "The sum of 5 and 3 is 8."
```

## File Structure

```
wp-tool-use/
├── wp-tool-use.php                    # Main plugin file
├── shortcodes/
│   ├── wp-tool-use-shortcode.php      # Shortcode handler
│   └── assets/
│       ├── css/
│       │   └── frontend-styles.css
│       └── js/
│           └── frontend-script.js
└── README.md                          # This file
```

## Security Features

- **Nonce Verification**: All AJAX requests are verified
- **Input Sanitization**: All user inputs are properly sanitized
- **Direct Access Prevention**: Plugin files cannot be accessed directly
- **Capability Checks**: Admin interface requires `manage_options` capability

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Valid OpenAI API key
- Modern web browser with JavaScript enabled

## Admin Menu Location

The plugin adds a top-level menu item:
- **Menu Location**: WordPress Admin → Tool Use Demo
- **Menu Icon**: Tools (dashicons-admin-tools)
- **Position**: 3.3
- **Capability Required**: manage_options

## Customization

### Adding New Tools

To add new tools, modify the JavaScript in `shortcodes/assets/js/frontend-script.js`:

1. Add the tool function:
```javascript
function my_new_tool(param1, param2) {
    // Your tool logic here
    return "Tool result";
}
```

2. Add the tool to the processToolCall function:
```javascript
function processToolCall(toolName, toolInput) {
    if (toolName === 'my_new_tool') {
        return my_new_tool(toolInput.param1, toolInput.param2);
    }
    // ... existing tools
}
```

3. Add the tool definition to the tools array in handleToolCalling function.

### Styling

Customize the appearance by modifying `shortcodes/assets/css/frontend-styles.css`. The plugin uses CSS custom properties and BEM-style class names for easy customization.

## Troubleshooting

### Plugin Not Working
1. Ensure the plugin is activated
2. Check that your theme supports shortcodes
3. Verify JavaScript is enabled in your browser
4. Check browser console for any JavaScript errors
5. For admin interface: ensure you have admin privileges

### OpenAI API Issues
1. Verify your API key is correct
2. Check that your OpenAI account has sufficient credits
3. Ensure you're using a model that supports function calling (gpt-4o-mini, gpt-4, etc.)

### Admin Menu Not Showing
1. Check that you have administrator privileges
2. Try deactivating and reactivating the plugin
3. Ensure no other plugins are conflicting

## Support

For issues and feature requests, please check the plugin documentation or contact the developer.

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Top-level admin menu (position 3.3)
- Basic tool calling functionality in shortcode
- Admin interface with basic AJAX
- Shortcode interface
- Responsive design
- Security features implemented
