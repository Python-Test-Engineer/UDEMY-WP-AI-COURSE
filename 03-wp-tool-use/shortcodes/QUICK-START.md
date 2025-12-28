# Quick Start Guide - WP Tool Use Shortcode

## 🚀 Get Started in 3 Steps

### Step 1: Activate Plugin
The plugin should already be active. If not, go to:
- **WordPress Admin → Plugins**
- Find "✅ 03 UDEMY TOOL USE"
- Click "Activate"

### Step 2: Add Shortcode to a Page
1. Go to **Pages → Add New** (or edit existing page)
2. Add this shortcode:
   ```
   [wp_tool_use]
   ```
3. Click **Publish** or **Update**

### Step 3: Test the Tools!
Visit the page and try asking:
- "What's the weather?" (uses Weather Tool)
- "Add 25 and 75" (uses Math Tool)

---

## 💡 Usage Examples

### Basic Usage (shows everything)
```
[wp_tool_use]
```
Displays API key input, tool cards, and query interface.

### Hide API Key Input
```
[wp_tool_use show_api_key_input="no"]
```
Hides API key field (requires admin to set key first).

### Hide Tool Information Cards
```
[wp_tool_use show_tool_info="no"]
```
Hides the tool cards display.

### Custom Placeholder
```
[wp_tool_use placeholder="Ask about weather or do math!"]
```

### Full Customization
```
[wp_tool_use placeholder="Try the AI tools!" show_api_key_input="no" show_tool_info="no"]
```

---

## 🔑 API Key Configuration

### Option 1: Configure in Admin (Recommended)
1. Go to **03 TOOL USE** in WordPress admin sidebar
2. Enter your OpenAI API key
3. Click "Save API Key"
4. The key will be pre-filled on the frontend

### Option 2: Let Users Enter Their Own Key
If you don't configure an admin key, users can enter their own API key directly on the frontend.

---

## ✅ What's Included

### Files Created:
- ✅ `shortcode/wp-tool-use-shortcode.php` - Main shortcode handler with tool calling
- ✅ `shortcode/assets/css/frontend-styles.css` - Gradient design with tool cards
- ✅ `shortcode/assets/js/frontend-script.js` - AJAX with tool execution display
- ✅ `shortcode/README.md` - Complete documentation
- ✅ `shortcode/QUICK-START.md` - This file

### Plugin Updated:
- ✅ `wp-tool-use.php` - Now includes shortcode functionality

---

## 🛠️ Available Tools

### 🌤️ Weather Tool
**What it does:** Returns random temperature (-10°C to 40°C)  
**Try asking:** 
- "What's the weather today?"
- "Tell me the temperature"
- "How's the weather?"

### ➕ Math Tool
**What it does:** Adds two numbers together  
**Try asking:**
- "Add 15 and 27"
- "Calculate 123 plus 456"
- "What's 50 + 75?"

---

## 🎨 Unique Features

- ✅ **AI Tool Use (Function Calling)** - AI automatically decides when to use tools
- ✅ **Tool Execution Display** - Shows which tools were used in green gradient box
- ✅ **Interactive Tool Cards** - Beautiful cards showing available tools
- ✅ **Gradient Design** - Purple gradient theme with green accents
- ✅ **Frontend API Key Input** - Users can configure API key on the page
- ✅ **Show/Hide Toggle** - JavaScript toggle for API keys
- ✅ **AJAX Powered** - No page reloads
- ✅ **Loading Indicators** - Visual feedback during processing
- ✅ **Error Handling** - Clear error messages
- ✅ **Responsive Design** - Works on all devices
- ✅ **Security** - Nonce verification and sanitization

---

## 🔒 Security

- ✅ WordPress nonce verification
- ✅ Input sanitization
- ✅ Output escaping
- ✅ XSS prevention
- ✅ Works for logged-in AND logged-out users
- ✅ API key never stored in browser

---

## 🎯 Testing

### Quick Weather Test:
1. Add `[wp_tool_use]` to a page
2. Visit the page
3. Enter: "What's the weather?"
4. Click "Send to AI"
5. See TWO things:
   - AI response with the temperature
   - Tool execution in green box showing which tool was used!

### Quick Math Test:
1. Enter: "Add 100 and 200"
2. Click "Send to AI"
3. Watch the magic:
   - AI calls the math tool
   - Tool calculates: 100 + 200 = 300
   - AI presents the answer naturally
   - Tool execution shown below

---

## 🎓 What Makes This Special?

This isn't just a chatbot - it's a **Tool Use Demo**!

### Regular AI:
❌ Can only generate text responses  
❌ Can't access real-time data  
❌ Limited to training knowledge  

### AI with Tools:
✅ Can call functions  
✅ Can get real-time information  
✅ Can perform calculations  
✅ Can interact with your system  

**This is the future of AI assistants!**

---

## 📱 Where to Use

Perfect for:
- ✅ Educational demonstrations
- ✅ Tool use tutorials
- ✅ Interactive examples
- ✅ Technical documentation
- ✅ Product demos
- ✅ Learning pages
- ✅ Any page explaining AI capabilities!

---

## 🐛 Troubleshooting

### Shortcode shows as text?
- Plugin not activated
- Check spelling: `[wp_tool_use]`

### Tools not executing?
- Check browser console for errors
- Verify API key is valid
- Ensure API key has credits

### Styling looks wrong?
- Clear browser cache
- Check for theme conflicts

---

## 🎓 Understanding Tool Execution

When you ask "What's the weather?":

1. **AI Analyzes** - Recognizes weather question
2. **AI Decides** - Chooses to use `get_weather()` tool
3. **Tool Executes** - PHP function runs, returns 23°C
4. **AI Responds** - "The current temperature is 23°C"
5. **You See**:
   - AI response in main container
   - "Weather tool executed: Current temperature is 23°C" in green box

---

## 📚 More Help

For detailed documentation, see:
- `README.md` - Complete documentation with examples
- `EXPLANTION-03.md` - Tool use concept explanation

---

## 🎉 You're All Set!

The shortcode is ready to demonstrate AI tool use!

**Try these prompts:**
- "What's the temperature?"
- "Add 42 and 58"
- "Tell me the weather and add 10 plus 20"

**Shortcode:** `[wp_tool_use]`  
**Model:** GPT-4o-mini with function calling  
**Tools:** Weather (random temp) + Math (addition)  
**Author:** Craig West  
**Version:** 1.0.0

---

## 💡 Pro Tip

Ask complex questions to see the AI intelligently choose tools:

- "What's the weather and add 5 and 7" - AI will use BOTH tools!
- "Add three numbers: 10, 20, and 30" - Watch AI adapt
- "Is it hot outside?" - AI uses weather tool and interprets result

**This demonstrates true AI intelligence!** 🚀
