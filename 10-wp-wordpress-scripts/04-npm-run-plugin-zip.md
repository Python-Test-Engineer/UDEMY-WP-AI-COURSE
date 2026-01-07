# plugin-zip script

`npm run plugin-zip` is a script command provided by `@wordpress/scripts` that creates a production-ready `.zip` file of your WordPress plugin.

Here's what it does:

**Primary function:**
- Builds your plugin for production (minifies JS/CSS, optimizes assets)
- Creates a `.zip` file in the project root containing only the files needed for distribution
- Excludes development files like `node_modules/`, source files, config files, etc.

**Typical workflow:**
```bash
npm run plugin-zip
```

This command:
1. Runs a production build of your assets
2. Packages everything into a zip file named after your plugin
3. The resulting zip can be uploaded directly to WordPress.org or distributed to users

**What gets included:**
- Built/compiled assets (minified JS, CSS)
- PHP files
- Templates
- Plugin metadata files
- Any other files needed for the plugin to function

**What gets excluded:**
- `node_modules/`
- Source files (uncompiled JS/CSS)
- Development config files (`.eslintrc`, `webpack.config.js`, etc.)
- Git files
- Tests

You can customize what gets included/excluded by creating a `.distignore` file in your plugin root, which works similar to `.gitignore`.

This is particularly useful when you're ready to release your plugin and need a clean, optimized package without all the development dependencies.