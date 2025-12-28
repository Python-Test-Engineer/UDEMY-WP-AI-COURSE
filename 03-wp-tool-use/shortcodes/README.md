# WP Tool Use Shortcode Documentation

## Overview
The WP Tool Use shortcode allows you to embed an AI-powered tool use demonstration on any WordPress page or post. Users can interact with OpenAI's GPT-4o-mini model that has access to custom tools like weather checking and math calculations.

---

## Shortcode Usage

### Basic Usage
```
[wp_tool_use]
```

Add this shortcode to any page or post to display the AI tool use interface.

---

## Shortcode Attributes

### 1. Custom Placeholder Text
Change the default prompt placeholder:
```
[wp_tool_use placeholder="Ask me about weather or math!"]
```

**Default:** `Ask about weather or do math calculations...`

### 2. Hide API Key Input
Hide the API key input field from users (requires admin-configured API key):
```
[wp_tool_use show_api_key_input="no"]
```

**Default:** `yes`  
**Options:** `yes` or `no`

### 3. Hide Tool Information
Hide the tool cards display:
```
[wp_tool_use show_tool_info="no"]
```

**Default:** `yes`  
**Options:** `yes` or `no`

### 4. Combined Attributes
Use multiple attributes together:
```
[wp_tool_use placeholder="Try the tools!" show_api_key_input="no" show_tool_info="yes"]
```

---

## Features

### ✅ AI Tool Use (Function Calling)
- **Weather Tool:** Returns random temperature (-10°C to 40°C)
- **Math Tool:** Adds two numbers together
- **Automatic Tool Selection:** AI decides when to use tools
- **Tool Execution Display:** Shows which tools were used

### ✅ Frontend API Key Configuration
- **Admin API Key:** If set in the admin panel, it's pre-populated
- **User API Key:** Users can enter their own API key
- **Show/Hide Toggle:** JavaScript toggle for API key visibility
- **Flexible Usage:** Works with admin or user-provided keys

### ✅ AJAX-Powered Interface
- **No Page Reload:** Submissions handled via AJAX
- **Loading Indicators:** Visual feedback during processing
- **Error Handling:** Clear error messages
- **Response Display:** AI responses and tool executions shown separately

### ✅ Modern Design
- **Gradient Styling:** Purple gradient theme with green accents
- **Tool Cards:** Interactive cards showing available tools
- **Responsive Design:** Works on all devices
- **Smooth Animations:** Fade-in effects and hover states

---

## What is Tool Use?

Tool use (also known as function calling) enables AI models to:
- Call external functions when needed
- Pass parameters to these functions
- Use the results in their responses
- Make decisions about when and how to use tools

Instead of just generating text, the AI can now **perform actions** and gather **real-time information**.

---

## Available Tools

### 🌤️ Weather Tool
**Function:** `get_weather()`  
**Description:** Returns a random temperature in Celsius  
**Parameters:** None  
**Example:** "What's the weather today?"

### ➕ Math Tool
**Function:** `add_two_numbers(a, b)`  
**Description:** Adds two numbers together  
**Parameters:** `a` (number), `b` (number)  
**Example:** "Add 15 and 27"

---

## How Tool Use Works

### Example 1: Weather Query
**User:** "What's the temperature outside?"  
**AI Process:**
1. Recognizes this is a weather question
2. Calls `get_weather()` tool
3. Receives result (e.g., 23°C)
4. Generates natural response: "The current temperature is 23°C"

**Frontend Display:**
- AI Response shows in main container
- Tool execution shown in green gradient box: "Weather tool executed: Current temperature is 23°C"

### Example 2: Math Query
**User:** "Calculate 42 plus 58"  
**AI Process:**
1. Recognizes this is a math problem
2. Calls `add_two_numbers(42, 58)` tool
3. Receives result: 100
4. Generates natural response: "42 + 58 equals 100"

**Frontend Display:**
- AI Response shows the answer
- Tool execution shown: "Math tool executed: 42 + 58 = 100"

---

## File Structure

```
03-wp-tool-use/
├── shortcode/
│   ├── wp-tool-use-shortcode.php    # Main shortcode file
│   ├── README.md                     # This documentation
│   └── assets/
│       ├── css/
│       │   └── frontend-styles.css  # Frontend styling
│       └── js/
│           └── frontend-script.js   # Frontend JavaScript
```

---

## Security Features

### 🔒 Nonce Verification
Every AJAX request includes WordPress nonce verification to prevent CSRF attacks.

### 🔒 Data Sanitization
- User inputs sanitized using `sanitize_textarea_field()`
- API keys sanitized with `sanitize_text_field()`
- Outputs escaped with `esc_attr()` and `esc_html()`

### 🔒 Public Access
- Shortcode works for both logged-in and logged-out users
- Uses `wp_ajax_nopriv_` hook for non-authenticated users
- API key required (either from admin or user input)

### 🔒 XSS Prevention
- JavaScript includes HTML escaping function
- Tool execution results properly sanitized
- No direct HTML injection

---

## API Request Flow

```
User Query
    ↓
JavaScript validates input
    ↓
AJAX POST to admin-ajax.php
    ↓
PHP handler verifies nonce
    ↓
Gets API key (frontend or admin)
    ↓
Defines available tools
    ↓
Sends request to OpenAI with tools
    ↓
AI analyzes and decides if tools needed
    ↓
If tools needed: AI requests tool execution
    ↓
PHP executes requested tools
    ↓
Returns results to JavaScript
    ↓
JavaScript displays:
  - AI Response
  - Tool Executions (if any)
```

---

## Tool Call Structure

When the AI wants to use tools, the OpenAI API response includes:

```json
{
  "tool_calls": [
    {
      "function": {
        "name": "add_two_numbers",
        "arguments": "{\"a\": 15, \"b\": 27}"
      }
    }
  ]
}
```

The PHP backend then:
1. Parses the tool call
2. Executes the appropriate function
3. Returns the result
4. JavaScript displays the tool execution details

---

## Error Handling

### Common Errors

**No Query Entered:**
```
"Please enter a query."
```

**No API Key:**
```
"API key is not configured. Please enter an API key or contact the administrator."
```

**Invalid API Key:**
```
"OpenAI API Error (Code 401): Incorrect API key provided..."
```

**Network Error:**
```
"Network error: [error message]"
```

---

## Styling Customization

### Color Scheme
- **Primary Purple Gradient:** `#667eea` to `#764ba2`
- **Secondary Green:** `#099709` (tool cards, accents)
- **Tool Execution Background:** Green gradient
- **Response Background:** `antiquewhite` with blue border

### Override Styles
```css
/* Change primary button gradient */
.wp-tool-use-button-primary {
    background: linear-gradient(135deg, #your-color1 0%, #your-color2 100%) !important;
}

/* Change tool cards background */
.wp-tool-use-tools-section {
    background: linear-gradient(135deg, #your-color1 0%, #your-color2 100%) !important;
}
```

---

## Testing the Shortcode

### Step 1: Activate Plugin
Make sure "✅ 03 UDEMY TOOL USE" plugin is activated.

### Step 2: Configure API Key (Optional)
Go to **03 TOOL USE** in WordPress admin and save your OpenAI API key.

### Step 3: Create a Test Page
1. Create a new page in WordPress
2. Add the shortcode: `[wp_tool_use]`
3. Publish the page

### Step 4: Test Weather Tool
1. Visit the published page
2. Enter: "What's the weather like?"
3. Click "Send to AI"
4. See both AI response AND tool execution

### Step 5: Test Math Tool
1. Enter: "Add 123 and 456"
2. Click "Send to AI"
3. Watch the AI use the math tool
4. See the calculation result

---

## Extending with New Tools

### Adding a New Tool

To add a custom tool, follow these steps:

#### 1. Create PHP Function
```php
function get_post_count() {
    return wp_count_posts()->publish;
}
```

#### 2. Add Tool Definition
In `wp-tool-use-shortcode.php`, add to `$tools` array:
```php
array(
    'type' => 'function',
    'function' => array(
        'name' => 'get_post_count',
        'description' => 'Get the total number of published posts',
        'parameters' => array(
            'type' => 'object',
            'properties' => (object)array(),
            'required' => array()
        )
    )
)
```

#### 3. Add Switch Case
In the tool execution section:
```php
case 'get_post_count':
    $result = get_post_count();
    $tool_response = "Post count tool executed: {$result} published posts";
    break;
```

#### 4. Update System Prompt
Mention the new tool in the `$system_prompt` variable.

---

## Real-World Use Cases

### Educational Applications
- Math problem solving
- Science calculations
- Unit conversions
- Data lookups

### Business Applications
- Inventory checks
- Price calculations
- Order status queries
- Customer data retrieval

### Content Management
- Post statistics
- User metrics
- Category information
- Search functionality

---

## Best Practices

### 1. Configure Admin API Key
For better UX, set the API key in admin so users don't need to enter it.

### 2. Show Tool Information
Keep `show_tool_info="yes"` to educate users about available tools.

### 3. Test Tools Regularly
Ensure tool functions are working correctly and returning expected results.

### 4. Monitor API Usage
Track OpenAI API costs, especially on public pages.

### 5. Clear Tool Descriptions
When adding new tools, make descriptions clear for the AI to understand.

---

## Differences from Admin Interface

| Feature | Admin Interface | Shortcode Interface |
|---------|----------------|---------------------|
| **Access** | Admin users only | All visitors |
| **API Key** | Required in settings | Optional (user can provide) |
| **Styling** | WordPress admin theme | Custom gradient design |
| **JavaScript** | Inline in PHP | Separate file |
| **AJAX Action** | `tool_use_ai` | `wp_tool_use_frontend_query` |
| **Tool Functions** | Same | Same |
| **Tool Definitions** | Same | Same |

---

## Browser Compatibility

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers

**Requirements:**
- JavaScript enabled
- jQuery (loaded by WordPress)

---

## Troubleshooting

### Shortcode Not Displaying
- Verify plugin is activated
- Check spelling: `[wp_tool_use]`
- Check for PHP errors in debug.log

### Tools Not Executing
- Verify tool functions exist
- Check tool definitions match function names
- Review console for JavaScript errors

### No AI Response
- Verify API key is correct and has credits
- Check browser console for errors
- Test in admin interface first

---

## Future Enhancement Ideas

- [ ] More tools (database queries, external APIs)
- [ ] Tool usage statistics
- [ ] Custom tool builder in admin
- [ ] Tool parameter validation
- [ ] Rate limiting per user/IP
- [ ] Tool execution history
- [ ] Tool permissions system
- [ ] Multi-step tool workflows

---

## Support

For issues or questions:
1. Check this documentation
2. Review main plugin documentation: `EXPLANTION-03.md`
3. Check WordPress debug.log
4. Check browser console for errors

---

## License

This shortcode is part of the WP Tool Use plugin.

**Author:** Craig West  
**Version:** 1.0.0  
**WordPress Version:** 5.0+  
**PHP Version:** 7.4+  
**OpenAI Model:** GPT-4o-mini with function calling

---

## Changelog

### Version 1.0.0
- Initial release
- Frontend shortcode with AJAX support
- Weather and math tools
- API key configuration on frontend
- Tool execution display
- Gradient design theme
- Responsive layout
- Security features
