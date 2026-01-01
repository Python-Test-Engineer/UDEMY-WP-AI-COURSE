# Quick Start Guide - WP Tool Use Demo v3.3

## Installation
1. Copy the `wp-tool-use` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Access via **WordPress Admin → Tool Use Demo** (top-level menu)

## Two Ways to Use

### Method 1: Admin Interface (Recommended)
1. Go to **WordPress Admin → Tool Use Demo**
2. Enter your OpenAI API key
3. Enter a prompt (e.g., "What's the weather?" or "What is 5 + 3?")
4. Click "Send Request"

### Method 2: Shortcode
```
[wp_tool_use]
```

## Custom Shortcode Usage
```
[wp_tool_use 
    title="Weather & Math Demo" 
    subtitle="Ask me anything!" 
    default_prompt="What is 7 + 8?"
]
```

## Test Prompts
Try these prompts to see tool calling in action:
- "What's the weather?" → Uses get_weather() tool
- "What is 15 + 25?" → Uses get_sum() tool  
- "Calculate 100 + 200" → Uses get_sum() tool

## What's New in v3.3
- **Top-Level Admin Menu**: Direct access from WordPress admin
- **Enhanced Interface**: Professional admin page with help text
- **Better Structure**: Improved organization and security

## Requirements
- WordPress 5.0+
- PHP 7.4+
- OpenAI API key
- JavaScript enabled
- Admin privileges for admin interface

## Quick Tips
1. Use the admin interface for testing and demonstrations
2. Use shortcodes for embedding on frontend pages
3. Multiple shortcode instances can be used on the same page
4. All inputs are sanitized and secure
