# @wordpress/scripts package.json Configuration

`@wordpress/scripts` is a WordPress package that provides pre-configured build tools for WordPress plugin and theme development. Here's how to configure it in your `package.json`:

## Basic Setup

```json
{
  "name": "my-wordpress-plugin",
  "version": "1.0.0",
  "description": "My WordPress plugin",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "test": "wp-scripts test-unit-js",
    "lint:js": "wp-scripts lint-js",
    "lint:css": "wp-scripts lint-style",
    "format": "wp-scripts format",
    "packages-update": "wp-scripts packages-update"
  },
  "devDependencies": {
    "@wordpress/scripts": "^27.0.0"
  }
}
```

## Available Scripts

- **`wp-scripts build`**: Production build (minified, optimized)
- **`wp-scripts start`**: Development mode with hot reload
- **`wp-scripts test-unit-js`**: Run Jest tests
- **`wp-scripts test-e2e`**: Run end-to-end tests with Playwright
- **`wp-scripts lint-js`**: Lint JavaScript with ESLint
- **`wp-scripts lint-style`**: Lint CSS with Stylelint
- **`wp-scripts format`**: Format code with Prettier
- **`wp-scripts check-engines`**: Verify Node/npm versions
- **`wp-scripts check-licenses`**: Check dependency licenses
- **`wp-scripts packages-update`**: Update WordPress packages

## Configuration

### Default Behavior
By default, `@wordpress/scripts` looks for:
- **Entry point**: `src/index.js`
- **Output**: `build/index.js`

### Custom Configuration (webpack)
Create a `webpack.config.js` to customize:

```javascript
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  entry: {
    frontend: './src/frontend.js',
    admin: './src/admin.js'
  }
};
```

### ESLint Configuration
Extend in `.eslintrc.js`:

```javascript
module.exports = {
  extends: ['plugin:@wordpress/eslint-plugin/recommended']
};
```

### Babel Configuration
Extend in `babel.config.js`:

```javascript
module.exports = {
  presets: ['@wordpress/babel-preset-default']
};
```

## Typical WordPress Block Plugin Example

```json
{
  "name": "my-block-plugin",
  "version": "1.0.0",
  "description": "Custom WordPress block",
  "main": "build/index.js",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "lint:js": "wp-scripts lint-js",
    "format": "wp-scripts format"
  },
  "devDependencies": {
    "@wordpress/scripts": "^27.0.0"
  },
  "dependencies": {
    "@wordpress/block-editor": "^12.0.0",
    "@wordpress/blocks": "^12.0.0",
    "@wordpress/i18n": "^4.0.0"
  }
}
```

## Key Benefits

- **Zero Configuration**: Works out of the box for most WordPress projects
- **Webpack Pre-configured**: Modern JavaScript bundling with React/JSX support
- **WordPress Standards**: Follows WordPress coding standards automatically
- **Development Server**: Hot module replacement for faster development
- **Modern JS**: Babel compilation for ES6+ features

The package handles all the complex build tooling so you can focus on WordPress development without configuring webpack, Babel, ESLint, or other tools manually.