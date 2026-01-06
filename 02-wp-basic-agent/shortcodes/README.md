# WP Basic Agent Shortcode Documentation

## Overview
The WP Basic Agent shortcode allows you to embed an AI-powered chat interface on any WordPress page or post. Users can interact with OpenAI's GPT-4o-mini model directly from the frontend.

---

## Shortcode Usage

### Basic Usage
```
[wp_basic_agent]
```

Add this shortcode to any page or post to display the AI agent interface.

---

## Shortcode Attributes

### 1. Custom Placeholder Text
Change the default prompt placeholder:
```
[wp_basic_agent placeholder="Type your question here..."]
```

**Default:** `Enter your prompt...`

### 2. Hide API Key Input
Hide the API key input field from users (requires admin-configured API key):
```
[wp_basic_agent show_api_key_input="no"]
```

**Default:** `yes`  
**Options:** `yes` or `no`

### 3. Combined Attributes
Use multiple attributes together:
```
[wp_basic_agent placeholder="Ask me anything!" show_api_key_input="no"]
```

---

## Features

### ✅ Frontend API Key Configuration
- **Admin API Key:** If set in the admin panel, it's pre-populated in the frontend
- **User API Key:** Users can enter their own API key to override the admin key
- **Show/Hide Toggle:** JavaScript-based toggle to show/hide the API key
- **Flexible Usage:** Works with admin-configured keys or user-provided keys

### ✅ AJAX-Powered Interface
- **No Page Reload:** Submissions are handled via AJAX
- **Loading Indicators:** Visual feedback during API requests
- **Error Handling:** Clear error messages for failed requests
- **Response Display:** AI responses appear in a formatted container

### ✅ System Prompt Integration
- Uses the same system prompt as the admin interface
- Located in: `admin/includes/inc-system-prompt.php`
- Ensures consistent AI behavior across admin and frontend

### ✅ Modern Design
- **Professional Styling:** Clean, modern interface with green accents
- **Responsive Design:** Works on desktop, tablet, and mobile
- **Accessible:** Proper form labels and ARIA attributes
- **Smooth Animations:** Fade-in effects for responses and errors

---

## Security Features

### 🔒 Nonce Verification
Every AJAX request includes WordPress nonce verification to prevent CSRF attacks.

### 🔒 Data Sanitization
- User inputs are sanitized using `sanitize_textarea_field()`
- API keys are sanitized with `sanitize_text_field()`
- Outputs are escaped with `esc_attr()` and `esc_html()`

### 🔒 Public Access
- Shortcode works for both logged-in and logged-out users
- Uses `wp_ajax_nopriv_` hook for non-authenticated users
- API key is required (either from admin or user input)

---

## File Structure

```
02-wp-basic-agent/
├── shortcode/
│   ├── wp-basic-agent-shortcode.php    # Main shortcode file
│   ├── README.md                        # This documentation
│   └── assets/
│       ├── css/
│       │   └── frontend-styles.css     # Frontend styling
│       └── js/
│           └── frontend-script.js      # Frontend JavaScript
```

---

## How It Works

### 1. Shortcode Registration
```php
add_shortcode('wp_basic_agent', 'wp_basic_agent_shortcode');
```

### 2. Asset Enqueuing
Assets are conditionally loaded only on pages with the shortcode:
```php
if (has_shortcode($post->post_content, 'wp_basic_agent')) {
    wp_enqueue_style('wp-basic-agent-frontend-styles', ...);
    wp_enqueue_script('wp-basic-agent-frontend-script', ...);
}
```

### 3. AJAX Handler
Frontend requests are routed to:
```php
add_action('wp_ajax_wp_basic_agent_frontend_query', 'wp_basic_agent_frontend_ajax_handler');
add_action('wp_ajax_nopriv_wp_basic_agent_frontend_query', 'wp_basic_agent_frontend_ajax_handler');
```

### 4. API Request Flow
```
User submits query
    ↓
JavaScript validates input
    ↓
AJAX POST to admin-ajax.php
    ↓
PHP handler verifies nonce
    ↓
Sanitizes input and gets API key
    ↓
Loads system prompt
    ↓
Sends request to OpenAI API
    ↓
Returns formatted response
    ↓
JavaScript displays result
```

---

## API Key Priority

1. **User-Provided Key:** If user enters a key in the frontend, it's used
2. **Admin-Configured Key:** Falls back to the key saved in admin settings
3. **No Key:** Shows error message requesting API key

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
The default styling uses:
- **Primary Green:** `#099709` (borders, buttons, headers)
- **Secondary Purple:** `#667eea` (subtitle, secondary buttons)
- **Response Background:** `antiquewhite` with blue border
- **Error Background:** Light red with red border

### Override Styles
To customize, add CSS to your theme:
```css
/* Change primary button color */
.wp-basic-agent-button-primary {
    background-color: #your-color !important;
}

/* Change response container */
.wp-basic-agent-response-container {
    background-color: #your-color !important;
    border-color: #your-border-color !important;
}
```

---

## JavaScript Customization

### Modify AJAX Handler
The JavaScript is located in `assets/js/frontend-script.js` and uses jQuery.

### Key Functions:
- `showResponse(text)` - Displays AI response
- `showError(message)` - Displays error messages
- `setLoadingState(isLoading)` - Toggles loading state

---

## WordPress Integration

### Hooks Used

**Shortcode:**
```php
add_shortcode('wp_basic_agent', 'wp_basic_agent_shortcode');
```

**Asset Enqueuing:**
```php
add_action('wp_enqueue_scripts', 'wp_basic_agent_enqueue_frontend_assets');
```

**AJAX Actions:**
```php
add_action('wp_ajax_wp_basic_agent_frontend_query', 'wp_basic_agent_frontend_ajax_handler');
add_action('wp_ajax_nopriv_wp_basic_agent_frontend_query', 'wp_basic_agent_frontend_ajax_handler');
```

---

## Testing the Shortcode

### Step 1: Activate Plugin
Make sure the "02 UDEMY BASIC AGENT WITH JS" plugin is activated.

### Step 2: Configure API Key (Optional)
Go to **BASIC AGENT** in the WordPress admin and save your OpenAI API key.

### Step 3: Create a Test Page
1. Create a new page in WordPress
2. Add the shortcode: `[wp_basic_agent]`
3. Publish the page

### Step 4: Test on Frontend
1. Visit the published page
2. Enter a query (e.g., "What is WordPress?")
3. Click "Send Request"
4. Watch for the AI response

### Step 5: Test API Key Input
If API key wasn't configured in admin:
1. Enter your OpenAI API key in the frontend field
2. Click "Show" to verify the key
3. Submit a query

---

## Browser Compatibility

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

**Requirements:**
- JavaScript enabled
- jQuery (loaded by WordPress)

---

## Performance Considerations

### Asset Loading
- CSS and JS only load on pages with the shortcode
- Conditional loading prevents unnecessary HTTP requests
- jQuery dependency is already included in WordPress

### AJAX Optimization
- 30-second timeout for API requests
- Loading indicators prevent double submissions
- Error handling for timeouts and network issues

---

## Troubleshooting

### Shortcode Not Displaying
- Verify plugin is activated
- Check for PHP errors in debug.log
- Ensure shortcode spelling is correct: `[wp_basic_agent]`

### No Response from AI
- Verify API key is correct and has credits
- Check browser console for JavaScript errors
- Test API key in admin interface first

### Styling Issues
- Clear browser cache
- Check for CSS conflicts with theme
- Verify `frontend-styles.css` is loading

### AJAX Not Working
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify nonce is being created correctly

---

## Extending the Shortcode

### Add More Attributes
```php
$atts = shortcode_atts(
    array(
        'placeholder' => 'Enter your prompt...',
        'show_api_key_input' => 'yes',
        'button_text' => 'Send Request', // New attribute
        'max_tokens' => 1024, // New attribute
    ),
    $atts,
    'wp_basic_agent'
);
```

### Customize System Prompt per Shortcode
Modify the AJAX handler to accept a custom prompt attribute.

### Add Response History
Store previous responses in browser localStorage or WordPress user meta.

---

## Differences from Admin Interface

| Feature | Admin Interface | Shortcode Interface |
|---------|----------------|---------------------|
| **Access** | Admin users only | All visitors |
| **API Key** | Required in settings | Optional (can be provided by user) |
| **Styling** | Admin styles (70% width) | Frontend styles (max 800px) |
| **JavaScript** | `openai.js` | `frontend-script.js` |
| **AJAX Action** | `openai_proxy` | `wp_basic_agent_frontend_query` |
| **System Prompt** | Same file (`inc-system-prompt.php`) | Same file |

---

## Best Practices

### 1. Configure Admin API Key
For better UX, configure the API key in admin settings so users don't need to enter it.

### 2. Hide API Key Input for Public Sites
If you don't want users to enter their own keys:
```
[wp_basic_agent show_api_key_input="no"]
```

### 3. Monitor API Usage
Keep track of OpenAI API costs, especially on public-facing pages with high traffic.

### 4. Rate Limiting (Future Enhancement)
Consider adding rate limiting to prevent abuse on public pages.

### 5. Custom Placeholder Text
Use contextual placeholders:
```
[wp_basic_agent placeholder="Ask about our products..."]
```

---

## Future Enhancements

### Potential Features:
- [ ] Rate limiting per user/IP
- [ ] Conversation history
- [ ] Multiple AI models selection
- [ ] Temperature control
- [ ] Token usage display
- [ ] Character counter
- [ ] Voice input support
- [ ] Export conversation as PDF/text
- [ ] Shortcode builder in admin
- [ ] Widget support

---

## Support

For issues or questions:
1. Check this documentation
2. Review the main plugin documentation: `EXPLANATION-02.md`
3. Check WordPress debug.log for PHP errors
4. Check browser console for JavaScript errors

---

## License

This shortcode is part of the WP Basic Agent plugin.

**Author:** Craig West  
**Version:** 1.0.0  
**WordPress Version:** 5.0+  
**PHP Version:** 7.4+

---

## Changelog

### Version 1.0.0
- Initial release
- Frontend shortcode with AJAX support
- API key configuration on frontend
- Show/hide toggle for API key
- Responsive design
- Error handling
- System prompt integration
