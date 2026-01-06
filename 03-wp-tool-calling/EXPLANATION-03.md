# WP Tool Calling Plugin

Created by asking Claude.ai to create a WP plugin based on `COURSE-1-HTML-EXAMPLES\HTML-PAGES\03-openai-tool-calling.html` and to create a shortcode as well.

WordPress plugin that demonstrates OpenAI Tool Calling functionality with `get_weather()` and `get_sum()` functions.

## Features

- **Admin Dashboard Interface**: Access the tool calling demo from WordPress admin
- **Frontend Shortcode**: Use `[tool_calling]` to display the demo on any page or post
- **Localized CSS**: All styles are namespaced to prevent conflicts with theme styles
- **Two Demo Tools**:
  - `get_weather()` - Returns current weather information
  - `get_sum(a, b)` - Calculates the sum of two numbers

## Installation

1. Upload the `03-wp-tool-calling` folder to your WordPress `wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

### Admin Dashboard

1. Go to **Tool Calling** in the WordPress admin menu
2. Enter your OpenAI API key (starts with sk-...)
3. Enter a prompt like:
   - "What's the weather in England?"
   - "What is 5 + 3?"
4. Click "Send Request" to see the tool calling in action

### Frontend Shortcode

Add the shortcode to any page or post:

```
[tool_calling]
```

Users can then interact with the tool calling demo from the frontend.

## File Structure

```
3A-wp-tool-calling/
├── index.php                          # Main plugin file
├── admin/
│   ├── admin-page.php                 # Admin menu and page
│   └── assets/
│       ├── css/
│       │   └── admin-styles.css       # Localized admin styles
│       └── js/
│           └── admin-script.js        # Admin functionality
└── shortcodes/
    ├── tool-calling-shortcode.php     # Shortcode registration
    └── assets/
        ├── css/
        │   └── frontend-styles.css    # Localized frontend styles
        └── js/
            └── frontend-script.js     # Frontend functionality
```

## How It Works

1. User enters a prompt and API key
2. The plugin calls OpenAI's API with defined tools
3. If OpenAI decides to use a tool, the plugin executes it locally
4. Results are sent back to OpenAI for final response
5. The conversation continues until OpenAI provides a final answer

## CSS Localization

All CSS classes are prefixed to prevent conflicts:
- Admin: `.wptc-*` (e.g., `.wptc-container`, `.wptc-input-group`)
- Frontend: `.wptc-frontend-*` (e.g., `.wptc-frontend-container`)

## JavaScript Namespacing

All JavaScript functions are prefixed:
- Admin: `wptc_*` (e.g., `wptc_handleToolCalling()`)
- Frontend: `wptc_frontend_*` (e.g., `wptc_frontend_handleToolCalling()`)

## Requirements

- WordPress 5.0 or higher
- OpenAI API key
- Modern browser with JavaScript enabled

## Notes

- API keys are not stored - users must enter them each session
- This is a demonstration plugin - extend it for production use
- Tool implementations are simple examples - customize as needed
