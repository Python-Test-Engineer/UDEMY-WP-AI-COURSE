# Quick Start Guide - WP Basic Agent Shortcode

## 🚀 Get Started in 3 Steps

### Step 1: Activate Plugin
The plugin should already be active. If not, go to:
- **WordPress Admin → Plugins**
- Find "✅ 02 UDEMY BASIC AGENT WITH JS"
- Click "Activate"

### Step 2: Add Shortcode to a Page
1. Go to **Pages → Add New** (or edit existing page)
2. Add this shortcode:
   ```
   [wp_basic_agent]
   ```
3. Click **Publish** or **Update**

### Step 3: Test It!
Visit the page and you'll see the AI agent interface.

---

## 💡 Usage Examples

### Basic Usage (with API key input)
```
[wp_basic_agent]
```
Users can enter their own API key on the frontend.

### With Admin API Key Only
```
[wp_basic_agent show_api_key_input="no"]
```
Hide the API key input (requires admin to set API key first).

### Custom Placeholder
```
[wp_basic_agent placeholder="Ask me about WordPress..."]
```

### Full Customization
```
[wp_basic_agent placeholder="How can I help you?" show_api_key_input="no"]
```

---

## 🔑 API Key Configuration

### Option 1: Configure in Admin (Recommended)
1. Go to **BASIC AGENT** in WordPress admin sidebar
2. Enter your OpenAI API key
3. Click "Save API Key"
4. The key will be pre-filled on the frontend

### Option 2: Let Users Enter Their Own Key
If you don't configure an admin key, users can enter their own API key directly on the frontend.

---

## ✅ What's Included

### Files Created:
- ✅ `shortcode/wp-basic-agent-shortcode.php` - Main shortcode handler
- ✅ `shortcode/assets/css/frontend-styles.css` - Professional styling
- ✅ `shortcode/assets/js/frontend-script.js` - AJAX functionality
- ✅ `shortcode/README.md` - Complete documentation
- ✅ `shortcode/QUICK-START.md` - This file

### Plugin Updated:
- ✅ `wp-basic-agent.php` - Now includes shortcode functionality

---

## 🎨 Features

- ✅ **Frontend API Key Input** - Users can configure API key on the page
- ✅ **Show/Hide Toggle** - JavaScript toggle to show/hide API keys
- ✅ **AJAX Powered** - No page reloads
- ✅ **Loading Indicators** - Visual feedback during processing
- ✅ **Error Handling** - Clear error messages
- ✅ **Responsive Design** - Works on all devices
- ✅ **System Prompt Integration** - Uses same prompt as admin interface
- ✅ **Security** - Nonce verification and data sanitization
- ✅ **Modern Styling** - Professional green/purple color scheme

---

## 🔒 Security

- ✅ WordPress nonce verification
- ✅ Input sanitization
- ✅ Output escaping
- ✅ Works for logged-in AND logged-out users
- ✅ API key is never stored in browser

---

## 🎯 Testing

### Quick Test:
1. Add `[wp_basic_agent]` to a page
2. Visit the page
3. Enter a prompt: "What is WordPress?"
4. Click "Send Request"
5. See the AI response!

### Test with API Key Input:
1. Enter your OpenAI API key (sk-...)
2. Click "Show" to verify it
3. Enter a prompt
4. Submit and watch the magic happen ✨

---

## 📱 Where to Use

Perfect for:
- ✅ Help pages
- ✅ FAQ pages
- ✅ Customer support pages
- ✅ Product information pages
- ✅ Blog posts
- ✅ Landing pages
- ✅ Any WordPress page or post!

---

## 🐛 Troubleshooting

### Shortcode shows as text?
- Plugin not activated
- Check spelling: `[wp_basic_agent]`

### No response from AI?
- Check API key is valid
- Ensure API key has credits
- Check browser console for errors

### Styling looks wrong?
- Clear browser cache
- Check for theme conflicts

---

## 📚 More Help

For detailed documentation, see:
- `README.md` - Complete documentation
- `EXPLANATION-02.md` - Plugin architecture details

---

## 🎉 You're All Set!

The shortcode is ready to use. Just add `[wp_basic_agent]` to any page and you're good to go!

**Shortcode:** `[wp_basic_agent]`
**Model:** GPT-4o-mini
**Author:** Craig West
**Version:** 1.0.0
