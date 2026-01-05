# @wordpress/scripts package.json Configuration

`@wordpress/scripts` is a WordPress package that provides pre-configured build tools for WordPress plugin and theme development. Here's how to configure it in your `package.json`:

## Basic Setup

```json
{
  "name": "wp-scripts-demo",
  "version": "1.0.0",
  "description": "WordPress plugin with using @wordpress/scripts",
  "main": "src/index.js",
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "check-engines": "wp-scripts check-engines",
    "check-licenses": "wp-scripts check-licenses",
    "format": "wp-scripts format",
    "lint:css": "wp-scripts lint-style",
    "lint:js": "wp-scripts lint-js",
    "lint:pkg-json": "wp-scripts lint-pkg-json",
    "packages-update": "wp-scripts packages-update",
    "plugin-zip": "wp-scripts plugin-zip"
  },
  "engines": {
    "node": ">=14.0.0",
    "npm": ">=6.14.4"
  },
  "devDependencies": {
    "@wordpress/scripts": "^31.2.0"
  }
}

```
## Installing @wordpress/scripts

If you have a minimal `package.json`, you can install @wordpress/scripts with `npm install @wordpress/scripts --save-dev`. This saves it as a dependency.

[https://www.npmjs.com/package/@wordpress/scripts](https://www.npmjs.com/package/@wordpress/scripts)

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
- **`wp-scripts plugin-zip:`**: Creates plugin zip with just build folder and all the other plugin files for WordPress but not package.json, src etc that are not needed.

## Configuration

### Default Behavior
By default, `@wordpress/scripts` looks for:
- **Entry point**: `src/index.js`
- **Output**: `build/index.js`

## Key Benefits

- **Zero Configuration**: Works out of the box for most WordPress projects
- **Webpack Pre-configured**: Modern JavaScript bundling with React/JSX support
- **WordPress Standards**: Follows WordPress coding standards automatically
- **Development Server**: Hot module replacement for faster development
- **Modern JS**: Babel compilation for ES6+ features

The package handles all the complex build tooling so you can focus on WordPress development without configuring webpack, Babel, ESLint, or other tools manually.