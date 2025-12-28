# WP OpenAI Set Key

A secure WordPress plugin that allows you to set and manage your OpenAI API key in one central location, making it accessible to all other plugins that need it.

## 🔐 Why Use This Plugin?

Instead of entering your OpenAI API key in multiple plugins (each storing it differently), this plugin provides a **single, secure location** to store your API key. Other plugins can then retrieve it programmatically, ensuring:

- **Security**: API key is encrypted using AES-256-CBC encryption with WordPress security salts
- **Convenience**: Set your key once, use it everywhere
- **Consistency**: All plugins access the same key
- **Easy Management**: Update your key in one place and all plugins instantly use the new key


## 🚀 How It Works

### For Site Administrators

1. **Install & Activate** this plugin
2. Navigate to **OPENAI KEY** in the WordPress admin menu (top-level, below Dashboard)
3. Enter your OpenAI API key (starts with `sk-proj-` or `sk-`)
4. Click **Save API Key**
5. Your key is now encrypted and stored securely

#### Viewing Your Key

- Use the **Display Masked API Key** button to view a partially masked version of your key
- The middle 10 characters are replaced with asterisks for security
- Example: `sk-proj-abc**********xyz123`

#### Updating or Deleting Your Key

- Simply enter a new key and save to update
- Leave the field empty and save to delete the key

### For Plugin Developers

Other WordPress plugins can easily retrieve the stored API key using the provided public API:

#### Basic Usage

```php
// Get the API key
$api_key = Secure_OpenAI_Key_Manager::get_api_key();

if (!empty($api_key)) {
    // Use the API key in your OpenAI API calls
    $client = new OpenAI\Client($api_key);
} else {
    // No API key configured - display an admin notice
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Please configure your OpenAI API key in <a href="' . admin_url('admin.php?page=openai-api-key') . '">Settings → OPENAI KEY</a></p></div>';
    });
}
```

#### Available Public Methods

**1. Get the API Key**
```php
$api_key = Secure_OpenAI_Key_Manager::get_api_key();
// Returns: string (the decrypted API key) or false (if not configured)
```

**2. Check if API Key Exists**
```php
$has_key = Secure_OpenAI_Key_Manager::has_api_key();
// Returns: bool (true if key exists, false otherwise)
```

**3. Get Masked API Key (for display)**
```php
$masked_key = Secure_OpenAI_Key_Manager::get_masked_api_key();
// Returns: string (e.g., "sk-pr**********xyz123") or false (if not configured)
```

#### Example: Conditional Feature Activation

```php
class My_OpenAI_Plugin {
    public function __construct() {
        if (Secure_OpenAI_Key_Manager::has_api_key()) {
            // API key is configured, enable features
            add_action('init', array($this, 'enable_features'));
        } else {
            // No API key, show admin notice
            add_action('admin_notices', array($this, 'show_key_notice'));
        }
    }
    
    public function show_key_notice() {
        ?>
        <div class="notice notice-warning">
            <p><strong>My OpenAI Plugin:</strong> Please configure your OpenAI API key in 
            <a href="<?php echo admin_url('admin.php?page=openai-api-key'); ?>">OPENAI KEY settings</a> 
            to use this plugin.</p>
        </div>
        <?php
    }
}
```

## 🔒 Security Features

- **AES-256-CBC Encryption**: API key is encrypted using industry-standard encryption
- **WordPress Salts**: Uses `AUTH_KEY`, `SECURE_AUTH_KEY`, `AUTH_SALT`, and `SECURE_AUTH_SALT` from wp-config.php as encryption keys
- **Never Plain Text**: Key is never stored in plain text in the database
- **Nonce Protection**: All form submissions and AJAX requests are protected with WordPress nonces
- **Capability Checks**: Only administrators can view or modify the API key
- **Multiple Storage Options**: Supports priority-based key sources:
  1. **wp-config.php** (highest priority) - Define `OPENAI_API_KEY` constant
  2. **Environment Variable** - Set `OPENAI_API_KEY` environment variable
  3. **Encrypted Database** (lowest priority) - Stored via admin interface

## 📋 Installation

1. Download or clone this repository
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Navigate to **OPENAI KEY** in the admin menu
5. Enter and save your OpenAI API key

## 🔧 Configuration Priority

The plugin checks for the API key in the following order:

1. **wp-config.php constant** (recommended for production):
   ```php
   define('OPENAI_API_KEY', 'sk-proj-your-key-here');
   ```

2. **Environment variable**:
   ```bash
   export OPENAI_API_KEY="sk-proj-your-key-here"
   ```

3. **Database** (encrypted, set via admin interface)

## 📝 Getting Your OpenAI API Key

1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Sign in to your account
3. Navigate to **API Keys** section
4. Click **Create new secret key**
5. Copy the key (starts with `sk-proj-` or `sk-`)
6. Paste it into this plugin's settings page

**Important**: Keep your API key secret! Never commit it to version control or share it publicly.

## 🛠️ Technical Details

### Class: `Secure_OpenAI_Key_Manager`

**Public Static Methods:**
- `get_api_key()` - Returns the decrypted API key or false
- `has_api_key()` - Returns boolean indicating if key is configured
- `get_masked_api_key()` - Returns partially masked key for display

**Private Methods:**
- `encrypt($data)` - Encrypts data using AES-256-CBC
- `decrypt($data)` - Decrypts encrypted data
- `save_api_key()` - Saves encrypted key to database

### Admin Interface

- **Top-level menu** in WordPress admin (below Dashboard)
- **Menu icon**: Network/AI icon (dashicons-admin-network)
- **Settings page** with key input, save, and display features
- **AJAX-powered** masked key display

## 🤝 Contributing

If you're building a WordPress plugin that uses OpenAI API, please consider integrating with this plugin to provide a better user experience. Users only need to set their API key once!

## 📄 License

This plugin is provided as-is for use in WordPress projects.

## 🆘 Support

For issues or questions:
- Check that the plugin is activated
- Verify your API key is valid on OpenAI Platform
- Ensure you have administrator privileges
- Check wp-config.php for conflicting `OPENAI_API_KEY` definitions

## 🔄 Version

Current Version: 1.0.0

December 2025