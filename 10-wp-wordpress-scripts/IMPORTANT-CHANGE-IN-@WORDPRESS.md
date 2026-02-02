# Change in bundled css file name

As of 19JAN2026, I have noticed that the npm run build command now produces a file called `index.css` rather than `style-index.css`.

If you run this command and the plugins don't work, firstly use the ones supplied as the enqueue and file names are synchronised.

Then if you decide to do your own `npm run build` ensure the css in the build folder is correctly enqueued by changing the file names accordingly in the main WP plugin file.:

```php
    wp_enqueue_style(
        'my-custom-app-style',
        MY_PLUGIN_URL . 'build/style-index.css', // this needs to be changed to index.css or whatever is in the build folder
        [],
        $asset_file['version']
    );
```